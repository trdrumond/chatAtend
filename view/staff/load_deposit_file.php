<?php
include("../cnf/session.php");

//depurador($_POST);



//$sql="SELECT id_file, token_chat, link_file, name_file, resp, data_hora from tbl_chat_files where token_chat='".$_POST['token']."'  order by id_file asc";

$sql="SELECT a.id_file, a.token_chat, a.link_file, a.name_file, a.resp, a.data_hora, b.id_chat from tbl_chat_files a, tbl_chat_info b where a.token_chat=b.token_chat and b.id_chat=".$_POST['chatId']." order by id_file asc";

//echo "<br>".$sql;

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
if(count($dados)>0){
    for($x=0;$x<count($dados);$x++){
        if($dados[$x]['link_file']!=''){
            echo '<div class="file_chat"><a href="'.$dados[$x]['link_file'].'" target="_blank"><i class="fas fa-file-alt fa-2x"></i><br>'.$dados[$x]['name_file'].'</a></div>';
        }
    }
} else { echo '<br><br><center><h6>Os arquivos ficam disponíveis no sistema por 90 dias.</h6></center>';}
?>
