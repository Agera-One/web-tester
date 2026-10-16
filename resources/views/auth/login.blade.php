<x-guest-layout>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 sm:p-8">
        <!-- Main Card Container -->
        <div class="bg-white w-full max-w-5xl rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[650px] p-3 sm:p-4">
            
            <!-- Left Side: Visual -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-[#112C71] via-[#5B58EB] to-[#56E1E9] rounded-3xl p-12 flex-col justify-between relative overflow-hidden">

                
                <!-- Bottom Text -->
                <div class="text-white mt-auto relative z-10">
                    <p class="text-sm font-medium mb-3 opacity-90">Welcome to Web Tester</p>
                    <h2 class="text-4xl font-bold leading-tight pr-4">Automate your E2E testing seamlessly and catch regressions faster</h2>
                </div>
            </div>

            <!-- Right Side: Login Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16">
                <div class="w-full max-w-md">
                    <!-- Form Header -->
                    <div class="mb-10 text-center lg:text-left">
                        <img src="{{ asset('images/seventhsoft-logo.png') }}" alt="Seventhsoft Logo" class="h-10 w-auto mb-6 mx-auto lg:mx-0 object-contain">
                        <h2 class="text-3xl font-extrabold text-gray-900">Log in to your account</h2>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Your email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#5B58EB] focus:ring focus:ring-[#5B58EB] focus:ring-opacity-20 transition-colors outline-none" />
                        </div>

                        <!-- Password -->
                        <div x-data="{ show: false }">
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                    class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 focus:border-[#5B58EB] focus:ring focus:ring-[#5B58EB] focus:ring-opacity-20 transition-colors outline-none" />
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-4 flex items-center text-gray-500 hover:text-[#5B58EB] focus:outline-none transition-colors">
                                    <!-- Eye Open Icon (shows when password is hidden) -->
                                    <svg x-show="!show" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <!-- Eye Off Icon (shows when password is shown) -->
                                    <svg x-show="show" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between pt-1">
                            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#5B58EB] shadow-sm focus:border-[#5B58EB] focus:ring focus:ring-[#5B58EB] focus:ring-opacity-20 cursor-pointer" name="remember">
                                <span class="ml-2 text-sm text-gray-600 select-none">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-semibold text-gray-600 hover:text-[#5B58EB] transition-colors" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-[#5B58EB] hover:bg-[#112C71] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5B58EB] transition-colors">
                                Log in
                            </button>
                        </div>
                        
                        <!-- Sign up link (Optional, standard practice to have) -->
                        @if (Route::has('register'))
                        <div class="text-center mt-6 text-sm text-gray-600">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="font-semibold text-[#5B58EB] hover:text-[#112C71] transition-colors">Sign up</a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
