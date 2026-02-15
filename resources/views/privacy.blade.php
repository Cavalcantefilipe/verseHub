<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - VerseHub</title>
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
            <h2>Política de Privacidade</h2>
            <p>Esta política descreve como o VerseHub coleta, usa e protege suas informações pessoais.</p>

            <h3>1. Informações que coletamos</h3>
            <p>Ao utilizar o VerseHub, podemos coletar:</p>
            <ul>
                <li><strong>Dados da conta Google:</strong> nome, endereço de e-mail e foto de perfil, quando você opta por fazer login com o Google.</li>
                <li><strong>Classificações de versículos:</strong> as categorias que você atribui aos versículos bíblicos.</li>
                <li><strong>Identificador do dispositivo:</strong> um identificador anônimo usado para salvar classificações de usuários não autenticados.</li>
            </ul>

            <h3>2. Como usamos suas informações</h3>
            <ul>
                <li>Autenticar e identificar sua conta no aplicativo.</li>
                <li>Salvar e sincronizar suas classificações de versículos entre dispositivos.</li>
                <li>Gerar estatísticas anônimas da comunidade sobre classificações de versículos.</li>
                <li>Melhorar a experiência do usuário no aplicativo.</li>
            </ul>

            <h3>3. Compartilhamento de dados</h3>
            <p>Não vendemos, alugamos ou compartilhamos suas informações pessoais com terceiros. As classificações de versículos são agregadas de forma anônima para gerar estatísticas da comunidade.</p>

            <h3>4. Autenticação com Google</h3>
            <p>Utilizamos o Google OAuth 2.0 para autenticação. Ao fazer login, recebemos apenas seu nome, e-mail e foto de perfil. Não temos acesso à sua senha do Google nem a outros dados da sua conta Google.</p>

            <h3>5. Armazenamento e segurança</h3>
            <p>Seus dados são armazenados em servidores seguros. Utilizamos criptografia HTTPS para todas as comunicações entre o aplicativo e nossos servidores. Tokens de autenticação são armazenados de forma segura no dispositivo.</p>

            <h3>6. Seus direitos</h3>
            <p>Você pode a qualquer momento:</p>
            <ul>
                <li>Sair da sua conta e remover seus dados locais.</li>
                <li>Solicitar a exclusão dos seus dados enviando um e-mail para o endereço abaixo.</li>
                <li>Usar o aplicativo sem criar uma conta (com funcionalidades limitadas).</li>
            </ul>

            <h3>7. Dados de menores</h3>
            <p>O VerseHub não coleta intencionalmente dados de menores de 13 anos. Se você acredita que coletamos dados de um menor, entre em contato conosco.</p>

            <h3>8. Alterações nesta política</h3>
            <p>Podemos atualizar esta política periodicamente. Notificaremos sobre mudanças significativas através do aplicativo.</p>

            <h3>9. Contato</h3>
            <p>Para dúvidas sobre esta política de privacidade, entre em contato:</p>
            <p><strong>E-mail:</strong> <a href="mailto:contato@filipelab.com">contato@filipelab.com</a></p>
        </div>

        <p class="updated">Última atualização: {{ date('d/m/Y') }}</p>
    </div>
</body>
</html>
