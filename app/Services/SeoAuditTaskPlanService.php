<?php

namespace App\Services;

use App\Models\SeoAudit;

class SeoAuditTaskPlanService
{
    public function __construct(
        protected SeoAuditInsightsService $insightsService,
    ) {
    }

    /**
     * Entry-point 1:
     * Gebruik deze als je alleen een SeoAudit meegeeft
     * (bijv. vanuit een job of andere plek).
     */
    public function buildPlan(SeoAudit $audit): array
    {
        $insights = $this->insightsService->buildInsights($audit);

        return $this->buildPlanFromInsights($audit, $insights);
    }

    /**
     * Entry-point 2:
     * Sluit aan op jouw controller-signature:
     *
     * generatePlan($seoAudit, $summary, $quickWins, $actions)
     */
    public function generatePlan(
        SeoAudit $audit,
        array $summary = [],
        array $quickWins = [],
        array $actions = []
    ): array {
        $insights = [
            'summary'             => $summary,
            'quick_wins'          => $quickWins,
            'recommended_actions' => $actions,
        ];

        return $this->buildPlanFromInsights($audit, $insights);
    }

    /**
     * Kernlogica: maak een slim takenplan op basis van de insights.
     */
    protected function buildPlanFromInsights(SeoAudit $audit, array $insights): array
    {
        $summary   = $insights['summary']             ?? [];
        $quickWins = $insights['quick_wins']          ?? [];
        $actions   = $insights['recommended_actions'] ?? [];
        $rawIssues = $insights['raw_issues']          ?? [];

        $tasks = [];

        // 1) Taken uit quick wins (meest concrete werk)
        foreach ($quickWins as $win) {
            $tasks[] = $this->taskFromQuickWin($win);
        }

        // 2) Taken uit recommended_actions (hogere-lijn acties per categorie)
        foreach ($actions as $action) {
            $tasks[] = $this->taskFromRecommendedAction($action);
        }

        // 3) Fallback als er weinig/geen quick_wins/actions zijn
        if (empty($tasks) && ! empty($rawIssues)) {
            $fallback = $this->buildFallbackTasksFromIssues($rawIssues);
            $tasks    = array_merge($tasks, $fallback);
        }

        // 4) Duplicaten wegfilteren op basis van titel + source
        $tasks = $this->deduplicateTasks($tasks);

        // 5) Taken sorteren op priority
        usort($tasks, fn ($a, $b) =>
            $this->priorityWeight($a['priority'] ?? 'normal')
            <=> $this->priorityWeight($b['priority'] ?? 'normal')
        );

        // 6) Samenvatting voor klant + interne notities
        $clientSummary     = $this->buildClientSummary($audit, $summary, $tasks);
        $notesForColleague = $this->buildInternalNotes($audit, $summary, $tasks);

        return [
            'generated_at' => now()->toIso8601String(),
            'type'         => $audit->type,
            'domain'       => $audit->domain,
            'score'        => $summary['score'] ?? $audit->overall_score,
            'plan'         => [
                'version'            => 1,
                'focus'              => $audit->type,
                'tasks'              => $tasks,
                'client_summary'     => $clientSummary,
                'notes_for_colleague'=> $notesForColleague,
            ],
        ];
    }

    /**
     * Maak één taak op basis van een quick win issue.
     */
    protected function taskFromQuickWin(array $win): array
    {
        $title    = $win['title'] ?? ($win['name'] ?? ($win['code'] ?? 'Verbeterpunt'));
        $category = $win['category'] ?? 'Techniek';
        $code     = $win['code'] ?? null;
        $pages    = (int) ($win['pages'] ?? ($win['value'] ?? 0));

        $description = $win['description'] ?? '';
        if ($description === '' && ! empty($win['name'])) {
            $description = $win['name'];
        }

        // Priority afleiden
        $priority = $this->derivePriorityFromIssue([
            'severity' => $win['impact'] === 'hoog' ? 'critical' : 'warning',
            'priority' => $win['priority'] ?? null,
            'value'    => $pages,
        ]);

        $estimated = $this->estimateMinutesForIssue([
            'effort' => $win['effort'] ?? null,
            'value'  => $pages,
        ]);

        return [
            'title'               => $title,
            'description'         => $description,
            'category'            => $category,
            'priority'            => $priority,
            'estimated_minutes'   => $estimated,
            'related_issue_codes' => $code ? [$code] : [],
            'source'              => 'technical_audit',
        ];
    }

