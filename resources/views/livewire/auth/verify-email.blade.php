@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-xl px-4 py-14 sm:py-20">
    <div class="rounded-2xl border border-gray-200 bg-white p-7 text-center shadow-sm sm:p-9">
        <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-2xl text-primary">✉</div>
        <h1 class="text-3xl font-semibold text-gray-900" style="font-family: 'DM Serif Display', serif;">Verify your email address</h1>
        <p class="mt-3 text-sm leading-6 text-gray-600">
            Before continuing, open the verification link we sent to your email address. If it has not arrived, you can request another one.
        </p>

        @if (session('status'))
            <div class="mt-5 rounded-lg bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="mt-7">
            @csrf
            <button type="submit" class="btn-primary w-full justify-center py-3">Resend verification email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-4">
            @csrf
            <button type="submit" class="text-sm font-medium text-gray-500 hover:text-primary">Sign out</button>
        </form>
    </div>
</section>
@endsection
