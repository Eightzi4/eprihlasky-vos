<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-width: 100%;
            width: 100% !important;
            height: 100% !important;
            background-color: #f7f7f7;
            font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
        }

        @media only screen and (max-width: 640px) {
            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 20px 10px !important;
            }

            .card {
                padding: 30px 20px !important;
            }

            .heading {
                font-size: 28px !important;
                line-height: 34px !important;
            }
        }
    </style>
</head>

<body style="background-color: #f7f7f7; color: #1d1d1b;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0"
        style="background-color: #f7f7f7; width: 100%; height: 100%;">
        <tr>
            <td align="center" style="padding: 40px 0;">
                <table class="container" width="600" border="0" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 32px; border: 1px solid rgba(255, 255, 255, 0.6); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);">
                    <tr>
                        <td class="card" style="padding: 50px 40px; text-align: center;">
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                <tr>
                                    <td
                                        style="background-color: #f7f7f7; border: 1px solid #e5e7eb; border-radius: 999px; padding: 6px 16px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                                        <span
                                            style="font-size: 12px; font-weight: 700; color: #e30613; letter-spacing: 0.5px; text-transform: uppercase;">
                                            E-přihláška VOŠ OAUH
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <div style="height: 30px; line-height: 30px;">&nbsp;</div>

                            <h1 class="heading"
                                style="margin: 0; font-size: 32px; font-weight: 800; color: #111827; line-height: 40px; letter-spacing: -0.025em; text-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                {{ $headline }}
                            </h1>

                            <div style="height: 20px; line-height: 20px;">&nbsp;</div>

                            @foreach ($lines as $line)
                                <p style="margin: 0 0 12px 0; font-size: 16px; line-height: 28px; color: #4b5563;">
                                    {{ $line }}
                                </p>
                            @endforeach

                            @if ($metaLine)
                                <div style="height: 12px; line-height: 12px;">&nbsp;</div>
                                <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                                    {{ $metaLine }}
                                </p>
                            @endif

                            @if ($buttonLabel && $buttonUrl)
                                <div style="height: 40px; line-height: 40px;">&nbsp;</div>
                                <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                                    <tr>
                                        <td align="center"
                                            style="border-radius: 16px; background-color: #f7f7f7; border: 1px solid #ffffff; border-bottom: 4px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);">
                                            <a href="{{ $buttonUrl }}"
                                                style="display: inline-block; padding: 16px 40px; font-family: 'Inter', 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 700; color: #1f2937; text-decoration: none; border-radius: 16px; text-shadow: 0 1px 1px rgba(255,255,255,0.5);">
                                                {{ $buttonLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if ($fallbackUrl)
                                <div style="height: 40px; line-height: 40px;">&nbsp;</div>
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="border-top: 1px solid #f3f4f6;"></td>
                                    </tr>
                                </table>

                                <div style="height: 30px; line-height: 30px;">&nbsp;</div>

                                <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 20px;">
                                    Pokud tlačítko nefunguje, využijte tento odkaz:
                                    <br>
                                    <a href="{{ $fallbackUrl }}"
                                        style="color: #e30613; text-decoration: underline;">{{ $fallbackUrl }}</a>
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>

                <table width="600" border="0" cellpadding="0" cellspacing="0" align="center"
                    style="margin-top: 30px;">
                    <tr>
                        <td align="center"
                            style="color: #9ca3af; font-size: 12px; line-height: 18px; font-weight: 500;">
                            <p style="margin: 0;">&copy; {{ date('Y') }} Obchodní akademie, Vyšší odborná škola a
                                Jazyková škola</p>
                            <p style="margin: 5px 0 0 0; color: #9ca3af;">Toto je automaticky generovaná zpráva.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
