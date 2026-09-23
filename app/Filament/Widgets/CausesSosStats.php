<?php

namespace App\Filament\Widgets;

use App\Models\Cause;
use App\Models\SosAlert;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CausesSosStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRaised = Cause::query()->sum('raised_amount');
        $activeCauses = Cause::query()->where('status', 'published')->count();
        $pendingCauses = Cause::query()->where('status', 'pending_review')->count();
        $activeSos = SosAlert::query()->where('status', 'active')->where(
            fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>=', now())
        )->count();

        return [
            Stat::make('Raised across causes', '₹'.number_format($totalRaised))
                ->description("{$activeCauses} published"),
            Stat::make('Causes awaiting review', $pendingCauses)
                ->color($pendingCauses > 0 ? 'warning' : 'success')
                ->description($pendingCauses > 0 ? 'Needs admin attention' : 'All clear'),
            Stat::make('Active SOS alerts', $activeSos)
                ->color($activeSos > 0 ? 'danger' : 'success'),
        ];
    }
}
