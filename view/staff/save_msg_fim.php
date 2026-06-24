<?php

include(__DIR__ . '/../cnf/session.php');



/** @var PDO $PDO */



header('Content-Type: application/json; charset=utf-8');



$tokenChat = isset($_POST['tokenChat']) ? trim((string)$_POST['tokenChat']) : '';

$msg = isset($_POST['msg']) ? (string)$_POST['msg'] : '';

$contrato = isset($_POST['contrato']) ? (int)$_POST['contrato'] : 0;



$retorno = [

    'ok' => false,

    'closed' => false,

    'already_closed' => false,

    'chatId' => null,

    'filaChatId' => null,

];



if ($tokenChat === '') {

    echo json_encode($retorno);

    exit;

}



$resultado = stChatEncerrarAtendimento($PDO, $tokenChat, $msg, $contrato);

if ($resultado['id_chat'] <= 0) {

    echo json_encode($retorno);

    exit;

}



$retorno['ok'] = true;

$retorno['closed'] = true;

$retorno['already_closed'] = $resultado['already_closed'];

$retorno['chatId'] = $resultado['id_chat'];

$retorno['filaChatId'] = $resultado['fila_chat_id'];



echo json_encode($retorno);

