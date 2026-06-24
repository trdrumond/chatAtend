<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Fortaleza');



$host = 'localhost';
//$host = '191.252.103.123';
$usuario = 'acesso.sistemas';
//$usuario = 'user_chatlogos';
$senha = 'tDHMpeXVTzQAZsGD';
//$banco = 'web_chatlogos_21';
$banco = 'web_chatlogos_piloto';
//$banco = 'web_chatlogos_enelce';
$dsn = "mysql:host={$host};port=3306;dbname={$banco};charset=utf8";

try {
    //$PDO = new PDO($dsn, $usuario, $senha);
    $PDO = new PDO(
        $dsn,
        $usuario,
        $senha,
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    //if($PDO){echo 'banco conectado';}
} catch (PDOException $e) {
    die($e->getMessage());
}

try {
    //$PDO_LOAD = new PDO($dsn, $usuario, $senha);
    $PDO_LOAD = new PDO(
        $dsn,
        $usuario,
        $senha,
        [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    //if($PDO){echo 'banco conectado';}
} catch (PDOException $e) {
    die($e->getMessage());
}


function generateHash(string $string): string
{
    return sha1($string);
}
