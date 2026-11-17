<?php

namespace App\Filament\Widgets;

use App\Models\Inquiry;
use App\Models\Review;
use App\Models\School;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Verified schools', School::query()
                ->where('is_demo', false)
                ->where('status', 'active')
                ->where('verification_status', 'verified')
                ->count())
                ->description('Real school listings approved for the public')
                ->descriptionIcon('heroicon-m-academic-cap'),

            Stat::make('Demo listings', School::query()->where('is_demo', true)->count())
                ->description('Fictional sample profiles, not verified')
                ->descriptionIcon('heroicon-m-beaker'),

            Stat::make('Pending school reviews', School::query()
                ->where('is_demo', false)
                ->where('status', 'pending')
                ->count())
                ->description('Real school listings awaiting an admin decision')
                ->descriptionIcon('heroicon-m-clock'),

            Stat::make('Pending reviews', Review::query()->where('status', 'pending')->count())
                ->description('Parent and student reviews awaiting moderation')
                ->descriptionIcon('heroicon-m-star'),

            Stat::make('New inquiries', Inquiry::query()->where('status', 'new')->count())
                ->description('School inquiries awaiting a response')
                ->descriptionIcon('heroicon-m-inbox'),

            Stat::make('Registered users', User::query()->count())
                ->description('All account types')
                ->descriptionIcon('heroicon-m-users'),
        ];
    }
}
