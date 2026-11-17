<?php

namespace App\Filament\Resources\Articles;

use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ArticleResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Article::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Content';
    }

    public static function getNavigationLabel(): string
    {
        return 'Articles & news';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-newspaper';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            TextInput::make('slug')->required()->maxLength(280)->unique(ignoreRecord: true),
            Select::make('category_id')->label('Category')->relationship('category', 'name')->searchable()->preload(),
            Select::make('author_id')->label('Author')->relationship('author', 'name')->searchable()->preload(),
            Textarea::make('excerpt')->rows(3)->maxLength(1000)->columnSpanFull(),
            RichEditor::make('body')->required()->columnSpanFull(),
            TextInput::make('cover_image')->label('Cover image path or URL')->maxLength(255)->columnSpanFull(),
            TextInput::make('read_time_minutes')->numeric()->minValue(1)->maxValue(120)->default(3),
            Select::make('status')->options([
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived',
            ])->required(),
            Toggle::make('is_featured')->label('Featured article'),
            DateTimePicker::make('published_at')->seconds(false),
            TextInput::make('meta_title')->maxLength(255),
            Textarea::make('meta_description')->rows(2)->maxLength(1000)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->limit(45),
                TextColumn::make('category.name')->label('Category'),
                TextColumn::make('author.name')->label('Author'),
                TextColumn::make('status')->badge(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('published_at')->dateTime()->sortable(),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived',
                ]),
                SelectFilter::make('category_id')->label('Category')->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['category', 'author']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArticles::route('/'),
        ];
    }
}
