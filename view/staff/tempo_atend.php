<?php
include("../cnf/session.php");

//var_dump($_POST);

if(isset($_POST['horario'])){
    $_POST['horario'];
    $timeDiff = (time_to_sec(date('H:i:s')))-(time_to_sec(date('H:i:s', strtotime($_POST['horario']))));
    echo $timeDiff = sec_to_time($timeDiff);
}

