<div x-data="{ open: false, catalogosOpen: false }">
    <!-- Sidebar Desktop -->
    <nav class="w-64 bg-[#003366] text-white min-h-screen flex flex-col shadow-xl hidden sm:flex transition-all duration-300 fixed left-0 top-0">
        <!-- Logo / Header -->
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-xl font-bold tracking-tight">Cooperativa Ambato</h1>
            <p class="text-xs text-blue-200 mt-1">Sistema de Pasajes</p>
        </div>

        <!-- Navigation Items -->
        <div class="flex-1 overflow-y-auto py-6 px-3">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 mb-2 {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4h4" />
                </svg>
                Dashboard
            </a>

            <!-- Admin Panel -->
            @can('manage_buses')
                <a href="{{ route('admin.panel') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 mb-2 {{ request()->routeIs('admin.panel') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Panel Admin
                </a>
            @endcan

            <!-- Catálogos Section -->
            @if (auth()->user()->hasAnyRole('admin', 'oficinista'))
                <div class="mb-4">
                    <button @click="catalogosOpen = !catalogosOpen"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-lg font-medium transition-all duration-200 text-blue-100 hover:bg-blue-700 hover:text-white group">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10l8-4" />
                            </svg>
                            <span>Catálogos</span>
                        </div>
                        <svg :class="catalogosOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>

                    <!-- Sub-items -->
                    <div x-show="catalogosOpen" @click.outside="catalogosOpen = false" x-transition class="mt-2 ml-4 space-y-2 border-l-2 border-blue-600 pl-4">
                        <!-- Buses -->
                        @if (auth()->user()->hasAnyRole('admin', 'oficinista'))
                            <a href="{{ route('catalogos.buses') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('catalogos.buses') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18 8h2a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1h2V7a3 3 0 013-3h4a3 3 0 013 3v1zm-4-3v1h4V5a1 1 0 00-1-1h-2a1 1 0 00-1 1z" />
                                </svg>
                                Buses
                            </a>
                        @endif

                        <!-- Categorías de Bus (Solo Admin) -->
                        @if (auth()->user()->hasRole('admin'))
                            <a href="{{ route('catalogos.categorias-bus') }}"
                                class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('catalogos.categorias-bus') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M9 2a1 1 0 000 2h2V2H9zM7 4a1 1 0 000 2h10V4H7zm-2 4a1 1 0 000 2h14V8H5zm0 4a1 1 0 000 2h14v-2H5zm0 4a1 1 0 000 2h14v-2H5zm0 4a1 1 0 000 2h14v-2H5z" />
                                </svg>
                                Categorías
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer / User Info -->
        <div class="p-6 border-t border-blue-700">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
                    <span class="font-bold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-blue-200 truncate">
                        @forelse (auth()->user()->getRoleNames() as $role)
                            {{ ucfirst($role) }}
                        @empty
                            Sin rol
                        @endforelse
                    </p>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Sidebar Toggle Button (Visible only on mobile) -->
    <div class="sm:hidden fixed bottom-6 right-6 z-50">
        <button @click="open = !open"
            class="w-14 h-14 bg-[#003366] text-white rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition-colors duration-200">
            <svg :class="open ? 'hidden' : 'block'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg :class="open ? 'block' : 'hidden'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0">
        <div x-show="open" @click="open = false" class="sm:hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>
    </transition>

    <!-- Mobile Sidebar Menu -->
    <transition
        enter-active-class="transition ease-out duration-200 transform"
        enter-from-class="translate-x-full"
        enter-to-class="translate-x-0"
        leave-active-class="transition ease-in duration-200 transform"
        leave-from-class="translate-x-0"
        leave-to-class="translate-x-full">
        <nav x-show="open" class="sm:hidden fixed right-0 top-0 h-screen w-64 bg-[#003366] text-white shadow-2xl flex flex-col z-40">
            <!-- Mobile Header -->
            <div class="p-6 border-b border-blue-700 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold tracking-tight">Cooperativa</h1>
                    <p class="text-xs text-blue-200">Ambato</p>
                </div>
                <button @click="open = false" class="p-2 hover:bg-blue-700 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Items -->
            <div class="flex-1 overflow-y-auto py-6 px-3">
                <a href="{{ route('dashboard') }}" @click="open = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 mb-2 {{ request()->routeIs('dashboard') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4h4" />
                    </svg>
                    Dashboard
                </a>

                @can('manage_buses')
                    <a href="{{ route('admin.panel') }}" @click="open = false"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 mb-2 {{ request()->routeIs('admin.panel') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-700 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Panel Admin
                    </a>
                @endcan

                @if (auth()->user()->hasAnyRole('admin', 'oficinista'))
                    <div class="mb-4">
                        <button @click="catalogosOpen = !catalogosOpen"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-lg font-medium transition-all duration-200 text-blue-100 hover:bg-blue-700 hover:text-white">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m0 0l8 4m-8-4v10l8 4m0-10l8 4m-8-4v10l8-4" />
                                </svg>
                                <span>Catálogos</span>
                            </div>
                            <svg :class="catalogosOpen ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </button>

                        <div x-show="catalogosOpen" x-transition class="mt-2 ml-4 space-y-2 border-l-2 border-blue-600 pl-4">
                            @if (auth()->user()->hasAnyRole('admin', 'oficinista'))
                                <a href="{{ route('catalogos.buses') }}" @click="open = false"
                                    class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('catalogos.buses') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M18 8h2a1 1 0 011 1v12a1 1 0 01-1 1H4a1 1 0 01-1-1V9a1 1 0 011-1h2V7a3 3 0 013-3h4a3 3 0 013 3v1zm-4-3v1h4V5a1 1 0 00-1-1h-2a1 1 0 00-1 1z" />
                                    </svg>
                                    Buses
                                </a>
                            @endif

                            @if (auth()->user()->hasRole('admin'))
                                <a href="{{ route('catalogos.categorias-bus') }}" @click="open = false"
                                    class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('catalogos.categorias-bus') ? 'bg-blue-700 text-white' : 'text-blue-100 hover:bg-blue-600 hover:text-white' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 2a1 1 0 000 2h2V2H9zM7 4a1 1 0 000 2h10V4H7zm-2 4a1 1 0 000 2h14V8H5zm0 4a1 1 0 000 2h14v-2H5zm0 4a1 1 0 000 2h14v-2H5zm0 4a1 1 0 000 2h14v-2H5z" />
                                    </svg>
                                    Categorías
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Mobile Footer -->
            <div class="p-6 border-t border-blue-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center">
                        <span class="font-bold text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-200 truncate">
                            @forelse (auth()->user()->getRoleNames() as $role)
                                {{ ucfirst($role) }}
                            @empty
                                Sin rol
                            @endforelse
                        </p>
                    </div>
                </div>
            </div>
        </nav>
    </transition>
</div>
