<?php
include("../cnf/session.php");

//depurador($_POST);
/*
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

    $sql="SELECT count(chat_id) as qtd from tbl_img where chat_id=".$_POST['chat_id'];
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoImg = $stmt->fetch( PDO::FETCH_ASSOC );

    if($infoImg['qtd']==0){
        $key = $infoImg['qtd']+1;
    } else {
        $key = $infoImg['qtd'];
    }


    //$key = $infoImg['qtd']+1;



    //$_POST['msg'].='<p><a href=staff/img.php?id='.$_POST['chat_id'].'&key='.$key.' target="_blank">Abrir imagem</a></p>';


}
*/
if($_POST['how']=='other'){
    //echo "executa script de leitura";
    $sqlVisual="UPDATE tbl_com_msg SET dt_visual=now() where dt_visual is null and com_id=".$_POST['chatId']." and dest_id=".$infoUser['id_user'];
    //echo "<br>".$sqlVisual;
    $stmt = $PDO->prepare( $sqlVisual );
    $result = $stmt->execute();
    /*
    if($result){
        echo "executa script de leitura";
    }
    */


}


echo $_POST['msg'];
?>





