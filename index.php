<?php
include("view/cnf/conexao.php");
include("view/cnf/config.php");

$mensagem = '';

if (!empty($_GET['erro'])) {
    $mensagem = '<div class="alert alert-danger" role="alert">Usuário ou senha inválidos. Tente novamente.</div>';
}

if ($_POST) {
    $loginUser = trim((string)($_POST['login'] ?? ''));
    $loginPass = (string)($_POST['senha'] ?? '');

    if ($loginUser === '' || $loginPass === '') {
        $mensagem = '<div class="alert alert-danger" role="alert">Informe usuário e senha.</div>';
    } else {
        $infoLogin = 'login=' . $loginUser . '&senha=' . generateHash($loginPass);
        $infoLogin = base64_encode($infoLogin);

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $link = $scheme . '://' . $host . $base . '/login.php?data=' . rawurlencode($infoLogin);

        header('Location: ' . $link);
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="imagem/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solvetask</title>
    <link rel="stylesheet" href="css/style-materialize-new.css">
    <script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js"
        integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous">
    </script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #E6E7E8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .right {
            text-align: right !important;
        }

        .left {
            text-align: left !important;
        }

        .login-container {
            background-color: white;
            min-width: 350px;
            padding: 4rem;
            border-radius: 8px;
            position: relative;
            z-index: 1;
            background-color: #FFFFFF;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 50%;
            z-index: -1;
        }


        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 16px;
            color: #EE3F60;
            background-color: #f5f5f5;
            border: 2px solid #dcdcdc;
            border-radius: 50px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .button:hover {
            background-color: #e0e0e0;
        }

        .button .icon {
            margin-right: 10px;
            border: solid #EE3F60;
            border-width: 0 2px 2px 0;
            display: inline-block;
            padding: 3px;
            transform: rotate(-45deg);
        }

        a {
            font-family: Arial, sans-serif;
            text-decoration: none;
            font-size: 12px;
            color: #c9c9c9;
        }

        a:hover {
            color: #EE3F60;
        }

        .imglogin {
            width: 200px;
        }

        .logo {
            margin-top: -20px;
            margin-bottom: 70px;
        }
    </style>
</head>

<body>
    <div class="login-container">

        <form method="post" action="">
            <div class="input-container left logo">
                <img src="imagem/logo.png" class="imglogin" alt="Logo">
            </div>
            <div class="input-container">
                <input type="text" id="login" name="login" class="input" required>
                <label for="login" class="label">Nome de usuário / Matrícula Logos</label>
            </div>
            <div class="input-container">
                <input type="password" id="senha" name="senha" class="input" required>
                <label for="senha" class="label">Senha</label>
            </div>
            <div class="input-container left">
                <a href="newpass.php">Esqueci minha senha</a>
            </div>
            <div class="input-container right">
                <button class="button" id="entrar">
                    <span class="icon"></span>
                    Entrar
                </button>
            </div>
            <?php echo $mensagem; ?>
        </form>
    </div>

    <script>

    </script>
</body>

</html>