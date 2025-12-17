<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreviewReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $company,
        public ?string $contactName,
        public string $klantUrl,
        public ?string $previewUrl = null,
    ) {}

    public function build()
    {
        return $this
            ->subject('Je preview van Eazyonline staat klaar')
            ->view('emails.preview-ready', [
                'company'      => $this->company,
                'contactName'  => $this->contactName,
                'klantUrl'     => $this->klantUrl,
                'previewUrl'   => $this->previewUrl,
            ]);
    }
}
