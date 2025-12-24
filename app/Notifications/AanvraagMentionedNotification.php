<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AanvraagMentionedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $aanvraagId,
        public string $aanvraagCompany,
        public int $commentId,
        public int $actorId,
        public string $actorName,
        public string $body
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $url = route('support.potentiele-klanten.show', ['aanvraag' => $this->aanvraagId]);

        return [
            'type'            => 'mention',
            'title'           => $this->actorName . ' heeft je getaggd in een aanvraag',
            'body'            => Str::limit(trim($this->body), 120),
            'aanvraag_id'     => $this->aanvraagId,
            'aanvraag_company'=> $this->aanvraagCompany,
            'comment_id'      => $this->commentId,
            'actor_id'        => $this->actorId,
            'actor_name'      => $this->actorName,
            'url'             => $url,
        ];
    }
}
