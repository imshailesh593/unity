<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserDocumentResource\Pages;
use App\Models\UserDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class UserDocumentResource extends Resource
{
    protected static ?string $model = UserDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?string $navigationGroup = 'Users & Referrals';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('rejection_reason')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('Phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Str::headline($state)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('rejection_reason')
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options(array_combine(UserDocument::TYPES, array_map(
                        fn (string $type) => Str::headline($type),
                        UserDocument::TYPES,
                    ))),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (UserDocument $record) => URL::temporarySignedRoute(
                        'admin.documents.view',
                        now()->addMinutes(10),
                        ['document' => $record->id],
                    ))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (UserDocument $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(fn (UserDocument $record) => $record->update([
                        'status' => 'approved',
                        'rejection_reason' => null,
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
                Tables\Actions\Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (UserDocument $record) => $record->status !== 'rejected')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Reason')
                            ->required(),
                    ])
                    ->action(fn (UserDocument $record, array $data) => $record->update([
                        'status' => 'rejected',
                        'rejection_reason' => $data['rejection_reason'],
                        'reviewed_by' => auth()->id(),
                        'reviewed_at' => now(),
                    ])),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserDocuments::route('/'),
        ];
    }
}
