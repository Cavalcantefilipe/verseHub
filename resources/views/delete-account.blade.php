<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exclusão de Conta - VerseHub</title>
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
        ul, ol {
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
        .warning {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .warning p {
            color: #991b1b;
            margin: 0;
        }
        .steps {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .steps p {
            color: #166534;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .steps ol { margin-bottom: 0; }
        .steps li { color: #166534; }
        .email-link {
            display: inline-block;
            background: #059669;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin-top: 12px;
        }
        .email-link:hover {
            background: #047857;
        }
        a { color: #059669; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .updated {
            text-align: center;
            color: #9ca3af;
            font-size: 13px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>VerseHub</h1>
            <p>Versículos Bíblicos</p>
        </div>

        <div class="card">
            <h2>Solicitar Exclusão de Conta e Dados</h2>
            <p>Se você deseja excluir sua conta do VerseHub e todos os dados associados, siga as instruções abaixo.</p>

            <div class="steps">
                <p>Como solicitar a exclusão:</p>
                <ol>
                    <li>Envie um e-mail para <strong>contato@filipelab.com</strong></li>
                    <li>Use o assunto: <strong>"Exclusão de conta VerseHub"</strong></li>
                    <li>Informe o e-mail da conta Google que você usou para login</li>
                    <li>Sua conta será excluída em até 7 dias úteis</li>
                </ol>
            </div>

            <a href="mailto:contato@filipelab.com?subject=Exclus%C3%A3o%20de%20conta%20VerseHub&body=Ol%C3%A1%2C%20gostaria%20de%20solicitar%20a%20exclus%C3%A3o%20da%20minha%20conta%20e%20dados%20do%20VerseHub.%0A%0AE-mail%20da%20conta%20Google%3A%20" class="email-link">Enviar e-mail de exclusão</a>

            <h3>Dados que serão excluídos</h3>
            <ul>
                <li><strong>Dados da conta:</strong> nome, e-mail e foto de perfil do Google.</li>
                <li><strong>Classificações:</strong> todas as classificações de versículos feitas pela sua conta.</li>
                <li><strong>Token de autenticação:</strong> invalidado imediatamente.</li>
            </ul>

            <h3>Dados que serão mantidos</h3>
            <ul>
                <li><strong>Estatísticas agregadas:</strong> contribuições anônimas para as estatísticas da comunidade podem ser mantidas de forma não identificável.</li>
            </ul>

            <div class="warning">
                <p><strong>Atenção:</strong> a exclusão é permanente e não pode ser desfeita. Todas as suas classificações serão perdidas.</p>
            </div>

            <h3>Contato</h3>
            <p>Para dúvidas, entre em contato: <a href="mailto:contato@filipelab.com">contato@filipelab.com</a></p>
        </div>

        <p class="updated">Última atualização: {{ date('d/m/Y') }}</p>
    </div>
</body>
</html>
