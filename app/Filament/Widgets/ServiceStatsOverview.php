<?php

namespace App\Filament\Widgets;

use App\Support\ServiceDashboardMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $totals = ServiceDashboardMetrics::totals();
        $dailyTotals = ServiceDashboardMetrics::dailyTotals();

        return [
            Stat::make('Total layanan', number_format($totals['total'], 0, ',', '.'))
                ->description('Semua pengajuan layanan')
                ->descriptionIcon(Heroicon::OutlinedDocumentChartBar)
                ->chart($dailyTotals)
                ->color('primary'),
            Stat::make('Menunggu proses', number_format($totals['pending'], 0, ',', '.'))
                ->description('Belum disetujui atau ditolak')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->chart($dailyTotals)
                ->color('warning'),
            Stat::make('Disetujui', number_format($totals['approved'], 0, ',', '.'))
                ->description('Pengajuan yang sudah disetujui')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->chart($dailyTotals)
                ->color('success'),
            Stat::make('Ditolak', number_format($totals['rejected'], 0, ',', '.'))
                ->description('Pengajuan yang ditolak')
                ->descriptionIcon(Heroicon::OutlinedXCircle)
                ->chart($dailyTotals)
                ->color('danger'),
        ];
    }
}
