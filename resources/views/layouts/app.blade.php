<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" 
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" 
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Web Tester') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Scripts -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
        
        <!-- Alpine.js (Assuming it's not in app.js yet, but standard Laravel Breeze has it. Adding here just in case, though Breeze bundle typically has it) -->
    </head>
    <body class="font-sans antialiased bg-slate-50 text-slate-800 dark:bg-[#050d21] dark:text-slate-200 transition-colors duration-300" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' }" x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))" :class="{ 'dark': darkMode }">
        <div x-data="{ sidebarExpanded: false }" class="flex h-screen overflow-hidden bg-slate-50 dark:bg-[#050d21]">

            <!-- Sidebar -->
            <aside @mouseenter="sidebarExpanded = true" @mouseleave="sidebarExpanded = false"
                   :class="sidebarExpanded ? 'w-64' : 'w-20'"
                   class="fixed inset-y-0 left-0 z-20 flex flex-col bg-white dark:bg-[#0A1930] shadow-[4px_0_24px_rgba(0,0,0,0.02)] dark:shadow-[4px_0_24px_rgba(0,0,0,0.2)] transition-all duration-300 ease-in-out border-r border-slate-100 dark:border-slate-800/50">
                
                <!-- Logo area -->
                <div class="flex items-center justify-center h-20 border-b border-slate-100 dark:border-slate-800/50">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-[#112C71] via-[#5B58EB] to-[#56E1E9] text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    </div>
                    <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-3 font-bold text-lg tracking-wide bg-clip-text text-transparent bg-gradient-to-r from-[#112C71] to-[#5B58EB] dark:from-[#56E1E9] dark:to-[#5B58EB] whitespace-nowrap" style="display: none;">Web Tester</span>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto overflow-x-hidden no-scrollbar">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') ?? '#' }}" class="flex items-center px-3 py-3 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-[#5B58EB]/10 hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors group">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-4 font-medium whitespace-nowrap" style="display: none;">Dashboard</span>
                    </a>
                    
                    <!-- Katalog Test -->
                    <a href="#" class="flex items-center px-3 py-3 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-[#5B58EB]/10 hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors group">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-4 font-medium whitespace-nowrap" style="display: none;">Katalog Test</span>
                    </a>

                    <!-- Riwayat Run -->
                    <a href="#" class="flex items-center px-3 py-3 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-[#5B58EB]/10 hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors group">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-4 font-medium whitespace-nowrap" style="display: none;">Riwayat Run</span>
                    </a>

                    <!-- Jadwal -->
                    <a href="#" class="flex items-center px-3 py-3 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-[#5B58EB]/10 hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors group">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-4 font-medium whitespace-nowrap" style="display: none;">Jadwal</span>
                    </a>

                    <!-- Environment -->
                    <a href="#" class="flex items-center px-3 py-3 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-[#5B58EB]/10 hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors group">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-4 font-medium whitespace-nowrap" style="display: none;">Environment</span>
                    </a>

                    <!-- Pengguna -->
                    <a href="#" class="flex items-center px-3 py-3 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-[#5B58EB]/10 hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors group">
                        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-4 font-medium whitespace-nowrap" style="display: none;">Pengguna</span>
                    </a>
                </nav>

                <!-- Dark Mode Toggle & Bottom section -->
                <div class="p-4 border-t border-slate-100 dark:border-slate-800/50">
                    <button @click="darkMode = !darkMode" class="flex items-center justify-center w-full p-2 text-slate-500 bg-slate-100 dark:bg-[#112240] dark:text-slate-400 rounded-xl hover:text-[#5B58EB] dark:hover:text-[#56E1E9] transition-colors">
                        <svg x-show="!darkMode" class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="darkMode" class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <span x-show="sidebarExpanded" x-transition.opacity.duration.300ms class="ml-3 font-medium whitespace-nowrap" x-text="darkMode ? 'Light Mode' : 'Dark Mode'" style="display: none;"></span>
                    </button>
                </div>
            </aside>

            <!-- Main Wrapper -->
            <div class="flex flex-col flex-1 min-h-screen bg-slate-50 dark:bg-[#050d21] transition-all duration-300 ease-in-out" :class="sidebarExpanded ? 'ml-64' : 'ml-20'">
                
                <!-- Top Header -->
                <header class="sticky top-0 z-10 bg-slate-50/80 dark:bg-[#050d21]/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800/50 shadow-sm">
                    <div class="flex items-center justify-between px-6 py-4">
                        <div class="flex items-center">
                            {{ $header ?? '' }}
                        </div>
                        
                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ openProfile: false }">
                            <button @click="openProfile = !openProfile" @click.away="openProfile = false" class="flex items-center focus:outline-none rounded-full ring-2 ring-transparent hover:ring-[#5B58EB] transition-all">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'User') }}&background=5B58EB&color=fff" alt="Profile" class="w-10 h-10 rounded-full object-cover shadow-sm">
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="openProfile" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                                 class="absolute right-0 mt-3 w-64 bg-white dark:bg-[#112240] rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700/50 overflow-hidden z-50" style="display: none;">
                                
                                <!-- User Info -->
                                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/50 bg-slate-50 dark:bg-[#0A1930]/50">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">{{ Auth::user()->name ?? 'Administrator' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ Auth::user()->email ?? 'admin@example.com' }}</p>
                                </div>
                                
                                <!-- Menu -->
                                <div class="p-2">
                                    <a href="#" class="flex items-center px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-[#0A1930] hover:text-[#5B58EB] dark:hover:text-[#56E1E9] rounded-xl transition-colors">
                                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        Pengaturan Akun
                                    </a>
                                </div>
                                
                                <div class="p-2 border-t border-slate-100 dark:border-slate-700/50">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors">
                                            <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-6 md:p-8 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
