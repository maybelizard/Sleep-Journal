@php
    $selected = $sleepEntry ? $sleepEntry->habits->pluck('id')->all() : [];
    $checkedHabits = session()->hasOldInput() ? old('habits', []) : $selected;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($sleepEntry)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="date" value="Date (the morning you woke up)" />
        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
            max="{{ now()->format('Y-m-d') }}"
            :value="old('date', $sleepEntry?->date?->format('Y-m-d') ?? now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date')" class="mt-2" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <x-input-label for="bedtime" value="Bedtime" />
            <x-text-input id="bedtime" name="bedtime" type="time" class="mt-1 block w-full"
                :value="old('bedtime', $sleepEntry?->bedtime?->format('H:i'))" required />
            <x-input-error :messages="$errors->get('bedtime')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="wake_time" value="Wake time" />
            <x-text-input id="wake_time" name="wake_time" type="time" class="mt-1 block w-full"
                :value="old('wake_time', $sleepEntry?->wake_time?->format('H:i'))" required />
            <x-input-error :messages="$errors->get('wake_time')" class="mt-2" />
        </div>
    </div>

    <div>
        <x-input-label value="Sleep quality" />
        <div class="mt-2 grid grid-cols-5 gap-2">
            @foreach (\App\Models\SleepEntry::QUALITY_LABELS as $value => $label)
                <label class="quality-option">
                    <input type="radio" name="quality" value="{{ $value }}" class="sr-only"
                        @checked((int) old('quality', $sleepEntry?->quality) === $value) required>
                    <span><b>{{ $value }}</b>{{ $label }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('quality')" class="mt-2" />
    </div>

    <div>
        <x-input-label value="Habits that day" />

        <div id="habit-list">
            @foreach ($habits as $habit)
                <label class="mt-2 flex items-center gap-2">
                    <input type="checkbox" name="habits[]" value="{{ $habit->id }}" class="rounded border-gray-300"
                        @checked(in_array($habit->id, $checkedHabits))>
                    <span>{{ $habit->name }}</span>
                </label>
            @endforeach
        </div>

        <p id="no-habits" class="mt-2 text-sm text-gray-500 {{ $habits->isNotEmpty() ? 'hidden' : '' }}">
            You haven't created any habits yet.
        </p>

        <div class="mt-3 flex items-center gap-2">
            <input id="new-habit-name" type="text" maxlength="100" placeholder="New habit, e.g. Gym in the morning"
                class="block w-full rounded-md border-gray-300 text-sm shadow-sm">
            <button type="button" id="add-habit-btn"
                class="whitespace-nowrap rounded-md border border-gray-300 bg-white px-3 py-2 text-xs font-semibold uppercase text-gray-700 hover:bg-gray-50">
                Add habit
            </button>
        </div>
        <p id="new-habit-error" class="mt-2 text-sm text-red-600"></p>

        <x-input-error :messages="$errors->get('habits.*')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('notes', $sleepEntry?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>Save</x-primary-button>
        <a href="{{ route('sleep-entries.index') }}" class="text-sm text-gray-600 underline">Cancel</a>
    </div>
</form>

<script>
    (() => {
        const input = document.getElementById('new-habit-name');
        const button = document.getElementById('add-habit-btn');
        const list = document.getElementById('habit-list');
        const empty = document.getElementById('no-habits');
        const error = document.getElementById('new-habit-error');
        const token = document.querySelector('input[name="_token"]').value;

        async function addHabit() {
            const name = input.value.trim();
            if (!name) return;

            error.textContent = '';
            button.disabled = true;

            try {
                const response = await fetch(@json(route('habits.store')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ name }),
                });
                const data = await response.json();

                if (!response.ok) {
                    error.textContent = data.errors?.name?.[0] ?? 'Could not add the habit.';
                    return;
                }

                const label = document.createElement('label');
                label.className = 'mt-2 flex items-center gap-2';

                const box = document.createElement('input');
                box.type = 'checkbox';
                box.name = 'habits[]';
                box.value = data.id;
                box.checked = true;
                box.className = 'rounded border-gray-300';

                const text = document.createElement('span');
                text.textContent = data.name;

                label.append(box, text);
                list.append(label);
                empty.classList.add('hidden');
                input.value = '';
            } catch (e) {
                error.textContent = 'Network error, please try again.';
            } finally {
                button.disabled = false;
            }
        }

        button.addEventListener('click', addHabit);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addHabit();
            }
        });
    })();
</script>