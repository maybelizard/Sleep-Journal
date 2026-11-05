<?php

namespace App\Http\Controllers;

use App\Http\Requests\SleepEntryRequest;
use App\Models\SleepEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SleepEntryController extends Controller
{
    public function index(Request $request): View
    {
        $entries = $request->user()
            ->sleepEntries()
            ->with('habits')
            ->latest('date')
            ->paginate(10);

        return view('sleep-entries.index', compact('entries'));
    }

    public function create(Request $request): View
    {
        $habits = $request->user()->habits()->orderBy('name')->get();

        return view('sleep-entries.create', compact('habits'));
    }

    public function store(SleepEntryRequest $request): RedirectResponse
    {
        $data = $request->entryData();
        $habitIds = $request->validated('habits', []);

        $entry = $request->user()->sleepEntries()->create($data);
        $entry->habits()->sync($habitIds);

        return redirect()->route('sleep-entries.index')
            ->with('status', 'Sleep entry saved.');
    }

    public function edit(Request $request, SleepEntry $sleepEntry): View
    {
        Gate::authorize('update', $sleepEntry);

        $habits = $request->user()->habits()->orderBy('name')->get();
        $sleepEntry->load('habits');

        return view('sleep-entries.edit', compact('sleepEntry', 'habits'));
    }

    public function update(SleepEntryRequest $request, SleepEntry $sleepEntry): RedirectResponse
    {
        Gate::authorize('update', $sleepEntry);

        $data = $request->entryData();
        $habitIds = $request->validated('habits', []);

        $sleepEntry->update($data);
        $sleepEntry->habits()->sync($habitIds);

        return redirect()->route('sleep-entries.index')
            ->with('status', 'Sleep entry updated.');
    }

    public function destroy(SleepEntry $sleepEntry): RedirectResponse
    {
        Gate::authorize('delete', $sleepEntry);

        $sleepEntry->delete();

        return redirect()->route('sleep-entries.index')
            ->with('status', 'Sleep entry deleted.');
    }
}