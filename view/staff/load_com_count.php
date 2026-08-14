<?php
include("../cnf/session.php");

//depurador($_POST);


$sql="SELECT count(id_msg) as qtd from tbl_com_msg where dt_visual is null and dest_id=?";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute([(int) $infoUser['id_user']]);
$dados = $stmt->fetch( PDO::FETCH_ASSOC );

$countCom = $dados['qtd'];

//CONTAGEM DE GRUPOS
$sql="SELECT id_com from tbl_com_info where grupo_com<>''";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
for($x=0;$x<count($dados);$x++){
    $configGrupo=0;
        $sqlGroupConfig="SELECT equipe_adm, equipe_bko, equipe_ate, cols from tbl_com_config where grupo_com_id=?";
        $stmt = $PDO->prepare($sqlGroupConfig);
        $result = $stmt->execute([(int) $dados[$x]['id_com']]);
        $infoConfigGrupo = $stmt->fetch( PDO::FETCH_ASSOC );
        if (!is_array($infoConfigGrupo)) {
            continue;
        }
        if($infoConfigGrupo['cols']==''){
            if($infoUser['nivel_id']<=1){
                $configGrupo = $infoConfigGrupo['equipe_adm'];
            } else
            if($infoUser['nivel_id']==4){
                $configGrupo = $infoConfigGrupo['equipe_bko'];
            } else
            if($infoUser['nivel_id']==5){
                $configGrupo = $infoConfigGrupo['equipe_ate'];
            }
        } else {
            if (stComColsHasUser($infoConfigGrupo['cols'] ?? '', (int) $infoUser['id_user'])) {
                $configGrupo=1;
            }
        }

        if($configGrupo==1){
            $sql="SELECT dt_view from tbl_com_msg_group_view where group_chat=? and user_id=?";
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute([(int) $dados[$x]['id_com'], (int) $infoUser['id_user']]);
            $infoView = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($infoView);
            if($infoView['dt_view']==''){
                $sqlMsg="SELECT count(id_msg) as qtd from tbl_com_msg_group where chat_group=? and rem_id<>?";
                $stmt = $PDO->prepare($sqlMsg);
                $result = $stmt->execute([(int) $dados[$x]['id_com'], (int) $infoUser['id_user']]);
                $count = $stmt->fetch( PDO::FETCH_ASSOC );
                $countCom = $countCom + $count['qtd'];

            } else {
                $sqlMsg="SELECT count(id_msg) as qtd from tbl_com_msg_group where chat_group=? and rem_id<>? and data_hora > ?";
                $stmt = $PDO->prepare($sqlMsg);
                $result = $stmt->execute([(int) $dados[$x]['id_com'], (int) $infoUser['id_user'], $infoView['dt_view']]);
                $count = $stmt->fetch( PDO::FETCH_ASSOC );
                $countCom = $countCom + $count['qtd'];

            }
        }

}
//FIM CONTAGEM DE GRUPOS

//echo "<br>".$countCom;
if($countCom > 0){
    $dadosCom = '<span class="badge bg-light text-dark">'.$countCom.'</span>';
} else {
    $dadosCom = '';
}

?>

<script>
    var count = <?= (int) $countCom ?>;
    var not = <?= json_encode(preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($_POST['not'] ?? '')), JSON_UNESCAPED_UNICODE) ?>;
    //console.log(not);
    $('#countCom').html(<?= json_encode($dadosCom, JSON_UNESCAPED_UNICODE) ?>);

    //if(count>0 && not == 'not'){
        //notCom(count);
    //}
</script>

