<?php
include("../cnf/conn.php");

$filaId = (int) ($_POST['fila'] ?? 0);

?>
            <ul class="nav nav-tabs" id="myTab" role="tablist">

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab_fila_<?= $filaId ?>" data-bs-toggle="tab" data-bs-target="#div_fila_<?= $filaId ?>" type="button" role="tab" aria-controls="div_fila_<?= $filaId ?>" aria-selected="true"><strong>FILA</strong></button>
                                </li>

            </ul>


            <div class="tab-content" id="info_fila_content">
                <div class="tab-pane fade show active" id="div_fila_<?= $filaId ?>" role="tabpanel" aria-labelledby="tab_fila_<?= $filaId ?>">
                </div>

            </div>
