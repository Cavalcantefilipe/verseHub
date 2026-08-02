<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Suporte — VerseHub</title>
    <meta name="description" content="Ajuda, contato e informações de suporte do aplicativo VerseHub.">
    <meta name="theme-color" content="#155a48">
    <style>
        :root { --paper:#f5f0e6; --raised:#fcf9f2; --ink:#25231e; --soft:#6c675e; --forest:#155a48; --line:#d9d0bf; --gold:#c69231; }
        * { box-sizing: border-box; }
        body { margin:0; background:var(--paper); color:var(--ink); font-family:Inter,ui-sans-serif,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; line-height:1.65; }
        a { color:var(--forest); }
        .shell { width:min(760px,calc(100% - 32px)); margin:0 auto; padding:40px 0 max(72px,env(safe-area-inset-bottom)); }
        .brand { display:inline-flex; align-items:center; gap:11px; color:var(--ink); text-decoration:none; font-weight:850; }
        .brand img { width:42px; height:42px; border-radius:12px; }
        header { padding-top:max(24px,env(safe-area-inset-top)); }
        main { margin-top:48px; }
        .eyebrow { color:var(--forest); font-size:12px; font-weight:900; letter-spacing:.13em; text-transform:uppercase; }
        h1,h2 { font-family:Georgia,"Times New Roman",serif; }
        h1 { margin:12px 0 14px; font-size:clamp(42px,8vw,64px); line-height:1; letter-spacing:-.045em; }
        .lead { margin:0 0 34px; color:var(--soft); font-size:18px; }
        .card { padding:30px; margin-top:16px; border:1px solid var(--line); border-radius:24px; background:var(--raised); }
        .card h2 { margin:0 0 10px; font-size:24px; }
        .card p { margin:0; color:var(--soft); }
        .button { display:inline-flex; min-height:50px; align-items:center; justify-content:center; margin-top:18px; padding:0 22px; border-radius:999px; background:var(--forest); color:#fff; font-weight:850; text-decoration:none; }
        .links { display:flex; flex-wrap:wrap; gap:16px; margin-top:32px; font-size:14px; font-weight:750; }
        .note { margin-top:36px; padding-top:22px; border-top:1px solid var(--line); color:var(--soft); font-size:13px; }
        :focus-visible { outline:3px solid var(--gold); outline-offset:4px; }
    </style>
</head>
<body>
<div class="shell">
    <header><a class="brand" href="/"><img src="{{ asset('assets/versehub-icon.png') }}" alt="" width="42" height="42"><span>VerseHub</span></a></header>
    <main>
        <span class="eyebrow">Central de ajuda</span>
        <h1>Como podemos ajudar?</h1>
        <p class="lead">Para dúvidas, problemas de acesso, denúncias ou solicitações relacionadas aos seus dados, fale diretamente com a equipe do VerseHub.</p>
        <section class="card">
            <h2>Fale com o suporte</h2>
            <p>Descreva o que aconteceu, o modelo do aparelho e a versão do sistema. Não envie senha, código de acesso ou informação bancária.</p>
            <a class="button" href="mailto:contato@filipelab.com?subject=Suporte%20VerseHub">Enviar e-mail</a>
        </section>
        <section class="card">
            <h2>Conta e dados</h2>
            <p>Você pode editar seus dados no perfil e iniciar a exclusão permanente da conta diretamente no app. Se não conseguir entrar, use o canal de suporte.</p>
            <a class="button" href="/delete-account">Ver como excluir a conta</a>
        </section>
        <section class="card">
            <h2>Conteúdo e comunidade</h2>
            <p>Use a opção de denúncia no próprio aplicativo para sinalizar conteúdo inadequado. Nossa equipe pode remover conteúdo ou restringir contas que violem os Termos de Uso.</p>
        </section>
        <nav class="links" aria-label="Informações legais"><a href="/privacy">Política de Privacidade</a><a href="/terms">Termos de Uso</a><a href="/delete-account">Exclusão de conta</a><a href="/">Página inicial</a></nav>
        <p class="note">Contato responsável: F A CAVALCANTE · <a href="mailto:contato@filipelab.com">contato@filipelab.com</a></p>
    </main>
</div>
</body>
</html>
