<?php

//TRANSFORMA STRING EM NOME DE CAMPO
function nomeCampoInput($string){
    $caracteres_sem_acento = array(
        'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','�'=>'Z', '�'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
        'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
        'Ï'=>'I', 'Ñ'=>'N', 'Ń'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U',
        'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
        'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
        'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ń'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u',
        'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f',
        'ă'=>'a', 'î'=>'i', 'â'=>'a', 'ș'=>'s', 'ț'=>'t', 'Ă'=>'A', 'Î'=>'I', 'Â'=>'A', 'Ș'=>'S', 'Ț'=>'T',
    );

    $nova_string = str_replace(" ", "_", strtr($string, $caracteres_sem_acento));
    $nova_string = str_replace("?", "", $nova_string);
    $nova_string = str_replace("-", "", $nova_string);
    $nova_string = str_replace("!", "", $nova_string);
    $nova_string = str_replace(".", "_", $nova_string);
    $nova_string = str_replace("(", "", $nova_string);
    $nova_string = str_replace(")", "", $nova_string);
    $nova_string = str_replace("r$", "", $nova_string);
    $nova_string = str_replace("R$", "", $nova_string);
    $nova_string = str_replace("$", "", $nova_string);
    $nova_string = str_replace("/", "_", $nova_string);
    if((substr($nova_string, -1))=='_'){ $nova_string = substr($nova_string, 0, -1); }

    return strtolower($nova_string);
}

//HORA PARA SEGUNDOS
function time_to_sec($time) {
    $hours = substr($time, 0, -6);
    $minutes = substr($time, -5, 2);
    $seconds = substr($time, -2);

    return $hours * 3600 + $minutes * 60 + $seconds;
  }
//SEGUNDOS PARA HORA
  function sec_to_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor($seconds % 3600 / 60);
    $seconds = $seconds % 60;

    return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
  }

//DEPURADOR
function depurador($array){
    echo "<pre>";
    var_dump($array);
    echo "</pre>";
}

function geraToken($matricula){
    return sha1($matricula);
}

function logAtendimento($PDO, $userId, $acao){
    $stmt = $PDO->prepare("SELECT id_user, contrato_id, agencia_id, fila_id from tbl_user where id_user=?");
    $stmt->execute([(int) $userId]);
    $userDados = $stmt->fetch( PDO::FETCH_ASSOC );
    if (!$userDados) {
        return;
    }

    $sql="INSERT INTO tbl_log_atendimento (user_id, contrato_id, agencia_id, fila_id, acao) VALUES (?, ?, ?, ?, ?)";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([
        $userDados['id_user'],
        $userDados['contrato_id'],
        $userDados['agencia_id'],
        $userDados['fila_id'],
        $acao,
    ]);
    //if($result==1){echo "<br>Deu Boa!";} else{echo "<br>Deu Ruim!";}
}



?>
