<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administrace | OAUH')</title>
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
</head>

<body class="topo-bg bg-white text-gray-900 antialiased min-h-screen flex flex-col">

    <div class="fixed inset-0 z-0 bg-white/5 backdrop-blur-[1px] pointer-events-none"></div>

    <header class="bg-[#f7f7f7]/90 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-6">

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 flex-shrink-0">
                <img src="https://www.oauh.cz/content/filters/l2.png" alt="Logo OAUH" class="h-8 w-auto">
                <span class="text-sm font-bold text-gray-500 hidden sm:block">Administrace</span>
            </a>

            <nav class="flex items-center gap-1 flex-grow">
                @php $admin = Auth::guard('admin')->user(); @endphp

                @php
                    $navItems = [
                        ['route' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'Přehled'],
                        ['route' => 'admin.applications', 'icon' => 'description', 'label' => 'Přihlášky'],
                    ];
                    if ($admin->isMainAdmin()) {
                        $navItems[] = ['route' => 'admin.rounds', 'icon' => 'event', 'label' => 'Kola'];
                        $navItems[] = [
                            'route' => 'admin.admins',
                            'icon' => 'manage_accounts',
                            'label' => 'Administrátoři',
                        ];
                    }
                @endphp

                @foreach ($navItems as $item)
                    @php
                        $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'] . '.*');
                    @endphp
                    @if (Route::has($item['route']))
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold transition-all duration-200
                                {{ $isActive ? 'bg-red-50 text-school-primary' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                            <span class="material-symbols-rounded text-[18px]">{{ $item['icon'] }}</span>
                            <span class="hidden md:inline">{{ $item['label'] }}</span>
                        </a>
                    @else
                        <span
                            class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-bold text-gray-300 cursor-not-allowed">
                            <span class="material-symbols-rounded text-[18px]">{{ $item['icon'] }}</span>
                            <span class="hidden md:inline">{{ $item['label'] }}</span>
                        </span>
                    @endif
                @endforeach
            </nav>

            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-xs font-bold text-gray-900 leading-tight">{{ $admin->name }}</span>
                    <span
                        class="text-xs text-gray-400">{{ $admin->isMainAdmin() ? 'Hlavní administrátor' : 'Administrátor' }}</span>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit"
                        class="group relative flex items-center justify-center px-3 py-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer border border-transparent hover:border-gray-200">
                        <div class="absolute inset-0 topo-bg opacity-30 transition-opacity duration-300"></div>
                        <div
                            class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:backdrop-blur-[4px] transition-all duration-300">
                        </div>
                        <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50">
                        </div>
                        <span
                            class="relative z-10 text-gray-600 font-bold text-xs flex items-center gap-1.5">
                            <span class="material-symbols-rounded text-[18px] group-hover:text-school-primary group-hover:-translate-x-1 transition-all duration-300">logout</span>
                            <span class="hidden sm:inline">Odhlásit se</span>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    @if (session('success') || session('error'))
        <div class="relative z-10 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-6">
            @if (session('success'))
                <div
                    class="flex items-center gap-3 px-5 py-3 bg-green-50 border border-green-200 rounded-2xl text-green-800 text-sm font-medium">
                    <span class="material-symbols-rounded text-green-500 text-[20px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div
                    class="flex items-center gap-3 px-5 py-3 bg-red-50 border border-red-200 rounded-2xl text-red-800 text-sm font-medium">
                    <span class="material-symbols-rounded text-school-primary text-[20px]">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>
    @endif

    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        @yield('content')
    </main>

</body>

</html>
