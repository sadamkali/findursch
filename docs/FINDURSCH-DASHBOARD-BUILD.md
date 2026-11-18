# FindurSch Dashboard Build Specification

This file is the dashboard-only extraction of the FindurSch full software build plan. It is the implementation reference for the school-owner, administrator, platform-owner and parent/guardian dashboard areas. The public website and school-registration wizard are outside this document's build scope.

The source plan is FINDURSCH-FULL-BUILD.md. Dashboard layouts and workflows are based on the uploaded reference screenshots. FindurSch must retain its own brand, words, pricing and business rules.

## 1. Dashboard roles and boundaries

| Role | Route | Responsibility |
|---|---|---|
| School owner | /dashboard | Manage only the school listings assigned to the signed-in owner. |
| Administrator | /admin | Moderate listings, reviews and content; manage operational queues. |
| Platform owner | /admin with additional permissions | Manage staff, plans, prices, payments, providers, site settings and audit records. |
| Parent / guardian | /account | Manage saved schools, comparisons, enquiries, reviews and personal details. |

A school owner must never access another school's data by changing a URL or submitted school ID. The server must scope every read and write by the authenticated owner's school ownership. Administrators and the platform owner have broader access according to their permission set.

The existing Laravel role key admin should remain compatible while administrator and platform-owner permissions are formalised. Do not rename existing roles or weaken existing access checks as part of a visual redesign.

## 2. Shared dashboard design system

Use the existing FindurSch palette and typography:

- Brand: #0c5a49; dark brand: #083f33; deepest brand: #052a22.
- Accent: #f0b030; darker accent: #d99a1a.
- Tint: #f1f6f3; line: #e4e8e6; text: #1b2420; muted text: #5d6b65.
- Dashboard page background: #f4f6f5.
- Dashboard card radius: 14px. Public cards keep their existing 10px radius.
- Display font: Playfair Display / Georgia. Body font: Inter / system sans-serif.

### Shared shell

- Fixed 280px dark-brand sidebar on desktop.
- Sidebar contains the FindurSch logo, current user or school identity, email, plan pill, grouped navigation and active-item state.
- Main content uses the grey dashboard background and white cards.
- Topbar uses a serif page title, a short subtitle, and one consistent Log out control.
- Below 1000px, the sidebar becomes a drawer; the page must not create horizontal overflow.
- Use one line-icon set consistently. Do not mix emoji icons or multiple icon styles.

### Shared components

Build reusable components rather than styling every screen independently:

1. Dashboard sidebar and responsive mobile drawer.
2. Dashboard topbar.
3. Stat tile: icon, large value, label and a Manage link.
4. Status pill: approved/published (green), pending (amber), draft (grey), rejected (red), premium (gold).
5. Amber feature notice with a clear Upgrade link.
6. Dark gradient welcome/hero banner.
7. Checklist row with a right-aligned action.
8. Included/locked feature chip.
9. Option tile with selected state.
10. Dashed upload zone with file guidance.
11. Text counter for words and characters.
12. Credit balance card and transaction rows.
13. Locked-feature panel.
14. Consistent empty state, confirmation and error states.

Keep the interface compact and purposeful. Use the same spacing, card borders, typography and button hierarchy across dashboard roles.

## 3. School-owner dashboard

### 3.1 Navigation and routes

The planned school-owner route map is:

| Navigation item | Route |
|---|---|
| Dashboard | /dashboard |
| My School Profile | /dashboard/profile |
| Edit School Information | /dashboard/school |
| Cover Photos | /dashboard/cover-photos |
| School Gallery | /dashboard/gallery |
| Facilities & Amenities | /dashboard/facilities |
| Academic Programmes | /dashboard/programmes |
| Admissions Information | /dashboard/admissions |
| SMS and Email | /dashboard/messages |
| WhatsApp | /dashboard/messages/whatsapp |
| Post an Event | /dashboard/events |
| Post School News | /dashboard/news |
| Jobs | /dashboard/opportunities/jobs |
| Scholarships | /dashboard/opportunities/scholarships |
| Advertisements | /dashboard/ads |
| Videos | /dashboard/videos |
| Subscription & Upgrade | /dashboard/subscription |
| Payment History | /dashboard/payments |
| Account Settings | /dashboard/settings |
| Enquiries | /dashboard/enquiries |

Expandable groups are used for Bulk Messages and Opportunities. Routes that are not implemented must be shown as unavailable or omitted from active navigation; do not create clickable dead links.

The current application uses /owner and /owner/.... Introduce /dashboard as the canonical school-owner area while preserving existing bookmarks and route-name compatibility during migration. Move each existing page once; do not create duplicate implementations.

### 3.2 Dashboard home

