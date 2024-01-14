<?php

namespace Laravel\Jetstream\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AddingAccount
{
    use Dispatchable;

    /**
     * The account owner.
     *
     * @var mixed
     */
    public $owner;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $owner
     * @return void
     */
    public function __construct($owner)
    {
        $this->owner = $owner;
    }
}
