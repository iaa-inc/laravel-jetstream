<x-jet-action-section>
    <x-slot name="title">
        {{ __('Delete Account') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Permanently delete this account.') }}
    </x-slot>

    <x-slot name="content">
        <div class="max-w-xl text-sm text-gray-600">
            {{ __('Once a account is deleted, all of its resources and data will be permanently deleted. Before deleting this account, please download any data or information regarding this account that you wish to retain.') }}
        </div>

        <div class="mt-5">
            <x-jet-danger-button wire:click="$toggle('confirmingAccountDeletion')" wire:loading.attr="disabled">
                {{ __('Delete Account') }}
            </x-jet-danger-button>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <x-jet-confirmation-modal wire:model="confirmingAccountDeletion">
            <x-slot name="title">
                {{ __('Delete Account') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Are you sure you want to delete this account? Once a account is deleted, all of its resources and data will be permanently deleted.') }}
            </x-slot>

            <x-slot name="footer">
                <x-jet-secondary-button wire:click="$toggle('confirmingAccountDeletion')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-jet-secondary-button>

                <x-jet-danger-button class="ml-2" wire:click="deleteAccount" wire:loading.attr="disabled">
                    {{ __('Delete Account') }}
                </x-jet-danger-button>
            </x-slot>
        </x-jet-confirmation-modal>
    </x-slot>
</x-jet-action-section>