Show these sections in order:

1. **Welcome banner:** school badge, welcome message, school name, level, district, day/boarding chips, current status and buttons for View public profile and Edit profile.
2. **Upgrade card:** explain Standard and Premium benefits. Prices shown in the original reference (Standard UGX 100,000/year and Premium UGX 380,000/year) are placeholders, not approved FindurSch prices. Do not activate or collect payment using them until pricing is confirmed.
3. **Account activated card:** dismissible welcome message and a profile-completion checklist. Persist dismissal per user.
4. **Four stat tiles:** profile views, cover photos used/allowed, gallery images used/allowed and events posted. Each tile links to its management page.
5. **Quick actions:** add cover photo, add gallery image, add event, create advertisement and edit profile.
6. **Recent activity:** real recent actions only; show a useful empty state when there is no activity.

Never display invented visitor figures or activity counts.

### 3.3 My School Profile

- Show school identity, school level, district, plan and a View my public page action.
- Calculate completeness from actual saved fields, not a hard-coded percentage.
- Use eight checklist items, each worth 12.5%:
  1. Add more information about the school.
  2. Upload the school logo.
  3. Upload school photographs.
  4. Add a cover photo.
  5. Complete academic programmes.
  6. Update facilities and amenities.
  7. Provide admission information.
  8. Add the location and map.
- Display package features as included or locked, with an upgrade action for locked public features.

### 3.4 Edit School Information

Use one long form with clearly separated cards and a single Save changes action:

| Section | Fields |
|---|---|
| Basic information | School name, school type, curriculum, ownership, gender policy, day/boarding, religion or faith |
| Contact information | School phone, WhatsApp number, school email, website, YouTube link |
| Fee structure | Minimum and maximum fees in UGX |
| Location | Region, district, county, sub-county, village/area, P.O. Box, physical address, Google Maps link and directions |
| Social media | Facebook, X/Twitter and Instagram |
| School description | Description with live 250-word and 1,500-character counters |
| Images | Logo and cover photo |

Required fields must be clearly marked. Validate the data on the server, not only in the browser.

### 3.5 Cover Photos and School Gallery

- Cover Photos: maximum five images; image, optional caption and upload action.
- School Gallery: maximum ten images; allow up to five files per upload.
- Accept JPG, PNG and WebP.
- Show thumbnails, upload progress, delete and drag-to-reorder controls, set-as-main and alt text.
- Compress and resize images client-side where possible, then validate and process them on the server.
- Use object storage or the existing media-library integration; never rely on a serverless container's local disk for persistent uploads.
- The ten-image gallery limit is the selected rule. Do not use the contradictory five-image count shown in the reference screenshot.

### 3.6 Facilities and Amenities

Allow owners to save facility selections even when their current plan does not display the facilities publicly. Show an amber notice explaining the plan requirement.

Use one master list of 24 facilities:

1. Library
2. Science Laboratories
3. ICT / Computer Lab
4. Boarding Facilities
5. School Transport
6. Sick Bay / Clinic
7. Sports Fields
8. Swimming Pool
9. Music & Drama
10. Chapel / Prayer Space
11. Mosque / Prayer Room
12. Dining Hall
13. Security & CCTV
14. Electricity / Solar
15. Clean Water
16. Special Needs Support
17. Wi-Fi
18. Playground
19. Day Care
20. Assembly Hall
21. Home Economics Room
22. Agriculture Farm
23. Workshops (Technical)
24. Counselling Office

Also provide an Other facilities textarea with one item per line. The registration wizard may show a smaller subset, but the dashboard and database must use this one master list.

### 3.7 Academic Programmes

Provide a school-owned programme manager. Each programme must support the programme name, level, duration, curriculum where applicable and description. Owners can add, edit and remove programmes belonging only to their own school. Link fee records to programmes where relevant.

### 3.8 Admissions Information

Use an admissions card with:

- Admissions are open toggle.
- Classes admitting.
- Application deadline.
- Application or interview fee.
- Admissions contact.
- Requirements, one per line.
- How to apply.
- Save admissions information action.

Show an upgrade notice where the current package does not include public display. Saving data must remain possible.

### 3.9 Enquiries

The owner can review enquiries for their own school, see the parent/contact details and message, mark an enquiry as read, respond and archive it. Keep a record of the response and response timestamp. Paginate long lists and make statuses easy to scan.

### 3.10 SMS and Email

The messaging screen has a main form and a right-hand information column.

**New message form**

- Channel: SMS or Email.
- Include parents who enquired (opt-in selection).
- Upload a CSV or Excel contact list with a sample download.
- Or paste recipient phone numbers/emails, one per line.
- Message textarea with live character and SMS-part counter.
- Quick insert chips for common school messages.
- Support the {first_name} placeholder with a neutral fallback where a name is missing.
- Save as template option.
- Send message action.
- Exclude unsubscribed recipients automatically.

