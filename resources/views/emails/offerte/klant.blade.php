@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Je offerte van Eazyonline staat klaar</title>
</head>
<body style="margin:0; padding:0; font-family:'Inter Tight', Arial, Helvetica, sans-serif; background-color:#f7f9fa;">
  <div style="max-width:600px; margin:0 auto; padding:2rem; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.05);">

    <h2 style="color:#21c2d3; font-weight:bold; font-size:24px; margin-bottom:0.5rem;">
      Je offerte staat voor je klaar
    </h2>

    <p style="font-size:16px; color:#333; margin:0 0 1rem 0;">
      @if($contactName)
        Hi {{ $contactName }},
      @else
        Hi {{ $company }},
      @endif
    </p>

    <p style="font-size:15px; color:#333; margin:0 0 1rem 0;">
      We hebben een voorstel uitgewerkt voor jullie website en online groei. Via onderstaande knop kun je de offerte rustig online bekijken en na akkoord digitaal bevestigen.
    </p>

    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      @if($vervalDatum)
        <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;">
          <strong>Geldig t/m:</strong> {{ $vervalDatum->format('d-m-Y') }}
        </p>
      @endif
    </div>

    <div style="text-align:center; margin:2rem 0;">
      <a href="{{ $klantUrl }}"
         style="display:inline-block; padding:1rem 2rem; background-color:#21c2d3; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:10px;">
         Bekijk je offerte
      </a>
    </div>

    <p style="font-size:13px; color:#555; margin:0 0 1.25rem 0;">
      Werkt de knop niet? Open de offerte dan via deze link:
      <br>
      <a href="{{ $klantUrl }}" style="color:#21c2d3; text-decoration:underline; word-break:break-all;">
        {{ $klantUrl }}
      </a>
    </p>

    <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

    <p style="font-size:12px; color:#aaa; margin:0;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>
