<?php
include("../cnf/session.php");

//depurador($_GET);
//echo "<br>".$_GET['pagina'];
$qtd = (( (int) ($_GET['pagina'] ?? 1) - 1) * 2) + 30;
if ($qtd < 0) {
    $qtd = 30;
}

$sql_hist="SELECT a.id_msg, a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_group, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.msg from tbl_com_msg_group a where chat_group=? order by id_msg desc limit 0," . (int) $qtd;

$stmt = $PDO->prepare($sql_hist);
$result = $stmt->execute([(int) ($_GET['com'] ?? 0)]);
$infoGroupMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );

for($z=count($infoGroupMsg);$z>=0;$z--){
    $ls=$infoGroupMsg[$z];

    $class = ($ls['rem_id']==$infoUser['id_user']) ? 'me' : 'other';
    if($ls['rem_id']==0){

        $h5="";
        $class = 'sys';
    } else {
        $h5 = "<h5>".ucwords(strtolower($ls['nome_rem']))."</h5>";
    }
    echo "<div class='$class'>
            <img src='".$ls['img']."'>
            <div class='text'>
                ".$h5."
                <div class='paragrafo'>".$ls['msg']."</div>
                <div class='dataHora'>".$ls['hora_msg']."</div>
            </div>
        </div>";
}
