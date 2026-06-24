<?php
include("../cnf/session.php");
//echo "<h6>Arquivos:</h6>";

$sql="SELECT id_file, token_chat, link_file, name_file, resp, data_hora from tbl_chat_files where token_chat='".$_POST['token']."'  order by id_file asc";

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
