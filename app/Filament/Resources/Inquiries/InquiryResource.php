<?php

namespace App\Filament\Resources\Inquiries;

use App\Filament\Resources\Inquiries\Pages\ListInquiries;
use App\Models\Inquiry;
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

class InquiryResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Inquiry::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Moderation';
    }

    public static function getNavigationLabel(): string
    {
        return 'School inquiries';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-inbox';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('school_id')->relationship('school', 'name')->searchable()->preload()->required(),
            Select::make('user_id')->relationship('user', 'name')->searchable()->preload(),
            TextInput::make('parent_name')->required()->maxLength(255),
            TextInput::make('parent_email')->email()->required()->maxLength(255),
            TextInput::make('parent_phone')->tel()->maxLength(30),
            TextInput::make('student_name')->maxLength(255),
            TextInput::make('student_age')->numeric()->minValue(1)->maxValue(100),
            TextInput::make('intended_level')->maxLength(120),
            Textarea::make('message')->required()->rows(5)->columnSpanFull(),
            Select::make('source')->options([
                'web' => 'Website',
                'whatsapp' => 'WhatsApp',
                'api' => 'API',
                'admin' => 'Admin',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('school.name')->label('School')->searchable()->sortable(),
                TextColumn::make('parent_name')->searchable(),
                TextColumn::make('parent_email')->searchable(),
                TextColumn::make('parent_phone')->toggleable(),
                TextColumn::make('intended_level')->label('Level')->toggleable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New',
                    'read' => 'Read',
                    'responded' => 'Responded',
                    'archived' => 'Archived',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('markRead')
                    ->label('Mark as read')
                    ->color('info')
                    ->visible(fn (Inquiry $record): bool => $record->status === 'new')
                    ->action(fn (Inquiry $record) => $record->update(['status' => 'read'])),
                Action::make('respond')
                    ->label('Record response')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->form([
                        Textarea::make('response')->required()->rows(4)->maxLength(5000),
                    ])
                    ->action(function (Inquiry $record, array $data): void {
                        $record->forceFill([
                            'owner_response' => $data['response'],
                            'responded_at' => now(),
                            'responded_by' => auth()->id(),
                            'status' => 'responded',
                        ])->save();
                    }),
                Action::make('archive')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Inquiry $record): bool => $record->status !== 'archived')
                    ->action(fn (Inquiry $record) => $record->update(['status' => 'archived'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('school');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
        ];
    }
}
