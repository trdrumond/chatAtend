<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);


if($_POST['msg']!=''){
    $sql="SELECT id_chat from tbl_chat_info where status_chat=1 and token_chat='".$_POST['tokenChat']."'";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );

    if (strpos($_POST['msg'], '<img') !== false) {
        $imgSrc = null;
        if (preg_match('/<img\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $_POST['msg'], $imgMatch)) {
            $imgSrc = $imgMatch[2];
        }

        if ($imgSrc !== null && $imgSrc !== '') {
            $sql = "SELECT count(chat_id) as qtd from tbl_img where chat_id=" . (int) $infoChat['id_chat'];
            $stmt = $PDO->prepare($sql);
            $stmt->execute();
            $infoImg = $stmt->fetch(PDO::FETCH_ASSOC);
            $key = ((int) $infoImg['qtd']) + 1;

            $_POST['msg'] .= '<p><a href=staff/img.php?id=' . (int) $infoChat['id_chat'] . '&key=' . $key . ' target="_blank">Abrir imagem</a></p>';

            $stmt = $PDO->prepare(
                'INSERT INTO tbl_img (chat_id, token_chat, src, chave) VALUES (:chat_id, :token_chat, :src, :chave)'
            );
            $stmt->execute([
                ':chat_id' => (int) $infoChat['id_chat'],
                ':token_chat' => $_POST['tokenChat'],
                ':src' => $imgSrc,
                ':chave' => (string) $key,
            ]);
        }
    }


    if($_POST['flag']!=''){
        $sql = "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg, flag) VALUES ('".$infoChat['id_chat']."', '".$_POST['contrato']."', '".(int)$_POST['rem']."', '".$_POST['dest']."', '".$_POST['msg']."', '".(int)$_POST['flag']."')";
    } else {
        $sql = "INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES ('".$infoChat['id_chat']."', '".$_POST['contrato']."', '".(int)$_POST['rem']."', '".$_POST['dest']."', '".$_POST['msg']."')";
    }

    //echo "<br>".$sql;
    $stmt = $PDO->prepare( $sql );
    $result = $stmt->execute();
}

?>
