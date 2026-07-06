<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="utf-8">
    <title>Export přihlášky #{{ $application->evidence_number ?: $application->application_number ?: $application->id }}</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        @page {
            size: A4 portrait;
            margin: 0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: 'Inter', Helvetica, Arial, sans-serif;
            color: #111827;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .page-container {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            padding: 32px;
            position: relative;
            background-color: #ffffff;
            overflow: hidden;
        }

        .w-full {
            width: 100%;
        }

        .align-top {
            vertical-align: top;
        }

        .align-middle {
            vertical-align: middle;
        }

        .align-bottom {
            vertical-align: bottom;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .header-table {
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 16px;
            margin-bottom: 20px;
            table-layout: fixed;
        }

        .study-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 20px;
        }

        .badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            color: #4b5563;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            padding: 3px 10px;
            border-radius: 6px;
            margin-left: 4px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .section-title svg {
            vertical-align: -3px;
            margin-right: 4px;
        }

        .data-table {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        .data-table tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .data-table tr:last-child {
            border-bottom: none;
        }

        .data-table td {
            padding: 4px 0;
            height: 26px;
            vertical-align: middle;
        }

        .data-label {
            font-size: 10px;
            color: #9ca3af;
        }

        .data-value {
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
            text-align: right;
            word-wrap: break-word;
            word-break: break-word;
        }

        .attachment-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 6px 10px;
            background-color: #ffffff;
            height: 44px;
        }

        .icon-box {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid;
            text-align: center;
            vertical-align: middle;
        }

        .icon-box-active {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }

        .icon-box-inactive {
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }

        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px 12px;
            height: 140px;
            overflow: hidden;
            position: relative;
        }

        .truncate-multiline {
            display: -webkit-box;
            -webkit-line-clamp: 7;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            word-break: break-all;
            word-wrap: break-word;
            line-height: 1.4;
            max-height: 108px;
            font-size: 11px;
            color: #374151;
        }

        .stamp-container {
            width: 120px;
            height: 120px;
            position: relative;
            display: inline-block;
            transform: rotate(-15deg);
            -webkit-transform: rotate(-15deg);
        }

        .footer-container {
            position: absolute;
            bottom: 32px;
            left: 32px;
            right: 32px;
        }
    </style>
</head>

