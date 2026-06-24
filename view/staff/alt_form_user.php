<?php
include("../cnf/session.php");


$sql="UPDATE tbl_user SET form_id='".$_POST['form']."' where id_user=".$_POST['id'];

//echo $sql;

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
?>
<script>
    Swal.fire(
                'Sua Demanda foi alterada!',
                '',
                'success'
            );


</script>
<?php
    }

    ?>
