<div class="flex bg-white dark:bg-zinc-800">
    <flux:sidebar sticky collapsible class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <div class="block">
                {{-- <flux:sidebar.brand
                    href="/menu-principal"
                    logo="{{asset('img/equisol-logo-1.png')}}"
                    name="Equisol S.A.S"
                /> --}}
            </div>

            

            <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            {{-- <flux:sidebar.item icon="globe-alt" href="{{ route('videogame.levels.first') }}" wire:current="pointer-events-none!">Histórico F.I</flux:sidebar.item> --}}
            {{-- <flux:sidebar.item icon="inbox" badge="12" href="#">Inbox</flux:sidebar.item>
            <flux:sidebar.item icon="document-text" href="#">Documents</flux:sidebar.item>
            <flux:sidebar.item icon="calendar" href="#">Calendar</flux:sidebar.item>

            <flux:sidebar.group expandable icon="star" heading="Favorites" class="grid">
                <flux:sidebar.item href="#">Marketing site</flux:sidebar.item>
                <flux:sidebar.item href="#">Android app</flux:sidebar.item>
                <flux:sidebar.item href="#">Brand guidelines</flux:sidebar.item>
            </flux:sidebar.group> --}}
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item wire:click="logout" icon="arrow-right-start-on-rectangle" class="text-red-500!">
                Salir
            </flux:sidebar.item>
        </flux:sidebar.nav>

       {{-- <flux:dropdown position="top" align="start" class="max-lg:hidden">
            <flux:sidebar.profile name="{{auth()->user()->full_name ?? 'N/A'}}" />

            <flux:menu>
                <flux:menu.radio.group>
                    <flux:menu.radio checked>{{auth()->user()->name ?? 'N/A'}}</flux:menu.radio>
                </flux:menu.radio.group>
                <flux:menu.separator />

                <flux:menu.item icon="arrow-right-start-on-rectangle">Logout</flux:menu.item> 
            </flux:menu>
        </flux:dropdown>  --}}
    </flux:sidebar>
</div>
