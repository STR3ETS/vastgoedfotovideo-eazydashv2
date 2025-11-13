<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Offerte;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferteController extends Controller
{
    /**
     * Vanuit projectkaart:
     * - als er nog geen offerte is: maak een concept op basis van intake + offertegesprek
     * - redirect naar bewerkpagina
     */
    public function generate(Project $project)
    {
        // Bestaande offerte hergebruiken
        $offerte = $project->offerte;

        if (! $offerte) {
            $offerte = new Offerte();
            $offerte->project()->associate($project);
            $offerte->public_uuid = (string) Str::uuid();
            $offerte->status      = 'draft';

            $aanvraag = $project->aanvraag; // jouw relatie

            $offerteTask = $project->tasks()
                ->where('type', 'call_customer')
                ->with('questions')
                ->first();

            $offerteNoteQuestion = optional($offerteTask)->questions->first();
            $offerteNotes        = $offerteNoteQuestion->answer ?? null;

            $offerte->title = 'Offerte voor ' . ($project->company ?: 'project #' . $project->id);

            // Eerste standaard body genereren
            $offerte->body = $this->generateDefaultBody($project, $aanvraag, $offerteNotes);

            // Handige meta-info bewaren voor later
            $offerte->meta = [
                'project_id'      => $project->id,
                'company'         => $project->company,
                'contact_name'    => $project->contact_name,
                'contact_email'   => $project->contact_email,
                'intake'          => $aanvraag ? $aanvraag->toArray() : null,
                'offerte_task_id' => $offerteTask?->id,
                'offerte_notes'   => $offerteNotes,
            ];

            $offerte->save();
        }

        return redirect()->route('support.offertes.edit', $offerte);
    }

    /**
     * Beheerder-edit pagina.
     */
    public function edit(Offerte $offerte)
    {
        $project = $offerte->project;

        return view('support.offertes.edit', compact('offerte', 'project'));
    }

    /**
     * Opslaan van bewerkte offerte.
     */
    public function update(Request $request, Offerte $offerte)
    {
        $data = $request->validate([
            'title'  => ['nullable', 'string', 'max:255'],
            'body'   => ['nullable', 'string'],
            'status' => ['nullable', 'string'], // bv. draft / ready_to_send
        ]);

        $offerte->fill($data);
        $offerte->save();

        return redirect()
            ->route('support.offertes.edit', $offerte)
            ->with('status', 'Offerte opgeslagen.');
    }

    /**
     * Beheer-preview van de offerte.
     */
    public function show(Offerte $offerte)
    {
        $project = $offerte->project;

        return view('support.offertes.show', compact('offerte', 'project'));
    }

    /**
     * Standaard HTML body op basis van intake + offertegesprek.
     * Wordt alleen bij eerste generate gebruikt.
     */
    protected function generateDefaultBody(Project $project, $aanvraag = null, ?string $offerteNotes = null): string
    {
        $company = $project->company ?: 'uw bedrijf';
        $contact = $project->contact_name ?: 'heer/mevrouw';

        $summary = null;
        if ($aanvraag) {
            // Pak wat logische velden als ze bestaan
            $summary = $aanvraag->description
                ?? $aanvraag->goal
                ?? $aanvraag->doel
                ?? null;
        }

        $lines = [];

        $lines[] = '<h1>Offerte website & online zichtbaarheid voor ' . e($company) . '</h1>';
        $lines[] = '<p>Beste ' . e($contact) . ',</p>';
        $lines[] = '<p>Bedankt voor het prettige gesprek. Op basis van het intakegesprek en het offertegesprek hebben wij onderstaand voorstel voor jullie uitgewerkt.</p>';

        if ($summary) {
            $lines[] = '<h2>Samenvatting van jullie vraag</h2>';
            $lines[] = '<p>' . e($summary) . '</p>';
        }

        if ($offerteNotes) {
            $lines[] = '<h2>Belangrijkste punten uit het offertegesprek</h2>';
            $lines[] = '<p>' . nl2br(e($offerteNotes)) . '</p>';
        }

        $lines[] = '<h2>Ons voorstel in het kort</h2>';
        $lines[] = '<ul>';
        $lines[] = '<li>Design & ontwikkeling van een conversiegerichte website.</li>';
        $lines[] = '<li>Technische inrichting (hosting, beveiliging, laadsnelheid).</li>';
        $lines[] = '<li>Basis SEO-inrichting zodat jullie goed gevonden worden in de regio.</li>';
        $lines[] = '</ul>';

        $lines[] = '<h2>Investering & planning</h2>';
        $lines[] = '<p>Hier kun je de investering en de doorlooptijd verder uitschrijven. Pas deze tekst gerust aan naar jullie eigen structuur.</p>';

        $lines[] = '<h2>Volgende stap</h2>';
        $lines[] = '<p>Als jullie akkoord zijn met deze offerte, kunnen we direct starten met de uitwerking en planning.</p>';

        $lines[] = '<p>Met vriendelijke groet,<br>Eazyonline</p>';

        return implode("\n\n", $lines);
    }
}
