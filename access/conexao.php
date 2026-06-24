<?php
include_once("../access/conn_config.php");

$sql = "SELECT host, usuario, senha, banco, pref from config where value='".$_POST['contrato']."' and ativo=1";
//echo "<br>".$sql;
$stmt = $PDO_CONF->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetch( PDO::FETCH_ASSOC );
//var_dump($dados);

$host = $dados['host'];
$usuario = $dados['usuario'];
$senha = $dados['senha'];
$banco = $dados['banco'];
$pref=$dados['pref'];

$dsn = "mysql:host={$host};port=3306;dbname={$banco};charset=utf8";
try {
    $PDO = new PDO($dsn, $usuario, $senha);
    //if($PDO){echo '<br>banco conectado';}
} catch (PDOException $e) {  die($e->getMessage()); }


?>