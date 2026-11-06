<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private const NIGHTS = 60;

    /** habit => [chance on any given night, effect on quality score] */
    private const HABITS = [
        'Caffeine after 3pm'     => [0.35, -1.0],
        'Screen time before bed' => [0.40, -0.7],
        'Evening workout'        => [0.30, +0.5],
        'Meditation'             => [0.30, +0.8],
        'Herbal tea'             => [0.35,  0.0],
    ];

    /** Used on only a few nights, to demo the "not enough data" state. */
    private const RARE_HABIT = 'Late-night snack';
    private const RARE_NIGHTS_AGO = [5, 20, 38];

    private const NOTES = [
        'Fell asleep quickly.',
        'Woke up a couple of times.',
        'Felt rested, no alarm needed.',
        'Busy day, hard to switch off.',
        'Noisy neighbours.',
    ];

    /**
     * Hand-written outlier nights, keyed by how many days ago the wake-up date was.
     * bed = [days before the wake-up date, 'H:i'], wake = 'H:i' on the wake-up date.
     */
    private const OUTLIERS = [
        3  => ['bed' => [0, '08:45'], 'wake' => '15:15', 'quality' => 2,
               'note' => 'Slept during the day after a night shift.'],
        11 => ['bed' => [1, '19:15'], 'wake' => '04:30', 'quality' => 5,
               'note' => 'Went to bed right after dinner, woke before the alarm.'],
        17 => ['bed' => [0, '02:40'], 'wake' => '06:30', 'quality' => 1,
               'note' => 'Deadline night, barely slept.'],
        25 => ['bed' => [1, '23:30'], 'wake' => '11:45', 'quality' => 3,
               'note' => 'Slept in after a long week.'],
        40 => ['bed' => [0, '04:15'], 'wake' => '11:30', 'quality' => 2,
               'note' => 'Jet lag after a late flight.'],
    ];

    public function run(): void
    {
        mt_srand(42); // reproducible demo data

        $user = User::firstOrNew(['email' => 'demo@example.com']);
        $user->forceFill([
            'name' => 'Demo User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ])->save();

        $user->sleepEntries()->delete(); // keeps re-seeding idempotent

        $habits = [];
        foreach ([...array_keys(self::HABITS), self::RARE_HABIT] as $name) {
            $habits[$name] = $user->habits()->firstOrCreate(['name' => $name]);
        }

        foreach (range(self::NIGHTS - 1, 0) as $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            // `date` is the wake-up date; a normal bedtime falls the evening before.
            $wake = $date->copy()->setTime(6, 0)->addMinutes(mt_rand(0, 150));
            $duration = mt_rand(330, 540);
            $bed = $wake->copy()->subMinutes($duration);

            $score = 3.6 + mt_rand(-8, 8) / 10;
            $habitIds = [];

            foreach (self::HABITS as $name => [$chance, $effect]) {
                if (mt_rand(1, 100) <= $chance * 100) {
                    $habitIds[] = $habits[$name]->id;
                    $score += $effect;
                }
            }

            if (in_array($daysAgo, self::RARE_NIGHTS_AGO, true)) {
                $habitIds[] = $habits[self::RARE_HABIT]->id;
                $score -= 1.0;
            }

            $score += $duration >= 420 ? 0.4 : ($duration < 360 ? -0.6 : 0);
            $quality = max(1, min(5, (int) round($score)));
            $notes = mt_rand(1, 100) <= 35 ? self::NOTES[array_rand(self::NOTES)] : null;

            // Outlier nights replace the generated times, quality and note.
            if ($outlier = self::OUTLIERS[$daysAgo] ?? null) {
                [$daysBefore, $bedTime] = $outlier['bed'];

                $bed = $date->copy()->subDays($daysBefore)->setTimeFromTimeString($bedTime);
                $wake = $date->copy()->setTimeFromTimeString($outlier['wake']);
                $quality = $outlier['quality'];
                $notes = $outlier['note'];
            }

            $entry = $user->sleepEntries()->create([
                'date' => $date,
                'bedtime' => $bed,
                'wake_time' => $wake,
                'duration_minutes' => (int) $bed->diffInMinutes($wake),
                'quality' => $quality,
                'notes' => $notes,
            ]);

            $entry->habits()->sync($habitIds);
        }
    }
}