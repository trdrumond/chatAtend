<?php
include("../cnf/session.php");

//var_dump($_POST);

$sql="SELECT id_user, fila_id, (SELECT filas from tbl_user_filas where user_id=id_user) as filas, contrato_id from tbl_user where id_user=?";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute([(int) ($_POST['id'] ?? 0)]);
    $dados = $stmt->fetch( PDO::FETCH_ASSOC );
    if (!is_array($dados) || !stContratoAllowed($infoUser ?? [], $infoUserConfig ?? [], (int) ($dados['contrato_id'] ?? 0))) {
        return;
    }

    $filas = (($dados['filas'] ?? '')=='') ? ($dados['fila_id'] ?? '') : $dados['filas'];
    $filas = explode(',', (string) $filas);


    $contratoIds = array_values(array_filter(array_map('intval', explode(',', (string) ($dados['contrato_id'] ?? '')))));
    $ddFilas = [];
    if (count($contratoIds) > 0) {
        $ph = implode(',', array_fill(0, count($contratoIds), '?'));
        $sql="SELECT id_fila, nome_fila from tbl_config_fila where ativo=1 and contrato_id in (".$ph.") order by id_fila asc";
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute($contratoIds);
        $ddFilas = $stmt->fetchAll( PDO::FETCH_ASSOC );
    }

?>



<div class="content-10-line">
    <center>
        <div class="switch">
            <select id="filas_config" name="filas_config[]" multiple class="form-control">
                <?php

                for($x=0;$x<count($ddFilas);$x++){
                    if(in_array($ddFilas[$x]['id_fila'], $filas)!=0){
                        $sel = 'selected';
                    } else {$sel = '';}
                    echo '<option value="'.(int) $ddFilas[$x]['id_fila'].'" '.$sel.'>'.stHtml($ddFilas[$x]['nome_fila']).'</option>';
                }
                ?>
            </select>
        </div>
    </center>
</div>

<div class="modal-footer" style="width: 100%; float: left;">
    <div id="feed_fil"></div>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i></button>
    <button type="button" id="save_fil" class="btn btn-success"><i class="fas fa-save"></i></button>
</div>


<script>
$(document).ready(function() {



    $("#save_fil").click(function() {
        var id = <?= (int) ($dados['id_user'] ?? 0) ?>;
        var filas = $('#filas_config').val();
        filUser(id, filas);
    });


    function filUser(id, filas) {
        $("#feed_fil").html(
            '<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>'
        );

        $.post("staff/alt_filas_config.php", {

                id,
                filas
            },
            function(valor) {
                $("#feed_fil").html(valor);
            });
    }

});
</script>