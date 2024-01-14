<?php

namespace Laravel\Jetstream;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

trait HasAccounts
{
    /**
     * Determine if the given account is the current account.
     *
     * @param  mixed  $account
     * @return bool
     */
    public function isCurrentAccount($account)
    {
        return $account->id === $this->currentAccount->id;
    }

    /**
     * Get the current account of the user's context.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentAccount()
    {
        if (is_null($this->current_account_id) && $this->id) {
            $this->switchAccount($this->personalAccount());
        }

        return $this->belongsTo(Jetstream::accountModel(), 'current_account_id');
    }

    /**
     * Switch the user's context to the given account.
     *
     * @param  mixed  $account
     * @return bool
     */
    public function switchAccount($account)
    {
        if (! $this->belongsToAccount($account)) {
            return false;
        }

        $this->forceFill([
            'current_account_id' => $account->id,
        ])->save();

        $this->setRelation('currentAccount', $account);

        return true;
    }

    /**
     * Get all of the accounts the user owns or belongs to.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allAccounts()
    {
        return $this->ownedAccounts->merge($this->accounts)->sortBy('name');
    }

    /**
     * Get all of the accounts the user owns.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ownedAccounts()
    {
        return $this->hasMany(Jetstream::accountModel());
    }

    /**
     * Get all of the accounts the user belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function accounts()
    {
        return $this->belongsToMany(Jetstream::accountModel(), Jetstream::membershipModel())
                        ->withPivot('role')
                        ->withTimestamps()
                        ->as('membership');
    }

    /**
     * Get the user's "personal" account.
     *
     * @return \App\Models\Account
     */
    public function personalAccount()
    {
        return $this->ownedAccounts->where('personal_account', true)->first();
    }

    /**
     * Determine if the user owns the given account.
     *
     * @param  mixed  $account
     * @return bool
     */
    public function ownsAccount($account)
    {
        return $this->id == $account->{$this->getForeignKey()};
    }

    /**
     * Determine if the user belongs to the given account.
     *
     * @param  mixed  $account
     * @return bool
     */
    public function belongsToAccount($account)
    {
        return $this->accounts->contains(function ($t) use ($account) {
            return $t->id === $account->id;
        }) || $this->ownsAccount($account);
    }

    /**
     * Get the role that the user has on the account.
     *
     * @param  mixed  $account
     * @return \Laravel\Jetstream\Role
     */
    public function accountRole($account)
    {
        if ($this->ownsAccount($account)) {
            return new OwnerRole;
        }

        if (! $this->belongsToAccount($account)) {
            return;
        }

        return Jetstream::findRole($account->users->where(
            'id', $this->id
        )->first()->membership->role);
    }

    /**
     * Determine if the user has the given role on the given account.
     *
     * @param  mixed  $account
     * @param  string  $role
     * @return bool
     */
    public function hasAccountRole($account, string $role)
    {
        if ($this->ownsAccount($account)) {
            return true;
        }

        return $this->belongsToAccount($account) && optional(Jetstream::findRole($account->users->where(
            'id', $this->id
        )->first()->membership->role))->key === $role;
    }

    /**
     * Get the user's permissions for the given account.
     *
     * @param  mixed  $account
     * @return array
     */
    public function accountPermissions($account)
    {
        if ($this->ownsAccount($account)) {
            return ['*'];
        }

        if (! $this->belongsToAccount($account)) {
            return [];
        }

        return $this->accountRole($account)->permissions;
    }

    /**
     * Determine if the user has the given permission on the given account.
     *
     * @param  mixed  $account
     * @param  string  $permission
     * @return bool
     */
    public function hasAccountPermission($account, string $permission)
    {
        if ($this->ownsAccount($account)) {
            return true;
        }

        if (! $this->belongsToAccount($account)) {
            return false;
        }

        if (in_array(HasApiTokens::class, class_uses_recursive($this)) &&
            ! $this->tokenCan($permission) &&
            $this->currentAccessToken() !== null) {
            return false;
        }

        $permissions = $this->accountPermissions($account);

        return in_array($permission, $permissions) ||
               in_array('*', $permissions) ||
               (Str::endsWith($permission, ':create') && in_array('*:create', $permissions)) ||
               (Str::endsWith($permission, ':update') && in_array('*:update', $permissions));
    }
}
