<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SosAlertResource\Pages;
use App\Models\SosAlert;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SosAlertResource extends Resource
{
    protected static ?string $model = SosAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationGroup = 'Causes & SOS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('author_id')
                    ->label('Author')
                    ->relationship('author', 'name')
                    ->disabled(),
                Forms\Components\Select::make('category')
                    ->options([
                        'blood' => 'Blood',
                        'organ' => 'Organ',
                        'medication' => 'Medication',
                        'other' => 'Other',
                    ])
                    ->disabled(),
                Forms\Components\TextInput::make('title')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('location')
                    ->disabled(),
                Forms\Components\TextInput::make('contact_info')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'resolved' => 'Resolved',
                        'expired' => 'Expired',
                    ])
                    ->required()
                    ->helperText('Moderation control — mark resolved once the need is met, or remove if abusive/false.'),
                Forms\Components\DateTimePicker::make('expires_at')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state) => $state === 'blood' ? 'danger' : 'warning'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Author')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('location'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'danger',
                        'resolved' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'resolved' => 'Resolved',
                        'expired' => 'Expired',
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'blood' => 'Blood',
                        'organ' => 'Organ',
                        'medication' => 'Medication',
                        'other' => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('resolve')
                    ->label('Mark resolved')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (SosAlert $record) => $record->status === 'active')
                    ->action(fn (SosAlert $record) => $record->update(['status' => 'resolved'])),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
            'index' => Pages\ListSosAlerts::route('/'),
            'edit' => Pages\EditSosAlert::route('/{record}/edit'),
        ];
    }
}
