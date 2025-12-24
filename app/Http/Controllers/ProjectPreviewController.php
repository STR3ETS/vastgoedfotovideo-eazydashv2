<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreviewViewed;
use App\Models\ProjectPreviewFeedback;
use App\Mail\PreviewApprovedCustomerMail;
use App\Mail\PreviewApprovedOwnerMail;
use App\Mail\PreviewViewedMultipleTimesCustomerMail;
use Carbon\Carbon;

class ProjectPreviewController extends Controller
{
    public function show(string $token, Request $request)
    {
        $project = Project::where('preview_token', $token)->firstOrFail();

        if (!$project->preview_url) {
            abort(404);
        }

        $now = Carbon::now();
        $isApproved = !is_null($project->preview_approved_at);

        // ✅ Alleen timer starten op eerste view ALS niet approved
        if (!$isApproved && is_null($project->preview_first_viewed_at)) {
            $project->preview_first_viewed_at = $now;
            $project->preview_expires_at      = (clone $now)->addDays(3);
            $project->save();
        }

        // ✅ Als approved: timer stopt direct
        if ($isApproved) {
            $expiresAt = null;
            $remainingSeconds = 0;
        } else {
            $expiresAt = $project->preview_expires_at ?? (clone $now)->addDays(3);
            $remainingSeconds = max(0, $now->lte($expiresAt) ? $now->diffInSeconds($expiresAt) : 0);
        }

        // ✅ Log view (mag je laten zoals je wilt)
        $ip = $request->ip();
        $ua = (string) $request->userAgent();
        $geo = $this->lookupIp($ip);

        $viewLog = $project->previewViews()->create([
            'ip'           => $ip,
            'city'         => $geo['city'] ?? null,
            'region'       => $geo['region'] ?? null,
            'country'      => $geo['country'] ?? null,
            'country_code' => $geo['country_code'] ?? null,
            'user_agent'   => mb_strimwidth($ua, 0, 512, ''),
        ]);

        // ✅ Na 5 views: stuur 1x reminder naar klant (alleen als niet approved)
        if (!$isApproved && !empty($project->contact_email)) {
            $viewsCount = $project->previewViews()->count(); // inclusief deze nieuwe view

            if ($viewsCount === 5) {
                try {
                    Mail::to($project->contact_email)
                        ->send(new PreviewViewedMultipleTimesCustomerMail($project));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        if (!$isApproved) {
            try {
                Mail::to('sales@eazyonline.nl')->send(new PreviewViewed($project, $viewLog));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('other.preview-showcase', [
            'project'          => $project,
            'previewUrl'       => $project->preview_url,
            'expiresAt'        => $expiresAt,
            'remainingSeconds' => $remainingSeconds,
            'isApproved'       => $isApproved, // ✅ belangrijk voor view (lock/timer stop)
        ]);
    }

    public function storeFeedback(string $token, Request $request)
    {
        $project = Project::where('preview_token', $token)->firstOrFail();

        if (!$project->preview_url) {
            abort(404);
        }

        $validated = $request->validate([
            'feedback' => ['required', 'string', 'max:2000'],
        ]);

        $project->previewFeedbacks()->create([
            'feedback' => $validated['feedback'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'status'         => 'ok',
                'message'        => 'Feedback opgeslagen',
                'project_status' => $project->status,
            ]);
        }

        return back()->with('status', 'feedback_saved');
    }

    protected function lookupIp(?string $ip): array
    {
        if (!$ip || $ip === '127.0.0.1' || $ip === '::1') {
            return [];
        }

        try {
            $resp = Http::timeout(2)->retry(1, 150)->get("https://ipapi.co/{$ip}/json/");

            if ($resp->ok()) {
                $j = $resp->json() ?: [];
                return [
                    'city'         => $j['city']         ?? null,
                    'region'       => $j['region']       ?? null,
                    'country'      => $j['country_name'] ?? null,
                    'country_code' => $j['country']      ?? null,
                ];
            }
        } catch (\Throwable $e) {
        }

        return [];
    }

    public function approve(string $token, Request $request)
    {
        $project = Project::where('preview_token', $token)->firstOrFail();

        if (!$project->preview_url) {
            abort(404);
        }

        // al goedgekeurd = idempotent
        if ($project->preview_approved_at) {
            return response()->json([
                'status' => 'ok',
                'alreadyApproved' => true,
            ]);
        }

        $project->preview_approved_at = Carbon::now();
        $project->preview_approved_ip = $request->ip();

        // ✅ (aanrader) ga door naar offerte fase
        // Als jij een andere status wil, verander dit:
        $project->status = 'preview_approved';

        $project->save();

        // ✅ mail naar klant
        try {
            // klant mail
            if (!empty($project->contact_email)) {
                Mail::to($project->contact_email)->send(new PreviewApprovedCustomerMail($project));
            }

            // owner mails (hardcoded)
            Mail::to(['sales@eazyonline.nl'])
                ->send(new PreviewApprovedOwnerMail($project));

        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'status' => 'ok',
            'approvedAt' => optional($project->preview_approved_at)->timezone('Europe/Amsterdam')->format('d-m-Y H:i'),
            'project_status' => $project->status,
        ]);
    }
}
