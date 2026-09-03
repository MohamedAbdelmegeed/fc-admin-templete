<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Future Code Admin') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Fallback Tailwind CSS via CDN if Vite isn't built yet -->
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Outfit', sans-serif;
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .gradient-text {
            background: linear-gradient(135deg, #14b8a6 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .animated-blob {
            animation: blob 7s infinite alternate;
        }
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-[#0b0c10] text-gray-900 dark:text-gray-100 antialiased selection:bg-teal-500 selection:text-white flex flex-col min-h-screen relative overflow-x-hidden">

    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-teal-500/20 blur-[100px] animated-blob"></div>
        <div class="absolute bottom-[-10%] right-[-5%] w-[50%] h-[50%] rounded-full bg-blue-600/20 blur-[120px] animated-blob" style="animation-delay: 2s;"></div>
    </div>

    <!-- Navigation -->
    <header class="w-full relative z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 border-b border-gray-200 dark:border-white/10">
                <div class="flex-shrink-0 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-teal-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg">
                        FC
                    </div>
                    <span class="font-bold text-xl tracking-tight">FutureCode</span>
                </div>
                
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-teal-500 transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium hover:text-teal-500 transition-colors">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm font-medium px-4 py-2 rounded-full bg-gray-900 text-white dark:bg-white dark:text-gray-900 hover:scale-105 transition-transform shadow-lg shadow-gray-900/20 dark:shadow-white/10">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center justify-center relative z-10 w-full">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-20 lg:py-32 flex flex-col items-center text-center">
            
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/20 text-teal-600 dark:text-teal-400 text-xs font-semibold uppercase tracking-wider mb-8">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-teal-500"></span>
                </span>
                v1.0 Production Ready
            </div>

            <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight mb-6 max-w-4xl">
                The Ultimate <br class="hidden lg:block"/>
                <span class="gradient-text">Admin Panel</span> Experience
            </h1>
            
            <p class="text-lg lg:text-xl text-gray-600 dark:text-gray-400 max-w-2xl mb-10 leading-relaxed">
                A highly advanced, multi-tenant administrative dashboard built on Laravel. Experience seamless management, unparalleled performance, and cutting-edge security.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="px-8 py-4 rounded-full bg-gradient-to-r from-teal-500 to-blue-600 text-white font-semibold shadow-xl shadow-teal-500/30 hover:shadow-teal-500/50 hover:scale-105 transition-all w-full sm:w-auto">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-8 py-4 rounded-full bg-gradient-to-r from-teal-500 to-blue-600 text-white font-semibold shadow-xl shadow-teal-500/30 hover:shadow-teal-500/50 hover:scale-105 transition-all w-full sm:w-auto">
                        Access Platform
                    </a>
                @endauth
                <a href="#features" class="px-8 py-4 rounded-full glass-panel font-semibold hover:bg-white/10 transition-colors w-full sm:w-auto">
                    Explore Features
                </a>
            </div>

        </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="py-24 relative z-10 w-full border-t border-gray-200 dark:border-white/5 bg-white/50 dark:bg-[#0f111a]/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Powerful Capabilities</h2>
                <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">Everything you need to manage your business effectively in one unified platform.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-panel p-8 rounded-2xl hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-12 h-12 rounded-xl bg-teal-500/20 flex items-center justify-center mb-6 text-teal-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Multi-Tenancy</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Secure data isolation with seamless context switching. Manage multiple organizations under a single roof effortlessly.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="glass-panel p-8 rounded-2xl hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center mb-6 text-blue-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.95 11.95 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Advanced Security</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Enterprise-grade security featuring Spatie Permissions, robust rate limiting, and strict model scoping.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="glass-panel p-8 rounded-2xl hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center mb-6 text-purple-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Lightning Fast</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">Built on Laravel Filament with Livewire, providing an incredibly fast and reactive user experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="w-full py-8 border-t border-gray-200 dark:border-white/10 relative z-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                &copy; {{ date('Y') }} Future Code. All rights reserved.
            </p>
            <div class="flex items-center gap-6">
                <a href="#" class="text-sm text-gray-500 hover:text-teal-500 transition-colors">Documentation</a>
                <a href="#" class="text-sm text-gray-500 hover:text-teal-500 transition-colors">Privacy</a>
                <a href="#" class="text-sm text-gray-500 hover:text-teal-500 transition-colors">Terms</a>
            </div>
        </div>
    </footer>

</body>
</html>
