<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('app')->group(function () {

    // OTP
    Route::post('/email',  [AuthController::class, 'sendLoginToken'])->name('support.send_token');
    Route::post('/verify', [AuthController::class, 'verifyLoginToken'])->name('support.verify_token');
    Route::post('/resend', [AuthController::class, 'resendLoginToken'])->name('support.resend_token');
    
    Route::middleware('auth')->group(function () {
        Route::patch('/first-login-dismiss', [AuthController::class, 'dismissFirstLogin'])->name('support.first_login.dismiss');
        Route::get('/', fn() => view('hub.index', ['user' => auth()->user()]))->name('support.dashboard');
        Route::patch('/tasks/questions/{question}', [TaskQuestionController::class, 'update'])->name('support.tasks.questions.update');

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

        // SEO Audit
        Route::prefix('seo-audit')
            ->name('support.seo-audit.')
            ->controller(SeoAuditController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/', 'store')->name('store');
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
