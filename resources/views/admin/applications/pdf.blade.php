<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="utf-8">
    <title>Přihláška {{ $application->evidence_number ?: $application->application_number ?: $application->id }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5px;
            color: #000000;
            background: #ffffff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            position: relative;
        }

        .content-wrap {
            position: relative;
            z-index: 1;
            padding: 12mm 13mm 10mm 13mm;
        }

        .header {
            background: #ffffff;
            border-radius: 10px;
            padding: 14px 18px 12px 18px;
            margin-bottom: 10px;
            border: 1.5px solid #000000;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .header-left {
            flex: 1;
        }

        .logo-row {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 9px;
        }

        .logo-img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .logo-text-col {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .school-name-main {
            font-size: 9.5px;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.2px;
            line-height: 1.3;
        }

        .school-name-sub {
            font-size: 8px;
            color: #444444;
            letter-spacing: 0.2px;
        }

        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #000000;
            line-height: 1.15;
            margin-bottom: 3px;
        }

        .doc-subtitle {
            font-size: 8.5px;
            color: #555555;
        }

        .pills-row {
            display: flex;
            gap: 5px;
            margin-top: 9px;
            flex-wrap: wrap;
        }

        .pill {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: bold;
        }

        .pill-dark {
            background: #000000;
            color: #ffffff;
        }

        .pill-light {
            background: #ffffff;
            color: #000000;
            border: 1.5px solid #000000;
        }

        .header-right {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 155px;
        }

        .meta-card {
            background: #f5f5f5;
            border: 1px solid #cccccc;
            border-radius: 7px;
            padding: 6px 10px;
        }

        .meta-label {
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: #666666;
            margin-bottom: 2px;
        }

        .meta-value {
            font-size: 10px;
            font-weight: bold;
            color: #000000;
        }

        .meta-value-large {
            font-size: 13px;
            font-weight: bold;
            color: #000000;
        }

        .accent-line {
            height: 2.5px;
            background: #000000;
            border-radius: 2px;
            margin-bottom: 10px;
        }

        .two-col {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .col-main {
            flex: 1.65;
            min-width: 0;
        }

        .col-side {
            flex: 1;
            min-width: 0;
        }

        .card {
            background: #ffffff;
            border: 1px solid #bbbbbb;
            border-radius: 9px;
            margin-bottom: 9px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .card-head {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            background: #f0f0f0;
            border-bottom: 1px solid #cccccc;
        }

        .card-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #000000;
            flex-shrink: 0;
        }

        .card-title {
            font-size: 8.5px;
            font-weight: bold;
            color: #000000;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .card-body {
            padding: 9px 12px 7px 12px;
        }

        .field-table {
            width: 100%;
            border-collapse: collapse;
        }

        .field-table td {
            vertical-align: top;
            padding: 3px;
        }

        .field-half {
            width: 50%;
        }

        .field-label {
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #666666;
            margin-bottom: 2px;
        }

        .field-value {
            background: #f8f8f8;
            border: 1px solid #d5d5d5;
            border-radius: 5px;
            padding: 5px 8px;
            font-size: 9.5px;
            font-weight: bold;
            color: #000000;
            min-height: 22px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .field-value-text {
            background: #f8f8f8;
            border: 1px solid #d5d5d5;
            border-radius: 5px;
            padding: 6px 8px;
            font-size: 9.5px;
            font-weight: normal;
            color: #000000;
            min-height: 30px;
            line-height: 1.45;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        .field-value-muted {
            font-weight: normal;
            color: #777777;
        }

        .program-highlight {
            background: #f0f0f0;
            border: 1.5px solid #000000;
            border-radius: 8px;
            padding: 10px 12px;
        }

        .program-name {
            font-size: 12px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 7px;
        }

        .prog-attr-table {
            width: 100%;
            border-collapse: collapse;
        }

        .prog-attr-table td {
            padding: 2px 3px 2px 0;
            vertical-align: top;
        }

        .prog-attr {
            display: inline-block;
            background: #ffffff;
            border: 1px solid #bbbbbb;
            border-radius: 4px;
            padding: 3px 7px;
            font-size: 7.5px;
            color: #000000;
        }

        .footer {
            margin-top: 10px;
            padding: 7px 14px;
            background: #f0f0f0;
            border-radius: 8px;
            border: 1px solid #cccccc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-text {
            font-size: 7.5px;
            color: #555555;
        }

        .footer-bold {
            font-weight: bold;
            color: #000000;
        }
    </style>
</head>

<body>
    @php
        $maturitaFile = $application->attachments->where('type', 'maturita')->first();
        $paymentFile = $application->attachments->where('type', 'payment')->first();
        $otherFiles = $application->attachments->where('type', 'other');
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
        $otherAttachmentList = $otherFiles->isNotEmpty() ? $otherFiles->pluck('filename')->implode(', ') : '–';
    @endphp

    <div class="page">
        <div class="content-wrap">

            <div class="header">
                <div class="header-left">
                    <div class="logo-row">
                        <img class="logo-img" src="{{ public_path('storage/logo.png') }}" alt="OAUH logo">
                        <div class="logo-text-col">
                            <div class="school-name-main">Obchodní akademie, Vyšší odborná škola</div>
                            <div class="school-name-sub">a Jazyková škola s právem státní jazykové zkoušky</div>
                            <div class="school-name-sub">Uherské Hradiště</div>
                        </div>
                    </div>

                    <div class="doc-title">Přihláška ke studiu</div>
                    <div class="doc-subtitle">Kompaktní export přihlášky pro administrativní zpracování</div>

                    <div class="pills-row">
                        @if ($studyProgram?->form)
                            <span class="pill pill-dark">{{ $studyProgram->form }}</span>
                        @endif
                        @if ($studyProgram?->length)
                            <span class="pill pill-light">{{ $studyProgram->length }}</span>
                        @endif
                        @if ($application->submitted && $application->submitted_at)
                            <span class="pill pill-light">Odesláno:
                                {{ $application->submitted_at->format('j. n. Y H:i') }}</span>
                        @endif
                    </div>
                </div>

                <div class="header-right">
                    <div class="meta-card">
                        <div class="meta-label">Evidenční číslo</div>
                        <div class="meta-value-large">
                            {{ $application->evidence_number ?: $application->application_number ?: $application->id }}
                        </div>
                    </div>
                    <div class="meta-card">
                        <div class="meta-label">Studijní program</div>
                        <div class="meta-value">{{ $studyProgram?->name ?: 'Neuvedeno' }}</div>
                    </div>
                    <div class="meta-card">
                        <div class="meta-label">Přijímací kolo</div>
                        <div class="meta-value">{{ $round?->label ?: ($round?->academic_year ?: 'Neuvedeno') }}</div>
                    </div>
                </div>
            </div>

            <div class="accent-line"></div>

            <div class="two-col">

                <div class="col-main">

                    <div class="card">
                        <div class="card-head">
                            <div class="card-dot"></div>
                            <div class="card-title">Osobní a kontaktní údaje</div>
                        </div>
                        <div class="card-body">
                            <table class="field-table">
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">Jméno a příjmení</div>
                                        <div class="field-value">{{ $fullName ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Rodné číslo</div>
                                        <div class="field-value">{{ $application->birth_number ?: '–' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">Datum narození</div>
                                        <div class="field-value">
                                            {{ $application->birth_date?->format('j. n. Y') ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Místo narození</div>
                                        <div class="field-value">{{ $application->birth_city ?: '–' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">Pohlaví</div>
                                        <div class="field-value">{{ $application->gender ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Státní občanství</div>
                                        <div class="field-value">{{ $application->citizenship ?: '–' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">E-mail</div>
                                        <div class="field-value">{{ $application->email ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Telefon</div>
                                        <div class="field-value">{{ $application->phone ?: '–' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="field-label">Adresa trvalého bydliště</div>
                                        <div class="field-value">{{ $address ?: '–' }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom:0;">
                        <div class="card-head">
                            <div class="card-dot"></div>
                            <div class="card-title">Předchozí vzdělání</div>
                        </div>
                        <div class="card-body">
                            <table class="field-table">
                                <tr>
                                    <td colspan="2">
                                        <div class="field-label">Název střední školy</div>
                                        <div class="field-value">{{ $application->previous_school ?: '–' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">IZO školy</div>
                                        <div class="field-value">{{ $application->izo ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Typ školy</div>
                                        <div class="field-value">{{ $application->school_type ?: '–' }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">Obor studia</div>
                                        <div class="field-value">{{ $application->previous_study_field ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Kód oboru (KKOV)</div>
                                        <div class="field-value">{{ $application->previous_study_field_code ?: '–' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="field-half">
                                        <div class="field-label">Rok maturity</div>
                                        <div class="field-value">{{ $application->graduation_year ?: '–' }}</div>
                                    </td>
                                    <td class="field-half">
                                        <div class="field-label">Průměr známek</div>
                                        <div class="field-value">{{ $application->grade_average ?: '–' }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="col-side">

                    <div class="card">
                        <div class="card-head">
                            <div class="card-dot"></div>
                            <div class="card-title">Vybraný program</div>
                        </div>
                        <div class="card-body">
                            <div class="program-highlight">
                                <div class="program-name">{{ $studyProgram?->name ?: '–' }}</div>
                                <table class="prog-attr-table">
                                    <tr>
                                        <td><span class="prog-attr"><strong>Kód:</strong>
                                                {{ $studyProgram?->code ?: '–' }}</span></td>
                                        <td><span class="prog-attr"><strong>Titul:</strong>
                                                {{ $studyProgram?->degree ?: '–' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td><span class="prog-attr"><strong>Místo:</strong>
                                                {{ $studyProgram?->location ?: '–' }}</span></td>
                                        <td><span class="prog-attr"><strong>Školné:</strong>
                                                {{ $studyProgram?->tuition_fee ?: '–' }}</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-bottom:0;">
                        <div class="card-head">
                            <div class="card-dot"></div>
                            <div class="card-title">Přílohy a doplňující informace</div>
                        </div>
                        <div class="card-body">
                            <table class="field-table">
                                <tr>
                                    <td colspan="2">
                                        <div class="field-label">Maturitní vysvědčení</div>
                                        <div class="field-value {{ $maturitaFile ? '' : 'field-value-muted' }}">
                                            {{ $maturitaFile ? $maturitaFile->filename : 'Nenahrано' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="field-label">Doklad o platbě</div>
                                        <div class="field-value {{ $paymentFile ? '' : 'field-value-muted' }}">
                                            {{ $paymentFile ? $paymentFile->filename : 'Nenahrаno' }}
                                        </div>
                                    </td>
                                </tr>
                                @if ($otherFiles->isNotEmpty())
                                    <tr>
                                        <td colspan="2">
                                            <div class="field-label">Další přílohy</div>
                                            <div class="field-value field-value-muted">{{ $otherAttachmentList }}
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            @if ($application->specific_needs || $application->note)
                <div style="margin-top: 9px;">
                    <div class="card" style="margin-bottom: 0;">
                        <div class="card-head">
                            <div class="card-dot"></div>
                            <div class="card-title">Poznámky</div>
                        </div>
                        <div class="card-body">
                            <table class="field-table">
                                @if ($application->specific_needs)
                                    <tr>
                                        <td colspan="2">
                                            <div class="field-label">Specifické potřeby</div>
                                            <div class="field-value-text">{{ $application->specific_needs }}</div>
                                        </td>
                                    </tr>
                                @endif
                                @if ($application->note)
                                    <tr>
                                        <td colspan="2">
                                            <div class="field-label">Poznámka administrativně</div>
                                            <div class="field-value-text">{{ $application->note }}</div>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="footer" style="margin-top: 10px;">
                <div class="footer-text">
                    Vygenerováno {{ now()->format('j. n. Y H:i') }} z administrace OAUH
                </div>
                <div class="footer-text">
                    <span class="footer-bold">OAUH</span> · Vyšší odborná škola · Uherské Hradiště
                </div>
            </div>

        </div>
    </div>
</body>

</html>
