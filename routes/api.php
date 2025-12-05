<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InboundAanvraagEmailController;
use App\Http\Controllers\M365InboundInfoController;

// ✅ Legacy provider inbound (Mailgun/SendGrid/Postmark)
// (niet jouw primaire route voor Outlook)
Route::post('/webhooks/inbound/aanvragen/info', [InboundAanvraagEmailController::class, 'info'])
    ->middleware('inbound.mail.secret');

// ✅ Microsoft 365 / Outlook inbound via Graph
Route::post('/webhooks/m365/info-messages', [M365InboundInfoController::class, 'handle'])
    ->middleware('inbound.mail.secret');
