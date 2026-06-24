<?php
include("../cnf/session.php");

//depurador($_POST);

$sql = "SELECT count(id_file) as qtd FROM tbl_com_files where com_id='".$_POST['com']."'";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoQtd = $stmt->fetch( PDO::FETCH_ASSOC );

$name_file = str_pad($infoQtd['qtd']+1, 3, '0', STR_PAD_LEFT);

$sql="INSERT INTO tbl_com_files (link_file, name_file, rem, com_id) VALUES ('".$_POST['file']."', '".$name_file."', '".$_POST['rem']."', '".$_POST['com']."')";

//echo "<br>".$sql;


$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
?>
<script>
    //sendFileCom(<?=$_POST['com']?>);

    name_file = '<?=$name_file; ?>';
    link = '<?=$_POST['file']; ?>';
    //console.log('save_file_com:' + name_file);

</script>

<?php } ?>
