@php
    $file = $file ?? null;
    $fileLabel = $fileLabel ?? null;
    $otherFiles = $otherFiles ?? collect();
@endphp

<div
    class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 p-6 sm:p-8 ring-1 ring-black/5 mb-6">
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
        <h2 class="text-xl font-bold text-gray-900">{{ $title }}</h2>
        <a href="{{ $editRoute }}"
            class="group relative flex items-center justify-center px-4 py-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 border border-transparent hover:border-gray-200">
            <div class="absolute inset-0 topo-bg opacity-30"></div>
            <div
                class="absolute inset-0 bg-white/60 backdrop-blur-[2px] group-hover:bg-white/70 transition-all duration-300">
            </div>
            <div class="absolute inset-0 rounded-xl border border-white/60 border-b-2 border-b-gray-200/50"></div>
            <span class="relative z-10 text-xs font-bold text-gray-600 flex items-center gap-2">
                <span
                    class="material-symbols-rounded text-[16px] text-gray-500 group-hover:text-school-primary transition-colors">edit</span>
                Upravit
            </span>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-5">
        @foreach ($rows as $row)
            @if ($row)
                <div class="{{ ($row['span'] ?? 1) === 2 ? 'sm:col-span-2' : '' }}">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">{{ $row['label'] }}</p>
                    @if (!empty($row['multiline']))
                        <p class="text-sm font-semibold text-gray-900 leading-relaxed whitespace-pre-line">
                            {{ $row['value'] }}</p>
                    @elseif (!empty($row['mono']))
                        <p class="text-sm font-semibold text-gray-900 font-mono">{{ $row['value'] }}</p>
                    @else
                        <p class="text-sm font-semibold text-gray-900">{{ $row['value'] }}</p>
                    @endif
                </div>
            @endif
        @endforeach

        @if ($file)
            <div class="sm:col-span-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">{{ $fileLabel }}</p>
                <a href="{{ asset('storage/' . $file->disk_path) }}" target="_blank"
                    class="inline-flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file">
                    @if (str_starts_with($file->mime_type, 'image/'))
                        <div
                            class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 bg-gray-100 flex-shrink-0">
                            <img src="{{ asset('storage/' . $file->disk_path) }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div
                            class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                            <span class="material-symbols-rounded">description</span>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p
                            class="text-sm font-bold text-gray-900 truncate group-hover/file:text-school-primary transition-colors">
                            {{ $file->filename }}
                        </p>
                        <p class="text-xs text-gray-500">{{ round($file->size / 1024) }} KB &bull; Klikněte pro
                            zobrazení</p>
                    </div>
                </a>
            </div>
        @endif

        @if ($otherFiles->isNotEmpty())
            <div class="sm:col-span-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-2">Další přílohy</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($otherFiles as $otherFile)
                        <a href="{{ asset('storage/' . $otherFile->disk_path) }}" target="_blank"
                            class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl hover:shadow-md transition-all group/file">
                            @if (str_starts_with($otherFile->mime_type, 'image/'))
                                <div
                                    class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 bg-gray-100 flex-shrink-0">
                                    <img src="{{ asset('storage/' . $otherFile->disk_path) }}"
                                        class="w-full h-full object-cover">
                                </div>
                            @else
                                <div
                                    class="h-10 w-10 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600 border border-blue-100 flex-shrink-0">
                                    <span class="material-symbols-rounded">attach_file</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p
                                    class="text-sm font-bold text-gray-900 truncate group-hover/file:text-school-primary transition-colors">
                                    {{ $otherFile->filename }}
                                </p>
                                <p class="text-xs text-gray-500">{{ round($otherFile->size / 1024) }} KB</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
