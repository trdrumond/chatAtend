<?php
include("../cnf/session.php");



$stm = $PDO->query("SELECT assuntos_id from tbl_config_fila where id_fila=".$_POST['fila']);
$ass = $stm->fetch(PDO::FETCH_ASSOC);

$assuntos = implode(",", $ass);

$sql="SELECT id_assunto, titulo_assunto from tbl_assunto where ativo=1 and id_assunto IN (".$assuntos.")"." order by titulo_assunto asc";

//echo $sql;
//echo '<option value="">'.$sql.'</option>';
$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();
$dados = $stmt->fetchAll( PDO::FETCH_ASSOC );
if(count($dados)>0){
    echo '<option value="">Todos</option>';
    for($x=0;$x<count($dados);$x++){
        echo '<option value="'.$dados[$x]['id_assunto'].'">'.$dados[$x]['titulo_assunto'].'</option>';
    }
} else {
    echo '<option value="">Assuntos</option>';
}


?>
