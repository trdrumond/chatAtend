<?php
include("../cnf/session.php");

$sql="SELECT src from tbl_img where chat_id=".$_GET['id']." and chave=".$_GET['key'];
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $infoImg = $stmt->fetch( PDO::FETCH_ASSOC );

echo '<img src='.$infoImg['src'].' style="width: 100%">';
