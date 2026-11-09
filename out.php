<?php
include("view/cnf/config.php");
/*
if($_SERVER['HTTP_HOST'] == 'localhost'){
    $link = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/chatlogos/chat-piloto_2.2/';
  } else {
    $link = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/chat-piloto_2.2/';
  }
*/
$link = 'https://solvetask.logos-ma.com.br/index-solve.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" href="imagem/favicon.png">
<title><?php echo $titulo; ?></title>
<style>
    #load {
        margin: auto !important;
        margin-top: 200px;
        text-align: center;
        line-height: 100px;
    }
</style>
<?php include __DIR__ . '/view/content/access/head.php'; ?>
<meta http-equiv="refresh" content="0;url='<?php echo $link; ?>'" />
</head>
<body class="st-glassy-login">
<main class="st-access-card st-access-card--center">
    <div class="st-access-logo"><img src="imagem/logo.png" class="loading-logo" alt="Solvetask"></div>
    <h1 class="st-access-title">Sessão encerrada</h1>
    <p class="st-access-sub" role="status" aria-live="polite">Redirecionando para o portal…</p>
    <div id="load"><img src="view/img/loading.gif" alt="Carregando..."></div>
</main>
</body>
</html>
