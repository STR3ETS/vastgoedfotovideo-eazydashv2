<?php

namespace App\Mail;

use App\Models\AanvraagWebsite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewAanvraagMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AanvraagWebsite $aanvraag, public string $aanvraagLink) {}

    public function build()
    {
        $company = $this->aanvraag->company ?: ('Aanvraag #' . $this->aanvraag->id);

        return $this
            ->subject('Nieuwe aanvraag: ' . $company)
            ->view('emails.aanvraag.new-aanvraag')
            ->with([
                'aanvraag'      => $this->aanvraag,
                'aanvraagId'    => $this->aanvraag->id,
                'company'       => $this->aanvraag->company,
                'aanvraagLink'  => $this->aanvraagLink,
                'createdAt'     => optional($this->aanvraag->created_at)->timezone('Europe/Amsterdam')->format('d-m-Y H:i'),
            ]);
    }
}
