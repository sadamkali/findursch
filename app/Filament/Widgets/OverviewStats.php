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
            Stat::make('Schools', School::query()->count())
                ->description('Total school records')
                ->descriptionIcon('heroicon-m-academic-cap'),
            Stat::make('Pending school reviews', School::query()->where('status', 'pending')->count())
                ->description('Awaiting an admin decision')
                ->descriptionIcon('heroicon-m-clock'),
            Stat::make('Pending reviews', Review::query()->where('status', 'pending')->count())
                ->description('Student and parent reviews')
                ->descriptionIcon('heroicon-m-star'),
            Stat::make('New inquiries', Inquiry::query()->where('status', 'new')->count())
                ->description('Awaiting a response')
                ->descriptionIcon('heroicon-m-inbox'),
            Stat::make('Registered users', User::query()->count())
                ->description('All account types')
                ->descriptionIcon('heroicon-m-users'),
        ];
    }
}
