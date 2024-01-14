<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RemovingAccountMember
{
    use Dispatchable;

    /**
     * The account instance.
     *
     * @var mixed
     */
    public $account;

    /**
     * The account member being removed.
     *
     * @var mixed
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $account
     * @param  mixed  $user
     * @return void
     */
    public function __construct($account, $user)
    {
        $this->account = $account;
        $this->user = $user;
    }
}
