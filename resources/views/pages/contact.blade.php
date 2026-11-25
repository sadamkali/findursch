@extends('layouts.app')

@section('content')
<x-site-page-hero
    eyebrow="FindURSCH team"
    title="Contact us"
    description="Send a message to the FindURSCH team about school listings, account access or general questions."
    :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Contact']]"
/>
<section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    @if(session('success'))<div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('contact.store') }}" class="space-y-5 rounded-xl border border-gray-200 bg-white p-6">@csrf
        <div><label for="name" class="mb-1 block text-sm font-medium text-gray-700">Your name</label><input id="name" name="name" value="{{ old('name') }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="email" class="mb-1 block text-sm font-medium text-gray-700">Email address</label><input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="subject" class="mb-1 block text-sm font-medium text-gray-700">Subject</label><input id="subject" name="subject" value="{{ old('subject', request('subject')) }}" required maxlength="255" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">@error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <div><label for="message" class="mb-1 block text-sm font-medium text-gray-700">Message</label><textarea id="message" name="message" required minlength="10" maxlength="10000" rows="6" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm">{{ old('message') }}</textarea>@error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
        <button type="submit" class="btn-primary">Send message</button>
    </form>
    <p class="mt-4 text-xs leading-5 text-gray-500">Your message is stored so the FindurSch team can review it. Email delivery notifications will be enabled when the production mail service is configured.</p>
</section>
@endsection
