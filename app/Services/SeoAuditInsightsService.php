<?php

namespace App\Services;

use App\Models\SeoAudit;
use App\Models\SeoAuditResult;
use Illuminate\Support\Arr;

class SeoAuditInsightsService
{
    /**
     * Wordt aangeroepen vanuit RunSeoAuditJob na het ophalen van het SERanking rapport.
     *
     * Doel:
     * - Rapport plat slaan naar een uniforme lijst van issues
     * - Issues verrijken met categorie, severity, impact, effort, owner, priority
     * - Issues opslaan in seo_audit_results
     * - Samenvatting + issues ook in meta['insights'] bewaren
     */
    public function storeResultsFromReport(SeoAudit $audit, array $report): void
    {
        $meta = $audit->meta ?? [];

        // Genormaliseerde issues uit het rapport halen
        $rawIssues = $this->normalizeIssuesFromReport($report);

        // Verrijken met categorie, severity, owner, impact, effort, priority
        $enriched = collect($rawIssues)->map(function (array $issue) {
            $issue['category'] = $this->categorizeIssue($issue);
            $issue['severity'] = $this->severityLabel($issue['status'] ?? null);
            $issue['owner']    = $this->suggestOwnerForIssue($issue);
            $issue['impact']   = $this->impactForIssue($issue);
            $issue['effort']   = $this->effortForIssue($issue);
            $issue['priority'] = $this->priorityForIssue($issue);

            return $issue;
        });

        // Bestaande resultaten voor deze audit verwijderen
        $audit->results()->delete();

        // Nieuwe seo_audit_results records aanmaken
        foreach ($enriched as $issue) {
            $data = $issue['data'] ?? [];

            // Proberen sample URLs eruit te halen als die in de data zitten
            $sampleUrls = $this->extractSampleUrlsFromIssue($issue);

            SeoAuditResult::create([
                'seo_audit_id'   => $audit->id,
                'raw_issue_id'   => $issue['raw_issue_id'] ?? null,
                'raw_name'       => $issue['raw_name'] ?? ($issue['name'] ?? null),
                'severity'       => $issue['severity'] ?? null,
                'pages_affected' => $issue['value'] ?? null,
                'sample_urls'    => $sampleUrls,
                'code'           => $issue['code'] ?? null,
                'label'          => $issue['name'] ?? ($issue['code'] ?? null),
                'category'       => $issue['category'] ?? null,
                'impact'         => $issue['impact'] ?? null,
                'effort'         => $issue['effort'] ?? null,
                'owner'          => $issue['owner'] ?? null,
                'priority'       => $issue['priority'] ?? null,
                'data'           => $data,
            ]);
        }

        // Basis summary op basis van rapport
        $summary = [
            'score'    => data_get($report, 'score_percent', $audit->overall_score),
            'pages'    => data_get($report, 'total_pages'),
            'errors'   => data_get($report, 'total_errors'),
            'warnings' => data_get($report, 'total_warnings'),
            'notices'  => data_get($report, 'total_notices'),
            'passed'   => data_get($report, 'total_passed'),
        ];

        // Extra tellingen op basis van verrijkte issues
        $collection = collect($enriched);

        $summary['critical_issues'] = $collection->where('severity', 'critical')->sum('value');
        $summary['warning_issues']  = $collection->where('severity', 'warning')->sum('value');
        $summary['info_issues']     = $collection->where('severity', 'info')->sum('value');
        $summary['issues_total']    = $collection->sum('value');

        $summary['issues_by_category'] = $collection
            ->groupBy('category')
            ->map(function ($items) {
                return [
                    'issues'   => $items->count(),
                    'pages'    => $items->sum('value'),
                    'critical' => $items->where('severity', 'critical')->sum('value'),
                    'warnings' => $items->where('severity', 'warning')->sum('value'),
                ];
            })
            ->toArray();

        // Enriched issues + summary in meta bewaren voor snelle toegang in de UI
        $meta['insights']['issues']        = $enriched->values()->all();
        $meta['insights']['summary']       = $summary;
        $meta['insights']['owner_groups']  = $this->buildOwnerGroups($enriched->all());
        $meta['insights']['page_overview'] = $this->buildPageOverview($enriched->all());

        $audit->meta = $meta;
        $audit->save();
    }