<body>

    @php
        $fullName = trim(($application->first_name ?? '') . ' ' . ($application->last_name ?? ''));
        $address = trim(
            implode(
                ', ',
                array_filter([
                    $application->street,
                    trim(($application->zip ?? '') . ' ' . ($application->city ?? '')),
                    $application->country,
                ]),
            ),
        );
        $studyProgram = $application->studyProgram;
        $round = $application->round;
        $generatedAt = now();
        $otherFilesList =
            isset($otherFiles) && $otherFiles->isNotEmpty() ? $otherFiles->pluck('filename')->implode(', ') : null;
    @endphp

    <div class="page-container">

        <table class="w-full header-table">
            <tr>
                <td class="align-top" style="width: 65%;">
                    <table class="w-full" style="table-layout: fixed;">
                        <tr>
                            <td style="width: 52px;" class="align-top">
                                <svg viewBox="0 0 512 512" style="width: 48px; height: 48px; display: block;"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <clipPath id="shapeClipHeader">
                                            <path d="M0,136 C0,60 60,0 136,0 H512 V376 C512,452 452,512 376,512 H0 Z" />
                                        </clipPath>
                                    </defs>
                                    <path d="M0,136 C0,60 60,0 136,0 H512 V376 C512,452 452,512 376,512 H0 Z"
                                        fill="#E30613" />
                                    <g clip-path="url(#shapeClipHeader)">
                                        <rect x="246" y="0" width="20" height="512" fill="#FFFFFF" />
                                        <rect x="0" y="246" width="512" height="20" fill="#FFFFFF" />
                                    </g>
                                    <text x="128" y="150" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                        font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                        dominant-baseline="middle">O</text>
                                    <text x="384" y="150" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                        font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                        dominant-baseline="middle">A</text>
                                    <text x="128" y="406" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                        font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                        dominant-baseline="middle">U</text>
                                    <text x="384" y="406" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                        font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                        dominant-baseline="middle">H</text>
                                </svg>
                            </td>
                            <td class="align-top" style="padding-left: 10px;">
                                <div
                                    style="font-size: 10px; font-weight: 700; color: #dc2626; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
                                    E-přihláška VOŠ OAUH</div>
                                <div
                                    style="font-size: 22px; font-weight: 800; color: #111827; letter-spacing: -0.025em; line-height: 1.1; margin-bottom: 4px;">
                                    {{ $fullName ?: '—' }}
                                </div>
                                <div style="font-size: 12px; color: #6b7280;">
                                    {{ $application->email ?: '—' }} &nbsp;·&nbsp; {{ $application->phone ?: '—' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="align-top text-right" style="width: 35%;">
                    <div
                        style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
                        Evidenční číslo</div>
                    <div
                        style="font-size: 18px; font-weight: 800; color: #111827; font-family: monospace; line-height: 1.1;">
                        {{ $application->evidence_number ?: $application->application_number ?: '#' . $application->id }}
                    </div>
                    @if ($application->submitted && $application->submitted_at)
                        <div style="font-size: 10px; color: #9ca3af; margin-top: 4px;">
                            Odesláno {{ $application->submitted_at->format('j. n. Y H:i') }}
                        </div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="study-card">
            <table class="w-full" style="table-layout: fixed;">
                <tr>
                    <td class="align-middle" style="width: 60%;">
                        <div
                            style="font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
                            Studijní program</div>
                        <div style="font-size: 15px; font-weight: 700; color: #dc2626; line-height: 1.2;">
                            {{ $studyProgram?->name ?: 'Neuvedeno' }}
                        </div>
                    </td>
                    <td class="align-middle text-right" style="width: 40%;">
                        @if ($studyProgram?->form)
                            <span class="badge">{{ $studyProgram->form }}</span>
                        @endif
                        @if ($studyProgram?->length)
                            <span class="badge">{{ $studyProgram->length }}</span>
                        @endif
                        <span class="badge">{{ $round?->label ?: $round?->academic_year ?: '—' }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <table class="w-full" style="margin-bottom: 20px; table-layout: fixed;">
            <tr>
                <td class="align-top" style="width: 48.5%;">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"
                            fill="#6b7280">
                            <path
                                d="M367-527q-47-47-47-113t47-113q47-47 113-47t113 47q47 47 47 113t-47 113q-47 47-113 47t-113-47ZM160-160v-112q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v112H160Zm80-80h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm296.5-343.5Q560-607 560-640t-23.5-56.5Q513-720 480-720t-56.5 23.5Q400-673 400-640t23.5 56.5Q447-560 480-560t56.5-23.5ZM480-640Zm0 400Z" />
                        </svg>
                        Osobní a kontaktní údaje
                    </div>
                    <table class="w-full data-table">
                        @foreach ([['l' => 'Rodné číslo', 'v' => $application->birth_number], ['l' => 'Datum narození', 'v' => $application->birth_date?->format('j. n. Y')], ['l' => 'Místo narození', 'v' => $application->birth_city], ['l' => 'Pohlaví', 'v' => $application->gender], ['l' => 'Státní občanství', 'v' => $application->citizenship], ['l' => 'Adresa', 'v' => $address], ['l' => 'E-mail', 'v' => $application->email], ['l' => 'Telefon', 'v' => $application->phone]] as $row)
                            <tr>
                                <td class="data-label" style="width: 38%;">{{ $row['l'] }}</td>
                                <td class="data-value" style="width: 62%;">{{ $row['v'] ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>

                <td style="width: 3%;"></td>

                <td class="align-top" style="width: 48.5%;">
                    <div class="section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"
                            fill="#6b7280">
                            <path
                                d="M480-120 200-272v-240L40-600l440-240 440 240v320h-80v-276l-80 44v240L480-120Zm0-332 274-148-274-148-274 148 274 148Zm0 241 200-108v-151L480-360 280-470v151l200 108Zm0-241Zm0 90Zm0 0Z" />
                        </svg>
                        Předchozí vzdělání
                    </div>
                    <table class="w-full data-table">
                        @foreach ([['l' => 'Název střední školy', 'v' => $application->previous_school], ['l' => 'IZO školy', 'v' => $application->izo], ['l' => 'Typ školy', 'v' => $application->school_type], ['l' => 'Obor studia', 'v' => $application->previous_study_field], ['l' => 'Kód oboru (KKOV)', 'v' => $application->previous_study_field_code], ['l' => 'Rok maturity', 'v' => $application->graduation_year], ['l' => 'Přinesu maturitní vysvědčení osobně', 'v' => $application->bring_maturita_in_person ? 'Ano' : 'Ne'], ['l' => 'Průměr — 1. pololetí 4. roč.', 'v' => $application->half_year_grade_average], ['l' => 'Průměr — 2. pololetí 4. roč.', 'v' => $application->grade_average], ['l' => 'Průměr — maturitní vysvědčení', 'v' => $application->maturita_grade_average]] as $row)
                            <tr>
                                <td class="data-label" style="width: 52%;">{{ $row['l'] }}</td>
                                <td class="data-value"
                                    style="width: 48%; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $row['v'] ?: '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>

        <div style="margin-bottom: 20px;">
            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20"
                    fill="#6b7280">
                    <path
                        d="M720-330q0 104-73 177T470-80q-104 0-177-73t-73-177v-370q0-75 52.5-127.5T400-880q75 0 127.5 52.5T580-700v350q0 46-32 78t-78 32q-46 0-78-32t-32-78v-370h80v370q0 13 8.5 21.5T470-320q13 0 21.5-8.5T500-350v-350q-1-42-29.5-71T400-800q-42 0-71 29t-29 71v370q-1 71 49 120.5T470-160q70 0 119-49.5T640-330v-390h80v390Z" />
                </svg>
                Dokumenty a přílohy
            </div>

            <table class="w-full" style="border-spacing: 0 8px; border-collapse: separate; table-layout: fixed;">
                <tr>
                    <td style="width: 48.5%; padding: 0;">
                        <div class="attachment-card">
                            <table class="w-full" style="table-layout: fixed;">
                                <tr>
                                    <td style="width: 34px;" class="align-middle">
                                        <div
                                            class="icon-box {{ $maturitaFile ? 'icon-box-active' : 'icon-box-inactive' }}">
                                            <table style="width: 100%; height: 100%;">
                                                <tr>
                                                    <td class="align-middle text-center">
                                                        @if ($maturitaFile)
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#16a34a"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#d1d5db"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                                            </svg>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="align-middle" style="padding-left: 8px;">
                                        <div style="font-size: 9px; color: #6b7280; line-height: 1;">Notářsky ověřené
                                            maturitní vysvědčení</div>
                                        <div
                                            style="font-size: 11px; font-weight: 700; color: {{ $maturitaFile ? '#1f2937' : '#9ca3af' }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; margin-top: 2px;">
                                            {{ $maturitaFile ? $maturitaFile->filename : 'Nenahráno' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>

                    <td style="width: 3%;"></td>

                    <td style="width: 48.5%; padding: 0;">
                        <div class="attachment-card">
                            <table class="w-full" style="table-layout: fixed;">
                                <tr>
                                    <td style="width: 34px;" class="align-middle">
                                        <div
                                            class="icon-box {{ $halfYearFile ? 'icon-box-active' : 'icon-box-inactive' }}">
                                            <table style="width: 100%; height: 100%;">
                                                <tr>
                                                    <td class="align-middle text-center">
                                                        @if ($halfYearFile)
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#16a34a"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#d1d5db"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                                            </svg>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="align-middle" style="padding-left: 8px;">
                                        <div style="font-size: 9px; color: #6b7280; line-height: 1;">Vysvědčení 4.
                                            ročník</div>
                                        <div
                                            style="font-size: 11px; font-weight: 700; color: {{ $halfYearFile ? '#1f2937' : '#9ca3af' }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; margin-top: 2px;">
                                            {{ $halfYearFile ? $halfYearFile->filename : 'Nenahráno' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>

                <tr>
                    <td style="width: 48.5%; padding: 0;">
                        <div class="attachment-card">
                            <table class="w-full" style="table-layout: fixed;">
                                <tr>
                                    <td style="width: 34px;" class="align-middle">
                                        <div
                                            class="icon-box {{ $paymentFile ? 'icon-box-active' : 'icon-box-inactive' }}">
                                            <table style="width: 100%; height: 100%;">
                                                <tr>
                                                    <td class="align-middle text-center">
                                                        @if ($paymentFile)
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#16a34a"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="M320-240h320v-80H320v80Zm0-160h320v-80H320v80ZM240-80q-33 0-56.5-23.5T160-160v-640q0-33 23.5-56.5T240-880h320l240 240v480q0 33-23.5 56.5T720-80H240Zm280-520v-200H240v640h480v-440H520ZM240-800v200-200 640-640Z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#d1d5db"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                                            </svg>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="align-middle" style="padding-left: 8px;">
                                        <div style="font-size: 9px; color: #6b7280; line-height: 1;">Doklad o platbě
                                        </div>
                                        <div
                                            style="font-size: 11px; font-weight: 700; color: {{ $paymentFile ? '#1f2937' : '#9ca3af' }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; margin-top: 2px;">
                                            {{ $paymentFile ? $paymentFile->filename : 'Nenahráno' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>

                    <td style="width: 3%;"></td>

                    <td style="width: 48.5%; padding: 0;">
                        <div class="attachment-card">
                            <table class="w-full" style="table-layout: fixed;">
                                <tr>
                                    <td style="width: 34px;" class="align-middle">
                                        <div
                                            class="icon-box {{ $otherFilesList ? 'icon-box-active' : 'icon-box-inactive' }}">
                                            <table style="width: 100%; height: 100%;">
                                                <tr>
                                                    <td class="align-middle text-center">
                                                        @if ($otherFilesList)
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#16a34a"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h240l80 80h320q33 0 56.5 23.5T880-640v400q0 33-23.5 56.5T800-160H160Zm0-80h640v-400H447l-80-80H160v480Zm0 0v-480 480Z" />
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" height="16"
                                                                viewBox="0 -960 960 960" width="16"
                                                                fill="#d1d5db"
                                                                style="display: block; margin: 0 auto;">
                                                                <path
                                                                    d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z" />
                                                            </svg>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                    <td class="align-middle" style="padding-left: 8px;">
                                        <div style="font-size: 9px; color: #6b7280; line-height: 1;">Další přílohy
                                        </div>
                                        <div
                                            style="font-size: 11px; font-weight: 700; color: {{ isset($otherFiles) && $otherFiles->isNotEmpty() ? '#1f2937' : '#9ca3af' }}; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; margin-top: 2px;">
                                            {{ isset($otherFiles) && $otherFiles->isNotEmpty() ? $otherFiles->count() : 'Žádné' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="w-full" style="margin-bottom: 20px; table-layout: fixed;">
            <tr>
                <td class="align-top" style="width: 48.5%;">
                    <div class="info-box">
                        <div
                            style="font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; line-height: 1;">
                            SPECIFICKÉ POTŘEBY UCHAZEČE</div>
                        <div class="truncate-multiline">
                            {{ $application->specific_needs ?: '—' }}
                        </div>
                    </div>
                </td>

                <td style="width: 3%;"></td>

                <td class="align-top" style="width: 48.5%;">
                    <div class="info-box">
                        <div
                            style="font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; line-height: 1;">
                            ADMINISTRATIVNÍ POZNÁMKA</div>
                        <div class="truncate-multiline">
                            {{ $application->note ?: '—' }}
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-container">
            <table class="w-full" style="table-layout: fixed;">
                <tr>
                    <td class="align-bottom" style="width: 68%;">
                        <div style="font-size: 10px; color: #9ca3af; line-height: 1.35;">
                            <span style="font-weight: 700; color: #6b7280;">Obchodní akademie, Vyšší odborná škola a
                                Jazyková škola</span><br>
                            s právem státní jazykové zkoušky Uherské Hradiště
                        </div>
                    </td>
                    <td class="align-bottom text-right" style="width: 32%;">
                        <div class="stamp-container">
                            <svg viewBox="0 0 512 512"
                                style="position: absolute; top: 0; left: 0; width: 120px; height: 120px; pointer-events: none;"
                                xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <clipPath id="shapeClipStamp">
                                        <path d="M0,136 C0,60 60,0 136,0 H512 V376 C512,452 452,512 376,512 H0 Z" />
                                    </clipPath>
                                </defs>

                                <path d="M0,136 C0,60 60,0 136,0 H512 V376 C512,452 452,512 376,512 H0 Z"
                                    fill="#F9CDD0" />

                                <g clip-path="url(#shapeClipStamp)">
                                    <rect x="246" y="0" width="20" height="512" fill="#FFFFFF" />
                                    <rect x="0" y="246" width="512" height="20" fill="#FFFFFF" />
                                </g>

                                <text x="128" y="150" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                    font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                    dominant-baseline="middle">O</text>
                                <text x="384" y="150" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                    font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                    dominant-baseline="middle">A</text>
                                <text x="128" y="406" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                    font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                    dominant-baseline="middle">U</text>
                                <text x="384" y="406" font-family="Arial, Helvetica, sans-serif" font-size="150"
                                    font-weight="400" fill="#FFFFFF" text-anchor="middle"
                                    dominant-baseline="middle">H</text>
                            </svg>

                            <table
                                style="width: 120px; height: 120px; border-collapse: collapse; position: relative; z-index: 10;">
                                <tr>
                                    <td class="align-middle text-center" style="padding: 0 4px;">
                                        <div
                                            style="font-size: 8.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.18em; line-height: 1.1;">
                                            VYGENEROVÁNO</div>
                                        <div
                                            style="font-size: 13px; font-weight: 800; line-height: 1.15; margin-top: 2px;">
                                            {{ $generatedAt->format('j. n. Y') }}</div>
                                        <div
                                            style="font-size: 9.5px; font-weight: 800; line-height: 1.1;">
                                            {{ $generatedAt->format('H:i') }}</div>
                                        <div
                                            style="font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; line-height: 1.1; margin-top: 3px;">
                                            E-PŘIHLÁŠKA<br>VOŠ OAUH</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>

</body>

</html>
