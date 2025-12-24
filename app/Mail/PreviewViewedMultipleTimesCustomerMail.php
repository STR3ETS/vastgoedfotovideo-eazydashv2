<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreviewViewedMultipleTimesCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project)
    {
        $this->project = $project->withoutRelations();
    }

    public function build()
    {
        $company = $this->project->company ?: 'jouw bedrijf';

        $previewLink = $this->project->preview_token
            ? route('preview.show', ['token' => $this->project->preview_token])
            : ($this->project->preview_url ?: null);

        return $this
            ->subject('Even checken: wil je de preview goedkeuren of feedback geven?')
            ->view('emails.preview_viewed_multiple_times_customer', [
                'company'     => $company,
                'contactName' => $this->project->contact_name ?: null,
                'previewLink' => $previewLink,
            ]);
    }
}
