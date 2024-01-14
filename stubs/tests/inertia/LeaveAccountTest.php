<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_leave_accounts()
    {
        $user = User::factory()->withPersonalAccount()->create();

        $user->currentAccount->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $response = $this->delete('/accounts/'.$user->currentAccount->id.'/members/'.$otherUser->id);

        $this->assertCount(0, $user->currentAccount->fresh()->users);
    }

    public function test_account_owners_cant_leave_their_own_account()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $response = $this->delete('/accounts/'.$user->currentAccount->id.'/members/'.$user->id);

        $response->assertSessionHasErrorsIn('removeAccountMember', ['account']);

        $this->assertNotNull($user->currentAccount->fresh());
    }
}
