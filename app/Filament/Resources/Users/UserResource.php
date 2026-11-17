<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends \Filament\Resources\Resource
{
    protected static ?string $model = User::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'People & Accounts';
    }

    public static function getNavigationLabel(): string
    {
        return 'Users';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->maxLength(255),
            TextInput::make('phone')->tel()->maxLength(30),
            Select::make('district_id')
                ->relationship('district', 'name')
                ->searchable()
                ->preload(),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->dehydrated(fn (?string $state): bool => filled($state)),
            Select::make('roles')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable()
                ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                ->label('Access roles'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('user_type')->label('Account type')->badge(),
                TextColumn::make('roles.name')->label('Roles')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('email_verified_at')->label('Email verified')->dateTime()->placeholder('Not verified'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('suspend')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->status === 'active' && ! $record->is(auth()->user()))
                    ->action(fn (User $record) => $record->forceFill(['status' => 'suspended'])->save()),
                Action::make('activate')
                    ->color('success')
                    ->visible(fn (User $record): bool => in_array($record->status, ['inactive', 'suspended'], true))
                    ->action(fn (User $record) => $record->forceFill(['status' => 'active'])->save()),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }
}
