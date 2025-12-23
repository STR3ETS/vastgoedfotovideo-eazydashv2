@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Je preview van Eazyonline staat klaar</title>
</head>
<body style="margin:0; padding:0; font-family:'Inter Tight', Arial, Helvetica, sans-serif; background-color:#ebf2f2;padding-top:1rem;padding-bottom:1rem;">
  <div style="max-width:600px; margin:0 auto; padding:2rem; background-color:#ffffff; border: 1px solid #E5E7EB; border-radius:2rem; box-shadow:0 0 10px rgba(0,0,0,0.05);">

    <img
      src="{{ $message->embed(public_path('assets/logo.webp')) }}"
      alt="Eazyonline team"
      style="display:block;max-width:25px;margin:0 auto;border:0;margin-bottom:1rem;"
    >
    <img
      src="{{ $message->embed(public_path('assets/memoji-row.png')) }}"
      alt="Eazyonline team"
      style="display:block;width:80%;max-width:325px;margin:0 auto;border:0;"
    >
    <h2 style="color:#21c2d3; font-weight:extrabold; font-size:28px; margin-top:0.5rem;text-align:center;">
      Je preview staat voor je klaar
    </h2>

    <hr style="margin:2rem 0; border:none; border-top:1px solid #21555820;">

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

    <p style="font-size:14px; color:#215558; margin:0 0 0.5rem 0;">
      @if($contactName)
        Beste {{ $contactName }},
      @else
        Beste {{ $company }},
      @endif
    </p>

    <p style="font-size:14px; color:#215558; margin:0 0 1.2rem 0;">
      Super tof dat je voor Eazyonline hebt gekozen. We hebben je website-preview net voor je klaargezet.
      Via de knop hieronder kun je ’m rustig bekijken en meteen je feedback of goedkeuring doorgeven.
    </p>

    <p style="font-size:14px; color:#215558; margin:0 0 1rem 0; padding:0.5rem;border-radius:10px;background-color:#ebf2f2;width:fit-content;">
      <strong>Tip vanuit mij:</strong> Bekijk jouw persoonlijke preview ook even op de mobiel.
    </p>

    <div style="margin:2rem 0;">
      <a href="{{ $klantUrl }}"
        style="display:inline-block; padding:0.75rem 1.5rem; background-color:#0F9B9F; text-decoration:none; color:#ffffff; font-size:1rem; font-weight:700; border-radius:999px;">
        Bekijk jouw persoonlijke preview hier
      </a>
    </div>

    <p style="font-size:13px; color:#555; margin:0 0 1.25rem 0;">
      Werkt de knop niet? Open de preview dan via deze link:
      <br>
      <a href="{{ $klantUrl }}" style="color:#21c2d3; text-decoration:underline; word-break:break-all;">
        {{ $klantUrl }}
      </a>
    </p>

    <hr style="margin:2rem 0; border:none; border-top:1px solid #21555820;">

    <p style="font-size:12px; color:#21555880; margin:0; text-align:center;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>