    /**
     * Maak één taak vanuit een recommended_action blok (per categorie).
     */
    protected function taskFromRecommendedAction(array $action): array
    {
        $title     = $action['title']    ?? 'Verbeteracties voor deze categorie';
        $category  = $action['category'] ?? 'SEO';
        $summary   = $action['summary']  ?? '';
        $steps     = $action['suggested_steps'] ?? [];
        $linked    = $action['linked_issues']   ?? [];

        $description = trim($summary . "\n\n" . $this->stepsToParagraph($steps));

        $codes = [];
        foreach ($linked as $issue) {
            if (! empty($issue['code'])) {
                $codes[] = $issue['code'];
            }
        }
        $codes = array_values(array_unique($codes));

        $priority  = $this->derivePriorityFromAction($action);
        $estimated = $this->estimateMinutesForAction($action);

        return [
            'title'               => $title,
            'description'         => $description,
            'category'            => $category,
            'priority'            => $priority,
            'estimated_minutes'   => $estimated,
            'related_issue_codes' => $codes,
            'source'              => 'technical_audit',
        ];
    }

    /**
     * Eenvoudige fallback als er geen quick_wins / actions zijn.
     */
    protected function buildFallbackTasksFromIssues(array $issues): array
    {
        $collection = collect($issues)
            ->whereIn('severity', ['critical', 'warning'])
            ->sortByDesc('value')
            ->take(5);

        $tasks = [];

        foreach ($collection as $issue) {
            $title       = $issue['name'] ?? ($issue['code'] ?? 'Verbeterpunt');
            $pages       = (int) ($issue['value'] ?? 0);
            $shortDesc   = $this->shortDescriptionFromIssueCode($issue['code'] ?? '');
            $description = $shortDesc;

            if ($pages > 0) {
                $description .= " (dit speelt op ca. {$pages} pagina's).";
            }

            $priority  = $this->derivePriorityFromIssue($issue);
            $estimated = $this->estimateMinutesForIssue($issue);

            $tasks[] = [
                'title'               => $title,
                'description'         => $description,
                'category'            => $issue['category'] ?? 'Overig',
                'priority'            => $priority,
                'estimated_minutes'   => $estimated,
                'related_issue_codes' => ! empty($issue['code']) ? [$issue['code']] : [],
                'source'              => 'technical_audit',
            ];
        }

        return $tasks;
    }

    protected function stepsToParagraph(array $steps): string
    {
        if (empty($steps)) {
            return '';
        }

        $clean = array_values(array_filter(array_map('trim', $steps)));

        if (empty($clean)) {
            return '';
        }

        if (count($clean) === 1) {
            return $clean[0];
        }

        return 'Stappen: ' . implode(' • ', $clean);
    }

    protected function derivePriorityFromIssue(array $issue): string
    {
        if (! empty($issue['priority'])) {
            return (string) $issue['priority'];
        }

        $severity = strtolower($issue['severity'] ?? '');
        $pages    = (int) ($issue['value'] ?? 0);

        if ($severity === 'critical') {
            return $pages <= 50 ? 'must_fix' : 'high';
        }

        if ($severity === 'warning') {
            return 'high';
        }

        return 'normal';
    }

    protected function derivePriorityFromAction(array $action): string
    {
        if (! empty($action['priority'])) {
            return (string) $action['priority'];
        }

        $category = strtolower($action['category'] ?? '');

        if (str_contains($category, 'techniek') || str_contains($category, 'technical')) {
            return 'must_fix';
        }

        if (str_contains($category, 'content') || str_contains($category, 'links')) {
            return 'high';
        }

        return 'normal';
    }

    protected function estimateMinutesForIssue(array $issue): int
    {
        $effort = strtolower($issue['effort'] ?? '');
        $pages  = (int) ($issue['value'] ?? ($issue['pages'] ?? 0));

        $base = match ($effort) {
            'laag', 'low'    => 30,
            'middel', 'medium' => 60,
            'hoog', 'high'   => 120,
            default          => 60,
        };

        $factor = 1.0;
        if ($pages > 0) {
            if ($pages > 25 && $pages <= 50) {
                $factor = 1.5;
            } elseif ($pages > 50 && $pages <= 100) {
                $factor = 2.0;
            } elseif ($pages > 100) {
                $factor = 3.0;
            }
        }

        $minutes = (int) ceil(($base * $factor) / 15) * 15;

        return max(15, $minutes);
    }

    protected function estimateMinutesForAction(array $action): int
    {
        $category   = strtolower($action['category'] ?? '');
        $linked     = $action['linked_issues'] ?? [];
        $issueCount = count($linked);

        $base = 60;

        if (str_contains($category, 'techniek') || str_contains($category, 'technical')) {
            $base = 90;
        } elseif (str_contains($category, 'content')) {
            $base = 60;
        } elseif (str_contains($category, 'links')) {
            $base = 75;
        }

        if ($issueCount > 3) {
            $base += 30;
        }

        return (int) ceil($base / 15) * 15;
    }

