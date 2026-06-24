<?php
include("../cnf/session.php");

$id = isset($_POST['id']) ? (string) $_POST['id'] : '';

if ($id !== '' && !empty($infoMeRapida)) {
    foreach ($infoMeRapida as $row) {
        if ((string) $row['id_campo'] === $id) {
            echo $row['txt'];
            break;
        }
    }
}

?>
