<?php

namespace App\Mail;

use App\Models\AanvraagWebsite;
use App\Models\AanvraagComment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AanvraagMentionedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AanvraagWebsite $aanvraag,
        public AanvraagComment $comment,
        public User $actor
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Je bent getaggd in een aanvraag'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aanvraag.mentioned',
            with: [
                'actorName'   => $this->actor->name ?? 'Onbekend',
                'company'     => $this->aanvraag->company ?? null,
                'aanvraagId'  => $this->aanvraag->id,
                'commentAt'   => optional($this->comment->created_at)->format('d-m-Y H:i'),
                'commentBody' => $this->comment->body ?? '',
                'aanvraagLink'=> route('support.potentiele-klanten.show', ['aanvraag' => $this->aanvraag->id]),
            ]
        );
    }
}
