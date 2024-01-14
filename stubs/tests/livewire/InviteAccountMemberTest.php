<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Jetstream\Http\Livewire\AccountMemberManager;
use Laravel\Jetstream\Mail\AccountInvitation;
use Livewire\Livewire;
use Tests\TestCase;

class InviteAccountMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_members_can_be_invited_to_account()
    {
        Mail::fake();

        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $component = Livewire::test(AccountMemberManager::class, ['account' => $user->currentAccount])
                        ->set('addAccountMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addAccountMember');

        Mail::assertSent(AccountInvitation::class);

        $this->assertCount(1, $user->currentAccount->fresh()->accountInvitations);
    }

    public function test_account_member_invitations_can_be_cancelled()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        // Add the account member...
        $component = Livewire::test(AccountMemberManager::class, ['account' => $user->currentAccount])
                        ->set('addAccountMemberForm', [
                            'email' => 'test@example.com',
                            'role' => 'admin',
                        ])->call('addAccountMember');

        $invitationId = $user->currentAccount->fresh()->accountInvitations->first()->id;

        // Cancel the account invitation...
        $component->call('cancelAccountInvitation', $invitationId);

        $this->assertCount(0, $user->currentAccount->fresh()->accountInvitations);
    }
}
