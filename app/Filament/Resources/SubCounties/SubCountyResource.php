<?php

namespace App\Filament\Resources\SubCounties;

use App\Filament\Resources\SubCounties\Pages\ListSubCounties;
use App\Models\SubCounty;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SubCountyResource extends \Filament\Resources\Resource
{
    protected static ?string $model = SubCounty::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Locations';
    }

    public static function getNavigationLabel(): string
    {
        return 'Sub-counties';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-map-pin';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('slug')->required()->maxLength(140),
            Select::make('district_id')
                ->relationship('district', 'name')
                ->searchable()
                ->preload()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('district.name')->label('District')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('district');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubCounties::route('/'),
        ];
    }
}
