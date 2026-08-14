<?php
include("../cnf/session.php");

$qeryPend = " and ate_resp=? and data_hora_fim is not null and data_hora_visualizacao is null";

$sql = "SELECT id_pend, chat_id, (SELECT protocolo from tbl_chat_fila where id_fila_chat=chat_id) as protocolo, (SELECT id_chat from tbl_chat_info where fila_chat_id=chat_id) as id_chat FROM tbl_pend_info where situacao_id=3 $qeryPend";
$stmt = $PDO_LOAD->prepare($sql);
$stmt->execute([(int) $idu]);
$dadosContratos = $stmt->fetchAll(PDO::FETCH_ASSOC);
for ($z = 0; $z < count($dadosContratos); $z++) {
    if ($dadosContratos[$z]['id_pend'] != '') {
        echo "<script>notPend(" . json_encode((string) $dadosContratos[$z]['protocolo']) . ", " . json_encode((string) $dadosContratos[$z]['id_chat']) . ");</script>";
    }
}

?>
