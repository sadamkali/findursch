<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ActivityLogResource extends \Filament\Resources\Resource
{
    protected static ?string $model = ActivityLog::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Platform';
    }

    public static function getNavigationLabel(): string
    {
        return 'Activity log';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-list';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('action')->searchable()->badge(),
                TextColumn::make('description')->limit(70)->wrap(),
                TextColumn::make('user_id')->label('User ID')->sortable(),
                TextColumn::make('subject_type')->label('Record type')->toggleable(),
                TextColumn::make('subject_id')->label('Record ID')->toggleable(),
                TextColumn::make('ip_address')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
