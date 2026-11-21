<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminFormController extends Controller
{
    private const TYPES = ['text', 'textarea', 'number', 'url', 'email', 'phone', 'date', 'select', 'checkboxes', 'checkbox'];

    private function emptyConfig(): array
    {
        return ['version' => 1, 'fields' => [], 'option_lists' => []];
    }

    private function settingArray(string $key, array $fallback): array
    {
        $value = Setting::get($key);
        if (is_array($value)) return $value;
        if (! is_string($value) || trim($value) === '') return $fallback;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $fallback;
    }

    private function permissions(Request $request): array
    {
        $user = $request->user();
        $owner = (bool) $user?->hasRole('admin');
        $administrator = $owner || (bool) $user?->hasRole('administrator') || (bool) $user?->can('admin.system.edit_site_settings');
        $editor = (bool) $user?->hasRole('editor');
        $support = (bool) $user?->hasRole('support');

        return [
            'can_view' => $owner || $administrator || $editor || $support || (bool) $user?->can('admin.forms.view'),
            'can_edit_fields' => $administrator,
            'can_manage_options' => $administrator || $editor,
            'can_delete_fields' => $owner,
            'can_publish' => $administrator || $editor,
            'role_label' => $owner ? 'Owner' : ($user?->hasRole('administrator') ? 'Administrator' : ($editor ? 'Editor' : ($support ? 'Support' : 'Administrator'))),
        ];
    }

    private function normalizeConfig(array $input): array
    {
        $fields = $input['fields'] ?? [];
        $lists = $input['option_lists'] ?? [];
        if (! is_array($fields) || count($fields) > 100) {
            abort(422, 'Add no more than 100 custom fields.');
        }
        if (! is_array($lists) || count($lists) > 50) {
            abort(422, 'Add no more than 50 option lists.');
        }

        $normalizedLists = [];
        foreach ($lists as $list) {
            if (! is_array($list)) abort(422, 'An option list is not valid.');
            $key = (string) ($list['key'] ?? '');
            $label = trim((string) ($list['label'] ?? ''));
            if (! preg_match('/^[a-z][a-z0-9_]{1,59}$/', $key) || $label === '' || mb_strlen($label) > 100) {
                abort(422, 'Every option list needs a valid name and label.');
            }
            if (isset($normalizedLists[$key])) abort(422, 'Option list names must be unique.');
            $options = $list['options'] ?? [];
            if (! is_array($options) || count($options) > 100) abort(422, 'An option list can contain up to 100 choices.');
            $normalizedOptions = [];
            $seen = [];
            foreach ($options as $option) {
                if (! is_array($option)) continue;
                $value = trim((string) ($option['value'] ?? ''));
                if ($value === '' || mb_strlen($value) > 120) continue;
                $fingerprint = mb_strtolower($value);
                if (isset($seen[$fingerprint])) continue;
                $seen[$fingerprint] = true;
                $normalizedOptions[] = [
                    'id' => (string) ($option['id'] ?? substr(sha1($key.'|'.$value), 0, 12)),
                    'value' => $value,
                    'enabled' => (bool) ($option['enabled'] ?? true),
                ];
            }
            $normalizedLists[$key] = ['key' => $key, 'label' => $label, 'options' => $normalizedOptions];
        }

        $normalizedFields = [];
        $seenIds = [];
        $seenKeys = [];
        foreach ($fields as $index => $field) {
            if (! is_array($field)) abort(422, 'A custom field is not valid.');
            $id = trim((string) ($field['id'] ?? ''));
            $key = trim((string) ($field['key'] ?? ''));
            $label = trim((string) ($field['label'] ?? ''));
            $type = (string) ($field['type'] ?? 'text');
            $registrationStep = $field['registration_step'] ?? null;
            $profileEnabled = (bool) ($field['profile_enabled'] ?? false);
            $optionList = trim((string) ($field['option_list'] ?? ''));
            if ($id === '' || mb_strlen($id) > 80 || isset($seenIds[$id])) abort(422, 'Every custom field needs a unique identifier.');
            if (! preg_match('/^[a-z][a-z0-9_]{1,59}$/', $key) || isset($seenKeys[$key])) abort(422, 'Custom field keys must be unique and use lowercase letters, numbers and underscores.');
            if ($label === '' || mb_strlen($label) > 120) abort(422, 'Every custom field needs a label of up to 120 characters.');
            if (! in_array($type, self::TYPES, true)) abort(422, 'Choose a supported field type.');
            if ($registrationStep !== null && ! in_array((int) $registrationStep, [2, 3, 4], true)) abort(422, 'Custom registration fields can be placed in School info, Location, or Facilities and fees.');
            if ($registrationStep === null && ! $profileEnabled) abort(422, 'Choose where each custom field should appear.');
            if (in_array($type, ['select', 'checkboxes'], true) && ($optionList === '' || ! isset($normalizedLists[$optionList]))) {
                abort(422, 'Choose an existing option list for each dropdown or checkbox group.');
            }
            $seenIds[$id] = true;
            $seenKeys[$key] = true;
            $normalizedFields[] = [
                'id' => $id,
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'help' => mb_substr(trim((string) ($field['help'] ?? '')), 0, 240),
                'required' => (bool) ($field['required'] ?? false),
                'enabled' => (bool) ($field['enabled'] ?? true),
                'registration_step' => $registrationStep === null ? null : (int) $registrationStep,
                'profile_enabled' => $profileEnabled,
                'option_list' => $optionList !== '' ? $optionList : null,
                'max_length' => max(0, min(5000, (int) ($field['max_length'] ?? 0))),
                'sort_order' => (int) ($field['sort_order'] ?? $index * 10),
            ];
        }

        return ['version' => 1, 'fields' => $normalizedFields, 'option_lists' => array_values($normalizedLists)];
    }

    public function index(Request $request): JsonResponse
    {
        $permissions = $this->permissions($request);
        abort_unless($permissions['can_view'], 403, 'You do not have permission to view form settings.');
        $published = $this->settingArray('school_forms_published', $this->emptyConfig());
        $draft = $this->settingArray('school_forms_draft', $published);
        $history = $this->settingArray('school_forms_history', []);

        return response()->json(['data' => [
            'published' => $published,
            'draft' => $draft,
            'history' => array_slice($history, 0, 10),
            'permissions' => $permissions,
        ]]);
    }

    public function saveDraft(Request $request): JsonResponse
    {
        $permissions = $this->permissions($request);
        abort_unless($permissions['can_edit_fields'] || $permissions['can_manage_options'], 403, 'You do not have permission to change form settings.');
        $request->validate(['config' => ['required', 'array']]);
        $config = $this->normalizeConfig($request->input('config', []));
        $currentDraft = $this->settingArray('school_forms_draft', $this->settingArray('school_forms_published', $this->emptyConfig())));
        $currentDraft = $this->normalizeConfig($currentDraft);

        if (! $permissions['can_edit_fields'] && $config['fields'] !== $currentDraft['fields']) {
            abort(403, 'Your role can manage option lists but cannot edit fields.');
        }
        if (! $permissions['can_manage_options'] && $config['option_lists'] !== $currentDraft['option_lists']) {
            abort(403, 'Your role cannot manage option lists.');
        }
        if (! $permissions['can_delete_fields']) {
            $nextIds = collect($config['fields'])->pluck('id')->all();
            foreach ($currentDraft['fields'] as $field) {
                if (! in_array($field['id'], $nextIds, true)) abort(403, 'Only the Owner can delete custom fields.');
            }
        }

        DB::transaction(function () use ($config) {
            Setting::query()->updateOrCreate(['key' => 'school_forms_draft'], [
                'value' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'forms',
                'label' => 'School form draft',
            ]);
        });

        return response()->json(['data' => ['draft' => $config, 'message' => 'Draft saved.']]);
    }

    public function publish(Request $request): JsonResponse
    {
        $permissions = $this->permissions($request);
        abort_unless($permissions['can_publish'], 403, 'You do not have permission to publish form settings.');
        $draft = $this->settingArray('school_forms_draft', $this->settingArray('school_forms_published', $this->emptyConfig()));
        $draft = $this->normalizeConfig($draft);
        $published = $this->settingArray('school_forms_published', $this->emptyConfig());

        $history = $this->settingArray('school_forms_history', []);
        array_unshift($history, [
            'message' => count($draft['fields']).' custom field'.(count($draft['fields']) === 1 ? '' : 's').' configured',
            'by' => (string) ($request->user()?->name ?? 'Administrator'),
            'at' => now()->toIso8601String(),
        ]);
        $history = array_slice($history, 0, 10);

        DB::transaction(function () use ($draft, $history) {
            Setting::query()->updateOrCreate(['key' => 'school_forms_published'], [
                'value' => json_encode($draft, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'forms',
                'label' => 'Published school forms',
            ]);
            Setting::query()->updateOrCreate(['key' => 'school_forms_draft'], [
                'value' => json_encode($draft, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'forms',
                'label' => 'School form draft',
            ]);
            Setting::query()->updateOrCreate(['key' => 'school_forms_history'], [
                'value' => json_encode($history, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'forms',
                'label' => 'School forms change history',
            ]);
        });

        return response()->json(['data' => ['published' => $draft, 'history' => $history, 'message' => 'Form settings published.']]);
    }
}
