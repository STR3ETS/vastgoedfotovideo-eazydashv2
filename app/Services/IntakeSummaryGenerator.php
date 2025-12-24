<?php

namespace App\Services;

use App\Models\AanvraagWebsite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class IntakeSummaryGenerator
{
    public function generate(AanvraagWebsite $aanvraag): ?string
    {
        $aanvraag->loadMissing('tasks.questions');

        $task = $aanvraag->tasks->firstWhere('type', 'conduct_intake');
        $questions = $task?->questions ?? collect();

        $raw = $this->buildRawIntakeText($questions);

        if (blank($raw)) {
            return null;
        }

        // ✅ Als je (nog) geen OpenAI key hebt: sla “ruwe intake” op als fallback
        $apiKey = config('services.openai.key');
        if (blank($apiKey)) {
            return "### Intake (ruwe Q&A)\n\n" . $raw;
        }

        $payload = [
            'model' => config('services.openai.model', 'gpt-4.1'),
            'instructions' =>
                "Je bent een project lead bij een webbureau.\n"
                . "Maak een KORTE maar COMPLETE interne samenvatting (NL) om een homepage preview te bouwen.\n"
                . "Richtlijn: 220–320 woorden totaal.\n"
                . "Geen markdown headings (#, ##, ###).\n"
                . "Gebruik EXACT deze kopjes, EXACT in deze volgorde, met EXACT 1 lege regel tussen elke sectie.\n"
                . "Zorg ook voor 1 lege regel NA elke bulletlijst.\n"
                . "\n"
                . "**Korte intro**: <1–2 zinnen: wat bouwen we + voor wie>\n"
                . "\n"
                . "**Doel**: <1 zin: resultaat/impact>\n"
                . "\n"
                . "**Must-haves**:\n"
                . "- <6–8 bullets, max 12 woorden per bullet>\n"
                . "\n"
                . "**Nice-to-haves**:\n"
                . "- <max 4 bullets, max 12 woorden per bullet>\n"
                . "\n"
                . "**Aandachtspunten**:\n"
                . "- <max 4 bullets: risico’s, afhankelijkheden, ontbrekende input>\n"
                . "\n"
                . "**Moet in de preview (homepage)**:\n"
                . "- <6–8 bullets: wat moet VISUEEL/CONTENT op de homepage terugkomen>\n"
                . "\n"
                . "**Huisstijl**:\n"
                . "- <kleuren/branding/logo/typografie>\n"
                . "\n"
                . "**Home preview opbouw (voorstel)**:\n"
                . "- <max 6 bullets: secties in volgorde, bijv. hero → diensten → CTA → footer>\n"
                . "\n"
                . "**Volgende stap**:\n"
                . "- <max 3 bullets>\n"
                . "\n"
                . "Regels:\n"
                . "- Als kleuren/huisstijl genoemd zijn: NOEM ze letterlijk (bijv. 'wit/roze').\n"
                . "- Preview = ALLEEN de homepage, geen subpagina’s.\n"
                . "- Als info ontbreekt: zet een bullet 'Nog te bepalen'.\n"
                . "- Geen extra tekst buiten deze secties.\n"
                . "- Feitelijk, kort, geen verkooppraat.",
            'input' =>
                "PROJECT/CONTEXT\n"
                ."Bedrijf: ".($aanvraag->company ?? 'Onbekend')."\n"
                ."Type aanvraag: ".($aanvraag->choice ?? 'onbekend')."\n"
                ."\n"
                ."INTAKE Q&A (brondata)\n\n"
                .$raw,

            // extra rem erop
            'max_output_tokens' => 520,
            'temperature' => 0.2,
        ];

        $res = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout(30)
            ->post('https://api.openai.com/v1/responses', $payload);

        if (!$res->ok()) {
            return "### Intake (ruwe Q&A)\n\n" . $raw;
        }

        $text = $this->extractOutputText((array) $res->json());

        return blank($text)
            ? ("### Intake (ruwe Q&A)\n\n" . $raw)
            : trim($text);
    }

    private function buildRawIntakeText(Collection $questions): string
    {
        return $questions
            ->sortBy(fn ($q) => $q->order ?? 999)
            ->map(function ($q) {
                $qText = trim((string) ($q->question ?? ''));
                $aText = trim((string) ($q->answer ?? ''));

                if ($qText === '' && $aText === '') {
                    return null;
                }

                $prefix = isset($q->order) ? ($q->order . '. ') : '';
                return $prefix . $qText . "\nAntwoord: " . ($aText !== '' ? $aText : '—');
            })
            ->filter()
            ->implode("\n\n");
    }

    private function extractOutputText(array $json): string
    {
        $out = [];

        foreach (($json['output'] ?? []) as $item) {
            if (($item['type'] ?? null) !== 'message') continue;
            if (($item['role'] ?? null) !== 'assistant') continue;

            foreach (($item['content'] ?? []) as $c) {
                if (($c['type'] ?? null) === 'output_text' && isset($c['text'])) {
                    $out[] = $c['text'];
                }
            }
        }

        return trim(implode("\n", $out));
    }
}
