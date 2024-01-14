<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Jetstream\Events\AccountCreated;
use Laravel\Jetstream\Events\AccountDeleted;
use Laravel\Jetstream\Events\AccountUpdated;
use Laravel\Jetstream\Account as JetstreamAccount;

class Account extends JetstreamAccount
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'personal_account' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'personal_account',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => AccountCreated::class,
        'updated' => AccountUpdated::class,
        'deleted' => AccountDeleted::class,
    ];
}
