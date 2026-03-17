@props(['application', 'current'])

@php
    $steps = [
        1 => ['label' => 'Osobní údaje', 'route' => 'application.step1'],
        2 => ['label' => 'Předchozí vzdělání', 'route' => 'application.step2'],
        3 => ['label' => 'Přílohy', 'route' => 'application.step3'],
        4 => ['label' => 'Platba', 'route' => 'application.step4'],
        5 => ['label' => 'Souhrn', 'route' => 'application.step5'],
    ];
@endphp

<div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-sm border border-white/60 p-4 mb-10 ring-1 ring-black/5">
    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-6 text-sm font-medium">
        @foreach ($steps as $num => $step)
            @php
                $isActive = $num === $current;
                $url = route($step['route'], $application->id);
                $btnCls =
                    'flex items-center gap-3 px-4 py-2 rounded-xl transition-colors border-none bg-transparent cursor-pointer ';
                $btnCls .= $isActive ? 'bg-red-50 text-school-primary' : 'text-gray-600 hover:bg-gray-100';
                $badgeCls = $isActive ? 'border-school-primary bg-white' : 'border-gray-300';
            @endphp

            @if ($num > 1)
                <div class="hidden sm:block w-12 h-px bg-gray-200"></div>
            @endif

            <button type="button" onclick="navigateTo('{{ $url }}')" class="{{ $btnCls }}">
                <span
                    class="h-8 w-8 rounded-full flex items-center justify-center border-2 text-sm font-bold {{ $badgeCls }}">
                    {{ $num }}
                </span>
                <span>{{ $step['label'] }}</span>
            </button>
        @endforeach
    </div>
</div>
