<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $reference }} no VerseHub</title>
    <meta name="theme-color" content="#165c49">
    <style>
        :root { color-scheme: light; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; min-height: 100dvh; display: grid; place-items: center; background: #f4efe3; color: #29251f; padding: max(24px, env(safe-area-inset-top)) 20px max(24px, env(safe-area-inset-bottom)); }
        main { width: min(100%, 520px); background: #fbf8ef; border: 1px solid #d8cfbe; border-radius: 28px; padding: 32px; box-shadow: 0 16px 45px rgba(52,45,35,.12); }
        .mark { width: 52px; height: 52px; display: grid; place-items: center; border-radius: 50%; color: #f0dfb8; background: #0d4436; font: 700 24px Georgia, serif; }
        h1 { margin: 24px 0 8px; font: 700 clamp(28px, 8vw, 40px)/1.05 Georgia, serif; }
        p { color: #6f685d; line-height: 1.6; }
        .ref { color: #165c49; font-weight: 800; letter-spacing: .04em; }
        a { min-height: 52px; display: flex; align-items: center; justify-content: center; margin-top: 24px; border-radius: 999px; background: #165c49; color: #fffdf7; font-weight: 800; text-decoration: none; }
        small { display: block; margin-top: 16px; color: #6f685d; text-align: center; }
    </style>
</head>
<body>
<main>
    <div class="mark">V</div>
    <h1>Abra este versículo no VerseHub</h1>
    <p class="ref">{{ $reference }} · {{ $version }}</p>
    <p>Continue a leitura, registre como a Palavra encontrou você e veja o que a comunidade sentiu.</p>
    <a id="open-app" href="{{ $appUrl }}">Abrir no aplicativo</a>
    <small>Se o app não abrir, instale ou atualize o VerseHub e tente novamente.</small>
</main>
<script>
    const target = @json($appUrl);
    document.getElementById('open-app').addEventListener('click', () => {
        window.location.href = target;
    });
</script>
</body>
</html>
