<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreviewViewed;
use App\Models\ProjectPreviewFeedback;
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

        if (is_null($project->preview_first_viewed_at)) {
            $project->preview_first_viewed_at = $now;
            $project->preview_expires_at      = (clone $now)->addDays(3);
            $project->save();
        }

        $expiresAt = $project->preview_expires_at ?? (clone $now)->addDays(3);
        $remainingSeconds = max(0, $now->lte($expiresAt) ? $now->diffInSeconds($expiresAt) : 0);

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

        try {
            Mail::to('boyd@eazyonline.nl')->send(new PreviewViewed($project, $viewLog));
        } catch (\Throwable $e) {
            report($e);
        }

        return view('other.preview-showcase', [
            'project'          => $project,
            'previewUrl'       => $project->preview_url,
            'expiresAt'        => $expiresAt,
            'remainingSeconds' => $remainingSeconds,
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
}
