<?php

namespace App\Mail;

use App\Models\TeamInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public TeamInvite $invite, public string $acceptUrl) {}

    public function build()
    {
        return $this
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('instellingen.invite.email.subject', [
                'company' => $this->invite->company->name,
            ]))
            ->view('emails.team_invite')
            ->with([
                'invite'    => $this->invite,
                'acceptUrl' => $this->acceptUrl,
            ]);
    }
}
