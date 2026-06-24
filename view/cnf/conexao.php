<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Fortaleza');



//$host = 'localhost';
$host = '10.33.29.106';
$usuario = 'acesso.sistemas';
//$usuario = 'user_chatlogos';
$senha = 'tDHMpeXVTzQAZsGD';

//$banco = 'web_chatlogos_piloto_20';
$banco = 'web_chatlogos_cred';
$dsn = "mysql:host={$host};port=3306;dbname={$banco};charset=utf8";

try {
    $PDO = new PDO($dsn, $usuario, $senha);
    //$PDO = new PDO($dsn, $usuario, $senha,
    //    [
    //        PDO::ATTR_PERSISTENT => true,
    //        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    //    ]
    //);
    //if($PDO){echo 'banco conectado';}
} catch (PDOException $e) {
    die($e->getMessage());
}

try {
    $PDO_LOAD = new PDO($dsn, $usuario, $senha);
    //$PDO_LOAD = new PDO($dsn, $usuario, $senha,
    //    [
    //        PDO::ATTR_PERSISTENT => true,
    //        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    //    ]
    //);
    //if($PDO){echo 'banco conectado';}
} catch (PDOException $e) {
    die($e->getMessage());
}


function generateHash(string $string): string
{
    return sha1($string);
}
