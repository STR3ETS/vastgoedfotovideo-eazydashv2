<?php

namespace App\Services;

use App\Models\SeoAudit;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class SeoAuditTaskPlanService
{
    /**
     * Bouwt een AI takenplan op basis van de audit insights.
     *
     * Output structuur:
     *
     * [
     *   'tasks' => [
     *     [
     *       'id'               => 'tech_1',
     *       'owner'            => 'developer',   // developer, copywriter, seo, designer, marketing
     *       'category'         => 'Techniek',    // komt uit jouw categorieën
     *       'title'            => 'Herstel 4xx foutmeldingen',
     *       'description'      => 'Korte uitleg wat er moet gebeuren',
     *       'priority'         => 'must_fix',    // must_fix, high, normal, low
     *       'impact'           => 'hoog',        // hoog, middel, laag
     *       'effort'           => 'laag',        // laag, middel, hoog
     *       'estimated_minutes'=> 60,
     *       'related_issues'   => ['http_status_4xx', 'redirect_loops'],
     *     ],
     *   ],
     *   'notes_for_colleague' => 'Korte toelichting voor interne collega',
     *   'client_summary'      => 'Samenvatting in eenvoudige taal voor de klant',
     * ]
     */
    public function generatePlan(SeoAudit $audit, array $summary, array $quickWins, array $actions): array
    {
        $apiKey = config('openai.api_key');

        if (! $apiKey) {
            throw new \RuntimeException('OpenAI API sleutel ontbreekt. Zet OPENAI_API_KEY in je .env of vul config/openai.php aan.');
        }

        // Beperk aantal items zodat de prompt niet uit de hand loopt
        $quickWinsTrimmed = collect($quickWins)->take(15)->values()->all();
        $actionsTrimmed   = collect($actions)->take(10)->values()->all();

        $model = config('openai.model', 'gpt-4o-mini');

        $payload = [
            'model' => $model,
            'temperature' => 0.4,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => implode("\n", [
                        'Je bent een Nederlandse SEO lead binnen een webbureau.',
                        'Je krijgt een technisch SEO audit rapport in compacte vorm.',
                        'Je maakt een concreet, taakgericht plan voor collega s: developer, copywriter, SEO en designer.',
                        'Schrijf kort, concreet en zonder verkooppraat.',
                        'Richt je op wat het team moet doen, niet op wat de klant moet doen.',
                        'Lever je output als geldige JSON met de structuur uit de beschrijving.',
                    ]),
                ],
                [
                    'role' => 'user',
                    'content' => json_encode([
                        'audit' => [
                            'id'      => $audit->id,
                            'domain'  => $audit->domain,
                            'company' => optional($audit->company)->name,
                            'type'    => $audit->type,
                            'status'  => $audit->status,
                        ],
                        'summary'   => $summary,
                        'quickWins' => $quickWinsTrimmed,
                        'actions'   => $actionsTrimmed,
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ],
        ];

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', $payload)
            ->throw()
            ->json();

        $content = data_get($response, 'choices.0.message.content');

        $data = json_decode((string) $content, true);

        if (! is_array($data)) {
            throw new \RuntimeException('OpenAI gaf geen geldige JSON terug voor het takenplan.');
        }

        // Zorg dat er altijd een tasks array is
        if (! isset($data['tasks']) || ! is_array($data['tasks'])) {
            $data['tasks'] = [];
        }

        return $data;
    }
}
