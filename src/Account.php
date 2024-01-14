<?php

namespace Laravel\Jetstream;

use Illuminate\Database\Eloquent\Model;

abstract class Account extends Model
{
    /**
     * Get the owner of the account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(Jetstream::userModel(), 'user_id');
    }

    /**
     * Get all of the account's users including its owner.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allUsers()
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Get all of the users that belong to the account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Jetstream::userModel(), Jetstream::membershipModel())
                        ->withPivot('role')
                        ->withTimestamps()
                        ->as('membership');
    }

    /**
     * Determine if the given user belongs to the account.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function hasUser($user)
    {
        return $this->users->contains($user) || $user->ownsAccount($this);
    }

    /**
     * Determine if the given email address belongs to a user on the account.
     *
     * @param  string  $email
     * @return bool
     */
    public function hasUserWithEmail(string $email)
    {
        return $this->allUsers()->contains(function ($user) use ($email) {
            return $user->email === $email;
        });
    }

    /**
     * Determine if the given user has the given permission on the account.
     *
     * @param  \App\Models\User  $user
     * @param  string  $permission
     * @return bool
     */
    public function userHasPermission($user, $permission)
    {
        return $user->hasAccountPermission($this, $permission);
    }

    /**
     * Get all of the pending user invitations for the account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountInvitations()
    {
        return $this->hasMany(Jetstream::accountInvitationModel());
    }

    /**
     * Remove the given user from the account.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function removeUser($user)
    {
        if ($user->current_account_id === $this->id) {
            $user->forceFill([
                'current_account_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all of the account's resources.
     *
     * @return void
     */
    public function purge()
    {
        $this->owner()->where('current_account_id', $this->id)
                ->update(['current_account_id' => null]);

        $this->users()->where('current_account_id', $this->id)
                ->update(['current_account_id' => null]);

        $this->users()->detach();

        $this->delete();
    }
}
