<?php

namespace App\Filament\Resources\SosAlertResource\Pages;

use App\Filament\Resources\SosAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSosAlerts extends ListRecords
{
    protected static string $resource = SosAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
