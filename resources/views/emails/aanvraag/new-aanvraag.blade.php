@php
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
  <meta charset="UTF-8">
  <title>Nieuwe aanvraag</title>
</head>
<body style="margin:0;padding:0;font-family:'Inter Tight', Arial, Helvetica, sans-serif;background-color:#ebf2f2;padding-top:1rem;padding-bottom:1rem;">
  <div style="max-width:600px;margin:0 auto;padding:2rem;background-color:#ffffff;border:1px solid #E5E7EB;border-radius:2rem;box-shadow:0 0 10px rgba(0,0,0,0.05);">

    <img
      src="{{ $message->embed(public_path('assets/logo.webp')) }}"
      alt="Eazyonline"
      width="25"
      height="25"
      style="display:block;width:25px;height:25px;margin:0 auto;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;margin-bottom:16px;"
    />

    <img
      src="{{ $message->embed(public_path('assets/memoji-row.png')) }}"
      alt="Eazyonline team"
      style="display:block;width:80%;max-width:325px;margin:0 auto;border:0;"
    >

    <h2 style="color:#21c2d3; font-weight:extrabold; font-size:28px; margin-top:0.5rem;text-align:center;">
      Nieuwe aanvraag binnengekomen
    </h2>

    <hr style="margin:2rem 0;border:none;border-top:1px solid #21555820;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;margin:0 0 14px 0;">
      <tr>
        <td valign="top" style="padding:0;">
          <div style="width:30px;height:30px;background-color:#ebf2f2;border-radius:999px;line-height:30px;text-align:center;">
            <img
              src="{{ $message->embed(public_path('assets/eazyonline/memojis/izzy.webp')) }}"
              alt="Izzy"
              width="25" height="25"
              style="display:inline-block;vertical-align:middle;border:0;outline:none;text-decoration:none;"
            >
          </div>
        </td>
        <td valign="top" style="padding:0 0 0 10px;">
          <div style="margin:0;color:#215558;font-weight:600;font-size:14px;line-height:16px;">
            Izzy
          </div>
          <div style="margin:2px 0 0 0;color:#6b7d7f;font-weight:400;font-size:12px;line-height:14px;">
            Assistente
          </div>
        </td>
      </tr>
    </table>

    <p style="font-size:14px;color:#215558;margin:0 0 1.2rem 0;line-height:1.6;">
      Er is zojuist een nieuwe website-aanvraag binnengekomen. Hieronder staan de belangrijkste gegevens.
    </p>

    <div style="margin:0 0 1.2rem 0;padding:1rem;background-color:#ebf2f2;border-radius:14px;">
      <p style="margin:0;font-size:14px;color:#215558;line-height:1.8;">
        <strong>Bedrijfsnaam:</strong> {{ $company ?: ('Aanvraag #' . $aanvraagId) }}<br>
        <strong>Wanneer:</strong> {{ $createdAt }} (Europe/Amsterdam)
      </p>
    </div>

    <div style="margin:0 0 1.2rem 0;padding:1rem;background-color:#ebf2f2;border-radius:14px;">
      <p style="margin:0;font-size:14px;color:#215558;line-height:1.8;">
        <strong>Naam:</strong> {{ $aanvraag->contactName ?? 'Onbekend' }}<br>
        <strong>Email:</strong> {{ $aanvraag->contactEmail ?? 'Onbekend' }}<br>
        <strong>Telefoon:</strong> {{ $aanvraag->contactPhone ?? 'Onbekend' }}
      </p>
    </div>

    @if(!empty($aanvraagLink))
      <div style="margin:2rem 0 0 0;">
        <a href="{{ $aanvraagLink }}"
          style="display:inline-block;padding:0.75rem 1.5rem;background-color:#0F9B9F;text-decoration:none;color:#ffffff;font-size:1rem;font-weight:700;border-radius:999px;">
          Open de aanvraag
        </a>
      </div>

      <p style="font-size:13px;color:#555;margin:1rem 0 0 0;">
        Werkt de knop niet? Open de aanvraag via deze link:
        <br>
        <a href="{{ $aanvraagLink }}" style="color:#21c2d3;text-decoration:underline;word-break:break-all;">
          {{ $aanvraagLink }}
        </a>
      </p>
    @endif

    <hr style="margin:2rem 0;border:none;border-top:1px solid #21555820;">

    <p style="font-size:13px;color:#215558;margin:0 0 1rem 0;text-align:center;">
      Tip: pak ‘m direct op in de funnel zodat we snel kunnen schakelen.
    </p>

    <p style="font-size:12px;color:#21555880;margin:0;text-align:center;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>
