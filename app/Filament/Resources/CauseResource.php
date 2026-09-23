<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CauseResource\Pages;
use App\Models\Cause;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CauseResource extends Resource
{
    protected static ?string $model = Cause::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Causes & SOS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->maxLength(255)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Select::make('organizer_id')
                            ->label('Organizer')
                            ->relationship('organizer', 'name', fn ($query) => $query->where('author_tier', 'organizer'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_review' => 'Pending review',
                                'published' => 'Published',
                                'closed' => 'Closed',
                            ])
                            ->default('draft')
                            ->required(),
                        Forms\Components\Toggle::make('verified')
                            ->label('Verified by admin')
                            ->helperText('Editorial trust signal only — never implies a tax-exemption (80G) claim.'),
                    ]),
                Forms\Components\Textarea::make('excerpt')
                    ->columnSpanFull()
                    ->rows(2),
                Forms\Components\RichEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('featured_image')
                    ->image()
                    ->directory('causes'),
                Forms\Components\Section::make('Funding')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('goal_amount')
                            ->label('Goal amount')
                            ->required()
                            ->numeric()
                            ->prefix('₹'),
                        Forms\Components\TextInput::make('raised_amount')
                            ->label('Raised amount')
                            ->required()
                            ->numeric()
                            ->prefix('₹')
                            ->helperText('Normally driven by the payment webhook — edit only to correct.'),
                        Forms\Components\DatePicker::make('deadline'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organizer.name')
                    ->label('Organizer')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'published' => 'success',
                        'pending_review' => 'warning',
                        'closed' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('verified')
                    ->boolean(),
                Tables\Columns\TextColumn::make('raised_amount')
                    ->money('inr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('goal_amount')
                    ->money('inr')
                    ->sortable(),
                Tables\Columns\TextColumn::make('deadline')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending review',
                        'published' => 'Published',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\TernaryFilter::make('verified'),
            ])
            ->actions([
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
            'index' => Pages\ListCauses::route('/'),
            'create' => Pages\CreateCause::route('/create'),
            'edit' => Pages\EditCause::route('/{record}/edit'),
        ];
    }
}
