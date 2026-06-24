<style>
    #bl_title {
        background-color: #F7F7F7;
        width: 100%;
        height: 60px;
        margin: 2px;
        padding: 5px;
    }
    .sub_title {
        font-size: 12px;
        color: #AAAAAA;
    }



    #bl_content {
        width: 100%;
        float: left;
        background-color: #FFFFFF;
        min-height: 400px;
        margin: 2px;
        padding: 10px;
    }

    .img_perfil_online {
        width: 50px;
        height: 50px;
    }

</style>
<?php
include("../cnf/session.php");

//depurador($_POST);


$sql="SELECT id_com, contrato_id, data_hora, rem_chat, dest_chat, grupo_com, grupo_nome from tbl_com_info where id_com=".$_POST['id_com'];
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$info = $stmt->fetch( PDO::FETCH_ASSOC );

$type="";

if($info['grupo_com']!=''){
        $sqlGroupConfig="SELECT equipe_adm, equipe_bko, equipe_ate, cols from tbl_com_config where grupo_com_id=".$info['id_com'];
        $stmt = $PDO->prepare($sqlGroupConfig);
        $result = $stmt->execute();
        $infoConfigGrupo = $stmt->fetch( PDO::FETCH_ASSOC );
       // var_dump($infoConfigGrupo);
        if($infoConfigGrupo['cols']==''){
            if($infoConfigGrupo['equipe_adm']=="1"){
                $infoPerfisPer .= '- Administrador ';
            }
            if($infoConfigGrupo['equipe_bko']=="1"){
                $infoPerfisPer .= '- BackOffice ';
            }
            if($infoConfigGrupo['equipe_ate']=="1"){
                $infoPerfisPer .= '- Solicitante ';
            }
            $infoPerfis .=' <label class="sub_title">( '. $infoPerfisPer .' )</label>';

        } else {
            $infoPerfis .= '<button id="btn_file_'.$info['id_com'].'" class="btn sub_title" title="Enviar Arquivo" data-bs-toggle="modal"  data-bs-target="#mod_participantes_'.$info['id_com'].'">
                Participantes
        </button>

            ';


        }

        $InfoTituloChat = ' <img src="img/grupo.fw.png" class="img_perfil_online rounded-circle" >';
        $InfoTituloChat .= ' '. ucwords(strtolower($info['grupo_nome']));
        $InfoTituloChat .=$infoPerfis;

        if($infoUser['nivel_id']<1){
            $InfoTituloChat .= '  <button type="button" class="btn btn-secondary bol" data-bs-toggle="modal" data-bs-target="#alt_group_'.$info['id_com'].'" title="Alterar Grupo"><i class="fas fa-tools"></i></button>';
        }
    $type="0";
} else {
    if($info['rem_chat']==$infoUser['id_user']){
        $sql="SELECT id_user, nome, sobrenome, (concat(nome, ' ', sobrenome)) as nome_completo, (SELECT img from tbl_user_img_perfil where user_id=id_user) as img_perfil, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel from tbl_user where id_user=".$info['dest_chat'];
    }
    if($info['dest_chat']==$infoUser['id_user']){
        $sql="SELECT id_user, nome, sobrenome, (concat(nome, ' ', sobrenome)) as nome_completo, (SELECT img from tbl_user_img_perfil where user_id=id_user) as img_perfil, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel from tbl_user where id_user=".$info['rem_chat'];
    }

    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $info = $stmt->fetch( PDO::FETCH_ASSOC );
    $type="1";
    //var_dump($info);

    $InfoTituloChat = ' <img src="'.$info['img_perfil'].'" class="img_perfil_online rounded-circle" >';
    $InfoTituloChat .= ' '. ucwords(strtolower($info['nome_completo']));
    $InfoTituloChat .= ' <label class="sub_title">( '. $info['nivel'] .' )</label>';

}


//echo "<h3>".ucwords(strtolower($info['nome_completo']))."</h3>";
?>


<div id="bl_title">
    <h3><?= $InfoTituloChat ?></h3>
</div>
<div id="bl_content">
    <?php
        if($type=="0"){
            include("../chat/chat_com.php");
        } else {
            include("../chat/chat_com_ind.php");
        }


    ?>
</div>


