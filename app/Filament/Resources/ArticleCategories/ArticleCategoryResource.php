<?php

namespace App\Filament\Resources\ArticleCategories;

use App\Filament\Resources\ArticleCategories\Pages\ListArticleCategories;
use App\Models\ArticleCategory;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ArticleCategoryResource extends \Filament\Resources\Resource
{
    protected static ?string $model = ArticleCategory::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content';
    }

    public static function getNavigationLabel(): string
    {
        return 'Article categories';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-tag';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true),
            Textarea::make('description')->rows(3)->columnSpanFull(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticleCategories::route('/'),
        ];
    }
}
