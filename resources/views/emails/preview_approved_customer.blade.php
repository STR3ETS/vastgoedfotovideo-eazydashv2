@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Preview goedgekeurd</title>
</head>
<body style="margin:0; padding:0; font-family:'Inter Tight', Arial, Helvetica, sans-serif; background-color:#f7f9fa;">
  <div style="max-width:600px; margin:0 auto; padding:2rem; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.05);">

    <h2 style="color:#21c2d3; font-weight:bold; font-size:24px; margin-bottom:0.5rem;">
      Preview goedgekeurd
    </h2>

    <p style="font-size:15px; color:#333; margin:0 0 1rem 0;">
      @if(!empty($contactName))
        Hi {{ $contactName }},
      @else
        Hi {{ $company }},
      @endif
    </p>

    <p style="font-size:15px; color:#333; margin:0 0 1rem 0;">
      Bedankt voor je goedkeuring! Hiermee bevestig je dat de preview de juiste richting op gaat.
      Vanaf hier zetten we dit om naar een duidelijke offerte (met planning) en bereiden we de livegang netjes voor.
    </p>

    {{-- Wat er nu gaat gebeuren --}}
    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      <p style="margin:0 0 .75rem 0; font-size:14px; color:#111;">
        <strong>Wat er nu gaat gebeuren</strong>
      </p>

      <ol style="margin:0; padding-left:18px; font-size:14px; color:#111; line-height:1.6;">
        <li style="margin:0 0 .5rem 0;">
          <strong>We bellen je om de preview door te nemen.</strong><br>
          We lopen samen alles kort langs en bespreken eventuele laatste wensen of aanpassingen.
        </li>
        <li style="margin:0 0 .5rem 0;">
          <strong>Daarna ontvang je de offerte per e-mail.</strong><br>
          Hierin staat duidelijk wat we leveren, de kosten en de verwachte planning richting livegang.
        </li>
        <li style="margin:0 0 .5rem 0;">
          <strong>Na akkoord krijg je de betaal-link / factuur.</strong><br>
          Betalen gaat via een veilige betaalomgeving (of factuur, afhankelijk van wat we afspreken).
        </li>
        <li style="margin:0;">
          <strong>Na betaling starten we met de final build & livegang.</strong><br>
          We werken de laatste details af en zetten alles live op jouw domein.
        </li>
      </ol>
    </div>

    {{-- Zekerheid / niet-scammy --}}
    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;">
        <strong>Goed om te weten</strong>
      </p>
      <p style="margin:0; font-size:14px; color:#111; line-height:1.6;">
        Je zit nog nergens “vast” zonder dat alles zwart-op-wit in de offerte staat.
        Tijdens het belmoment checken we samen of alles klopt, zodat je precies weet waar je aan toe bent.
      </p>
    </div>

    {{-- Wat we eventueel van jou nodig hebben --}}
    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;">
        <strong>Handig om alvast klaar te leggen</strong>
      </p>
      <ul style="margin:.25rem 0 0 0; padding-left:18px; font-size:14px; color:#111; line-height:1.6;">
        <li>Eventuele laatste wensen of opmerkingen over de preview</li>
        <li>Logo/beelden (als die nog niet definitief zijn)</li>
        <li>Teksten of belangrijke info die op de site moet komen</li>
        <li>Domein/hosting gegevens (alleen als livegang op jouw domein direct gepland wordt)</li>
      </ul>
    </div>

    {{-- CTA preview --}}
    @if(!empty($previewLink))
      <div style="text-align:center; margin:1.5rem 0 1rem 0;">
        <a href="{{ $previewLink }}"
           style="display:inline-block; padding:1rem 2rem; background-color:#21c2d3; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:10px;">
          Open de preview nog eens
        </a>
      </div>

      <p style="font-size:13px; color:#555; margin:0 0 1.25rem 0;">
        Werkt de knop niet? Open via:
        <br>
        <a href="{{ $previewLink }}" style="color:#21c2d3; text-decoration:underline; word-break:break-all;">
          {{ $previewLink }}
        </a>
      </p>
    @endif

    {{-- Contact --}}
    <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

    <p style="font-size:13px; color:#555; margin:0 0 1rem 0;">
      Wil je vóór het belmoment alvast iets doorgeven? Reageer gewoon op deze e-mail of mail naar
      <a href="mailto:info@eazyonline.nl" style="color:#21c2d3; text-decoration:underline;">info@eazyonline.nl</a>.
    </p>

    <p style="font-size:12px; color:#aaa; margin:0;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>