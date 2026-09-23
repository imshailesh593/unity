<?php

namespace App\Filament\Resources\SosAlertResource\Pages;

use App\Filament\Resources\SosAlertResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSosAlert extends EditRecord
{
    protected static string $resource = SosAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
