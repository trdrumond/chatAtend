<?php
include("../cnf/func.php");


$tempoAtendimento = (strtotime("now") - strtotime($_POST['tempo']));

echo "<i class='far fa-clock'></i> TA: ".sec_to_time($tempoAtendimento);



