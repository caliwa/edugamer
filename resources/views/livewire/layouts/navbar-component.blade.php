<flux:header class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar class="lg:hidden w-full">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:spacer />
        <flux:dropdown position="top" align="start">
            <flux:profile />
            <flux:menu>
                <flux:menu.radio.group>
                    {{-- <flux:menu.radio>{{auth()->user()->name ?? 'N/A'}}</flux:menu.radio> --}}
                </flux:menu.radio.group>
            </flux:menu>
        </flux:dropdown>
    </flux:navbar>
    <flux:navbar scrollable class="flex justify-end">
        {{-- <flux:navbar.item href="#" current>Dashboard</flux:navbar.item>
        <flux:navbar.item badge="32" href="#">Orders</flux:navbar.item>
        <flux:navbar.item href="#">Catalog</flux:navbar.item>
        <flux:navbar.item href="#">Configuration</flux:navbar.item> --}}
        <flux:tooltip content="Modo pantalla">
            <flux:button x-data x-on:click="$flux.dark = ! $flux.dark" icon="moon" variant="subtle" aria-label="Toggle dark mode" />
        </flux:tooltip>
    </flux:navbar>

</flux:header>
