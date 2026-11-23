@extends('layouts.app')

@section('content')
<x-site-page-head :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Terms and conditions']]" eyebrow="FindURSCH policies" :show-ad="false">
    <x-slot:title>Terms and conditions</x-slot:title>
    <x-slot:description>Last updated: 17 November 2026</x-slot:description>
</x-site-page-head>
<section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="prose prose-gray max-w-none">
        <p>These terms describe the basic rules for using FindurSch. By accessing the platform, you agree to use it lawfully and responsibly.</p>
        <h2>Purpose of the platform</h2><p>FindurSch helps users discover and compare school information, save listings, send inquiries and read or submit reviews. It is an information and communication service. It does not itself enrol learners or guarantee admission, school quality, availability or outcomes.</p>
        <h2>School information</h2><p>We aim to review school listings, but information may be incomplete, outdated or changed by a school. A verified label indicates that a listing has passed the platform's review process; it is not a guarantee of every statement or a government accreditation. Confirm fees, admissions, facilities and current requirements directly with the school.</p>
        <h2>Accounts</h2><p>You must provide accurate account details, protect your password and keep your contact information current. You are responsible for activity carried out through your account. Do not impersonate another person or attempt to obtain access to another user's account.</p>
        <h2>School-owner listings</h2><p>School representatives must have authority to submit information on behalf of a school. New listings and changes may require review before becoming publicly visible. FindurSch may reject, hide or suspend inaccurate, misleading or inappropriate listings.</p>
        <h2>Reviews and user content</h2><p>Reviews must reflect genuine experience, be respectful and avoid private information, harassment, threats, discriminatory content or knowingly false statements. Reviews may be moderated, edited for safety or removed where they breach these terms. A school response does not replace the reviewer's own account.</p>
        <h2>Acceptable use</h2><p>You must not misuse the platform, interfere with its operation, attempt unauthorised access, introduce malicious code, scrape or harvest personal information unlawfully, or use the service to mislead or harm others.</p>
        <h2>Subscriptions and payments</h2><p>Subscription and paid promotion features are not currently active. Do not make a payment to an individual or third party claiming to activate a FindurSch package. Any future paid service will be presented with its terms before payment is enabled.</p>
        <h2>Availability and changes</h2><p>The service may be updated, interrupted or changed as it develops. We will take reasonable steps to protect platform records, but uninterrupted availability cannot be guaranteed.</p>
        <h2>Contact</h2><p>For questions about these terms, use the <a href="{{ route('contact') }}">contact page</a>.</p>
    </div>
</section>
@endsection
