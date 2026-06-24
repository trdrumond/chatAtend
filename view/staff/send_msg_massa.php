<!--<script type="text/javascript" src='chat/assets/js/script_com_ind.js?<?= time() ?>' defer></script>-->
<?php
include("../cnf/session.php");

//depurador($_SESSION);
//depurador($_POST);

//echo count($_POST['col']);
if($_POST['msg']!=''){
    $_POST['msg']= str_replace(array("\n","\r","\r\n"),'',$_POST['msg']);
    for($x=0; $x < count($_POST['col']); $x++ ){
        //echo "<br>".$_POST['col'][$x];
        //echo "<br>".$_POST['msg'];


        $tk = strtotime(date('Y-m-d H:i:s')).$_POST['col'][$x];
        $id_com='';
        $sql_1="SELECT id_com from tbl_com_info where rem_chat=".$infoUser['id_user']." and dest_chat=".$_POST['col'][$x];
        $stmt = $PDO->prepare($sql_1);
        $result = $stmt->execute();
        $info_1 = $stmt->fetch( PDO::FETCH_ASSOC );

        $sql_2="SELECT id_com from tbl_com_info where dest_chat=".$infoUser['id_user']." and rem_chat=".$_POST['col'][$x];
        $stmt = $PDO->prepare($sql_2);
        $result = $stmt->execute();
        $info_2 = $stmt->fetch( PDO::FETCH_ASSOC );

        if($info_1['id_com']!=''){
            $id_com=$info_1['id_com'];
        }
        if($info_2['id_com']!=''){
            $id_com=$info_2['id_com'];
        }
        if($id_com==''){
            //echo " - Sem id_com";
            $sql="INSERT INTO tbl_com_info (contrato_id, rem_chat, dest_chat, grupo_com, grupo_nome) VALUES ('".$infoUser['contrato_id']."', '".$infoUser['id_user']."', '".$_POST['col'][$x]."', '".$_POST['grupo_com']."', '".$_POST['grupo_nome']."')";
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
            if($result==1){
                $sql_3="SELECT id_com from tbl_com_info where rem_chat=".$infoUser['id_user']." and dest_chat=".$_POST['col'][$x];
                $stmt = $PDO->prepare($sql_3);
                $result = $stmt->execute();
                $info_3 = $stmt->fetch( PDO::FETCH_ASSOC );
                if($info_3['id_com']!=''){
                    $id_com=$info_3['id_com'];
                    //echo " - ".$id_com;
                }
            }
        }

        if($id_com!=''){
            //echo " - ".$id_com;
            ?>
                <div id="feed_massa_<?=$id_com?>"></div>
                <script>
                    //console.log('chegou aqui ' + <?=$id_com?>);

                    chat_com_<?=$id_com?>();

                    function chat_com_<?=$id_com?>(){

                        //console.log('executa função de envio ' + <?=$id_com?>)

                        var msg = '<?=$_POST['msg']?>';
                        var rem = '<?=$_POST['rem']?>';
                        var dest = '<?=$_POST['col'][$x]?>';
                        var com = '<?=$id_com?>';
                        var nome = '<?=$_POST['nome']?>';
                        var img = '<?=$_POST['img']?>';
                        var tk = '<?=$tk?>';

                        //console.log('Show ' + <?=$id_com?>);

                        saveMsgComMassa(msg, rem, dest, com, nome, img, tk);

                    }


                </script>
            <?php
        }
    }
}




?>
