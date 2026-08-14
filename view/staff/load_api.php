<?php
include("../cnf/conn.php");

header('Access-Control-Allow-Origin: *');

$apiUser = (string) ($_POST['user'] ?? '');
$dataDe = (string) ($_POST['data_de'] ?? '');
$dataAte = (string) ($_POST['data_ate'] ?? '');

$sql = "SELECT nome_user from tbl_api_user where nome_user=?";
$stmt = $PDO->prepare($sql);
$stmt->execute([$apiUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (is_array($user) && ($user['nome_user'] ?? '') !== '') {
    $deOk = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataDe);
    $ateOk = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataAte);
    if ($deOk && $ateOk) {
        $sql = "SELECT a.protocolo as Protocolo, a.data_hora as Hora_Registro"
            . ", (sec_to_time(time_to_sec(ta)+time_to_sec(te))) as TD"
            . ", d.nome_fila as Fila, b.titulo_assunto as Assunto, a.motivo_cancela as Motivo_Cancelamento"
            . ", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=ate_resp) as Solicitante"
            . ", (SELECT a.nome_regional from tbl_regional a, tbl_user b where b.id_user=ate_resp and a.id_regional=b.regional_id) as Regional"
            . ", (SELECT a.nome_municipio from tbl_municipio a, tbl_user b where b.id_user=ate_resp and a.id_municipio=b.municipio_id) as Municipio"
            . ", (SELECT a.nome_agencia from tbl_agencia a, tbl_user b where b.id_user=ate_resp and a.id_agencia=b.agencia_id) as Agencia"
            . ", (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=bko_resp) as Backoffice"
            . ", c.nome_situacao as Status, date_format(a.hora_inicio, '%H:%i:%s') as Hora_Inicio, date_format(a.hora_fim, '%H:%i:%s') as Hora_Fim, a.ta as TA, a.te as TE"
            . " from tbl_chat_fila a, tbl_assunto b, tbl_situacao_chat c, tbl_config_fila d"
            . " where a.assunto_id=b.id_assunto"
            . " and a.status_fila=c.id_situacao"
            . " and date_format(a.data_hora, '%Y-%m-%d') BETWEEN ? and ?"
            . " and a.fila_id=d.id_fila"
            . " order by a.data_hora asc";

        $stmt = $PDO->prepare($sql);
        $stmt->execute([$dataDe, $dataAte]);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo count($dados) . " registros";
        echo "<br>";
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    } else {
        echo '<center><h2>DATAS INVÁLIDAS PARA A CONSULTA</h2></center>';
    }
} else {
    echo '<center><h1>USUÁRIO INVÁLIDO</h1></center>';
}
