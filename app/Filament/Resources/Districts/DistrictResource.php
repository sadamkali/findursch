<?php

namespace App\Filament\Resources\Districts;

use App\Filament\Resources\Districts\Pages\ListDistricts;
use App\Models\District;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DistrictResource extends \Filament\Resources\Resource
{
    protected static ?string $model = District::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Locations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Districts';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            Select::make('region_id')->relationship('region', 'name')->searchable()->preload()->required(),
            Toggle::make('is_featured')->label('Featured district'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('region.name')->label('Region')->sortable(),
                TextColumn::make('school_count')->label('School count')->numeric()->sortable(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('region');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDistricts::route('/'),
        ];
    }
}
