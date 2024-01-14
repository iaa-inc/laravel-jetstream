<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\AccountMemberManager;
use Livewire\Livewire;
use Tests\TestCase;

class RemoveAccountMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_members_can_be_removed_from_accounts()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $user->currentAccount->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $component = Livewire::test(AccountMemberManager::class, ['account' => $user->currentAccount])
                        ->set('accountMemberIdBeingRemoved', $otherUser->id)
                        ->call('removeAccountMember');

        $this->assertCount(0, $user->currentAccount->fresh()->users);
    }

    public function test_only_account_owner_can_remove_account_members()
    {
        $user = User::factory()->withPersonalAccount()->create();

        $user->currentAccount->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $component = Livewire::test(AccountMemberManager::class, ['account' => $user->currentAccount])
                        ->set('accountMemberIdBeingRemoved', $user->id)
                        ->call('removeAccountMember')
                        ->assertStatus(403);
    }
}
