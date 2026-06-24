<?php
include("../cnf/session.php");

//depurador($_POST);


$sql="SELECT id_com, rem_chat, (SELECT concat(nome, ' ', sobrenome) as nome from tbl_user where id_user=rem_chat) as rem_nome, dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as dest_nome, grupo_com, grupo_nome from tbl_com_info order by dt_update desc";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
$countCom='';
for($x=0;$x<count($dados);$x++){
    //$indice = $x+1;
    $indice = $x;
    if($dados[$x]['grupo_com']!=''){
        $nome_list=ucwords(strtolower($dados[$x]['grupo_nome']));
    } else {
        $exRem = explode(" ", $dados[$x]['rem_nome']);
        $nomeRem = $exRem[0]. ' '.end($exRem);
        $exDest = explode(" ", $dados[$x]['dest_nome']);
        $nomeDest = $exDest[0]. ' '.end($exDest);


        $nome_list= ucwords(strtolower($nomeRem)) .' -> '.ucwords(strtolower($nomeDest)) ;
    }


    echo '<div class="tab'.$active.'" id="title-'.$indice.'"  onclick="selAbaComList('.$indice.','.$dados[$x]['id_com'].')">'.$nome_list.'</div>';
}
//echo "<script>laodComCount();</script>";
