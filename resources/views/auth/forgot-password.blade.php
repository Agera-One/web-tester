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

            <!-- Right Side: Reset Password Form -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16">
                <div class="w-full max-w-md">
                    <!-- Form Header -->
                    <div class="mb-8 text-center lg:text-left">
                        <img src="{{ asset('images/seventhsoft-logo.png') }}" alt="Seventhsoft Logo" class="h-10 w-auto mb-6 mx-auto lg:mx-0 object-contain">
                        <h2 class="text-3xl font-extrabold text-gray-900 mb-3">Reset Password</h2>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                        </p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <!-- Validation Errors -->
                    <x-auth-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Your email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#5B58EB] focus:ring focus:ring-[#5B58EB] focus:ring-opacity-20 transition-colors outline-none" />
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-4">
                            <button type="submit" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-[#5B58EB] hover:bg-[#112C71] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5B58EB] transition-colors">
                                {{ __('Email Password Reset Link') }}
                            </button>
                        </div>
                        
                        <div class="text-center mt-6 text-sm text-gray-600">
                            Remember your password? 
                            <a href="{{ route('login') }}" class="font-semibold text-[#5B58EB] hover:text-[#112C71] transition-colors">Log in</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