**Credit summary**

- Show free monthly credits, purchased credits and remaining balance separately.
- Show a transaction history/ledger.
- Explain monthly reset and expiry rules.
- Provide a purchase form only after the platform owner configures a real payment provider and approved pricing.

The screenshot's 20/150/1,000 monthly free credits and UGX 35 per SMS are reference values only. They are not approved production settings.

### 3.11 WhatsApp

On plans without WhatsApp, show a locked-feature panel describing the feature and the required upgrade. Do not simulate bulk WhatsApp sending.

When enabled, the screen requires official WhatsApp Business Platform integration, opt-in recipients, approved templates and delivery/read reporting. A normal WhatsApp personal account is not a bulk messaging integration.

### 3.12 Other owner modules

Build Events, School News, Jobs, Scholarships, Advertisements and Videos with consistent form/list/empty-state patterns. Owners submit content for moderation where required. Subscription, Payment History and Account Settings show actual account data and functional actions. Do not expose unfinished modules as working links.

## 4. Packages and feature access

Use Basic, Standard and Premium plans, but do not treat any reference price or quota as approved business policy.

Use the "fill free, show paid" rule:

- Owners may enter and save profile details, facilities, admissions and other package-related content.
- The owner dashboard explains which public features are included and which are locked.
- The public school profile displays only features enabled for the school's active plan.
- Do not delete saved content when a school downgrades; hide display-only features and reveal them again if the school upgrades.
- Feature checks must be server-side and reusable, for example a School feature service such as hasFeature('gallery').
- Every paid action must check the subscription status and dates on the server.

Plan fields and feature matrix must be centrally configured. Do not hard-code prices or message quotas into Blade templates.

## 5. Administrator dashboard

Keep the administrator area at /admin. Use a consistent dark sidebar, topbar, overview tiles, filterable tables, bulk actions and clear status pills. The existing Filament panel is the current admin foundation; extend and theme it rather than creating a second admin framework.

### Administrator overview

Show operational queues:

- Schools awaiting review.
- Reviews awaiting moderation.
- Stories awaiting approval or publication.
- Advertisements awaiting moderation.
- Flagged or problematic messages.
- New enquiries/contact messages requiring attention.

All counts must be database-backed and link to the corresponding queue.

### Administrator modules

| Module | Capabilities |
|---|---|
| Schools | Review, approve, verify, feature, suspend, edit school records and inspect package status |
| Reviews | Approve/reject reviews and inspect spam flags |
| Articles and news | Create, edit, publish and moderate submitted stories |
| Events, Jobs, Scholarships | Moderate and publish content |
| Ads | Review requests, upload creatives, schedule, pause and stop placements |
| Enquiries | Audit enquiries and resolve disputes |
| Messaging | Inspect campaign logs, abuse reports and block/limit a school |
| Taxonomy | Manage regions, districts, school types, curricula, facilities and categories |
| Inbox | Manage contact messages and newsletter subscribers |

Administrators may carry out moderation and operational duties but must not automatically gain access to platform-owner financial settings or staff administration.

## 6. Platform-owner dashboard

The platform owner has the administrator capabilities plus restricted platform-wide controls:

| Page | Capabilities |
|---|---|
| Overview | Revenue, active paid schools, sign-ups and growth |
| Admin users | Create/remove administrators and assign permissions |
| Plans and pricing | Configure Basic/Standard/Premium, SMS prices, ad prices and quotas |
| Payments | Inspect transactions, invoices, refunds and failed payments |
| Messaging providers | Configure gateway credentials, balances and sender IDs |
| Site settings | Manage logo, contact details, SEO defaults, homepage feature slots and footer links |
| Audit log | See who changed what and when |
| Exports and backups | Export schools, enquiries and subscribers; manage backup processes |

These controls must be owner-only. Provider secrets must be stored securely and never returned in page data or logs.

## 7. Parent / guardian account

Keep the parent account at /account, separate from the school-owner dashboard.

Required screens:

- Overview.
- Saved schools.
- Compare list.
- Enquiries sent with status and school responses.
- Reviews written and review status.
- Profile details and password.

Parents can post reviews but cannot approve reviews, edit schools or view other parents' private enquiries.

## 8. Approval, verification and permissions

Keep approval status separate from verification status:

Draft → Submitted → Under review → Published → Suspended.

Verification is a separate check and badge. A published school may be verified or unverified; a verified badge should only be shown when the verification process has genuinely been completed. The current production visibility rule is stricter and currently requires active + verified; any change to public visibility is a separate business-rule change and must not be smuggled into dashboard styling work.

