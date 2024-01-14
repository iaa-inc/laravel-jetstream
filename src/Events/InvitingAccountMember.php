<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InvitingAccountMember
{
    use Dispatchable;

    /**
     * The account instance.
     *
     * @var mixed
     */
    public $account;

    /**
     * The email address of the invitee.
     *
     * @var mixed
     */
    public $email;

    /**
     * The role of the invitee.
     *
     * @var mixed
     */
    public $role;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $account
     * @param  mixed  $email
     * @param  mixed  $role
     * @return void
     */
    public function __construct($account, $email, $role)
    {
        $this->account = $account;
        $this->email = $email;
        $this->role = $role;
    }
}
