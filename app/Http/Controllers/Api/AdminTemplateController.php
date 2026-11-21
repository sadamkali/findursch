<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminTemplateController extends Controller
{
    private function permissionRows(): array
    {
        return [
            ['Schools', 'View schools', 'admin.schools.view', [1, 1, 1, 1]],
            ['Schools', 'Edit any school', 'admin.schools.edit_any', [1, 1, 0, 0]],
            ['Schools', 'Approve or verify schools', 'admin.schools.approve', [1, 1, 0, 0]],
            ['Schools', 'Feature schools on the homepage', 'admin.schools.feature', [1, 1, 1, 0]],
            ['Schools', 'Suspend a school', 'admin.schools.suspend', [1, 1, 0, 0]],
            ['Schools', 'Delete a school', 'admin.schools.delete', [1, 0, 0, 0]],
            ['Locations', 'Manage regions, districts and towns or areas', 'admin.locations.manage', [1, 1, 0, 0]],
            ['Reviews', 'Moderate reviews', 'admin.reviews.moderate', [1, 1, 1, 1]],
            ['Content', 'Publish articles and blogs', 'admin.content.publish', [1, 1, 1, 0]],
            ['Content', 'Approve submitted stories', 'admin.content.approve', [1, 1, 1, 0]],
            ['Content', 'Manage events', 'admin.content.events', [1, 1, 1, 0]],
            ['Content', 'Manage jobs and scholarships', 'admin.content.scholarships', [1, 1, 1, 0]],
            ['Content', 'Manage videos', 'admin.content.videos', [1, 1, 1, 0]],
            ['Ads and messaging', 'Manage ads', 'admin.ads.manage', [1, 1, 0, 0]],
            ['Ads and messaging', 'View message campaigns', 'admin.messages.view', [1, 1, 0, 1]],
            ['Ads and messaging', "Block or limit a school's messaging", 'admin.messages.restrict', [1, 1, 0, 0]],
            ['Enquiries and inbox', 'View enquiries (audit)', 'admin.enquiries.view', [1, 1, 0, 1]],
            ['Enquiries and inbox', 'Reply to the contact inbox', 'admin.enquiries.reply', [1, 1, 0, 1]],
            ['Billing', 'View payments', 'admin.billing.view_payments', [1, 0, 0, 0]],
            ['Billing', "Change a school's plan", 'admin.billing.change_plan', [1, 0, 0, 0]],
            ['Billing', 'Refund payments', 'admin.billing.refund_payments', [1, 0, 0, 0]],
            ['Billing', 'Edit plans and prices', 'admin.billing.edit_plans', [1, 0, 0, 0]],
            ['Users and roles', 'View admin users', 'admin.users.view', [1, 1, 0, 0]],
            ['Users and roles', 'Create or remove admins', 'admin.users.manage', [1, 0, 0, 0]],
            ['Users and roles', 'Edit roles and privileges', 'admin.roles.edit', [1, 0, 0, 0]],
            ['System', 'Edit site settings', 'admin.system.edit_site_settings', [1, 0, 0, 0]],
            ['System', 'View audit log', 'admin.system.view_audit_log', [1, 1, 0, 0]],
            ['System', 'Export data', 'admin.system.export_data', [1, 0, 0, 0]],
        ];
    }

    private function ensureRolesAndPermissions(): void
    {
        $rows = $this->permissionRows();
        foreach ($rows as $row) {
            Permission::findOrCreate($row[2], 'web');
        }
        Permission::findOrCreate('admin.workspace.access', 'web');

        $defaults = [
            'admin' => 0,
            'administrator' => 1,
            'editor' => 2,
            'support' => 3,
        ];

        foreach ($defaults as $roleName => $column) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            if ($roleName === 'admin' || ! $role->wasRecentlyCreated) {
                continue;
            }

            $keys = collect($rows)
                ->filter(fn ($row) => (int) $row[3][$column] === 1)
                ->pluck(2)
                ->all();
            $role->syncPermissions([...$keys, 'admin.workspace.access']);
        }
    }

    private function requirePermission(Request $request, string $permission): void
    {
        $user = $request->user();
        abort_unless($user && ($user->hasRole('admin') || $user->can($permission)), 403, 'You do not have permission to make this change.');
    }

    private function requireOwner(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403, 'Only the FindurSch owner administrator can manage roles and administrator accounts.');
    }

    private function settingDefaults(): array
    {
        return [
            // Public website identity and brand.
            'site_name' => 'FindURSCH',
            'site_tagline' => "Uganda's School Discovery Platform",
            'site_description' => 'Discover, compare and learn about schools across Uganda with FindurSch.',
            'site_logo' => '',
            'site_horizontal_logo' => '',
            'site_homepage_logo' => '',
            'site_footer_logo' => '',
            'site_mobile_logo' => '',
            'site_favicon' => '',
            'site_apple_touch_icon' => '',
            'site_social_image' => '',
            // Dashboard-only brand assets. Defaults reuse the current approved R2 brand artwork.
            'dashboard_logo' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/04e4be58-8896-4389-bdf5-41df98dc4390.png',
            'dashboard_login_logo' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_horizontal_logo/04e4be58-8896-4389-bdf5-41df98dc4390.png',
            'dashboard_mark' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_mobile_logo/dec9d1e6-f5db-41fa-bca4-3dba371e801f.png',
            'dashboard_favicon' => 'https://pub-6c31260540c84b1ba11ed7196e3b29b9.r2.dev/site-media/branding/site_favicon/b3a07092-d41b-44ba-9ba6-86243efcc20b.png',
            'primary_color' => '#0c5a49',
            'accent_color' => '#f0b030',
            'font_display' => 'Playfair Display',
            'font_body' => 'Inter',

            // Search and social previews.
            'meta_title' => 'FindURSCH: find the right school in Uganda',
            'meta_description' => 'Discover and compare schools across Uganda with FindurSch.',
            'meta_keywords' => '',

            // Public contact and social links.
            'support_email' => 'hello@findursch.ug',
            'support_phone' => '+256 700 000000',
            'support_whatsapp' => '',
            'contact_address' => 'Kampala, Uganda',
            'facebook_url' => '',
            'twitter_url' => '',
            'instagram_url' => '',
            'youtube_url' => '',

            // Homepage hero carousel. The image URL remains available for old templates.
            'homepage_hero_image_url' => '',
            'homepage_slides' => '[]',
            'site_navigation' => '[{"label":"Home","href":"/","enabled":true},{"label":"Schools","href":"/schools","enabled":true},{"label":"Compare","href":"/compare","enabled":true},{"label":"Scholarships","href":"/scholarships","enabled":true},{"label":"News","href":"/news","enabled":true},{"label":"Events","href":"/events","enabled":true},{"label":"Blogs","href":"/articles","enabled":true}]',

            // Header and footer structure.
            'site_topbar_enabled' => '1',
            'site_navigation_enabled' => '1',
            'site_account_actions_enabled' => '1',
            'site_header_cta_label' => 'List your school',
            'site_footer_enabled' => '1',
            'site_footer_explore_enabled' => '1',
            'site_footer_resources_enabled' => '1',
            'site_footer_support_enabled' => '1',
            'site_footer_social_enabled' => '1',
            'site_footer_promo_enabled' => '1',
            'site_footer_copyright_text' => '',

            // Homepage display controls.
            'homepage_register_tab_enabled' => '1',
            'homepage_search_enabled' => '1',
            'homepage_featured_schools_enabled' => '1',
            'homepage_sidebar_enabled' => '1',
            'homepage_categories_enabled' => '1',
            'homepage_upcoming_events_enabled' => '1',
            'homepage_news_enabled' => '1',
            'homepage_videos_enabled' => '1',
            'homepage_scholarships_enabled' => '1',
            'homepage_reviews_enabled' => '1',
            'homepage_insights_enabled' => '1',
            'homepage_banner_ads_enabled' => '1',
            'homepage_cta_enabled' => '1',

            // Publishing and moderation.
            'schools_require_approval' => '1',
            'reviews_require_approval' => '1',
            'maintenance_mode' => '0',

            // Administrator security.
            'require_two_step' => '0',
            'admin_signin_alerts' => '1',
            'block_new_country_signins' => '0',
            'admin_session_timeout_minutes' => '120',
            'admin_min_password_length' => '10',
        ];
    }

    private function booleanSettingKeys(): array
    {
        return [
            'homepage_register_tab_enabled', 'homepage_search_enabled',
            'homepage_featured_schools_enabled', 'homepage_sidebar_enabled',
            'homepage_categories_enabled', 'homepage_upcoming_events_enabled',
            'homepage_news_enabled', 'homepage_videos_enabled',
            'homepage_scholarships_enabled', 'homepage_reviews_enabled',
            'homepage_insights_enabled', 'homepage_banner_ads_enabled',
            'homepage_cta_enabled', 'site_topbar_enabled', 'site_navigation_enabled',
            'site_account_actions_enabled', 'site_footer_enabled', 'site_footer_explore_enabled',
            'site_footer_resources_enabled', 'site_footer_support_enabled',
            'site_footer_social_enabled', 'site_footer_promo_enabled', 'schools_require_approval',
            'reviews_require_approval', 'maintenance_mode', 'require_two_step',
            'admin_signin_alerts', 'block_new_country_signins',
        ];
    }

    private function jsonSettingKeys(): array
    {
        return ['homepage_slides', 'site_navigation'];
    }

    private function settingGroup(string $key): string
    {
        if (str_starts_with($key, 'admin_') || in_array($key, ['require_two_step', 'admin_signin_alerts', 'block_new_country_signins'], true)) return 'security';
        if (in_array($key, ['dashboard_logo', 'dashboard_login_logo', 'dashboard_mark', 'dashboard_favicon'], true)) return 'dashboard-branding';
        if ($key === 'site_navigation') return 'navigation';
        if (str_starts_with($key, 'site_footer_')) return 'footer';
        if (in_array($key, ['site_topbar_enabled', 'site_navigation_enabled', 'site_account_actions_enabled', 'site_header_cta_label'], true)) return 'header';
        if (str_starts_with($key, 'homepage_hero') || $key === 'homepage_slides') return 'homepage';
        if (str_starts_with($key, 'homepage_')) return 'display';
        if (in_array($key, ['primary_color', 'accent_color', 'font_display', 'font_body', 'site_logo', 'site_horizontal_logo', 'site_homepage_logo', 'site_footer_logo', 'site_mobile_logo', 'site_favicon', 'site_apple_touch_icon', 'site_social_image'], true)) return 'branding';
        if (in_array($key, ['meta_title', 'meta_description', 'meta_keywords'], true)) return 'seo';
        if (in_array($key, ['support_email', 'support_phone', 'support_whatsapp', 'contact_address'], true)) return 'contact';
        if (str_ends_with($key, '_url')) return 'social';
        if (in_array($key, ['schools_require_approval', 'reviews_require_approval', 'maintenance_mode'], true)) return 'publishing';
        return 'general';
    }

    /**
     * Publicly exposes only the non-sensitive dashboard logo URLs used before sign-in.
     * These assets are public images stored in Cloudflare R2.
     */
    public function publicDashboardBranding(): JsonResponse
    {
        $keys = ['dashboard_logo', 'dashboard_login_logo', 'dashboard_mark', 'dashboard_favicon', 'site_logo', 'site_horizontal_logo'];
        $defaults = $this->settingDefaults();
        $values = Setting::query()->whereIn('key', $keys)->pluck('value', 'key')->all();

        return response()->json([
            'data' => [
                'dashboard_logo' => $values['dashboard_logo'] ?? $defaults['dashboard_logo'],
                'dashboard_login_logo' => $values['dashboard_login_logo'] ?? $defaults['dashboard_login_logo'],
                'dashboard_mark' => $values['dashboard_mark'] ?? $defaults['dashboard_mark'],
                'dashboard_favicon' => $values['dashboard_favicon'] ?? $defaults['dashboard_favicon'],
                'site_logo' => $values['site_logo'] ?? $defaults['site_logo'],
                'site_horizontal_logo' => $values['site_horizontal_logo'] ?? $defaults['site_horizontal_logo'],
            ],
        ])->header('Cache-Control', 'public, max-age=60, stale-while-revalidate=300');
    }

    public function settings(Request $request): JsonResponse
    {
        $this->ensureRolesAndPermissions();
        $this->requirePermission($request, 'admin.system.edit_site_settings');

        $defaults = $this->settingDefaults();
        $booleanKeys = $this->booleanSettingKeys();
        $jsonKeys = $this->jsonSettingKeys();

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => in_array($key, $booleanKeys, true) ? 'boolean' : (in_array($key, $jsonKeys, true) ? 'json' : 'string'),
                    'group' => $this->settingGroup($key),
                    'label' => Str::headline($key),
                ]
            );
        }

        $values = Setting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        foreach ($booleanKeys as $key) {
            $values[$key] = filter_var($values[$key] ?? false, FILTER_VALIDATE_BOOLEAN);
        }
        foreach ($jsonKeys as $key) {
            $decoded = json_decode((string) ($values[$key] ?? '[]'), true);
            $values[$key] = is_array($decoded) ? $decoded : [];
        }

        return response()->json(['data' => ['settings' => $values]]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $this->requirePermission($request, 'admin.system.edit_site_settings');
        $data = $request->validate(['settings' => ['required', 'array']]);
        $defaults = $this->settingDefaults();
        $allowed = array_keys($defaults);
        $values = collect($data['settings'])->only($allowed);
        $booleanKeys = $this->booleanSettingKeys();
        $jsonKeys = $this->jsonSettingKeys();

        foreach ($values as $key => $value) {
            if (in_array($key, $booleanKeys, true)) {
                validator([$key => $value], [$key => ['required', 'boolean']])->validate();
            } elseif (in_array($key, $jsonKeys, true)) {
                if ($key === 'homepage_slides') {
                    validator(['slides' => $value], [
                        'slides' => ['present', 'array', 'max:8'],
                        'slides.*.image_url' => ['nullable', 'url', 'max:2048'],
                        'slides.*.title' => ['required', 'string', 'max:180'],
                        'slides.*.description' => ['nullable', 'string', 'max:1000'],
                        'slides.*.button_label' => ['nullable', 'string', 'max:80'],
                        'slides.*.button_url' => ['nullable', 'string', 'max:500'],
                        'slides.*.enabled' => ['sometimes', 'boolean'],
                    ])->validate();
                } else {
                    validator(['navigation' => $value], [
                        'navigation' => ['present', 'array', 'max:10'],
                        'navigation.*.label' => ['required', 'string', 'max:40'],
                        'navigation.*.href' => [
                            'required', 'string', 'max:255',
                            function ($attribute, $path, $fail) {
                                if (! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/[\r\n]/', $path)) {
                                    $fail('Navigation links must use an internal site path beginning with a single slash.');
                                }
                            },
                        ],
                        'navigation.*.enabled' => ['sometimes', 'boolean'],
                    ])->validate();
                }
            } else {
                $rules = match ($key) {
                    'site_name' => ['required', 'string', 'max:255'],
                    'site_header_cta_label' => ['nullable', 'string', 'max:60'],
                    'site_footer_copyright_text' => ['nullable', 'string', 'max:180'],
                    'site_tagline' => ['nullable', 'string', 'max:255'],
                    'site_description', 'meta_description' => ['nullable', 'string', 'max:1000'],
                    'meta_title' => ['nullable', 'string', 'max:255'],
                    'meta_keywords' => ['nullable', 'string', 'max:1000'],
                    'support_email' => ['nullable', 'email', 'max:255'],
                    'support_phone', 'support_whatsapp' => ['nullable', 'string', 'max:40'],
                    'contact_address' => ['nullable', 'string', 'max:255'],
                    'site_logo', 'site_horizontal_logo', 'site_homepage_logo', 'site_footer_logo',
                    'site_mobile_logo', 'site_favicon', 'site_apple_touch_icon', 'site_social_image',
                    'dashboard_logo', 'dashboard_login_logo', 'dashboard_mark', 'dashboard_favicon',
                    'homepage_hero_image_url',
                    'facebook_url', 'twitter_url', 'instagram_url', 'youtube_url' => ['nullable', 'url', 'max:2048'],
                    'primary_color', 'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                    'font_display' => ['required', 'in:Playfair Display,DM Serif Display'],
                    'font_body' => ['required', 'in:Inter,system-ui'],
                    'admin_session_timeout_minutes' => ['required', 'integer', 'in:30,120,480'],
                    'admin_min_password_length' => ['required', 'integer', 'in:8,10,12'],
                    default => ['nullable', 'string', 'max:2000'],
                };
                validator([$key => $value], [$key => $rules])->validate();
            }

            $stored = in_array($key, $booleanKeys, true)
                ? ($value ? '1' : '0')
                : (in_array($key, $jsonKeys, true)
                    ? json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                    : (string) ($value ?? ''));

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $stored,
                    'type' => in_array($key, $booleanKeys, true) ? 'boolean' : (in_array($key, $jsonKeys, true) ? 'json' : (in_array($key, ['admin_session_timeout_minutes', 'admin_min_password_length'], true) ? 'integer' : 'string')),
                    'group' => $this->settingGroup($key),
                    'label' => Str::headline($key),
                ]
            );
        }

        return response()->json(['message' => 'Settings saved.', 'data' => ['settings' => $values]]);
    }

    public function uploadMedia(Request $request): JsonResponse
    {
        $data = $request->validate([
            'scope' => ['required', 'in:site-media,advertisements,articles,school-posts'],
            'asset' => ['nullable', 'string', 'max:80'],
            'school_id' => ['required_if:scope,school-posts', 'nullable', 'integer', 'exists:schools,id'],
            'post_type' => ['required_if:scope,school-posts', 'nullable', 'in:event,news,bursary'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
        ]);

        $user = $request->user();
        if ($data['scope'] === 'site-media') {
            $this->requirePermission($request, 'admin.system.edit_site_settings');
            $assets = [
                'homepage_slide',
                'site_logo',
                'site_horizontal_logo',
                'site_homepage_logo',
                'site_footer_logo',
                'site_mobile_logo',
                'site_favicon',
                'site_apple_touch_icon',
                'site_social_image',
                'dashboard_logo',
                'dashboard_login_logo',
                'dashboard_mark',
                'dashboard_favicon',
            ];
            $asset = $data['asset'] ?? 'homepage_slide';
            abort_unless(in_array($asset, $assets, true), 422, 'Choose a valid brand image.');
            $directory = $asset === 'homepage_slide'
                ? 'site-media/homepage-carousel'
                : (str_starts_with($asset, 'dashboard_')
                    ? 'dashboard-media/branding/'.$asset
                    : 'site-media/branding/'.$asset);
        } elseif ($data['scope'] === 'advertisements') {
            abort_unless(
                $user && ($user->hasRole('admin') || $user->hasRole('school_owner') || $user->can('admin.ads.manage')),
                403,
                'You do not have permission to upload an advertisement image.'
            );
            $directory = 'advertisements/'.$user->getKey();
        } elseif ($data['scope'] === 'articles') {
            abort_unless(
                $user && ($user->hasRole('admin') || $user->can('admin.content.publish')),
                403,
                'You do not have permission to upload an article image.'
            );
            $directory = 'articles/'.$user->getKey();
        } else {
            abort_unless($user, 401);
            $school = School::query()->findOrFail((int) $data['school_id']);
            abort_unless(
                $user->hasRole('admin')
                    || $user->can('admin.content.publish')
                    || (int) $school->owner_id === (int) $user->getKey(),
                403,
                'You do not have permission to upload an image for this school.'
            );
            $directory = 'schools/'.$school->getKey().'/posts/'.$data['post_type'];
        }

        $storageDisk = (string) config('media-library.disk_name', 'filebase');
        abort_unless(in_array($storageDisk, ['filebase', 'r2'], true), 503, 'Media storage is not configured.');

        $disk = config('filesystems.disks.'.$storageDisk, []);
        $storageIsMissing = blank($disk['key'] ?? null)
            || blank($disk['secret'] ?? null)
            || blank($disk['bucket'] ?? null)
            || ($storageDisk === 'r2' && (blank($disk['endpoint'] ?? null) || blank($disk['url'] ?? null)));

        if ($storageIsMissing) {
            return response()->json([
                'message' => strtoupper($storageDisk).' storage is not fully configured. Check the storage credentials, bucket and public URL in the backend environment.',
            ], 503);
        }

        try {
            $file = $request->file('file');
            $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
            $filename = Str::uuid()->toString().'.'.$extension;
            $options = [
                'ContentType' => $file->getMimeType(),
                'CacheControl' => 'public, max-age=31536000',
            ];

            // Filebase supports S3 visibility headers. R2 public delivery is
            // configured at bucket/domain level and does not use object ACLs.
            if ($storageDisk === 'filebase') {
                $options['visibility'] = 'public';
            }

            $path = Storage::disk($storageDisk)->putFileAs(
                $directory,
                $file,
                $filename,
                $options
            );

            if (! $path) {
                throw new RuntimeException($storageDisk.' did not confirm the uploaded object.');
            }

            return response()->json([
                'url' => Storage::disk($storageDisk)->url($path),
                'pathname' => $path,
                'storage' => $storageDisk,
            ], 201);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'The selected media storage could not store this image. Check the storage credentials and bucket configuration, then try again.',
            ], 502);
        }
    }

    public function access(Request $request): JsonResponse
    {
        $this->ensureRolesAndPermissions();
        $this->requirePermission($request, 'admin.users.view');

        $rows = $this->permissionRows();
        $roles = Role::query()->whereNotIn('name', ['school_owner', 'parent', 'student'])->with('permissions:id,name')->orderBy('id')->get()->map(function (Role $role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'label' => match ($role->name) {
                    'admin' => 'Owner',
                    'administrator' => 'Administrator',
                    'editor' => 'Editor',
                    'support' => 'Support',
                    default => Str::headline($role->name),
                },
                'permissions' => $role->permissions->pluck('name')->values(),
                'locked' => $role->name === 'admin',
            ];
        });

        $excludedRoles = ['school_owner', 'parent', 'student'];
        $users = User::query()
            ->with('roles:id,name')
            ->whereHas('roles', fn ($query) => $query->whereNotIn('name', $excludedRoles))
            ->orderBy('created_at')
            ->get()
            ->map(function (User $user) {
                $role = $user->roles->first();
                $invitation = DB::table('admin_invitations')
                    ->where('user_id', $user->id)
                    ->whereNull('accepted_at')
                    ->latest('created_at')
                    ->first();

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_id' => $role?->id,
                    'role' => $role?->name,
                    'role_label' => match ($role?->name) {
                        'admin' => 'Owner',
                        'administrator' => 'Administrator',
                        'editor' => 'Editor',
                        'support' => 'Support',
                        default => $role ? Str::headline($role->name) : '—',
                    },
                    'status' => $invitation ? 'Invited' : ($user->status === 'suspended' ? 'Suspended' : ($user->status === 'active' ? 'Active' : 'Inactive')),
                    'last_active' => $user->last_login_at?->toIso8601String() ?? null,
                    'pending_country' => DB::table('admin_login_countries')->where('user_id', $user->id)->whereNull('approved_at')->orderByDesc('first_seen_at')->value('country_code'),
                    'owner' => $role?->name === 'admin',
                ];
            })->values();

        return response()->json([
            'data' => [
                'roles' => $roles,
                'users' => $users,
                'permissions' => collect($rows)->map(fn ($row) => ['group' => $row[0], 'label' => $row[1], 'key' => $row[2]])->values(),
            ],
        ]);
    }

    public function createRole(Request $request): JsonResponse
    {
        $this->requireOwner($request);
        $data = $request->validate(['name' => ['required', 'string', 'min:2', 'max:30']]);
        $name = Str::slug(trim($data['name']), '_');
        abort_if($name === '' || in_array($name, ['admin', 'administrator', 'editor', 'support', 'school_owner', 'parent', 'student'], true), 422, 'Choose a different role name.');
        abort_if(Role::query()->where('name', $name)->where('guard_name', 'web')->exists(), 422, 'That role already exists.');

        $role = Role::create(['name' => $name, 'guard_name' => 'web']);
        return response()->json(['data' => ['id' => $role->id, 'name' => $role->name, 'label' => Str::headline($role->name), 'permissions' => [], 'locked' => false]], 201);
    }

    public function updateRole(Request $request, int $roleId): JsonResponse
    {
        $this->requireOwner($request);
        $this->ensureRolesAndPermissions();
        $role = Role::query()->findOrFail($roleId);
        abort_if($role->name === 'admin', 403, 'The Owner role cannot be edited.');
        $allowed = collect($this->permissionRows())->pluck(2)->all();
        $data = $request->validate([
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'distinct', 'in:'.implode(',', $allowed)],
        ]);
        $permissions = $data['permissions'];
        if ($permissions !== []) {
            $permissions[] = 'admin.workspace.access';
        }
        $role->syncPermissions($permissions);
        return response()->json(['message' => 'Role privileges saved.', 'data' => ['id' => $role->id, 'permissions' => $role->permissions()->pluck('name')]]);
    }

    public function resetRoles(Request $request): JsonResponse
    {
        $this->requireOwner($request);
        $this->ensureRolesAndPermissions();
        foreach (['administrator' => 1, 'editor' => 2, 'support' => 3] as $roleName => $column) {
            $keys = collect($this->permissionRows())
                ->filter(fn ($row) => (int) $row[3][$column] === 1)
                ->pluck(2)
                ->all();
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions([...$keys, 'admin.workspace.access']);
        }
        return response()->json(['message' => 'Default privileges restored.']);
    }

    public function inviteAdmin(Request $request): JsonResponse
    {
        $this->requireOwner($request);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);
        $email = Str::lower(trim($data['email']));
        $role = Role::query()->findOrFail($data['role_id']);
        abort_if($role->name === 'admin' || in_array($role->name, ['school_owner', 'parent', 'student'], true), 422, 'Choose an administrator role.');
        abort_unless($role->permissions()->where('name', 'like', 'admin.%')->exists(), 422, 'Assign at least one administrator privilege to this role before sending an invitation.');
        abort_if(User::query()->whereRaw('LOWER(email) = ?', [$email])->exists(), 422, 'An account already uses this email address.');

        $token = Str::random(64);
        $user = User::query()->create([
            'name' => Str::before($email, '@'),
            'email' => $email,
            'password' => Hash::make(Str::random(64)),
        ]);
        $user->forceFill(['user_type' => 'admin', 'status' => 'inactive'])->save();
        $user->syncRoles([$role]);

        DB::table('admin_invitations')->insert([
            'user_id' => $user->id,
            'email' => $email,
            'role_id' => $role->id,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
            'accepted_at' => null,
            'invited_by' => $request->user()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $dashboardUrl = rtrim((string) env('DASHBOARD_URL', 'https://findursch-dashboard.vercel.app'), '/');
        $inviteUrl = $dashboardUrl.'/accept-invite?email='.urlencode($email).'&token='.urlencode($token);

        try {
            Mail::raw(
                "You have been invited to manage FindurSch.\n\nOpen this secure link within 7 days to activate your administrator account:\n".$inviteUrl."\n\nIf you were not expecting this invitation, you can ignore this email.",
                fn ($message) => $message->to($email)->subject('Your FindurSch administrator invitation')
            );
        } catch (\Throwable $exception) {
            DB::table('admin_invitations')->where('user_id', $user->id)->delete();
            $user->delete();
            report($exception);
            return response()->json(['message' => 'The invitation could not be emailed. Check the backend mail configuration and try again.'], 503);
        }

        return response()->json(['message' => 'Administrator invitation sent.', 'data' => ['id' => $user->id, 'email' => $email, 'status' => 'Invited']], 201);
    }

    public function updateAdmin(Request $request, int $userId): JsonResponse
    {
        $this->requireOwner($request);
        $user = User::query()->with('roles')->findOrFail($userId);
        abort_if($user->hasRole('admin') || $user->is($request->user()), 403, 'The Owner account and your own account cannot be changed here.');

        $data = $request->validate([
            'role_id' => ['sometimes', 'integer', 'exists:roles,id'],
            'status' => ['sometimes', 'in:active,suspended'],
        ]);
        abort_if($data === [], 422, 'No administrator changes were supplied.');

        if (isset($data['role_id'])) {
            $role = Role::query()->findOrFail($data['role_id']);
            abort_if(in_array($role->name, ['admin', 'school_owner', 'parent', 'student'], true), 422, 'Choose a staff role.');
            $user->syncRoles([$role]);
            $user->forceFill(['user_type' => 'admin'])->save();
        }
        if (isset($data['status'])) {
            $user->forceFill(['status' => $data['status']])->save();
            if ($data['status'] !== 'active') {
                $user->tokens()->delete();
            }
        }

        return response()->json(['message' => 'Administrator updated.']);
    }

    public function deleteAdmin(Request $request, int $userId): JsonResponse
    {
        $this->requireOwner($request);
        $user = User::query()->findOrFail($userId);
        abort_if($user->hasRole('admin') || $user->is($request->user()), 403, 'The Owner account and your own account cannot be removed.');
        $user->tokens()->delete();
        DB::table('admin_invitations')->where('user_id', $user->id)->delete();
        $user->delete();
        return response()->json(['message' => 'Administrator removed.']);
    }


    public function approveCountry(Request $request, int $userId, string $country): JsonResponse
    {
        $this->requireOwner($request);
        $countryCode = strtoupper($country);
        abort_unless(preg_match('/^[A-Z]{2}$/', $countryCode), 422, 'The country code is invalid.');
        $target = User::query()->findOrFail($userId);
        abort_if($target->hasRole('admin'), 422, 'Owner administrator accounts do not need country approval.');

        $updated = DB::table('admin_login_countries')
            ->where('user_id', $target->id)
            ->where('country_code', $countryCode)
            ->whereNull('approved_at')
            ->update([
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
                'updated_at' => now(),
            ]);
        abort_unless($updated > 0, 404, 'No pending sign-in from this country was found.');

        return response()->json(['message' => 'Sign-ins from '.$countryCode.' are now approved for this administrator.']);
    }

    public function signOutOthers(Request $request): JsonResponse
    {
        $this->requireOwner($request);
        $staffIds = User::query()
            ->whereHas('roles', fn ($query) => $query->whereNotIn('name', ['school_owner', 'parent', 'student']))
            ->where('id', '!=', $request->user()->id)
            ->pluck('id');
        DB::table('personal_access_tokens')
            ->where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $staffIds)
            ->delete();
        return response()->json(['message' => 'All other administrator sessions were signed out.']);
    }

    public function acceptInvitation(Request $request): JsonResponse
    {
        $minimum = max(8, (int) Setting::get('admin_min_password_length', 10));
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'token' => ['required', 'string', 'size:64'],
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'password' => ['required', 'string', 'min:'.$minimum, 'confirmed'],
        ]);
        $email = Str::lower(trim($data['email']));
        $invitation = DB::table('admin_invitations')
            ->where('email', $email)
            ->whereNull('accepted_at')
            ->latest('created_at')
            ->first();

        abort_unless(
            $invitation && now()->lessThan($invitation->expires_at) && hash_equals($invitation->token_hash, hash('sha256', $data['token'])),
            422,
            'This invitation is invalid or has expired.'
        );

        $user = User::query()->findOrFail($invitation->user_id);
        $user->forceFill([
            'name' => trim($data['name']),
            'password' => Hash::make($data['password']),
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();
        DB::table('admin_invitations')->where('id', $invitation->id)->update(['accepted_at' => now(), 'updated_at' => now()]);

        return response()->json(['message' => 'Your administrator account is active. Sign in to continue.']);
    }
}
