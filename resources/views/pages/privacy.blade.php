@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <p class="text-sm font-semibold uppercase tracking-wider text-primary">FindurSch policies</p><h1 class="mt-2 text-4xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Privacy policy</h1><p class="mt-3 text-sm text-gray-500">Last updated: 17 November 2026</p>
    <div class="prose prose-gray mt-8 max-w-none">
        <p>This page explains the information FindurSch uses to operate its school discovery and account services. It is a platform privacy notice, not a substitute for advice on applicable Ugandan data-protection requirements.</p>
        <h2>Information we collect</h2><ul><li><strong>Account information:</strong> name, email address, phone number and account profile details you provide.</li><li><strong>School interactions:</strong> saved schools, school inquiries, reviews and responses associated with your account.</li><li><strong>Messages:</strong> information you submit through the contact form.</li><li><strong>Technical visit information:</strong> when a school profile is opened, the platform currently records the school viewed, date and time, IP address, session identifier and referring page where available.</li></ul>
        <h2>How information is used</h2><p>Information is used to operate user accounts, show saved schools and account history, send school inquiries to the appropriate listing, moderate reviews, respond to contact messages, protect the service and understand how school listings are used.</p>
        <h2>School information and public content</h2><p>School profiles and approved reviews are intended to be visible to visitors. Do not include private, sensitive or unnecessary personal information in a public review or school listing.</p>
        <h2>Service providers</h2><p>The platform uses hosting and database infrastructure to operate the website. These providers process information needed to host the application and store its records. Service integrations and their settings may change as the platform develops.</p>
        <h2>Retention and security</h2><p>Information is retained for as long as it is needed to provide the service, maintain account and school records, address disputes and meet applicable obligations. Technical and organisational safeguards are used, but no internet service can guarantee absolute security.</p>
        <h2>Your choices and requests</h2><p>You can update profile details through your account. For requests to access, correct or delete information, use the contact page and identify the account or record involved. Some records may need to be retained where required for security, moderation or legal reasons.</p>
        <h2>Children</h2><p>FindurSch is intended for parents, guardians, adult school representatives and other users who are permitted to use the service. Do not submit a child's unnecessary personal information through public forms or reviews.</p>
        <h2>Changes</h2><p>This notice may be updated as the platform's features and data practices change. The date at the top indicates the latest revision.</p>
        <p>For privacy questions, please use the <a href="{{ route('contact') }}">contact page</a>.</p>
    </div>
</section>
@endsection
