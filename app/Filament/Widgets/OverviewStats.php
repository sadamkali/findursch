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
            Stat::make('Published schools', School::query()->publiclyVisible()->count())
                ->description('Listings currently visible to families')
                ->descriptionIcon('heroicon-m-academic-cap'),

            Stat::make('Pending school submissions', School::query()
                ->where('status', 'pending')
                ->count())
                ->description('School listings waiting for an admin decision')
                ->descriptionIcon('heroicon-m-clock'),

            Stat::make('Suspended schools', School::query()->where('status', 'suspended')->count())
                ->description('Listings currently unavailable to the public')
                ->descriptionIcon('heroicon-m-no-symbol'),

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
