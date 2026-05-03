<?php

namespace App\Filament\Widgets;

use App\Support\ServiceDashboardMetrics;
use Filament\Widgets\ChartWidget;

class ServiceTrendChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected ?string $heading = 'Tren Pengajuan';

    protected ?string $description = 'Perkembangan layanan berdasarkan bulan.';

    protected ?string $pollingInterval = null;

    public ?string $filter = '6';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<scalar, scalar>
     */
    protected function getFilters(): ?array
    {
        return [
            '3' => '3 bulan',
            '6' => '6 bulan',
            '12' => '12 bulan',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $trend = ServiceDashboardMetrics::monthlyTrend((int) ($this->filter ?? 6));

        return [
            'datasets' => [
                [
                    'label' => 'Total',
                    'data' => $trend['total'],
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Menunggu',
                    'data' => $trend['pending'],
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Disetujui',
                    'data' => $trend['approved'],
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Ditolak',
                    'data' => $trend['rejected'],
                    'tension' => 0.35,
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
