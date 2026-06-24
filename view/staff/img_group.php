<?php
include("../cnf/session.php");

$sql="SELECT src from tbl_com_img where com_id=".$_GET['id']." and chave=".$_GET['key'];
//echo "<br>".$sql;
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoImg = $stmt->fetch( PDO::FETCH_ASSOC );

echo '<img src='.$infoImg['src'].' style="width: 100%">';
