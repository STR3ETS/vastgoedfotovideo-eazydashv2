<?php

namespace App\Notifications;

use App\Models\AanvraagWebsite;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewAanvraagNotification extends Notification
{
    use Queueable;

    public function __construct(public AanvraagWebsite $aanvraag) {}

    public function via(object $notifiable): array
    {
        return ['database']; // <-- jij leest database notifications uit
    }

    public function toDatabase(object $notifiable): array
    {
        $company = $this->aanvraag->company ?: ('Aanvraag #' . $this->aanvraag->id);

        $url = \Illuminate\Support\Facades\Route::has('support.potentiele-klanten.show')
            ? route('support.potentiele-klanten.show', ['aanvraag' => $this->aanvraag->id])
            : url('/app/potentiele-klanten/' . $this->aanvraag->id);

        return [
            'title' => 'Nieuwe aanvraag binnengekomen',
            'body'  => $company,
            'url'   => $url,
            'aanvraag_id' => $this->aanvraag->id,
        ];
    }
}
