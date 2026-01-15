<?php

namespace App\Actions;

use App\Models\ProjectPlanningItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AssignPhotographerToPlanningItem
{
    public function execute(ProjectPlanningItem $item): ?User
    {
        if (!$item->start_at || !$item->end_at || blank($item->location)) {
            return null;
        }

        // Zorg dat dit item coords heeft
        if (!$this->ensureGeocoded($item)) {
            return null;
        }

        $photographers = User::query()
            ->where('rol', 'fotograaf')
            ->get();

        if ($photographers->isEmpty()) {
            return null;
        }

        $bestUser = null;
        $bestScore = null;

        foreach ($photographers as $user) {
            // 1) Geen overlap
            $overlap = ProjectPlanningItem::query()
                ->where('assignee_user_id', $user->id)
                ->whereNotNull('start_at')
                ->whereNotNull('end_at')
                ->where('start_at', '<', $item->end_at)
                ->where('end_at', '>', $item->start_at)
                ->exists();

            if ($overlap) {
                continue;
            }

            // 2) Vorige klus (laatste die eindigt voor start)
            $prev = ProjectPlanningItem::query()
                ->where('assignee_user_id', $user->id)
                ->whereNotNull('start_at')
                ->whereNotNull('end_at')
                ->where('end_at', '<=', $item->start_at)
                ->orderBy('end_at', 'desc')
                ->first();

            // 3) Volgende klus (eerste die start na end)
            $next = ProjectPlanningItem::query()
                ->where('assignee_user_id', $user->id)
                ->whereNotNull('start_at')
                ->whereNotNull('end_at')
                ->where('start_at', '>=', $item->end_at)
                ->orderBy('start_at', 'asc')
                ->first();

            $score = 0;

            // Check prev -> item
            if ($prev) {
                if (!$this->ensureGeocoded($prev)) {
                    continue;
                }

                $slackSeconds = max(0, $item->start_at->diffInSeconds($prev->end_at, false) * -1);
                // diffInSeconds met false geeft negatief/positief afhankelijk van volgorde
                // we willen: item.start - prev.end
                $slackSeconds = $item->start_at->timestamp - $prev->end_at->timestamp;
                if ($slackSeconds < 0) {
                    continue;
                }

                $travelSeconds = $this->routeDurationSeconds(
                    (float) $prev->location_lat,
                    (float) $prev->location_lng,
                    (float) $item->location_lat,
                    (float) $item->location_lng
                );

                if ($travelSeconds === null || $travelSeconds > $slackSeconds) {
                    continue;
                }

                $score += $travelSeconds;
            }

            // Check item -> next
            if ($next) {
                if (!$this->ensureGeocoded($next)) {
                    continue;
                }

                $slackSeconds = $next->start_at->timestamp - $item->end_at->timestamp;
                if ($slackSeconds < 0) {
                    continue;
                }

                $travelSeconds = $this->routeDurationSeconds(
                    (float) $item->location_lat,
                    (float) $item->location_lng,
                    (float) $next->location_lat,
                    (float) $next->location_lng
                );

                if ($travelSeconds === null || $travelSeconds > $slackSeconds) {
                    continue;
                }

                $score += $travelSeconds;
            }

            // Score: laagste totale extra reistijd wint
            if ($bestScore === null || $score < $bestScore) {
                $bestScore = $score;
                $bestUser = $user;
            }
        }

        if (!$bestUser) {
            return null;
        }

        // Update planning item
        $item->assignee_user_id = $bestUser->id;
        $item->save();

        // Zorg dat fotograaf als member bij project zit
        if ($item->project) {
            $item->project->members()->syncWithoutDetaching([
                $bestUser->id => ['role' => 'photographer'],
            ]);
        }

        return $bestUser;
    }

    private function ensureGeocoded(ProjectPlanningItem $item): bool
    {
        if ($item->location_lat && $item->location_lng) {
            return true;
        }

        $text = trim((string) $item->location);
        if ($text === '') return false;

        $cacheKey = 'ors:geocode:' . md5(Str::lower($text));

        $coords = Cache::remember($cacheKey, now()->addDays(30), function () use ($text) {
            $base = rtrim((string) config('services.ors.base_url'), '/');
            $key  = (string) config('services.ors.key');

            if ($key === '') return null;

            $res = Http::timeout(12)
                ->acceptJson()
                ->get($base . '/geocode/search', [
                    'api_key' => $key,
                    'text' => $text,
                    // optioneel: focus op NL resultaten
                    'boundary.country' => 'NL',
                    'size' => 1,
                ]);

            if (!$res->ok()) {
                return null;
            }

            $json = $res->json();

            $first = $json['features'][0] ?? null;
            $coords = $first['geometry']['coordinates'] ?? null; // [lng, lat]

            if (!is_array($coords) || count($coords) < 2) {
                return null;
            }

            return [
                'lng' => (float) $coords[0],
                'lat' => (float) $coords[1],
            ];
        });

        if (!$coords) {
            return false;
        }

        $item->location_lat = $coords['lat'];
        $item->location_lng = $coords['lng'];
        $item->location_geocoded_at = now();
        $item->save();

        return true;
    }

    private function routeDurationSeconds(float $fromLat, float $fromLng, float $toLat, float $toLng): ?int
    {
        // Cache per route om ORS calls laag te houden
        $cacheKey = 'ors:route:'
            . md5($fromLat . ',' . $fromLng . '|' . $toLat . ',' . $toLng . '|'
            . (string) config('services.ors.profile', 'driving-car'));

        return Cache::remember($cacheKey, now()->addDays(14), function () use ($fromLat, $fromLng, $toLat, $toLng) {
            $base = rtrim((string) config('services.ors.base_url'), '/');
            $key  = (string) config('services.ors.key');
            $profile = (string) config('services.ors.profile', 'driving-car');

            if ($key === '') return null;

            $res = Http::timeout(15)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => $key,
                    'Content-Type' => 'application/json; charset=utf-8',
                ])
                ->post($base . '/v2/directions/' . $profile, [
                    'coordinates' => [
                        [$fromLng, $fromLat],
                        [$toLng, $toLat],
                    ],
                ]);

            if (!$res->ok()) {
                return null;
            }

            $json = $res->json();
            $duration = $json['routes'][0]['summary']['duration'] ?? null;

            if (!is_numeric($duration)) {
                return null;
            }

            return (int) round((float) $duration);
        });
    }
}
