# FindURSCH — School Discovery Platform Build Documentation

**Generated:** 2026-06-07

> Comprehensive documentation of FindURSCH — Uganda's school discovery, comparison, and admissions platform. Built on Laravel 12 + Filament 4 + Livewire 3, modelled structurally after International Schools Database and localised for Uganda, with an East Africa expansion path.

---

## Table of Contents

1. [Tech Stack](#1-tech-stack)
2. [Environment Variables](#2-environment-variables)
3. [Database Schema](#3-database-schema)
4. [Models & Relationships](#4-models--relationships)
5. [Routes Map — Public Frontend](#5-routes-map--public-frontend)
6. [Routes Map — School Owner Portal](#6-routes-map--school-owner-portal)
7. [Filament Admin Panel — Resources & Pages](#7-filament-admin-panel--resources--pages)
8. [Livewire Pages — Build Logic](#8-livewire-pages--build-logic)
9. [Design System](#9-design-system)
10. [Alpine.js & Livewire Frontend Architecture](#10-alpinejs--livewire-frontend-architecture)
11. [Seeders Reference](#11-seeders-reference)
12. [Console Commands & Scheduling](#12-console-commands--scheduling)
13. [Services Layer](#13-services-layer)
14. [Known Gaps & TODOs](#14-known-gaps--todos)

---

## 1. Tech Stack

### Backend (composer.json)

| Package | Version | Purpose |
|---------|---------|---------|
| `php` | ^8.2 | PHP runtime |
| `laravel/framework` | ^12.0 | Core Laravel framework |
| `filament/filament` | ^4.0 | Admin panel + school owner portal (multi-panel) |
| `livewire/livewire` | ^3.0 | Reactive frontend components |
| `spatie/laravel-permission` | ^6.0 | RBAC — admin / school owner / parent / student |
| `laravel/scout` | ^10.0 | Full-text search abstraction layer |
| `meilisearch/meilisearch-php` | ^1.0 | Meilisearch PHP client |
| `spatie/laravel-medialibrary` | ^11.0 | School gallery, cover images, logos |
| `spatie/laravel-sluggable` | ^3.0 | Auto-slug generation for schools, articles |
| `spatie/laravel-activitylog` | ^4.0 | Admin and owner activity tracking |
| `spatie/laravel-sitemap` | ^7.0 | Auto-generate sitemap for SEO |
| `intervention/image` | ^3.0 | Image resizing and optimisation |
| `laravel/sanctum` | ^4.0 | API token auth for future Flutter mobile app |
| `laravel/horizon` | ^5.0 | Queue worker monitoring dashboard |
| `maatwebsite/excel` | ^3.1 | Export schools / inquiries to CSV/XLSX |
| `barryvdh/laravel-dompdf` | ^3.0 | Generate PDF school reports for owners |
| `laravel/tinker` | ^2.10 | Interactive REPL |
| `fakerphp/faker` | ^1.23 (dev) | Fake data for seeders |
| `laravel/pail` | ^1.2 (dev) | Log viewer |
| `laravel/pint` | ^1.24 (dev) | PHP code style fixer |
| `laravel/sail` | ^1.41 (dev) | Docker dev environment |
| `pestphp/pest` | ^3.8 (dev) | Testing framework |
| `pestphp/pest-plugin-laravel` | ^3.2 (dev) | Pest Laravel integration |
| `mockery/mockery` | ^1.6 (dev) | Mocking framework |

### Frontend (package.json)

| Package | Version | Purpose |
|---------|---------|---------|
| `tailwindcss` | ^4.1 | Utility-first CSS framework |
| `@tailwindcss/vite` | ^4.1 | Tailwind CSS v4 Vite plugin |
| `@tailwindcss/typography` | ^0.5 | Prose/article content styling |
| `@tailwindcss/forms` | ^0.5 | Form element base styles |
| `vite` | ^6.0 | Build tool and dev server |
| `laravel-vite-plugin` | ^2.0 | Laravel Vite integration |
| `alpinejs` | ^3.14 | Lightweight reactivity (Livewire companion) |
| `@alpinejs/anchor` | ^3.14 | Alpine anchor plugin (dropdowns) |
| `@alpinejs/collapse` | ^3.14 | Alpine collapse plugin (accordions) |
| `@alpinejs/focus` | ^3.14 | Alpine focus trap plugin (modals) |
| `@alpinejs/intersect` | ^3.14 | Alpine intersection observer plugin |
| `lucide` | ^0.383 | Icon library (thin, clean, consistent) |
| `chart.js` | ^4.4 | Analytics charts for owner dashboard |
| `tom-select` | ^2.3 | Enhanced searchable select dropdowns |
| `filepond` | ^4.31 | School gallery and image upload |
| `@filepond/plugin-image-preview` | ^4.6 | Image preview in upload widget |
| `@filepond/plugin-file-validate-type` | ^1.2 | Upload file type validation |
| `@filepond/plugin-image-resize` | ^2.0 | Client-side image resize before upload |
| `leaflet` | ^1.9 | Map for school locations (lighter than Google) |
| `dayjs` | ^1.11 | Date formatting |
| `lodash` | ^4.17 | General utility functions |
| `prettier` | ^3.4 (dev) | Code formatter |
| `prettier-plugin-tailwindcss` | ^0.6 (dev) | Tailwind class ordering |
| `concurrently` | ^9.0 | Run multiple dev processes |

---

## 2. Environment Variables

| Key | Default | What it configures |
|-----|---------|-------------------|
| `APP_NAME` | `FindURSCH` | Application name shown in browser title and emails |
| `APP_ENV` | `local` | Environment (local / staging / production) |
| `APP_KEY` | — | Application encryption key |
| `APP_DEBUG` | `true` | Debug mode (false in production) |
| `APP_URL` | `http://localhost` | Application base URL |
| `APP_LOCALE` | `en` | Default locale |
| `APP_FALLBACK_LOCALE` | `en` | Fallback locale |
| `APP_FAKER_LOCALE` | `en_UG` | Faker locale — Uganda English |
| `APP_TIMEZONE` | `Africa/Kampala` | Application timezone (UTC+3) |
| `APP_MAINTENANCE_DRIVER` | `file` | Maintenance mode driver |
| `BCRYPT_ROUNDS` | `12` | Bcrypt hashing rounds |
| `LOG_CHANNEL` | `stack` | Log channel |
| `LOG_STACK` | `single` | Stack log channel |
| `LOG_LEVEL` | `debug` | Log level |
| `DB_CONNECTION` | `mysql` | Database connection |
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `findursch` | Database name |
| `DB_USERNAME` | `root` | Database username |
| `DB_PASSWORD` | — | Database password |
| `SESSION_DRIVER` | `database` | Session driver |
| `SESSION_LIFETIME` | `120` | Session lifetime (minutes) |
| `CACHE_STORE` | `redis` | Cache driver — Redis for performance |
| `QUEUE_CONNECTION` | `redis` | Queue driver — Redis for jobs |
| `REDIS_HOST` | `127.0.0.1` | Redis host |
| `REDIS_PASSWORD` | `null` | Redis password |
| `REDIS_PORT` | `6379` | Redis port |
| `BROADCAST_CONNECTION` | `pusher` | Broadcast driver (for real-time notifications) |
| `PUSHER_APP_ID` | — | Pusher App ID |
| `PUSHER_APP_KEY` | — | Pusher App Key |
| `PUSHER_APP_SECRET` | — | Pusher App Secret |
| `PUSHER_APP_CLUSTER` | `ap2` | Pusher cluster (Asia-Pacific 2 closest to Uganda) |
| `FILESYSTEM_DISK` | `public` | Default filesystem disk |
| `AWS_ACCESS_KEY_ID` | — | AWS access key (for S3 media storage in production) |
| `AWS_SECRET_ACCESS_KEY` | — | AWS secret key |
| `AWS_DEFAULT_REGION` | `af-south-1` | AWS region (Africa) |
| `AWS_BUCKET` | `findursch-media` | AWS S3 bucket for school images |
| `SCOUT_DRIVER` | `meilisearch` | Laravel Scout driver |
| `MEILISEARCH_HOST` | `http://localhost:7700` | Meilisearch server host |
| `MEILISEARCH_KEY` | — | Meilisearch master key |
| `GOOGLE_MAPS_API_KEY` | — | Google Maps API key (for geocoding and embeds) |
| `MAIL_MAILER` | `smtp` | Mail driver |
| `MAIL_HOST` | — | SMTP host |
| `MAIL_PORT` | `587` | SMTP port |
| `MAIL_USERNAME` | — | SMTP username |
| `MAIL_PASSWORD` | — | SMTP password |
| `MAIL_FROM_ADDRESS` | `hello@findursch.ug` | Mail from address |
| `MAIL_FROM_NAME` | `FindURSCH` | Mail from name |
| `FLUTTERWAVE_PUBLIC_KEY` | — | Flutterwave public key (Uganda mobile money / card) |
| `FLUTTERWAVE_SECRET_KEY` | — | Flutterwave secret key |
| `FLUTTERWAVE_ENCRYPTION_KEY` | — | Flutterwave encryption key |
| `HORIZON_SECRET` | — | Laravel Horizon auth secret |
| `VITE_APP_NAME` | `${APP_NAME}` | Vite app name (frontend) |
| `VITE_PUSHER_APP_KEY` | `${PUSHER_APP_KEY}` | Pusher key exposed to frontend |
| `VITE_GOOGLE_MAPS_KEY` | `${GOOGLE_MAPS_API_KEY}` | Maps key exposed to frontend |

---

## 3. Database Schema

### Table: `users`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (255) | NOT NULL |
| `email` | string (255) | NOT NULL, UNIQUE |
| `password` | string (255) | NOT NULL |
| `user_type` | enum('admin','school_owner','parent','student') | DEFAULT 'parent' |
| `status` | enum('active','inactive','suspended','banned') | DEFAULT 'active' |
| `profile_photo` | string (255) | NULLABLE |
| `phone` | string (30) | NULLABLE |
| `district_id` | bigInteger (unsigned) | NULLABLE, FK → districts.id |
| `email_verified_at` | timestamp | NULLABLE |
| `last_login_at` | timestamp | NULLABLE |
| `remember_token` | string (100) | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Indexes:** UNIQUE on `email`, FK on `district_id`

---

### Table: `regions`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (100) | NOT NULL |
| `slug` | string (120) | NOT NULL, UNIQUE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Seed data:** Central, Eastern, Western, Northern

---

### Table: `districts`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `region_id` | bigInteger (unsigned) | NOT NULL, FK → regions.id |
| `name` | string (120) | NOT NULL |
| `slug` | string (140) | NOT NULL, UNIQUE |
| `school_count` | integer | DEFAULT 0 (cached, updated by job) |
| `is_featured` | boolean | DEFAULT false |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `sub_counties`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `district_id` | bigInteger (unsigned) | NOT NULL, FK → districts.id |
| `name` | string (120) | NOT NULL |
| `slug` | string (140) | NOT NULL |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `school_types`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (100) | NOT NULL |
| `slug` | string (120) | NOT NULL, UNIQUE |
| `icon` | string (60) | NULLABLE (Lucide icon name) |
| `description` | text | NULLABLE |
| `order` | integer | DEFAULT 0 |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Seed data:** Nursery, Primary, Secondary, A-Level, University, Vocational, International

---

### Table: `curricula`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (120) | NOT NULL |
| `slug` | string (140) | NOT NULL, UNIQUE |
| `abbreviation` | string (30) | NOT NULL |
| `description` | text | NULLABLE |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Seed data:** UNEB (O-Level), UNEB (A-Level), Cambridge IGCSE, Cambridge A-Level, International Baccalaureate (IB), STEM Focus

---

### Table: `facilities`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (120) | NOT NULL |
| `slug` | string (140) | NOT NULL, UNIQUE |
| `icon` | string (60) | NULLABLE (Lucide icon name) |
| `category` | enum('academic','sports','welfare','technology','arts') | NOT NULL |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Seed data:** Library, Science Labs, Computer Lab, Sports Field, Swimming Pool, Dormitories, Chapel/Mosque, Cafeteria, WiFi Campus, Art Room, Music Room, Drama Hall, Sick Bay

---

### Table: `schools`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `owner_id` | bigInteger (unsigned) | NULLABLE, FK → users.id |
| `type_id` | bigInteger (unsigned) | NOT NULL, FK → school_types.id |
| `district_id` | bigInteger (unsigned) | NOT NULL, FK → districts.id |
| `sub_county_id` | bigInteger (unsigned) | NULLABLE, FK → sub_counties.id |
| `name` | string (255) | NOT NULL |
| `slug` | string (280) | NOT NULL, UNIQUE |
| `tagline` | string (255) | NULLABLE |
| `description` | text | NULLABLE |
| `founded_year` | year | NULLABLE |
| `registration_number` | string (60) | NULLABLE (MOE registration) |
| `latitude` | decimal (10,8) | NULLABLE |
| `longitude` | decimal (11,8) | NULLABLE |
| `address` | text | NULLABLE |
| `phone` | string (30) | NULLABLE |
| `email` | string (255) | NULLABLE |
| `website` | string (255) | NULLABLE |
| `whatsapp` | string (30) | NULLABLE |
| `boarding_type` | enum('boarding','day','mixed') | DEFAULT 'day' |
| `gender_policy` | enum('boys','girls','mixed') | DEFAULT 'mixed' |
| `student_count_approx` | integer | NULLABLE |
| `status` | enum('draft','pending','active','suspended','rejected') | DEFAULT 'draft' |
| `verification_status` | enum('unverified','pending','verified') | DEFAULT 'unverified' |
| `verified_at` | timestamp | NULLABLE |
| `verified_by` | bigInteger (unsigned) | NULLABLE, FK → users.id (admin who verified) |
| `is_featured` | boolean | DEFAULT false |
| `featured_until` | date | NULLABLE |
| `views_count` | integer | DEFAULT 0 (cached) |
| `inquiries_count` | integer | DEFAULT 0 (cached) |
| `rating_average` | decimal (3,2) | DEFAULT 0.00 (cached) |
| `reviews_count` | integer | DEFAULT 0 (cached) |
| `meta_title` | string (255) | NULLABLE |
| `meta_description` | text | NULLABLE |
| `rejection_reason` | text | NULLABLE |
| `submitted_at` | timestamp | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Indexes:** UNIQUE on `slug`, FK on `owner_id`, `type_id`, `district_id`, `sub_county_id`; composite index on `status` + `verification_status`; index on `is_featured`; index on `district_id` + `status`

---

### Table: `school_curriculum` (Pivot)

| Column | Type | Constraints |
|--------|------|-------------|
| `school_id` | bigInteger (unsigned) | FK → schools.id |
| `curriculum_id` | bigInteger (unsigned) | FK → curricula.id |

**Primary:** Composite (school_id, curriculum_id)

---

### Table: `school_programs`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `curriculum_id` | bigInteger (unsigned) | NULLABLE, FK → curricula.id |
| `name` | string (180) | NOT NULL |
| `level` | string (60) | NULLABLE (e.g., "P1–P7", "S1–S4", "S5–S6") |
| `duration` | string (60) | NULLABLE (e.g., "7 years", "4 years") |
| `description` | text | NULLABLE |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `school_fees`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `program_id` | bigInteger (unsigned) | NULLABLE, FK → school_programs.id |
| `category` | enum('tuition','boarding','uniform','meals','activities','other') | NOT NULL |
| `label` | string (180) | NOT NULL (human-readable label) |
| `amount` | decimal (12,2) | NOT NULL |
| `currency` | string (10) | DEFAULT 'UGX' |
| `period` | enum('per_term','per_year','once_off','per_month') | DEFAULT 'per_term' |
| `academic_year` | string (20) | NULLABLE (e.g., "2024/2025") |
| `notes` | text | NULLABLE |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `school_facilities` (Pivot)

| Column | Type | Constraints |
|--------|------|-------------|
| `school_id` | bigInteger (unsigned) | FK → schools.id |
| `facility_id` | bigInteger (unsigned) | FK → facilities.id |
| `notes` | string (255) | NULLABLE |

**Primary:** Composite (school_id, facility_id)

---

### Table: `school_contacts`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `type` | enum('phone','email','whatsapp','facebook','twitter','instagram','website','other') | NOT NULL |
| `value` | string (255) | NOT NULL |
| `label` | string (120) | NULLABLE (e.g., "Admissions Office") |
| `is_primary` | boolean | DEFAULT false |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `reviews`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `user_id` | bigInteger (unsigned) | NOT NULL, FK → users.id |
| `reviewer_type` | enum('parent','student','alumni','staff','other') | NOT NULL |
| `rating` | tinyInteger (unsigned) | NOT NULL (1–5) |
| `title` | string (255) | NOT NULL |
| `body` | text | NOT NULL |
| `academic_rating` | tinyInteger (unsigned) | NULLABLE (1–5 sub-rating) |
| `facilities_rating` | tinyInteger (unsigned) | NULLABLE |
| `staff_rating` | tinyInteger (unsigned) | NULLABLE |
| `environment_rating` | tinyInteger (unsigned) | NULLABLE |
| `status` | enum('pending','approved','rejected','flagged') | DEFAULT 'pending' |
| `is_verified` | boolean | DEFAULT false |
| `owner_response` | text | NULLABLE |
| `owner_responded_at` | timestamp | NULLABLE |
| `helpful_count` | integer | DEFAULT 0 |
| `moderated_by` | bigInteger (unsigned) | NULLABLE |
| `moderated_at` | timestamp | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Indexes:** FK on `school_id`, `user_id`; index on `status`; composite UNIQUE on `school_id` + `user_id` (one review per user per school)

---

### Table: `review_votes`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `review_id` | bigInteger (unsigned) | NOT NULL, FK → reviews.id |
| `user_id` | bigInteger (unsigned) | NOT NULL, FK → users.id |
| `vote` | enum('helpful','not_helpful') | NOT NULL |
| `created_at` | timestamp | NULLABLE |

**Unique:** Composite (review_id, user_id)

---

### Table: `inquiries`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `user_id` | bigInteger (unsigned) | NULLABLE, FK → users.id (null for guest inquiries) |
| `parent_name` | string (255) | NOT NULL |
| `parent_email` | string (255) | NOT NULL |
| `parent_phone` | string (30) | NULLABLE |
| `student_name` | string (255) | NULLABLE |
| `student_age` | tinyInteger | NULLABLE |
| `intended_level` | string (120) | NULLABLE (e.g., "S.1 Boarding 2025") |
| `message` | text | NOT NULL |
| `source` | enum('web','whatsapp','api','admin') | DEFAULT 'web' |
| `status` | enum('new','read','responded','archived') | DEFAULT 'new' |
| `owner_response` | text | NULLABLE |
| `responded_at` | timestamp | NULLABLE |
| `responded_by` | bigInteger (unsigned) | NULLABLE, FK → users.id |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Indexes:** FK on `school_id`, `user_id`; index on `status`

---

### Table: `saved_schools`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `user_id` | bigInteger (unsigned) | NOT NULL, FK → users.id |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `notes` | text | NULLABLE |
| `created_at` | timestamp | NULLABLE |

**Unique:** Composite (user_id, school_id)

---

### Table: `school_views`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `user_id` | bigInteger (unsigned) | NULLABLE |
| `ip_address` | string (45) | NULLABLE |
| `session_id` | string (100) | NULLABLE |
| `referrer` | string (500) | NULLABLE |
| `created_at` | timestamp | NOT NULL |

**Note:** No `updated_at` — append-only log. Prune records older than 90 days via scheduled command.

---

### Table: `plans`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (120) | NOT NULL |
| `slug` | string (140) | NOT NULL, UNIQUE |
| `tagline` | string (255) | NULLABLE |
| `description` | text | NULLABLE |
| `price` | decimal (12,2) | NOT NULL, DEFAULT 0.00 |
| `currency` | string (10) | DEFAULT 'UGX' |
| `duration_days` | integer | NOT NULL |
| `billing_cycle` | enum('monthly','yearly','once_off') | DEFAULT 'yearly' |
| `features` | json | NULLABLE |
| `includes_featured_slot` | boolean | DEFAULT false |
| `featured_slot_count` | tinyInteger | DEFAULT 0 |
| `is_active` | boolean | DEFAULT true |
| `order` | integer | DEFAULT 0 |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Seed data:** Free (0 UGX), Standard (500,000 UGX/yr), Premium (1,500,000 UGX/yr)

---

### Table: `subscriptions`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `plan_id` | bigInteger (unsigned) | NOT NULL, FK → plans.id |
| `start_date` | date | NOT NULL |
| `end_date` | date | NOT NULL |
| `status` | enum('active','expired','cancelled','pending') | DEFAULT 'pending' |
| `auto_renew` | boolean | DEFAULT false |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `payments`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `subscription_id` | bigInteger (unsigned) | NULLABLE, FK → subscriptions.id |
| `amount` | decimal (12,2) | NOT NULL |
| `currency` | string (10) | DEFAULT 'UGX' |
| `payment_method` | string (60) | NULLABLE (mobile_money, card, bank_transfer) |
| `transaction_id` | string (255) | NULLABLE, UNIQUE |
| `status` | enum('pending','completed','failed','refunded') | DEFAULT 'pending' |
| `gateway` | string (60) | NULLABLE (flutterwave, manual) |
| `gateway_response` | json | NULLABLE |
| `paid_at` | timestamp | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `featured_slots`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `school_id` | bigInteger (unsigned) | NOT NULL, FK → schools.id |
| `slot_type` | enum('homepage','category','district','search_top','article_sidebar') | NOT NULL |
| `context_type` | string (60) | NULLABLE (e.g., 'school_type', 'district') |
| `context_id` | bigInteger (unsigned) | NULLABLE |
| `starts_at` | timestamp | NOT NULL |
| `ends_at` | timestamp | NOT NULL |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `articles`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `author_id` | bigInteger (unsigned) | NULLABLE, FK → users.id |
| `category_id` | bigInteger (unsigned) | NULLABLE, FK → article_categories.id |
| `title` | string (255) | NOT NULL |
| `slug` | string (280) | NOT NULL, UNIQUE |
| `excerpt` | text | NULLABLE |
| `body` | longText | NOT NULL |
| `cover_image` | string (255) | NULLABLE |
| `read_time_minutes` | tinyInteger | DEFAULT 3 |
| `status` | enum('draft','published','archived') | DEFAULT 'draft' |
| `is_featured` | boolean | DEFAULT false |
| `views_count` | integer | DEFAULT 0 |
| `published_at` | timestamp | NULLABLE |
| `meta_title` | string (255) | NULLABLE |
| `meta_description` | text | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `article_categories`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `name` | string (120) | NOT NULL |
| `slug` | string (140) | NOT NULL, UNIQUE |
| `description` | text | NULLABLE |
| `is_active` | boolean | DEFAULT true |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

**Seed data:** Admissions Guide, School News, Education Policy, Parent Tips, Rankings

---

### Table: `settings`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `key` | string (255) | NOT NULL, UNIQUE |
| `value` | text | NULLABLE |
| `type` | enum('string','json','boolean','integer','html') | DEFAULT 'string' |
| `group` | string (60) | NULLABLE (branding, seo, contact, social, payment) |
| `label` | string (255) | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Table: `activity_logs`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigInteger (unsigned) | Primary, auto-increment |
| `user_id` | bigInteger (unsigned) | NULLABLE |
| `action` | string (255) | NOT NULL |
| `description` | text | NULLABLE |
| `subject_type` | string (255) | NULLABLE |
| `subject_id` | bigInteger (unsigned) | NULLABLE |
| `properties` | json | NULLABLE |
| `ip_address` | string (45) | NULLABLE |
| `user_agent` | text | NULLABLE |
| `created_at` | timestamp | NULLABLE |
| `updated_at` | timestamp | NULLABLE |

---

### Standard Laravel Tables

`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`, `password_reset_tokens`, `personal_access_tokens`, `notifications`

---

## 4. Models & Relationships

### User (app/Models/User.php) — Table: `users`

**Fillable:** `name`, `email`, `password`, `user_type`, `status`, `profile_photo`, `phone`, `district_id`

**Hidden:** `password`, `remember_token`

**Casts:**
- `email_verified_at` → `datetime`
- `last_login_at` → `datetime`
- `password` → `hashed`

**Relationships:**
- `district()` → `BelongsTo(District::class)`
- `ownedSchool()` → `HasOne(School::class, 'owner_id')`
- `reviews()` → `HasMany(Review::class)`
- `inquiries()` → `HasMany(Inquiry::class)`
- `savedSchools()` → `BelongsToMany(School::class, 'saved_schools')`
- `notifications()` → standard Laravel polymorphic

**Methods:**
- `isAdmin(): bool` — checks `user_type === 'admin'`
- `isSchoolOwner(): bool` — checks `user_type === 'school_owner'`
- `isParent(): bool` — checks `user_type === 'parent'`
- `hasSchool(): bool` — checks if `ownedSchool` exists
- `hasSaved(School $school): bool` — checks saved_schools pivot

---

### School (app/Models/School.php) — Table: `schools`

**Fillable:** `owner_id`, `type_id`, `district_id`, `sub_county_id`, `name`, `slug`, `tagline`, `description`, `founded_year`, `registration_number`, `latitude`, `longitude`, `address`, `phone`, `email`, `website`, `whatsapp`, `boarding_type`, `gender_policy`, `student_count_approx`, `status`, `verification_status`, `is_featured`, `featured_until`, `meta_title`, `meta_description`

**Casts:**
- `is_featured` → `boolean`
- `featured_until` → `date`
- `rating_average` → `decimal:2`
- `submitted_at` → `datetime`
- `verified_at` → `datetime`

**Relationships:**
- `type()` → `BelongsTo(SchoolType::class)`
- `district()` → `BelongsTo(District::class)`
- `subCounty()` → `BelongsTo(SubCounty::class)`
- `owner()` → `BelongsTo(User::class, 'owner_id')`
- `curricula()` → `BelongsToMany(Curriculum::class)`
- `programs()` → `HasMany(SchoolProgram::class)`
- `fees()` → `HasMany(SchoolFee::class)`
- `facilities()` → `BelongsToMany(Facility::class, 'school_facilities')`
- `reviews()` → `HasMany(Review::class)`
- `approvedReviews()` → `HasMany(Review::class)->where('status', 'approved')`
- `inquiries()` → `HasMany(Inquiry::class)`
- `activeSubscription()` → `HasOne(Subscription::class)->where('status', 'active')->latest()`
- `featuredSlots()` → `HasMany(FeaturedSlot::class)`
- `contacts()` → `HasMany(SchoolContact::class)`
- `savedByUsers()` → `BelongsToMany(User::class, 'saved_schools')`
- `views()` → `HasMany(SchoolView::class)`

**Scout searchable fields:** `name`, `tagline`, `description`, `district.name`, `type.name`, `curricula.*.abbreviation`

**Scopes:**
- `scopeActive($query)` — where `status = 'active'`
- `scopeVerified($query)` — where `verification_status = 'verified'`
- `scopeFeatured($query)` — where `is_featured = true` and `featured_until >= now()`
- `scopeInDistrict($query, $districtId)`
- `scopeByType($query, $typeId)`
- `scopeByCurriculum($query, $curriculumId)` — via join
- `scopePending($query)` — where `status = 'pending'`

**Methods:**
- `isPremium(): bool` — checks activeSubscription plan
- `canReceiveInquiries(): bool` — active + verified
- `updateRatingCache(): void` — recalculates and saves `rating_average`, `reviews_count`

---

### Region (app/Models/Region.php) — Table: `regions`

**Fillable:** `name`, `slug`

**Relationships:**
- `districts()` → `HasMany(District::class)`

---

### District (app/Models/District.php) — Table: `districts`

**Fillable:** `region_id`, `name`, `slug`, `school_count`, `is_featured`

**Relationships:**
- `region()` → `BelongsTo(Region::class)`
- `schools()` → `HasMany(School::class)`
- `subCounties()` → `HasMany(SubCounty::class)`

---

### SubCounty (app/Models/SubCounty.php) — Table: `sub_counties`

**Fillable:** `district_id`, `name`, `slug`

**Relationships:**
- `district()` → `BelongsTo(District::class)`
- `schools()` → `HasMany(School::class)`

---

### SchoolType (app/Models/SchoolType.php) — Table: `school_types`

**Fillable:** `name`, `slug`, `icon`, `description`, `order`, `is_active`

**Relationships:**
- `schools()` → `HasMany(School::class, 'type_id')`

---

### Curriculum (app/Models/Curriculum.php) — Table: `curricula`

**Fillable:** `name`, `slug`, `abbreviation`, `description`, `is_active`

**Relationships:**
- `schools()` → `BelongsToMany(School::class)`
- `programs()` → `HasMany(SchoolProgram::class)`

---

### Facility (app/Models/Facility.php) — Table: `facilities`

**Fillable:** `name`, `slug`, `icon`, `category`, `is_active`

**Relationships:**
- `schools()` → `BelongsToMany(School::class, 'school_facilities')`

---

### SchoolProgram (app/Models/SchoolProgram.php) — Table: `school_programs`

**Fillable:** `school_id`, `curriculum_id`, `name`, `level`, `duration`, `description`, `is_active`

**Relationships:**
- `school()` → `BelongsTo(School::class)`
- `curriculum()` → `BelongsTo(Curriculum::class)`
- `fees()` → `HasMany(SchoolFee::class, 'program_id')`

---

### SchoolFee (app/Models/SchoolFee.php) — Table: `school_fees`

**Fillable:** `school_id`, `program_id`, `category`, `label`, `amount`, `currency`, `period`, `academic_year`, `notes`, `is_active`

**Casts:**
- `amount` → `decimal:2`

**Relationships:**
- `school()` → `BelongsTo(School::class)`
- `program()` → `BelongsTo(SchoolProgram::class)`

---

### SchoolContact (app/Models/SchoolContact.php) — Table: `school_contacts`

**Fillable:** `school_id`, `type`, `value`, `label`, `is_primary`

**Relationships:**
- `school()` → `BelongsTo(School::class)`

---

### Review (app/Models/Review.php) — Table: `reviews`

**Fillable:** `school_id`, `user_id`, `reviewer_type`, `rating`, `title`, `body`, `academic_rating`, `facilities_rating`, `staff_rating`, `environment_rating`, `status`, `is_verified`, `owner_response`, `owner_responded_at`, `helpful_count`

**Casts:**
- `is_verified` → `boolean`
- `owner_responded_at` → `datetime`

**Relationships:**
- `school()` → `BelongsTo(School::class)`
- `user()` → `BelongsTo(User::class)`
- `votes()` → `HasMany(ReviewVote::class)`

**Scopes:**
- `scopeApproved($query)`
- `scopePending($query)`

---

### Inquiry (app/Models/Inquiry.php) — Table: `inquiries`

**Fillable:** `school_id`, `user_id`, `parent_name`, `parent_email`, `parent_phone`, `student_name`, `student_age`, `intended_level`, `message`, `source`, `status`

**Relationships:**
- `school()` → `BelongsTo(School::class)`
- `user()` → `BelongsTo(User::class)`

---

### Plan (app/Models/Plan.php) — Table: `plans`

**Fillable:** `name`, `slug`, `tagline`, `description`, `price`, `currency`, `duration_days`, `billing_cycle`, `features`, `includes_featured_slot`, `featured_slot_count`, `is_active`, `order`

**Casts:**
- `price` → `decimal:2`
- `features` → `array`
- `is_active` → `boolean`
- `includes_featured_slot` → `boolean`

**Relationships:**
- `subscriptions()` → `HasMany(Subscription::class)`

**Scopes:**
- `scopeActive($query)`

---

### Subscription (app/Models/Subscription.php) — Table: `subscriptions`

**Fillable:** `school_id`, `plan_id`, `start_date`, `end_date`, `status`, `auto_renew`

**Casts:**
- `start_date` → `date`
- `end_date` → `date`
- `auto_renew` → `boolean`

**Relationships:**
- `school()` → `BelongsTo(School::class)`
- `plan()` → `BelongsTo(Plan::class)`
- `payments()` → `HasMany(Payment::class)`

**Methods:**
- `isExpired(): bool` — checks `end_date < today()`
- `daysRemaining(): int`

---

### Payment (app/Models/Payment.php) — Table: `payments`

**Fillable:** `school_id`, `subscription_id`, `amount`, `currency`, `payment_method`, `transaction_id`, `status`, `gateway`, `gateway_response`, `paid_at`

**Casts:**
- `amount` → `decimal:2`
- `gateway_response` → `array`
- `paid_at` → `datetime`

**Relationships:**
- `school()` → `BelongsTo(School::class)`
- `subscription()` → `BelongsTo(Subscription::class)`

---

### Article (app/Models/Article.php) — Table: `articles`

**Fillable:** `author_id`, `category_id`, `title`, `slug`, `excerpt`, `body`, `cover_image`, `read_time_minutes`, `status`, `is_featured`, `published_at`, `meta_title`, `meta_description`

**Casts:**
- `published_at` → `datetime`
- `is_featured` → `boolean`

**Relationships:**
- `author()` → `BelongsTo(User::class, 'author_id')`
- `category()` → `BelongsTo(ArticleCategory::class)`

**Scopes:**
- `scopePublished($query)` — where `status = 'published'` and `published_at <= now()`

---

### Setting (app/Models/Setting.php) — Table: `settings`

**Fillable:** `key`, `value`, `type`, `group`, `label`

**Static Methods:**
- `get(string $key, mixed $default = null): mixed` — fetch and cast value
- `set(string $key, mixed $value): void`

---

## 5. Routes Map — Public Frontend

All public routes defined in `routes/web.php`.

| Method | URI | Controller / Livewire Component | Middleware | Route Name |
|--------|-----|---------------------------------|------------|------------|
| GET | `/` | `Livewire\Home\HomePage` | web | `home` |
| GET | `/schools` | `Livewire\Schools\SchoolListing` | web | `schools.index` |
| GET | `/schools/{school:slug}` | `Livewire\Schools\SchoolDetail` | web | `schools.show` |
| GET | `/district/{district:slug}` | `Livewire\Districts\DistrictSchools` | web | `district.show` |
| GET | `/search` | `Livewire\Search\SearchResults` | web | `search` |
| GET | `/compare` | `Livewire\Compare\CompareSchools` | web | `compare` |
| GET | `/compare/{school1:slug}/{school2:slug}` | `Livewire\Compare\CompareSchools` | web | `compare.schools` |
| GET | `/articles` | `Livewire\Articles\ArticleListing` | web | `articles.index` |
| GET | `/articles/{article:slug}` | `Livewire\Articles\ArticleShow` | web | `articles.show` |
| GET | `/about` | `Guest\PageController@about` | web | `about` |
| GET | `/contact` | `Guest\ContactController@show` | web | `contact` |
| POST | `/contact` | `Guest\ContactController@store` | web | `contact.store` |
| GET | `/privacy-policy` | `Guest\PageController@legal` | web | `legal.privacy` |
| GET | `/terms-and-conditions` | `Guest\PageController@legal` | web | `legal.terms` |
| GET | `/sitemap.xml` | `Guest\SitemapController@index` | — | `sitemap` |
| GET | `/login` | `Guest\AuthController@showLogin` | web, guest | `login` |
| POST | `/login` | `Guest\AuthController@storeLogin` | web, guest | — |
| GET | `/register` | `Guest\AuthController@showRegister` | web, guest | `register` |
| POST | `/register` | `Guest\AuthController@storeRegister` | web, guest | — |
| GET | `/register/school-owner` | `Guest\SchoolOwnerAuthController@show` | web, guest | `register.school-owner` |
| POST | `/register/school-owner` | `Guest\SchoolOwnerAuthController@store` | web, guest | — |
| GET | `/forgot-password` | `Guest\AuthController@forgotPassword` | web, guest | `password.request` |
| POST | `/forgot-password` | `Guest\AuthController@sendResetLink` | web, guest | `password.email` |
| GET | `/reset-password/{token}` | `Guest\AuthController@resetForm` | web, guest | `password.reset` |
| POST | `/reset-password` | `Guest\AuthController@resetPassword` | web, guest | `password.update` |
| POST | `/logout` | `Guest\AuthController@destroy` | web, auth | `logout` |

### Authenticated User Routes (parents / students)

| Method | URI | Controller / Livewire Component | Middleware | Route Name |
|--------|-----|---------------------------------|------------|------------|
| GET | `/dashboard` | `Livewire\User\UserDashboard` | web, auth | `user.dashboard` |
| POST | `/schools/{school}/inquire` | `User\InquiryController@store` | web, auth | `inquiry.store` |
| POST | `/schools/{school}/reviews` | `User\ReviewController@store` | web, auth | `review.store` |
| POST | `/schools/{school}/save` | `User\SavedSchoolController@toggle` | web, auth | `school.save.toggle` |
| GET | `/saved-schools` | `Livewire\User\SavedSchools` | web, auth | `saved-schools` |
| GET | `/my-inquiries` | `Livewire\User\MyInquiries` | web, auth | `my-inquiries` |
| GET | `/my-reviews` | `Livewire\User\MyReviews` | web, auth | `my-reviews` |
| GET | `/profile` | `Livewire\User\ProfileEdit` | web, auth | `profile.edit` |
| PUT | `/profile` | `User\ProfileController@update` | web, auth | `profile.update` |

### Payment Callback Routes

| Method | URI | Controller@method | Route Name |
|--------|-----|-------------------|------------|
| GET | `/payments/flutterwave/callback` | `User\PaymentController@flutterwaveCallback` | `payments.flutterwave.callback` |
| POST | `/webhooks/flutterwave` | `User\PaymentController@flutterwaveWebhook` | `webhooks.flutterwave` |

---

## 6. Routes Map — School Owner Portal

School owner portal is implemented as a **second Filament 4 panel** at `/owner/*`. Defined in `app/Providers/Filament/OwnerPanelProvider.php`.

All routes require `web`, `auth`, and `school_owner` middleware. Users without an associated school are redirected to the school setup wizard.

| Method | URI | Filament Page / Resource | Route Name |
|--------|-----|--------------------------|------------|
| GET | `/owner` | Redirect → dashboard | `owner` |
| GET | `/owner/dashboard` | `Owner\Pages\OwnerDashboard` | `owner.dashboard` |
| GET | `/owner/school` | `Owner\Resources\SchoolResource\Pages\EditSchool` | `owner.school.edit` |
| GET | `/owner/school/programs` | `Owner\Resources\ProgramResource\Pages\ListPrograms` | `owner.programs.index` |
| GET | `/owner/school/fees` | `Owner\Resources\FeeResource\Pages\ListFees` | `owner.fees.index` |
| GET | `/owner/school/facilities` | `Owner\Pages\ManageFacilities` | `owner.facilities` |
| GET | `/owner/school/gallery` | `Owner\Pages\ManageGallery` | `owner.gallery` |
| GET | `/owner/inquiries` | `Owner\Resources\InquiryResource\Pages\ListInquiries` | `owner.inquiries.index` |
| GET | `/owner/reviews` | `Owner\Resources\ReviewResource\Pages\ListReviews` | `owner.reviews.index` |
| GET | `/owner/analytics` | `Owner\Pages\Analytics` | `owner.analytics` |
| GET | `/owner/subscription` | `Owner\Pages\Subscription` | `owner.subscription` |
| GET | `/owner/settings` | `Owner\Pages\OwnerSettings` | `owner.settings` |
| GET | `/owner/setup` | `Owner\Pages\SchoolSetupWizard` | `owner.setup` |

---

## 7. Filament Admin Panel — Resources & Pages

Admin panel at `/admin/*`. Defined in `app/Providers/Filament/AdminPanelProvider.php`. Uses Filament 4 resource auto-routing. Access restricted to `user_type = 'admin'`.

### Resources

| Resource Class | Model | Panel Sections |
|---------------|-------|----------------|
| `SchoolResource` | `School` | List / Create / Edit / View — includes verification workflow, featured toggle |
| `UserResource` | `User` | List / Create / Edit — filter by user_type, status control |
| `DistrictResource` | `District` | List / Create / Edit — manage school_count cache |
| `RegionResource` | `Region` | List / Create / Edit |
| `ReviewResource` | `Review` | List / Edit — moderate (approve/reject/flag), view school owner responses |
| `InquiryResource` | `Inquiry` | List / View — read-only audit of all inquiries |
| `PlanResource` | `Plan` | List / Create / Edit — manage pricing tiers |
| `SubscriptionResource` | `Subscription` | List / View — status management |
| `PaymentResource` | `Payment` | List / View — status updates, export CSV |
| `ArticleResource` | `Article` | List / Create / Edit — rich text editor (TipTap), publish workflow |
| `ArticleCategoryResource` | `ArticleCategory` | List / Create / Edit |
| `FacilityResource` | `Facility` | List / Create / Edit |
| `SchoolTypeResource` | `SchoolType` | List / Create / Edit |
| `CurriculumResource` | `Curriculum` | List / Create / Edit |
| `FeaturedSlotResource` | `FeaturedSlot` | List / Create / Edit |
| `ActivityLogResource` | `ActivityLog` | List / View — read-only audit trail |

### Custom Pages

| Page Class | URI | Purpose |
|-----------|-----|---------|
| `Admin\Pages\Dashboard` | `/admin` | KPI overview — total schools, pending, MRR, visitors, charts |
| `Admin\Pages\PendingApprovals` | `/admin/pending-approvals` | Dedicated moderation queue with approve/reject actions |
| `Admin\Pages\SiteSettings` | `/admin/settings` | Key-value settings editor (branding, SEO, contact, payment keys) |
| `Admin\Pages\Reports` | `/admin/reports` | Expiring subscriptions, district breakdown, review trends |

---

## 8. Livewire Pages — Build Logic

### Home Page — `GET /`

- **Livewire Component:** `app/Livewire/Home/HomePage.php`
- **View:** `resources/views/livewire/home/home-page.blade.php`
- **Queries on mount:**
  - `SchoolType::active()->ordered()->get()` — for type grid
  - `School::active()->verified()->featured()->with(['district','type'])->limit(6)->get()` — featured cards
  - `District::with('region')->inRandomOrder()->limit(8)->get()` — district browse
  - `Article::published()->featured()->with('category')->limit(3)->get()` — latest articles
- **Child Livewire Components:** `SchoolSearchBar`, `FeaturedSchoolsCarousel`, `DistrictGrid`
- **Alpine.js:** Filter chip active state, mobile search expand

---

### School Listing — `GET /schools`

- **Livewire Component:** `app/Livewire/Schools/SchoolListing.php`
- **View:** `resources/views/livewire/schools/school-listing.blade.php`
- **URL query params:** `district`, `type`, `curriculum`, `boarding`, `min_fees`, `max_fees`, `facilities[]`, `verified_only`, `sort`, `page`
- **Query logic:** Builds Scout/Meilisearch query from active filters. Falls back to Eloquent for complex fee range filtering
- **Properties (reactive):** `$district`, `$type`, `$curriculum`, `$boardingType`, `$minFees`, `$maxFees`, `$verifiedOnly`, `$sort`, `$search`
- **Computed:** `$schools` — paginated 15 per page, eager-loads `district`, `type`, `curricula`
- **Child Components:** `SchoolCard`, `SchoolFilters` (sidebar), `MapEmbed`

---

### School Detail — `GET /schools/{school:slug}`

- **Livewire Component:** `app/Livewire/Schools/SchoolDetail.php`
- **View:** `resources/views/livewire/schools/school-detail.blade.php`
- **Props:** `School $school` (route model bound)
- **Queries on mount:**
  - Eager loads `type`, `district`, `curricula`, `programs`, `fees`, `facilities`, `contacts`, `approvedReviews.user`, `activeSubscription.plan`
  - Dispatches `RecordSchoolView` job (queued, avoids slowing page load)
- **Tabs (Alpine.js-powered):** Overview, Programs & Fees, Gallery, Reviews, Admissions
- **Child Components:** `ReviewsList`, `InquiryForm`, `SchoolGallery`, `RatingSummary`, `MapEmbed`, `RelatedSchools`
- **Auth check:** `InquiryForm` shows login prompt if unauthenticated

---

### District Schools — `GET /district/{district:slug}`

- **Livewire Component:** `app/Livewire/Districts/DistrictSchools.php`
- **View:** `resources/views/livewire/districts/district-schools.blade.php`
- **Props:** `District $district` (route model bound with `region`)
- **Queries:** Schools in this district, active + verified. Same filters as SchoolListing but pre-scoped
- **SEO:** Generates dynamic meta title/description from district name and school count

---

### Search Results — `GET /search`

- **Livewire Component:** `app/Livewire/Search/SearchResults.php`
- **View:** `resources/views/livewire/search/search-results.blade.php`
- **URL query param:** `q` — full-text search term
- **Engine:** `School::search($query)->query(fn($q) => $q->active()->verified())->paginate(15)`
- **Fallback:** If Meilisearch is unavailable, falls back to `LIKE` query on `name` + `description`
- **Instant search:** Alpine.js + Livewire `wire:model.live.debounce.300ms` on search input

---

### Compare Schools — `GET /compare`

- **Livewire Component:** `app/Livewire/Compare/CompareSchools.php`
- **View:** `resources/views/livewire/compare/compare-schools.blade.php`
- **State:** Stored in Livewire `$schoolIds` array (max 3). Persisted in session
- **Props from URL:** `?schools[]=slug1&schools[]=slug2` pre-fills comparison
- **Comparison fields:** Type, Curriculum(s), District, Fees (lowest annual), Boarding, Gender Policy, Student Count, Rating, Verified status, Facilities match
- **Child Components:** `CompareSchoolPicker` (add school slot), `CompareTable`

---

### Article Listing — `GET /articles`

- **Livewire Component:** `app/Livewire/Articles/ArticleListing.php`
- **View:** `resources/views/livewire/articles/article-listing.blade.php`
- **Queries:** `Article::published()->with('category','author')->latest('published_at')->paginate(12)`
- **Filter:** By category (tab chips)

---

### Article Show — `GET /articles/{article:slug}`

- **Livewire Component:** `app/Livewire/Articles/ArticleShow.php`
- **View:** `resources/views/livewire/articles/article-show.blade.php`
- **Props:** `Article $article`
- **Dispatch on mount:** `RecordArticleView` job
- **Related:** `Article::published()->where('category_id', $article->category_id)->where('id', '!=', $article->id)->limit(3)->get()`

---

### School Owner Dashboard — `GET /owner/dashboard`

- **Filament Page:** `app/Filament/Owner/Pages/OwnerDashboard.php`
- **Data pulled:**
  - `$school->views_count` (last 30 days from `school_views`)
  - `$school->inquiries()->where('created_at', '>=', now()->subDays(30))->count()`
  - `$school->rating_average` and `$school->reviews_count`
  - `$school->activeSubscription()->with('plan')->first()`
  - `$school->inquiries()->where('status','new')->latest()->limit(5)->get()`
- **Widgets:** `ViewsChart` (Chart.js 30-day line), `InquiriesByStatusPie`, `SubscriptionStatusCard`, `RecentInquiriesTable`

---

### School Setup Wizard — `GET /owner/setup`

- **Filament Page:** `app/Filament/Owner/Pages/SchoolSetupWizard.php`
- **Steps:** (Filament 4 Wizard component)
  1. Basic Info — name, type, description, founded year, registration number
  2. Location — district, sub-county, address, latitude/longitude (map pin)
  3. Programs — add curricula + program levels
  4. Fees — fee table per program per category
  5. Facilities — checkbox grid
  6. Gallery — FilePond image upload (min 1, max 20)
  7. Review & Submit — summary before submitting for admin approval
- **On submit:** Sets `schools.status = 'pending'`, sets `submitted_at`, fires `SchoolSubmittedForReview` event → notifies admin

---

### Admin Dashboard — `GET /admin`

- **Filament Page:** `app/Filament/Admin/Pages/Dashboard.php`
- **Widgets:**
  - `TotalSchoolsCard` — total active + breakdown by type
  - `PendingApprovalsCard` — count with link to moderation queue
  - `MonthlyVisitorsCard` — from `school_views` aggregate
  - `MRRCard` — monthly recurring revenue from active subscriptions
  - `SchoolsByDistrictChart` — top 10 districts bar chart
  - `SubscriptionRevenueChart` — last 12 months line chart
  - `RecentSubmissionsTable` — latest 5 pending schools

---

### Admin Pending Approvals — `GET /admin/pending-approvals`

- **Filament Page:** `app/Filament/Admin/Pages/PendingApprovals.php`
- **Queries:** `School::pending()->with('owner','district','type','curricula')->latest('submitted_at')->paginate(20)`
- **Actions per row:** Approve (sets `status = active`, `verification_status = verified`, fires `SchoolApproved` event) / Reject (requires rejection reason textarea, fires `SchoolRejected` event) / Preview (opens school detail in new tab)
- **Approve effect:** Notifies owner via email + in-app notification

---

## 9. Design System

### CSS Architecture

- **Framework:** Tailwind CSS v4 (configured via `@import 'tailwindcss'` in `resources/css/app.css`)
- **Plugin:** `@tailwindcss/typography` for article prose
- **Plugin:** `@tailwindcss/forms` for base form styling

### CSS Custom Properties

All brand colors defined as CSS variables in `resources/views/layouts/app.blade.php` `<head>`, loaded from `config('brand')` which reads from the `settings` table:

| Variable | Default | Notes |
|----------|---------|-------|
| `--brand-primary` | `#4F46E5` | Indigo — primary CTA, links, active states |
| `--brand-accent` | `#F59E0B` | Amber/Gold — badges, highlights, featured |
| `--brand-success` | `#10B981` | Green — verified badge, active status |
| `--brand-danger` | `#EF4444` | Red — rejected, errors |
| `--brand-warning` | `#F59E0B` | Warning states |
| `--brand-navy` | `#0F172A` | Dashboard sidebar background |
| `--font-display` | `DM Serif Display` | Headlines, hero text |
| `--font-body` | `Inter` | Body copy, UI text |

### Tailwind Color Tokens (CSS v4 theme)

```css
@theme {
  --color-primary:     var(--brand-primary);
  --color-primary-50:  color-mix(in srgb, var(--brand-primary), white 95%);
  --color-primary-100: color-mix(in srgb, var(--brand-primary), white 90%);
  --color-primary-600: color-mix(in srgb, var(--brand-primary), black 10%);
  --color-primary-700: color-mix(in srgb, var(--brand-primary), black 20%);

  --color-accent:     var(--brand-accent);
  --color-accent-50:  color-mix(in srgb, var(--brand-accent), white 95%);
  --color-accent-600: color-mix(in srgb, var(--brand-accent), black 10%);
}
```

### Typography

| Use | Font | Weight | Size |
|-----|------|--------|------|
| Hero headline | DM Serif Display | 400 (italic) | `clamp(2rem, 5vw, 3.5rem)` |
| Section heading | DM Serif Display | 400 | `1.75rem – 2.25rem` |
| Card title | Inter | 600 | `1rem` |
| Body copy | Inter | 400 | `0.9375rem` |
| Caption / muted | Inter | 400 | `0.8125rem` |
| Badge / label | Inter | 700 | `0.6875rem` (uppercase, tracked) |

**Google Fonts import:**
```html
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
```

### Spacing Scale

The spacing scale follows Tailwind defaults. Key layout values:

| Token | Value | Use |
|-------|-------|-----|
| `--space-section` | `5rem` (80px) | Section vertical padding |
| `--space-card` | `1.5rem` (24px) | Card internal padding |
| `--space-card-sm` | `1rem` (16px) | Compact card padding (mobile) |

### Border Radius

| Token | Value | Use |
|-------|-------|-----|
| `rounded-card` | `16px` | School cards, hero sections |
| `rounded-badge` | `999px` | Chips, status badges |
| `rounded-btn` | `10px` | Primary buttons |
| `rounded-input` | `10px` | Form inputs |

### Shadows

```css
--shadow-card:   0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
--shadow-card-hover: 0 4px 8px rgba(0,0,0,0.08), 0 12px 32px rgba(0,0,0,0.06);
--shadow-modal:  0 20px 60px rgba(0,0,0,0.15);
```

### Button Styles

| Variant | Style |
|---------|-------|
| `btn-primary` | `bg-primary text-white rounded-btn px-5 py-2.5 font-semibold hover:bg-primary-700 transition` |
| `btn-secondary` | `border border-primary text-primary rounded-btn px-5 py-2.5 font-semibold hover:bg-primary-50 transition` |
| `btn-accent` | `bg-accent text-white rounded-btn px-5 py-2.5 font-semibold hover:bg-accent-600 transition` |
| `btn-ghost` | `text-gray-600 hover:text-primary hover:bg-primary-50 rounded-btn px-4 py-2 transition` |

### Badge Styles

| Badge | Style |
|-------|-------|
| Verified | `bg-green-50 text-green-700 border border-green-200 rounded-badge px-2.5 py-0.5 text-xs font-bold` |
| Featured | `bg-amber-50 text-amber-700 border border-amber-200 rounded-badge px-2.5 py-0.5 text-xs font-bold` |
| Premium | `bg-primary-50 text-primary-700 border border-primary-200 rounded-badge px-2.5 py-0.5 text-xs font-bold` |
| Pending | `bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-badge px-2.5 py-0.5 text-xs font-bold` |
| Active | `bg-green-50 text-green-700 border border-green-200 rounded-badge px-2.5 py-0.5 text-xs font-bold` |
| Rejected | `bg-red-50 text-red-700 border border-red-200 rounded-badge px-2.5 py-0.5 text-xs font-bold` |

### Icon System

- **Library:** Lucide (via `npm i lucide`) — thin, consistent 24px stroke icons
- **Usage in Blade:** `<x-lucide-search class="w-5 h-5 text-primary" />` (Blade component wrapper)
- **Usage in Alpine:** Inline SVG or `<img>` from Lucide CDN for Alpine-driven dynamic icons
- **No Font Awesome** unless absolutely required for legacy compatibility

### Responsive Breakpoints

| Breakpoint | Width | Layout change |
|-----------|-------|---------------|
| `sm` | 640px | Stacked → 2-column on select grids |
| `md` | 768px | Filter sidebar appears; nav becomes full |
| `lg` | 1024px | 3-column school grid; split hero layout |
| `xl` | 1280px | Max content width locks at 1280px |
| `2xl` | 1536px | Unused — platform targets 1280px max |

---

## 10. Alpine.js & Livewire Frontend Architecture

### Entry Points

**Client:** `resources/js/app.js`
- Imports Alpine.js + plugins (`anchor`, `collapse`, `focus`, `intersect`)
- Imports `lucide` and calls `createIcons()` on DOM load
- Imports `tom-select`, `filepond` plugins
- Initialises `leaflet` if a map container is found on the page
- Imports `chart.js` for dashboard pages only (lazy via dynamic import)

### Frontend Directory Structure

```
resources/
├── css/
│   └── app.css                  # Tailwind v4 entry + CSS variables
├── js/
│   ├── app.js                   # Alpine + plugin bootstrap
│   ├── map.js                   # Leaflet map initialisation helpers
│   ├── charts.js                # Chart.js wrapper for owner analytics
│   ├── filepond.js              # FilePond gallery upload setup
│   └── tom-select.js            # Tom Select initialisation
├── views/
│   ├── layouts/
│   │   ├── app.blade.php        # Public layout (nav, footer, meta)
│   │   └── guest.blade.php      # Auth layout (centered, minimal)
│   ├── components/
│   │   ├── school-card.blade.php         # Reusable school listing card
│   │   ├── school-card-horizontal.blade.php  # Result row card
│   │   ├── badge.blade.php              # Dynamic badge component
│   │   ├── star-rating.blade.php        # Star display (read-only + interactive)
│   │   ├── inquiry-form.blade.php       # Embedded inquiry form
│   │   ├── map-embed.blade.php          # Leaflet or Google Maps iframe
│   │   ├── breadcrumbs.blade.php        # SEO breadcrumbs
│   │   ├── pagination.blade.php         # Tailwind-styled paginator
│   │   └── section-heading.blade.php    # Consistent section header
│   ├── livewire/
│   │   ├── home/
│   │   ├── schools/
│   │   ├── districts/
│   │   ├── search/
│   │   ├── compare/
│   │   ├── articles/
│   │   └── user/
│   └── vendor/                  # Filament overrides (if customised)
```

### Key Livewire Components

| Component Class | Responsibility |
|----------------|----------------|
| `SchoolSearchBar` | Global search with live suggestions via `wire:model.live.debounce.300ms`, renders school name suggestions from Scout |
| `SchoolFilters` | Reactive filter sidebar — each filter updates `$parent` via `$dispatch('filters-updated', ...)` |
| `InquiryForm` | Sends inquiry form, shows success message inline, tracks `school_id` |
| `ReviewForm` | Star picker + textarea, submits new review, shows pending notice |
| `ReviewsList` | Paginates and renders approved reviews, handles helpful vote |
| `SchoolGallery` | Lightbox image grid, uses Alpine for modal state |
| `SchoolCard` | Stateless card component — renders school summary |
| `MapEmbed` | Initialises Leaflet with school `lat/lng`, shows single pin or cluster |
| `CompareSchoolPicker` | Autocomplete school selector for adding to compare slot |
| `SaveSchoolButton` | Toggles saved state via optimistic UI, requires auth |

### Alpine.js Data Components

```javascript
// Navigation mega-menu (resources/views/layouts/app.blade.php)
Alpine.data('megaMenu', () => ({
  open: null,
  toggle(section) { this.open = this.open === section ? null : section }
}))

// School detail tabs
Alpine.data('schoolTabs', () => ({
  active: 'overview',
  setTab(tab) { this.active = tab }
}))

// Comparison add/remove
Alpine.data('compareBar', () => ({
  schools: JSON.parse(localStorage.getItem('compare_schools') || '[]'),
  add(slug) { if (this.schools.length < 3 && !this.schools.includes(slug)) this.schools.push(slug) },
  remove(slug) { this.schools = this.schools.filter(s => s !== slug) },
  // persists to localStorage for cross-page state
}))
```

---

## 11. Seeders Reference

All seeders run via `php artisan db:seed`. Production uses `--class` to run specific seeders only.

| Seeder | Data Inserted | Count |
|--------|-------------|-------|
| `RegionSeeder` | Uganda's 4 regions | 4 |
| `DistrictSeeder` | All 135 Uganda districts with region assignments | 135 |
| `SchoolTypeSeeder` | Nursery, Primary, Secondary, A-Level, University, Vocational, International | 7 |
| `CurriculumSeeder` | UNEB O-Level, UNEB A-Level, Cambridge IGCSE, Cambridge A-Level, IB, STEM Focus | 6 |
| `FacilitySeeder` | 13 common school facilities with icons and categories | 13 |
| `PlanSeeder` | Free (0 UGX), Standard (500,000 UGX/yr), Premium (1,500,000 UGX/yr) | 3 |
| `ArticleCategorySeeder` | Admissions Guide, School News, Education Policy, Parent Tips, Rankings | 5 |
| `SettingSeeder` | Site name, logo, SEO defaults, contact info, social links, branding colors | ~25 |
| `AdminUserSeeder` | Super admin account: admin@findursch.ug | 1 |
| `DemoSchoolSeeder` | 20 demo schools across 5 districts (dev/staging only) | 20 |
| `DemoUserSeeder` | Sample parent users, school owner users (dev/staging only) | 10 |
| `DemoReviewSeeder` | 50 approved reviews spread across demo schools (dev/staging only) | 50 |

**Note:** `Demo*` seeders are guarded by `if (app()->environment(['local', 'staging']))` checks and must never run in production.

All seeders use `firstOrCreate()` — safe to re-run.

---

## 12. Console Commands & Scheduling

### Artisan Commands

| Command | Class | Description |
|---------|-------|-------------|
| `findursch:update-district-counts` | `UpdateDistrictCounts` | Recalculates `districts.school_count` from live data |
| `findursch:update-school-ratings` | `UpdateSchoolRatings` | Recalculates `schools.rating_average` and `schools.reviews_count` from approved reviews |
| `findursch:prune-school-views` | `PruneSchoolViews` | Deletes `school_views` records older than 90 days |
| `findursch:expire-subscriptions` | `ExpireSubscriptions` | Sets `subscriptions.status = 'expired'` for past end_date, updates school flags |
| `findursch:expire-featured-slots` | `ExpireFeaturedSlots` | Sets `featured_slots.is_active = false` for past end dates, clears `schools.is_featured` |
| `findursch:send-subscription-reminders` | `SendSubscriptionReminders` | Emails school owners 14 days and 3 days before subscription expiry |
| `findursch:sitemap-generate` | `GenerateSitemap` | Regenerates `public/sitemap.xml` using Spatie Sitemap |
| `findursch:index-schools` | `IndexSchools` | Full re-index of all active schools into Meilisearch |

### Scheduled Tasks

| Frequency | Command | Notes |
|-----------|---------|-------|
| `daily` | `findursch:update-district-counts` | Low priority; overnight is fine |
| `daily` | `findursch:update-school-ratings` | Run after midnight to avoid cache inconsistency |
| `daily` | `findursch:expire-subscriptions` | Run at 00:01 to catch any midnight expirations |
| `daily` | `findursch:expire-featured-slots` | Same as above |
| `weekly` | `findursch:prune-school-views` | Keeps `school_views` table lean |
| `dailyAt('09:00')` | `findursch:send-subscription-reminders` | Business-hours notification time (EAT) |
| `weekly` | `findursch:sitemap-generate` | Every Monday — covers new schools approved during the week |
| `hourly` | `horizon:snapshot` | Laravel Horizon metrics snapshot |
| `monthly` | `telescope:prune` | If Laravel Telescope is enabled in staging |

### Dev Server Scripts (composer.json)

| Command | What it runs |
|---------|-------------|
| `composer run dev` | `php artisan serve` + `queue:listen` + `npm run dev` + Horizon via concurrently |
| `composer run setup` | `composer install`, `.env` copy, `key:generate`, `migrate`, `db:seed`, `npm install`, `npm run build`, `scout:import App\\Models\\School` |
| `composer run fresh` | `migrate:fresh --seed` — full reset (dev only) |

---

## 13. Services Layer

### SchoolService (app/Services/SchoolService.php)

Handles school creation, update, status transitions, and cached counter resets.

Key methods:
- `createDraft(User $owner, array $data): School`
- `submit(School $school): void` — transitions `draft → pending`, fires `SchoolSubmittedForReview`
- `approve(School $school, User $admin): void` — transitions `pending → active`, sets `verified_at`
- `reject(School $school, User $admin, string $reason): void` — transitions `pending → rejected`
- `suspend(School $school): void`
- `toggleFeatured(School $school, ?string $until = null): void`

---

### SearchService (app/Services/SearchService.php)

Wraps Laravel Scout + Meilisearch for school discovery.

Key methods:
- `search(string $query, array $filters = [], int $perPage = 15): LengthAwarePaginator`
- `buildFilters(array $params): array` — converts URL params to Meilisearch filter string
- `suggest(string $query, int $limit = 8): Collection` — instant search suggestions

---

### InquiryService (app/Services/InquiryService.php)

Handles inquiry creation and response workflow.

Key methods:
- `create(School $school, array $data, ?User $user): Inquiry`
- `markAsRead(Inquiry $inquiry): void`
- `respond(Inquiry $inquiry, string $response, User $responder): void`
- `getUnreadCount(School $school): int`

---

### ReviewService (app/Services/ReviewService.php)

Handles review submission, moderation, and rating cache updates.

Key methods:
- `submit(School $school, User $user, array $data): Review`
- `approve(Review $review, User $admin): void` — triggers `School::updateRatingCache()`
- `reject(Review $review, User $admin): void`
- `flag(Review $review): void`
- `addOwnerResponse(Review $review, string $response): void`
- `markHelpful(Review $review, User $user): void`

---

### SubscriptionService (app/Services/SubscriptionService.php)

Handles plan activation, renewal, and expiry.

Key methods:
- `activate(School $school, Plan $plan, Payment $payment): Subscription`
- `renew(Subscription $subscription): Subscription`
- `expire(Subscription $subscription): void` — fires `SubscriptionExpired` event
- `getActiveSubscription(School $school): ?Subscription`
- `getExpiringSubscriptions(int $daysAhead = 14): Collection`

---

### PaymentService (app/Services/PaymentService.php)

Wraps Flutterwave gateway for mobile money and card payments.

Key methods:
- `initiatePayment(School $school, Plan $plan): array` — returns Flutterwave checkout URL
- `verifyPayment(string $transactionId): Payment`
- `handleWebhook(array $payload): void`
- `recordManualPayment(School $school, Plan $plan, array $data): Payment` — for admin-recorded bank transfers

---

### NotificationService (app/Services/NotificationService.php)

Sends database and email notifications for key events.

Key methods:
- `notifyAdminNewSubmission(School $school): void`
- `notifyOwnerApproved(School $school): void`
- `notifyOwnerRejected(School $school, string $reason): void`
- `notifyOwnerNewInquiry(Inquiry $inquiry): void`
- `notifyOwnerNewReview(Review $review): void`
- `notifyOwnerSubscriptionExpiring(Subscription $subscription, int $daysLeft): void`
- `notifyUserInquiryResponse(Inquiry $inquiry): void`

---

### ActivityLogService (app/Services/ActivityLogService.php)

Thin wrapper around Spatie Activity Log for consistent logging across the app.

Key methods:
- `log(string $action, ?Model $subject, ?User $causer, array $properties = []): void`
- `logSchoolAction(string $action, School $school, User $causer): void`
- `logAdminAction(string $action, Model $subject, User $admin): void`

---

### SitemapService (app/Services/SitemapService.php)

Generates XML sitemap using Spatie Sitemap.

Key methods:
- `generate(): void` — writes `public/sitemap.xml`
- Includes: homepage, all active schools, all districts, all published articles, static pages

---

### BrandingConfigServiceProvider (app/Providers/BrandingConfigServiceProvider.php)

Loads brand settings from `settings` table on every request and makes them available as `config('brand.*')`. Used by layouts to inject CSS variables. Includes a Redis cache layer (TTL: 60 minutes) to avoid per-request DB reads.

---

## 14. Known Gaps & TODOs

| Area | Priority | Description |
|------|----------|-------------|
| **Map provider** | High | Wireframe shows Google Maps; budget constraint may require Leaflet + OpenStreetMap. Decision needed before school detail page is built. Geocoding on school save is also Google-dependent. |
| **Meilisearch fallback** | High | Search falls back to `LIKE` query but this does not handle typos or relevance scoring. Fallback should be documented and tested under Meilisearch downtime. |
| **Fee currency display** | Medium | All fees stored in UGX. If school enters fees in USD (international schools), no conversion logic exists. A `currency` flag per fee record exists but display normalisation is not built. |
| **Guest inquiry rate limiting** | High | Guest inquiries (no user_id) are unauthenticated. Rate limiting by IP must be applied to `InquiryController@store` to prevent spam. |
| **Review verification** | Medium | `is_verified` flag exists on reviews but no verification mechanism is implemented. Plan: email-challenge or admin-manual toggle. |
| **School owner self-registration** | Medium | The submission wizard is built but the school owner panel requires the owner to already have a school record. The "Register Your School" flow (user creation + school draft creation in one flow) needs a dedicated controller. |
| **Filament owner panel theming** | Low | Filament 4 panels need custom CSS to match the public site's indigo/amber palette. The default Filament theme will be noticeable. |
| **Compare state persistence** | Low | Compare school list is stored in `localStorage`. This means the compare bar disappears on full page load if Alpine initialises slowly. Consider storing in Livewire session instead. |
| `app/Models/BaseModel.php` | Low | Stub — no additional logic, extends Eloquent directly. Add `SoftDeletes` consideration for School and User models. |
| **Image optimisation on upload** | Medium | `intervention/image` is installed but no automatic WebP conversion or responsive image generation is configured on Spatie MediaLibrary conversions. Must define conversions in `School::registerMediaConversions()`. |
| **Subscription auto-renewal** | Low | `subscriptions.auto_renew` column exists but no auto-renewal job or payment trigger is implemented. Manual renewal only for MVP. |
| **Multi-country expansion** | Future | `districts` and `regions` tables are Uganda-specific. Expansion to Kenya/Tanzania requires a `countries` table and scoping all geographic queries. Schema note: add `country_id` to `districts` when expansion begins. |
| **Article author workflow** | Low | Articles can only be authored by admin users. A future contributor/guest author role and editorial approval flow is not modelled. |
| **Analytics deeper than view counts** | Medium | `school_views` tracks raw views. No UTM source tracking, device type, or search term attribution is captured. Needed for owner analytics to be meaningful. |
| **Horizon access control** | High | Laravel Horizon is installed but the `HorizonServiceProvider` gate is not configured. In production this would expose the queue dashboard publicly. |

---

*End of documentation. Generated from FindURSCH wireframe v4 and build reference on 2026-06-07.*
*Platform: Laravel 12 + Filament 4 + Livewire 3 + Tailwind CSS 4 + Meilisearch · Uganda-first · East Africa expansion ready*
