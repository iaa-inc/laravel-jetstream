<?php

namespace App\Providers;

use App\Actions\Jetstream\AddAccountMember;
use App\Actions\Jetstream\CreateAccount;
use App\Actions\Jetstream\DeleteAccount;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteAccountMember;
use App\Actions\Jetstream\RemoveAccountMember;
use App\Actions\Jetstream\UpdateAccountName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePermissions();

        Jetstream::createAccountsUsing(CreateAccount::class);
        Jetstream::updateAccountNamesUsing(UpdateAccountName::class);
        Jetstream::addAccountMembersUsing(AddAccountMember::class);
        Jetstream::inviteAccountMembersUsing(InviteAccountMember::class);
        Jetstream::removeAccountMembersUsing(RemoveAccountMember::class);
        Jetstream::deleteAccountsUsing(DeleteAccount::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     *
     * @return void
     */
    protected function configurePermissions()
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::role('admin', __('Administrator'), [
            'create',
            'read',
            'update',
            'delete',
        ])->description(__('Administrator users can perform any action.'));

        Jetstream::role('editor', __('Editor'), [
            'read',
            'create',
            'update',
        ])->description(__('Editor users have the ability to read, create, and update.'));
    }
}
