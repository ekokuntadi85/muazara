<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')">{{ __('Dashboard') }}</flux:navlist.item>

                <flux:navlist.group :heading="__('Master Data')" expandable :expanded="false">
                    <flux:navlist.item icon="layout-grid" :href="route('products.index')" :current="request()->routeIs('products.index')" wire:navigate>Daftar Produk</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-list" :href="route('categories.index')" :current="request()->routeIs('categories.index')" wire:navigate>Kategori</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document" :href="route('units.index')" :current="request()->routeIs('units.index')" wire:navigate>Satuan</flux:navlist.item>
                    <flux:navlist.item icon="building-office-2" :href="route('suppliers.index')" :current="request()->routeIs('suppliers.*')" wire:navigate>Supplier</flux:navlist.item>
                    <flux:navlist.item icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.*')" wire:navigate>Customer</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Transaksi')" expandable :expanded="false">
                    <flux:navlist.item icon="credit-card" :href="route('purchases.index')" :current="request()->routeIs('purchases.*')" wire:navigate>Daftar Pembelian</flux:navlist.item>
                    <flux:navlist.item icon="currency-dollar" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" wire:navigate>Daftar Penjualan</flux:navlist.item>
                    <flux:navlist.item icon="computer-desktop" :href="route('pos.index')" :current="request()->routeIs('pos.index')" wire:navigate>POS</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" :href="route('accounts-receivable.index')" :current="request()->routeIs('accounts-receivable.index')" wire:navigate>Daftar Invoice Kredit</flux:navlist.item>
                    <flux:navlist.item icon="adjustments-horizontal" :href="route('stock-opname.index')" :current="request()->routeIs('stock-opname.index')" wire:navigate>Stok Opname</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Laporan')" expandable :expanded="false">
                    <flux:navlist.item icon="chart-bar" :href="route('reports.sales')" :current="request()->routeIs('reports.sales')" wire:navigate>Laporan Penjualan</flux:navlist.item>
                    <flux:navlist.item icon="calendar-days" :href="route('reports.expiring-stock')" :current="request()->routeIs('reports.expiring-stock')" wire:navigate>Laporan Stok Kedaluwarsa</flux:navlist.item>
                    <flux:navlist.item icon="arrow-down-circle" :href="route('reports.low-stock')" :current="request()->routeIs('reports.low-stock')" wire:navigate>Laporan Stok Menipis</flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="route('stock-card.index')" :current="request()->routeIs('stock-card.index')" wire:navigate>Kartu Stok</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                {{-- <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item> --}}
            </flux:navlist>

            <!-- Desktop User Menu -->
            @auth
                <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden sticky top-0 z-10 bg-white dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>


        {{ $slot }}

        

        @vite('resources/js/app.js')
        @fluxScripts
        @stack('scripts')
    </body>
</html>