<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response = $this->delete('/accounts/'.$user->currentAccount->id.'/members/'.$otherUser->id);

        $this->assertCount(0, $user->currentAccount->fresh()->users);
    }

    public function test_only_account_owner_can_remove_account_members()
    {
        $user = User::factory()->withPersonalAccount()->create();

        $user->currentAccount->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $response = $this->delete('/accounts/'.$user->currentAccount->id.'/members/'.$user->id);

        $response->assertStatus(403);
    }
}
