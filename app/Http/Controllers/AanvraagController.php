<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AanvraagWebsite;
use App\Models\AanvraagTask;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AanvraagController extends Controller
{
    public function storeWebsiteAanvraag(Request $request)
    {
        $validated = $request->validate([
            'choice'       => 'nullable|string',
            'url'          => 'nullable|string',
            'company'      => 'nullable|string',
            'description'  => 'nullable|string',
            'goal'         => 'nullable|string',
            'example1'     => 'nullable|string',
            'example2'     => 'nullable|string',
            'contactName'  => 'required|string',
            'contactEmail' => 'required|email',
            'contactPhone' => 'required|string',
        ]);

        $validated['visit_id'] = $this->getVisitIdFromCookie($request);

        Log::info('Aanvraag store - validated data', $validated);

        $aanvraag = AanvraagWebsite::create($validated);

        /**
         * ✅ AI-samenvatting maken en opslaan
         */
        try {
            $summary = $this->generateAiSummaryForAanvraag($aanvraag);

            if ($summary) {
                $aanvraag->ai_summary = $summary;
                $aanvraag->save();
            }
        } catch (\Throwable $e) {
            Log::warning('[Aanvraag AI summary] failed', [
                'aanvraag_id' => $aanvraag->id,
                'error'       => $e->getMessage(),
            ]);
        }

        // Default call-task + checklist
        $callTask = AanvraagTask::create([
            'aanvraag_website_id' => $aanvraag->id,
            'type'                => 'call_customer',
            'title'               => 'Bel klant voor intake',
            'status'              => 'open',
            'due_at'              => now()->addDay(),
        ]);

        // Kies vragen op basis van choice: 'new' / 'renew'
        if ($aanvraag->choice === 'renew') {
            $questions = [
                "Zou je me wat kunnen vertellen over jullie bedrijf/huidige stand van zaken?",
                "Wat mis je aan je huidige website?",
                "Wat moet de website jullie concreet opleveren in de komende 6–12 maanden?",
                "Wanneer zouden jullie zeggen: “De website is écht een succes\"?",
                "Is de content/tekst actueel?",
                "Wie is jullie ideale klant voor deze website (branche, type bedrijf/persoon)?",
                "Met welk probleem of met welke vraag komt die ideale klant meestal bij jullie?",
                "Waarom kiezen klanten nu voor jullie en niet voor een concurrent?",
                "Welke diensten of producten moeten extra in de spotlight staan op de website?",
                "Zijn er specifieke acties die bezoekers moeten doen? (bellen, offerte aanvragen, afspraak plannen, downloaden, inschrijven, etc.)",
                "Zijn er referenties/cases die jullie graag willen uitlichten?",
                "Hebben jullie een bestaande huisstijl (logo, kleuren, fonts) waar de website op aan moet sluiten? Zo nee, moeten wij dit voor jullie faciliteren?",
                "Wat vond je goed aan de voorbeeld websites die je ons gaf?",
                "Wat voor een uitstraling moet de website hebben?",
                "Hebben jullie al een SEO strategie? Zo ja, vertel. Zo nee, moeten wij dit faciliteren?",
                "Gaan jullie advertenties draaien op de website? Zo ja, doet een andere partij dit voor jullie of moeten wij dit faciliteren?",
                "Maken jullie al gebruik van automatiseringen/software? Zo ja, vertel. Zo nee, moeten wij dit voor jullie faciliteren indien nodig?",
                "Is er een specifieke datum of campagne waartegen de website live moet staan?",
                "Is er nog iets anders wat voor jullie belangrijk is wat we nog niet hebben besproken?",
                "Wie is er verantwoordelijk voor het project?",
                "Vind je het goed dat we een groepsapp aanmaken waarin we je op de hoogte houden en jouw website preview sturen?",
            ];
        } else {
            $questions = [
                "Zou je me wat kunnen vertellen over jullie bedrijf/huidige stand van zaken?",
                "Wat wil je graag zien op de website?",
                "Wat moet de website jullie concreet opleveren in de komende 6–12 maanden?",
                "Wanneer zouden jullie zeggen: “De website is écht een succes\"?",
                "Heb je content/teksten of wil je dat wij dit faciliteren?",
                "Wie is jullie ideale klant voor deze website (branche, type bedrijf/persoon)?",
                "Met welk probleem of met welke vraag komt die ideale klant meestal bij jullie?",
                "Waarom kiezen klanten nu voor jullie en niet voor een concurrent?",
                "Welke diensten of producten moeten extra in de spotlight staan op de website?",
                "Zijn er specifieke acties die bezoekers moeten doen? (bellen, offerte aanvragen, afspraak plannen, downloaden, inschrijven, etc.)",
                "Zijn er referenties/cases die jullie graag willen uitlichten?",
                "Hebben jullie een bestaande huisstijl (logo, kleuren, fonts) waar de website op aan moet sluiten? Zo nee, moeten wij dit voor jullie faciliteren?",
                "Wat vond je goed aan de voorbeeld websites die je ons gaf?",
                "Wat voor een uitstraling moet de website hebben?",
                "Hebben jullie al een SEO strategie? Zo ja, vertel. Zo nee, moeten wij dit faciliteren?",
                "Gaan jullie advertenties draaien op de website? Zo ja, doet een andere partij dit voor jullie of moeten wij dit faciliteren?",
                "Maken jullie al gebruik van automatiseringen/software? Zo ja, vertel. Zo nee, moeten wij dit voor jullie faciliteren indien nodig?",
                "Is er een specifieke datum of campagne waartegen de website live moet staan?",
                "Is er nog iets anders wat voor jullie belangrijk is wat we nog niet hebben besproken?",
                "Wie is er verantwoordelijk voor het project?",
                "Vind je het goed dat we een groepsapp aanmaken waarin we je op de hoogte houden en jouw website preview sturen?",
            ];
        }

        // ✚ Altijd als laatste: vrije notitie-vraag (niet verplicht)
        $notesQuestion = "Notities (vrij veld)";
        $questions[] = $notesQuestion;

        $callTask->questions()->createMany(
            collect($questions)->map(function ($q, $index) use ($notesQuestion) {
                $isNotes = mb_strtolower($q) === mb_strtolower($notesQuestion);
                return [
                    'question' => $q,
                    'order'    => $index + 1,
                    'required' => $isNotes ? false : true,
                ];
            })->toArray()
        );

        Log::info('Aanvraag store - created aanvraag', $aanvraag->toArray());

        return response()->json([
            'success' => true,
            'id'      => $aanvraag->id,
            'message' => 'Aanvraag succesvol opgeslagen 🚀',
            'summary' => $aanvraag->ai_summary, // handig voor direct UI updaten
        ]);
    }

    private function generateAiSummaryForAanvraag(AanvraagWebsite $aanvraag): ?string
    {
        $choiceLabel = $aanvraag->choice === 'renew' ? 'Website vernieuwen' : 'Nieuwe website';

        $payload = [
            'type'        => $choiceLabel,
            'url'         => $aanvraag->url,
            'bedrijf'     => $aanvraag->company,
            'omschrijving'=> $aanvraag->description,
            'doel'        => $aanvraag->goal,
            'voorbeelden' => array_values(array_filter([$aanvraag->example1, $aanvraag->example2])),
            'contact'     => [
                'naam'  => $aanvraag->contactName,
                'email' => $aanvraag->contactEmail,
                'tel'   => $aanvraag->contactPhone,
            ],
        ];

        $instructions = <<<SYS
Je bent een senior webstrateeg bij een webdesign agency.
Maak een compacte, professionele samenvatting van deze website-aanvraag.
Schrijf in het Nederlands, 3-6 zinnen.
Noem: type aanvraag (nieuw/vernieuwen), bedrijf (indien bekend), URL (indien bekend),
doel, opvallende wensen/voorbeelden en wat dit impliceert voor de intake.
Geen bullets, geen emoji.
SYS;

        $response = OpenAI::responses()->create([
            'model' => config('openai.model', 'gpt-5'),
            'instructions' => $instructions,
            'input' => "Aanvraag data (JSON):\n" . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'temperature' => 0.2,
            'max_output_tokens' => 220,
        ]);

        $text = trim($response->outputText ?? '');

        return $text !== '' ? $text : null;
    }

    private function getVisitIdFromCookie(Request $request): ?string
    {
        return $request->cookie('eo_visit_id');
    }
}