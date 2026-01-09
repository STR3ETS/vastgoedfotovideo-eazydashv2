<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeamInviteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTakenController;
use App\Http\Controllers\ProjectTaskChatController;
use App\Http\Controllers\ProjectTaskSubtaskController;
use App\Http\Controllers\ProjectFinancienController;
use App\Http\Controllers\ProjectQuoteController;
use App\Http\Controllers\TakenController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\FinancienController;
use App\Http\Controllers\GebruikersController;
use App\Http\Controllers\PlanningController;

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
            return view('hub.index', [
                'user' => auth()->user(),
            ]);
        })->name('support.dashboard');

        Route::get('/planning-management', [PlanningController::class, 'index'])->name('support.planning.index');
        Route::get('/planning-management/{planningItem}/bewerken', [PlanningController::class, 'edit'])->name('support.planning.edit');
        Route::patch('/planning-management/{planningItem}', [PlanningController::class, 'update'])->name('support.planning.update');
        Route::delete('/planning-management/{planningItem}', [PlanningController::class, 'destroy'])->name('support.planning.destroy');

        Route::prefix('projecten')
            ->middleware('company_id:1')
            ->name('support.projecten.')
            ->group(function () {
                Route::controller(ProjectController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/{project}', 'show')->name('show');
                });

                // TAKEN
                Route::patch('/{project}/tasks/{task}/name', [ProjectTakenController::class, 'updateName'])->name('taken.name')->scopeBindings();
                Route::patch('/{project}/tasks/{task}/status', [ProjectTakenController::class, 'updateStatus'])->name('taken.update')->scopeBindings();
                Route::patch('/{project}/tasks/bulk/status', [ProjectTakenController::class, 'bulkUpdateStatus'])->name('taken.bulk_status')->scopeBindings();
                Route::patch('/{project}/tasks/{task}/assignee', [ProjectTakenController::class, 'updateAssignee'])->name('taken.assignee')->scopeBindings();
                Route::get('/{project}/tasks/location-suggest', [ProjectTakenController::class, 'locationSuggest'])->name('taken.location_suggest')->scopeBindings();
                Route::patch('/{project}/tasks/{task}/location', [ProjectTakenController::class, 'updateLocation'])->name('taken.location')->scopeBindings();
                Route::patch('/{project}/tasks/{task}/due-date', [ProjectTakenController::class, 'updateDueDate'])->name('taken.due_date')->scopeBindings();
                Route::post('/{project}/tasks', [ProjectTakenController::class, 'store'])->name('taken.store')->scopeBindings();
                Route::delete('/{project}/tasks/bulk/delete', [ProjectTakenController::class, 'bulkDestroy'])->name('taken.bulk_destroy')->scopeBindings();
                Route::delete('/{project}/tasks/{task}', [ProjectTakenController::class, 'destroy'])->name('taken.destroy')->scopeBindings();

                // DETAIL TAKEN
                Route::get('/{project}/tasks/{task}', [ProjectTakenController::class, 'show'])->name('taken.show')->whereNumber('task')->scopeBindings();
                Route::patch('/{project}/tasks/{task}/description', [ProjectTakenController::class, 'updateDescription'])->name('taken.description')->scopeBindings();
                Route::get('/{project}/tasks/{task}/chat', [ProjectTaskChatController::class, 'messages'])->name('taken.chat.messages')->scopeBindings();
                Route::post('/{project}/tasks/{task}/chat', [ProjectTaskChatController::class, 'store'])->name('taken.chat.store')->scopeBindings();
                Route::get('/{project}/tasks/{task}/chat/attachments/{attachment}', [ProjectTaskChatController::class, 'download'])->name('taken.chat.attachments.download')->scopeBindings();
                Route::prefix('{project}/tasks/{task}/subtasks')
                    ->scopeBindings()
                    ->name('taken.subtasks.')
                    ->group(function () {
                        Route::post('/', [ProjectTaskSubtaskController::class, 'store'])->name('store');
                        Route::patch('/{subtask}/status',   [ProjectTaskSubtaskController::class, 'updateStatus'])->name('status');
                        Route::patch('/{subtask}/assignee', [ProjectTaskSubtaskController::class, 'updateAssignee'])->name('assignee');
                        Route::patch('/{subtask}/due-date', [ProjectTaskSubtaskController::class, 'updateDueDate'])->name('due_date');
                        Route::delete('/bulk/delete', [ProjectTaskSubtaskController::class, 'bulkDestroy'])->name('bulk_destroy');
                        Route::delete('/{subtask}',   [ProjectTaskSubtaskController::class, 'destroy'])->name('destroy');
                    });

                // FINANCIEEL
                Route::post('/{project}/finance-items', [ProjectFinancienController::class, 'store'])->name('finance.store')->scopeBindings();
                Route::patch('/{project}/finance-items/{financeItem}', [ProjectFinancienController::class, 'update'])->name('finance.update')->scopeBindings();
                Route::delete('/{project}/finance-items/{financeItem}', [ProjectFinancienController::class, 'destroy'])->name('finance.destroy')->scopeBindings();
                Route::delete('/{project}/finance-items/bulk/delete', [ProjectFinancienController::class, 'bulkDestroy'])->name('finance.bulk_destroy')->scopeBindings();
                Route::post('/{project}/offertes', [ProjectQuoteController::class, 'store'])->name('finance.offertes.store');
                Route::get('/{project}/finance/offertes/{quote}/pdf', [ProjectQuoteController::class, 'pdf'])->name('finance.offertes.pdf')->scopeBindings();
            });

        Route::prefix('taken')
            ->middleware('company_id:1')
            ->name('support.taken.')
            ->controller(TakenController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
            });

        Route::prefix('onboarding')
            ->name('support.onboarding.')
            ->controller(OnboardingController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/nieuw', 'create')->name('create');

                Route::get('/stap-1', 'step1')->name('step1');
                Route::post('/stap-1', 'storeStep1')->name('step1.store');
                Route::get('/stap-2', 'step2')->name('step2');
                Route::post('/stap-2', 'storeStep2')->name('step2.store');
                Route::get('/stap-3', 'step3')->name('step3');
                Route::post('/stap-3', 'storeStep3')->name('step3.store');
                Route::get('/stap-4', 'step4')->name('step4');
                Route::post('/stap-4', 'storeStep4')->name('step4.store');
                Route::get('/stap-5', 'step5')->name('step5');
                Route::post('/stap-5', 'storeStep5')->name('step5.store');
                Route::get('/stap-6', 'step6')->name('step6');
                Route::post('/submit', 'submit')->name('submit');

                Route::post('/reset', 'reset')->name('reset');

                Route::get('/{onboardingRequest}', 'show')->name('show');
            });

        Route::prefix('financien')
            ->middleware('company_id:1')
            ->name('support.financien.')
            ->controller(FinancienController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
            });

        Route::prefix('gebruikers')
            ->name('support.gebruikers.')
            ->controller(GebruikersController::class)
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/lijst/{rol}', 'lijst')->name('lijst');
                Route::get('/{user}', 'show')->whereNumber('user')->name('show');
                Route::patch('/{user}', 'update')->whereNumber('user')->name('update');
                Route::delete('/{user}', 'destroy')->whereNumber('user')->name('destroy');
                Route::post('/', 'store')->name('store');
            });

        Route::post('/logout', [AuthController::class, 'logout'])->name('support.logout');
    });
});
