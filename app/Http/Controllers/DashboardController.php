<?php

namespace App\Http\Controllers;

use App\Services\SleepStats;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $stats = new SleepStats($request->user());

        $range = in_array($request->query('range'), ['30', '90', 'all'], true)
            ? $request->query('range')
            : '30';
        $days = $range === 'all' ? null : (int) $range;

        return view('dashboard', [
            'averages' => $stats->averages(),
            'fluctuations' => $stats->fluctuations(),
            'ideal' => $stats->ideal(),
            'charts' => $stats->series($days),
            'range' => $range,
            'extremes' => $stats->extremes(),
            'habitInsights' => $stats->habitInsights(),
        ]);
    }
}