<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'FindURSCH — Uganda School Discovery Platform' }}</title>
    <meta name="description" content="{{ $description ?? 'Discover, compare, and apply to the best schools in Uganda.' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-white text-gray-900 antialiased" style="font-family: 'Inter', sans-serif;">

    {{-- NAV --}}
    <header x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false" class="site-header sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <span class="inline-flex w-9 h-9 rounded-btn items-center justify-center bg-primary text-white font-bold">F</span>
                        <span class="font-semibold text-lg" style="font-family: 'DM Serif Display', serif;">FindURSCH</span>
                    </a>
                    <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                        <a href="{{ route('schools.index') }}" class="transition-colors duration-200 hover:text-primary">Schools</a>
                        <a href="{{ route('articles.index') }}" class="transition-colors duration-200 hover:text-primary">Articles</a>
                        <a href="{{ route('compare') }}" class="transition-colors duration-200 hover:text-primary">Compare</a>
                        <a href="{{ route('about') }}" class="transition-colors duration-200 hover:text-primary">About</a>
                        <a href="{{ route('contact') }}" class="transition-colors duration-200 hover:text-primary">Contact</a>
                    </nav>
                </div>
                <div class="flex items-center gap-3">
                    @auth
                        @if (auth()->user()->hasRole('admin'))
                            <a href="{{ url('/admin') }}" class="text-sm font-medium text-gray-600 hover:text-primary">Admin</a>
                        @elseif (auth()->user()->hasRole('school_owner'))
                            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-primary">School dashboard</a>
                        @else
                            <a href="{{ route('user.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-primary">My Account</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-primary">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-primary">Login</a>
                        <a href="{{ route('register') }}" class="btn-primary text-sm">Sign up</a>
                    @endauth

                    <button type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-700 transition-colors hover:border-primary hover:text-primary md:hidden"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        :aria-expanded="mobileMenuOpen"
                        aria-controls="mobile-primary-navigation"
                        aria-label="Toggle navigation">
                        <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                        <svg x-cloak x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>
            </div>

            <div id="mobile-primary-navigation" x-cloak x-show="mobileMenuOpen" x-transition class="border-t border-gray-100 py-3 md:hidden">
                <nav aria-label="Mobile navigation" class="grid gap-1 text-sm font-medium text-gray-700">
                    <a @click="mobileMenuOpen = false" href="{{ route('schools.index') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">Schools</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('articles.index') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">Articles</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('compare') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">Compare schools</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('about') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">About FindurSch</a>
                    <a @click="mobileMenuOpen = false" href="{{ route('contact') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">Contact</a>
                    @guest
                        <a @click="mobileMenuOpen = false" href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">Login</a>
                        <a @click="mobileMenuOpen = false" href="{{ route('register') }}" class="rounded-lg px-3 py-2.5 hover:bg-primary-50 hover:text-primary">Create account</a>
                    @endguest
                </nav>
            </div>
        </div>
    </header>

    <div x-data="compareBar" x-cloak x-show="schools.length > 0" class="fixed inset-x-0 bottom-0 z-50 border-t border-gray-200 bg-white/95 shadow-lg backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <p class="text-sm font-medium text-gray-700"><span x-text="schools.length"></span> of 3 schools selected to compare</p>
            <div class="flex items-center gap-3">
                <button type="button" @click="clear()" class="text-sm font-medium text-gray-500 hover:text-red-600">Clear</button>
                <a :href="'/compare?schools=' + encodeURIComponent(schools.join(','))" class="btn-primary text-sm">Compare schools</a>
            </div>
        </div>
    </div>

    {{-- MAIN --}}
    <main class="min-h-screen">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-20 border-t border-gray-100 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <span class="inline-flex w-9 h-9 rounded-btn items-center justify-center bg-primary text-white font-bold">F</span>
                        <span class="font-semibold text-lg" style="font-family: 'DM Serif Display', serif;">FindURSCH</span>
                    </div>
                    <p class="text-sm text-gray-600">Uganda's school discovery, comparison, and admissions platform.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Explore</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="{{ route('schools.index') }}" class="hover:text-primary">All Schools</a></li>
                        <li><a href="{{ route('articles.index') }}" class="hover:text-primary">Articles</a></li>
                        <li><a href="{{ route('compare') }}" class="hover:text-primary">Compare</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Company</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li><a href="{{ route('about') }}" class="hover:text-primary">About</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-primary">Contact</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-primary">Privacy</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-primary">Terms</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-3">Get in touch</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>hello@findursch.ug</li>
                        <li>+256 700 000000</li>
                        <li>Kampala, Uganda</li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 pt-6 border-t border-gray-200 text-sm text-gray-500 text-center">
                © {{ date('Y') }} FindURSCH. All rights reserved.
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
