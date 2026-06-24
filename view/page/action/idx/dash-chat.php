<script>userRem = <?=$infoUser['id_user']?>;</script>
<?php


  if($infoUser['nivel_id']<=2){
    $qyer = " and nivel_id>2";
  } else {
    $qyer = " and nivel_id<=2";
  }





  $sql="SELECT id_user, concat(nome, ' ', sobrenome) as nome_completo, token from tbl_user where id_user<>".$infoUser['id_user']." and ativo=1 $qyer order by nome_completo";
  //echo "<br>".$sql;
  $stmt = $PDO->prepare($sql);
  $result = $stmt->execute();
  $ddConversa = $stmt->fetchAll( PDO::FETCH_ASSOC );
  //depurador($ddConversa);


?>



<ul class="nav nav-tabs" id="tabChat" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="user-tab" data-bs-toggle="tab" data-bs-target="#user" type="button" role="tab" aria-controls="user" aria-selected="true">Geral</button>
    </li>
  <?php for($x=0;$x<count($ddConversa);$x++){ ?>
    <li class="nav-item" role="presentation" id="tab_<?=$ddConversa[$x]['id_user']?>">
      <button class="nav-link" id="user-tab_<?=$ddConversa[$x]['id_user']?>" data-bs-toggle="tab" data-bs-target="#user_<?=$ddConversa[$x]['id_user']?>" type="button" role="tab" aria-controls="user_<?=$ddConversa[$x]['id_user']?>" aria-selected="true"><?=ucwords(strtolower($ddConversa[$x]['nome_completo']))?></button>
    </li>
  <?php } ?>
</ul>




<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="user" role="tabpanel" aria-labelledby="user-tab">

        <?php
            $userDestinatario = 0;


            $sql="SELECT token_chat from tbl_chat_info where status_chat=1 and contrato_id=".$infoUser['contrato_id']." and dest_chat=".$userDestinatario;
            //echo "<br>".$sql;


            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
            //depurador($infoChat);

            if($infoChat!=''){
                $tokenChat = $infoChat['token_chat'];
            }
            /*
            else {
                $stringToken = $userDestinatario . date('YmdHis');
                $tokenChat = md5($stringToken);
                $sql = "INSERT INTO tbl_chat_info (contrato_id, token_chat, dest_chat, status_chat) VALUES ('".$infoUser['contrato_id']."', '".$tokenChat."', '".$userDestinatario."', 1)";
                //echo "<br>".$sql;
                $stmt = $PDO->prepare( $sql );
                $result = $stmt->execute();
            }
            */


            include("chat/chat_ind.php");
        ?>
    </div>
  <?php for($x=0;$x<count($ddConversa);$x++){ ?>
    <div class="tab-pane fade" id="user_<?=$ddConversa[$x]['id_user']?>" role="tabpanel" aria-labelledby="user-tab_<?=$ddConversa[$x]['id_user']?>">

      <?php
        $userDestinatario = $ddConversa[$x]['id_user'];
        //echo "<br>".$userDestinatario;

        if($infoUser['nivel_id']<2){
            $sql="SELECT id_chat, token_chat, status_chat from tbl_chat_info where status_chat=1 and contrato_id=".$infoUser['contrato_id']." and rem_chat=".$infoUser['id_user']." and dest_chat=".$userDestinatario;
        } else {
            $sql="SELECT id_chat, token_chat, status_chat from tbl_chat_info where status_chat=1 and contrato_id=".$infoUser['contrato_id']." and rem_chat=".$userDestinatario." and dest_chat=".$infoUser['id_user'];
        }

        //echo "<br>".$sql;

        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $infoChat = $stmt->fetch( PDO::FETCH_ASSOC );
        //depurador($infoChat);
        if($infoChat!=''){
            $tokenChat = $infoChat['token_chat'];
        }
        /*
        else {
            $stringToken = $userDestinatario . date('YmdHis');
            $tokenChat = md5($stringToken);
            if($infoUser['nivel_id']<2){
                $sql = "INSERT INTO tbl_chat_info (contrato_id, token_chat, rem_chat, dest_chat, status_chat) VALUES ('".$infoUser['contrato_id']."', '".$tokenChat."', '".$infoUser['id_user']."', '".$userDestinatario."', 1)";
            } else {
                $sql = "INSERT INTO tbl_chat_info (contrato_id, token_chat, rem_chat, dest_chat, status_chat) VALUES ('".$infoUser['contrato_id']."', '".$tokenChat."', '".$userDestinatario."', '".$infoUser['id_user']."', 1)";
            }

            //echo "<br>".$sql;
            $stmt = $PDO->prepare( $sql );
            $result = $stmt->execute();
        }
        */

        include("chat/chat_ind.php");
      ?>
    </div>
  <?php } ?>

    <!--
        <div class="tab-pane fade" id="regional" role="tabpanel" aria-labelledby="regional-tab">...</div>
    -->
</div>
<script type="text/javascript" src="js/load.js"></script>
