<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);


if($_POST['msg']!=''){
    $_POST['msg']= str_replace(array("\n","\r","\r\n"),'',$_POST['msg']);

    $sql="SELECT id_com, data_hora, rem_chat, dest_chat, grupo_com from tbl_com_info where id_com=".$_POST['com'];
    //echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoCom = $stmt->fetch( PDO::FETCH_ASSOC );


    if($infoCom['rem_chat']==$infoUser['id_user']){
        $destChat=$infoCom['dest_chat'];
    }
    if($infoCom['dest_chat']==$infoUser['id_user']){
        $destChat=$infoCom['rem_chat'];
    }




    if (strpos($_POST['msg'], '<img') !== false) {
        $str = explode(" ", $_POST['msg']);
        $text = $str;
        $element = 'src';

        for($x=0;$x<count($str);$x++){
            if (strpos($str[$x], $element) !== false) {
                $stingSearch = $str[$x];
                $keySearch = $x;
            }
        }

        $link = substr($str[$keySearch], 4);

        $sql="SELECT count(com_id) as qtd from tbl_com_img where com_id=".$_POST['com'];
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoImg = $stmt->fetch( PDO::FETCH_ASSOC );
        $key = $infoImg['qtd']+1;

        $_POST['msg'].='<p><a href=staff/img_group.php?id='.$_POST['com'].'&key='.$key.' target="_blank">Abrir imagem</a></p>';

        $sql = "INSERT INTO tbl_com_img (com_id, src, chave) VALUES ('".$_POST['com']."', ".$link.", '".$key."')";
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();

    }


    if($infoCom['rem_chat']==0 && $infoCom['dest_chat']==0){
        $sql = "INSERT INTO tbl_com_msg_group (chat_group, rem_id, msg) VALUES ('".$infoCom['id_com']."', '".$_POST['rem']."', '".$_POST['msg']."')";
    } else {
        $sql = "INSERT INTO tbl_com_msg (com_id, contrato_id, rem_id, dest_id, msg) VALUES ('".$_POST['com']."', '".$infoUser['contrato_id']."', '".$infoUser['id_user']."', '".$destChat."', '".$_POST['msg']."')";
    }


    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
    if($result){
        $sql = "UPDATE tbl_com_info SET dt_update=now() where id_com=".$_POST['com'];
        //echo "<br>".$sql;
        $stmt = $PDO->prepare( $sql );
        $result = $stmt->execute();
        if($result){

            echo "<script>sendMessageCom('".$_POST['msg']."', '".$_POST['rem']."', '".$_POST['com']."', '".$_POST['nome']."', '".$_POST['img']."', '".$_POST['tk']."');</script>";
        }
    }
}

?>

