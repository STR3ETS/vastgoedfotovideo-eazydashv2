<?php

use Illuminate\Support\Facades\Route;

use App\Models\Offerte;
use App\Models\WorkSession;

use App\Http\Controllers\AanvraagController;
use App\Http\Controllers\PotentieleKlantenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GebruikersController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\InstellingenController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SeoAuditController;
use App\Http\Controllers\TaskQuestionController;
use App\Http\Controllers\AanvraagFileController;
use App\Http\Controllers\IntakeController;
use App\Http\Controllers\ProjectenController;
use App\Http\Controllers\ProjectPreviewController;
use App\Http\Controllers\OfferteController;
use App\Http\Controllers\WorkSessionController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MailingController;
use App\Http\Controllers\SeoProjectController;
use App\Http\Controllers\SocialsController;

// eazyonline.nl website
Route::view('/', 'website.home')->name('pages.home');
Route::post('/aanvraag/website', [AanvraagController::class, 'storeWebsiteAanvraag']);

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('support.login');

Route::middleware('guest')
    ->prefix('register')
    ->name('onboarding.')
    ->controller(OnboardingController::class)
    ->group(function () {
        Route::get('/account', 'account')->name('account');

        Route::get ('/step-1', 'step1')->name('step1');
        Route::post('/step-1', 'storeStep1')->name('step1.store');

        Route::get ('/step-2', 'step2')->name('step2');
        Route::post('/step-2', 'storeStep2')->name('step2.store');

        Route::get ('/step-3', 'step3')->name('step3');
        Route::post('/step-3', 'storeStep3')->name('step3.store');

        Route::post('/finish', 'finish')->name('finish');
});

Route::prefix('preview')
    ->name('preview.')
    ->controller(ProjectPreviewController::class)
    ->group(function () {
        Route::get('/{token}', 'show')->name('show');
        Route::post('/{token}/feedback', 'storeFeedback')->name('feedback.store');
    });

Route::prefix('offerte')
    ->name('offerte.')
    ->controller(OfferteController::class)
    ->group(function () {
        Route::get('/{token}', 'klant')->name('klant.show');
        Route::post('/{token}/sign', 'sign')->name('sign');
        Route::get('/{token}/edit', 'beheerder')->name('beheerder.show');
        Route::get('/{token}/download', 'download')->name('download');
        Route::post('/{token}/regenerate', 'regenerate')->name('regenerate');
        Route::post('/{token}/inline', 'inlineUpdate')->name('inline-update');
        Route::post('/{token}/revoke', 'revoke')->name('revoke');
        Route::post('/{token}/send', 'send')->name('send');
    });

