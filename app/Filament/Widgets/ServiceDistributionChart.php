<?php

namespace App\Filament\Widgets;

use App\Support\ServiceDashboardMetrics;
use Filament\Widgets\ChartWidget;

class ServiceDistributionChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected ?string $heading = 'Distribusi Layanan';

    protected ?string $description = 'Jumlah pengajuan per jenis layanan.';

    protected ?string $pollingInterval = null;

    public ?string $filter = 'all';

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<scalar, scalar>
     */
    protected function getFilters(): ?array
    {
        return [
            'all' => 'Semua status',
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $distribution = ServiceDashboardMetrics::serviceDistribution($this->filter ?? 'all');

        return [
            'datasets' => [
                [
                    'label' => 'Pengajuan',
                    'data' => $distribution['data'],
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $distribution['labels'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
