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
            :value="old('date', $sleepEntry?->date?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="bedtime" value="Bedtime" />
        <x-text-input id="bedtime" name="bedtime" type="datetime-local" class="mt-1 block w-full"
            :value="old('bedtime', $sleepEntry?->bedtime?->format('Y-m-d\TH:i'))" required />
        <x-input-error :messages="$errors->get('bedtime')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="wake_time" value="Wake time" />
        <x-text-input id="wake_time" name="wake_time" type="datetime-local" class="mt-1 block w-full"
            :value="old('wake_time', $sleepEntry?->wake_time?->format('Y-m-d\TH:i'))" required />
        <x-input-error :messages="$errors->get('wake_time')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="quality" value="Sleep quality" />
        <select id="quality" name="quality" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            @for ($i = 1; $i <= 5; $i++)
                <option value="{{ $i }}" @selected(old('quality', $sleepEntry?->quality) == $i)>{{ $i }}</option>
            @endfor
        </select>
        <x-input-error :messages="$errors->get('quality')" class="mt-2" />
    </div>

    <div>
        <x-input-label value="Habits that day" />
        @forelse ($habits as $habit)
            <label class="mt-2 flex items-center gap-2">
                <input type="checkbox" name="habits[]" value="{{ $habit->id }}"
                    class="rounded border-gray-300" @checked(in_array($habit->id, $checkedHabits))>
                <span>{{ $habit->name }}</span>
            </label>
        @empty
            <p class="mt-2 text-sm text-gray-500">You haven't created any habits yet.</p>
        @endforelse
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