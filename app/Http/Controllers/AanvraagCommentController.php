<?php

namespace App\Http\Controllers;

use App\Models\AanvraagWebsite;
use Illuminate\Http\Request;
use App\Mail\AanvraagMentionedMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Notifications\AanvraagMentionedNotification;

class AanvraagCommentController extends Controller
{
    public function index(Request $request, AanvraagWebsite $aanvraag)
    {
        $afterId = (int) $request->query('after_id', 0);

        $q = $aanvraag->comments()->with('user');

        // eerste load: nieuwste bovenaan (zoals je belmomenten ook doen)
        if ($afterId <= 0) {
            $q->latest()->take(25);
        } else {
            // polling: alleen nieuwe, in oplopende volgorde zodat unshift netjes blijft
            $q->where('id', '>', $afterId)->orderBy('id')->take(50);
        }

        $comments = $q->get()->map(function ($c) {
            return [
                'id'         => $c->id,
                'parent_id'  => $c->parent_id,
                'created_at' => optional($c->created_at)->format('d-m-Y H:i'),
                'user_name'  => optional($c->user)->name ?? 'Onbekend',
                'body'       => $c->body,
            ];
        });

        return response()->json([
            'success'  => true,
            'comments' => $comments,
        ]);
    }

    public function store(Request $request, AanvraagWebsite $aanvraag)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('aanvraag_comments', 'id')->where('aanvraag_website_id', $aanvraag->id),
            ],
        ]);

        // ✅ body normaliseren (ook nbsp)
        $bodyForSave = str_replace("\xC2\xA0", ' ', (string) $data['body']);
        $bodyForSave = trim($bodyForSave);

        // ✅ fix: als iemand typt "@NaamBericht" -> maak "@Naam Bericht"
        if (Str::contains($bodyForSave, '@')) {
            $internalUsers = User::query()
                ->whereNull('company_id')
                ->whereNotNull('email')
                ->get(['id', 'name']);

            foreach ($internalUsers as $u) {
                $name = (string) $u->name;
                if ($name === '') continue;

                // alleen spatie toevoegen als er DIRECT letters/cijfers/_ achter de naam komen
                $pattern = '/@' . preg_quote($name, '/') . '(?=[\p{L}\p{N}_])/iu';
                $bodyForSave = preg_replace($pattern, '@' . $name . ' ', $bodyForSave);
            }
        }

        $comment = $aanvraag->comments()->create([
            'user_id'    => auth()->id(),
            'parent_id'  => $data['parent_id'] ?? null,
            'body'       => $bodyForSave,
        ]);

        $comment->loadMissing('user');

        // ✅ Mail naar getaggde personen (match op "@Volledige Naam")
        try {
            $actor = auth()->user();

            $bodyRaw = (string) $comment->body;

            // normaliseer whitespace (ook non-breaking spaces) → 1 spatie
            $body = preg_replace('/[\x{00A0}\s]+/u', ' ', trim($bodyRaw));

            if (Str::contains($body, '@')) {
                $internalUsers = User::query()
                    ->whereNull('company_id')     // ✅ alleen interne users
                    ->whereNotNull('email')
                    ->get(['id', 'name', 'email']);

                $boyd = User::query()->where('email', 'boyd@eazyonline.nl')->first(['id','name','email','company_id']);

                Log::info('Mention debug - internal users', [
                    'internal_count' => $internalUsers->count(),
                    'sample' => $internalUsers->take(8)->map(fn($u) => [
                        'id' => $u->id,
                        'name' => $u->name,
                        'email' => $u->email,
                        'company_id' => $u->company_id,
                        'name_hex' => bin2hex((string) $u->name),
                    ])->values()->all(),
                ]);

                Log::info('Mention debug - boyd row', [
                    'exists' => (bool) $boyd,
                    'id' => $boyd?->id,
                    'name' => $boyd?->name,
                    'email' => $boyd?->email,
                    'company_id' => $boyd?->company_id,
                    'name_hex' => $boyd ? bin2hex((string) $boyd->name) : null,
                    'body_hex' => bin2hex((string) $comment->body),
                ]);


                $norm = function (string $s): string {
                    // NBSP/rare chars -> spatie
                    $s = str_replace("\xC2\xA0", ' ', $s);
                    $s = preg_replace('/\s+/u', ' ', trim($s));
                    return mb_strtolower($s, 'UTF-8');
                };

                $bodyN = $norm($body);

                $mentionedUsers = $internalUsers->filter(function ($u) use ($bodyN, $norm) {
                    $needle = '@' . $norm((string) $u->name);

                    // komt "@naam" voor?
                    $pos = mb_stripos($bodyN, $needle, 0, 'UTF-8');
                    if ($pos === false) return false;

                    // boundary check: na de naam mag geen letter/cijfer/_ komen
                    $afterPos = $pos + mb_strlen($needle, 'UTF-8');
                    $nextChar = mb_substr($bodyN, $afterPos, 1, 'UTF-8');

                    if ($nextChar === '') return true; // einde string = ok

                    return !preg_match('/^[\p{L}\p{N}_]/u', $nextChar);
                })
                ->unique('id');

                Log::info('Mention mail check', [
                    'aanvraag_id' => $aanvraag->id,
                    'body' => $body,
                    'matched' => $mentionedUsers->pluck('email')->values()->all(),
                ]);

                foreach ($mentionedUsers as $u) {
                    // mail (heb je al)
                    Mail::to($u->email)->send(
                        new AanvraagMentionedMail($aanvraag, $comment, $actor)
                    );
                    // ✅ database notification (bel)
                    $u->notify(new AanvraagMentionedNotification(
                        aanvraagId: (int) $aanvraag->id,
                        aanvraagCompany: (string) ($aanvraag->company ?? 'Website aanvraag'),
                        commentId: (int) $comment->id,
                        actorId: (int) ($actor?->id ?? 0),
                        actorName: (string) ($actor?->name ?? 'Onbekend'),
                        body: (string) ($comment->body ?? '')
                    ));
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'success' => true,
            'comment' => [
                'id'         => $comment->id,
                'parent_id'  => $comment->parent_id,
                'created_at' => optional($comment->created_at)->format('d-m-Y H:i'),
                'user_name'  => optional($comment->user)->name ?? 'Onbekend',
                'body'       => $comment->body,
            ],
        ]);
    }
}
