<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $activeUsers = User::query()->where('status', 'active')->count();
        $pendingUsers = User::query()->where('status', '!=', 'active')->count();
        $revenue = Payment::query()->where('status', 'success')->sum('amount');

        $topReferrer = User::query()
            ->withCount(['referralsMade as paid_referrals_count' => fn ($query) => $query->where('referred_paid', true)])
            ->orderByDesc('paid_referrals_count')
            ->first();

        return [
            Stat::make('Total users', $totalUsers),
            Stat::make('Active users', $activeUsers)
                ->description("{$pendingUsers} pending activation")
                ->color('success'),
            Stat::make('Revenue', '₹'.number_format($revenue)),
            Stat::make('Top referrer', $topReferrer?->name ?? '—')
                ->description($topReferrer ? "{$topReferrer->paid_referrals_count} paid referrals" : 'No referrals yet'),
        ];
    }
}
