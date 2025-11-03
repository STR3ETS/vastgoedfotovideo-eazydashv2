<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Jouw Eazy login code</title>
</head>
<body style="margin:0; padding:0; font-family: 'Inter Tight', sans-serif; background-color:#f7f9fa;">
    <div style="max-width:600px; margin:0 auto; padding:2rem; background-color:#ffffff; border-radius:10px; box-shadow:0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color:#21c2d3; font-weight:bold; font-size:24px; margin-bottom:0.5rem;">
            EazyOnline <span style="color:#191919;"></span>
        </h2>
        <p style="font-size:16px; color:#333;">
            Je probeert in te loggen bij <strong>EazyOnline</strong>. Gebruik onderstaande code om toegang te krijgen:
        </p>

        <div style="text-align:center; margin:2rem 0;">
            <div style="display:inline-block; padding:1rem 2rem; background-color:#21c2d3; color:white; font-size:28px; font-weight:bold; letter-spacing:6px; border-radius:10px;">
                {{ $token }}
            </div>
        </div>

        <p style="font-size:14px; color:#555;">
            Deze code is 15 minuten geldig. Heb je dit niet zelf aangevraagd? Dan kun je deze mail veilig negeren.
        </p>

        <hr style="margin:2rem 0; border:none; border-top:1px solid #eee;">

        <p style="font-size:12px; color:#aaa;">
            &copy; {{ date('Y') }} EazyOnline – Alle rechten voorbehouden.
        </p>
    </div>
</body>
</html>
