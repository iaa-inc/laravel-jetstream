<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAccountNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_names_can_be_updated()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $response = $this->put('/accounts/'.$user->currentAccount->id, [
            'name' => 'Test Account',
        ]);

        $this->assertCount(1, $user->fresh()->ownedAccounts);
        $this->assertEquals('Test Account', $user->currentAccount->fresh()->name);
    }
}
