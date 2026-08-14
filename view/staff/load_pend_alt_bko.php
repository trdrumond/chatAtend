<?php
include("../cnf/session.php");

//depurador($_POST);


?>
    <select name="sel_bko" id="sel_bko">
        <option value="">Backoffice</option>
        <?php
            $sql="SELECT id_user, concat(nome, ' ', sobrenome) as nome from tbl_user where ativo=1 and nivel_id=4 order by nome asc";
            //echo "<br>".$sql;

            $stmt = $PDO->prepare($sql);
            $result = $stmt->execute();
            $dds = $stmt->fetchAll( PDO::FETCH_ASSOC );
            $idBko = (int) ($_POST['id_bko'] ?? 0);
            for($y=0;$y<count($dds);$y++){
                if($idBko==(int) $dds[$y]['id_user']){$sel="selected";} else {$sel="";}
                echo '<option value="'.(int) $dds[$y]['id_user'].'" '.$sel.'>'.stHtml($dds[$y]['nome']).'</option>';
            }
        ?>
    </select>
    <button id="btn_alt_bko">Salvar</button>

    <script>
        $("#btn_alt_bko").click(function(){
            //console.log('clicou');
            var bko = $('#sel_bko').val();
            //console.log(sol);
            if(bko != ''){
                altBko(bko, <?= (int) ($_POST['fila_chat_id'] ?? 0) ?>);
            }

        });
        function altBko(bko, fila){
            var div = '#bko_id';
            $(div).html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..." width="50"></div>');

            $.post("staff/alt_pend_bko.php",
            {
                bko, fila
            },
            function (valor) {
                $(div).html(valor);
            });
        }
    </script>