Route::prefix('app')->group(function () {

    // OTP
    Route::post('/email',  [AuthController::class, 'sendLoginToken'])->name('support.send_token');
    Route::post('/verify', [AuthController::class, 'verifyLoginToken'])->name('support.verify_token');
    Route::post('/resend', [AuthController::class, 'resendLoginToken'])->name('support.resend_token');
    
    Route::middleware('auth')->group(function () {
        Route::patch('/first-login-dismiss', [AuthController::class, 'dismissFirstLogin'])->name('support.first_login.dismiss');
        
        Route::get('/', function () {
            $user = auth()->user();
            $now  = now();
            $today      = $now->toDateString();
            $weekStart  = $now->copy()->startOfWeek();
            $weekEnd    = $now->copy()->endOfWeek();
            $monthStart = $now->copy()->startOfMonth();
            $monthEnd   = $now->copy()->endOfMonth();
            $activeSession = $user->workSessions()
                ->whereNull('clock_out_at')
                ->latest('clock_in_at')
                ->first();
            $activeSeconds = 0;
            if ($activeSession) {
                $activeSeconds = $now->diffInSeconds($activeSession->clock_in_at);
            }
            $calcTotal = function ($sessions) {
                return $sessions->reduce(function ($carry, \App\Models\WorkSession $session) {
                    if ($session->clock_out_at) {
                        $seconds = $session->worked_seconds
                            ?? $session->clock_out_at->diffInSeconds($session->clock_in_at);
                    } else {
                        // lopende sessie meenemen
                        $seconds = now()->diffInSeconds($session->clock_in_at);
                    }

                    return $carry + max(0, $seconds);
                }, 0);
            };
            $todaySeconds = $calcTotal(
                $user->workSessions()
                    ->whereDate('clock_in_at', $today)
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
            return view('hub.index', [
                'user'          => $user,
                'activeSession' => $activeSession,
                'activeSeconds' => $activeSeconds,
                'todaySeconds'  => $todaySeconds,
                'weekSeconds'   => $weekSeconds,
                'monthSeconds'  => $monthSeconds,
            ]);
        })->name('support.dashboard');

        Route::post('/work/clock-in',  [WorkSessionController::class, 'clockIn'])->name('support.work.clock_in');
        Route::post('/work/clock-out', [WorkSessionController::class, 'clockOut'])->name('support.work.clock_out');

        Route::get('/sales/offertes', function () {
            $user = auth()->user();
            $offertes = Offerte::with('project')
                ->orderByDesc('created_at')
                ->get();
            return view('hub.overzicht.offertes', compact('user', 'offertes'));
        })->name('support.dashboard.offertes');

        Route::patch('/tasks/questions/{question}', [TaskQuestionController::class, 'update'])->name('support.tasks.questions.update');

        // Intake
        Route::prefix('support/intake')
            ->name('support.intake.')
            ->controller(IntakeController::class)
            ->group(function () {
                Route::get('/availability', 'availability')->name('availability');
                Route::patch('/{aanvraag}/complete', 'complete')->name('complete');
                Route::patch('/{aanvraag}/clear', 'clear')->name('clear');
        });

        // Support
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

        // Potentiële klanten
        Route::prefix('potentiele-klanten')
            ->name('support.potentiele-klanten.')
            ->controller(PotentieleKlantenController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::patch('/{aanvraag}/status', 'updateStatus')->name('status.update');
                Route::post('/{aanvraag}/calls', 'storeCall')->name('calls.store');
                Route::post('/{aanvraag}/files', [AanvraagFileController::class, 'store'])->name('files.store');
                Route::delete('/files/{file}', [AanvraagFileController::class, 'destroy'])->name('files.destroy');
                Route::get('/files/{file}/download', [AanvraagFileController::class, 'download'])->name('files.download');
        });

        // Projecten
        Route::prefix('projecten')
            ->name('support.projecten.')
            ->controller(ProjectenController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::patch('/{project}/status', 'updateStatus')->name('status.update');
                Route::patch('/{project}/preview', 'updatePreview')->name('preview.update');
                Route::patch('/{project}/offerte-notes', 'updateOfferteNotes')->name('offerte_notes.update');
                Route::patch('/{project}/offerte-complete', 'completeOfferteTask')->name('offerte.complete');
                Route::post('/{project}/calls', 'storeCall')->name('calls.store');
                Route::post('/{project}/offerte-generate', 'generateOfferte')->name('offerte.generate');
        });

        Route::prefix('seo')
            ->name('support.seo.')
            ->controller(SeoProjectController::class)
            ->group(function () {
                Route::get('/projects', 'index')->name('projects.index');
                Route::get('/projects/create', 'create')->name('projects.create');
                Route::post('/projects', 'store')->name('projects.store');
                Route::get('/projects/{seoProject}', 'show')->name('projects.show');
                Route::get('/projects/{seoProject}/edit', 'edit')->name('projects.edit');
                Route::patch('/projects/{seoProject}', 'update')->name('projects.update');
            });

        // Marketing
        Route::prefix('marketing')
            ->name('support.marketing.')
            ->group(function () {
                Route::get('/', [MarketingController::class, 'index'])->name('index');

                Route::prefix('mailing')
                    ->name('mailing.')
                    ->controller(MailingController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');

                        Route::get('/nieuwsbrieven', 'nieuwsbrievenIndex')->name('nieuwsbrievenIndex');

                        Route::get('/templates', 'templatesIndex')->name('templatesIndex');
                        Route::get('/templates/nieuwsbrief-templates', 'nieuwsbriefTemplates')->name('nieuwsbriefTemplates');
                        Route::post('/templates/nieuwsbrief-templates/quick-create', 'quickCreateNieuwsbriefTemplate')->name('nieuwsbriefTemplates.quickCreate');
                        Route::get('/templates/actie-aanbod-templates', 'actieAanbodTemplates')->name('actieAanbodTemplates');
                        Route::get('/templates/onboarding-opvolg-templates', 'onboardingOpvolgTemplates')->name('onboardingOpvolgTemplates');
                        Route::patch('/templates/nieuwsbrief-templates/{template}', 'updateNieuwsbriefTemplate')->name('nieuwsbriefTemplates.update');

                        Route::get('/campagnes', 'campagnesIndex')->name('campagnesIndex');
                    });

                Route::prefix('socials')
                    ->name('socials.')
                    ->controller(SocialsController::class)
                    ->group(function () {
                        Route::get('/', 'index')->name('index');

                        Route::get('/contentkalender', 'contentkalenderIndex')->name('contentkalenderIndex');
                        Route::get('/posts', 'postsIndex')->name('postsIndex');
                        Route::get('/activiteiten', 'activiteitenIndex')->name('activiteitenIndex');
                    });
        });

        // Gebruikers
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

        // Instellingen
        Route::prefix('instellingen')
            ->name('support.instellingen.')
            ->controller(InstellingenController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::get('/persoonlijk', 'personal')->name('personal');
                Route::get('/bedrijf', 'company')->name('company');
                Route::get('/team', 'team')->name('team');
                Route::get('/billing', 'billing')->name('billing');
                Route::get('/documenten', 'documents')->name('documents');

                Route::patch('/persoonlijke-gegevens', 'update')->name('update');
                Route::patch('/bedrijf', 'updateCompany')->name('company.update');
                Route::post('/team/invite', [TeamInviteController::class, 'send'])->middleware('throttle:10,1')->name('team.invite');
        });

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('support.logout');
    });

    Route::get ('/instellingen/team/invite/{token}', [TeamInviteController::class, 'showAccept'])->name('support.instellingen.team.invite.accept');
    Route::post('/instellingen/team/invite/{token}', [TeamInviteController::class, 'handleAccept'])->name('support.instellingen.team.invite.handle');
});

// Redirects (bestaande)
Route::redirect('/support', '/service-hub');
Route::redirect('/support/login', '/login');