    /**
     * Bouwt een slim insights object voor de detailpagina.
     * Dit gebruik je in SeoAuditController@show.
     */
    public function buildInsights(SeoAudit $audit): array
    {
        $meta   = $audit->meta ?? [];
        $report = data_get($meta, 'seranking.report', data_get($meta, 'report', []));

        // Issues uit meta als ze al opgeslagen zijn, anders opnieuw uit report halen
        $issues = data_get($meta, 'insights.issues');
        if (! is_array($issues)) {
            $issues = $this->normalizeIssuesFromReport(is_array($report) ? $report : []);
            $issues = collect($issues)->map(function (array $issue) {
                $issue['category'] = $this->categorizeIssue($issue);
                $issue['severity'] = $this->severityLabel($issue['status'] ?? null);
                $issue['owner']    = $this->suggestOwnerForIssue($issue);
                $issue['impact']   = $this->impactForIssue($issue);
                $issue['effort']   = $this->effortForIssue($issue);
                $issue['priority'] = $this->priorityForIssue($issue);

                return $issue;
            })->values()->all();
        }

        $collection = collect($issues);

        // Basis summary; als die in meta staat, gebruiken we die als start
        $summary = data_get($meta, 'insights.summary', [
            'score'    => data_get($report, 'score_percent', $audit->overall_score),
            'pages'    => data_get($report, 'total_pages'),
            'errors'   => data_get($report, 'total_errors'),
            'warnings' => data_get($report, 'total_warnings'),
            'notices'  => data_get($report, 'total_notices'),
            'passed'   => data_get($report, 'total_passed'),
        ]);

        // Zeker weten dat de extra velden ook aanwezig zijn
        $summary['critical_issues'] = $collection->where('severity', 'critical')->sum('value');
        $summary['warning_issues']  = $collection->where('severity', 'warning')->sum('value');
        $summary['info_issues']     = $collection->where('severity', 'info')->sum('value');
        $summary['issues_total']    = $collection->sum('value');

        $summary['issues_by_category'] = $collection
            ->groupBy('category')
            ->map(function ($items) {
                return [
                    'issues'   => $items->count(),
                    'pages'    => $items->sum('value'),
                    'critical' => $items->where('severity', 'critical')->sum('value'),
                    'warnings' => $items->where('severity', 'warning')->sum('value'),
                ];
            })
            ->toArray();

        // Groepen per categorie voor de UI
        $groups = $collection
            ->groupBy('category')
            ->map(function ($items) {
                return [
                    'critical' => $items->where('severity', 'critical')->values()->all(),
                    'warnings' => $items->where('severity', 'warning')->values()->all(),
                    'info'     => $items->where('severity', 'info')->values()->all(),
                ];
            })
            ->toArray();

        $quickWins          = $this->buildQuickWins($collection->all());
        $recommendedActions = $this->buildRecommendedActions($collection->all());

        // Owner groepen (wie moet wat doen)
        $ownerGroups = data_get($meta, 'insights.owner_groups');
        if (! is_array($ownerGroups)) {
            $ownerGroups = $this->buildOwnerGroups($collection->all());
        }

        // Pagina overzicht (welke pagina's zijn het zwaarst getroffen)
        $pageOverview = data_get($meta, 'insights.page_overview');
        if (! is_array($pageOverview)) {
            $pageOverview = $this->buildPageOverview($collection->all());
        }

        return [
            'summary'             => $summary,
            'issue_groups'        => $groups,
            'owner_groups'        => $ownerGroups,
            'page_overview'       => $pageOverview,
            'quick_wins'          => $quickWins,
            'recommended_actions' => $recommendedActions,
            'raw_issues'          => $collection->all(),
            'raw_report'          => $report,
        ];
    }

