<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="utf-8">
    <title>Přihláška {{ $application->evidence_number ?: $application->application_number ?: $application->id }}</title>
    <style>
        @page {
            size: 210mm 297mm;
            margin: 15mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.3;
        }

        .page-container {
            width: 175mm;
            margin: 0 auto;
        }

        .layout-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .header-wrapper {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .school-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #0f172a;
        }

        .school-subtitle {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }

        .doc-meta-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .doc-meta-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin-top: 2px;
        }

        .hero-banner {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        .hero-label {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .hero-value {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .badge {
            display: inline-block;
            background: #e2e8f0;
            color: #334155;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            margin-right: 5px;
        }

        .badge-accent {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .section-title {
            font-size: 10px;
            color: #0f172a;
            text-transform: uppercase;
            font-weight: bold;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 8px;
            margin-top: 14px;
        }

        .section-title:first-child {
            margin-top: 0;
        }

        .val-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 8px;
            margin-bottom: 8px;
            min-height: 35px;
        }

        .lbl {
            font-size: 7px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: bold;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
        }

        .val {
            font-size: 9.5px;
            color: #0f172a;
            font-weight: bold;
            word-wrap: break-word;
        }

        .val-text {
            font-weight: normal;
            line-height: 1.4;
            white-space: pre-wrap;
        }

        .text-muted {
            color: #94a3b8;
            font-weight: normal;
        }

        .footer-table {
            width: 100%;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }

        .footer-cell {
            font-size: 8px;
            color: #64748b;
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
            implode(', ', array_filter([
                $application->street,
                trim(($application->zip ?? '') . ' ' . ($application->city ?? '')),
                $application->country,
            ]))
        );

        $studyProgram = $application->studyProgram;
        $round = $application->round;
        $otherAttachmentList = $otherFiles->isNotEmpty() ? $otherFiles->pluck('filename')->implode(', ') : '–';
    @endphp

    <div class="page-container">

        <div class="header-wrapper">
            <table class="layout-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="36" valign="middle" style="padding-right: 8px;">
                        <img src="{{ public_path('storage/logo.png') }}" style="width: 36px; height: 36px; display: block;">
                    </td>
                    <td valign="middle">
                        <div class="school-title">Obchodní akademie, Vyšší odborná škola</div>
                        <div class="school-subtitle">a Jazyková škola s právem státní jazykové zkoušky Uherské Hradiště</div>
                    </td>
                    <td valign="middle" align="right">
                        <div class="doc-meta-label">Evidenční číslo přihlášky</div>
                        <div class="doc-meta-value">{{ $application->evidence_number ?: $application->application_number ?: $application->id }}</div>
                        @if ($application->submitted && $application->submitted_at)
                            <div style="font-size: 7.5px; color: #64748b; margin-top: 3px;">
                                Odesláno: {{ $application->submitted_at->format('j. n. Y H:i') }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="hero-banner">
            <table class="layout-table" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="50%" valign="top">
                        <div style="margin-right: 10px;">
                            <div class="hero-label">Uchazeč</div>
                            <div class="hero-value">{{ $fullName ?: '–' }}</div>
                            <div style="font-size: 8.5px; color: #64748b;">
                                {{ $application->email ?: 'E-mail neuveden' }} &nbsp;|&nbsp; {{ $application->phone ?: 'Tel. neuveden' }}
                            </div>
                        </div>
                    </td>
                    <td width="50%" valign="top" align="right">
                        <div style="margin-left: 10px;">
                            <div class="hero-label">Studijní program</div>
                            <div class="hero-value" style="color: #1e3a8a;">{{ $studyProgram?->name ?: 'Neuvedeno' }}</div>
                            <div>
                                @if ($studyProgram?->form)
                                    <span class="badge">{{ $studyProgram->form }}</span>
                                @endif
                                @if ($studyProgram?->length)
                                    <span class="badge">{{ $studyProgram->length }}</span>
                                @endif
                                <span class="badge badge-accent">Kolo: {{ $round?->label ?: ($round?->academic_year ?: 'Neuvedeno') }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="layout-table" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" valign="top">
                    <div style="margin-right: 6px;">

                        <div class="section-title">Osobní a kontaktní údaje</div>

                        <table class="layout-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="50%" valign="top">
                                    <div style="margin-right: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Rodné číslo</div>
                                            <div class="val">{{ $application->birth_number ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td width="50%" valign="top">
                                    <div style="margin-left: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Datum narození</div>
                                            <div class="val">{{ $application->birth_date?->format('j. n. Y') ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="layout-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="50%" valign="top">
                                    <div style="margin-right: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Místo narození</div>
                                            <div class="val">{{ $application->birth_city ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td width="50%" valign="top">
                                    <div style="margin-left: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Pohlaví</div>
                                            <div class="val">{{ $application->gender ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <div class="val-box">
                            <div class="lbl">Státní občanství</div>
                            <div class="val">{{ $application->citizenship ?: '–' }}</div>
                        </div>

                        <div class="val-box">
                            <div class="lbl">Adresa trvalého bydliště</div>
                            <div class="val">{{ $address ?: '–' }}</div>
                        </div>

                        <div class="section-title">Předchozí vzdělání</div>

                        <div class="val-box">
                            <div class="lbl">Název střední školy</div>
                            <div class="val">{{ $application->previous_school ?: '–' }}</div>
                        </div>

                        <table class="layout-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="50%" valign="top">
                                    <div style="margin-right: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">IZO školy</div>
                                            <div class="val">{{ $application->izo ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td width="50%" valign="top">
                                    <div style="margin-left: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Typ školy</div>
                                            <div class="val">{{ $application->school_type ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="layout-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="50%" valign="top">
                                    <div style="margin-right: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Obor studia</div>
                                            <div class="val">{{ $application->previous_study_field ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td width="50%" valign="top">
                                    <div style="margin-left: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Kód oboru (KKOV)</div>
                                            <div class="val">{{ $application->previous_study_field_code ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                        <table class="layout-table" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="50%" valign="top">
                                    <div style="margin-right: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Rok maturity</div>
                                            <div class="val">{{ $application->graduation_year ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td width="50%" valign="top">
                                    <div style="margin-left: 4px;">
                                        <div class="val-box">
                                            <div class="lbl">Průměr známek</div>
                                            <div class="val">{{ $application->grade_average ?: '–' }}</div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>

                    </div>
                </td>

                <td width="50%" valign="top">
                    <div style="margin-left: 6px;">

                        <div class="section-title">Přílohy</div>

                        <div class="val-box">
                            <div class="lbl">Maturitní vysvědčení</div>
                            <div class="val {{ $maturitaFile ? '' : 'text-muted' }}">
                                {{ $maturitaFile ? $maturitaFile->filename : 'Nenahráno' }}
                            </div>
                        </div>

                        <div class="val-box">
                            <div class="lbl">Doklad o platbě</div>
                            <div class="val {{ $paymentFile ? '' : 'text-muted' }}">
                                {{ $paymentFile ? $paymentFile->filename : 'Nenahráno' }}
                            </div>
                        </div>

                        @if ($otherFiles->isNotEmpty())
                            <div class="val-box">
                                <div class="lbl">Další přílohy</div>
                                <div class="val text-muted">{{ $otherAttachmentList }}</div>
                            </div>
                        @endif

                        @if ($application->specific_needs || $application->note)
                            <div class="section-title">Poznámky</div>

                            @if ($application->specific_needs)
                                <div class="val-box">
                                    <div class="lbl">Specifické potřeby</div>
                                    <div class="val val-text">{{ $application->specific_needs }}</div>
                                </div>
                            @endif

                            @if ($application->note)
                                <div class="val-box">
                                    <div class="lbl">Poznámka administrativně</div>
                                    <div class="val val-text">{{ $application->note }}</div>
                                </div>
                            @endif
                        @endif

                    </div>
                </td>
            </tr>
        </table>

        <table class="footer-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="footer-cell">
                    Vygenerováno: <strong>{{ now()->format('j. n. Y H:i') }}</strong> (Administrace OAUH)
                </td>
                <td class="footer-cell" align="right">
                    <strong>OAUH</strong> · Vyšší odborná škola · Uherské Hradiště
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
