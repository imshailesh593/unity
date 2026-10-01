<?php

namespace App\Filament\Pages;

use App\Models\PaymentGatewaySetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PhonePeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'PhonePe Settings';

    protected static string $view = 'filament.pages.phone-pe-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(PaymentGatewaySetting::forGateway('phonepe')->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('Accept payments via PhonePe')
                    ->helperText('Turn off to take PhonePe payments offline site-wide.'),
                Forms\Components\Toggle::make('is_sandbox')
                    ->label('Sandbox mode')
                    ->helperText('On: use the sandbox credentials below for every payment. Off: use the live credentials.')
                    ->live(),

                Forms\Components\Section::make('Sandbox credentials')
                    ->description('From PhonePe Business Dashboard → Developer Settings, UAT/sandbox environment.')
                    ->schema([
                        Forms\Components\TextInput::make('sandbox_client_id')->label('Client ID'),
                        Forms\Components\TextInput::make('sandbox_client_secret')->label('Client secret')->password()->revealable(),
                        Forms\Components\TextInput::make('sandbox_client_version')->label('Client version'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Live credentials')
                    ->description('From PhonePe Business Dashboard → Developer Settings, production environment.')
                    ->schema([
                        Forms\Components\TextInput::make('client_id')->label('Client ID'),
                        Forms\Components\TextInput::make('client_secret')->label('Client secret')->password()->revealable(),
                        Forms\Components\TextInput::make('client_version')->label('Client version'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Webhook authentication')
                    ->description('Enter the same username/password here and in PhonePe\'s dashboard webhook configuration, pointing it at '.route('api.v1.webhooks.payment', [], false))
                    ->schema([
                        Forms\Components\TextInput::make('webhook_username'),
                        Forms\Components\TextInput::make('webhook_password')->password()->revealable(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        PaymentGatewaySetting::forGateway('phonepe')->update($state);

        Notification::make()
            ->title('PhonePe settings saved')
            ->success()
            ->send();
    }
}
