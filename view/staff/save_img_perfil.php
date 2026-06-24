<?php
include("../cnf/session.php");
require_once __DIR__ . '/../cnf/cache_layout.php';

//depurador($_POST);

$very_1="SELECT img from tbl_user_img_perfil where user_id=".$_POST['id'];
//echo "<br>".$very_1;
$stmt = $PDO->prepare( $very_1 );
$result = $stmt->execute();
$info = $stmt->fetch( PDO::FETCH_ASSOC );

if($info['img']!=''){
    $sql="UPDATE tbl_user_img_perfil SET img='".$_POST['imgText']."' where user_id=".$_POST['id'];
} else {
    $sql="INSERT INTO tbl_user_img_perfil (user_id, img) values ('".$_POST['id']."', '".$_POST['imgText']."')";
}

//echo "<br>".$sql;

$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
    clearUserLayoutCache((int) $_POST['id']);
?>
<script>
    
        Swal.fire(
                'Imagem alterada!',
                'Sua imagem foi carregada corretamente!',
                'success'
            );
    $("#user_img_perfil").html('<img src="<?php echo $_POST['imgText']; ?>" class="rounded-circle" alt="perfil" height="80" width="80" style="margin: auto;">');
    
    


</script>
<?php
    }

    ?>
