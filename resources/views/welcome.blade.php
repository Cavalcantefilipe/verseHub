<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>VerseHub — a Palavra encontra a sua história</title>
    <meta name="description" content="Leia a Bíblia, registre o que um versículo despertou em você e compartilhe descobertas com uma comunidade cristã.">
    <meta name="theme-color" content="#123f34">
    <meta name="color-scheme" content="light dark">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="VerseHub — a Palavra encontra a sua história">
    <meta property="og:description" content="Leitura bíblica, sentimentos, comunidade e uma jornada de fé que cresce com você.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('assets/versehub-icon.png') }}">
    <meta name="twitter:card" content="summary">
    <link rel="canonical" href="{{ url('/') }}">
    <link rel="icon" href="{{ asset('assets/versehub-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/versehub-icon.png') }}">
    <style>
        :root {
            color-scheme: light;
            --paper: #f5f0e6;
            --paper-raised: #fcf9f2;
            --ink: #25231e;
            --ink-soft: #6c675e;
            --forest: #155a48;
            --forest-deep: #0c3e32;
            --forest-soft: #dce9df;
            --gold: #c69231;
            --gold-soft: #f1dfb6;
            --line: #d9d0bf;
            --white: #fffdf8;
            --shadow: 0 28px 80px rgba(49, 42, 31, .14);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--paper);
            color: var(--ink);
            font-family: Inter, ui-sans-serif, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; }
        img { max-width: 100%; display: block; }
        button, a { -webkit-tap-highlight-color: transparent; }
        .shell { width: min(1180px, calc(100% - 40px)); margin: 0 auto; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            background: color-mix(in srgb, var(--paper) 88%, transparent);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid color-mix(in srgb, var(--line) 72%, transparent);
            padding-top: env(safe-area-inset-top);
        }
        .nav { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: inline-flex; align-items: center; gap: 12px; text-decoration: none; font-weight: 850; letter-spacing: -.02em; }
        .brand img { width: 42px; height: 42px; border-radius: 12px; box-shadow: 0 8px 22px rgba(13, 68, 54, .22); }
        .nav-links { display: flex; align-items: center; gap: 26px; color: var(--ink-soft); font-size: 14px; font-weight: 700; }
        .nav-links a { text-decoration: none; }
        .nav-links a:hover { color: var(--forest); }
        .nav-cta { padding: 11px 18px; border-radius: 999px; background: var(--forest); color: var(--white) !important; }
        .hero { position: relative; overflow: hidden; padding: 92px 0 86px; }
        .hero::before {
            content: "";
            position: absolute;
            width: 680px;
            height: 680px;
            right: -250px;
            top: -250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(198, 146, 49, .22), rgba(198, 146, 49, 0) 70%);
            pointer-events: none;
        }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1.06fr) minmax(340px, .94fr); align-items: center; gap: 82px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 9px; color: var(--forest); font-size: 12px; font-weight: 900; letter-spacing: .13em; text-transform: uppercase; }
        .eyebrow::before { content: ""; width: 28px; height: 2px; background: var(--gold); }
        h1, h2, h3, blockquote { font-family: Georgia, "Times New Roman", serif; }
        h1 { margin: 24px 0 22px; max-width: 760px; font-size: clamp(48px, 7vw, 82px); line-height: .98; letter-spacing: -.055em; font-weight: 700; }
        h1 em { color: var(--forest); font-style: normal; }
        .hero-copy > p { max-width: 650px; margin: 0; color: var(--ink-soft); font-size: clamp(18px, 2vw, 21px); }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 34px; }
        .button { min-height: 52px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 0 23px; border-radius: 999px; font-weight: 850; text-decoration: none; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; }
        .button:hover { transform: translateY(-2px); }
        .button-primary { background: var(--forest); color: var(--white); box-shadow: 0 12px 28px rgba(21, 90, 72, .22); }
        .button-secondary { border: 1px solid var(--line); background: var(--paper-raised); }
        .availability { display: flex; flex-wrap: wrap; gap: 14px 24px; margin-top: 24px; color: var(--ink-soft); font-size: 13px; font-weight: 750; }
        .availability span { display: inline-flex; align-items: center; gap: 7px; }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--gold); box-shadow: 0 0 0 5px var(--gold-soft); }
        .phone-stage { position: relative; min-height: 650px; display: grid; place-items: center; }
        .phone-stage::after { content: ""; position: absolute; width: 440px; height: 440px; border-radius: 50%; background: var(--forest-soft); z-index: -1; }
        .phone {
            width: min(360px, 82vw);
            border: 9px solid #111815;
            border-radius: 48px;
            background: #f7f1e5;
            box-shadow: var(--shadow);
            overflow: hidden;
            transform: rotate(2.5deg);
        }
        .phone-top { width: 37%; height: 26px; margin: 0 auto; background: #111815; border-radius: 0 0 18px 18px; }
        .app-screen { padding: 24px 22px 26px; }
        .app-kicker { color: var(--forest); font-size: 10px; font-weight: 900; letter-spacing: .14em; text-transform: uppercase; }
        .app-title { margin: 6px 0 26px; font: 700 30px/1.05 Georgia, serif; }
        .verse-card { padding: 24px; border: 1px solid #d7cdbb; border-radius: 24px; background: #fffaf1; }
        .verse-card blockquote { margin: 0; font-size: 22px; line-height: 1.43; }
        .verse-ref { margin-top: 18px; color: var(--forest); font-size: 12px; font-weight: 900; letter-spacing: .08em; }
        .feeling-title { margin: 26px 0 12px; font: 700 17px Georgia, serif; }
        .chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .chip { padding: 8px 11px; border-radius: 999px; border: 1px solid #d7cdbb; background: #f9f3e7; color: #625d53; font-size: 11px; font-weight: 800; }
        .chip.active { color: #fff; background: var(--forest); border-color: var(--forest); }
        .mini-nav { display: grid; grid-template-columns: repeat(4, 1fr); margin-top: 28px; padding-top: 16px; border-top: 1px solid #ddd3c2; color: #817b70; font-size: 9px; text-align: center; font-weight: 800; }
        .mini-nav b { color: var(--forest); }
        .section { padding: 100px 0; }
        .section-soft { background: var(--paper-raised); border-block: 1px solid var(--line); }
        .section-head { max-width: 730px; margin-bottom: 48px; }
        .section h2 { margin: 14px 0 14px; font-size: clamp(38px, 5vw, 58px); line-height: 1.05; letter-spacing: -.04em; }
        .section-head p { margin: 0; color: var(--ink-soft); font-size: 18px; }
        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .feature { min-height: 260px; padding: 30px; border: 1px solid var(--line); border-radius: 28px; background: var(--paper-raised); }
        .feature-number { color: var(--gold); font: 700 15px Georgia, serif; }
        .feature h3 { margin: 52px 0 10px; font-size: 25px; line-height: 1.15; }
        .feature p { margin: 0; color: var(--ink-soft); }
        .screens { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; align-items: start; }
        .screen-card { position: relative; padding: 14px; border-radius: 34px; background: #111815; box-shadow: 0 24px 60px rgba(49, 42, 31, .13); }
        .screen-card:nth-child(2) { margin-top: 52px; }
        .screen-card .screen { min-height: 570px; overflow: hidden; border-radius: 24px; background: #f5f0e6; padding: 28px 20px; }
        .screen-card.dark .screen { background: #171c1a; color: #f6f0e4; }
        .screen-label { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; color: var(--ink-soft); font-size: 11px; font-weight: 900; letter-spacing: .1em; text-transform: uppercase; }
        .dark .screen-label { color: #bcb3a5; }
        .mock-heading { margin: 0 0 22px; font-size: 28px; line-height: 1.1; }
        .mock-card { margin-bottom: 12px; padding: 18px; border: 1px solid #d8cfbe; border-radius: 19px; background: #fbf8ef; }
        .dark .mock-card { border-color: #3c443f; background: #202723; }
        .mock-ref { color: var(--forest); font-size: 10px; font-weight: 900; letter-spacing: .08em; }
        .dark .mock-ref { color: #73b69a; }
        .mock-card p { margin: 8px 0 0; font: 16px/1.45 Georgia, serif; }
        .community-meta { display: flex; justify-content: space-between; margin-top: 14px; color: var(--ink-soft); font-size: 10px; font-weight: 750; }
        .dark .community-meta { color: #bcb3a5; }
        .profile-hero { text-align: center; padding: 8px 0 24px; }
        .avatar { width: 74px; height: 74px; display: grid; place-items: center; margin: 0 auto 13px; border-radius: 50%; background: var(--forest-soft); color: var(--forest); font: 700 26px Georgia, serif; }
        .profile-hero h3 { margin: 0; font-size: 22px; }
        .profile-hero p { margin: 4px 0 0; color: var(--ink-soft); font-size: 12px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .stat { padding: 14px 6px; border: 1px solid var(--line); border-radius: 16px; text-align: center; }
        .stat strong { display: block; color: var(--forest); font: 700 20px Georgia, serif; }
        .stat small { color: var(--ink-soft); font-size: 9px; font-weight: 800; text-transform: uppercase; }
        .level { margin-top: 20px; padding: 18px; border-radius: 18px; background: var(--forest); color: var(--white); }
        .level-top { display: flex; justify-content: space-between; font-size: 12px; font-weight: 850; }
        .progress { height: 7px; margin-top: 12px; border-radius: 999px; background: rgba(255,255,255,.22); overflow: hidden; }
        .progress span { display: block; width: 68%; height: 100%; background: var(--gold-soft); border-radius: inherit; }
        .trust-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 70px; align-items: center; }
        .quote-card { padding: 44px; border-radius: 32px; background: var(--forest-deep); color: var(--white); }
        .quote-card blockquote { margin: 0; font-size: clamp(28px, 4vw, 42px); line-height: 1.22; }
        .quote-card p { margin: 20px 0 0; color: #cfe0d8; }
        .check-list { display: grid; gap: 16px; }
        .check { display: grid; grid-template-columns: 38px 1fr; gap: 14px; align-items: start; }
        .check-icon { width: 38px; height: 38px; display: grid; place-items: center; border-radius: 50%; background: var(--forest-soft); color: var(--forest); font-weight: 950; }
        .check h3 { margin: 2px 0 3px; font: 800 16px/1.3 Inter, sans-serif; }
        .check p { margin: 0; color: var(--ink-soft); font-size: 14px; }
        .cta { padding: 92px 0; }
        .cta-card { position: relative; overflow: hidden; padding: 64px; border-radius: 38px; background: var(--forest); color: var(--white); }
        .cta-card::after { content: "V"; position: absolute; right: -18px; bottom: -100px; color: rgba(255,255,255,.06); font: 700 330px/1 Georgia, serif; }
        .cta-card h2 { position: relative; z-index: 1; max-width: 720px; margin: 0 0 18px; font-size: clamp(38px, 6vw, 64px); line-height: 1; }
        .cta-card p { position: relative; z-index: 1; max-width: 650px; color: #d3e2dc; font-size: 18px; }
        .cta-card .button { position: relative; z-index: 1; margin-top: 16px; background: var(--gold-soft); color: var(--forest-deep); }
        footer { padding: 38px 0 max(38px, env(safe-area-inset-bottom)); border-top: 1px solid var(--line); }
        .footer-grid { display: flex; align-items: center; justify-content: space-between; gap: 24px; color: var(--ink-soft); font-size: 13px; }
        .footer-links { display: flex; flex-wrap: wrap; gap: 18px; }
        .footer-links a { text-decoration: none; }
        .footer-note { max-width: 600px; margin-top: 18px; color: var(--ink-soft); font-size: 12px; }
        :focus-visible { outline: 3px solid var(--gold); outline-offset: 4px; }
        @media (max-width: 900px) {
            .hero { padding-top: 64px; }
            .hero-grid, .trust-grid { grid-template-columns: 1fr; }
            .hero-grid { gap: 34px; }
            .phone-stage { min-height: 570px; }
            .feature-grid, .screens { grid-template-columns: 1fr; }
            .feature { min-height: auto; }
            .feature h3 { margin-top: 28px; }
            .screen-card { width: min(100%, 390px); margin-inline: auto; }
            .screen-card:nth-child(2) { margin-top: 0; }
            .nav-links a:not(.nav-cta) { display: none; }
        }
        @media (max-width: 560px) {
            .shell { width: min(100% - 28px, 1180px); }
            .nav { min-height: 68px; }
            .brand img { width: 38px; height: 38px; }
            .nav-cta { padding: 9px 14px; font-size: 12px; }
            .hero { padding: 52px 0 58px; }
            .hero-grid { gap: 12px; }
            h1 { font-size: 48px; }
            .phone-stage { min-height: 515px; }
            .phone { width: 300px; }
            .app-screen { padding: 19px 17px 21px; }
            .verse-card blockquote { font-size: 19px; }
            .section { padding: 72px 0; }
            .screen-card .screen { min-height: 520px; }
            .quote-card, .cta-card { padding: 34px 26px; border-radius: 28px; }
            .footer-grid { align-items: flex-start; flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; }
        }
    </style>
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "VerseHub",
        "applicationCategory": "ReferenceApplication",
        "operatingSystem": "iOS, Android",
        "url": "{{ url('/') }}",
        "offers": { "@@type": "Offer", "price": "0", "priceCurrency": "BRL" }
    }
    </script>
</head>
<body>
<header class="topbar">
    <nav class="nav shell" aria-label="Navegação principal">
        <a class="brand" href="/" aria-label="VerseHub, página inicial">
            <img src="{{ asset('assets/versehub-icon.png') }}" alt="" width="42" height="42">
            <span>VerseHub</span>
        </a>
        <div class="nav-links">
            <a href="#recursos">Recursos</a>
            <a href="#telas">Conheça o app</a>
            <a href="/support">Suporte</a>
            <a class="nav-cta" href="mailto:contato@filipelab.com?subject=Quero%20testar%20o%20VerseHub">Quero testar</a>
        </div>
    </nav>
</header>

<main>
    <section class="hero">
        <div class="hero-grid shell">
            <div class="hero-copy">
                <span class="eyebrow">Sua jornada com a Palavra</span>
                <h1>A Bíblia encontra o que você está <em>vivendo.</em></h1>
                <p>Leia com calma, registre o que cada versículo despertou em você e descubra como a Palavra também alcançou outras pessoas.</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="#telas">Conhecer o VerseHub <span aria-hidden="true">↓</span></a>
                    <a class="button button-secondary" href="mailto:contato@filipelab.com?subject=Quero%20participar%20dos%20testes%20do%20VerseHub">Participar dos testes</a>
                </div>
                <div class="availability" aria-label="Disponibilidade do aplicativo">
                    <span><i class="status-dot" aria-hidden="true"></i> Android em testes</span>
                    <span><i class="status-dot" aria-hidden="true"></i> iOS em preparação</span>
                </div>
            </div>
            <div class="phone-stage" aria-label="Prévia da leitura no VerseHub">
                <div class="phone">
                    <div class="phone-top"></div>
                    <div class="app-screen">
                        <div class="app-kicker">Leitura de hoje</div>
                        <div class="app-title">A Palavra, no seu ritmo.</div>
                        <div class="verse-card">
                            <blockquote>“Confia no Senhor de todo o teu coração e não te estribes no teu próprio entendimento.”</blockquote>
                            <div class="verse-ref">PROVÉRBIOS 3:5</div>
                        </div>
                        <div class="feeling-title">O que este texto despertou?</div>
                        <div class="chips">
                            <span class="chip active">Confiança</span>
                            <span class="chip">Esperança</span>
                            <span class="chip">Direção</span>
                            <span class="chip">Finanças</span>
                        </div>
                        <div class="mini-nav"><b>Leitura</b><span>Comunidade</span><span>Salvos</span><span>Perfil</span></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section section-soft" id="recursos">
        <div class="shell">
            <div class="section-head">
                <span class="eyebrow">Mais que um leitor</span>
                <h2>Uma experiência bíblica que continua depois da leitura.</h2>
                <p>O VerseHub une reflexão pessoal e comunidade sem transformar sua fé em competição ou exposição obrigatória.</p>
            </div>
            <div class="feature-grid">
                <article class="feature"><span class="feature-number">01</span><h3>Leia e organize</h3><p>Pesquise livros e passagens, salve versículos, destaque trechos e mantenha anotações privadas.</p></article>
                <article class="feature"><span class="feature-number">02</span><h3>Registre o momento</h3><p>Associe sentimentos e situações — de esperança e família a finanças, chamado e evangelismo.</p></article>
                <article class="feature"><span class="feature-number">03</span><h3>Caminhe em comunidade</h3><p>Veja publicações recentes ou populares, participe com respeito e abra links diretamente no versículo.</p></article>
                <article class="feature"><span class="feature-number">04</span><h3>Cresça com constância</h3><p>Níveis, sequência de leitura e conquistas ajudam a manter o hábito sem reduzir a fé a números.</p></article>
                <article class="feature"><span class="feature-number">05</span><h3>Compartilhe com contexto</h3><p>Envie um link de passagem ou de perfil público para alguém continuar a experiência no app.</p></article>
                <article class="feature"><span class="feature-number">06</span><h3>Escolha sua privacidade</h3><p>Você controla o que é público. Notas pessoais permanecem privadas e a conta pode ser excluída no app.</p></article>
            </div>
        </div>
    </section>

    <section class="section" id="telas">
        <div class="shell">
            <div class="section-head">
                <span class="eyebrow">Por dentro do app</span>
                <h2>Feito para ler, sentir e conversar.</h2>
                <p>Uma interface acolhedora, legível e adaptada ao modo claro ou noturno.</p>
            </div>
            <div class="screens">
                <article class="screen-card dark">
                    <div class="screen">
                        <div class="screen-label"><span>Leitura</span><span>NVI</span></div>
                        <h3 class="mock-heading">João 15</h3>
                        <div class="mock-card"><span class="mock-ref">VERSÍCULO 5</span><p>“Eu sou a videira; vocês são os ramos...”</p></div>
                        <div class="mock-card"><span class="mock-ref">VERSÍCULO 9</span><p>“Como o Pai me amou, assim eu os amei...”</p></div>
                        <div class="mock-card"><span class="mock-ref">VERSÍCULO 12</span><p>“O meu mandamento é este: amem-se uns aos outros...”</p></div>
                    </div>
                </article>
                <article class="screen-card">
                    <div class="screen">
                        <div class="screen-label"><span>Comunidade</span><span>Populares</span></div>
                        <h3 class="mock-heading">A Palavra entre nós.</h3>
                        <div class="mock-card"><span class="mock-ref">FILIPENSES 4:6</span><p>Hoje este texto me lembrou de entregar a ansiedade em oração.</p><div class="community-meta"><span>Marina · nível 8</span><span>♡ 42 · 8 comentários</span></div></div>
                        <div class="mock-card"><span class="mock-ref">MATEUS 5:14</span><p>Uma direção simples para viver o evangelismo no cotidiano.</p><div class="community-meta"><span>Paulo · nível 5</span><span>♡ 31 · 5 comentários</span></div></div>
                    </div>
                </article>
                <article class="screen-card">
                    <div class="screen">
                        <div class="screen-label"><span>Perfil</span><span>Público</span></div>
                        <div class="profile-hero"><div class="avatar">FA</div><h3>Filipe</h3><p>Uma jornada de leitura, um dia de cada vez.</p></div>
                        <div class="stats"><div class="stat"><strong>128</strong><small>leituras</small></div><div class="stat"><strong>54</strong><small>curtidas</small></div><div class="stat"><strong>19</strong><small>comentários</small></div></div>
                        <div class="level"><div class="level-top"><span>Nível 7</span><span>680 / 1.000</span></div><div class="progress"><span></span></div></div>
                        <div class="mock-card" style="margin-top: 14px"><span class="mock-ref">CONQUISTA RECENTE</span><p>Sete dias seguidos voltando à Palavra.</p></div>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-soft" id="privacidade">
        <div class="trust-grid shell">
            <div class="quote-card">
                <blockquote>Seu espaço de fé deve ser acolhedor — e seus dados, tratados com respeito.</blockquote>
                <p>Sem anúncios, sem venda de dados e sem rastreamento entre aplicativos.</p>
            </div>
            <div class="check-list">
                <div class="check"><span class="check-icon">✓</span><div><h3>Leitura sem conta</h3><p>Recursos básicos podem ser usados sem criar perfil.</p></div></div>
                <div class="check"><span class="check-icon">✓</span><div><h3>Controle de visibilidade</h3><p>Você escolhe o que aparece no perfil e na comunidade.</p></div></div>
                <div class="check"><span class="check-icon">✓</span><div><h3>Exclusão transparente</h3><p>A conta e os dados associados podem ser excluídos diretamente no aplicativo.</p></div></div>
                <div class="check"><span class="check-icon">✓</span><div><h3>Suporte acessível</h3><p>Política de privacidade, termos e contato ficam disponíveis neste site.</p></div></div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="shell">
            <div class="cta-card">
                <h2>Quer acompanhar o VerseHub desde o começo?</h2>
                <p>O aplicativo está em fase de preparação para as lojas. Fale com a gente para participar dos testes ou acompanhar o lançamento.</p>
                <a class="button" href="mailto:contato@filipelab.com?subject=Quero%20acompanhar%20o%20lan%C3%A7amento%20do%20VerseHub">Quero receber novidades</a>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="shell">
        <div class="footer-grid">
            <span>© {{ date('Y') }} F A CAVALCANTE · VerseHub</span>
            <nav class="footer-links" aria-label="Links legais e de suporte">
                <a href="/privacy">Privacidade</a>
                <a href="/terms">Termos</a>
                <a href="/delete-account">Excluir conta</a>
                <a href="/support">Suporte</a>
            </nav>
        </div>
        <p class="footer-note">Os textos bíblicos pertencem aos respectivos detentores de direitos e somente são disponibilizados conforme as autorizações e condições aplicáveis.</p>
    </div>
</footer>
</body>
</html>
