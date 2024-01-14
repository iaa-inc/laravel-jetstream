<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AccountEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The account instance.
     *
     * @var \App\Models\Account
     */
    public $account;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function __construct($account)
    {
        $this->account = $account;
    }
}
