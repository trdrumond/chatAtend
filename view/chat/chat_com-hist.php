<link rel='stylesheet' type='text/css' href='chat/assets/css/style-com.css?<? time() ?>'></style>
<style>
    .chat-div_<?=$_POST['id_com']?> {
        width: 95%;
        margin-top: 5px;
        margin-left: auto;
        margin-right: auto;
        height: 370px;
        background: #FFFFFF;
    }

    .chat-content-hist {
        margin: auto;
        width: 100%;
        height: 370px;
        background: #FFFFFF;
        overflow: scroll;

    }
</style>
<script>
    var com = <?=$_POST['id_com']?>;
    var user = '<?=$infoUser['id_user']?>';
    var indice =<?=$_POST['indice']?>;
    //console.log('indice group: ' + indice);
    //console.log("Com group: " + com);


    if(typeof load_message_box !== 'undefined'){
        clearTimeout(load_message_box);
    }
</script>
<?php
    $comIdGroup = (int) ($_POST['id_com'] ?? 0);
    $userIdGroup = (int) ($infoUser['id_user'] ?? 0);

    $sql="SELECT data_hora from tbl_com_msg_group where chat_group=? and rem_id<>? order by id_msg limit 1";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$comIdGroup, $userIdGroup]);
    $infoMsg = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($infoMsg);
    $sql="SELECT user_id, dt_view from tbl_com_msg_group_view where group_chat=? and user_id=?";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$comIdGroup, $userIdGroup]);
    $infoMsgView = $stmt->fetch( PDO::FETCH_ASSOC );

    if($infoMsgView['user_id']==''){
        $sql = "INSERT INTO tbl_com_msg_group_view (user_id, group_chat) VALUES (?, ?)";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute([$userIdGroup, $comIdGroup]);
    }

    $sql = "UPDATE tbl_com_msg_group_view SET dt_view=now() where group_chat=? and user_id=?";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute([$comIdGroup, $userIdGroup]);
    if($result){
        echo '<script>loadComList();</script>';
    }




    $tk = strtotime(date('Y-m-d H:i:s'));

    $sql="SELECT id_com, data_hora, rem_chat, dest_chat, grupo_com from tbl_com_info where id_com=?";
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([$comIdGroup]);
    $infoCom = $stmt->fetch( PDO::FETCH_ASSOC );
    //depurador($infoCom);


    if($infoCom['rem_chat']==0 && $infoCom['dest_chat']==0){
        $sql_hist="SELECT a.id_msg, a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_group, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.msg from tbl_com_msg_group a where chat_group=? order by id_msg desc limit 0,30";
    } else {
        $sql_hist="SELECT a.id_msg, a.data_hora, date_format(a.data_hora, '%d/%m/%Y %H:%i') as hora_msg, a.chat_group, a.rem_id, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=rem_id) as nome_rem, (SELECT nome from tbl_user where id_user=rem_id) as nome, (SELECT sobrenome from tbl_user where id_user=rem_id) as sobrenome, (SELECT img from tbl_user_img_perfil where user_id=rem_id) as img, a.msg from tbl_com_msg a where com_id=? order by id_msg desc limit 0,30";
    }


    //echo "<br>".$sql_hist;

    $stmt = $PDO->prepare($sql_hist);
    $result = $stmt->execute([(int) $infoCom['id_com']]);
    $infoComMsg = $stmt->fetchAll( PDO::FETCH_ASSOC );

    //depurador($infoComMsg);
    /*
    if($infoCom['rem_chat']==0 && $infoCom['dest_chat']==0){
        $com = $infoCom['grupo_com'];
    } else {
        $com = $infoCom['id_com'];
    }
    */
    $com = $infoCom['id_com'];



?>

    <div class="chat-div_<?=$_POST['id_com']?>">
        <section class="chat-content-hist" id="chat-content_com_<?=$_POST['id_com']?>">
            <?php if(count($infoComMsg)>30){ ?>
            <button id="ver-mais_<?=$_POST['id_com']?>" class="btn-chat" data-ref="2">Carregar mais...</button>
            <?php } ?>

                <div id="conteudo-grupo_<?=$_POST['id_com']?>">
                    <?php

                            for($z=count($infoComMsg);$z>=0;$z--){
                                $ls=$infoComMsg[$z];

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
                    ?>
                    </div>
        </section>
    </div>

    <?php
        $infoParticipantes='';
        $sqlGroupConfig="SELECT grupo_com_id, equipe_adm, equipe_bko, equipe_ate, cols, (SELECT grupo_nome from tbl_com_info where id_com=grupo_com_id) as nome_grupo from tbl_com_config where grupo_com_id=?";
        $stmt = $PDO->prepare($sqlGroupConfig);
        $result = $stmt->execute([$comIdGroup]);
        $infoConfigGrupo = $stmt->fetch( PDO::FETCH_ASSOC );
        // var_dump($infoConfigGrupo);
        if($infoConfigGrupo['cols']!=''){
        ?>

        <div class="modal fade" id="mod_participantes_<?=$_POST['id_com']?>" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style="width: 100%;">
                <div class="modal-header">
                    <h3 class="modal-title" id="exampleModalLabel">Participantes do Grupo</h1>
                    <button type="button" id="close_part_<?=$_POST['id_com']?>" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                            <?php

                                    $infoConfigGrupo['col'] = str_replace("'", "", $infoConfigGrupo['cols']);
                                    $infoConfigGrupo['col']=substr($infoConfigGrupo['col'], 2);
                                    //echo "<br>".$infoConfigGrupo['col'];

                                    $colIds = array_values(array_filter(array_map('intval', preg_split('/\s*,\s*/', (string) ($infoConfigGrupo['col'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))));
                                    $infoPart = [];
                                    if (count($colIds) > 0) {
                                        $ph = implode(',', array_fill(0, count($colIds), '?'));
                                        $sqlGroupConfig="SELECT concat(nome, ' ', sobrenome) as nome_user, (SELECT nome_nivel from tbl_nivel where id_nivel=nivel_id) as nivel from tbl_user where id_user IN ($ph) order by nivel, nome_user";
                                        //echo "<br>".$sqlGroupConfig;

                                        $stmt = $PDO->prepare($sqlGroupConfig);
                                        $result = $stmt->execute($colIds);
                                        $infoPart = $stmt->fetchAll( PDO::FETCH_ASSOC );
                                    }
                                    //depurador($infoPart);

                                    for($arrPart=0;$arrPart<count($infoPart);$arrPart++){
                                        echo "<li>".$infoPart[$arrPart]['nivel']." - ".$infoPart[$arrPart]['nome_user']."</li>";
                                    }





                            ?>
                </div>
                </div>
            </div>
        </div>
<?php } ?>
