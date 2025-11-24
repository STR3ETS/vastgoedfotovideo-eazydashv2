<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class OfferteKlantMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $company;
    public ?string $contactName;
    public string $klantUrl;
    public ?Carbon $vervalDatum;

    public function __construct(
        string $company,
        ?string $contactName,
        string $klantUrl,
        ?Carbon $vervalDatum = null
    ) {
        $this->company     = $company;
        $this->contactName = $contactName;
        $this->klantUrl    = $klantUrl;
        $this->vervalDatum = $vervalDatum;
    }

    public function build()
    {
        return $this
            ->subject('Je offerte van Eazyonline staat klaar')
            ->view('emails.offerte.klant');
    }
}
