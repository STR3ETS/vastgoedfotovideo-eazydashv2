@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Preview reminder</title>
</head>
<body style="margin:0; padding:0; font-family:'Inter Tight', Arial, Helvetica, sans-serif; background-color:#f7f9fa;">
  <div style="max-width:600px; margin:0 auto; padding:2rem; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.05);">

    <h2 style="color:#21c2d3; font-weight:bold; font-size:24px; margin-bottom:0.5rem;">
      Kleine check-in 👋
    </h2>

    <p style="font-size:15px; color:#333; margin:0 0 1rem 0;">
      @if(!empty($contactName))
        Hi {{ $contactName }},
      @else
        Hi {{ $company }},
      @endif
    </p>

    <p style="font-size:15px; color:#333; margin:0 0 1rem 0;">
      We zien dat je de preview inmiddels een paar keer hebt bekeken. Helemaal goed, dat betekent meestal dat je ‘m serieus aan het checken bent.
      Als je klaar bent, kun je de preview <strong>goedkeuren</strong> of <strong>feedback achterlaten</strong>, dan kunnen wij direct door.
    </p>

    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;">
        <strong>Wat je nu het beste kunt doen:</strong>
      </p>
      <ol style="margin:0; padding-left:18px; font-size:14px; color:#111; line-height:1.6;">
        <li style="margin:0 0 .5rem 0;">
          Als je nog iets wil aanpassen: laat feedback achter in de preview. Dan pakken wij dit op zodra de offerte is getekend.
        </li>
        <li style="margin:0 0 .5rem 0;">
          Als alles klopt: klik in de preview op <strong>“Goedkeuren”</strong>.
        </li>
        <li style="margin:0;">
          Daarna bellen we je kort om alles door te nemen en sturen we de offerte.
        </li>
      </ol>
    </div>

    @if(!empty($previewLink))
      <div style="text-align:center; margin:1.5rem 0 1rem 0;">
        <a href="{{ $previewLink }}"
           style="display:inline-block; padding:1rem 2rem; background-color:#21c2d3; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:10px;">
          Open preview
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

    <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

    <p style="font-size:13px; color:#555; margin:0 0 1rem 0;">
      Liever meteen iets doorgeven? Reageer op deze mail of mail naar
      <a href="mailto:info@eazyonline.nl" style="color:#21c2d3; text-decoration:underline;">info@eazyonline.nl</a>.
    </p>

    <p style="font-size:12px; color:#aaa; margin:0;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>
