@props(['title', 'description' => null])

<div
    class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5 mb-6">
    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $title }}</h2>
        @if ($description)
            <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
        @endif
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        {{ $slot }}
    </div>
</div>
