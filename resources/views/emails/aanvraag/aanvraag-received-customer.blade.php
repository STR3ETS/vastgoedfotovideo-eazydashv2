@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Aanvraag ontvangen</title>
</head>
<body style="margin:0;padding:0;font-family:'Inter Tight', Arial, Helvetica, sans-serif;background-color:#ebf2f2;padding-top:1rem;padding-bottom:1rem;">
  <div style="max-width:600px;margin:0 auto;padding:2rem;background-color:#ffffff;border:1px solid #E5E7EB;border-radius:2rem;box-shadow:0 0 10px rgba(0,0,0,0.05);">

    {{-- Outlook-safe sizing: gebruik width attribute i.p.v. max-width --}}
    <img
      src="{{ $message->embed(public_path('assets/logo.webp')) }}"
      alt="Eazyonline"
      width="25"
      style="display:block;width:25px;height:auto;margin:0 auto;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;margin-bottom:1rem;"
    >

    <img
      src="{{ $message->embed(public_path('assets/memoji-row.png')) }}"
      alt="Eazyonline team"
      width="325"
      style="display:block;width:80%;max-width:325px;height:auto;margin:0 auto;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;"
    >

    <h2 style="color:#21c2d3; font-weight:extrabold; font-size:28px; margin-top:0.5rem;text-align:center;">
      Aanvraag ontvangen
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
              style="display:inline-block;vertical-align:middle;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;"
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
      Thanks! We hebben je aanvraag goed ontvangen. Hieronder zie je wat er nu gaat gebeuren.
    </p>

    <div style="margin:0 0 1.2rem 0;padding:1rem;background-color:#ebf2f2;border-radius:14px;">
      <p style="margin:0 0 .75rem 0;font-size:14px;color:#215558;font-weight:700;">
        Wat er nu gaat gebeuren
      </p>

      <ol style="margin:0;padding-left:18px;font-size:14px;color:#215558;line-height:1.6;">
        <li style="margin:0 0 .5rem 0;">
          <strong>We nemen contact met je op.</strong><br>
          {{ $pickupText }}
        </li>
        <li style="margin:0 0 .5rem 0;">
          <strong>We plannen een intakegesprek in.</strong><br>
          In dat gesprek lopen we je wensen door en bepalen we samen de scope en prioriteiten.
        </li>
        <li style="margin:0;">
          <strong>Daarna zetten we alles om naar een concreet plan.</strong><br>
          Je krijgt duidelijke vervolgstappen (en indien nodig een voorstel/offerte).
        </li>
      </ol>
    </div>

    <hr style="margin:2rem 0;border:none;border-top:1px solid #21555820;">

    <p style="font-size:13px;color:#215558;margin:0 0 1rem 0;text-align:center;">
      Vragen of opmerking? Wij zijn van maandag t/m vrijdag 09:00-17:00 bereikbaar op
      <a href="mailto:info@eazyonline.nl" style="color:#21c2d3;text-decoration:underline;">info@eazyonline.nl</a>.
    </p>

    <p style="font-size:12px;color:#21555880;margin:0;text-align:center;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>

  </div>
</body>
</html>