    /**
     * Slaat SERanking report plat naar een standaard issues-lijst.
     * We houden hier ook de ruwe check data bij in 'data'.
     */
    protected function normalizeIssuesFromReport(array $report): array
    {
        $issues = [];

        foreach (data_get($report, 'sections', []) as $section) {
            $sectionName = $section['name'] ?? $section['uid'] ?? 'Onbekende categorie';
            $props       = $section['props'] ?? [];

            foreach ($props as $code => $check) {
                // In SERanking kan de key zowel string-code als numerieke index zijn
                $issueCode = is_string($code) ? $code : ($check['code'] ?? null);

                $issues[] = [
                    'raw_issue_id' => $check['id']   ?? null,
                    'raw_name'     => $check['name'] ?? ($check['title'] ?? null),

                    'code'         => $issueCode,
                    'section'      => $sectionName,
                    'status'       => $check['status'] ?? null, // error / warning / ok
                    'name'         => $check['name']   ?? ($check['title'] ?? $issueCode),
                    'value'        => (int) ($check['value'] ?? 0), // aantal pagina's

                    'data'         => $check, // volledige ruwe data bewaren
                ];
            }
        }

        return $issues;
    }

    /**
     * Bepaal globale categorie op basis van section/code.
     */
    protected function categorizeIssue(array $issue): string
    {
        $section = mb_strtolower((string) ($issue['section'] ?? ''));
        $code    = mb_strtolower((string) ($issue['code'] ?? ''));

        // Techniek
        if (
            str_contains($section, 'performance') ||
            str_contains($section, 'speed') ||
            str_contains($section, 'server') ||
            str_contains($section, 'https') ||
            str_contains($section, 'mobile') ||
            str_contains($section, 'index')
        ) {
            return 'Techniek';
        }
        if (str_contains($code, 'core_web_vitals') || str_contains($code, 'page_speed')) {
            return 'Techniek';
        }

        // Content
        if (
            str_contains($section, 'content') ||
            str_contains($section, 'meta') ||
            str_contains($section, 'title') ||
            str_contains($section, 'headings')
        ) {
            return 'Content';
        }
        if (
            str_contains($code, 'meta_') ||
            str_contains($code, 'title') ||
            str_contains($code, 'h1')
        ) {
            return 'Content';
        }

        // Links
        if (str_contains($section, 'links') || str_contains($section, 'backlink')) {
            return 'Links';
        }
        if (str_contains($code, 'backlink') || str_contains($code, 'anchor')) {
            return 'Links';
        }

        // UX
        if (str_contains($section, 'usability') || str_contains($section, 'ux')) {
            return 'UX';
        }

        return 'Overig';
    }

    /**
     * Zet status van SERanking om naar severity label.
     */
    protected function severityLabel(?string $status): string
    {
        $status = $status ? mb_strtolower($status) : '';

        return match ($status) {
            'error', 'critical' => 'critical',
            'warning'           => 'warning',
            default             => 'info',
        };
    }

    /**
     * Impact van een issue op basis van severity en aantal pagina's.
     */
    protected function impactForIssue(array $issue): string
    {
        $severity = $issue['severity'] ?? $this->severityLabel($issue['status'] ?? null);
        $pages    = (int) ($issue['value'] ?? 0);

        if ($severity === 'critical') {
            return $pages > 50 ? 'hoog' : 'middel';
        }

        if ($severity === 'warning') {
            return $pages > 100 ? 'middel' : 'laag';
        }

        return 'laag';
    }

