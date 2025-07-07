<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Hotel Settings Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Hotel Pricing Settings
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Configure pricing settings that will apply to all hotels in the system
                </p>
            </div>
            <div class="p-6">
                <x-filament-panels::form wire:submit="saveHotelSettings">
                    {{ $this->getForm('hotelForm') }}

                    <x-filament-panels::form.actions :actions="$this->getHotelFormActions()" />
                </x-filament-panels::form>
            </div>
        </div>
        <!-- Package Settings Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Package Settings
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Manage subscription packages that can be used in invoices
                </p>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
