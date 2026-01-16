<?php

namespace App\Actions;

use App\Models\ProjectPlanningItem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssignPhotographerToPlanningItem
{
    public function execute(ProjectPlanningItem $item): ?User
    {
        if (!$item->start_at || !$item->end_at || blank($item->location)) {
            Log::warning('Auto-assign: planning item mist data', [
                'planning_item_id' => $item->id,
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'location' => $item->location,
            ]);
            return null;
        }

        // Zorg dat dit item coords heeft
        if (!$this->ensureGeocoded($item)) {
            Log::warning('Auto-assign: geocoding mislukt voor target item', [
                'planning_item_id' => $item->id,
                'location' => $item->location,
            ]);
            return null;
        }

        $photographers = User::query()
            ->where('rol', 'fotograaf')
            ->get();

        if ($photographers->isEmpty()) {
            Log::warning('Auto-assign: geen fotografen gevonden', []);
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
                    // als we prev niet kunnen geocoden, kunnen we travel niet bepalen
                    continue;
                }

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
            Log::info('Auto-assign: geen geschikte fotograaf gevonden', [
                'planning_item_id' => $item->id,
                'location' => $item->location,
                'start_at' => (string) $item->start_at,
                'end_at' => (string) $item->end_at,
            ]);
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

        Log::info('Auto-assign: fotograaf toegewezen', [
            'planning_item_id' => $item->id,
            'assignee_user_id' => $bestUser->id,
        ]);

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

        // ✅ cache NOOIT null: alleen cache bij succes
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['lat'], $cached['lng'])) {
            $item->location_lat = (float) $cached['lat'];
            $item->location_lng = (float) $cached['lng'];
            $item->location_geocoded_at = now();
            $item->save();
            return true;
        }

        $base = rtrim((string) config('services.ors.base_url'), '/');
        $key  = (string) config('services.ors.key');

        if ($key === '') {
            Log::error('ORS geocode: services.ors.key leeg');
            return false;
        }

        $res = Http::timeout(15)
            ->acceptJson()
            ->get($base . '/geocode/search', [
                'api_key' => $key,
                'text' => $text,
                'boundary.country' => 'NL',
                'size' => 1,
            ]);

        if (!$res->ok()) {
            Log::error('ORS geocode faalde', [
                'status' => $res->status(),
                'body' => $res->body(),
                'location_text' => $text,
            ]);
            return false;
        }

        $json = $res->json();
        $first = $json['features'][0] ?? null;
        $coords = $first['geometry']['coordinates'] ?? null; // [lng, lat]

        if (!is_array($coords) || count($coords) < 2) {
            Log::error('ORS geocode: geen coordinates in response', [
                'location_text' => $text,
                'json' => $json,
            ]);
            return false;
        }

        $lng = (float) $coords[0];
        $lat = (float) $coords[1];

        // ✅ cache alleen succesvolle coords
        Cache::put($cacheKey, ['lat' => $lat, 'lng' => $lng], now()->addDays(30));

        $item->location_lat = $lat;
        $item->location_lng = $lng;
        $item->location_geocoded_at = now();
        $item->save();

        return true;
    }

    private function routeDurationSeconds(float $fromLat, float $fromLng, float $toLat, float $toLng): ?int
    {
        $profile = (string) config('services.ors.profile', 'driving-car');

        $cacheKey = 'ors:route:' . md5(
            $fromLat . ',' . $fromLng . '|' . $toLat . ',' . $toLng . '|' . $profile
        );

        // ✅ cache NOOIT null: alleen cache bij succes
        $cached = Cache::get($cacheKey);
        if (is_int($cached) && $cached > 0) {
            return $cached;
        }

        $base = rtrim((string) config('services.ors.base_url'), '/');
        $key  = (string) config('services.ors.key');

        if ($key === '') {
            Log::error('ORS route: services.ors.key leeg');
            return null;
        }

        $res = Http::timeout(20)
            ->acceptJson()
            ->withHeaders([
                'Authorization' => $key,
            ])
            ->post($base . '/v2/directions/' . $profile, [
                'coordinates' => [
                    [$fromLng, $fromLat],
                    [$toLng, $toLat],
                ],
            ]);

        if (!$res->ok()) {
            Log::error('ORS route faalde', [
                'status' => $res->status(),
                'body' => $res->body(),
                'from' => [$fromLat, $fromLng],
                'to' => [$toLat, $toLng],
                'profile' => $profile,
            ]);
            return null;
        }

        $json = $res->json();
        $duration = $json['routes'][0]['summary']['duration'] ?? null;

        if (!is_numeric($duration)) {
            Log::error('ORS route: geen duration in response', [
                'json' => $json,
            ]);
            return null;
        }

        $seconds = (int) round((float) $duration);

        // ✅ cache alleen succesvolle duration
        if ($seconds > 0) {
            Cache::put($cacheKey, $seconds, now()->addDays(14));
        }

        return $seconds;
    }
}
