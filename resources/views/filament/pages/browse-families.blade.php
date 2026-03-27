<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search families by name..."
                />
            </x-filament::input.wrapper>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($families as $family)
                <x-filament::section>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $family->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $family->slug }}
                            </p>
                            <x-filament::badge :color="match($family->visibility->value) {
                                'public_browsable' => 'success',
                                'connections_only' => 'warning',
                                default => 'gray',
                            }">
                                {{ $family->visibility->label() }}
                            </x-filament::badge>
                        </div>
                    </div>
                </x-filament::section>
            @empty
                <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-8">
                    @if ($search)
                        No families found matching "{{ $search }}".
                    @else
                        No public or connected families to display.
                    @endif
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
