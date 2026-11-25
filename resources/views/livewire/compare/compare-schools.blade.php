@extends('layouts.app')

@section('content')
<x-site-page-hero
    eyebrow="School comparison"
    title="Compare Schools"
    description="Review school details side by side and compare up to three schools on fees, curriculum, facilities and other key information."
    :breadcrumbs="[['label' => 'Home', 'url' => route('home')], ['label' => 'Compare']]"
/><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
    @if($schools->count() === 0)
        <div class="text-center py-20">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h2 class="text-xl font-semibold mb-2">No schools to compare</h2>
            <p class="text-gray-500 mb-6">Add schools from the search results or school detail pages</p>
            <a href="{{ route('schools.index') }}" class="btn-primary">Browse Schools</a>
        </div>
    @else
        <div class="flex justify-between items-center mb-6">
            <p class="text-sm text-gray-600">{{ $schools->count() }} of 3 schools selected</p>
            @if($schools->count() === 3)
                <button wire:click="clear" class="text-sm text-red-600 hover:underline">Clear all</button>
            @endif
        </div>

        <div class="overflow-x-auto rounded-card border border-gray-200 bg-white">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="p-4 text-left font-semibold">Feature</th>
                        @foreach($schools as $school)
                            <th class="p-4 text-center">
                                <a href="{{ route('schools.show', $school) }}" class="font-semibold hover:text-primary">{{ $school->name }}</a>
                                @if($school->is_featured)
                                    <span class="inline-block ml-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-badge px-2 py-0.5 text-xs font-bold">Featured</span>
                                @endif
                                <button wire:click="remove({{ $school->id }})" class="ml-2 text-gray-400 hover:text-red-500 text-sm">Remove</button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr><td class="p-4 font-medium">Type</td>@foreach($schools as $s)<td class="p-4 text-center">{{ $s->type?->name }}</td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">District</td>@foreach($schools as $s)<td class="p-4 text-center">{{ $s->district?->name }}</td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Curriculum(s)</td>@foreach($schools as $s)<td class="p-4 text-center"><div class="flex flex-wrap justify-center gap-1">@foreach($s->curricula as $c)<span class="bg-primary-50 text-primary-700 rounded-badge px-2 py-0.5 text-xs font-semibold">{{ $c->abbreviation }}</span>@endforeach</div></td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Boarding</td>@foreach($schools as $s)<td class="p-4 text-center capitalize">{{ $s->boarding_type }}</td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Gender Policy</td>@foreach($schools as $s)<td class="p-4 text-center capitalize">{{ $s->gender_policy }}</td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Student Count</td>@foreach($schools as $s)<td class="p-4 text-center">{{ $s->student_count_approx ? number_format($s->student_count_approx) : 'N/A' }}</td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Rating</td>@foreach($schools as $s)<td class="p-4 text-center">@if($s->rating_average > 0)<span class="text-amber-500">★</span> {{ number_format($s->rating_average, 1) }} ({{ $s->reviews_count }})@else No reviews @endif</td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Verified</td>@foreach($schools as $s)<td class="p-4 text-center">@if($s->verification_status === 'verified')<span class="text-green-600 font-semibold">✓ Verified</span>@else <span class="text-gray-400">Unverified</span>@endif</td>@endforeach</tr>
                    <tr class="bg-gray-50"><td class="p-4 font-medium">Estimated Fee Range (per term)</td>@foreach($schools as $s)
                        @php
                            $feeFrom = is_numeric($s->fees_from) && (float) $s->fees_from > 0 ? (float) $s->fees_from : null;
                            $feeTo = is_numeric($s->fees_to) && (float) $s->fees_to > 0 ? (float) $s->fees_to : null;
                        @endphp
                        <td class="p-4 text-center">
                            @if($feeFrom !== null && $feeTo !== null && $feeFrom < $feeTo)
                                UGX {{ number_format(min($feeFrom, $feeTo)) }} – UGX {{ number_format(max($feeFrom, $feeTo)) }}
                            @else
                                Fee range not provided
                            @endif
                        </td>
                    @endforeach</tr>
                    <tr><td class="p-4 font-medium">Facilities</td>@foreach($schools as $s)<td class="p-4 text-center"><div class="flex flex-wrap justify-center gap-1">@foreach($s->facilities->take(6) as $f)<span class="bg-gray-100 text-gray-700 rounded-badge px-2 py-0.5 text-xs">{{ $f->name }}</span>@endforeach @if($s->facilities->count() > 6)<span class="bg-primary-50 text-primary-700 rounded-badge px-2 py-0.5 text-xs">+{{ $s->facilities->count() - 6 }} more</span>@endif</div></td>@endforeach</tr>
                    <tr><td class="p-4 font-medium">Description</td>@foreach($schools as $s)<td class="p-4"><p class="text-sm text-gray-600 line-clamp-3">{{ $s->description }}</p></td>@endforeach</tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection