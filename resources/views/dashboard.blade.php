<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl tracking-tight text-slate-800 dark:text-slate-100">
            Overview
        </h2>
    </x-slot>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Run Hari Ini -->
        <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
            <!-- Top border gradient accent -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Run Hari Ini</p>
                    <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100">348</h3>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-blue-50 dark:bg-blue-900/20 text-[#5B58EB] dark:text-[#56E1E9]">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-emerald-500 flex items-center font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    12.5%
                </span>
                <span class="text-slate-400 ml-2">dari kemarin</span>
            </div>
        </div>

        <!-- Card 2: Pass Rate -->
        <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Pass Rate</p>
                    <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100">92.4%</h3>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-emerald-500 flex items-center font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    2.1%
                </span>
                <span class="text-slate-400 ml-2">dari minggu lalu</span>
            </div>
        </div>

        <!-- Card 3: Test Gagal -->
        <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Test Gagal</p>
                    <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100">26</h3>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-rose-50 dark:bg-rose-900/20 text-rose-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-rose-500 flex items-center font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                    4.3%
                </span>
                <span class="text-slate-400 ml-2">dari kemarin</span>
            </div>
        </div>

        <!-- Card 4: Total Scenarios -->
        <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60 relative overflow-hidden group">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[#112C71] via-[#5B58EB] to-[#56E1E9] opacity-80 group-hover:opacity-100 transition-opacity"></div>
            
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Total Skenario</p>
                    <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100">1,248</h3>
                </div>
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-purple-50 dark:bg-purple-900/20 text-purple-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-emerald-500 flex items-center font-medium">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    12 Skenario
                </span>
                <span class="text-slate-400 ml-2">baru bulan ini</span>
            </div>
        </div>
    </div>

    <!-- Main Content Area Example -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Statistik Eksekusi</h3>
                <button class="text-sm font-medium text-[#5B58EB] dark:text-[#56E1E9] hover:underline">Lihat Detail</button>
            </div>
            <div class="h-64 flex items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-700/50 rounded-xl text-slate-400 dark:text-slate-500">
                [ Area Chart Visualisasi ]
            </div>
        </div>
        
        <div class="bg-white dark:bg-[#0A1930] rounded-2xl shadow-lg shadow-slate-200/40 dark:shadow-none p-6 border border-slate-100 dark:border-slate-800/60">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-lg text-slate-800 dark:text-slate-100">Status Environment</h3>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-[#112240] rounded-xl">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 mr-3"></div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">Production</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg">Online</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-[#112240] rounded-xl">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-emerald-500 mr-3"></div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">Staging</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-lg">Online</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-[#112240] rounded-xl">
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full bg-rose-500 mr-3 shadow-[0_0_8px_rgba(244,63,94,0.6)]"></div>
                        <span class="font-medium text-slate-700 dark:text-slate-300">Dev</span>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 rounded-lg">Error</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