    /**
     * Verwachte effort om het op te lossen.
     */
    protected function effortForIssue(array $issue): string
    {
        $code = mb_strtolower((string) ($issue['code'] ?? ''));

        if (
            str_contains($code, 'redirect') ||
            str_contains($code, '4xx') ||
            str_contains($code, '5xx') ||
            str_contains($code, 'broken_link')
        ) {
            return 'middel';
        }

        if (
            str_contains($code, 'meta_') ||
            str_contains($code, 'title') ||
            str_contains($code, 'description') ||
            str_contains($code, 'h1')
        ) {
            return 'laag';
        }

        if (
            str_contains($code, 'page_speed') ||
            str_contains($code, 'core_web_vitals')
        ) {
            return 'hoog';
        }

        return 'middel';
    }

    /**
     * Prioriteit voor issue in het werk, los van quick wins in de UI.
     */
    protected function priorityForIssue(array $issue): string
    {
        $severity = $issue['severity'] ?? $this->severityLabel($issue['status'] ?? null);
        $pages    = (int) ($issue['value'] ?? 0);

        if ($severity === 'critical' && $pages <= 50) {
            return 'quick_win';
        }

        if ($severity === 'critical' && $pages > 50) {
            return 'must_fix';
        }

        if ($severity === 'warning' && $pages > 0) {
            return 'normal';
        }

        return 'low';
    }

    /**
     * Quick wins zijn issues met hoge impact maar beperkt aantal pagina's.
     */
    protected function buildQuickWins(array $issues): array
    {
        $collection = collect($issues)
            ->filter(function ($i) {
                if (! in_array($i['severity'], ['critical', 'warning'], true)) {
                    return false;
                }

                $affected = (int) ($i['value'] ?? 0);

                return $affected > 0 && $affected <= 50;
            })
            ->sortByDesc('severity') // critical eerst
            ->sortByDesc('value');

        $quick = [];

        foreach ($collection->take(10) as $issue) {
            $pages    = (int) ($issue['value'] ?? 0);
            $pagesTxt = $pages > 0 ? " (ca. {$pages} pagina’s)" : '';

            $quick[] = [
                'title'       => $issue['name'] ?: ($issue['code'] ?: 'Onbekend probleem'),
                'description' => $this->shortDescriptionForIssue($issue) . $pagesTxt,
                'impact'      => $issue['impact'] ?? ($issue['severity'] === 'critical' ? 'hoog' : 'middelmatig'),
                'effort'      => $issue['effort'] ?? 'laag',
                'owner'       => $issue['owner'] ?? $this->suggestOwnerForIssue($issue),
                'pages'       => $pages,
                'category'    => $issue['category'] ?? 'Overig',
                'code'        => $issue['code'],
            ];
        }

        return $quick;
    }

    /**
     * Grotere acties per categorie (voor je plan van aanpak).
     */
    protected function buildRecommendedActions(array $issues): array
    {
        $byCategory = collect($issues)
            ->filter(fn ($i) => in_array($i['severity'], ['critical', 'warning'], true))
            ->groupBy('category');

        $actions = [];

        foreach ($byCategory as $category => $items) {
            $top = $items
                ->sortByDesc('severity')
                ->sortByDesc('value')
                ->take(3)
                ->values()
                ->all();

            if (empty($top)) {
                continue;
            }

            $titles = collect($top)
                ->map(fn ($i) => $i['name'] ?: $i['code'])
                ->filter()
                ->values()
                ->all();

            $actions[] = [
                'category'        => $category,
                'title'           => $this->actionTitleForCategory($category),
                'priority'        => $this->priorityForCategory($category),
                'impact'          => 'hoog',
                'effort'          => 'middelmatig',
                'owner'           => $this->suggestOwnerForCategory($category),
                'summary'         => 'Belangrijkste aandachtspunten: ' . implode(', ', $titles),
                'linked_issues'   => $top,
                'suggested_steps' => $this->suggestStepsForCategory($category),
            ];
        }

        usort($actions, fn ($a, $b) => $a['priority'] <=> $b['priority']);

        return $actions;
    }

