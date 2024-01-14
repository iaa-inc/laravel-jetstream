<?php

namespace App\Models;

use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\AccountInvitation as JetstreamAccountInvitation;

class AccountInvitation extends JetstreamAccountInvitation
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'role',
    ];

    /**
     * Get the account that the invitation belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Jetstream::accountModel());
    }
}