### Permission matrix

| Action | Platform owner | Administrator | School owner | Parent |
|---|:---:|:---:|:---:|:---:|
| Edit own school | Yes | Yes | Own only | No |
| Edit any school | Yes | Yes | No | No |
| Approve/verify/feature/suspend school | Yes | Yes | No | No |
| Change a school's plan | Yes | No | Request/upgrade own | No |
| Submit events/news/jobs/scholarships | Yes | Yes | Own school, moderated | No |
| Send bulk SMS/email | Yes | No | Own school, within credits | No |
| Send bulk WhatsApp | Yes | No | Premium only | No |
| Buy SMS credits | Yes | No | Own school | No |
| View enquiries | Yes | Audit | Own only | Own only |
| Post a review | Yes | Yes | No | Yes |
| Approve reviews | Yes | Yes | No | No |
| Manage ads | Yes | Yes | Own requests/placements | No |
| Manage plans/prices/payments | Yes | No | View own | No |
| Create/remove administrators | Yes | No | No | No |
| View audit log | Yes | Read-only | No | No |

Enforce access through Laravel policies/gates and role middleware. Hiding buttons is not an authorization control.

## 9. Existing application foundation and gaps

The dashboard build must extend the Laravel 12, Livewire 3, Filament 4 and Spatie-based application already in this repository.

### Existing dashboard code to preserve and extend

- School-owner Livewire components: Dashboard, SchoolEditor, SchoolDetails, Inquiries and Reviews.
- School-owner Blade views for those five screens.
- Parent account Livewire components: Dashboard, SavedSchools, MyInquiries, MyReviews and ProfileEdit.
- Filament admin panel at /admin.
- Admin resources for schools, users, regions, districts, sub-counties, reviews, enquiries, articles, article categories, contact messages, settings and activity logs.
- Existing ownership middleware and owner-access feature tests.
- Existing school, programme, fee, contact, facility, enquiry, review, plan, subscription, payment, activity-log and media-library foundations.

### Known implementation gaps to address

- School-owner area currently uses /owner; the specification expects /dashboard.
- Current owner screens do not yet use the complete shared dashboard shell and navigation.
- Cover photo and gallery management screens, admissions management, messaging/credits and content modules are not complete.
- Events, jobs, scholarships, advertisements, SMS/WhatsApp campaigns, credit ledger, templates and suppression-list models/tables are not present in the current migration inventory.
- A dedicated admissions data structure is not present in the current migration inventory.
- Platform-owner-only permissions and pages are not yet separated from administrator access.
- Parent compare-list management is not a distinct dashboard screen yet.
- Existing SchoolDetails already handles programmes, fees and school contacts; extend it or split it cleanly without duplicating data.

Use the existing Spatie Media Library integration for persistent school images where suitable. Add new migrations/models only for real missing data structures. Do not create unused placeholder tables or dead navigation routes.

## 10. Recommended build order

1. **Dashboard foundation:** shared responsive dashboard shell, sidebar, topbar, navigation, status pills, notices, stat tiles and empty states.
2. **School-owner core:** canonical /dashboard, home, school profile completeness and edit information; keep existing data and route compatibility.
3. **School content:** cover photos, gallery, facilities, programmes, admissions and enquiries.
4. **Admin moderation:** overview queues, school review workflow and review moderation; ensure the owner can submit and track records.
5. **Parent account:** complete saved, compare, enquiries, reviews and profile screens in the same visual system.
6. **Plans and feature access:** central plan-feature rules, subscriptions and package notices; keep reference prices disabled until approved.
7. **Messaging:** SMS/email, credits, imports, templates and opt-outs first; WhatsApp only after an official provider integration.
8. **Content and revenue modules:** events, news, jobs, scholarships, videos, ads, payment history and platform settings.
9. **Platform-owner controls:** staff roles, plans/pricing, providers, audit log, exports and backups.
10. **Hardening:** policies, rate limits, uploads, queues, data privacy, tests, accessibility, responsive checks and performance.

## 11. Build rules

- Preserve current business data and working flows.
- Do not replace or redesign public pages as part of this dashboard-only scope.
- Do not deploy after every small edit; group related, tested work into coherent commits.
- Keep production database migrations safe for the Vercel deployment model; use the documented one-off release process, not per-instance container startup migrations.
- Add or update feature tests for role access, ownership boundaries, saving, moderation and plan restrictions.
- Remove navigation links only when the corresponding screen is not implemented; never leave dead actions.
- Keep unfinished payment and messaging providers disabled until real provider credentials, pricing and policies are configured.
- Before deleting an existing application file, confirm it is not referenced by routes, views, autoloading, deployment scripts or tests.