    protected function shortDescriptionForIssue(array $issue): string
    {
        $code = mb_strtolower((string) ($issue['code'] ?? ''));

        if (str_contains($code, 'meta_title') || str_contains($code, 'title')) {
            return 'Titels controleren en optimaliseren voor zoekwoorden en klikratio';
        }

        if (str_contains($code, 'meta_description')) {
            return 'Meta descriptions toevoegen of verbeteren zodat elke pagina een duidelijke omschrijving heeft';
        }

        if (str_contains($code, 'h1')) {
            return 'Per pagina een duidelijke H1 kop toevoegen of corrigeren';
        }

        if (str_contains($code, 'image_alt')) {
            return 'Alt teksten toevoegen aan belangrijke afbeeldingen';
        }

        if (str_contains($code, 'redirect') || str_contains($code, '4xx') || str_contains($code, '5xx')) {
            return 'Kapotte links en redirects herstellen zodat alle pagina’s goed bereikbaar zijn';
        }

        if (str_contains($code, 'page_speed') || str_contains($code, 'core_web_vitals')) {
            return 'Laadsnelheid verbeteren door afbeeldingen te optimaliseren en caching in te richten';
        }

        return 'Los dit probleem op voor de belangrijkste pagina’s';
    }

    protected function suggestOwnerForIssue(array $issue): string
    {
        $category = $issue['category'] ?? '';

        return match ($category) {
            'Techniek' => 'developer',
            'Content'  => 'copywriter',
            'Links'    => 'seo',
            'UX'       => 'designer',
            default    => 'seo',
        };
    }

    protected function actionTitleForCategory(string $category): string
    {
        return match ($category) {
            'Techniek' => 'Technische basis van de website op orde brengen',
            'Content'  => 'Content en metadata optimaliseren',
            'Links'    => 'Autoriteit en interne links verbeteren',
            'UX'       => 'Gebruikerservaring en mobile vriendelijkheid verbeteren',
            default    => 'Belangrijkste SEO problemen aanpakken',
        };
    }

    protected function priorityForCategory(string $category): int
    {
        return match ($category) {
            'Techniek' => 1,
            'Content'  => 2,
            'Links'    => 3,
            'UX'       => 4,
            default    => 5,
        };
    }

    protected function suggestOwnerForCategory(string $category): string
    {
        return match ($category) {
            'Techniek' => 'developer',
            'Content'  => 'copywriter',
            'Links'    => 'seo',
            'UX'       => 'designer',
            default    => 'seo',
        };
    }

    protected function suggestStepsForCategory(string $category): array
    {
        return match ($category) {
            'Techniek' => [
                'Controleer laadsnelheid en core web vitals op de belangrijkste pagina’s.',
                'Implementeer caching en compressie waar mogelijk.',
                'Los 4xx en 5xx fouten op en controleer redirects.',
            ],
            'Content' => [
                'Bepaal de belangrijkste zoekwoorden per pagina.',
                'Optimaliseer titels en meta descriptions.',
                'Controleer H1 koppen en heading-structuur.',
            ],
            'Links' => [
                'Herstel kapotte interne links.',
                'Check of belangrijke pagina’s voldoende interne links krijgen.',
                'Maak een plan voor het verkrijgen van kwalitatieve backlinks.',
            ],
            'UX' => [
                'Check hoe de belangrijkste pagina’s eruit zien op mobiel.',
                'Verbeter leesbaarheid en klikbare elementen op kleine schermen.',
                'Zorg dat formulieren en call-to-actions duidelijk zichtbaar zijn.',
            ],
            default => [
                'Begin met de categorie met hoogste prioriteit en werk de acties gestructureerd af.',
            ],
        };
    }

