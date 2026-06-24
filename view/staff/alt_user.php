<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

//depurador($_POST);

if($_POST['ativo']==0){ $qryInativo = ", data_inativo=now()"; } else {$qryInativo = ", data_inativo=null";}

$qryNivel = '';
if($_POST['nivel']!=''){
    $qryNivel = ", nivel_id='".$_POST['nivel']."'";
}
$sql="UPDATE tbl_user SET nome='".$_POST['nome']."', sobrenome='".$_POST['sobrenome']."', email='".$_POST['email']."', uf_id='".$_POST['uf']."', municipio_id='".$_POST['municipio']."', contrato_id='".$_POST['contrato']."', regional_id='".$_POST['regional']."', empresa_id='".$_POST['empresa']."', agencia_id='".$_POST['agencia']."', fila_id='".$_POST['fila']."', ativo='".$_POST['ativo']."', data_update=curdate()  $qryNivel $qryInativo where id_user=".$_POST['id'];

//echo $sql;

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
    clearUserLayoutCache((int) $_POST['id']);
?>
<script>
$("#modal_alt").modal('hide');
actionPage('cad-usu', 'cnf');



function actionPage(action, sec) {
    $("#action-page").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="100"></div>');
    //console.log('A ação é: ' + action);
    $.post("action.php", {
            action: action,
            sec: sec
        },
        function(valor) {
            $("#action-page").html(valor);
        });
}
</script>
<?php
    }

    ?>
