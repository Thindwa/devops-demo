<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            *,::after,::before{box-sizing:border-box;margin:0;padding:0}body{font-family:'Instrument Sans',system-ui,sans-serif;background:#18181b;color:#fafafa;min-height:100vh;line-height:1.5;-webkit-font-smoothing:antialiased}.fixed{position:fixed}.inset-0{inset:0}.-z-10{z-index:-10}.sticky{position:sticky}.top-0{top:0}.z-50{z-index:50}.border-b{border-bottom-width:1px}.border-zinc-800\/80{border-color:rgba(39,39,42,.8)}.bg-zinc-950\/80{background-color:rgba(9,9,11,.8)}.backdrop-blur-xl{backdrop-filter:blur(24px)}.max-w-6xl{max-width:72rem}.mx-auto{margin-left:auto;margin-right:auto}.px-6{padding-left:1.5rem;padding-right:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.flex{display:flex}.items-center{align-items:center}.justify-between{justify-content:space-between}.text-sm{font-size:.875rem}.font-medium{font-weight:500}.text-zinc-500{color:#71717a}.gap-2{gap:.5rem}.text-zinc-400{color:#a1a1aa}.text-zinc-300{color:#d4d4d8}.px-4{padding-left:1rem;padding-right:1rem}.py-2{padding-top:.5rem;padding-bottom:.5rem}.rounded-full{border-radius:9999px}.bg-white{background:#fff}.text-zinc-900{color:#18181b}.shadow-lg{box-shadow:0 10px 15px -3px rgb(0 0 0/.1)}.overflow-x-hidden{overflow-x:hidden}.pt-20{padding-top:5rem}.pb-32{padding-bottom:8rem}.max-w-3xl{max-width:48rem}.text-violet-400{color:#a78bfa}.uppercase{text-transform:uppercase}.tracking-widest{letter-spacing:.1em}.mb-6{margin-bottom:1.5rem}.text-5xl{font-size:3rem;line-height:1}.text-7xl{font-size:4.5rem;line-height:1}.font-semibold{font-weight:600}.tracking-tight{letter-spacing:-.025em}.text-white{color:#fff}.leading-\[1\.1\]{line-height:1.1}.bg-gradient-to-r{background-image:linear-gradient(to right,var(--tw-gradient-stops))}.from-violet-400{--tw-gradient-from:#a78bfa;--tw-gradient-stops:var(--tw-gradient-from),var(--tw-gradient-to)}.via-fuchsia-400{--tw-gradient-stops:var(--tw-gradient-from),#e879f9,var(--tw-gradient-to)}.to-pink-400{--tw-gradient-to:#f472b6}.bg-clip-text{-webkit-background-clip:text;background-clip:text}.text-transparent{color:transparent}.mt-8{margin-top:2rem}.text-lg{font-size:1.125rem}.text-xl{font-size:1.25rem}.text-zinc-400{color:#a1a1aa}.leading-relaxed{line-height:1.625}.max-w-xl{max-width:36rem}.mt-12{margin-top:3rem}.gap-4{gap:1rem}.inline-flex{display:inline-flex}.py-3\.5{padding-top:.875rem;padding-bottom:.875rem}.rounded-xl{border-radius:.75rem}.border{border-width:1px}.border-zinc-700{border-color:#3f3f46}.mt-20{margin-top:5rem}.pt-16{padding-top:4rem}.border-t{border-top-width:1px}.text-xs{font-size:.75rem}.mb-6{margin-bottom:1.5rem}.grid{display:grid}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}.grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}.p-4{padding:1rem}.rounded-2xl{border-radius:1rem}.bg-zinc-900\/50{background-color:rgba(24,24,27,.5)}.h-10{height:2.5rem}.w-10{width:2.5rem}.ml-auto{margin-left:auto}a:hover .group-hover\:text-white{color:#fff}
        </style>
    @endif
</head>
<body class="antialiased min-h-screen bg-zinc-950 text-zinc-100 font-sans overflow-x-hidden">
    {{-- Background gradient --}}
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_80%_50%_at_50%_-20%,rgba(120,119,198,0.15),transparent)]" aria-hidden="true"></div>
    <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_60%_40%_at_80%_60%,rgba(251,113,133,0.08),transparent)]" aria-hidden="true"></div>

    <header class="sticky top-0 z-50 border-b border-zinc-800/80 bg-zinc-950/80 backdrop-blur-xl">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <span class="text-sm font-medium text-zinc-500">{{ config('app.name', 'Laravel') }}</span>
            @if (Route::has('login'))
                <nav class="flex items-center gap-2">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-zinc-300 hover:text-white px-4 py-2 rounded-full hover:bg-zinc-800/80 transition-all duration-200">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-400 hover:text-white px-4 py-2 rounded-full hover:bg-zinc-800/80 transition-all duration-200">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-medium bg-white text-zinc-900 hover:bg-zinc-100 px-4 py-2 rounded-full transition-all duration-200 shadow-lg shadow-white/5">
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 pt-20 pb-32 sm:pt-28 sm:pb-40">
        <div class="max-w-3xl">
            <p class="text-sm font-medium text-violet-400/90 uppercase tracking-widest mb-6">
                Welcome
            </p>
            <h1 class="text-5xl sm:text-7xl font-semibold tracking-tight text-white leading-[1.1]">
                Build something
                <span class="bg-gradient-to-r from-violet-400 via-fuchsia-400 to-pink-400 bg-clip-text text-transparent">beautiful</span>
            </h1>
            <p class="mt-8 text-lg sm:text-xl text-zinc-400 leading-relaxed max-w-xl">
                A modern starting point for your next application. Fast, simple, and ready to ship.
            </p>

            <div class="mt-12 flex flex-wrap gap-4">
                <a href="https://laravel.com/docs" target="_blank" rel="noopener" class="group inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-medium bg-white text-zinc-900 hover:bg-zinc-100 transition-all duration-200 shadow-lg shadow-white/10 hover:shadow-xl hover:shadow-white/15 hover:-translate-y-0.5">
                    Documentation
                    <svg class="w-4 h-4 opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="https://laracasts.com" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl font-medium border border-zinc-700 text-zinc-300 hover:border-zinc-600 hover:text-white hover:bg-zinc-800/50 transition-all duration-200">
                    Laracasts
                </a>
            </div>

            {{-- Quick links --}}
            <div class="mt-20 pt-16 border-t border-zinc-800/80">
                <p class="text-xs font-medium text-zinc-500 uppercase tracking-widest mb-6">Resources</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="https://laravel.com/docs" target="_blank" rel="noopener" class="group flex items-center gap-4 p-4 rounded-2xl border border-zinc-800/80 bg-zinc-900/50 hover:border-zinc-700 hover:bg-zinc-800/50 transition-all duration-200">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/10 text-violet-400 group-hover:bg-violet-500/20 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </span>
                        <div>
                            <span class="font-medium text-zinc-200 group-hover:text-white transition-colors">Docs</span>
                            <span class="block text-sm text-zinc-500">Laravel documentation</span>
                        </div>
                        <svg class="w-4 h-4 ml-auto text-zinc-600 group-hover:text-zinc-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <a href="https://laracasts.com" target="_blank" rel="noopener" class="group flex items-center gap-4 p-4 rounded-2xl border border-zinc-800/80 bg-zinc-900/50 hover:border-zinc-700 hover:bg-zinc-800/50 transition-all duration-200">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-pink-500/10 text-pink-400 group-hover:bg-pink-500/20 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <span class="font-medium text-zinc-200 group-hover:text-white transition-colors">Laracasts</span>
                            <span class="block text-sm text-zinc-500">Video tutorials</span>
                        </div>
                        <svg class="w-4 h-4 ml-auto text-zinc-600 group-hover:text-zinc-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