    /**
     * Bouwt groepen per eigenaar (developer, copywriter, seo, designer).
     */
    protected function buildOwnerGroups(array $issues): array
    {
        $byOwner = collect($issues)
            ->groupBy(function ($issue) {
                return $issue['owner'] ?? $this->suggestOwnerForIssue($issue);
            });

        $groups = [];

        foreach ($byOwner as $owner => $items) {
            $owner = $owner ?: 'onbekend';

            $totalIssues    = $items->count();
            $criticalPages  = $items->where('severity', 'critical')->sum('value');
            $warningPages   = $items->where('severity', 'warning')->sum('value');

            $topIssues = $items
                ->sortByDesc('severity')
                ->sortByDesc('value')
                ->take(5)
                ->map(function ($issue) {
                    return [
                        'title'    => $issue['name'] ?: $issue['code'],
                        'code'     => $issue['code'],
                        'pages'    => (int) ($issue['value'] ?? 0),
                        'category' => $issue['category'] ?? 'Overig',
                        'severity' => $issue['severity'] ?? 'info',
                        'impact'   => $issue['impact'] ?? null,
                        'effort'   => $issue['effort'] ?? null,
                    ];
                })
                ->values()
                ->all();

            $groups[] = [
                'owner'         => $owner,
                'total_issues'  => $totalIssues,
                'critical_pages'=> $criticalPages,
                'warning_pages' => $warningPages,
                'top_issues'    => $topIssues,
            ];
        }

        usort($groups, function ($a, $b) {
            // Sorteer eerst op critical_pages, dan op warning_pages
            $cmp = ($b['critical_pages'] <=> $a['critical_pages']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return $b['warning_pages'] <=> $a['warning_pages'];
        });

        return $groups;
    }

    /**
     * Bouwt een overzicht per pagina op basis van sample URLs.
     */
    protected function buildPageOverview(array $issues): array
    {
        $pages = [];

        foreach ($issues as $issue) {
            $urls     = $this->extractSampleUrlsFromIssue($issue);
            $severity = $issue['severity'] ?? $this->severityLabel($issue['status'] ?? null);
            $category = $issue['category'] ?? 'Overig';

            if (empty($urls)) {
                continue;
            }

            foreach ($urls as $url) {
                if (! isset($pages[$url])) {
                    $pages[$url] = [
                        'url'         => $url,
                        'issues_total'=> 0,
                        'critical'    => 0,
                        'warnings'    => 0,
                        'info'        => 0,
                        'categories'  => [],
                    ];
                }

                $pages[$url]['issues_total']++;

                if ($severity === 'critical') {
                    $pages[$url]['critical']++;
                } elseif ($severity === 'warning') {
                    $pages[$url]['warnings']++;
                } else {
                    $pages[$url]['info']++;
                }

                if (! isset($pages[$url]['categories'][$category])) {
                    $pages[$url]['categories'][$category] = 0;
                }
                $pages[$url]['categories'][$category]++;
            }
        }

        // Top pagina's bepalen
        $pageList = array_values($pages);

        usort($pageList, function ($a, $b) {
            // Eerst op critical, dan warnings, dan totaal
            $cmp = $b['critical'] <=> $a['critical'];
            if ($cmp !== 0) {
                return $cmp;
            }

            $cmp = $b['warnings'] <=> $a['warnings'];
            if ($cmp !== 0) {
                return $cmp;
            }

            return $b['issues_total'] <=> $a['issues_total'];
        });

        $topPages = array_slice($pageList, 0, 10);

        return [
            'pages'     => $pageList,
            'top_pages' => $topPages,
        ];
    }

    /**
     * Haalt sample URLs uit issue of data.
     */
    protected function extractSampleUrlsFromIssue(array $issue): array
    {
        if (isset($issue['sample_urls']) && is_array($issue['sample_urls'])) {
            return $issue['sample_urls'];
        }

        $data = $issue['data'] ?? [];

        if (! is_array($data)) {
            return [];
        }

        $urls = $data['sample_urls'] ?? $data['urls'] ?? null;

        if (is_null($urls)) {
            return [];
        }

        if (! is_array($urls)) {
            $urls = [$urls];
        }

        // Schoonmaken en dubbelingen eruit
        $urls = array_values(array_unique(array_filter($urls)));

        return $urls;
    }
}
