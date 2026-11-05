<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Edit sleep entry</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-2xl bg-white p-6 shadow sm:rounded-lg">
            @include('sleep-entries._form', [
                'action' => route('sleep-entries.update', $sleepEntry),
                'sleepEntry' => $sleepEntry,
            ])
        </div>
    </div>
</x-app-layout>