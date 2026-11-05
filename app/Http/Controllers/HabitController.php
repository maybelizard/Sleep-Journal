<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HabitController extends Controller
{
    public function index(Request $request): View
    {
        $habits = $request->user()
            ->habits()
            ->withCount('sleepEntries')
            ->orderBy('name')
            ->get();

        return view('habits.index', compact('habits'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('habits')->where('user_id', $request->user()->id),
            ],
        ]);

        $habit = $request->user()->habits()->create($data);

        if ($request->wantsJson()) {
            return response()->json($habit, 201);
        }

        return redirect()->route('habits.index')->with('status', 'Habit added.');
    }

    public function destroy(Habit $habit): RedirectResponse
    {
        Gate::authorize('delete', $habit);

        $habit->delete();

        return redirect()->route('habits.index')->with('status', 'Habit deleted.');
    }
}