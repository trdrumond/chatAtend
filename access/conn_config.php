<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Fortaleza');

    $host_config = 'localhost';
    $user_config = 'acesso.sistemas';
    $pass_config = 'tDHMpeXVTzQAZsGD';
    $db_config = 'web_chat_config';

$dns_config = "mysql:host={$host_config};port=3306;dbname={$db_config};charset=utf8";
try {
    $PDO_CONF = new PDO($dns_config, $user_config, $pass_config);
    //if($PDO_CONF){echo 'banco conectado';}
} catch (PDOException $e) {  die($e->getMessage()); }

function generateHash( $string )
{
    return sha1( $string );
}


?>
