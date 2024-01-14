<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Settings') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @livewire('accounts.update-account-name-form', ['account' => $account])

            @livewire('accounts.account-member-manager', ['account' => $account])

            @if (Gate::check('delete', $account) && ! $account->personal_account)
                <x-jet-section-border />

                <div class="mt-10 sm:mt-0">
                    @livewire('accounts.delete-account-form', ['account' => $account])
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
