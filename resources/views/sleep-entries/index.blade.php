<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Sleep log</h2>
            <div class="flex gap-2">
                <a href="{{ route('habits.index') }}"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase text-gray-700 hover:bg-gray-50">
                    Edit habits
                </a>
                <a href="{{ route('sleep-entries.create') }}"
                    class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase text-white hover:bg-gray-700">
                    Add sleep entry
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-4 rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="overflow-x-auto bg-white shadow sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Slept</th>
                            <th class="px-4 py-3">Duration</th>
                            <th class="px-4 py-3">Quality</th>
                            <th class="px-4 py-3">Habits</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($entries as $entry)
                            <tr>
                                <td class="px-4 py-3">{{ $entry->date->format('D, d M Y') }}</td>
                                <td class="px-4 py-3">
                                    {{ $entry->bedtime->format('H:i') }} → {{ $entry->wake_time->format('H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ intdiv($entry->duration_minutes, 60) }}h {{ $entry->duration_minutes % 60 }}m
                                </td>
                                <td class="px-4 py-3">{{ $entry->quality }}/5</td>
                                <td class="px-4 py-3">{{ $entry->habits->pluck('name')->join(', ') ?: '—' }}</td>
                                <td class="flex gap-3 px-4 py-3">
                                    <a href="{{ route('sleep-entries.edit', $entry) }}"
                                        class="text-indigo-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('sleep-entries.destroy', $entry) }}"
                                        onsubmit="return confirm('Delete this entry?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                    No entries yet. Add your first night of sleep.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $entries->links() }}</div>
        </div>
    </div>
</x-app-layout>