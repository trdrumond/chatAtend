<?php

//require_once __DIR__ . '/vendor/autoload.php';

include("dadosPdf.php");

echo $dadosPdf;

/*
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($dadosPdf);
$mpdf->Output();
*/

?>
<script>

    const myTimeout = setTimeout(printar, 2000);

    function printar(){

        window.print();




    }
</script>
