<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="ltr"
    class="antialiased bg-gray-100"
>
<head>
    <meta charset="UTF-8">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{config('app.name')}}</title>

    <style>[x-cloak] {
            display: none !important;
        }</style>

    @if (filled($fontsUrl = config('filament.google_fonts')))
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $fontsUrl }}" rel="stylesheet"/>
    @endif

    <link rel="stylesheet" href="{{mix('css/monet.css', 'monet')}}"/>
    <script src="{{mix('js/monet.js', 'monet')}}" defer></script>

    @livewireStyles
    @livewireScripts
    @stack('scripts')

    @if (config('filament.dark_mode'))
        <script>
            const theme = localStorage.getItem('theme')
            if ((theme === 'dark') || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark')
            }
        </script>
    @endif
</head>
<body class="bg-gray-100 text-gray-900 dark:text-gray-100 dark:bg-gray-900">
<div
    class="monet-auth-page flex items-center justify-center min-h-screen bg-gray-100 text-gray-900 py-12 dark:bg-gray-900 dark:text-white"
>
    <div class="w-screen px-6 -mt-16 space-y-8 md:mt-0 md:px-2 max-w-md">
        <div
            class="p-8 space-y-4 bg-white/50 backdrop-blur-xl border border-gray-200 shadow-2xl rounded-2xl relative dark:bg-gray-900/50 dark:border-gray-700"
        >
            <div class="flex justify-center w-full">
                <div class="text-xl font-bold tracking-tight dark:text-white">
                    {{config('app.name')}}
                </div>
            </div>

            <h2 class="text-2xl font-bold tracking-tight text-center">
                @yield('title')
            </h2>

            <div>
                @yield('content')
            </div>
        </div>
    </div>
</div>

@livewire('notifications')
</body>
</html>
