<div class="action-workspace">

    <?php
    include(__DIR__ . '/cnf/session.php');
    if (!isset($infoUser) || !is_array($infoUser)) {
        $infoUser = [];
    }

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

        $sec = basename((string) ($_POST['sec'] ?? ''));
        $action = basename((string) ($_POST['action'] ?? ''));
        $allowedActions = [
            'idx' => [
                'chat-ate', 'chat-bko', 'chat-fila', 'com-idx', 'com-idx-list',
                'dash-ate', 'dash-ava', 'dash-cha', 'dash-chat', 'dash-fila',
                'dash-idx', 'dash-inicio', 'dash-pause', 'dash-scor',
                'gov-analytics', 'help', 'hist-dash', 'hist-pend', 'ia-insights',
                'new-dem', 'rel-dash', 'rel-fila', 'rel-ind',
            ],
            'cnf' => [
                'cad-age', 'cad-ass', 'cad-ctt', 'cad-emp', 'cad-faq', 'cad-fil',
                'cad-men', 'cad-pri', 'cad-reg', 'cad-usu', 'cnf-dash', 'cnf-ia',
                'log-acess-cnf', 'res-base',
            ],
            'usu' => ['pass'],
        ];
        if (!isset($allowedActions[$sec]) || !in_array($action, $allowedActions[$sec], true)) {
            http_response_code(400);
            echo 'Ação inválida.';
            return;
        }
        $actionFile = __DIR__ . '/page/action/' . $sec . '/' . $action . '.php';
        $actionReal = realpath($actionFile);
        $actionBase = realpath(__DIR__ . '/page/action/' . $sec);
        if ($actionReal === false || $actionBase === false || strpos($actionReal, $actionBase) !== 0) {
            http_response_code(400);
            echo 'Ação inválida.';
            return;
        }
        include $actionReal;
    ?>


</div>
