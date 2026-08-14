<?php
include("../cnf/session.php");

//depurador($_POST);


?>
    <select name="sel_sol" id="sel_sol">
        <option value="">Solicitante</option>
        <?php
            $sql="SELECT id_user, concat(nome, ' ', sobrenome) as nome from tbl_user where ativo=1 and nivel_id=5 order by nome asc";
            //echo "<br>".$sql;

            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $idSol = (int) ($_POST['id_solicitante'] ?? 0);
            $filaChatId = (int) ($_POST['fila_chat_id'] ?? 0);
            for($y=0;$y<count($dds);$y++){
                if($idSol==(int) $dds[$y]['id_user']){$sel="selected";} else {$sel="";}
                echo '<option value="'.(int) $dds[$y]['id_user'].'" '.$sel.'>'.stHtml($dds[$y]['nome']).'</option>';
            }
        ?>
    </select>
    <button id="btn_alt_sol">Salvar</button>

    <script>
        $("#btn_alt_sol").click(function(){
            //console.log('clicou');
            var sol = $('#sel_sol').val();
            //console.log(sol);
            if(sol != ''){
                altSol(sol, <?= (int) ($_POST['fila_chat_id'] ?? 0) ?>);
            }

        });
        function altSol(sol, fila){
            var div = '#solicitante_id';
            $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

            $.post("staff/alt_pend_sol.php",
            {
                sol, fila
            },
            function (valor) {
                $(div).html(valor);
            });
        }
    </script>
