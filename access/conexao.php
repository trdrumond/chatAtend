<?php
include_once("../access/conn_config.php");

$sql = "SELECT host, usuario, senha, banco, pref from config where value=? and ativo=1";
$stmt = $PDO_CONF->prepare( $sql );
$result = $stmt->execute([(string) ($_POST['contrato'] ?? '')]);
$dados = $stmt->fetch( PDO::FETCH_ASSOC );
if (!is_array($dados) || empty($dados['host'])) {
    die('Contrato inválido.');
}

$host = $dados['host'];
$usuario = $dados['usuario'];
$senha = $dados['senha'];
$banco = $dados['banco'];
$pref=$dados['pref'];

$dsn = "mysql:host={$host};port=3306;dbname={$banco};charset=utf8";
try {
    $PDO = new PDO($dsn, $usuario, $senha);
    //if($PDO){echo '<br>banco conectado';}
} catch (PDOException $e) {
    error_log('piloto_2.0 access conexao: ' . $e->getMessage());
    die('Falha ao conectar ao banco de dados.');
}


?>