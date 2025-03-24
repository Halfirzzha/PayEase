<x-filament-panels::page>
    <x-filament::section>
        <form wire:submit.prevent="edit" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit" color="primary">
                Bayar
            </x-filament::button>
        </form>
    </x-filament::section>
</x-filament-panels::page>
