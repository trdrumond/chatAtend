<div class="action-workspace">

    <?php
    include(__DIR__ . '/cnf/session.php');
    if (!isset($infoUser) || !is_array($infoUser)) {
        $infoUser = [];
    }

        //var_dump($_POST);
        if(!isset($_POST['sec'])){
            $_POST['sec']=$_GET['sec'];
        }
        if($_POST['sec']=='idx'){
            if(!isset($_POST['action'])){
                $nivelLogin = (int)($_SESSION['dados']['nivel_id'] ?? 0);
                if ($nivelLogin === 4 || $nivelLogin === 5) {
                    $_POST['action'] = 'dash-' . $infoUser['idx'];
                } else {
                    $_POST['action'] = 'dash-fila';
                }
            }
        }

        if($_POST['sec']=='usu'){
            $_POST['action']='pass';
        }

        if($_POST['sec']=='cnf'){
            if(!isset($_POST['action'])){
                $_POST['action']='cnf-dash';
            }
        }
        //var_dump($_POST);

        if($infoUser['id_user']==1){
            //var_dump($_POST);
        }





        include("page/action/".$_POST['sec']."/".$_POST['action'].".php");
    ?>


</div>