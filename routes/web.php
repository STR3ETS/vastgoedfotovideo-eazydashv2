<?php

use Illuminate\Support\Facades\Route;

use App\Models\Offerte;
use App\Models\AanvraagWebsite;
use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WorkSessionController;
use App\Http\Controllers\TaskQuestionController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\ProjectenController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\GebruikersController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\OnboardingController;

// ✅ Landing/login
Route::view('/', 'auth.login')->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('support.login');

Route::prefix('app')->group(function () {

    // ✅ Auth (OTP)
    Route::post('/email',  [AuthController::class, 'sendLoginToken'])->name('support.send_token');
    Route::post('/verify', [AuthController::class, 'verifyLoginToken'])->name('support.verify_token');
    Route::post('/resend', [AuthController::class, 'resendLoginToken'])->name('support.resend_token');

    // ✅ Invite accept (auth-gerelateerd, buiten auth middleware)
    Route::get ('/instellingen/team/invite/{token}', [TeamInviteController::class, 'showAccept'])->name('support.instellingen.team.invite.accept');
    Route::post('/instellingen/team/invite/{token}', [TeamInviteController::class, 'handleAccept'])->name('support.instellingen.team.invite.handle');

    Route::middleware('auth')->group(function () {

        // ✅ Dashboard
        Route::patch('/first-login-dismiss', [AuthController::class, 'dismissFirstLogin'])->name('support.first_login.dismiss');

        Route::get('/', function () {
            $user = auth()->user();
            $now  = now();

            $todayDate  = $now->toDateString();
            $weekStart  = $now->copy()->startOfWeek();
            $weekEnd    = $now->copy()->endOfWeek();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd   = $now->copy()->endOfMonth();

            // Actieve work session
            $activeSession = $user->workSessions()
                ->whereNull('clock_out_at')
                ->latest('clock_in_at')
                ->first();

            $activeSeconds = 0;
            if ($activeSession) {
                $activeSeconds = $now->diffInSeconds($activeSession->clock_in_at);
            }

            // Totalen helper
            $calcTotal = function ($sessions) {
                return $sessions->reduce(function ($carry, \App\Models\WorkSession $session) {
                    if ($session->clock_out_at) {
                        $seconds = $session->worked_seconds
                            ?? $session->clock_out_at->diffInSeconds($session->clock_in_at);
                    } else {
                        $seconds = now()->diffInSeconds($session->clock_in_at);
                    }

                    return $carry + max(0, $seconds);
                }, 0);
            };

            $todaySeconds = $calcTotal(
                $user->workSessions()
                    ->whereDate('clock_in_at', $todayDate)
                    ->get()
            );

            $weekSeconds = $calcTotal(
                $user->workSessions()
                    ->whereBetween('clock_in_at', [$weekStart, $weekEnd])
                    ->get()
            );

            $monthSeconds = $calcTotal(
                $user->workSessions()
                    ->whereBetween('clock_in_at', [$monthStart, $monthEnd])
                    ->get()
            );

            // Intakes van vandaag
            $intakesToday = AanvraagWebsite::with('owner')
                ->whereNotNull('intake_at')
                ->whereDate('intake_at', $todayDate)
                ->whereHas('owner', function ($q) use ($user) {
                    $q->where('id', $user->id);
                })
                ->orderBy('intake_at')
                ->get();

            // Teamleden (zelfde company of intern)
            $teamMembersQuery = User::query();

            if (!empty($user->company_id)) {
                $teamMembersQuery->where('company_id', $user->company_id);
            } else {
                $teamMembersQuery->whereNull('company_id');
            }

            $teamMembers = $teamMembersQuery
                ->orderBy('name')
                ->get()
                ->map(function (User $member) use ($now) {
                    $lastSession = $member->workSessions()
                        ->orderByDesc('clock_in_at')
                        ->first();

                    $isOnline   = false;
                    $statusText = 'Nog geen sessies';

                    if ($lastSession) {
                        $isOnline = is_null($lastSession->clock_out_at);

                        if ($isOnline) {
                            $statusText = 'Online sinds ' . $lastSession->clock_in_at->format('H:i');
                        } else {
                            $clockOut = $lastSession->clock_out_at;

                            if ($clockOut->isYesterday()) {
                                $statusText = 'Offline sinds gisteren ' . $clockOut->format('H:i');
                            } elseif ($clockOut->lt($now->copy()->startOfDay())) {
                                $statusText = 'Offline sinds ' . $clockOut->format('d-m H:i');
                            } else {
                                $statusText = 'Offline sinds ' . $clockOut->format('H:i');
                            }
                        }
                    }

                    $avatar = trim((string) $member->avatar_url);

                    if ($avatar === '') {
                        $avatar = '/assets/eazyonline/memojis/default.webp';
                    } else {
                        if (!preg_match('~^https?://~i', $avatar) && ($avatar[0] ?? '') !== '/') {
                            $avatar = '/' . $avatar;
                        }

                        if (!preg_match('~^https?://~i', $avatar)) {
                            $rel = ltrim($avatar, '/');
                            if (!file_exists(public_path($rel))) {
                                $avatar = '/assets/eazyonline/memojis/default.webp';
                            }
                        }
                    }

                    return (object) [
                        'id'          => $member->id,
                        'name'        => $member->name,
                        'avatar'      => $avatar,
                        'is_online'   => $isOnline,
                        'status_text' => $statusText,
                    ];
                });

            // Timeline config
            $startHour   = 9;
            $endHour     = 17;

            $rowHeightPx = 28;
            $gapPx       = 16;
            $slotPx      = $rowHeightPx + $gapPx;
            $pxPerMinute = $slotPx / 60;

            $leftOffsetPx = 65;

            $intakeCards = $intakesToday
                ->map(function (AanvraagWebsite $aanvraag) use (
                    $startHour,
                    $pxPerMinute,
                    $leftOffsetPx,
                    $rowHeightPx,
                    $slotPx
                ) {
                    if (!$aanvraag->intake_at) {
                        return null;
                    }

                    $start    = \Carbon\Carbon::parse($aanvraag->intake_at);
                    $duration = (int) ($aanvraag->intake_duration ?? 30);

                    $companyName = $aanvraag->company ?? 'bedrijf';

                    $minutesFromStart = max(
                        0,
                        (($start->hour - $startHour) * 60) + $start->minute
                    );

                    $heightPx = (int) max(44, round(($duration / 60) * $slotPx));

                    $centerY = ($minutesFromStart / 60) * $slotPx + ($rowHeightPx / 2);
                    $topPx = (int) max(0, round($centerY - ($heightPx / 2)));

                    $finalTopPx = $topPx - $slotPx;

                    if ($minutesFromStart === 0) {
                        $finalTopPx -= 8;
                    }

                    return (object) [
                        'aanvraag_id'  => $aanvraag->id,
                        'company'      => $companyName,
                        'start'        => $start,
                        'duration'     => $duration,
                        'topPx'        => $finalTopPx,
                        'heightPx'     => $heightPx,
                        'leftOffsetPx' => $leftOffsetPx,
                        'url'          => '#',
                    ];
                })
                ->filter()
                ->values();

            $formatDuration = function (int $seconds): string {
                $h = intdiv($seconds, 3600);
                $m = intdiv($seconds % 3600, 60);
                return sprintf('%d uur %02d minuten', $h, $m);
            };

            return view('hub.index', [
                'user'            => $user,
                'activeSession'   => $activeSession,
                'activeSeconds'   => $activeSeconds,
                'todaySeconds'    => $todaySeconds,
                'weekSeconds'     => $weekSeconds,
                'monthSeconds'    => $monthSeconds,

                'intakesToday'    => $intakesToday,
                'intakeTimeline'  => [
                    'startHour'     => $startHour,
                    'endHour'       => $endHour,
                    'rowHeightPx'   => $rowHeightPx,
                    'gapPx'         => $gapPx,
                    'slotPx'        => $slotPx,
                    'pxPerMinute'   => $pxPerMinute,
                    'leftOffsetPx'  => $leftOffsetPx,
                ],
                'intakeCards'     => $intakeCards,
                'formatDuration'  => $formatDuration,
                'teamMembers'     => $teamMembers,
            ]);
        })->name('support.dashboard');

        // Dashboard acties
        Route::post('/work/clock-in',  [WorkSessionController::class, 'clockIn'])->name('support.work.clock_in');
        Route::post('/work/clock-out', [WorkSessionController::class, 'clockOut'])->name('support.work.clock_out');

        // ✅ Planning & Management (menu)
        // Pas de view aan naar jouw echte blade indien nodig
        Route::view('/planning-management', 'hub.planning.index')->name('support.planning.index');

        // Planning endpoints die je al had (onderdeel van planning)
        Route::prefix('support/intake')
            ->name('support.intake.')
            ->controller(IntakeController::class)
            ->group(function () {
                Route::get('/availability', 'availability')->name('availability');
                Route::patch('/{aanvraag}/complete', 'complete')->name('complete');
                Route::patch('/{aanvraag}/clear', 'clear')->name('clear');
            });

        // ✅ Taken (menu)
        Route::view('/taken', 'hub.taken.index')->name('support.taken.index');
        Route::patch('/tasks/questions/{question}', [TaskQuestionController::class, 'update'])->name('support.tasks.questions.update');

        // ✅ Onboarding (menu)
        Route::prefix('onboarding')
            ->name('support.onboarding.')
            ->controller(OnboardingController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
            });

        // ✅ Financiën (menu) - jouw bestaande offertes-overzicht verhuisd hierheen
        Route::get('/financien', function () {
            $user = auth()->user();
            $offertes = Offerte::with('project')
                ->orderByDesc('created_at')
                ->get();

            return view('hub.overzicht.offertes', compact('user', 'offertes'));
        })->name('support.financien.index');

        // ✅ Ondersteuning (menu) - je bestaande support routes
        Route::prefix('support')
            ->name('support.')
            ->controller(SupportController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::prefix('tickets')
                    ->as('tickets.')
                    ->group(function () {
                        Route::get('/openstaand',     'open')->name('openstaand');
                        Route::get('/in-behandeling', 'inBehandeling')->name('in_behandeling');
                        Route::get('/gesloten',       'gesloten')->name('gesloten');
                    });
            });

        // ✅ Projecten (menu)
        Route::prefix('projecten')
            ->middleware('company_id:1')
            ->name('support.projecten.')
            ->controller(ProjectenController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::patch('/{project}/status', 'updateStatus')->name('status.update');
                Route::patch('/{project}/tasks/status', 'updateTaskStatus')->name('tasks.status.update');
                Route::patch('/{project}/assignee', 'updateAssignee')->name('assignee.update');
                Route::patch('/{project}/preview', 'updatePreview')->name('preview.update');
                Route::patch('/{project}/offerte-notes', 'updateOfferteNotes')->name('offerte_notes.update');
                Route::patch('/{project}/offerte-complete', 'completeOfferteTask')->name('offerte.complete');
                Route::post('/{project}/calls', 'storeCall')->name('calls.store');
                Route::post('/{project}/offerte-generate', 'generateOfferte')->name('offerte.generate');
            });

        // ✅ Gebruikers (menu)
        Route::prefix('gebruikers')
            ->name('support.gebruikers.')
            ->controller(GebruikersController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/klanten', 'klanten')->name('klanten');
                Route::get('/medewerkers', 'medewerkers')->name('medewerkers');

                Route::post('/', 'store')->name('store');

                Route::get   ('/klanten/{klant}', 'showKlant')->name('klanten.show');
                Route::patch ('/klanten/{klant}', 'updateKlant')->name('klanten.update');
                Route::delete('/klanten/{klant}', 'destroyKlant')->name('klanten.destroy');

                Route::get   ('/medewerkers/{medewerker}', 'showMedewerker')->name('medewerkers.show');
                Route::patch ('/medewerkers/{medewerker}', 'updateMedewerker')->name('medewerkers.update');
                Route::delete('/medewerkers/{medewerker}', 'destroyMedewerker')->name('medewerkers.destroy');

                Route::get('/bedrijven', 'bedrijven')->name('bedrijven');
                Route::get('/bedrijven/{company}', 'bedrijfShow')->name('bedrijven.show');
                Route::post('/bedrijven', 'storeBedrijf')->name('bedrijven.store');
                Route::get ('/bedrijven/{company}/personen', 'bedrijfPersonen')->name('bedrijven.personen');
                Route::post('/bedrijven/{company}/personen/koppel', 'bedrijfPersonenKoppel')->name('bedrijven.personen.koppel');
                Route::delete('/bedrijven/{company}/personen/{user}', 'bedrijfPersonenOntkoppel')->name('bedrijven.personen.ontkoppel');
                Route::get('/bedrijven/{company}/personen/lijst', 'bedrijfPersonenLijst')->name('bedrijven.personen.lijst');
                Route::post('/bedrijven/{company}/personen/{user}/toggle-admin', 'bedrijfToggleAdmin')->name('bedrijven.admin.toggle');
            });

        // ✅ Logout (auth)
        Route::post('/logout', [AuthController::class, 'logout'])->name('support.logout');
    });
});
