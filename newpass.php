<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="imagem/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solvetask — Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style-materialize-new.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(145deg, #eef5fb 0%, #e8f6fc 45%, #C1E7F5 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
        }

        .right { text-align: right !important; }
        .left { text-align: left !important; }

        .login-container {
            background: #ffffff;
            min-width: 360px;
            max-width: 420px;
            width: 100%;
            padding: 3rem 3.5rem;
            border-radius: 24px;
            position: relative;
            z-index: 1;
            box-shadow: 0 8px 32px rgba(37, 33, 89, .12);
            border: 1px solid rgba(37, 33, 89, .06);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            color: #ffffff;
            background: linear-gradient(135deg, #D6336C, #FA5252);
            border: none;
            border-radius: 50px;
            text-decoration: none;
            cursor: pointer;
            transition: opacity .2s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(238, 63, 96, .3);
        }

        .button:hover {
            opacity: .92;
            box-shadow: 0 6px 20px rgba(238, 63, 96, .35);
        }

        .button .icon {
            margin-right: 10px;
            border: solid #ffffff;
            border-width: 0 2px 2px 0;
            display: inline-block;
            padding: 3px;
            transform: rotate(-45deg);
        }

        a {
            font-family: inherit;
            text-decoration: none;
            font-size: 12px;
            color: #94a3b8;
            transition: color .15s;
        }

        a:hover { color: #EE3F60; }

        .imglogin { width: 130px; max-width: 70%; }

        .logo {
            margin-bottom: 1.25rem;
            text-align: center;
        }

        .login-subtitle {
            text-align: center;
            font-size: 12px;
            color: #5c5a8a;
            margin-bottom: 1.75rem;
            letter-spacing: .02em;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="input-container left logo">
            <img src="imagem/logo.png" class="imglogin" alt="Solvetask">
        </div>
        <p class="login-subtitle">Plataforma de atendimento corporativo</p>

        <div class="input-container">
            <input type="text" id="login" name="login" class="input" required>
            <label for="login" class="label">Nome de usuário / matrícula Logos</label>
        </div>
        <div class="input-container">
            <input type="password" id="senha" name="senha" class="input" required>
            <label for="senha" class="label">Senha</label>
        </div>
        <div class="input-container">
            <select name="contrato" id="contrato" class="input">
                <option value=""></option>
                <option value="">EDP</option>
                <option value="">ENEL CE</option>
                <option value="">NeoEnergia PE</option>
            </select>
            <label for="contrato" class="label">Selecione o contrato</label>
        </div>
        <div class="input-container left">
            <a href="newpass.php">Esqueci minha senha</a>
        </div>
        <div class="input-container right">
            <button type="button" class="button">
                <span class="icon"></span>
                Entrar
            </button>
        </div>
    </div>
</body>
</html>
