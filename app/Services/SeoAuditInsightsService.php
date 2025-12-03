<?php

namespace App\Services;

use App\Models\SeoAudit;
use App\Models\SeoAuditResult;

class SeoAuditInsightsService
{
    /**
     * Wordt aangeroepen vanuit RunSeoAuditJob na het ophalen van het SERanking rapport.
     *
     * Doel:
     * - Rapport plat slaan naar een uniforme lijst van issues
     * - Issues verrijken met categorie, severity, impact, effort, priority
     * - Issues opslaan in seo_audit_results (historiek / detail)
     * - Modules + slimme takenstructuur in $audit->meta bewaren:
     *   - meta['modules']['tech'] => summary + report
     *   - meta['tasks']          => takenlijst over alle modules
     *   - meta['insights']       => samenvatting + issue-lijsten voor de UI
     */
    public function storeResultsFromReport(SeoAudit $audit, array $report): void
    {
        $meta = $audit->meta ?? [];

        // 1) Issues normaliseren en verrijken
        $rawIssues = $this->normalizeIssuesFromReport($report);

        $enriched = collect($rawIssues)->map(function (array $issue) {
            $issue['category'] = $this->categorizeIssue($issue);
            $issue['severity'] = $this->severityLabel($issue['status'] ?? null);
            $issue['impact']   = $this->impactForIssue($issue);
            $issue['effort']   = $this->effortForIssue($issue);
            $issue['priority'] = $this->priorityForIssue($issue);

            return $issue;
        });

        // 2) Bestaande results verwijderen en nieuwe seo_audit_results records aanmaken
        $audit->results()->delete();

        foreach ($enriched as $issue) {
            $data = $issue['data'] ?? [];

            // Sample URLs uit de data halen als die er zijn
            $sampleUrls = null;
            if (is_array($data)) {
                $sampleUrls = $data['sample_urls'] ?? $data['urls'] ?? null;
                if (!is_null($sampleUrls) && !is_array($sampleUrls)) {
                    $sampleUrls = [$sampleUrls];
                }
            }

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
                'owner'          => null, // niet meer in gebruik, maar kolom bestaat nog
                'priority'       => $issue['priority'] ?? null,
                'data'           => $data,
            ]);
        }

        // 3) Basis summary uit het rapport
        $summary = [
            'score'    => data_get($report, 'score_percent', $audit->overall_score),
            'pages'    => data_get($report, 'total_pages'),
            'errors'   => data_get($report, 'total_errors'),
            'warnings' => data_get($report, 'total_warnings'),
            'notices'  => data_get($report, 'total_notices'),
            'passed'   => data_get($report, 'total_passed'),
        ];

        // 4) Modules-structuur (nu alleen tech-module, later uit te breiden met keywords/backlinks)
        $techModule = [
            'enabled'      => true,
            'status'       => 'completed',
            'score'        => (int) ($summary['score'] ?? 0),
            'summary'      => $summary,
            'report'       => $report,
            'domain_props' => data_get($report, 'domain_props', []),
        ];

        $meta['modules']['tech'] = $techModule;

        // 5) Slimme takenlijst genereren vanuit issues
        $tasks = $this->buildTasksFromIssues($enriched->all());
        $meta['tasks'] = $tasks;

        // 6) Quick wins en aanbevolen acties (voor UI)
        $quickWins = $this->buildQuickWins($enriched->all());
        $actions   = $this->buildRecommendedActions($enriched->all());

        $meta['insights'] = [
            'summary'             => $summary,
            'issues'              => $enriched->values()->all(),
            'quick_wins'          => $quickWins,
            'recommended_actions' => $actions,
        ];

        $audit->meta = $meta;
        $audit->save();
    }

    /**
     * Bouwt een insights-object voor de detailpagina.
     * Wordt gebruikt in SeoAuditController@show.
     */
    public function buildInsights(SeoAudit $audit): array
    {
        $meta = $audit->meta ?? [];

        // Rapport ophalen uit nieuwe modules-structuur, fallback naar oude seranking.meta
        $report = data_get($meta, 'modules.tech.report', data_get($meta, 'seranking.report', []));

        // Issues uit meta als ze zijn opgeslagen, anders opnieuw uit report halen
        $issues = data_get($meta, 'insights.issues');
        if (!is_array($issues)) {
            $issues = $this->normalizeIssuesFromReport(is_array($report) ? $report : []);
            $issues = collect($issues)->map(function (array $issue) {
                $issue['category'] = $this->categorizeIssue($issue);
                $issue['severity'] = $this->severityLabel($issue['status'] ?? null);
                $issue['impact']   = $this->impactForIssue($issue);
                $issue['effort']   = $this->effortForIssue($issue);
                $issue['priority'] = $this->priorityForIssue($issue);
                return $issue;
            })->values()->all();
        }

        // Summary uit meta of opnieuw berekenen
        $summary = data_get($meta, 'insights.summary', [
            'score'    => data_get($report, 'score_percent', $audit->overall_score),
            'pages'    => data_get($report, 'total_pages'),
            'errors'   => data_get($report, 'total_errors'),
            'warnings' => data_get($report, 'total_warnings'),
            'notices'  => data_get($report, 'total_notices'),
            'passed'   => data_get($report, 'total_passed'),
        ]);

        $collection = collect($issues);

        // Extra tellingen
        $summary['critical_issues'] = $collection->where('severity', 'critical')->sum('value');
        $summary['warning_issues']  = $collection->where('severity', 'warning')->sum('value');

        // Groepen per categorie (voor de "issue_groups" UI)
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

        // Quick wins en acties uit meta of opnieuw genereren
        $quickWins = data_get($meta, 'insights.quick_wins');
        if (!is_array($quickWins)) {
            $quickWins = $this->buildQuickWins($collection->all());
        }

        $actions = data_get($meta, 'insights.recommended_actions');
        if (!is_array($actions)) {
            $actions = $this->buildRecommendedActions($collection->all());
        }

        // Taken uit meta (nieuwe structuur)
        $tasks = data_get($meta, 'tasks', []);

        return [
            'summary'             => $summary,
            'issue_groups'        => $groups,
            'quick_wins'          => $quickWins,
            'recommended_actions' => $actions,
            'raw_issues'          => $collection->all(),
            'raw_report'          => $report,
            'tasks'               => $tasks,
        ];
    }

    /**
     * Slaat SERanking report plat naar een standaard issues-lijst.
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
                    'status'       => $check['status'] ?? null, // error / warning / notice / ok
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
            str_contains($section, 'security') ||
            str_contains($section, 'mobile') ||
            str_contains($section, 'crawling') ||
            str_contains($section, 'index')
        ) {
            return 'Techniek';
        }
        if (
            str_contains($code, 'core_web_vitals') ||
            str_contains($code, 'page_speed') ||
            str_contains($code, 'http') ||
            str_contains($code, 'robots') ||
            str_contains($code, 'sitemap')
        ) {
            return 'Techniek';
        }

        // Content
        if (
            str_contains($section, 'content') ||
            str_contains($section, 'meta') ||
            str_contains($section, 'title') ||
            str_contains($section, 'metatags') ||
            str_contains($section, 'headings')
        ) {
            return 'Content';
        }
        if (
            str_contains($code, 'meta_') ||
            str_contains($code, 'title') ||
            str_contains($code, 'description') ||
            str_contains($code, 'h1') ||
            str_contains($code, 'image_') ||
            str_contains($code, 'image_no_alt')
        ) {
            return 'Content';
        }

        // Links
        if (str_contains($section, 'links') || str_contains($section, 'backlink')) {
            return 'Links';
        }
        if (
            str_contains($code, 'backlink') ||
            str_contains($code, 'anchor') ||
            str_contains($code, 'inlinks') ||
            str_contains($code, 'extlinks') ||
            str_contains($code, 'links')
        ) {
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
            str_contains($code, 'chrome_ux_') ||
            str_contains($code, 'lighthouse_') ||
            str_contains($code, 'core_web_vitals')
        ) {
            return 'hoog';
        }

        return 'middel';
    }

    /**
     * Basis prioriteit voor een issue.
     */
    protected function priorityForIssue(array $issue): string
    {
        $severity = $issue['severity'] ?? $this->severityLabel($issue['status'] ?? null);
        $pages    = (int) ($issue['value'] ?? 0);

        if ($severity === 'critical' && $pages <= 50 && $pages > 0) {
            return 'quick_win';
        }

        if ($severity === 'critical' && $pages > 50) {
            return 'must_fix';
        }

        if ($severity === 'warning' && $pages > 0) {
            return 'high';
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
                if (!in_array($i['severity'], ['critical', 'warning'], true)) {
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
                'impact'      => $issue['impact'] ?? ($issue['severity'] === 'critical' ? 'hoog' : 'middel'),
                'effort'      => $issue['effort'] ?? 'laag',
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
                'effort'          => 'middel',
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

        if (str_contains($code, 'meta_description') || str_contains($code, 'description')) {
            return 'Meta descriptions toevoegen of verbeteren zodat elke pagina een duidelijke omschrijving heeft';
        }

        if (str_contains($code, 'h1')) {
            return 'Per pagina een duidelijke H1 kop toevoegen of corrigeren';
        }

        if (str_contains($code, 'image_alt') || str_contains($code, 'image_no_alt')) {
            return 'Alt teksten toevoegen aan belangrijke afbeeldingen';
        }

        if (str_contains($code, 'images4xx') || str_contains($code, 'images5xx')) {
            return 'Kapotte afbeeldingen herstellen zodat alle visuals goed laden';
        }

        if (
            str_contains($code, 'redirect') ||
            str_contains($code, '4xx') ||
            str_contains($code, '5xx') ||
            str_contains($code, 'http4xx') ||
            str_contains($code, 'http5xx')
        ) {
            return 'Kapotte pagina’s en redirects herstellen zodat alle URL’s goed bereikbaar zijn';
        }

        if (
            str_contains($code, 'page_speed') ||
            str_contains($code, 'chrome_ux_') ||
            str_contains($code, 'lighthouse_') ||
            str_contains($code, 'loading_speed')
        ) {
            return 'Laadsnelheid verbeteren door afbeeldingen en code te optimaliseren en caching in te richten';
        }

        if (str_contains($code, 'no_inlinks') || str_contains($code, 'less_inlink')) {
            return 'Interne linkstructuur verbeteren zodat belangrijke pagina’s meer interne links krijgen';
        }

        if (str_contains($code, 'sitemap')) {
            return 'XML sitemap nalopen, fouten herstellen en zorgen dat alleen juiste URL’s opgenomen zijn';
        }

        if (str_contains($code, 'robots')) {
            return 'Robots.txt controleren en corrigeren zodat zoekmachines de juiste pagina’s kunnen crawlen';
        }

        return 'Los dit probleem op voor de belangrijkste pagina’s';
    }

    protected function actionTitleForCategory(string $category): string
    {
        return match ($category) {
            'Techniek' => 'Technische basis van de website op orde brengen',
            'Content'  => 'Content en metadata optimaliseren',
            'Links'    => 'Autoriteit en interne links verbeteren',
            'UX'       => 'Gebruikerservaring en mobiel vriendelijk verbeteren',
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
                'Herstel kapotte interne en externe links.',
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
     * Bouwt een generieke takenlijst over alle issues.
     * We groeperen per code zodat je 1 taak krijgt voor bijvoorbeeld alle 4xx pagina’s.
     */
    protected function buildTasksFromIssues(array $issues): array
    {
        $collection = collect($issues);

        // Alleen issues die daadwerkelijk iets te fixen hebben
        $filtered = $collection->filter(function (array $issue) {
            $severity = $issue['severity'] ?? $this->severityLabel($issue['status'] ?? null);
            $pages    = (int) ($issue['value'] ?? 0);

            if ($pages <= 0) {
                return false;
            }

            return in_array($severity, ['critical', 'warning'], true);
        });

        $groupedByCode = $filtered->groupBy(function (array $issue) {
            return (string) ($issue['code'] ?? $issue['name'] ?? 'unknown');
        });

        $tasks = [];
        $i     = 1;

        foreach ($groupedByCode as $code => $items) {
            /** @var array $first */
            $first = $items->first();

            $totalPages = (int) $items->sum('value');
            $severity   = $first['severity'] ?? $this->severityLabel($first['status'] ?? null);
            $category   = $first['category'] ?? $this->categorizeIssue($first);
            $impact     = $this->impactForIssue($first);
            $effort     = $this->effortForIssue($first);
            $priority   = $this->priorityForIssue([
                'severity' => $severity,
                'value'    => $totalPages,
            ]);

            $title       = $this->taskTitleForCode($code, $first);
            $description = $this->taskDescriptionForCode($code, $first, $totalPages);

            $tasks[] = [
                'id'            => 'task_' . $code . '_' . $i,
                'title'         => $title,
                'description'   => $description,
                'source_module' => 'tech',
                'category'      => $category,
                'related_codes' => [$code],
                'related_urls'  => [], // later kun je hier sample URLs aan koppelen
                'related_keywords' => [],
                'priority'      => $priority,
                'impact'        => $impact,
                'effort'        => $effort,
                'status'        => 'open',
                'estimated_min' => $this->estimateMinutesForCode($code, $effort, $totalPages),
                'pages_affected'=> $totalPages,
            ];

            $i++;
        }

        // sorteer taken grofweg op prioriteit en impact
        $priorityOrder = [
            'must_fix'  => 1,
            'quick_win' => 2,
            'high'      => 3,
            'normal'    => 4,
            'low'       => 5,
        ];

        usort($tasks, function (array $a, array $b) use ($priorityOrder) {
            $pa = $priorityOrder[$a['priority']] ?? 99;
            $pb = $priorityOrder[$b['priority']] ?? 99;

            if ($pa === $pb) {
                // bij gelijke prioriteit: meeste pagina’s eerst
                return ($b['pages_affected'] ?? 0) <=> ($a['pages_affected'] ?? 0);
            }

            return $pa <=> $pb;
        });

        return $tasks;
    }

    protected function taskTitleForCode(string $code, array $issue): string
    {
        $codeL = mb_strtolower($code);

        if (str_contains($codeL, 'http4xx') || str_contains($codeL, 'http5xx')) {
            return 'Herstel pagina’s met 4xx en 5xx statuscodes';
        }

        if (str_contains($codeL, 'images4xx') || str_contains($codeL, 'images5xx')) {
            return 'Herstel kapotte afbeeldingen';
        }

        if (
            str_contains($codeL, 'meta_title') ||
            str_contains($codeL, 'title_long') ||
            str_contains($codeL, 'title_short') ||
            str_contains($codeL, 'title_missing') ||
            str_contains($codeL, 'title_duplicate')
        ) {
            return 'Titels (title tags) optimaliseren';
        }

        if (
            str_contains($codeL, 'description_') ||
            str_contains($codeL, 'meta_description')
        ) {
            return 'Meta descriptions toevoegen en verbeteren';
        }

        if (
            str_contains($codeL, 'h1_') ||
            str_contains($codeL, 'h1')
        ) {
            return 'H1 koppen structureren en corrigeren';
        }

        if (
            str_contains($codeL, 'image_no_alt') ||
            str_contains($codeL, 'image_alt')
        ) {
            return 'Alt teksten voor afbeeldingen toevoegen';
        }

        if (
            str_contains($codeL, 'loading_speed') ||
            str_contains($codeL, 'page_speed') ||
            str_contains($codeL, 'chrome_ux_') ||
            str_contains($codeL, 'lighthouse_')
        ) {
            return 'Laadsnelheid en performance verbeteren';
        }

        if (
            str_contains($codeL, 'sitemap') ||
            str_contains($codeL, 'robots')
        ) {
            return 'Sitemap en robots.txt controleren en fixen';
        }

        if (str_contains($codeL, 'no_inlinks') || str_contains($codeL, 'less_inlink')) {
            return 'Interne linkstructuur naar belangrijke pagina’s verbeteren';
        }

        return $issue['name'] ?? ($code ?: 'SEO taak');
    }

    protected function taskDescriptionForCode(string $code, array $issue, int $pages): string
    {
        $base = $this->shortDescriptionForIssue($issue);
        if ($pages > 0) {
            $base .= " (ca. {$pages} pagina’s getroffen)";
        }

        return $base;
    }

    protected function estimateMinutesForCode(string $code, string $effort, int $pages): int
    {
        $codeL = mb_strtolower($code);

        // basis op effort
        $base = match ($effort) {
            'laag'   => 30,
            'middel' => 60,
            'hoog'   => 120,
            default  => 45,
        };

        // kleine correcties per type issue
        if (
            str_contains($codeL, 'meta_title') ||
            str_contains($codeL, 'title_') ||
            str_contains($codeL, 'description_') ||
            str_contains($codeL, 'h1_')
        ) {
            // content-issues zijn vaak sneller als het om weinig pagina’s gaat
            if ($pages <= 5) {
                return 30;
            }
            if ($pages <= 20) {
                return 60;
            }
        }

        if (str_contains($codeL, 'http4xx') || str_contains($codeL, 'http5xx')) {
            if ($pages <= 5) {
                return 45;
            }
        }

        if (
            str_contains($codeL, 'loading_speed') ||
            str_contains($codeL, 'page_speed') ||
            str_contains($codeL, 'chrome_ux_') ||
            str_contains($codeL, 'lighthouse_')
        ) {
            // performance-taken zijn meestal wat zwaarder
            return max($base, 90);
        }

        return $base;
    }
}
