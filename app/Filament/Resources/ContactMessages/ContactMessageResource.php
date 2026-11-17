<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends \Filament\Resources\Resource
{
    protected static ?string $model = ContactMessage::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Moderation';
    }

    public static function getNavigationLabel(): string
    {
        return 'Contact messages';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-envelope';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('subject'),
            TextEntry::make('message')->columnSpanFull(),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('subject')->searchable()->limit(45),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New',
                    'read' => 'Read',
                    'archived' => 'Archived',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('markRead')
                    ->label('Mark as read')
                    ->color('info')
                    ->visible(fn (ContactMessage $record): bool => $record->status === 'new')
                    ->action(fn (ContactMessage $record) => $record->update([
                        'status' => 'read',
                        'handled_by' => auth()->id(),
                        'handled_at' => now(),
                    ])),
                Action::make('archive')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (ContactMessage $record): bool => $record->status !== 'archived')
                    ->action(fn (ContactMessage $record) => $record->update([
                        'status' => 'archived',
                        'handled_by' => auth()->id(),
                        'handled_at' => now(),
                    ])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
        ];
    }
}
