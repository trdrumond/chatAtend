
<?php
    include("../cnf/session.php");


    //depurador($_POST);

    echo "<script>console.log('verifica atendente');</script>";


    $sqlVer="SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, timediff(now(), data_hora) as te from tbl_chat_fila where (status_fila=".ST_FILA_NA_FILA." or ".stFilaSqlChamarSolicitante().") and ate_resp=?";
    $stmt = $PDO->prepare($sqlVer);
    $result = $stmt->execute([(int) $_SESSION['dados']['id_user']]);
    $infFila = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($infFila);

    if(!$infFila){
        //echo "Sem demandas!";
        session_destroy();
        echo '<meta http-equiv="refresh" content="0;url=index.php" />';
    }
