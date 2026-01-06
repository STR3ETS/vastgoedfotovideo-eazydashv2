<?php

namespace App\Http\Controllers;

use App\Models\OnboardingRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PlanningController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $section = $request->query('section'); // today | planning | qc | team

        if (!$section) {
            return redirect()->route('support.planning.index', [
                'section' => 'today',
                'date'    => now()->toDateString(),
            ]);
        }
        // TODAY
        $selectedDate = null;
        $prevDate = null;
        $nextDate = null;
        $dateLabel = null;
        $todayRequests = collect();

        if ($section === 'today') {
            $dateParam = $request->query('date');

            try {
                $selectedDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : now()->startOfDay();
            } catch (\Throwable $e) {
                $selectedDate = now()->startOfDay();
            }

            $prevDate = $selectedDate->copy()->subDay();
            $nextDate = $selectedDate->copy()->addDay();
            $dateLabel = $selectedDate->copy()->locale('nl')->translatedFormat('l j F Y');

            $todayRequests = OnboardingRequest::query()
                ->with('user')
                ->whereDate('shoot_date', $selectedDate->toDateString())
                ->orderBy('shoot_slot')
                ->orderByDesc('id')
                ->get();
        }

        // PLANNING (lijst)
        $filterDate = null;
        $perPage = (int) $request->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $planningItems = collect();
        if ($section === 'planning') {
            $filterDateParam = $request->query('date');

            try {
                $filterDate = $filterDateParam ? Carbon::parse($filterDateParam)->startOfDay() : null;
            } catch (\Throwable $e) {
                $filterDate = null;
            }

            $q = OnboardingRequest::query()->with('user');

            if ($filterDate) {
                $q->whereDate('shoot_date', $filterDate->toDateString());
            }

            $planningItems = $q
                ->orderByDesc('shoot_date')
                ->orderBy('shoot_slot')
                ->limit($perPage)
                ->get();
        }

        return view('hub.planning.index', compact(
            'user',
            'section',

            'selectedDate',
            'prevDate',
            'nextDate',
            'dateLabel',
            'todayRequests',

            'filterDate',
            'perPage',
            'planningItems'
        ));
    }

    public function edit(OnboardingRequest $onboardingRequest)
    {
        $user = auth()->user();

        return view('hub.planning.edit', compact('user', 'onboardingRequest'));
    }

    public function update(Request $request, OnboardingRequest $onboardingRequest)
    {
        $validated = $request->validate([
            'shoot_date' => ['required', 'date'],
            'shoot_slot' => ['required', 'string', 'max:30'],
            'status'     => ['required', 'string', 'max:20'],
        ]);

        $onboardingRequest->update($validated);

        // terug naar vandaag-overzicht van de gekozen datum
        $date = Carbon::parse($validated['shoot_date'])->toDateString();

        return redirect()
            ->route('support.planning.index', ['section' => 'today', 'date' => $date])
            ->with('success', 'Planning is bijgewerkt.');
    }

    public function destroy(Request $request, OnboardingRequest $onboardingRequest)
    {
        $date = optional($onboardingRequest->shoot_date)->toDateString() ?? now()->toDateString();

        $onboardingRequest->delete();

        return redirect()
            ->route('support.planning.index', ['section' => 'today', 'date' => $date])
            ->with('success', 'Planning is verwijderd.');
    }
}
