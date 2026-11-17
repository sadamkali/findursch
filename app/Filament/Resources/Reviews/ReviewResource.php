<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use Filament\Actions\Action;
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

class ReviewResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Review::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Moderation';
    }

    public static function getNavigationLabel(): string
    {
        return 'Reviews';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-star';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_id')->relationship('school', 'name')->searchable()->preload()->required(),
            Select::make('user_id')->relationship('user', 'name')->searchable()->preload()->required(),
            Select::make('reviewer_type')->options([
                'parent' => 'Parent',
                'student' => 'Student',
                'alumni' => 'Alumni',
                'staff' => 'Staff',
                'other' => 'Other',
            ])->required(),
            TextInput::make('rating')->numeric()->minValue(1)->maxValue(5)->required(),
            TextInput::make('title')->required()->maxLength(255),
            Textarea::make('body')->required()->rows(5)->columnSpanFull(),
            Select::make('status')->options([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'flagged' => 'Flagged',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('school.name')->label('School')->searchable(),
                TextColumn::make('user.name')->label('Reviewer')->searchable(),
                TextColumn::make('rating')->badge(),
                TextColumn::make('title')->searchable()->limit(45),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'flagged' => 'Flagged',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->status !== 'approved')
                    ->action(function (Review $record): void {
                        $record->forceFill([
                            'status' => 'approved',
                            'moderated_by' => auth()->id(),
                            'moderated_at' => now(),
                        ])->save();

                        $record->school?->updateRatingCache();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Review $record): bool => $record->status !== 'rejected')
                    ->action(function (Review $record): void {
                        $record->forceFill([
                            'status' => 'rejected',
                            'moderated_by' => auth()->id(),
                            'moderated_at' => now(),
                        ])->save();

                        $record->school?->updateRatingCache();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['school', 'user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
        ];
    }
}
