<?php

namespace App\Mail;

use App\Models\AanvraagWebsite;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AanvraagReceivedCustomerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AanvraagWebsite $aanvraag,
    ) {}

    public function build()
    {
        $createdAt = optional($this->aanvraag->created_at)
            ? $this->aanvraag->created_at->timezone('Europe/Amsterdam')
            : now('Europe/Amsterdam');

        // simpele logica: vrijdag/za/zo -> maandag oppakken, anders binnen 24 werkuren
        $day = (int) $createdAt->dayOfWeekIso; // 1=ma ... 5=vr ... 7=zo
        $pickupText = ($day >= 5) // vr/za/zo
            ? 'Als je de aanvraag op vrijdag (of in het weekend) doet, pakken we dit maandag weer op.'
            : 'Binnen 24 uur op een werkdag neemt een medewerker contact met je op.';

        $choiceLabel = $this->aanvraag->choice === 'renew'
            ? 'Website vernieuwen'
            : 'Nieuwe website';

        return $this
            ->subject('We hebben je aanvraag ontvangen ✅')
            ->view('emails.aanvraag.aanvraag-received-customer')
            ->with([
                'aanvraag'     => $this->aanvraag,
                'company'      => $this->aanvraag->company,
                'contactName'  => $this->aanvraag->contactName,
                'createdAt'    => $createdAt->format('d-m-Y H:i'),
                'pickupText'   => $pickupText,
                'choiceLabel'  => $choiceLabel,
            ]);
    }
}
