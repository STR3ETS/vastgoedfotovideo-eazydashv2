@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Preview goedgekeurd</title>
</head>
<body style="margin:0;padding:0;font-family:'Inter Tight', Arial, Helvetica, sans-serif;background-color:#ebf2f2;padding-top:1rem;padding-bottom:1rem;">
  <div style="max-width:600px;margin:0 auto;padding:2rem;background-color:#ffffff;border:1px solid #E5E7EB;border-radius:2rem;box-shadow:0 0 10px rgba(0,0,0,0.05);">

    <img
      src="{{ $message->embed(public_path('assets/logo.webp')) }}"
      alt="Eazyonline"
      style="display:block;max-width:25px;margin:0 auto;border:0;margin-bottom:1rem;"
    >

    <img
      src="{{ $message->embed(public_path('assets/memoji-row.png')) }}"
      alt="Eazyonline team"
      style="display:block;width:80%;max-width:325px;margin:0 auto;border:0;"
    >

    <h2 style="color:#21c2d3; font-weight:extrabold; font-size:28px; margin-top:0.5rem;text-align:center;">
      Preview goedgekeurd
    </h2>

    <hr style="margin:2rem 0;border:none;border-top:1px solid #21555820;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:0 0 14px 0;">
      <tr>
        <td valign="top" style="padding:0;">
          <div style="width:30px;height:30px;background-color:#ebf2f2;border-radius:999px;line-height:30px;text-align:center;">
            <img
              src="{{ $message->embed(public_path('assets/eazyonline/memojis/raphael.png')) }}"
              alt="Raphael Muskitta"
              width="25" height="25"
              style="display:inline-block;vertical-align:middle;border:0;outline:none;text-decoration:none;"
            >
          </div>
        </td>
        <td valign="top" style="padding:0 0 0 10px;">
          <div style="margin:0;color:#215558;font-weight:600;font-size:14px;line-height:16px;">
            Raphael Muskitta
          </div>
          <div style="margin:2px 0 0 0;color:#6b7d7f;font-weight:400;font-size:12px;line-height:14px;">
            Eigenaar Eazyonline
          </div>
        </td>
      </tr>
    </table>

    <p style="font-size:14px;color:#215558;margin:0 0 0.5rem 0;">
      @if(!empty($contactName))
        Beste {{ $contactName }},
      @else
        Beste {{ $company }},
      @endif
    </p>

    <p style="font-size:14px;color:#215558;margin:0 0 1.2rem 0;line-height:1.6;">
      Thanks voor je goedkeuring! Vanaf hier zetten we alles om naar een duidelijke offerte (incl. planning)
      en bereiden we de livegang netjes voor.
    </p>

    <div style="margin:0 0 1.2rem 0;padding:1rem;background-color:#ebf2f2;border-radius:14px;">
      <p style="margin:0 0 .75rem 0;font-size:14px;color:#215558;font-weight:700;">
        Wat er nu gaat gebeuren
      </p>

      <ol style="margin:0;padding-left:18px;font-size:14px;color:#215558;line-height:1.6;">
        <li style="margin:0 0 .5rem 0;">
          <strong>We bellen je om de preview door te nemen.</strong><br>
          We lopen alles kort langs en checken eventuele laatste wensen.
        </li>
        <li style="margin:0 0 .5rem 0;">
          <strong>Daarna ontvang je de offerte per mail.</strong><br>
          Met duidelijke scope, kosten en een planning richting livegang.
        </li>
        <li style="margin:0 0 .5rem 0;">
          <strong>Na akkoord krijg je de betaal-link / factuur.</strong><br>
          Veilig betalen via onze betaalomgeving (of factuur, afhankelijk van afspraak).
        </li>
        <li style="margin:0;">
          <strong>Na betaling starten we met de final build & livegang.</strong><br>
          Laatste details strakmaken en live op jouw domein.
        </li>
      </ol>
    </div>

    <div style="margin:0 0 1.2rem 0;padding:1rem;background-color:#ebf2f2;border-radius:14px;">
      <p style="margin:0 0 .5rem 0;font-size:14px;color:#215558;font-weight:700;">
        Goed om te weten
      </p>
      <p style="margin:0;font-size:14px;color:#215558;line-height:1.6;">
        Je zit nog nergens aan vast zonder dat alles zwart-op-wit in de offerte staat.
        Tijdens het belmoment checken we samen of alles klopt, zodat je precies weet waar je aan toe bent.
      </p>
    </div>

    <div style="margin:0 0 1.2rem 0;padding:1rem;background-color:#ebf2f2;border-radius:14px;">
      <p style="margin:0 0 .5rem 0;font-size:14px;color:#215558;font-weight:700;">
        Handig om alvast klaar te leggen
      </p>
      <ul style="margin:.25rem 0 0 0;padding-left:18px;font-size:14px;color:#215558;line-height:1.6;">
        <li>Laatste wensen of opmerkingen over de preview</li>
        <li>Logo/beelden (als die nog niet definitief zijn)</li>
        <li>Teksten of belangrijke info die op de site moet komen</li>
        <li>Domein/hosting gegevens (alleen als livegang op jouw domein direct gepland wordt)</li>
      </ul>
    </div>

    @if(!empty($previewLink))
      <div style="margin:2rem 0 0 0;">
        <a href="{{ $previewLink }}"
          style="display:inline-block;padding:0.75rem 1.5rem;background-color:#0F9B9F;text-decoration:none;color:#ffffff;font-size:1rem;font-weight:700;border-radius:999px;">
          Open de preview nog eens
        </a>
      </div>

      <p style="font-size:13px;color:#555;margin:1rem 0 0 0;">
        Werkt de knop niet? Open de preview dan via deze link:
        <br>
        <a href="{{ $previewLink }}" style="color:#21c2d3;text-decoration:underline;word-break:break-all;">
          {{ $previewLink }}
        </a>
      </p>
    @endif

    <hr style="margin:2rem 0;border:none;border-top:1px solid #21555820;">

    <p style="font-size:13px;color:#215558;margin:0 0 1rem 0;text-align:center;">
      Wil je vóór het belmoment alvast iets doorgeven? Reageer op deze e-mail of mail naar
      <a href="mailto:info@eazyonline.nl" style="color:#21c2d3;text-decoration:underline;">info@eazyonline.nl</a>.
    </p>

    <p style="font-size:12px;color:#21555880;margin:0;text-align:center;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>