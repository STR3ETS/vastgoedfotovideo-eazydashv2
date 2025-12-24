<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreviewApprovedOwnerMail extends Mailable
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
            ->subject('Preview goedgekeurd – ' . $company)
            ->view('emails.preview_approved_owner', [
                'company'       => $company,
                'contactName'   => $this->project->contact_name ?: null,
                'contactEmail'  => $this->project->contact_email ?: null,
                'contactPhone'  => $this->project->contact_phone ?: null,
                'previewLink'   => $previewLink,
                'approvedAt'    => optional($this->project->preview_approved_at)
                                    ? $this->project->preview_approved_at->timezone('Europe/Amsterdam')->format('d-m-Y H:i')
                                    : null,
                'approvedIp'    => $this->project->preview_approved_ip ?: null,
                'projectStatus' => $this->project->status ?: null,
            ]);
    }
}
