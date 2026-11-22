<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Curriculum;
use App\Models\Facility;
use App\Models\SchoolType;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminFormController extends Controller
{
    private const TYPES = ['text', 'textarea', 'number', 'url', 'email', 'phone', 'date', 'select', 'checkboxes', 'checkbox'];

    private const REFERENCE_GROUPS = ['school_types', 'curricula', 'facilities'];

    private const STATIC_GROUPS = [
        'school_roles',
        'ownerships',
        'gender_policies',
        'boarding_types',
        'religious_affiliations',
        'referral_sources',
    ];

    private function emptyConfig(): array
    {
        return ['version' => 2, 'fields' => [], 'option_lists' => [], 'registration_options' => []];
    }

    private function defaultStaticChoices(): array
    {
        $make = static fn (string $id, string $value, ?string $label = null): array => [
            'id' => $id,
            'value' => $value,
            'label' => $label ?? $value,
            'enabled' => true,
        ];

        return [
            'school_roles' => [
                $make('role_director', 'Director / Proprietor'),
                $make('role_head_teacher', 'Head teacher / Principal'),
                $make('role_deputy_head_teacher', 'Deputy head teacher'),
                $make('role_administrator', 'Administrator'),
                $make('role_bursar', 'Bursar'),
                $make('role_marketing', 'Marketing officer'),
                $make('role_other', 'Other'),
            ],
            'ownerships' => [
                $make('ownership_private', 'Private'),
                $make('ownership_government', 'Government'),
                $make('ownership_faith_based', 'Faith-based'),
                $make('ownership_ngo_community', 'NGO / Community'),
            ],
            'gender_policies' => [
                $make('gender_mixed', 'mixed', 'Mixed (boys and girls)'),
                $make('gender_boys', 'boys', 'Boys only'),
                $make('gender_girls', 'girls', 'Girls only'),
            ],
            'boarding_types' => [
                $make('boarding_day', 'day', 'Day'),
                $make('boarding_full', 'boarding', 'Boarding'),
                $make('boarding_mixed', 'mixed', 'Day and boarding'),
            ],
            'religious_affiliations' => [
                $make('religion_non_denominational', 'Non-denominational'),
                $make('religion_catholic', 'Catholic'),
                $make('religion_anglican', 'Anglican'),
                $make('religion_pentecostal', 'Pentecostal'),
                $make('religion_sda', 'Seventh-day Adventist'),
                $make('religion_muslim', 'Muslim'),
                $make('religion_none', 'None'),
            ],
            'referral_sources' => [
                $make('referral_search', 'Search engine'),
                $make('referral_social', 'Social media'),
                $make('referral_school', 'Another school'),
                $make('referral_word_of_mouth', 'Word of mouth'),
                $make('referral_team', 'Your team contacted us'),
                $make('referral_other', 'Other'),
            ],
        ];
    }

    private function settingArray(string $key, array $fallback): array
    {
        $value = Setting::get($key);
        if (is_array($value)) return $value;
        if (! is_string($value) || trim($value) === '') return $fallback;
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $fallback;
    }

    private function defaultReferenceOptions(): array
    {
        $schoolTypes = SchoolType::query()->orderBy('order')->orderBy('name')->get()->map(fn (SchoolType $item) => [
            'id' => (string) $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'description' => $item->description,
            'icon' => $item->icon,
            'sort_order' => (int) $item->order,
            // The old A-Level school type is retired. Secondary covers both O-Level and A-Level.
            'enabled' => (bool) $item->is_active && $item->slug !== 'a-level',
        ])->values()->all();

        $curricula = Curriculum::query()->orderBy('name')->get()->map(fn (Curriculum $item) => [
            'id' => (string) $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'abbreviation' => $item->abbreviation,
            'description' => $item->description,
            'enabled' => (bool) $item->is_active && ! in_array($item->slug, ['uneb-o-level', 'uneb-a-level'], true),
        ])->values()->all();

        $curriculumSlugs = collect($curricula)->pluck('slug')->all();
        if (! in_array('uganda-national-curriculum-primary', $curriculumSlugs, true)) {
            $curricula[] = [
                'id' => 'new_curriculum_uganda_primary',
                'name' => 'Uganda National Curriculum (Primary)',
                'slug' => 'uganda-national-curriculum-primary',
                'abbreviation' => 'UG-PRI',
                'description' => 'Uganda national curriculum for primary education.',
                'enabled' => true,
            ];
        }
        if (! in_array('uganda-national-curriculum-uneb', $curriculumSlugs, true)) {
            $curricula[] = [
                'id' => 'new_curriculum_uganda_uneb',
                'name' => 'Uganda National Curriculum (UNEB)',
                'slug' => 'uganda-national-curriculum-uneb',
                'abbreviation' => 'UG-UNEB',
                'description' => 'Uganda national curriculum for secondary education, covering both O-Level and A-Level.',
                'enabled' => true,
            ];
        }

        $facilities = Facility::query()->orderBy('category')->orderBy('name')->get()->map(fn (Facility $item) => [
            'id' => (string) $item->id,
            'name' => $item->name,
            'slug' => $item->slug,
            'category' => $item->category,
            'icon' => $item->icon,
            'enabled' => (bool) $item->is_active,
        ])->values()->all();

        return [
            'school_types' => $schoolTypes,
            'curricula' => $curricula,
            'facilities' => $facilities,
        ];
    }

    private function defaultRegistrationOptions(): array
    {
        $savedStatic = $this->settingArray('school_registration_choices', []);
        $static = array_replace($this->defaultStaticChoices(), $savedStatic);

        return array_merge($this->defaultReferenceOptions(), $static);
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

    private function normalizeStaticList(mixed $items, string $key): array
    {
        if (! is_array($items) || count($items) > 100) abort(422, 'Each registration choice list can contain up to 100 entries.');

        $normalized = [];
        $seenValues = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) abort(422, 'A registration choice is not valid.');
            $id = trim((string) ($item['id'] ?? ($key.'_'.($index + 1))));
            $value = trim((string) ($item['value'] ?? ''));
            $label = trim((string) ($item['label'] ?? $value));
            if ($id === '' || mb_strlen($id) > 100 || $value === '' || mb_strlen($value) > 120 || $label === '' || mb_strlen($label) > 120) {
                abort(422, 'Each registration choice needs a value and a label of up to 120 characters.');
            }
            $fingerprint = mb_strtolower($value);
            if (isset($seenValues[$fingerprint])) abort(422, 'Values in a registration choice list must be unique.');
            $seenValues[$fingerprint] = true;
            $normalized[] = [
                'id' => $id,
                'value' => $value,
                'label' => $label,
                'enabled' => (bool) ($item['enabled'] ?? true),
            ];
        }

        if (! collect($normalized)->contains(fn ($item) => $item['enabled'])) {
            abort(422, 'Keep at least one active choice in each registration dropdown.');
        }

        return $normalized;
    }

    private function normalizeReferenceList(mixed $items, string $key): array
    {
        if (! is_array($items) || count($items) > 200) abort(422, 'Each registration reference list can contain up to 200 entries.');

        $modelClass = match ($key) {
            'school_types' => SchoolType::class,
            'curricula' => Curriculum::class,
            'facilities' => Facility::class,
        };

        $normalized = [];
        $seenNames = [];
        $seenIds = [];
        foreach ($items as $index => $item) {
            if (! is_array($item)) abort(422, 'A registration reference entry is not valid.');
            $id = trim((string) ($item['id'] ?? ''));
            $name = trim((string) ($item['name'] ?? ''));
            $slug = Str::slug((string) ($item['slug'] ?? $name));
            if ($id === '' || mb_strlen($id) > 100 || $name === '' || mb_strlen($name) > 120 || $slug === '') {
                abort(422, 'Each registration entry needs a valid name.');
            }

            $fingerprint = mb_strtolower($name);
            if (isset($seenNames[$fingerprint])) abort(422, 'Names in a registration list must be unique.');
            $seenNames[$fingerprint] = true;

            // Resolve newly added/draft rows against the database before they
            // can be published. Existing records are reused instead of copied.
            $existing = $modelClass::query()
                ->whereRaw('LOWER(BTRIM(name)) = ?', [$fingerprint])
                ->first();

            if ($existing && ctype_digit($id) && (int) $existing->id !== (int) $id) {
                abort(422, 'Names in a registration list must be unique.');
            }

            if ($existing && ! ctype_digit($id)) {
                $id = (string) $existing->id;
                $slug = $existing->slug;
            }

            if (isset($seenIds[$id])) abort(422, 'Each registration entry must refer to a different record.');
            $seenIds[$id] = true;

            $entry = [
                'id' => $id,
                'name' => $name,
                'slug' => $slug,
                'description' => mb_substr(trim((string) ($item['description'] ?? '')), 0, 1000),
                'icon' => mb_substr(trim((string) ($item['icon'] ?? '')), 0, 80),
                'enabled' => (bool) ($item['enabled'] ?? true),
                'sort_order' => (int) ($item['sort_order'] ?? ($index * 10)),
            ];
            if ($key === 'curricula') {
                $abbreviation = trim((string) ($item['abbreviation'] ?? ''));
                if ($abbreviation === '') $abbreviation = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 10));
                $entry['abbreviation'] = mb_substr($abbreviation, 0, 30);
            }
            if ($key === 'facilities') {
                $entry['category'] = mb_substr(trim((string) ($item['category'] ?? 'other')) ?: 'other', 0, 80);
            }
            $normalized[] = $entry;
        }

        if ($key !== 'facilities' && ! collect($normalized)->contains(fn ($item) => $item['enabled'])) {
            abort(422, 'Keep at least one active entry in this registration list.');
        }

        return $normalized;
    }

    private function normalizeRegistrationOptions(mixed $input): array
    {
        if (! is_array($input) || $input === []) return $this->defaultRegistrationOptions();

        $defaults = $this->defaultRegistrationOptions();
        $normalized = [];
        foreach (self::REFERENCE_GROUPS as $key) {
            $normalized[$key] = $this->normalizeReferenceList($input[$key] ?? $defaults[$key] ?? [], $key);
        }
        foreach (self::STATIC_GROUPS as $key) {
            $normalized[$key] = $this->normalizeStaticList($input[$key] ?? $defaults[$key] ?? [], $key);
        }

        return $normalized;
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

        return [
            'version' => 2,
            'fields' => $normalizedFields,
            'option_lists' => array_values($normalizedLists),
            'registration_options' => $this->normalizeRegistrationOptions($input['registration_options'] ?? null),
        ];
    }

    private function publishReferenceOptions(array $options): array
    {
        foreach (self::REFERENCE_GROUPS as $kind) {
            $saved = [];
            foreach ($options[$kind] ?? [] as $item) {
                $id = (string) $item['id'];
                $attributes = [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'is_active' => (bool) $item['enabled'],
                ];

                if ($kind === 'school_types') {
                    $attributes += [
                        'description' => $item['description'] ?: null,
                        'icon' => $item['icon'] ?: null,
                        'order' => (int) $item['sort_order'],
                    ];
                    $model = ctype_digit($id) ? SchoolType::query()->find((int) $id) : null;
                    if (! $model) $model = SchoolType::query()->whereRaw('LOWER(BTRIM(name)) = ?', [mb_strtolower(trim($item['name']))])->first();
                    if (! $model) $model = new SchoolType();
                } elseif ($kind === 'curricula') {
                    $attributes += [
                        'abbreviation' => $item['abbreviation'],
                        'description' => $item['description'] ?: null,
                    ];
                    $model = ctype_digit($id) ? Curriculum::query()->find((int) $id) : null;
                    if (! $model) $model = Curriculum::query()->whereRaw('LOWER(BTRIM(name)) = ?', [mb_strtolower(trim($item['name']))])->first();
                    if (! $model) $model = new Curriculum();
                } else {
                    $attributes += [
                        'category' => $item['category'] ?: 'other',
                        'icon' => $item['icon'] ?: null,
                    ];
                    $model = ctype_digit($id) ? Facility::query()->find((int) $id) : null;
                    if (! $model) $model = Facility::query()->whereRaw('LOWER(BTRIM(name)) = ?', [mb_strtolower(trim($item['name']))])->first();
                    if (! $model) $model = new Facility();
                }

                $model->fill($attributes);
                $model->save();
                $item['id'] = (string) $model->id;
                $item['slug'] = $model->slug;
                $saved[] = $item;
            }
            $options[$kind] = $saved;
        }

        return $options;
    }

    public function index(Request $request): JsonResponse
    {
        $permissions = $this->permissions($request);
        abort_unless($permissions['can_view'], 403, 'You do not have permission to view form settings.');

        $storedPublished = Setting::get('school_forms_published');
        if (is_string($storedPublished)) {
            $decodedPublished = json_decode($storedPublished, true);
            $storedPublished = is_array($decodedPublished) ? $decodedPublished : [];
        }
        $storedPublished = is_array($storedPublished) ? $storedPublished : [];
        $hasPublishedRegistrationOptions = array_key_exists('registration_options', $storedPublished);

        $published = $this->normalizeConfig($storedPublished ?: $this->emptyConfig());
        $draft = $this->normalizeConfig($this->settingArray('school_forms_draft', $published));

        // Before the new manager is first published, show the actual published
        // state as empty. This keeps the generated defaults in the draft and
        // makes the first Publish action available to apply them to registration.
        if (! $hasPublishedRegistrationOptions) {
            $published['registration_options'] = [
                'school_types' => [],
                'curricula' => [],
                'facilities' => [],
                'school_roles' => [],
                'ownerships' => [],
                'gender_policies' => [],
                'boarding_types' => [],
                'religious_affiliations' => [],
                'referral_sources' => [],
            ];
        }

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
        $currentDraft = $this->normalizeConfig($this->settingArray('school_forms_draft', $this->settingArray('school_forms_published', $this->emptyConfig())));
        $published = $this->normalizeConfig($this->settingArray('school_forms_published', $this->emptyConfig()));

        $publishedKeys = collect($published['fields'])->keyBy('id');
        foreach ($config['fields'] as $field) {
            $existingPublished = $publishedKeys->get($field['id']);
            if ($existingPublished && $existingPublished['key'] !== $field['key']) {
                abort(422, 'A field key cannot be changed after the field has been published. This protects existing school answers.');
            }
        }

        if (! $permissions['can_edit_fields'] && $config['fields'] !== $currentDraft['fields']) {
            abort(403, 'Your role can manage registration choices but cannot edit custom fields.');
        }
        if (! $permissions['can_manage_options'] && (
            $config['option_lists'] !== $currentDraft['option_lists'] ||
            $config['registration_options'] !== $currentDraft['registration_options']
        )) {
            abort(403, 'Your role cannot manage registration choices or option lists.');
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
        $draft = $this->normalizeConfig($this->settingArray('school_forms_draft', $this->settingArray('school_forms_published', $this->emptyConfig())));
        $history = $this->settingArray('school_forms_history', []);
        $referencesSummary = count($draft['registration_options']['school_types']).' school levels, '
            .count($draft['registration_options']['curricula']).' curricula and '
            .count($draft['registration_options']['facilities']).' facilities';

        DB::transaction(function () use (&$draft, &$history, $request, $referencesSummary) {
            $draft['registration_options'] = $this->publishReferenceOptions($draft['registration_options']);

            $staticChoices = [];
            foreach (self::STATIC_GROUPS as $key) {
                $staticChoices[$key] = $draft['registration_options'][$key];
            }

            Setting::query()->updateOrCreate(['key' => 'school_registration_choices'], [
                'value' => json_encode($staticChoices, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'forms',
                'label' => 'Published school registration choices',
            ]);

            array_unshift($history, [
                'message' => count($draft['fields']).' custom fields and registration choices published · '.$referencesSummary,
                'by' => (string) ($request->user()?->name ?? 'Administrator'),
                'at' => now()->toIso8601String(),
            ]);
            $history = array_slice($history, 0, 10);

            foreach (['school_forms_published' => 'Published school forms', 'school_forms_draft' => 'School form draft'] as $key => $label) {
                Setting::query()->updateOrCreate(['key' => $key], [
                    'value' => json_encode($draft, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                    'type' => 'json',
                    'group' => 'forms',
                    'label' => $label,
                ]);
            }
            Setting::query()->updateOrCreate(['key' => 'school_forms_history'], [
                'value' => json_encode($history, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'type' => 'json',
                'group' => 'forms',
                'label' => 'School forms change history',
            ]);
        });

        return response()->json(['data' => ['published' => $draft, 'draft' => $draft, 'history' => $history, 'message' => 'Form settings published.']]);
    }
}
