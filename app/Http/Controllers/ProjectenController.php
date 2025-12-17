<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Offerte;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PreviewReadyMail;

class ProjectenController extends Controller
{
    public function index()
    {
        $projects = Project::with([
                'aanvraag.tasks.questions',
                'tasks.questions',
                'callLogs.user',
            ])
            ->latest()
            ->paginate(15);

        $statusMap = [
            'preview' => [
                'value' => 'preview',
                'label' => __('projecten.statuses.preview'),
            ],
            'waiting_customer' => [
                'value' => 'waiting_customer',
                'label' => __('projecten.statuses.waiting_customer'),
            ],
            'preview_approved' => [
                'value' => 'preview_approved',
                'label' => __('projecten.statuses.preview_approved'),
            ],
            'offerte' => [
                'value' => 'offerte',
                'label' => __('projecten.statuses.offerte'),
            ],
        ];

        $statusCounts = Project::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $statusByValue = collect($statusMap)
            ->mapWithKeys(fn ($s) => [$s['value'] => $s['label']])
            ->all();

        return view('hub.projecten.index', [
            'user'          => auth()->user(),
            'projects'      => $projects,
            'statusMap'     => $statusMap,
            'statusCounts'  => $statusCounts,
            'statusByValue' => $statusByValue,
        ]);
    }

