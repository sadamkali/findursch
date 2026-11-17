<?php

namespace App\Filament\Resources\Regions;

use App\Filament\Resources\Regions\Pages\ListRegions;
use App\Models\Region;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class RegionResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Region::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Locations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Regions';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-globe-asia-australia';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('slug')->required()->maxLength(120)->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('districts_count')->counts('districts')->label('Districts'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegions::route('/'),
        ];
    }
}
