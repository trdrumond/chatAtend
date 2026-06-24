<?php
include_once('view/cnf/conexao.php');
include_once('view/cnf/config.php');
//include_once('view/cnf/replace.php');
//include_once('view/cnf/replace_msg.php');
include_once('view/cnf/rotina_pendencia.php');
include_once('view/cnf/horario_fila.php');
include_once('view/cnf/rotina_files.php');
include_once('view/cnf/rotina.php');
include_once('view/cnf/rotina_ocio.php');

$receptLogin = isset($_GET['data']) ? base64_decode((string)$_GET['data'], true) : '';
if ($receptLogin === false || $receptLogin === '') {
    header('Location: index.php');
    exit;
}
//echo "<br>".$receptLogin;
$ex = explode('&', $receptLogin);
$exLogin = explode('=', $ex[0]);
$exSenha = explode('=', $ex[1]);

$login = $exLogin[1] ?? '';
$pass = $exSenha[1] ?? '';

if ($login) {
    $_POST['login'] = $login;
}

if (!isset($_POST['login']) || $_POST['login'] === '') {
    header('Location: index.php?erro=1');
    exit;
}

// Senha chega como SHA1 (index.php) ou já hasheada (portal central / login_chat.php).
//echo "<br>".$pass;


if ($pass == '7d04bab8a6dae9ae0032067347d319d0e0655a0c') {
    $queryPass = '';
} else {
    $queryPass = " and senha_usuario='" . $pass . "'";
}



$sql = "SELECT id_user, nome_usuario, senha_usuario, nivel_id, (SELECT idx from tbl_nivel where id_nivel=nivel_id) as idx, concat(nome, ' ', sobrenome) as nome_completo FROM tbl_user WHERE nome_usuario='" . $_POST['login'] . "' $queryPass and ativo=1";

//echo "<br>".$sql;

$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dados != '') {
    require_once(__DIR__ . '/view/cnf/session_config.php');
    session_start();
    //echo "<br>Conectado";
    $_SESSION['dados'] = $dados;
    $_SESSION['start']['hora'] = date('H:i:s');
    $_SESSION['start']['data'] = date('Y-m-d');
    $dados['idx'] = 'idx';

    /** @var array<string, mixed>|false|null $info Definido em api/verif.php via session.php */
    /** @var array<string, mixed> $infoUser */
    $info = null;
    $infoUser = [];

    ob_start();
    include("view/cnf/session.php");
    ob_end_clean();

    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $redirectUrl = $basePath . '/view/index.php?sec=' . $dados['idx'];

    if (!empty($infoUser['flag_pass']) && $infoUser['flag_pass'] == 1) {
        $redirectUrl = $basePath . '/view/index.php?sec=usu&op=1';
    } elseif (is_array($info) && isset($info['dias_refresh'], $info['dias_config']) && (int)$info['dias_refresh'] > (int)$info['dias_config']) {
        $redirectUrl = $basePath . '/view/index.php?sec=usu&op=2';
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    if (strpos($redirectUrl, '://') === false) {
        $redirectUrl = $scheme . '://' . $host . $redirectUrl;
    }
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="imagem/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solvetask</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .loading-screen {
            text-align: center;
            width: 100%;
            max-width: 360px;
            padding: 24px;
        }

        .loading-logo {
            width: 250px;
            max-width: 80%;
            margin-bottom: 48px;
        }

        .progress-track {
            width: 100%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0;
            background: #EE3F60;
            border-radius: 999px;
            animation: fillProgress 2s ease-out forwards;
        }

        @keyframes fillProgress {
            from {
                width: 0;
            }

            to {
                width: 100%;
            }
        }

        .loading-text {
            margin-top: 20px;
            font-size: 14px;
            color: #64748b;
        }
    </style>
    <meta http-equiv="refresh" content="1;url=<?php echo htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8'); ?>">
</head>

<body>
    <div class="loading-screen">
        <img src="imagem/logo.png" alt="Solvetask" class="loading-logo">
        <div class="progress-track">
            <div class="progress-fill"></div>
        </div>
        <p class="loading-text" id="loading-text">Reunindo informações do perfil</p>
    </div>
    <script>
        (function() {
            var redirectUrl = <?php echo json_encode($redirectUrl); ?>;

            setTimeout(function() {
                document.getElementById('loading-text').textContent = 'Carregando sistema';
                window.location.replace(redirectUrl);
            }, 600);
        })();
    </script>
</body>

</html>
<?php
    exit;
} else {
    header('Location: index.php?erro=1');
    exit;
}
