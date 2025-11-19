<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Offerte;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class OfferteController extends Controller
{
    public function klant(string $token)
    {
        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        return view('hub.projecten.offerte.klant', [
            'offerte' => $offerte,
            'project' => $offerte->project,
        ]);
    }
    
    public function beheerder(string $token)
    {
        $user = auth()->user();

        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        return view('hub.projecten.offerte.beheerder', [
            'user'    => $user,
            'offerte' => $offerte,
            'project' => $offerte->project,
        ]);
    }

    public function regenerate(Request $request, string $token)
    {
        $openaiKey = config('services.openai.key');

        if (! $openaiKey) {
            return response()->json([
                'success' => false,
                'message' => 'OpenAI API key ontbreekt. Vul OPENAI_API_KEY in je .env in.',
            ], 500);
        }

        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        $project = $offerte->project;

        if (! $project) {
            return response()->json([
                'success' => false,
                'message' => 'Geen gekoppeld project gevonden bij deze offerte.',
            ], 422);
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

        // Overrides leegmaken en nieuwe AI-content opslaan
        $offerte->content_overrides = null;
        $offerte->generated         = $content;
        $offerte->save();

        $beheerderUrl = route('offerte.beheerder.show', ['token' => $offerte->public_uuid]);
        $klantUrl     = route('offerte.klant.show', ['token' => $offerte->public_uuid]);

        return response()->json([
            'success'        => true,
            'message'        => 'Offerte is opnieuw gegenereerd met AI en alle handmatige wijzigingen zijn leeggemaakt.',
            'offerte_id'     => $offerte->id,
            'redirect_url'   => $beheerderUrl,
            'public_url'     => $klantUrl,
        ]);
    }

    public function inlineUpdate(Request $request, string $token)
    {
        /** @var Offerte $offerte */
        $offerte = Offerte::where('public_uuid', $token)->firstOrFail();

        $data = $request->validate([
            'key'     => 'required|string',
            'value'   => 'nullable|string',
            '_delete' => 'sometimes|boolean',
        ]);

        $key       = $data['key'];
        $overrides = $offerte->content_overrides ?? [];
        $generated = $offerte->generated ?? [];

        // 👉 Defaults die je nu in de Blade als fallback gebruikt
        $defaultScopeItems = [
            'Volledig maatwerk ontwerp afgestemd op jullie merk, doelgroep en positionering.',
            'Conversiegerichte pagina’s (bijvoorbeeld: Home, Diensten, Over ons, Contact).',
            'Technische basis voor SEO (laadsnelheid, structuur, metadata, basis redirects).',
            'Koppelingen met belangrijke tools (bijvoorbeeld: betaalprovider, e-mailmarketing, statistieken).',
            'Gebruiksvriendelijk beheer zodat jullie zelf content, producten en pagina’s kunnen beheren.',
            'Begeleiding bij livegang en korte training in het gebruik van de omgeving.',
        ];

        $defaultGoalsItems = [
            'Meer relevante bezoekers via organische zoekresultaten (SEO).',
            'Stijging in conversies (aanvragen, bestellingen of afspraken).',
            'Betere inzichtelijkheid in prestaties via duidelijke rapportages en dashboards.',
            'Kortere doorlooptijd van eerste bezoek tot klant.',
        ];

        $defaultApproachPhases = [
            [
                'title' => 'Fase 1 – Strategie & kick-off',
                'text'  => 'Gezamenlijke sessie(s) om doelen, doelgroep, positionering en functionaliteiten scherp te krijgen. We vertalen dit naar een concreet plan van aanpak.',
            ],
            [
                'title' => 'Fase 2 – Design & concept',
                'text'  => 'Uitwerking van het visuele ontwerp (desktop & mobiel), inclusief feedbackronde(s). Na akkoord zetten we het design door naar de bouw.',
            ],
            [
                'title' => 'Fase 3 – Bouw & inrichting',
                'text'  => 'Technische realisatie, contentinvoer en koppelingen (betaalprovider, formulieren, tracking). We leveren een testomgeving op om samen door te lopen.',
            ],
            [
                'title' => 'Fase 4 – Testen, livegang & nazorg',
                'text'  => 'Laatste checks, livegang en overdracht. Eventuele puntjes op de i verwerken we na livegang in overleg.',
            ],
        ];

        // Bepaal top-level key: strong_points, improvement_points, summary_bullets, ...
        $topLevel = explode('.', $key)[0];

        // Alle lijsten die door AI gevuld worden en waar jij inline items van toevoegt/verwijdert
        $listKeys = [
            'strong_points',
            'improvement_points',
            'summary_bullets',
            'scope_items',
            'goals_items',
            'approach_phases',
            'page_structure',
            'investment',
        ];

        // 👉 Als we voor het eerst in zo'n lijst editen:
        // kopieer dan eerst de volledige AI-array naar overrides,
        // zodat bestaande AI-items blijven bestaan.
        if (in_array($topLevel, $listKeys, true) && !array_key_exists($topLevel, $overrides)) {
            $base = data_get($generated, $topLevel, []);

            // Als AI niets heeft gezet, val terug op de Blade-defaults
            if ($topLevel === 'scope_items' && empty($base)) {
                $base = $defaultScopeItems;
            }

            if ($topLevel === 'goals_items' && empty($base)) {
                $base = $defaultGoalsItems;
            }

            if ($topLevel === 'approach_phases' && empty($base)) {
                $base = $defaultApproachPhases;
            }

            // Alleen zetten als het echt een array is
            if (is_array($base)) {
                $overrides[$topLevel] = $base;
            }
        }

        // DELETE (bullet / item verwijderen)
        if (!empty($data['_delete'])) {
            if (str_contains($key, '.')) {
                $parts      = explode('.', $key);
                $index      = array_pop($parts);      // bv. "2"
                $parentPath = implode('.', $parts);   // bv. "strong_points"

                $parent = data_get($overrides, $parentPath, []);

                if (is_array($parent) && array_key_exists($index, $parent)) {
                    unset($parent[$index]);
                    // Herindexeren zodat je weer nette 0,1,2,... hebt
                    $parent = array_values($parent);
                    data_set($overrides, $parentPath, $parent);
                } else {
                    Arr::forget($overrides, $key);
                }
            } else {
                Arr::forget($overrides, $key);
            }
        } else {
            // NORMALE UPDATE (tekst aanpassen of nieuw item)
            data_set($overrides, $key, $data['value']);
        }

        $offerte->content_overrides = $overrides;
        $offerte->save();

        return response()->json([
            'status' => 'ok',
            'key'    => $data['key'],
        ]);
    }

    public function download(string $token)
    {
        /** @var Offerte $offerte */
        $offerte = Offerte::with('project')
            ->where('public_uuid', $token)
            ->firstOrFail();

        $offerteDate   = $offerte->created_at ?? now();
        $offerteNummer = $offerte->number
            ?? ('OF-' . $offerteDate->format('Ym') . str_pad($offerte->id ?? 1, 4, '0', STR_PAD_LEFT));

        $pdf = Pdf::loadView('hub.projecten.offerte.pdf', [
            'offerte'       => $offerte,
            'project'       => $offerte->project,
            'offerteDate'   => $offerteDate,
            'offerteNummer' => $offerteNummer,
        ])->setPaper('a4');

        return $pdf->download($offerteNummer . '.pdf');
    }
}
