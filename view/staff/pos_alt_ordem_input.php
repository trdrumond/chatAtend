<?php
include("../cnf/conn.php");
depurador($_POST);


    $very="SELECT campo_id, (SELECT desc_campo from tbl_forms_pos_input_campo where id_campo=campo_id) as desc_campo, ordem from tbl_forms_pos_input_campo_cnf where campo_id=".$_POST['id_campo'];
    $stmt = $PDO->prepare( $very );
    $result = $stmt->execute();
    $ver = $stmt->fetch( PDO::FETCH_ASSOC );
    //echo " - campo: ".$ver['id_campo']." - ".$ver['desc_campo']." - ".$ver['ordem'];
    if($_POST['ordem']<$ver['ordem']){
        //echo "<br>"."ordem menor";
        $sql_2 = "UPDATE tbl_forms_pos_input_campo_cnf SET ordem='0' where campo_id=".$_POST['id_campo'];
        $stmt = $PDO->prepare( $sql_2 );
        $result_2 = $stmt->execute();
        for($x=($_POST['ordem']);$x<$ver['ordem'];$x++){
            //echo "<br>".$x;
            $very_1="SELECT campo_id, (SELECT desc_campo from tbl_forms_pos_input_campo where id_campo=campo_id) as desc_campo from tbl_forms_pos_input_campo_cnf where form_id=".$_POST['fila']." and ordem=".$x;
            $stmt = $PDO->prepare( $very_1 );
            $result = $stmt->execute();
            $ver_1 = $stmt->fetch( PDO::FETCH_ASSOC );
            //echo " - campo: ".$ver_1['campo_id']." - ".$ver_1['desc_campo'];
            $newOrder = $x+1;
            $sql_1 = "UPDATE tbl_forms_pos_input_campo_cnf SET ordem='".$newOrder."' where campo_id=".$ver_1['campo_id'];
            $stmt = $PDO->prepare( $sql_1 );
            $result_1 = $stmt->execute();
            //if($result_1==1){ echo " - mod;";}
            //echo "<br>".$sql_1;
        }
        $sql_2 = "UPDATE tbl_forms_pos_input_campo_cnf SET ordem='".$_POST['ordem']."' where campo_id=".$_POST['id_campo'];
        $stmt = $PDO->prepare( $sql_2 );
        $result_2 = $stmt->execute();
    } else if($_POST['ordem']>$ver['ordem']){
        //echo "<br>"."ordem Maior";
        $sql_2 = "UPDATE tbl_forms_pos_input_campo_cnf SET ordem='0' where campo_id=".$_POST['id_campo'];
        $stmt = $PDO->prepare( $sql_2 );
        $result_2 = $stmt->execute();
        for($x=($ver['ordem']);$x<$_POST['ordem'];$x++){
            //echo "<br>".$x;
            $very_1="SELECT campo_id, (SELECT desc_campo from tbl_forms_pos_input_campo where id_campo=campo_id) as desc_campo from tbl_forms_pos_input_campo_cnf where form_id=".$_POST['fila']." and ordem=".($x+1);
            $stmt = $PDO->prepare( $very_1 );
            $result = $stmt->execute();
            $ver_1 = $stmt->fetch( PDO::FETCH_ASSOC );
            //echo " - campo: ".$ver_1['id_campo']." - ".$ver_1['desc_campo'];
            $sql_1 = "UPDATE tbl_forms_pos_input_campo_cnf SET ordem='".$x."' where campo_id=".$ver_1['campo_id'];
            $stmt = $PDO->prepare( $sql_1 );
            $result_1 = $stmt->execute();
            //if($result_1==1){ echo " - mod;";}
        }

        $sql_2 = "UPDATE tbl_forms_pos_input_campo_cnf SET ordem='".$_POST['ordem']."' where campo_id=".$_POST['id_campo'];
        $stmt = $PDO->prepare( $sql_2 );
        $result_2 = $stmt->execute();


    }

    ?>

        <script>
                load(<?php echo $_POST['fila']; ?>);

                function load(id_filas){

                    $("#tbl_<?php echo $_POST['fila']; ?>").html('<div id="load_gif"><img src="img/loading.gif" alt="Carregando..."></div>');
                        $.post("staff/pos_tbl_config_form.php",
                    {
                        id_filas: id_filas
                    },
                    function (valor) {
                        $("#tbl_<?php echo $_POST['fila']; ?>").html(valor);
                    });

                }
        </script>

