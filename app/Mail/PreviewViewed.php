<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\ProjectPreviewView;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreviewViewed extends Mailable
{
    use Queueable, SerializesModels;

    public Project $project;
    public ProjectPreviewView $viewLog;

    public function __construct(Project $project, ProjectPreviewView $viewLog)
    {
        $this->project = $project->withoutRelations();
        $this->viewLog = $viewLog->withoutRelations();
    }

    public function build()
    {
        $company = $this->project->company ?: 'Onbekend bedrijf';
        $subject = "Er is zojuist een preview bekeken.";

        // Data voor de view
        $previewUrl   = $this->project->preview_url;
        $previewToken = $this->project->preview_token;
        $previewLink  = $previewToken ? route('preview.show', ['token' => $previewToken]) : $previewUrl;

        $dt = optional($this->viewLog->created_at)->timezone('Europe/Amsterdam');
        $when = $dt ? $dt->format('d-m-Y H:i') : '—';

        $location = collect([
            $this->viewLog->city,
            $this->viewLog->region,
            $this->viewLog->country,
        ])->filter()->implode(', ');

        return $this->subject($subject)
            ->view('emails.preview_viewed', [
                'company'     => $company,
                'previewLink' => $previewLink,
                'previewUrl'  => $previewUrl,
                'viewedAt'    => $when,
                'ip'          => $this->viewLog->ip ?: '—',
                'location'    => $location,
                'countryCode' => $this->viewLog->country_code,
            ]);
    }
}
