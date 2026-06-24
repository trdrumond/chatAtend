<?php
include("../cnf/session.php");


//depurador($_POST);

?>
<ul class="nav nav-tabs" id="myTab" role="tablist">

    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab_fila_<?= $_POST['fila']; ?>" data-bs-toggle="tab" data-bs-target="#div_fila_<?= $_POST['fila']; ?>" type="button" role="tab" aria-controls="div_fila_<?= $_POST['fila']; ?>" aria-selected="true"><strong>FILA</strong></button>
    </li>


</ul>


<div class="tab-content" id="info_fila_content">
    <div class="tab-pane fade show active" id="div_fila_<?= $_POST['fila']; ?>" role="tabpanel" aria-labelledby="tab_fila_<?= $_POST['fila']; ?>">
    </div>




</div>