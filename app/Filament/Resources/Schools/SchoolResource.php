<?php

namespace App\Filament\Resources\Schools;

use App\Filament\Resources\Schools\Pages\ListSchools;
use App\Models\School;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SchoolResource extends \Filament\Resources\Resource
{
    protected static ?string $model = School::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'School Management';
    }

    public static function getNavigationLabel(): string
    {
        return 'Schools';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-academic-cap';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('tagline')->maxLength(255),
            Select::make('owner_id')
                ->label('School owner')
                ->relationship(
                    'owner',
                    'name',
                    modifyQueryUsing: fn (Builder $query): Builder => $query->where('user_type', 'school_owner')->where('status', 'active'),
                )
                ->searchable()
                ->preload(),
            Select::make('type_id')
                ->label('School type')
                ->relationship('type', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('district_id')
                ->relationship('district', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('sub_county_id')
                ->label('Sub-county')
                ->relationship('subCounty', 'name')
                ->searchable()
                ->preload(),
            Textarea::make('description')->rows(5)->columnSpanFull(),
            TextInput::make('address')->maxLength(255),
            TextInput::make('phone')->tel()->maxLength(30),
            TextInput::make('email')->email()->maxLength(255),
            TextInput::make('website')->url()->maxLength(255),
            TextInput::make('whatsapp')->maxLength(30),
            Select::make('boarding_type')->options([
                'boarding' => 'Boarding',
                'day' => 'Day',
                'mixed' => 'Day and boarding',
            ])->required(),
            Select::make('gender_policy')->options([
                'boys' => 'Boys',
                'girls' => 'Girls',
                'mixed' => 'Mixed',
            ])->required(),
            Toggle::make('is_featured')->label('Featured school'),
            DatePicker::make('featured_until')->label('Featured until'),
            TextInput::make('founded_year')->numeric()->minValue(1800)->maxValue((int) date('Y')),
            TextInput::make('registration_number')->maxLength(60),
            TextInput::make('student_count_approx')->numeric()->minValue(0),
            TextInput::make('latitude')->numeric()->minValue(-90)->maxValue(90),
            TextInput::make('longitude')->numeric()->minValue(-180)->maxValue(180),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('district.name')->label('District')->sortable(),
                TextColumn::make('type.name')->label('Type'),
                TextColumn::make('status')->badge(),
                TextColumn::make('verification_status')->label('Verification')->badge(),
                IconColumn::make('is_featured')->label('Featured')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'pending' => 'Pending review',
                    'active' => 'Active',
                    'suspended' => 'Suspended',
                    'rejected' => 'Rejected',
                ]),
                SelectFilter::make('verification_status')->options([
                    'unverified' => 'Not verified',
                    'pending' => 'Pending',
                    'verified' => 'Verified',
                ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (School $record): bool => true),
                Action::make('approve')
                    ->label('Approve & verify')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn (School $record): bool => ($record->status !== 'active' || $record->verification_status !== 'verified'))
                    ->action(function (School $record): void {
                        $record->forceFill([
                            'status' => 'active',
                            'verification_status' => 'verified',
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                            'rejection_reason' => null,
                        ])->save();
                    }),
                Action::make('suspend')
                    ->label('Suspend listing')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (School $record): bool => $record->status === 'active')
                    ->action(fn (School $record) => $record->update(['status' => 'suspended'])),
                Action::make('restore')
                    ->label('Restore listing')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (School $record): bool => $record->status === 'suspended' && $record->verification_status === 'verified')
                    ->action(fn (School $record) => $record->update(['status' => 'active'])),
                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (School $record): bool => true)
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')->label('Reason for rejection')->required()->maxLength(2000),
                    ])
                    ->action(function (School $record, array $data): void {
                        $record->forceFill([
                            'status' => 'rejected',
                            'verification_status' => 'unverified',
                            'verified_at' => null,
                            'verified_by' => null,
                            'rejection_reason' => $data['reason'],
                        ])->save();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['district', 'type', 'owner']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSchools::route('/'),
        ];
    }
}
