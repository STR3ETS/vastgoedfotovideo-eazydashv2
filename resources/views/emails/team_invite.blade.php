@php
  /** @var \App\Models\TeamInvite $invite */
  /** @var string $acceptUrl */
  $company = $invite->company;
  $lang = str_replace('_', '-', app()->getLocale() ?? 'nl');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('instellingen.invite.email.subject', ['company' => $company->name]) }}</title>
</head>
<body style="margin:0; padding:0; font-family: 'Inter Tight', Arial, Helvetica, sans-serif; background-color:#f7f9fa;">
  <div style="max-width:600px; margin:0 auto; padding:2rem; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.05);">
    <h2 style="color:#21c2d3; font-weight:bold; font-size:24px; margin-bottom:0.5rem;">
      {{ __('instellingen.invite.email.subject', ['company' => $company->name]) }}
    </h2>

    <p style="font-size:16px; color:#333; margin:0 0 1rem 0;">
      {{ __('instellingen.invite.email.intro', ['company' => $company->name]) }}
    </p>

    <div style="text-align:center; margin:2rem 0;">
      <a href="{{ $acceptUrl }}"
         style="display:inline-block; padding:1rem 2rem; background-color:#21c2d3; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:10px;">
         {{ __('instellingen.invite.email.cta') }}
      </a>
    </div>

    {{-- Fallback link voor clients die knoppen blokkeren --}}
    <p style="font-size:13px; color:#555; margin:0 0 1.25rem 0;">
      {{ __('instellingen.invite.email.cta') }}:
      <br>
      <a href="{{ $acceptUrl }}" style="color:#21c2d3; text-decoration:underline; word-break:break-all;">
        {{ $acceptUrl }}
      </a>
    </p>

    <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

    <p style="font-size:12px; color:#999; margin:0;">
      {{ __('instellingen.invite.email.footer_days', ['days' => 7]) }}
    </p>

    <p style="font-size:12px; color:#aaa; margin:0.75rem 0 0 0;">
      © 2025 EazyOnline – Alle rechten voorbehouden.
    </p>
  </div>
</body>
</html>
