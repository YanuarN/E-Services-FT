<?php

namespace App\Support;

use App\Models\ExamPermissionLetter;
use App\Models\InternshipLetter;
use App\Models\InternshipRecommendationLetter;
use App\Models\LetterOfAssignment;
use App\Models\LetterOfAssignmentIndividual;
use App\Models\LetterTemplate;
use App\Models\PassportApplicationLetter;
use App\Models\ResearchDataRequestLetter;
use App\Models\ResearchPermissionLetter;
use App\Models\RoomUsageRequest;
use App\Models\ScholarshipsStatementLetter;
use App\Models\TestingPermissionRequestLetter;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServiceDashboardMetrics
{
    private const CACHE_TTL = 60;

    /**
     * @return array<string, array{label: string, short_label: string, model: class-string<Model>, statuses: array<string, string>}>
     */
    public static function services(): array
    {
        return [
            'exam_permission' => [
                'label' => LetterTemplate::LETTER_TYPES['exam_permission'],
                'short_label' => 'Izin Ujian',
                'model' => ExamPermissionLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'internship' => [
                'label' => LetterTemplate::LETTER_TYPES['internship'],
                'short_label' => 'PKN',
                'model' => InternshipLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'internship_recommendation' => [
                'label' => LetterTemplate::LETTER_TYPES['internship_recommendation'],
                'short_label' => 'Rekom. Magang',
                'model' => InternshipRecommendationLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'letter_of_assignment' => [
                'label' => LetterTemplate::LETTER_TYPES['letter_of_assignment'],
                'short_label' => 'Tugas Kelompok',
                'model' => LetterOfAssignment::class,
                'statuses' => self::letterStatuses(),
            ],
            'letter_of_assignment_individual' => [
                'label' => LetterTemplate::LETTER_TYPES['letter_of_assignment_individual'],
                'short_label' => 'Tugas Mandiri',
                'model' => LetterOfAssignmentIndividual::class,
                'statuses' => self::letterStatuses(),
            ],
            'passport_application' => [
                'label' => LetterTemplate::LETTER_TYPES['passport_application'],
                'short_label' => 'Paspor',
                'model' => PassportApplicationLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'research_data_request' => [
                'label' => LetterTemplate::LETTER_TYPES['research_data_request'],
                'short_label' => 'Data Penelitian',
                'model' => ResearchDataRequestLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'research_permission' => [
                'label' => LetterTemplate::LETTER_TYPES['research_permission'],
                'short_label' => 'Izin Survey',
                'model' => ResearchPermissionLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'room_usage_request' => [
                'label' => LetterTemplate::LETTER_TYPES['room_usage_request'],
                'short_label' => 'Ruang',
                'model' => RoomUsageRequest::class,
                'statuses' => [
                    'pending' => 'PENDING',
                    'approved' => 'APPROVED',
                    'rejected' => 'REJECTED',
                ],
            ],
            'scholarships_statement' => [
                'label' => LetterTemplate::LETTER_TYPES['scholarships_statement'],
                'short_label' => 'Beasiswa',
                'model' => ScholarshipsStatementLetter::class,
                'statuses' => self::letterStatuses(),
            ],
            'testing_permission_request' => [
                'label' => LetterTemplate::LETTER_TYPES['testing_permission_request'],
                'short_label' => 'Uji Alat',
                'model' => TestingPermissionRequestLetter::class,
                'statuses' => self::letterStatuses(),
            ],
        ];
    }

    /**
     * @return array{pending: string, approved: string, rejected: string}
     */
    private static function letterStatuses(): array
    {
        return [
            'pending' => 'SUBMITTED',
            'approved' => 'APPROVE',
            'rejected' => 'REJECT',
        ];
    }

    /**
     * @return array{total: int, pending: int, approved: int, rejected: int}
     */
    public static function totals(): array
    {
        return Cache::remember('dashboard:service-metrics:totals', self::CACHE_TTL, function (): array {
            $totals = [
                'total' => 0,
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
            ];

            foreach (self::serviceStatusSummary() as $summary) {
                $totals['total'] += $summary['total'];
                $totals['pending'] += $summary['pending'];
                $totals['approved'] += $summary['approved'];
                $totals['rejected'] += $summary['rejected'];
            }

            return $totals;
        });
    }

    /**
     * @return array<string, array{label: string, total: int, pending: int, approved: int, rejected: int}>
     */
    private static function serviceStatusSummary(): array
    {
        return Cache::remember('dashboard:service-metrics:status-summary', self::CACHE_TTL, function (): array {
            $summary = [];

            foreach (self::services() as $key => $service) {
                /** @var class-string<Model> $model */
                $model = $service['model'];

                $statusCounts = $model::query()
                    ->select('status')
                    ->selectRaw('count(*) as aggregate')
                    ->groupBy('status')
                    ->pluck('aggregate', 'status');

                $summary[$key] = [
                    'label' => $service['short_label'],
                    'total' => 0,
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0,
                ];

                foreach ($service['statuses'] as $normalizedStatus => $storedStatus) {
                    $count = (int) ($statusCounts[$storedStatus] ?? 0);

                    $summary[$key][$normalizedStatus] = $count;
                    $summary[$key]['total'] += $count;
                }
            }

            return $summary;
        });
    }

    /**
     * @return array<int, int>
     */
    public static function dailyTotals(int $days = 7): array
    {
        return Cache::remember("dashboard:service-metrics:daily-totals:{$days}", self::CACHE_TTL, function () use ($days): array {
            $startDate = CarbonImmutable::today()->subDays($days - 1)->startOfDay();
            $endDate = CarbonImmutable::today()->endOfDay();
            $totals = array_fill(0, $days, 0);

            foreach (self::services() as $service) {
                /** @var class-string<Model> $model */
                $model = $service['model'];

                $rows = $model::query()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw(self::dateExpression().' as period, count(*) as aggregate')
                    ->groupBy('period')
                    ->pluck('aggregate', 'period');

                foreach ($rows as $period => $aggregate) {
                    $index = (int) $startDate->diffInDays(CarbonImmutable::parse((string) $period));

                    if ($index >= 0 && $index < $days) {
                        $totals[$index] += (int) $aggregate;
                    }
                }
            }

            return $totals;
        });
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    public static function serviceDistribution(string $status = 'all'): array
    {
        $labels = [];
        $data = [];

        foreach (self::serviceStatusSummary() as $summary) {
            $labels[] = $summary['label'];
            $data[] = $summary[$status] ?? $summary['total'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * @return array{labels: array<int, string>, total: array<int, int>, approved: array<int, int>, pending: array<int, int>, rejected: array<int, int>}
     */
    public static function monthlyTrend(int $months = 6): array
    {
        return Cache::remember("dashboard:service-metrics:monthly-trend:{$months}", self::CACHE_TTL, function () use ($months): array {
            $startMonth = CarbonImmutable::now()->startOfMonth()->subMonths($months - 1);
            $endDate = CarbonImmutable::now()->endOfMonth();
            $periods = [];

            for ($index = 0; $index < $months; $index++) {
                $month = $startMonth->addMonths($index);

                $periods[$month->format('Y-m')] = [
                    'label' => $month->translatedFormat('M Y'),
                    'total' => 0,
                    'approved' => 0,
                    'pending' => 0,
                    'rejected' => 0,
                ];
            }

            foreach (self::services() as $service) {
                /** @var class-string<Model> $model */
                $model = $service['model'];

                $rows = $model::query()
                    ->whereBetween('created_at', [$startMonth, $endDate])
                    ->selectRaw(self::monthExpression().' as period, status, count(*) as aggregate')
                    ->groupBy('period', 'status')
                    ->get();

                foreach ($rows as $row) {
                    $period = (string) $row->period;

                    if (! array_key_exists($period, $periods)) {
                        continue;
                    }

                    $aggregate = (int) $row->aggregate;
                    $periods[$period]['total'] += $aggregate;

                    foreach ($service['statuses'] as $normalizedStatus => $storedStatus) {
                        if ($row->status === $storedStatus) {
                            $periods[$period][$normalizedStatus] += $aggregate;
                            break;
                        }
                    }
                }
            }

            return [
                'labels' => array_column($periods, 'label'),
                'total' => array_column($periods, 'total'),
                'approved' => array_column($periods, 'approved'),
                'pending' => array_column($periods, 'pending'),
                'rejected' => array_column($periods, 'rejected'),
            ];
        });
    }

    private static function dateExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char(created_at, 'YYYY-MM-DD')",
            'sqlsrv' => "format(created_at, 'yyyy-MM-dd')",
            'sqlite' => "strftime('%Y-%m-%d', created_at)",
            default => "date_format(created_at, '%Y-%m-%d')",
        };
    }

    private static function monthExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            'sqlsrv' => "format(created_at, 'yyyy-MM')",
            'sqlite' => "strftime('%Y-%m', created_at)",
            default => "date_format(created_at, '%Y-%m')",
        };
    }
}
