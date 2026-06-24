<?php
function infoAtend($PDO, $id_chat){

    $sql="SELECT a.protocolo, b.id_chat, a.ate_resp, a.status_fila, (SELECT nome_situacao FROM tbl_situacao_chat WHERE id_situacao=status_fila) as situacao, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=ate_resp) as solicitante, a.bko_resp, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as backoffice, a.ta, a.te  from tbl_chat_fila_secondary a, tbl_chat_info_secondary b where a.id_fila_chat=b.fila_chat_id and a.id_fila_chat=$id_chat";

    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $info = $stmt->fetch( PDO::FETCH_ASSOC );

    $string = "";
    $string .= "\nProtocolo: ".$info['protocolo'];
    $string .= "\nSolicitante: ".$info['solicitante'];
    $string .= "\nBackoffice: ".$info['backoffice'];
    $string .= "\nSituação: ".$info['situacao'];
    $string .= "\nTempo de Espera: ".$info['te'];
    $string .= "\nTempo do Atendimento: ".$info['ta'];
    $string .= "\n";

    $sql="SELECT date_format(data_hora, '%d/%m/%Y %H:%i:%s') as data_hora, rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_envio, msg FROM tbl_chat_msg where chat_id=".$info['id_chat']." order by id_msg";

    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    while($msg = $stmt->fetch( PDO::FETCH_ASSOC )){
        $msg['nome_envio'] = ($msg['rem_id']==0) ? "Sistema" : $msg['nome_envio'];
        $string .= "\n[".$msg['data_hora']."] ".$msg['nome_envio'].": ".$msg['msg'];
    }
    //return $string;
    return atendTxt($PDO, $info['id_chat'], utf8_decode($string));
}
    //echo "<br>----------<br>";


function atendTxt($PDO, $id_chat, $string){
    $path = '../staff/txt/';

    $nomeArquivo = $id_chat.".txt";

    if (file_put_contents($path.$nomeArquivo, $string)) {
        $feed =  "<br>Arquivo '$nomeArquivo' criado e string gravada com sucesso!";
    } else {
        $feed = "<br>Falha ao criar ou gravar no arquivo.";
    }

    return $feed;

}



?>