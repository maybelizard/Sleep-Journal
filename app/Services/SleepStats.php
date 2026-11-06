<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\SleepEntry;

class SleepStats
{
    private const MIN_NIGHTS = 5;          // nights needed both with and without a habit
    private const QUALITY_THRESHOLD = 0.3; // quality difference that counts as an effect
    private const DURATION_THRESHOLD = 30; // minutes; only used when quality shows no clear effect

    public function __construct(private User $user)
    {
    }

    /**
     * Average duration and quality for the last 7 days, last 30 days and all time.
     */
    public function averages(): array
    {
        $week = now()->subDays(6)->toDateString();
        $month = now()->subDays(29)->toDateString();

        $row = DB::table('sleep_entries')
            ->where('user_id', $this->user->id)
            ->selectRaw('
                COUNT(*)                                              AS nights_all,
                COUNT(CASE WHEN `date` >= ? THEN 1 END)               AS nights_week,
                COUNT(CASE WHEN `date` >= ? THEN 1 END)               AS nights_month,
                AVG(duration_minutes)                                 AS duration_all,
                AVG(CASE WHEN `date` >= ? THEN duration_minutes END)  AS duration_week,
                AVG(CASE WHEN `date` >= ? THEN duration_minutes END)  AS duration_month,
                AVG(quality)                                          AS quality_all,
                AVG(CASE WHEN `date` >= ? THEN quality END)           AS quality_week,
                AVG(CASE WHEN `date` >= ? THEN quality END)           AS quality_month
            ', [$week, $month, $week, $month, $week, $month])
            ->first();

        return [
            'nights' => [
                'week' => (int) $row->nights_week,
                'month' => (int) $row->nights_month,
                'all' => (int) $row->nights_all,
            ],
            'duration' => [
                'week' => $this->number($row->duration_week),
                'month' => $this->number($row->duration_month),
                'all' => $this->number($row->duration_all),
            ],
            'quality' => [
                'week' => $this->number($row->quality_week),
                'month' => $this->number($row->quality_month),
                'all' => $this->number($row->quality_all),
            ],
        ];
    }

    public function fluctuations(): array
    {
        $week = now()->subDays(6)->toDateString();
        $month = now()->subDays(29)->toDateString();

        // One row per night; times measured from midnight of the day before the wake-up date.
        $nights = DB::table('sleep_entries')
            ->where('user_id', $this->user->id)
            ->selectRaw('
                `date`,
                duration_minutes,
                TIMESTAMPDIFF(MINUTE, `date`, bedtime) + 1440   AS bed_min,
                TIMESTAMPDIFF(MINUTE, `date`, wake_time) + 1440 AS wake_min
            ');

        $row = DB::query()
            ->fromSub($nights, 'e')
            ->selectRaw('
                STDDEV_SAMP(duration_minutes)                                AS duration_all,
                STDDEV_SAMP(CASE WHEN `date` >= ? THEN duration_minutes END) AS duration_week,
                STDDEV_SAMP(CASE WHEN `date` >= ? THEN duration_minutes END) AS duration_month,
                STDDEV_SAMP(bed_min)                                         AS bed_all,
                STDDEV_SAMP(CASE WHEN `date` >= ? THEN bed_min END)          AS bed_week,
                STDDEV_SAMP(CASE WHEN `date` >= ? THEN bed_min END)          AS bed_month,
                STDDEV_SAMP(wake_min)                                        AS wake_all,
                STDDEV_SAMP(CASE WHEN `date` >= ? THEN wake_min END)         AS wake_week,
                STDDEV_SAMP(CASE WHEN `date` >= ? THEN wake_min END)         AS wake_month
            ', [$week, $month, $week, $month, $week, $month])
            ->first();

        return [
            'duration' => [
                'week' => $this->number($row->duration_week),
                'month' => $this->number($row->duration_month),
                'all' => $this->number($row->duration_all),
            ],
            'bedtime' => [
                'week' => $this->number($row->bed_week),
                'month' => $this->number($row->bed_month),
                'all' => $this->number($row->bed_all),
            ],
            'waketime' => [
                'week' => $this->number($row->wake_week),
                'month' => $this->number($row->wake_month),
                'all' => $this->number($row->wake_all),
            ],
        ];
    }

    /**
     * Ideal bedtime, wake time and duration: a quality-weighted average over
     * the nights rated above the user's average quality.
     */
    public function ideal(): ?array
    {
        $userId = $this->user->id;

        $row = DB::table('sleep_entries')
            ->where('user_id', $userId)
            ->whereRaw('quality > (SELECT AVG(quality) FROM sleep_entries WHERE user_id = ?)', [$userId])
            ->selectRaw('
                COUNT(*)                                                          AS nights,
                SUM(quality)                                                      AS weight,
                SUM(quality * duration_minutes)                                   AS weighted_duration,
                SUM(quality * (TIMESTAMPDIFF(MINUTE, `date`, bedtime) + 1440))    AS weighted_bed,
                SUM(quality * (TIMESTAMPDIFF(MINUTE, `date`, wake_time) + 1440))  AS weighted_wake
            ')
            ->first();

        $nights = (int) $row->nights;

        if ($nights < 3 || (float) $row->weight <= 0) {
            return null;
        }

        $weight = (float) $row->weight;

        return [
            'nights' => $nights,
            'avg_quality' => (float) DB::table('sleep_entries')->where('user_id', $userId)->avg('quality'),
            'duration' => (float) $row->weighted_duration / $weight,
            'bedtime' => (float) $row->weighted_bed / $weight,
            'waketime' => (float) $row->weighted_wake / $weight,
        ];
    }

    private function number(mixed $value): ?float
    {
        return $value === null ? null : (float) $value;
    }

    /**
     * One point per night for the charts. $days = null means all time.
     */
    public function series(?int $days): array
    {
        $rows = DB::table('sleep_entries')
            ->where('user_id', $this->user->id)
            ->when($days, fn ($q) => $q->where('date', '>=', now()->subDays($days - 1)->toDateString()))
            ->orderBy('date')
            ->selectRaw('
                `date`,
                duration_minutes,
                quality,
                TIMESTAMPDIFF(MINUTE, `date`, bedtime) + 1440   AS bed_min,
                TIMESTAMPDIFF(MINUTE, `date`, wake_time) + 1440 AS wake_min
            ')
            ->get();

        return [
            'labels' => $rows->map(fn ($r) => Carbon::parse($r->date)->format('d M'))->all(),
            'dates' => $rows->map(fn ($r) => Carbon::parse($r->date)->format('D, d M Y'))->all(),
            'duration' => $rows->map(fn ($r) => round($r->duration_minutes / 60, 2))->all(),
            'quality' => $rows->map(fn ($r) => (int) $r->quality)->all(),
            'bedtime' => $rows->map(fn ($r) => (int) $r->bed_min)->all(),
            'waketime' => $rows->map(fn ($r) => (int) $r->wake_min)->all(),
            'qualityLabels' => SleepEntry::QUALITY_LABELS,
        ];
    }

    /**
     * Best and worst night: quality first, then duration.
     */
    public function extremes(): array
    {
        $nights = fn () => $this->user->sleepEntries()->with('habits');

        /** @var SleepEntry|null $best */
        $best = $nights()->orderByDesc('quality')->orderByDesc('duration_minutes')->orderByDesc('date')->first();

        /** @var SleepEntry|null $worst */
        $worst = $nights()->orderBy('quality')->orderBy('duration_minutes')->orderByDesc('date')->first();

        if ($best && $worst && $best->is($worst)) {
            $worst = null; // only one night logged, so there's nothing to contrast
        }

        return ['best' => $best, 'worst' => $worst];
    }

    /**
     * Compare nights with vs. without each habit.
     */
    public function habitInsights(): array
    {
        $rows = DB::table('habits as h')
            ->join('sleep_entries as e', 'e.user_id', '=', 'h.user_id')
            ->leftJoin('habit_sleep_entry as hse', function ($join) {
                $join->on('hse.habit_id', '=', 'h.id')
                    ->on('hse.sleep_entry_id', '=', 'e.id');
            })
            ->where('h.user_id', $this->user->id)
            ->groupBy('h.id', 'h.name')
            ->selectRaw('
                h.name,
                COUNT(hse.habit_id)                                                 AS n_with,
                COUNT(CASE WHEN hse.habit_id IS NULL THEN 1 END)                    AS n_without,
                AVG(CASE WHEN hse.habit_id IS NOT NULL THEN e.quality END)          AS q_with,
                AVG(CASE WHEN hse.habit_id IS NULL     THEN e.quality END)          AS q_without,
                AVG(CASE WHEN hse.habit_id IS NOT NULL THEN e.duration_minutes END) AS d_with,
                AVG(CASE WHEN hse.habit_id IS NULL     THEN e.duration_minutes END) AS d_without
            ')
            ->get();

        $ranked = [];
        $insufficient = [];

        foreach ($rows as $r) {
            $nWith = (int) $r->n_with;
            $nWithout = (int) $r->n_without;

            if ($nWith < self::MIN_NIGHTS || $nWithout < self::MIN_NIGHTS) {
                $insufficient[] = ['name' => $r->name, 'n_with' => $nWith, 'n_without' => $nWithout];
                continue;
            }

            $qualityDiff = round((float) $r->q_with - (float) $r->q_without, 2);
            $durationDiff = (float) $r->d_with - (float) $r->d_without;

            $ranked[] = [
                'name' => $r->name,
                'n_with' => $nWith,
                'n_without' => $nWithout,
                'quality_with' => (float) $r->q_with,
                'quality_without' => (float) $r->q_without,
                'quality_diff' => $qualityDiff,
                'duration_diff' => $durationDiff,
                'verdict' => $this->verdict($qualityDiff, $durationDiff),
            ];
        }

        // Best first: quality effect, then duration effect.
        usort($ranked, fn ($a, $b) => [$b['quality_diff'], $b['duration_diff']]
                                <=> [$a['quality_diff'], $a['duration_diff']]);

        $byVerdict = fn (string $v) => array_values(array_filter($ranked, fn ($h) => $h['verdict'] === $v));

        return [
            'best' => $ranked[0] ?? null,
            'worst' => count($ranked) > 1 ? end($ranked) : null,
            'good' => $byVerdict('good'),
            'bad' => array_reverse($byVerdict('bad')), // most harmful first
            'neutral' => $byVerdict('neutral'),
            'insufficient' => $insufficient,
            'ranked_count' => count($ranked),
            'min_nights' => self::MIN_NIGHTS,
        ];
    }

    /**
     * Quality decides first; duration only breaks the "no clear quality effect" case.
     */
    private function verdict(float $qualityDiff, float $durationDiff): string
    {
        if ($qualityDiff >= self::QUALITY_THRESHOLD) return 'good';
        if ($qualityDiff <= -self::QUALITY_THRESHOLD) return 'bad';
        if ($durationDiff >= self::DURATION_THRESHOLD) return 'good';
        if ($durationDiff <= -self::DURATION_THRESHOLD) return 'bad';

        return 'neutral';
    }
}