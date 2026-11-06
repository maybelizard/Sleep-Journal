@php
    $periods = ['week' => 'Last 7 days', 'month' => 'Last 30 days', 'all' => 'All time'];

    $formatDuration = fn ($m) => $m === null
        ? '—'
        : intdiv((int) round($m), 60).'h '.((int) round($m) % 60).'m';

    $formatQuality = fn ($q) => $q === null ? '—' : number_format($q, 1).'/5';

    $qualityWord = fn ($q) => $q === null
        ? ''
        : \App\Models\SleepEntry::QUALITY_LABELS[(int) round($q)];

    $formatSpread = function ($m) {
        if ($m === null) return '—';
        $m = (int) round($m);
        return '± '.($m < 60 ? $m.' min' : intdiv($m, 60).'h '.str_pad($m % 60, 2, '0', STR_PAD_LEFT).'m');
    };

    $spreads = [
        'duration' => 'Sleep duration',
        'bedtime' => 'Bedtime',
        'waketime' => 'Wake time',
    ];

    $formatClock = fn ($m) => $m === null
    ? '—'
    : sprintf('%02d:%02d', intdiv((int) round($m) % 1440, 60), (int) round($m) % 60);

    $ranges = ['30' => '30 days', '90' => '90 days', 'all' => 'All time'];

    $habitColumns = [
        'good' => ['Improve your sleep', 'No habit clearly helps yet.'],
        'bad' => ['Make your sleep worse', 'No habit clearly hurts yet.'],
        'neutral' => ['No clear difference', 'Nothing here yet.'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">

            @if ($averages['nights']['all'] === 0)
                <div class="bg-white p-6 text-center shadow sm:rounded-lg">
                    <p class="text-gray-500">No data yet.</p>
                    <a href="{{ route('sleep-entries.create') }}" class="mt-2 inline-block font-medium text-indigo-600 hover:underline">
                        Log your first night →
                    </a>
                </div>
            @else
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Average sleep duration</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach ($periods as $key => $label)
                            <div>
                                <p class="text-xs text-gray-500">{{ $label }}</p>
                                <p class="mt-1 text-2xl font-semibold text-indigo-600">
                                    {{ $formatDuration($averages['duration'][$key]) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $averages['nights'][$key] }} {{ Str::plural('night', $averages['nights'][$key]) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Average sleep quality</h3>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        @foreach ($periods as $key => $label)
                            <div>
                                <p class="text-xs text-gray-500">{{ $label }}</p>
                                <p class="mt-1 text-2xl font-semibold text-indigo-600">
                                    {{ $formatQuality($averages['quality'][$key]) }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $qualityWord($averages['quality'][$key]) ?: 'no data' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Consistency</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        How far a typical night strays from your average. Lower means more consistent.
                    </p>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-gray-500">
                                <tr>
                                    <th class="py-2 pr-4"></th>
                                    @foreach ($periods as $label)
                                        <th class="px-4 py-2">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($spreads as $metric => $metricLabel)
                                    <tr>
                                        <td class="py-3 pr-4 text-gray-500">{{ $metricLabel }}</td>
                                        @foreach ($periods as $key => $label)
                                            <td class="px-4 py-3 text-lg font-semibold text-indigo-600">
                                                {{ $formatSpread($fluctuations[$metric][$key]) }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Your ideal sleep</h3>

                    @if ($ideal)
                        <p class="mt-1 text-xs text-gray-500">
                            Based on {{ $ideal['nights'] }} nights rated above your average of
                            {{ number_format($ideal['avg_quality'], 1) }}/5, with better nights counting more.
                        </p>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <p class="text-xs text-gray-500">Go to bed around</p>
                                <p class="mt-1 text-2xl font-semibold text-indigo-600">{{ $formatClock($ideal['bedtime']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Wake up around</p>
                                <p class="mt-1 text-2xl font-semibold text-indigo-600">{{ $formatClock($ideal['waketime']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Sleep for about</p>
                                <p class="mt-1 text-2xl font-semibold text-indigo-600">{{ $formatDuration($ideal['duration']) }}</p>
                            </div>
                        </div>
                    @else
                        <p class="mt-2 text-sm text-gray-500">
                            Not enough data yet. This needs at least 3 nights rated above your average quality.
                        </p>
                    @endif
                </div>

                {{-- ================= Charts ================= --}}
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Evolution</h3>
                        <div class="flex gap-3 text-sm">
                            @foreach ($ranges as $key => $label)
                                <a href="{{ route('dashboard', ['range' => $key]) }}"
                                class="{{ $range === (string) $key ? 'font-semibold text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if (count($charts['labels']) > 0)
                        <script type="application/json" id="chart-data">@json($charts, JSON_HEX_TAG)</script>

                        <div class="mt-4 grid gap-8 lg:grid-cols-2">
                            <div>
                                <p class="mb-2 text-sm text-gray-500">Bedtime</p>
                                <div class="relative h-56"><canvas id="chart-bedtime"></canvas></div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm text-gray-500">Wake time</p>
                                <div class="relative h-56"><canvas id="chart-waketime"></canvas></div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm text-gray-500">Sleep duration</p>
                                <div class="relative h-56"><canvas id="chart-duration"></canvas></div>
                            </div>
                            <div>
                                <p class="mb-2 text-sm text-gray-500">Sleep quality</p>
                                <div class="relative h-56"><canvas id="chart-quality"></canvas></div>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">No nights logged in this period.</p>
                    @endif
                </div>

                {{-- ================= Best / worst night ================= --}}
                @if ($extremes['best'])
                    <div class="bg-white p-6 shadow sm:rounded-lg">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Best and worst night</h3>
                        <p class="mt-1 text-xs text-gray-500">Ranked by quality first, then by sleep duration. All time.</p>

                        <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            @foreach (['best' => 'Best night', 'worst' => 'Worst night'] as $key => $title)
                                @php $night = $extremes[$key]; @endphp
                                <div>
                                    <p class="text-xs text-gray-500">{{ $title }}</p>

                                    @if ($night)
                                        <p class="mt-1 text-lg font-semibold text-indigo-600">{{ $night->date->format('D, d M Y') }}</p>
                                        <p class="text-sm text-gray-800">
                                            {{ $night->quality_label }} ({{ $night->quality }}/5) ·
                                            {{ $formatDuration($night->duration_minutes) }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $night->bedtime->format('H:i') }} → {{ $night->wake_time->format('H:i') }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">
                                            Habits: {{ $night->habits->pluck('name')->join(', ') ?: 'none' }}
                                        </p>
                                        @if ($night->notes)
                                            <p class="mt-1 text-sm italic text-gray-500">“{{ Str::limit($night->notes, 120) }}”</p>
                                        @endif
                                        <a href="{{ route('sleep-entries.edit', $night) }}"
                                        class="mt-2 inline-block text-sm text-indigo-600 hover:underline">View / edit</a>
                                    @else
                                        <p class="mt-1 text-sm text-gray-500">Log at least two nights to compare.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ================= Habits ================= --}}
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Habits and your sleep</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Nights with a habit compared to nights without it
                        (a habit needs at least {{ $habitInsights['min_nights'] }} nights both with and without to be judged.)
                    </p>

                    @if ($habitInsights['ranked_count'] === 0)
                        <p class="mt-4 text-sm text-gray-500">
                            Not enough data yet. Keep logging habits on your nights.
                        </p>
                    @else
                        <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            @foreach (['best' => 'Best habit', 'worst' => 'Worst habit'] as $key => $title)
                                <div>
                                    <p class="text-xs text-gray-500">{{ $title }}</p>
                                    @if ($habitInsights[$key])
                                        @include('dashboard._habit-effect', ['h' => $habitInsights[$key]])
                                    @else
                                        <p class="text-sm text-gray-500">Needs at least two judged habits.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-6 border-t border-gray-100 pt-6 lg:grid-cols-3">
                            @foreach ($habitColumns as $verdict => [$title, $emptyText])
                                <div>
                                    <p class="text-sm font-semibold text-indigo-600">{{ $title }}</p>
                                    <div class="mt-3 space-y-3">
                                        @forelse ($habitInsights[$verdict] as $h)
                                            <div>@include('dashboard._habit-effect', ['h' => $h])</div>
                                        @empty
                                            <p class="text-sm text-gray-500">{{ $emptyText }}</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if (count($habitInsights['insufficient']) > 0)
                            <p class="mt-6 text-xs text-gray-500">
                                Not enough data yet for:
                                {{ collect($habitInsights['insufficient'])->map(fn ($h) => "{$h['name']} ({$h['n_with']} with / {$h['n_without']} without)")->join(', ') }}
                            </p>
                        @endif
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>