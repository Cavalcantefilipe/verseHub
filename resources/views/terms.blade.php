<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termos de Uso - VerseHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f9fafb;
            color: #1f2937;
            line-height: 1.7;
        }
        .container {
            max-width: 720px;
            margin: 0 auto;
            padding: 40px 24px 80px;
        }
        .logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #059669;
        }
        .logo p {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
        h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 32px;
            margin-bottom: 12px;
        }
        h3 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-top: 20px;
            margin-bottom: 8px;
        }
        p, li {
            font-size: 15px;
            color: #4b5563;
            margin-bottom: 12px;
        }
        ul {
            padding-left: 24px;
            margin-bottom: 16px;
        }
        li { margin-bottom: 6px; }
        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 32px;
            border: 1px solid #e5e7eb;
            margin-top: 24px;
        }
        .updated {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-top: 40px;
        }
        a { color: #059669; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>VerseHub</h1>
            <p>Versículos Bíblicos</p>
        </div>

        <div class="card">
            <h2>Termos de Uso</h2>
            <p>Ao utilizar o VerseHub, você concorda com os seguintes termos.</p>

            <h3>1. Sobre o serviço</h3>
            <p>O VerseHub é um aplicativo gratuito que permite ler a Bíblia, classificar versículos por temas e ver estatísticas da comunidade cristã.</p>

            <h3>2. Uso adequado</h3>
            <p>Ao usar o VerseHub, você concorda em:</p>
            <ul>
                <li>Utilizar o aplicativo de forma respeitosa e de acordo com seu propósito.</li>
                <li>Não tentar acessar dados de outros usuários de forma não autorizada.</li>
                <li>Não utilizar o aplicativo para fins ilegais ou prejudiciais.</li>
                <li>Não tentar comprometer a segurança ou estabilidade do serviço.</li>
            </ul>

            <h3>3. Conta de usuário</h3>
            <p>O login é feito exclusivamente via Google OAuth. Você é responsável por manter a segurança da sua conta Google. Podemos suspender contas que violem estes termos.</p>

            <h3>4. Conteúdo bíblico</h3>
            <p>Os textos bíblicos exibidos no aplicativo são obtidos de fontes públicas e APIs de terceiros. O VerseHub não é responsável pela precisão das traduções. As versões disponíveis incluem NVI, ACF e Almeida Revista e Atualizada.</p>

            <h3>5. Classificações</h3>
            <p>As classificações de versículos são contribuições voluntárias dos usuários. As estatísticas da comunidade são geradas a partir dessas classificações e exibidas de forma agregada e anônima.</p>

            <h3>6. Disponibilidade</h3>
            <p>O VerseHub é oferecido "como está". Nos esforçamos para manter o serviço disponível, mas não garantimos disponibilidade ininterrupta. Podemos modificar ou descontinuar funcionalidades a qualquer momento.</p>

            <h3>7. Propriedade intelectual</h3>
            <p>O design, código e marca VerseHub são de propriedade do desenvolvedor. O conteúdo bíblico pertence aos respectivos detentores dos direitos das traduções.</p>

            <h3>8. Limitação de responsabilidade</h3>
            <p>O VerseHub não se responsabiliza por danos diretos ou indiretos decorrentes do uso do aplicativo. O uso é por sua conta e risco.</p>

            <h3>9. Alterações nos termos</h3>
            <p>Podemos atualizar estes termos a qualquer momento. O uso continuado do aplicativo após alterações constitui aceitação dos novos termos.</p>

            <h3>10. Contato</h3>
            <p>Para dúvidas sobre estes termos, entre em contato:</p>
            <p><strong>E-mail:</strong> <a href="mailto:contato@filipelab.com">contato@filipelab.com</a></p>
        </div>

        <p class="updated">Última atualização: {{ date('d/m/Y') }}</p>
    </div>
</body>
</html>
