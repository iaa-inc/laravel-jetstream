<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\DeleteAccountForm;
use Livewire\Livewire;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_accounts_can_be_deleted()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $user->ownedAccounts()->save($account = Account::factory()->make([
            'personal_account' => false,
        ]));

        $account->users()->attach(
            $otherUser = User::factory()->create(), ['role' => 'test-role']
        );

        $component = Livewire::test(DeleteAccountForm::class, ['account' => $account->fresh()])
                                ->call('deleteAccount');

        $this->assertNull($account->fresh());
        $this->assertCount(0, $otherUser->fresh()->accounts);
    }

    public function test_personal_accounts_cant_be_deleted()
    {
        $this->actingAs($user = User::factory()->withPersonalAccount()->create());

        $component = Livewire::test(DeleteAccountForm::class, ['account' => $user->currentAccount])
                                ->call('deleteAccount')
                                ->assertHasErrors(['account']);

        $this->assertNotNull($user->currentAccount->fresh());
    }
}
