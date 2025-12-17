<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreviewApprovedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Project $project)
    {
        $this->project = $project->withoutRelations();
    }

    public function build()
    {
        $company = $this->project->company ?: 'Onbekend bedrijf';
        $previewLink = $this->project->preview_token
            ? route('preview.show', ['token' => $this->project->preview_token])
            : ($this->project->preview_url ?: null);

        return $this
            ->subject('Bedankt! We bellen je om de preview door te nemen')
            ->view('emails.preview_approved_customer', [
                'company'     => $company,
                'contactName' => $this->project->contact_name ?: null,
                'previewLink' => $previewLink,
            ]);
    }
}
