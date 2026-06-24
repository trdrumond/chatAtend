<?php
include("../cnf/session.php");

//depurador($_POST);


$sql="SELECT id_com, rem_chat, (SELECT concat(nome, ' ', sobrenome) as nome from tbl_user where id_user=rem_chat) as rem_nome, dest_chat, (SELECT concat(nome, ' ', sobrenome) from tbl_user where id_user=dest_chat) as dest_nome, grupo_com, grupo_nome from tbl_com_info where grupo_com<>'' or rem_chat=".$infoUser['id_user']." or dest_chat=".$infoUser['id_user']." order by dt_update desc";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
$countCom='';
for($x=0;$x<count($dados);$x++){
    //$indice = $x+1;
    $indice = $x;

    if($dados[$x]['grupo_nome']!=''){
        //echo "<br>Indice: ".$indice;
        //echo "<br>Com: ".$dados[$x]['id_com'];

        $configGrupo=0;
        $sqlGroupConfig="SELECT equipe_adm, equipe_bko, equipe_ate, cols from tbl_com_config where grupo_com_id=".$dados[$x]['id_com'];
        //echo "<br>".$sqlGroupConfig;
        $stmt = $PDO->prepare($sqlGroupConfig);
        $result = $stmt->execute();
        $infoConfigGrupo = $stmt->fetch( PDO::FETCH_ASSOC );
        //depurador($infoConfigGrupo);
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
            $stringUser = "'".$infoUser['id_user']."'";
            if (strpos($infoConfigGrupo['cols'], $stringUser) !== false) {
                $configGrupo=1;

            }
        }

        //echo "<br>".$configGrupo;

        if($configGrupo==1){
            $sql="SELECT dt_view from tbl_com_msg_group_view where group_chat=".$dados[$x]['id_com']." and user_id=".$infoUser['id_user'];
            //echo "<br>".$sql;
            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $infoView = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($infoView);
            if($infoView['dt_view']==''){
                $sqlMsg="SELECT count(id_msg) as qtd from tbl_com_msg_group where chat_group=".$dados[$x]['id_com']." and rem_id<>".$infoUser['id_user'];
                //echo '<br>'.$sqlMsg;
                $stmt = $PDO->prepare($sqlMsg);
                $result = $stmt->execute();
                $count = $stmt->fetch( PDO::FETCH_ASSOC );
                if($count['qtd']>0){
                    $blink = ' blink_me';
                    $span = ' <span class="badge bg-secondary">'.$count['qtd'].'</span>';
                } else {
                    $blink='';
                    $span='';
                }
            } else {
                $sqlMsg="SELECT count(id_msg) as qtd from tbl_com_msg_group where chat_group=".$dados[$x]['id_com']." and rem_id<>".$infoUser['id_user']." and data_hora > '".$infoView['dt_view']."'";
                //echo '<br>'.$sqlMsg;
                $stmt = $PDO->prepare($sqlMsg);
                $result = $stmt->execute();
                $count = $stmt->fetch( PDO::FETCH_ASSOC );
                if($count['qtd']>0){
                    $blink = ' blink_me';
                    $span = ' <span class="badge bg-secondary">'.$count['qtd'].'</span>';
                } else {
                    $blink='';
                    $span='';
                }
            }


            if($indice==$_POST['indice'] && $dados[$x]['id_com']==$_POST['com']){
                $active = ' active-tab';
                //echo "<br> active-tab ".$indice;
                $blink='';
                $span='';
            } else {
                $active = '';
            }


            echo '<div class="tab '.$active.''.$blink.'" id="title-'.$indice.'"  onclick="selAbaCom('.$indice.','.$dados[$x]['id_com'].')">'.ucwords(strtolower($dados[$x]['grupo_nome'])).''.$span.'</div>';
        }

    }
    if($dados[$x]['rem_chat']==$infoUser['id_user']){
        $sqlVisual="SELECT count(id_msg) as qtd from tbl_com_msg where dt_visual is null and com_id=".$dados[$x]['id_com']." and dest_id=".$infoUser['id_user'];
        //echo '<br>'.$sqlVisual;
        $stmt = $PDO->prepare($sqlVisual);
        $result = $stmt->execute();
        $count = $stmt->fetch( PDO::FETCH_ASSOC );
        if($count['qtd']>0){
            $blink = ' blink_me';
            $span = ' <span class="badge bg-secondary">'.$count['qtd'].'</span>';
        } else {
            $blink='';
            $span='';
        }
        if($indice==$_POST['indice'] && $dados[$x]['id_com']==$_POST['com']){
            $active = ' active-tab';
            //echo "<br> active-tab ".$indice;
            $blink='';
            $span='';
        } else {
            $active = '';
        }
        echo '<div class="tab'.$active.''.$blink.'" id="title-'.$indice.'"  onclick="selAbaCom('.$indice.','.$dados[$x]['id_com'].')">'.ucwords(strtolower($dados[$x]['dest_nome'])).''.$span.'</div>';
    }
    if($dados[$x]['dest_chat']==$infoUser['id_user']){
        $sqlVisual="SELECT count(id_msg) as qtd from tbl_com_msg where dt_visual is null and com_id=".$dados[$x]['id_com']." and dest_id=".$infoUser['id_user'];
        //echo '<br>'.$sqlVisual;
        $stmt = $PDO->prepare($sqlVisual);
        $result = $stmt->execute();
        $count = $stmt->fetch( PDO::FETCH_ASSOC );
        if($count['qtd']>0){
            $blink = ' blink_me';
            $span = ' <span class="badge bg-secondary">'.$count['qtd'].'</span>';
        } else {
            $blink='';
            $span='';
        }
        if($indice==$_POST['indice'] && $dados[$x]['id_com']==$_POST['com']){
            $active = ' active-tab';
            $blink='';
            $span='';
        } else {
            $active = '';
        }
        echo '<div class="tab'.$active.''.$blink.'" id="title-'.$indice.'"  onclick="selAbaCom('.$indice.','.$dados[$x]['id_com'].')">'.ucwords(strtolower($dados[$x]['rem_nome'])).''.$span.'</div>';
    }


}
echo "<script>laodComCount();</script>";
