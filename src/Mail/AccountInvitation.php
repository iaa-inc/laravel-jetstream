<?php

namespace Laravel\Jetstream\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Laravel\Jetstream\AccountInvitation as AccountInvitationModel;

class AccountInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The account invitation instance.
     *
     * @var \Laravel\Jetstream\AccountInvitation
     */
    public $invitation;

    /**
     * Create a new message instance.
     *
     * @param  \Laravel\Jetstream\AccountInvitation  $invitation
     * @return void
     */
    public function __construct(AccountInvitationModel $invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('jetstream::mail.account-invitation', ['acceptUrl' => URL::signedRoute('account-invitations.accept', [
            'invitation' => $this->invitation,
        ])])->subject(__('Account Invitation'));
    }
}