    public function updateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:preview,waiting_customer,preview_approved,offerte'],
        ]);

        $oldStatus        = $project->status;
        $project->status  = $data['status'];
        $project->save();

        $offerteTaskData = null;

        if ($oldStatus !== 'offerte' && $project->status === 'offerte') {
            /** @var ProjectTask $task */
            $task = $this->ensureOfferteTask($project);

            /** @var ProjectTaskQuestion|null $question */
            $question = $task->questions()->where('order', 1)->first();

            $offerteTaskData = [
                'title'       => $task->title,
                'description' => $task->description,
                'notes'       => $question?->answer,
                'completed'   => (bool) $task->completed_at,
            ];
        }

        $label = __('projecten.statuses.' . $project->status);

        return response()->json([
            'success'      => true,
            'id'           => $project->id,
            'status'       => $project->status,
            'label'        => $label,
            'offerte_task' => $offerteTaskData,
        ]);
    }

    public function updateTaskStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'type'    => ['required', 'string', 'max:60'],
            'checked' => ['nullable'],
            'status'  => ['nullable', 'string', 'in:open,done'],
        ]);

        $type = (string) $data['type'];

        // ✅ accepteer zowel checked (true/false) als status (open/done)
        if (array_key_exists('checked', $data) && $data['checked'] !== null) {
            $checked = filter_var($data['checked'], FILTER_VALIDATE_BOOLEAN);
        } elseif (!empty($data['status'])) {
            $checked = strtolower((string) $data['status']) === 'done';
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Missing checked/status',
            ], 422);
        }

        // call_customer blijft via offerte flow
        if ($type === 'call_customer') {
            $task = $this->ensureOfferteTask($project);
        } else {
            $task = $project->tasks()->firstOrCreate(
                ['type' => $type],
                [
                    'title' => \Illuminate\Support\Str::headline($type),
                    'order' => 999,
                    'status' => 'open',
                ]
            );
        }

        $task->status = $checked ? 'done' : 'open';
        $task->completed_at = $checked ? now() : null;
        $task->save();

        return response()->json([
            'success'   => true,
            'type'      => $type,
            'status'    => $task->status,
            'completed' => (bool) $task->completed_at,
        ]);
    }

    public function updateAssignee(Request $request, Project $project)
    {
        $data = $request->validate([
            'assignee_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (!is_null($data['assignee_id'])) {
            $isStaff = User::whereKey($data['assignee_id'])
                ->whereNull('company_id')
                ->exists();

            if (!$isStaff) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ongeldige medewerker.',
                ], 422);
            }
        }

        $project->assignee_id = $data['assignee_id'] ?? null;
        $project->save();

        return response()->json([
            'success'     => true,
            'id'          => $project->id,
            'assignee_id' => $project->assignee_id,
        ]);
    }

    public function updatePreview(Request $request, Project $project)
    {
        $data = $request->validate([
            'preview_url' => ['nullable', 'string', 'max:2048'],
        ]);

        // onthoud oude url zodat we niet onnodig spammen
        $oldPreviewUrl = $project->preview_url;

        if (! $project->preview_token) {
            $project->preview_token = Project::generatePreviewToken();
        }

        $project->preview_url = $data['preview_url'] ?: null;

        // ❌ NIET automatisch project status aanpassen
        // if (! empty($project->preview_url)) {
        //     $project->status = 'waiting_customer';
        // }

        $project->save();

        // ✅ Taak "create_preview" syncen met preview_url
        $task = $project->tasks()->firstOrCreate(
            ['type' => 'create_preview'],
            [
                'title'  => 'Preview maken & preview-url opgeven',
                'status' => 'open',
                'order'  => 10,
            ]
        );

        if (!empty($project->preview_url)) {
            $task->status = 'done';
            $task->completed_at = $task->completed_at ?: now();
        } else {
            $task->status = 'open';
            $task->completed_at = null;
        }
        $task->save();

        // ✅ Mail sturen naar klant zodra preview is opgeslagen
        $mailSent = false;

        // Stuur alleen als er een preview_url is en een contact_email
        if (!empty($project->preview_url) && !empty($project->contact_email)) {

            // Stuur alleen wanneer hij net gezet is of gewijzigd is
            $shouldSend = empty($oldPreviewUrl) || $oldPreviewUrl !== $project->preview_url;

            if ($shouldSend) {
                try {
                    $klantUrl = route('preview.show', ['token' => $project->preview_token]);

                    Mail::to($project->contact_email)->send(new PreviewReadyMail(
                        company: $project->company ?: 'Onbekend bedrijf',
                        contactName: $project->contact_name ?: null,
                        klantUrl: $klantUrl,
                        previewUrl: $project->preview_url
                    ));

                    $mailSent = true;
                } catch (\Throwable $e) {
                    report($e); // mail-fail mag je preview-save niet slopen
                }
            }
        }

        $label = __('projecten.statuses.' . $project->status);

        return response()->json([
            'success'      => true,
            'id'           => $project->id,
            'preview_url'  => $project->preview_url,
            'preview_link' => $project->preview_url && $project->preview_token
                ? route('preview.show', ['token' => $project->preview_token])
                : null,
            'status'       => $project->status,
            'label'        => $label,
            'mail_sent'    => $mailSent,
        ]);
    }

    public function updateOfferteNotes(Request $request, Project $project)
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        /** @var ProjectTask $task */
        $task = $this->ensureOfferteTask($project);

        /** @var ProjectTaskQuestion $question */
        $question = $task->questions()->firstOrCreate(
            ['order' => 1],
            [
                'question' => 'Notities offertegesprek',
                'required' => false,
            ]
        );

        $question->answer = $data['notes'] ?? null;
        $question->save();

        return response()->json([
            'success' => true,
            'notes'   => $question->answer,
        ]);
    }

    public function completeOfferteTask(Request $request, Project $project)
    {
        /** @var ProjectTask $task */
        $task = $this->ensureOfferteTask($project);

        if (! $task->completed_at) {
            $task->completed_at = now();
            $task->save();
        }

        $question = $task->questions()->where('order', 1)->first();

        return response()->json([
            'success'      => true,
            'offerte_task' => [
                'title'       => $task->title,
                'description' => $task->description,
                'notes'       => $question?->answer,
                'completed'   => (bool) $task->completed_at,
            ],
        ]);
    }

    public function storeCall(Request $request, Project $project)
    {
        $data = $request->validate([
            'outcome' => ['required', 'string', 'in:geen_antwoord,gesproken'],
            'note'    => ['nullable', 'string'],
        ]);

        $user = $request->user();

        /** @var \App\Models\ProjectCallLog $log */
        $log = $project->callLogs()->create([
            'user_id'   => $user?->id,
            'called_at' => now(),
            'outcome'   => $data['outcome'],
            'note'      => $data['note'] ?? null,
        ]);

        return response()->json([
            'success'    => true,
            'id'         => $log->id,
            'called_at'  => optional($log->called_at)->format('d-m-Y H:i'),
            'outcome'    => $log->outcome,
            'note'       => $log->note,
            'user_name'  => optional($log->user)->name,
        ]);
    }

    public function generateOfferte(Request $request, Project $project)
    {
        $openaiKey = config('services.openai.key');

        if (! $openaiKey) {
            return response()->json([
                'success' => false,
                'message' => 'OpenAI API key ontbreekt. Vul OPENAI_API_KEY in je .env in.',
            ], 500);
        }

        // Bestaande offerte hergebruiken of nieuwe aanmaken
        /** @var Offerte $offerte */
        $offerte = $project->offerte;

        if (! $offerte) {
            $offerte = new Offerte();
            $offerte->project()->associate($project);
            $offerte->public_uuid = Str::random(20);
            $offerte->status      = 'draft';
        }

        // ▶ Basisgegevens uit project
        $company      = $project->company ?: 'Onbekend bedrijf';
        $contactName  = $project->contact_name ?: null;
        $contactEmail = $project->contact_email ?: null;
        $contactPhone = $project->contact_phone ?: null;
        $previewUrl   = $project->preview_url;

        // ▶ Intake (aanvraag)
        $intakePairs = collect();

        if ($project->aanvraag) {
            $project->aanvraag->loadMissing('tasks.questions');

            $intakePairs = $project->aanvraag->tasks
                ->flatMap(function ($task) {
                    return $task->questions->map(function ($q) use ($task) {
                        return [
                            'task'     => $task->title ?? $task->type,
                            'question' => $q->question,
                            'answer'   => $q->answer,
                        ];
                    });
                })
                ->filter(fn ($qa) => filled($qa['answer']));
        }

        $intakeSummary = $intakePairs
            ->values()
            ->map(function ($qa, $index) {
                $idx = $index + 1;
                return "{$idx}. [{$qa['task']}] Vraag: {$qa['question']} | Antwoord: {$qa['answer']}";
            })
            ->join("\n");

        if ($intakeSummary === '') {
            $intakeSummary = 'Geen ingevulde intakevragen beschikbaar.';
        }

        // ▶ Offertegesprek (project)
        $offertePairs = $project->tasks()
            ->with('questions')
            ->get()
            ->flatMap(function ($task) {
                return $task->questions->map(function ($q) use ($task) {
                    return [
                        'task'     => $task->title ?? $task->type,
                        'question' => $q->question,
                        'answer'   => $q->answer,
                    ];
                });
            })
            ->filter(fn ($qa) => filled($qa['answer']));

        $offerteSummary = $offertePairs
            ->values()
            ->map(function ($qa, $index) {
                $idx = $index + 1;
                return "{$idx}. [{$qa['task']}] Vraag: {$qa['question']} | Antwoord: {$qa['answer']}";
            })
            ->join("\n");

        if ($offerteSummary === '') {
            $offerteSummary = 'Geen ingevulde vragen uit het offertegesprek beschikbaar.';
        }

        // ▶ GROTE PROMPT MET ALLE PAKKETTEN + PRIJZEN
$prompt = <<<TXT
Je gaat een commerciële offerte schrijven in het Nederlands voor webdesign / webshop / online groei.

Gebruik ALLE context hieronder om de tekst heel concreet en specifiek te maken. Verzin niets dat in tegenspraak is met de input.

### 1. Basisgegevens
- Bedrijfsnaam: {$company}
- Contactpersoon: {$contactName}
- E-mail: {$contactEmail}
- Telefoon: {$contactPhone}
- Preview-URL (indien al gemaakt): {$previewUrl}

### 2. Intakegesprek (AANVRAAG)
Dit zijn de antwoorden uit de intake (aanvraag_task_questions):

{$intakeSummary}

### 3. Offertegesprek (PROJECT)
Dit zijn de antwoorden uit het offertegesprek (project_task_questions):

{$offerteSummary}

### 4. WEBSITE-PAKKETTEN (vaste bedragen – ALLEEN deze gebruiken)

Voor de website gebruik je ALTIJD één van deze drie pakketten. Je mag GEEN andere maand- of opstartbedragen bedenken.

- Eazy Essential – Website
  - Maandelijks: € 39,95 per maand
  - Opstart: € 750,- eenmalig
  - Geschikt voor: kleinere bedrijven / starters met een compacte site (bijv. one-pager of 3–5 pagina's).

- Eazy Step-up – Website
  - Maandelijks: € 59,95 per maand
  - Opstart: € 1.500,- eenmalig
  - Geschikt voor: MKB met meerdere pagina’s, cases, funnels of lichte webshop.

- Eazy Pro – Website
  - Maandelijks: € 99,95 per maand
  - Opstart: € 2.500,- eenmalig
  - Geschikt voor: grotere bedrijven met uitgebreide webshop, maatwerk en koppelingen.

BELANGRIJK:
- "package_name" MOET exact één van deze drie zijn: "Eazy Essential", "Eazy Step-up" of "Eazy Pro".
- "setup_price_eur" MAG ALLEEN 750, 1500 of 2500 zijn.
- "monthly_price_eur" MAG ALLEEN 39.95, 59.95 of 99.95 zijn.
- De website in de investering moet SUPER duidelijk zijn: één regel met pakketnaam + opstart + maandbedrag.

### 5. EXTRA DIENSTEN & PRIJZEN (volgens eazyonline.nl)

Gebruik onderstaande pakketten en bedragen als je extra diensten toevoegt. Verzin GEEN eigen bedragen zoals 350,- of 450,-. Gebruik ALLEEN de exacte bedragen hieronder.

Je voegt alleen een extra dienst toe als dit expliciet terugkomt in intake of offertegesprek (bijv. ze willen social media beheer, SEO, advertenties, drukwerk, branding, etc.).

#### 5.1 Social media beheer (pagina /socialmedia)
- Social media – Eazy Essential: vanaf € 300,- per maand
- Social media – Eazy Step-up: vanaf € 850,- per maand
- Social media – Eazy Custom: vanaf € 1250,- per maand

#### 5.2 Listing design (branding > Listing)
- Listing design – Eazy Start: vanaf € 287,- (eenmalig)
- Listing design – Eazy Groei: vanaf € 1287,- (eenmalig)
- Listing design – Eazy Premium: vanaf € 1887,- (eenmalig)

#### 5.3 Branding & huisstijl (branding > Branding)
- Branding – Eazy Start: vanaf € 337,- (eenmalig)
- Branding – Eazy Groei: vanaf € 487,- (eenmalig)
- Branding – Eazy Premium: vanaf € 987,- (eenmalig)

#### 5.4 Drukwerk (branding > Drukwerk)
- Drukwerk – Eazy Start: vanaf € 297,- (eenmalig)
- Drukwerk – Eazy Groei: vanaf € 697,- (eenmalig)
- Drukwerk – Eazy Premium: vanaf € 1497,- (eenmalig)

#### 5.5 Productverpakking (branding > Productverpakking)
- Productverpakking – Eazy Start: vanaf € 497,- (eenmalig)
- Productverpakking – Eazy Groei: vanaf € 997,- (eenmalig)
- Productverpakking – Eazy Premium: vanaf € 1997,- (eenmalig)

#### 5.6 SEO (pagina /seo – tab SEO)
- SEO – Eazy Start: vanaf € 397,- per maand
- SEO – Eazy Groei: vanaf € 797,- per maand
- SEO – Eazy Premium: vanaf € 1497,- per maand

#### 5.7 SEA / Search Engine Advertising (pagina /seo – tab SEA)
- SEA – Eazy Start: vanaf € 297,- per maand
- SEA – Eazy Groei: vanaf € 597,- per maand
- SEA – Eazy Premium: vanaf € 1297,- per maand

#### 5.8 Online marketing / advertenties (pagina /marketing)
- Online marketing – Eazy Essential: vanaf € 497,- per maand
- Online marketing – Eazy Step-up: vanaf € 997,- per maand
- Online marketing – Eazy Pro: vanaf € 1997,- per maand

Richtlijnen voor EXTRA diensten:
- Voeg alleen een extra dienst toe als dit logisch is op basis van intake + offertegesprek.
- Kies per categorie maximaal één pakket (bijvoorbeeld één SEO-pakket, één social media-pakket).
- Gebruik in "amount" altijd een heldere tekst, zoals:
  - "Vanaf € 300,- per maand"
  - "Vanaf € 487,- eenmalig"

GEEN tekst als:
- "Inbegrepen in opstart"
- "vanaf € 350,- per maand"
- Of andere bedragen die niet in bovenstaande lijst staan.

### 6. Hoe de INVESTERING eruit moet zien

De JSON-key "investment" moet er als volgt uitzien:

- "package_name": het gekozen WEBSITE-pakket (Eazy Essential / Eazy Step-up / Eazy Pro).
- "setup_price_eur": numeriek, 750 / 1500 / 2500.
- "monthly_price_eur": numeriek, 39.95 / 59.95 / 99.95.
- "why_this_package": duidelijke uitleg waarom dit webpakket het beste past.
- "rows": een array met regels voor de investering:
  - De EERSTE regel is ALTIJD de website:
    - "label": "Website – [pakketnaam]"
    - "amount": "Opstart € X,- eenmalig + € Y per maand"
  - Eventuele extra diensten (optioneel) komen daarna:
    - "label": bijvoorbeeld "SEO – Eazy Groei" of "Social media – Eazy Essential"
    - "amount": bijvoorbeeld "Vanaf € 797,- per maand" of "Vanaf € 337,- eenmalig"
- "total_setup_amount": tekstversie van de opstartkosten van het gekozen webpakket,
  bijvoorbeeld "€ 1.500,- eenmalig".
- "total_monthly_amount": tekstversie van het maandbedrag van het gekozen webpakket,
  bijvoorbeeld "€ 59,95 per maand".

BELANGRIJK:
- "rows" bevat GEEN regels met "Inbegrepen in opstart".
- Alle bedragen in "amount" komen UIT de lijsten hierboven.
- Extra diensten worden NIET opgeteld in "total_monthly_amount": die totalen zijn ALLEEN voor het gekozen websitepakket.

### 7. Paginastructuur (belangrijk onderdeel van de offerte)

Bepaal op basis van intake + offertegesprek wat een logische paginastructuur is voor deze website.

- Denk in concrete pagina’s zoals: Home, Diensten, Over ons, Cases/Projecten, Tarieven, Blog, Contact, Veelgestelde vragen, Webshop, etc.
- Kies alleen pagina’s die logisch zijn voor dit bedrijf en deze doelen.
- Richtlijn: meestal 4–10 pagina’s, geen overdreven of onrealistische structuur.

Voor elke pagina vul je in de JSON in:

- "title": duidelijke paginanaam (bijvoorbeeld "Home", "Diensten", "Over ons").
- "goal": korte omschrijving van het hoofddoel van die pagina. Voorbeelden:
  - "Eerste indruk, vertrouwen en directe conversie naar contact of aanvraag"
  - "Helder overzicht van alle diensten en doorsturen naar de juiste dienst"
  - "Alle praktische gegevens en laagdrempelige contactopties"
- "key_sections": 2–5 concrete blokken/secties als tekstregels (strings), bijvoorbeeld:
  - "Hero met krachtige belofte en duidelijke CTA"
  - "Overzicht belangrijkste diensten met korte uitleg"
  - "Social proof met reviews en logo’s van klanten"
  - "Contactformulier + contactgegevens en openingstijden"

GEEN HTML tags gebruiken in "key_sections", alleen platte tekst.
Maak de paginastructuur zo dat een designer/dev meteen snapt hoe de site moet worden opgebouwd.

### 8. OPDRACHT & JSON-STRUCTUUR

Schrijf een commerciële maar heldere offerte-tekst op basis van intake + offertegesprek.

Lever ALLEEN geldige JSON terug met exact deze structuur en keys:

{
  "headline": "korte, krachtige titel voor de offerte (1 zin, max 120 tekens)",
  "summary_paragraph": "samenvatting van het voorstel (3–5 zinnen, logisch lopend verhaal)",
  "summary_bullets": [
    "concreet voordeel 1, direct gekoppeld aan deze klant",
    "concreet voordeel 2, direct gekoppeld aan deze klant",
    "concreet voordeel 3, direct gekoppeld aan deze klant",
    "concreet voordeel 4, direct gekoppeld aan deze klant"
  ],
  "strong_points": [
    "sterk punt huidige situatie 1, gebaseerd op intake/aanvraag",
    "sterk punt huidige situatie 2, gebaseerd op intake/aanvraag",
    "sterk punt huidige situatie 3, gebaseerd op intake/aanvraag"
  ],
  "improvement_points": [
    "verbeterpunt 1, gebaseerd op intake + offertegesprek",
    "verbeterpunt 2, gebaseerd op intake + offertegesprek",
    "verbeterpunt 3, gebaseerd op intake + offertegesprek"
  ],
  "investment": {
    "package_name": "Eazy Essential of Eazy Step-up of Eazy Pro",
    "setup_price_eur": 1500,
    "monthly_price_eur": 59.95,
    "why_this_package": "korte uitleg waarom dit pakket het beste past bij deze klant",
    "rows": [
      {
        "label": "Website – Eazy Step-up",
        "amount": "Opstart € 1.500,- eenmalig + € 59,95 per maand"
      },
      {
        "label": "SEO – Eazy Groei",
        "amount": "Vanaf € 797,- per maand"
      }
    ],
    "total_setup_amount": "€ 1.500,- eenmalig",
    "total_monthly_amount": "€ 59,95 per maand"
  },
  "page_structure": {
    "summary": "korte samenvatting waarom deze paginastructuur logisch is voor dit bedrijf",
    "pages": [
      {
        "title": "Home",
        "goal": "Eerste indruk, vertrouwen en directe conversie naar contact of aanvraag",
        "key_sections": [
          "Hero met krachtige belofte en duidelijke CTA",
          "Korte introductie van het bedrijf",
          "Overzicht belangrijkste diensten met korte uitleg",
          "Social proof (reviews, logo’s, projecten)"
        ]
      },
      {
        "title": "Diensten",
        "goal": "Helder overzicht van alle diensten en sturen naar de juiste actie",
        "key_sections": [
          "Intro-blok met positionering en belofte",
          "Overzicht van diensten met korte beschrijving per dienst",
          "Call-to-actions naar contact of offerte-aanvraag"
        ]
      },
      {
        "title": "Contact",
        "goal": "Drempel zo laag mogelijk maken om contact op te nemen",
        "key_sections": [
          "Korte tekst wanneer en waarvoor mensen contact kunnen opnemen",
          "Contactformulier",
          "Contactgegevens (telefoon, e-mail, adres)",
          "Praktische info zoals openingstijden en route"
        ]
      }
    ]
  }
}

Let op:
- Pas de voorbeeldwaarden aan op basis van intake/offertegesprek.
- Houd je STRIKT aan de genoemde bedragen en pakketten.
- Extra diensten alleen toevoegen als er in intake/offerte echt over gesproken is.
- De paginastructuur moet logisch zijn voor deze specifieke klant, niet generiek.
- Verwijs in de tekst niet naar deze instructies of naar JSON, alleen de data zelf moet in JSON-formaat terugkomen.

Schrijf alles in jij-vorm, toegankelijk en professioneel.
TXT;

        try {
            $response = Http::withToken($openaiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4.1-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Je bent een Nederlandse copywriter die offertes schrijft voor webdesign en online marketing. Antwoord altijd in het Nederlands.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);

            if (! $response->ok()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OpenAI request mislukt (HTTP-fout).',
                ], 500);
            }

            $payload    = $response->json();
            $rawContent = $payload['choices'][0]['message']['content'] ?? '{}';
            $content    = json_decode($rawContent, true) ?: [];
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Genereren van offerte is mislukt.',
            ], 500);
        }

        // JSON opslaan
        $offerte->generated = $content;
        $offerte->save();

        // Klant:     /offerte/{token}
        // Beheerder: /offerte/{token}/edit
        $beheerderUrl = route('offerte.beheerder.show', ['token' => $offerte->public_uuid]);
        $klantUrl     = route('offerte.klant.show', ['token' => $offerte->public_uuid]);

        return response()->json([
            'success'        => true,
            'offerte_id'     => $offerte->id,
            'redirect_url'   => $beheerderUrl,
            'public_url'     => $klantUrl,
        ]);
    }

    /**
     * Zorgt dat de "bellen met de klant"-taak bestaat en geeft die terug.
     */
    protected function ensureOfferteTask(Project $project): ProjectTask
    {
        return $project->tasks()->firstOrCreate(
            ['type' => 'call_customer'],
            [
                'title'       => 'Bellen met de klant',
                'description' => 'Bel de klant t.a.v. feedback/goedkeuring preview',
                'due_at'      => null,
            ]
        );
    }
}