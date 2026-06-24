<?php
include("../cnf/session.php");

//depurador($_POST);

$sql = "SELECT count(id_file) as qtd FROM tbl_com_files where token_chat='".$_POST['tokenChat']."'";
//echo "<br>".$sql;
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$infoQtd = $stmt->fetch( PDO::FETCH_ASSOC );

$name_file = str_pad($infoQtd['qtd']+1, 3, '0', STR_PAD_LEFT);

$sql="INSERT INTO tbl_com_files (link_file, name_file, resp) VALUES ('".$_POST['file']."', '".$name_file."', '".$_POST['rem']."')";

//echo $sql;


$stmt = $PDO->prepare( $sql );
$result = $stmt->execute();

if($result==1){
?>
<script>
    sendFile(<?=$_POST['chatId']?>);
    name_file = '<?=$name_file; ?>';
    //console.log(name_file);

</script>

<?php } ?>
