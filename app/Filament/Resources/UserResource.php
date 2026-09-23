<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\ReferredUsersRelationManager;
use App\Models\User;
use App\Services\ActivationService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Users & Referrals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('firebase_uid')
                    ->label('Firebase UID')
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrateStateUsing(fn ($state) => $state)
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Only needed for admin/editor panel accounts. Leave blank to keep unchanged.'),
                Forms\Components\TextInput::make('referral_code')
                    ->disabled()
                    ->dehydrated(false)
                    ->maxLength(12),
                Forms\Components\Select::make('referred_by')
                    ->label('Referred by')
                    ->relationship('referrer', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options([
                        'registered' => 'Registered',
                        'pending' => 'Pending',
                        'active' => 'Active',
                    ])
                    ->required()
                    ->helperText('Status is normally server-computed. Manual override should only be used for admin corrections.'),
                Forms\Components\Toggle::make('has_paid')
                    ->label('Activation fee paid'),
                Forms\Components\Select::make('author_tier')
                    ->label('Author tier')
                    ->options([
                        'member' => 'Member',
                        'author' => 'Author — can post Blogs & SOS',
                        'organizer' => 'Organizer — can also create Causes',
                    ])
                    ->default('member')
                    ->required()
                    ->helperText('Organizer is cumulative — it includes Author privileges too.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('referral_code')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('referrer.name')
                    ->label('Referred by')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('has_paid')
                    ->boolean(),
                Tables\Columns\TextColumn::make('author_tier')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'organizer' => 'success',
                        'author' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),
                Tables\Columns\TextColumn::make('referrals_made_count')
                    ->label('Paid referrals')
                    ->counts(['referralsMade' => fn ($query) => $query->where('referred_paid', true)])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'registered' => 'Registered',
                        'pending' => 'Pending',
                        'active' => 'Active',
                    ]),
                Tables\Filters\TernaryFilter::make('has_paid')
                    ->label('Activation fee paid'),
                Tables\Filters\SelectFilter::make('author_tier')
                    ->options([
                        'member' => 'Member',
                        'author' => 'Author',
                        'organizer' => 'Organizer',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('recheckActivation')
                    ->label('Re-check activation')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (User $record) => $record->status !== 'active')
                    ->action(function (User $record) {
                        $activated = app(ActivationService::class)->checkAndActivate($record);

                        Notification::make()
                            ->title($activated ? 'User activated' : 'Activation conditions not yet met')
                            ->success($activated)
                            ->warning(! $activated)
                            ->send();
                    }),
                Tables\Actions\Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->status === 'active')
                    ->action(fn (User $record) => $record->update(['status' => 'pending'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReferredUsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