    protected function priorityWeight(string $priority): int
    {
        return match (strtolower($priority)) {
            'must_fix' => 1,
            'high'     => 2,
            'normal'   => 3,
            'low'      => 4,
            default    => 3,
        };
    }

    protected function deduplicateTasks(array $tasks): array
    {
        $seen  = [];
        $clean = [];

        foreach ($tasks as $task) {
            $key = mb_strtolower(($task['title'] ?? '') . '|' . ($task['source'] ?? ''));

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $clean[]    = $task;
        }

        return $clean;
    }

    protected function shortDescriptionFromIssueCode(string $code): string
    {
        $code = mb_strtolower($code);

        if (str_contains($code, 'title')) {
            return 'Titels controleren en optimaliseren voor zoekwoorden en klikratio.';
        }

        if (str_contains($code, 'description')) {
            return 'Meta descriptions toevoegen of verbeteren zodat elke pagina een duidelijke omschrijving heeft.';
        }

        if (str_contains($code, 'h1')) {
            return 'Per pagina een duidelijke H1 kop toevoegen of corrigeren.';
        }

        if (str_contains($code, 'alt') || str_contains($code, 'image_no_alt')) {
            return 'Alt-teksten toevoegen aan belangrijke afbeeldingen.';
        }

        if (str_contains($code, 'http4xx') || str_contains($code, '4xx') || str_contains($code, '5xx') || str_contains($code, 'redirect')) {
            return 'Kapotte pagina’s en redirects herstellen zodat alle URL’s goed bereikbaar zijn.';
        }

        if (str_contains($code, 'loading_speed') || str_contains($code, 'speed') || str_contains($code, 'core_web_vitals')) {
            return 'Laadsnelheid verbeteren door afbeeldingen te optimaliseren en caching/compressie in te richten.';
        }

        return 'Los dit probleem op voor de belangrijkste pagina’s.';
    }

    protected function buildClientSummary(SeoAudit $audit, array $summary, array $tasks): string
    {
        $score  = $summary['score'] ?? $audit->overall_score;
        $domain = $audit->domain;

        $errors   = (int) ($summary['errors']   ?? 0);
        $warnings = (int) ($summary['warnings'] ?? 0);
        $pages    = (int) ($summary['pages']    ?? 0);

        $firstTasks = array_slice($tasks, 0, 3);
        $taskTitles = array_map(fn ($t) => $t['title'] ?? 'Taak', $firstTasks);

        $taskListText = empty($taskTitles)
            ? ''
            : 'We starten met: ' . implode(', ', $taskTitles) . '.';

        $scoreText = is_null($score)
            ? "We hebben een technische SEO-check gedaan op {$domain}."
            : "De technische SEO-check voor {$domain} geeft op dit moment een score van ongeveer {$score}%.";

        $detailText = "In totaal zijn er {$errors} kritieke punten en {$warnings} waarschuwingen gevonden over circa {$pages} gescande pagina’s.";

        return trim($scoreText . ' ' . $detailText . ' ' . $taskListText);
    }

    protected function buildInternalNotes(SeoAudit $audit, array $summary, array $tasks): string
    {
        $score    = $summary['score'] ?? $audit->overall_score;
        $errors   = (int) ($summary['errors']   ?? 0);
        $warnings = (int) ($summary['warnings'] ?? 0);
        $notices  = (int) ($summary['notices']  ?? 0);
        $pages    = (int) ($summary['pages']    ?? 0);

        $mustFix = collect($tasks)->where('priority', 'must_fix')->pluck('title')->all();
        $high    = collect($tasks)->where('priority', 'high')->pluck('title')->all();

        $lines = [];

        $lines[] = "Huidige score: {$score}% | Kritieke fouten: {$errors} | Waarschuwingen: {$warnings} | Notices: {$notices} | Gescande pagina’s: {$pages}.";
        $lines[] = '';
        $lines[] = 'Must-fix taken (eerst oppakken): ' . (empty($mustFix) ? '-' : implode(' | ', $mustFix));
        $lines[] = 'High-priority taken (daarna): ' . (empty($high) ? '-' : implode(' | ', $high));
        $lines[] = '';
        $lines[] = 'Let op: taken zijn gebaseerd op de SERanking site-audit. Na uitvoering nieuwe audit inplannen voor effectmeting.';

        return implode("\n", $lines);
    }
}
