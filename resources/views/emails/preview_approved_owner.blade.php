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
      De klant heeft zojuist de preview goedgekeurd.
    </p>

    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;">
        <strong>Project:</strong> {{ $company }}
      </p>

      @if(!empty($approvedAt))
        <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;">
          <strong>Wanneer:</strong> {{ $approvedAt }} (Europe/Amsterdam)
        </p>
      @endif

      @if(!empty($approvedIp))
        <p style="margin:0; font-size:14px; color:#111;">
          <strong>IP:</strong> {{ $approvedIp }}
        </p>
      @endif
    </div>

    <div style="margin:1.25rem 0; padding:1rem; background:#f7f9fa; border:1px solid #e9eef2; border-radius:10px;">
      <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;"><strong>Contactpersoon:</strong>
        {{ $contactName ?: '—' }}
      </p>
      <p style="margin:0 0 .5rem 0; font-size:14px; color:#111;"><strong>E-mailadres:</strong>
        {{ $contactEmail ?: '—' }}
      </p>
      <p style="margin:0; font-size:14px; color:#111;"><strong>Telefoonnummer:</strong>
        {{ $contactPhone ?: '—' }}
      </p>
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

    <p style="font-size:12px; color:#aaa; margin:0;">
      © {{ date('Y') }} Eazyonline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>
