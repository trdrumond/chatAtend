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
<title><?php echo $titulo; ?></title>
<style>
    #load {
        margin: auto !important;
        margin-top: 200px;
        text-align: center;
        line-height: 100px;
    }
</style>
<div id="load"><img src="view/img/loading.gif" alt="Carregando..."></div>
<meta http-equiv="refresh" content="0;url='<?php echo $link; ?>'" />
