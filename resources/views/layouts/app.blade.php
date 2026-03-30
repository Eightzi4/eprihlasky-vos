<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'E-přihláška | OAUH')</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('storage/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .topo-bg {
            background-image: url("{{ asset('storage/topography_background.svg') }}");
            background-attachment: fixed;
            background-size: cover;
            background-position: center;
        }

        .material-symbols-rounded {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            vertical-align: middle;
        }
    </style>
    @stack('styles')
</head>

<body class="topo-bg bg-white text-school-dark antialiased flex flex-col min-h-screen relative">
    <div class="fixed inset-0 z-0 bg-white/5 backdrop-blur-[1px] pointer-events-none"></div>

    <header class="bg-[#f7f7f7]/90 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative h-20 flex items-center justify-center">
            <div class="absolute left-4 sm:left-8">
                @yield('header-left')
            </div>

            <a href="https://www.oauh.cz/" class="flex-shrink-0 hover:opacity-90 transition-opacity">
                <img src="https://www.oauh.cz/content/filters/l2.png" alt="Logo OAUH"
                    class="h-12 w-auto object-contain">
            </a>

            <div class="absolute right-4 sm:right-8">
                @yield('header-right')
            </div>
        </div>
    </header>

    <main class="flex-grow flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-12 relative z-10">
        @yield('content')
    </main>

    <footer class="bg-[#f7f7f7] border-t border-gray-200 py-8 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-center items-center">
            <p class="text-sm text-gray-500 text-center font-medium">
                &copy; {{ date('Y') }} Obchodní akademie, Vyšší odborná škola a Jazyková škola
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
