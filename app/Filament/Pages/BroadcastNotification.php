<?php

namespace App\Filament\Pages;

use App\Services\NotificationDispatcher;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BroadcastNotification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'CMS';

    protected static ?string $navigationLabel = 'Broadcast notification';

    protected static string $view = 'filament.pages.broadcast-notification';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('body')
                    ->required()
                    ->rows(4),
            ])
            ->statePath('data');
    }

    public function send(NotificationDispatcher $dispatcher): void
    {
        $state = $this->form->getState();

        $dispatcher->broadcast($state['title'], $state['body']);

        Notification::make()
            ->title('Broadcast sent')
            ->success()
            ->send();

        $this->form->fill();
    }
}
