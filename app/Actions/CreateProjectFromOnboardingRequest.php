<?php

namespace App\Actions;

use App\Models\OnboardingRequest;
use App\Models\Project;
use App\Models\ProjectFinanceItem;
use App\Models\ProjectPlanningItem;
use App\Models\ProjectTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateProjectFromOnboardingRequest
{
    public function execute(OnboardingRequest $req, int $createdByUserId): Project
    {
        return DB::transaction(function () use ($req, $createdByUserId) {

            // 1) Project (idempotent: nooit dubbel)
            $project = Project::firstOrCreate(
                ['onboarding_request_id' => $req->id],
                [
                    'client_user_id'     => $req->user_id,
                    'created_by_user_id' => $createdByUserId,
                    'title'              => $this->defaultTitle($req),
                    'status'             => 'active',
                    'category'           => 'onboarding',
                    'template'           => null,
                ]
            );

            // 2) Creator als lid
            $project->members()->syncWithoutDetaching([
                $createdByUserId => ['role' => 'admin'],
            ]);

            // 3) Default tasks
            $this->seedTasks($project, $req);

            // 4) Finance (package + extras, met correcte prijzen + total_cents)
            $this->seedFinance($project, $req);

            // 5) Planning (shoot date + slot)
            $this->seedPlanning($project, $req);

            return $project;
        });
    }

    private function defaultTitle(OnboardingRequest $req): string
    {
        $name = trim(($req->contact_first_name ?? '') . ' ' . ($req->contact_last_name ?? ''));
        $label = $this->packageLabel($req->package);

        $name = $name !== '' ? $name : 'Project';
        return $name . ' | ' . $label;
    }

    private function packageCatalog(): array
    {
        // EXACT dezelfde prijzen als onboarding show (maar dan in cents)
        return [
            'pro' => [
                'title' => 'Pro pakket',
                'price_cents' => 50900,
            ],
            'plus' => [
                'title' => 'Plus pakket',
                'price_cents' => 38500,
            ],
            'essentials' => [
                'title' => 'Essentials pakket',
                'price_cents' => 32500,
            ],
            'media' => [
                'title' => 'Media pakket',
                'price_cents' => 26000,
            ],
            'buiten' => [
                'title' => 'Buiten pakket',
                'price_cents' => 7500,
            ],
            'funda_klaar' => [
                'title' => 'Funda klaar pakket',
                'price_cents' => 5000,
            ],
        ];
    }

    private function extrasCatalog(): array
    {
        // EXACT dezelfde extras + bedragen als onboarding show (in cents)
        return [
            'privacy_check' => ['title' => 'Privacy check', 'price_cents' => 1000],
            'detailfotos' => ['title' => 'Detailfoto’s', 'price_cents' => 2500],
            'hoogtefotografie_8m' => ['title' => 'Hoogtefotografie 8 meter', 'price_cents' => 2500],
            'plattegrond_in_video' => ['title' => 'Plattegronden verwerkt in video', 'price_cents' => 1500],
            'tekst_video' => ['title' => 'Tekst toevoegen video', 'price_cents' => 1500],
            'floorplanner_3d' => ['title' => 'Floorplanner plattegronden 3D', 'price_cents' => 1000],
            'meubels_toevoegen' => ['title' => 'Plattegronden toevoegen: meubels', 'price_cents' => 1000],
            'tuin_toevoegen' => ['title' => 'Plattegronden toevoegen: tuin', 'price_cents' => 1000],
            'artist_impression' => ['title' => 'Artist impression', 'price_cents' => 9500],
            'woningtekst' => ['title' => 'Woningtekst', 'price_cents' => 8500],
            'video_1min' => ['title' => '1 minuut video', 'price_cents' => 1500],
            'foto_slideshow' => ['title' => 'Foto slideshow', 'price_cents' => 1500],
            'levering_24u' => ['title' => 'Levering binnen 24 uur', 'price_cents' => 3500],
            'huisstijl_plattegrond' => ['title' => 'Plattegronden in eigen huisstijl', 'price_cents' => 1000],
            'm2_per_ruimte' => ['title' => 'Plattegronden: m2 per ruimte aangegeven', 'price_cents' => 500],
            'style_shoot' => ['title' => 'Style shoot', 'price_cents' => 4000],
        ];
    }

    private function packageLabel(?string $key): string
    {
        $catalog = $this->packageCatalog();
        $key = (string) $key;

        return $catalog[$key]['title'] ?? ucfirst($key);
    }

    private function selectedExtras(OnboardingRequest $req): array
    {
        $extras = $req->extras ?? [];

        if (is_string($extras)) {
            $decoded = json_decode($extras, true);
            $extras = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($extras)) $extras = [];

        return array_values(array_unique(array_filter(
            $extras,
            fn($v) => is_string($v) && $v !== ''
        )));
    }

    private function baseTasksForPackage(string $packageKey): array
    {
        // Vul dit eventueel later aan met jullie exacte pakket-inhoud.
        // BELANGRIJK: de naming doen we straks als "{Pakket} | {Taaknaam}".
        return match ($packageKey) {
            'plus' => [
                'Alles uit Essentials',
                "Dronefotografie: 5 foto's",
                'Woningvideo met plattegrond',
                'gemeubileerde 3D plattegronden + tuin',
                'Privacy check',
                'Voorfoto',
            ],
            'pro' => [
                'Alles uit Plus',
                'Interactieve tools (Pro)',
            ],
            'essentials' => [
                'Fotografie (interieur + exterieur)',
                'Woningvideo',
                'Plattegronden',
            ],
            'media' => [
                'Foto- en videografie',
                '360 graden foto’s',
            ],
            'buiten' => [
                'Buiten fotografie',
                'Buiten video',
                '360 graden fotografie buiten',
            ],
            'funda_klaar' => [
                'Funda oplevering (alle essentials)',
            ],
            default => [
                'Pakket uitvoeren',
            ],
        };
    }

    private function seedTasks(Project $project, OnboardingRequest $req): void
    {
        $packageKey = (string) ($req->package ?? '');
        $packageLabel = $this->packageLabel($packageKey);

        $extraToTask = [
            'privacy_check' => 'Privacy check',
            'detailfotos' => "Detailfoto's",
            'hoogtefotografie_8m' => 'Hoogtefotografie (8m)',
            'plattegrond_in_video' => 'Plattegrond in video',
            'tekst_video' => 'Tekst/ondertiteling video',
            'floorplanner_3d' => '3D plattegrond (floorplanner)',
            'meubels_toevoegen' => 'Meubels toevoegen (3D)',
            'tuin_toevoegen' => 'Tuin toevoegen (3D)',
            'artist_impression' => 'Artist impression',
            'woningtekst' => 'Woningtekst',
            'video_1min' => 'Video (1 min)',
            'foto_slideshow' => 'Foto slideshow',
            'levering_24u' => 'Levering binnen 24u',
            'huisstijl_plattegrond' => 'Plattegrond in huisstijl',
            'm2_per_ruimte' => 'm² per ruimte',
            'style_shoot' => 'Style shoot',
        ];

        $names = $this->baseTasksForPackage($packageKey);

        foreach ($this->selectedExtras($req) as $extraKey) {
            if (isset($extraToTask[$extraKey])) {
                $names[] = $extraToTask[$extraKey];
            }
        }

        $names = array_values(array_unique(array_filter($names)));

        foreach ($names as $i => $baseName) {
            // ✅ Dit is jouw gewenste format:
            // "Essentials pakket | Taaknaam"
            $canonicalName = $packageLabel . ' | ' . $baseName;

            // Als er al legacy taken bestaan (zonder prefix), hernoemen we die i.p.v. dupliceren
            $existing = ProjectTask::query()
                ->where('project_id', $project->id)
                ->where(function ($q) use ($canonicalName, $baseName) {
                    $q->where('name', $canonicalName)
                      ->orWhere('name', $baseName)
                      ->orWhere('name', 'like', '%| ' . $baseName);
                })
                ->first();

            if ($existing) {
                $existing->name = $canonicalName;
                $existing->status = $existing->status ?: 'pending';
                $existing->sort_order = $existing->sort_order ?: ($i + 1);
                $existing->save();
                continue;
            }

            ProjectTask::create([
                'project_id' => $project->id,
                'name' => $canonicalName,
                'status' => 'pending',
                'sort_order' => $i + 1,
                'location' => null,
                'due_date' => null,
                'assigned_user_id' => null,
            ]);
        }
    }

    private function seedFinance(Project $project, OnboardingRequest $req): void
    {
        $pkgCatalog = $this->packageCatalog();
        $exCatalog  = $this->extrasCatalog();

        $wanted = [];

        // Package line
        $pkgKey = (string) ($req->package ?? '');
        if (isset($pkgCatalog[$pkgKey])) {
            $wanted[] = [
                'description' => $pkgCatalog[$pkgKey]['title'],
                'qty' => 1,
                'unit_price_cents' => (int) $pkgCatalog[$pkgKey]['price_cents'],
                'unit' => 'pcs',
            ];
        } else {
            $wanted[] = [
                'description' => $this->packageLabel($req->package),
                'qty' => 1,
                'unit_price_cents' => 0,
                'unit' => 'pcs',
            ];
        }

        // Extras lines
        foreach ($this->selectedExtras($req) as $key) {
            if (!isset($exCatalog[$key])) continue;

            $wanted[] = [
                'description' => $exCatalog[$key]['title'],
                'qty' => 1,
                'unit_price_cents' => (int) $exCatalog[$key]['price_cents'],
                'unit' => 'pcs',
            ];
        }

        // Cleanup: remove stale onboarding-related finance lines that are not selected anymore
        $knownDescriptions = array_merge(
            array_map(fn($p) => $p['title'], $pkgCatalog),
            array_map(fn($e) => $e['title'], $exCatalog)
        );
        $wantedDescriptions = array_map(fn($l) => $l['description'], $wanted);

        ProjectFinanceItem::query()
            ->where('project_id', $project->id)
            ->whereIn('description', $knownDescriptions)
            ->whereNotIn('description', $wantedDescriptions)
            ->delete();

        // Upsert wanted lines (idempotent + zet total_cents)
        foreach ($wanted as $line) {
            $qty   = (int) ($line['qty'] ?? 1);
            $unit  = (int) ($line['unit_price_cents'] ?? 0);
            $total = $qty * $unit;

            ProjectFinanceItem::updateOrCreate(
                ['project_id' => $project->id, 'description' => $line['description']],
                [
                    'quantity' => $qty,
                    'unit' => $line['unit'] ?? 'pcs',
                    'unit_price_cents' => $unit,
                    'total_cents' => $total,
                ]
            );
        }
    }

    private function seedPlanning(Project $project, OnboardingRequest $req): void
    {
        if (!$req->shoot_date || !$req->shoot_slot) return;

        // shoot_slot voorbeeld: "09:00 - 11:00"
        [$start, $end] = array_map('trim', explode('-', (string) $req->shoot_slot));

        $date = Carbon::parse($req->shoot_date)->format('Y-m-d');
        $startAt = Carbon::parse($date . ' ' . $start);
        $endAt   = Carbon::parse($date . ' ' . $end);

        $location = trim(($req->address ?? '') . ', ' . ($req->postcode ?? '') . ' ' . ($req->city ?? ''));

        ProjectPlanningItem::firstOrCreate(
            ['project_id' => $project->id, 'notes' => 'Uitvoerdatum - Onboarding foto'],
            [
                'start_at' => $startAt,
                'end_at' => $endAt,
                'location' => $location,
                'assignee_user_id' => null,
            ]
        );
    }
}
