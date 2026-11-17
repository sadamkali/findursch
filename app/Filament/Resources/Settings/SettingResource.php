<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Models\Setting;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SettingResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Setting::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Platform';
    }

    public static function getNavigationLabel(): string
    {
        return 'Site settings';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->required()->maxLength(255)->unique(ignoreRecord: true),
            TextInput::make('label')->maxLength(255),
            Select::make('group')->options([
                'branding' => 'Branding',
                'seo' => 'SEO',
                'contact' => 'Contact',
                'social' => 'Social',
                'payment' => 'Payment display',
            ])->searchable(),
            Select::make('type')->options([
                'string' => 'Text',
                'json' => 'JSON',
                'boolean' => 'Boolean',
                'integer' => 'Number',
                'html' => 'HTML',
            ])->required(),
            Textarea::make('value')->rows(4)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->searchable()->placeholder('—'),
                TextColumn::make('key')->searchable()->copyable(),
                TextColumn::make('group')->badge(),
                TextColumn::make('type')->badge(),
                TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('group')->options([
                    'branding' => 'Branding',
                    'seo' => 'SEO',
                    'contact' => 'Contact',
                    'social' => 'Social',
                    'payment' => 'Payment display',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('group');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSettings::route('/'),
        ];
    }
}
