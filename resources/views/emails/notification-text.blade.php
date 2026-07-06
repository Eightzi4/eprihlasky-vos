{{ $headline }}

@foreach ($lines as $line)
{{ $line }}
@endforeach

@if ($metaLine)
{{ $metaLine }}
@endif

@if ($buttonLabel && $buttonUrl)
{{ $buttonLabel }}: {{ $buttonUrl }}
@endif

@if ($fallbackUrl)
Odkaz: {{ $fallbackUrl }}
@endif

--
E-přihláška VOŠ OAUH
Toto je automaticky generovaná zpráva.
