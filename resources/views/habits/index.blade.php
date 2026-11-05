<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Habits</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl space-y-6 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded bg-green-100 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white p-6 shadow sm:rounded-lg">
                <form method="POST" action="{{ route('habits.store') }}" class="flex items-start gap-3">
                    @csrf
                    <div class="flex-1">
                        <x-text-input name="name" type="text" class="block w-full"
                            placeholder="New habit, e.g. Caffeine after 3pm" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <x-primary-button>Add habit</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <ul class="divide-y divide-gray-100">
                    @forelse ($habits as $habit)
                        <li class="flex items-center justify-between px-6 py-3">
                            <div>
                                <span class="font-medium text-gray-800">{{ $habit->name }}</span>
                                <span class="ml-2 text-xs text-gray-500">
                                    used in {{ $habit->sleep_entries_count }} {{ Str::plural('entry', $habit->sleep_entries_count) }}
                                </span>
                            </div>
                            <form method="POST" action="{{ route('habits.destroy', $habit) }}"
                                  onsubmit="return confirm('Delete this habit? It will be removed from all past entries.')">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-red-600 hover:underline">Delete</button>
                            </form>
                        </li>
                    @empty
                        <li class="px-6 py-6 text-center text-gray-500">No habits yet. Add your first one above.</li>
                    @endforelse
                </ul>
            </div>

            <a href="{{ route('sleep-entries.index') }}" class="text-sm text-gray-600 hover:underline">← Back to sleep log</a>
        </div>
    </div>
</x-app-layout>