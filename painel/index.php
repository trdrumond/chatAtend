<?php
$patch_local = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'];
$patch = 'https://solvetask.logos-ma.com.br';
//var_dump($_GET);
?>
<!DOCTYPE html
    PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//PT-BR" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>PAINEL SOLVETASK</title>
    <link rel="shortcut icon" href="../imagem/favicon.png">
    <meta http-equiv="refresh" content="1200">



    <link href="<?=$patch?>/css/bootstrap-5.0.2/css/bootstrap.min.css?<?= time() ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- <script src="https://kit.fontawesome.com/d916662550.js" crossorigin="anonymous"></script> -->
    <script src="<?=$patch_local?>/js/fontawesome/js/all.js?<?= time() ?>" crossorigin="anonymous"></script>
    <script type="text/javascript" src="<?=$patch?>/js/jquery.js?<?= time() ?>"></script>
    <script type="text/javascript" src="<?=$patch?>/js/jquery.titlealert.js?<?= time() ?>"></script>
    <script type="text/javascript" src="<?=$patch?>/js/jquery.form.js?<?= time() ?>"></script>
    <script src="../view/js/action.js?<?= time() ?>"></script>
    <script src="../view/js/minus.js?<?= time() ?>"></script>

    <script src="<?=$patch?>/css/bootstrap-5.0.2/js/popper.min.js?<?= time() ?>"></script>
    <script src="<?=$patch?>/css/bootstrap-5.0.2/js/bootstrap.min.js?<?= time() ?>"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script type="text/javascript" src="../painel/api.js?<?= time() ?>"></script>


    <script src="<?=$patch?>/js/sweetalert2/dist/sweetalert2.all.js?<?= time() ?>"></script>

    <!-- CONEXÃO COM WEBSOCKET -->
    <!-- <script type="text/javascript" src='../view/chat/assets/js/script.js?<?= time() ?>' defer></script> -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../view/css/style.css?v=<?= filemtime(dirname(__DIR__) . '/view/css/style.css') ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../view/css/style-materialize.css?v=<?= filemtime(dirname(__DIR__) . '/view/css/style-materialize.css') ?>">

    <style>
        body { background: #f0f4f8; font-family: 'Inter', sans-serif; }
        #body { padding: 16px; min-height: 100vh; }
    </style>

</head>

<body>
    <div id="body">
        <div id="dadosPainel"></div>
        <script>
        dadosPainel();
        </script>


    </div>

    <link rel="stylesheet" type="text/css" href="<?=$patch?>/js/datatable-complete/datatables.css?<?= time() ?>">


    <link rel="stylesheet" type="text/css"
        href="<?=$patch?>/js/datatable-complete/select-1.3.3/css/select.dataTables.min.css?<?= time() ?>">

    <link rel="stylesheet" type="text/css"
        href="<?=$patch?>/js/datatable-complete/buttons-2.0.1/css/buttons.dataTables.min.css?<?= time() ?>">
    <link rel="stylesheet" type="text/css"
        href="<?=$patch?>/js/datatable-complete/datetime-1.1.1/css/dataTables.dateTime.min.css?<?= time() ?>">
    <link rel="stylesheet" type="text/css"
        href="<?=$patch?>/js/datatable-complete/colreorder-1.5.4/css/colReorder.dataTables.min.css?<?= time() ?>">

    <script type="text/javascript" charset="utf8"
        src="<?=$patch?>/js/datatable-complete/jquery-1.10.25/jquery.dataTables.min.js?<?= time() ?>"></script>


    <script type="text/javascript"
        src="<?=$patch?>/js/datatable-complete/select-1.3.3/js/datatables.select.min.js?<?= time() ?>"></script>
    <script type="text/javascript"
        src="<?=$patch?>/js/datatable-complete/datetime-1.1.1/js/dataTables.dateTime.min.js?<?= time() ?>"></script>
    <script type="text/javascript"
        src="<?=$patch?>/js/datatable-complete/colreorder-1.5.4/js/dataTables.colReorder.min.js?<?= time() ?>"></script>
    <script type="text/javascript"
        src="<?=$patch?>/js/datatable-complete/buttons-1.7.1/datatables.buttons.min.js?<?= time() ?>"></script>
    <script type="text/javascript" src="<?=$patch?>/js/datatable-complete/jszip-2.5.0/jszip.min.js?<?= time() ?>">
    </script>
    <script type="text/javascript" src="<?=$patch?>/js/datatable-complete/pdfmake-0.1.36/pdfmake.min.js?<?= time() ?>">
    </script>
    <script type="text/javascript" src="<?=$patch?>/js/datatable-complete/buttons-1.7.1/buttons.html5.min.js"></script>
    <script type="text/javascript" src="<?=$patch?>/js/datatable-complete/pdfmake-0.1.36/vfs_fonts.js"></script>
    <script type="text/javascript"
        src="<?=$patch?>/js/datatable-complete/buttons-2.0.1/js/buttons.print.min.js?<?= time() ?>"></script>

    <script src="<?=$patch?>/js/charts/core.js"></script>
    <script src="<?=$patch?>/js/charts/charts.js"></script>
    <script src="<?=$patch?>/js/charts/frozen.js"></script>
    <script src="<?=$patch?>/js/charts/animated.js"></script>


</body>

</html>