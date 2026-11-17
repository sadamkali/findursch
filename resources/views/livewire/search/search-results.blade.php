@extends('layouts.app')

@section('content')
<div class="bg-gray-50 py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-normal mb-4" style="font-family: 'DM Serif Display', serif;">
            Search Results
            @if($q) <span class="text-primary font-normal">for "{{ $q }}"</span> @endif
        </h1>
        <p class="text-gray-600">{{ $schools->total() }} schools found</p>
    </div>
</div>

<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid lg:grid-cols-[280px_1fr] gap-8">
        <aside class="space-y-6">
            <div class="bg-white rounded-card border border-gray-100 p-5">
                <h3 class="font-semibold mb-3">Search</h3>
                <form wire:submit.prevent="search">
                    <input wire:model.live.debounce.300ms="q" type="text" placeholder="Search schools..." class="w-full rounded-input border border-gray-300 px-3 py-2 text-sm" />
                </form>
            </div>
        </aside>

        <div>
            @if($q && strlen($q) >= 2)
                @if($schools->count() > 0)
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($schools as $school)
                            @include('components.school-card', ['school' => $school])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $schools->links() }}</div>
                @else
                    <div class="text-center py-20 bg-gray-50 rounded-card">
                        <p class="text-gray-600">No schools match "{{ $q }}".</p>
                    </div>
                @endif
            @else
                <div class="text-center py-20">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <h2 class="text-xl font-semibold mb-2">Search for schools</h2>
                    <p class="text-gray-500">Enter a school name, district, or curriculum to get started</p>
                </div>
            @endif

            @if($districts->count() > 0)
                <div class="mt-10">
                    <h3 class="font-semibold mb-3">Matching Districts</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($districts as $d)
                            <a href="{{ route('district.show', $d) }}" class="p-4 rounded-card bg-white border border-gray-100 hover:border-primary transition">
                                <h4 class="font-semibold">{{ $d->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $d->region?->name }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection