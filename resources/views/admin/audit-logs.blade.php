@extends('layouts.admin')

@section('title', 'Audit log | Administrace OAUH')

@section('content')
    @php
        $descriptionLabels = [
            \App\Models\AuditLog::DESCRIPTION_VIEW_APPLICATION => 'Zobrazení detailu přihlášky',
            \App\Models\AuditLog::DESCRIPTION_VIEW_AUDIT_LOG => 'Zobrazení auditního logu',
            \App\Models\AuditLog::DESCRIPTION_UPDATE_EVIDENCE_NUMBER => 'Úprava evidenčního čísla',
            \App\Models\AuditLog::DESCRIPTION_EXPORT_APPLICATION_CSV => 'Export přihlášky do CSV',
            \App\Models\AuditLog::DESCRIPTION_EXPORT_APPLICATION_PDF => 'Export přihlášky do PDF',
            \App\Models\AuditLog::DESCRIPTION_DOWNLOAD_ATTACHMENT => 'Stažení přílohy',
            \App\Models\AuditLog::DESCRIPTION_UPLOAD_ATTACHMENT => 'Nahrání přílohy',
            \App\Models\AuditLog::DESCRIPTION_DELETE_ATTACHMENT => 'Smazání přílohy',
            \App\Models\AuditLog::DESCRIPTION_ACCEPT_EDUCATION => 'Uznání vzdělání',
            \App\Models\AuditLog::DESCRIPTION_REVERT_EDUCATION => 'Zrušení uznání vzdělání',
            \App\Models\AuditLog::DESCRIPTION_ACCEPT_PAYMENT => 'Potvrzení platby',
            \App\Models\AuditLog::DESCRIPTION_REVERT_PAYMENT => 'Zrušení potvrzení platby',
        ];
    @endphp

    <div
        class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-sm border border-white/60 ring-1 ring-black/5 overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100/80 bg-white/40 flex items-center gap-3">
            <div class="h-8 w-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-rounded text-school-primary text-[20px]">history</span>
            </div>
            <div>
                <h1 class="font-bold text-gray-800 text-lg">Audit log</h1>
                <p class="text-sm text-gray-500">Neměnný přehled přístupů a zásahů do osobních údajů.</p>
            </div>
        </div>

        <div class="overflow-x-auto border-t border-gray-100/80">
            <table class="w-full min-w-[1100px] divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        @foreach (['Kdy', 'Administrátor', 'Akce', 'Událost', 'Přihláška', 'IP adresa', 'Platnost sezení'] as $heading)
                            <th class="px-5 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-red-50/20 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="font-semibold text-gray-900">{{ $log->created_at?->format('j. n. Y') }}</div>
                                <div>{{ $log->created_at?->format('H:i:s') }}</div>
                            </td>
                            <td class="px-5 py-4 text-sm">
                                <div class="font-semibold text-gray-900">
                                    {{ $log->admin?->name ?? 'Systém / neautorizováno' }}</div>
                                <div class="text-xs text-gray-400">{{ $log->admin?->email ?? '—' }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                <span
                                    class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 font-semibold text-gray-700">
                                    {{ $log->actionType?->label ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-gray-700">
                                {{ $descriptionLabels[$log->description] ?? ($log->description ?: '—') }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-500">
                                @if ($log->application)
                                    <a href="{{ route('admin.applications.show', $log->application->id) }}"
                                        class="inline-flex flex-col rounded-xl border border-gray-200 bg-white px-3 py-2 hover:border-school-primary/30 hover:shadow-sm transition-all">
                                        <span class="font-semibold text-gray-900">
                                            {{ $log->application->evidence_number ?: ($log->application->application_number ? '#' . $log->application->application_number : 'ID ' . $log->application->id) }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ trim(($log->application->first_name ?? '') . ' ' . ($log->application->last_name ?? '')) ?: 'Bez jména' }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-gray-400">Bez vazby na přihlášku</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ $log->ip_address }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div>{{ $log->not_before?->format('j. n. Y H:i') ?? '—' }}</div>
                                <div class="text-xs text-gray-400">do {{ $log->not_after?->format('j. n. Y H:i') ?? '—' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                                <span
                                    class="material-symbols-rounded text-[48px] block mb-2 opacity-30">history_toggle_off</span>
                                Audit log zatím neobsahuje žádné záznamy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div
            class="px-6 sm:px-8 py-5 border-t border-gray-100/80 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-500 gap-4 bg-white/30">
            <p>
                Zobrazeno
                <span class="font-bold text-gray-900">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</span>
                z <span class="font-bold text-gray-900">{{ $logs->total() }}</span> záznamů
            </p>
            <div class="flex items-center gap-2">
                @if ($logs->onFirstPage())
                    <span class="p-2 border border-gray-200 rounded-xl opacity-40">
                        <span class="material-symbols-rounded text-[18px]">chevron_left</span>
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                        class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        <span class="material-symbols-rounded text-[18px]">chevron_left</span>
                    </a>
                @endif

                <span class="px-2 font-medium tabular-nums">
                    Strana {{ $logs->currentPage() }} z {{ $logs->lastPage() }}
                </span>

                @if ($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                        class="p-2 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
                        <span class="material-symbols-rounded text-[18px]">chevron_right</span>
                    </a>
                @else
                    <span class="p-2 border border-gray-200 rounded-xl opacity-40">
                        <span class="material-symbols-rounded text-[18px]">chevron_right</span>
                    </span>
                @endif
            </div>
        </div>
    </div>
@endsection
