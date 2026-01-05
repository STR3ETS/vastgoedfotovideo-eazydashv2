<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Jouw inlogcode</title>
</head>
<body style="margin:0;padding:0;background:#F5EFED;font-family:'Inter Tight', Arial, Helvetica, sans-serif;">
  <div style="width:100%;padding:50px 14px;">
    <div style="max-width:600px;margin:0 auto;">
      <!-- Logo -->
      <div style="padding:0 6px 14px 6px;">
        <img src="{{ $message->embed(public_path('assets/vastgoedfotovideo/logo-full.png')) }}"
             alt="Vastgoed Foto Video"
             style="height:34px;max-width:100%;display:block;">
      </div>
      <!-- Card -->
      <div style="background:#ffffff;border-radius:32px;overflow:hidden;">
        <!-- Header -->
        <div style="padding:28px 28px 0 28px;">
          <h1 style="margin:0;color:#191D38;font-size:32px;line-height:1.15;font-weight:900;letter-spacing:-2px;">
            Je probeert in te loggen.
          </h1>
          <p style="margin:14px 0 14px 0;color:rgba(25,29,56,0.75);font-size:16px;line-height:1.6;font-weight:600;">
            Onderstaand is de code die je kan gebruiken om in te loggen in jouw VastgoedFotoVideo omgeving. <span style="opacity:50%;">Heb je deze code niet aangevraagd? Negeer deze e-mail dan veilig.</span>
          </p>
        </div>
        <!-- Code block -->
        <div style="padding:22px 28px 0 28px;">
          <div style="display:inline-block;background:#009AC3;color:#fff;border-radius:12px;padding:16px 14px 16px 22px;
                      font-size:30px;font-weight:900;letter-spacing:10px;line-height:1;box-shadow:0 10px 20px rgba(0,154,195,0.22);">
            {{ $token }}
          </div>

          <p style="margin:18px 0 0 0;color:rgba(25,29,56,0.65);font-size:13px;line-height:1.6;font-weight:600;opacity:50%;">
            Deze code is 15 minuten geldig.
          </p>
        </div>
        <!-- Footer -->
        <div style="padding:24px 28px 26px 28px;">
          <div style="height:1px;background:rgba(25,29,56,0.08);margin:0 0 14px 0;"></div>

          <p style="margin:0;color:#191D38;font-size:12px;line-height:1.6;font-weight:600;">
            &copy; {{ date('Y') }} Vastgoed Foto Video – Alle rechten voorbehouden.
          </p>
        </div>
      </div>
      <!-- Small spacing -->
      <div style="height:10px;"></div>
    </div>
  </div>
</body>
</html>
