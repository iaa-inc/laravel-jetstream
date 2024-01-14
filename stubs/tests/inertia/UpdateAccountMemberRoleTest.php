<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAccountMemberRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_member_roles_can_be_updated()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $user->currentAccount->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $response = $this->put('/accounts/'.$user->currentAccount->id.'/members/'.$otherUser->id, [
            'role' => 'editor',
        ]);

        $this->assertTrue($otherUser->fresh()->hasAccountRole(
            $user->currentAccount->fresh(), 'editor'
        ));
    }

    public function test_only_account_owner_can_update_account_member_roles()
    {
        $user = User::factory()->withPersonalAccount()->create();

        $user->currentAccount->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'admin']
        );

        $this->actingAs($otherUser);

        $response = $this->put('/accounts/'.$user->currentAccount->id.'/members/'.$otherUser->id, [
            'role' => 'editor',
        ]);

        $this->assertTrue($otherUser->fresh()->hasAccountRole(
            $user->currentAccount->fresh(), 'admin'
        ));
    }
}
