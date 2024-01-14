<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\CreateAccountForm;
use Livewire\Livewire;
use Tests\TestCase;

class CreateAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_can_be_created()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        Livewire::test(CreateAccountForm::class)
                    ->set(['state' => ['name' => 'Test Account']])
                    ->call('createAccount');

        $this->assertCount(2, $user->fresh()->ownedAccounts);
        $this->assertEquals('Test Account', $user->fresh()->ownedAccounts()->latest('id')->first()->name);
    }
}
