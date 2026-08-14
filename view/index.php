<?php
require_once __DIR__ . '/cnf/session.php';
require_once __DIR__ . '/cnf/cache_layout.php';

/** @var array<string, mixed> $infoUser */

$solvetaskFsRoot = dirname(__DIR__, 2);
$solvetaskRoot = rtrim(str_replace('\\', '/', dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')))), '/');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? 'https'
    : ($_SERVER['REQUEST_SCHEME'] ?? 'http');
$patch = $scheme . '://' . $_SERVER['HTTP_HOST'] . $solvetaskRoot;
$isLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1' || strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0);
$patch_ = $isLocal ? $patch : ($scheme . '://solvetask.logos-ma.com.br');

$assetVer = static function (string $path): string {
    return is_file($path) ? (string) filemtime($path) : '1';
};

$tinymceDir = $solvetaskFsRoot . '/js/tinymce_5_10_1';
$tinymceVerParts = [];
foreach (['/tinymce.min.js', '/themes/silver/theme.min.js'] as $tinymceRelPath) {
    $tinymceFile = $tinymceDir . $tinymceRelPath;
    if (is_file($tinymceFile)) {
        $tinymceVerParts[] = filemtime($tinymceFile);
    }
}
$tinymceVer = $tinymceVerParts ? (string) max($tinymceVerParts) : (string) time();
$tinymceShimFile = __DIR__ . '/js/st-tinymce-firefox-shim.js';
$tinymceShimVer = $assetVer($tinymceShimFile);
$tinymceLoaderFile = __DIR__ . '/js/st-tinymce-loader.js';
$tinymceLoaderVer = $assetVer($tinymceLoaderFile);
$viewJsPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/view')), '/') . '/js';
$stLoadTinyMce = ((int) ($infoUser['env_img'] ?? 0) === 1)
    || in_array($_GET['sec'] ?? '', ['idx', 'cnf'], true);

$vBootstrapCss = $assetVer($solvetaskFsRoot . '/css/bootstrap-5/css/bootstrap.min.css');
$vFontAwesome = $assetVer($solvetaskFsRoot . '/js/fontawesome/js/all.js');
$vJquery = $assetVer($solvetaskFsRoot . '/js/jquery.js');
$vJqueryTitlealert = $assetVer($solvetaskFsRoot . '/js/jquery.titlealert.js');
$vJqueryForm = $assetVer($solvetaskFsRoot . '/js/jquery.form.js');
$vActionJs = $assetVer(__DIR__ . '/js/action.js');
$vStBkoDistrib = $assetVer(__DIR__ . '/js/st-bko-distrib.js');
$vMinusJs = $assetVer(__DIR__ . '/js/minus.js');
$vPopper = $assetVer($solvetaskFsRoot . '/css/bootstrap-5/js/popper.min.js');
$vBootstrapJs = $assetVer($solvetaskFsRoot . '/css/bootstrap-5/js/bootstrap.min.js');
$vSweetalert = $assetVer($solvetaskFsRoot . '/js/sweetalert2/dist/sweetalert2.all.js');
$vStyleCss = $assetVer(__DIR__ . '/css/style.css');
$vStyleMaterialize = $assetVer(__DIR__ . '/css/style-materialize.css');
$vStChatOpen = $assetVer(__DIR__ . '/js/st-chat-open.js');
$vChatScript = $assetVer(__DIR__ . '/chat/assets/js/script.js');
$vChatNotify = $assetVer(__DIR__ . '/chat/assets/js/notify.js');

$dtBase = $solvetaskFsRoot . '/js/datatable-complete';
$vDtCss = $assetVer($dtBase . '/datatables.css');
$vDtSelectCss = $assetVer($dtBase . '/select-1.3.3/css/select.dataTables.min.css');
$vDtButtonsCss = $assetVer($dtBase . '/buttons-2.0.1/css/buttons.dataTables.min.css');
$vDtDatetimeCss = $assetVer($dtBase . '/datetime-1.1.1/css/dataTables.dateTime.min.css');
$vDtColreorderCss = $assetVer($dtBase . '/colreorder-1.5.4/css/colReorder.dataTables.min.css');
$vDtJquery = $assetVer($dtBase . '/jquery-1.10.25/jquery.dataTables.min.js');
$vDtSelectJs = $assetVer($dtBase . '/select-1.3.3/js/datatables.select.min.js');
$vDtDatetimeJs = $assetVer($dtBase . '/datetime-1.1.1/js/dataTables.dateTime.min.js');
$vDtColreorderJs = $assetVer($dtBase . '/colreorder-1.5.4/js/dataTables.colReorder.min.js');
$vDtButtonsJs = $assetVer($dtBase . '/buttons-1.7.1/datatables.buttons.min.js');
$vDtJszip = $assetVer($dtBase . '/jszip-2.5.0/jszip.min.js');
$vDtPdfmake = $assetVer($dtBase . '/pdfmake-0.1.36/pdfmake.min.js');
$vDtButtonsHtml5 = $assetVer($dtBase . '/buttons-1.7.1/buttons.html5.min.js');
$vDtVfsFonts = $assetVer($dtBase . '/pdfmake-0.1.36/vfs_fonts.js');
$vDtButtonsPrint = $assetVer($dtBase . '/buttons-2.0.1/js/buttons.print.min.js');

if (!isset($_GET['sec'])) {
    header('Location: index.php?sec=idx');
    exit;
}

$headCache = getCachedLayout('layout_head_v3_' . md5($patch), function () use (
    $patch,
    $vBootstrapCss,
    $vFontAwesome,
    $vJquery,
    $vJqueryTitlealert,
    $vJqueryForm,
    $vActionJs,
    $vStBkoDistrib,
    $vMinusJs,
    $vPopper,
    $vBootstrapJs,
    $vSweetalert,
    $tinymceLoaderVer,
    $vStyleCss,
    $vStyleMaterialize
) {
    ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Solvetask</title>
    <link rel="shortcut icon" href="../imagem/favicon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="<?= $patch ?>/css/bootstrap-5/css/bootstrap.min.css?v=<?= $vBootstrapCss ?>" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <script src="<?= $patch ?>/js/fontawesome/js/all.js?v=<?= $vFontAwesome ?>" crossorigin="anonymous"></script>
    <script type="text/javascript" src="<?= $patch ?>/js/jquery.js?v=<?= $vJquery ?>"></script>
    <script type="text/javascript" src="<?= $patch ?>/js/jquery.titlealert.js?v=<?= $vJqueryTitlealert ?>"></script>
    <script type="text/javascript" src="<?= $patch ?>/js/jquery.form.js?v=<?= $vJqueryForm ?>"></script>
    <script src="js/action.js?v=<?= $vActionJs ?>"></script>
    <script src="js/st-bko-distrib.js?v=<?= $vStBkoDistrib ?>"></script>
    <script src="js/minus.js?v=<?= $vMinusJs ?>"></script>

    <script src="<?= $patch ?>/css/bootstrap-5/js/popper.min.js?v=<?= $vPopper ?>"></script>
    <script src="<?= $patch ?>/css/bootstrap-5/js/bootstrap.min.js?v=<?= $vBootstrapJs ?>"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script src="<?= $patch ?>/js/sweetalert2/dist/sweetalert2.all.js?v=<?= $vSweetalert ?>"></script>

    <script src="js/st-tinymce-loader.js?v=<?= $tinymceLoaderVer ?>"></script>

    <link href="css/style.css?v=<?= $vStyleCss ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="css/style-materialize.css?v=<?= $vStyleMaterialize ?>">
<?php
    return ob_get_clean();
}, 600);

echo $headCache;
?>

    <script>window.ST_CSRF = <?= json_encode($stCsrf ?? '', JSON_UNESCAPED_SLASHES) ?>;</script>
    <script>
    window.stTinyMceConfig = {
        base: <?= json_encode($patch, JSON_UNESCAPED_SLASHES) ?>,
        localJs: <?= json_encode($viewJsPath, JSON_UNESCAPED_SLASHES) ?>,
        ver: <?= json_encode($tinymceVer) ?>,
        shimVer: <?= json_encode($tinymceShimVer) ?>
    };
    </script>
    <?php if ($stLoadTinyMce) { ?>
    <link rel="preload" href="<?= $patch ?>/js/tinymce_5_10_1/tinymce.min.js?v=<?= $tinymceVer ?>" as="script">
    <script>if (typeof stTinyMceReady === 'function') { stTinyMceReady(); }</script>
    <?php } ?>

    <?php if (($_GET['sec'] ?? '') === 'idx') { ?>
        <script type="text/javascript" src="js/st-chat-open.js?v=<?= $vStChatOpen ?>"></script>
        <script type="text/javascript" src="chat/assets/js/script.js?v=<?= $vChatScript ?>" defer></script>
        <script type="text/javascript" src="chat/assets/js/notify.js?v=<?= $vChatNotify ?>" defer></script>
        <script src="<?= $patch ?>/js/charts/core.js"></script>
        <script src="<?= $patch ?>/js/charts/charts.js"></script>
        <script src="<?= $patch ?>/js/charts/animated.js"></script>
        <script src="<?= $patch ?>/js/charts/material.js"></script>
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <?php } ?>

</head>

<body>
    <div id="app-topbar">
        <div class="app-brand">
            <div class="app-brand-text">
                <span class="app-brand-title">Solvetask</span>
                <span class="app-brand-dot" aria-hidden="true"></span>
                <span class="app-brand-subtitle">Atendimento inteligente</span>
            </div>
        </div>
        <div id="suspended"><?php include __DIR__ . '/content/suspended.php'; ?></div>
    </div>

    <div id="header">
        <div id="perfil">
            <?php include __DIR__ . '/content/perfil.php'; ?>
        </div>

        <div id="page">
            <?php include __DIR__ . '/page/page-' . $_GET['sec'] . '.php'; ?>
        </div>
    </div>

    <div id="app-footer">
        <span class="app-footer-status">
            <span id="footer-statusServer" title="Status da conexão">
                <span id="sinal_server_footer" class="signal <?= ($_GET['sec'] === 'idx') ? 'status_neutro' : 'status_online' ?>"></span>
            </span>
            <span id="footer-status-label"><?= ($_GET['sec'] === 'idx') ? 'Conectando...' : 'Sistema online' ?></span>
            &nbsp;·&nbsp; Versão 2.0
            <?php if (!empty($infoUser['contrato'])) { ?> &nbsp;·&nbsp; <?= htmlspecialchars($infoUser['contrato']) ?><?php } ?>
        </span>
        <span style="display:flex;align-items:center;gap:10px;">
            <img src="img/brand1.png" alt="Grupo Logos" onerror="this.style.display='none'">
            <span style="font-size:11px;color:#5c5a8a;">Grupo Logos · 2021</span>
        </span>
    </div>

    <?php
    $scriptsCache = getCachedLayout('layout_scripts_datatables_v3_' . md5($patch), function () use (
        $patch,
        $vDtCss,
        $vDtSelectCss,
        $vDtButtonsCss,
        $vDtDatetimeCss,
        $vDtColreorderCss,
        $vDtJquery,
        $vDtSelectJs,
        $vDtDatetimeJs,
        $vDtColreorderJs,
        $vDtButtonsJs,
        $vDtJszip,
        $vDtPdfmake,
        $vDtButtonsHtml5,
        $vDtVfsFonts,
        $vDtButtonsPrint
    ) {
        ob_start();
    ?>
    <link rel="stylesheet" type="text/css" href="<?= $patch ?>/js/datatable-complete/datatables.css?v=<?= $vDtCss ?>">

    <link rel="stylesheet" type="text/css"
        href="<?= $patch ?>/js/datatable-complete/select-1.3.3/css/select.dataTables.min.css?v=<?= $vDtSelectCss ?>">

    <link rel="stylesheet" type="text/css"
        href="<?= $patch ?>/js/datatable-complete/buttons-2.0.1/css/buttons.dataTables.min.css?v=<?= $vDtButtonsCss ?>">
    <link rel="stylesheet" type="text/css"
        href="<?= $patch ?>/js/datatable-complete/datetime-1.1.1/css/dataTables.dateTime.min.css?v=<?= $vDtDatetimeCss ?>">
    <link rel="stylesheet" type="text/css"
        href="<?= $patch ?>/js/datatable-complete/colreorder-1.5.4/css/colReorder.dataTables.min.css?v=<?= $vDtColreorderCss ?>">

    <script type="text/javascript" charset="utf8"
        src="<?= $patch ?>/js/datatable-complete/jquery-1.10.25/jquery.dataTables.min.js?v=<?= $vDtJquery ?>"></script>

    <script type="text/javascript"
        src="<?= $patch ?>/js/datatable-complete/select-1.3.3/js/datatables.select.min.js?v=<?= $vDtSelectJs ?>"></script>
    <script type="text/javascript"
        src="<?= $patch ?>/js/datatable-complete/datetime-1.1.1/js/dataTables.dateTime.min.js?v=<?= $vDtDatetimeJs ?>"></script>
    <script type="text/javascript"
        src="<?= $patch ?>/js/datatable-complete/colreorder-1.5.4/js/dataTables.colReorder.min.js?v=<?= $vDtColreorderJs ?>"></script>
    <script type="text/javascript"
        src="<?= $patch ?>/js/datatable-complete/buttons-1.7.1/datatables.buttons.min.js?v=<?= $vDtButtonsJs ?>"></script>
    <script type="text/javascript" src="<?= $patch ?>/js/datatable-complete/jszip-2.5.0/jszip.min.js?v=<?= $vDtJszip ?>">
    </script>
    <script type="text/javascript" src="<?= $patch ?>/js/datatable-complete/pdfmake-0.1.36/pdfmake.min.js?v=<?= $vDtPdfmake ?>">
    </script>
    <script type="text/javascript" src="<?= $patch ?>/js/datatable-complete/buttons-1.7.1/buttons.html5.min.js?v=<?= $vDtButtonsHtml5 ?>"></script>
    <script type="text/javascript" src="<?= $patch ?>/js/datatable-complete/pdfmake-0.1.36/vfs_fonts.js?v=<?= $vDtVfsFonts ?>"></script>
    <script type="text/javascript"
        src="<?= $patch ?>/js/datatable-complete/buttons-2.0.1/js/buttons.print.min.js?v=<?= $vDtButtonsPrint ?>"></script>
    <?php
        return ob_get_clean();
    }, 600);
    echo $scriptsCache;
    ?>

    <?php if ($infoUser['nivel_id'] == 5) { ?>
        <script>
            setInterval(function() {
                verificaAtendente(<?= $infoUser['id_user'] ?>)
            }, 300000);


            function verificaAtendente(id_atendente) {

                $.post("staff/verifica_atendente.php", {
                        id_atendente
                    },
                    function(valor) {
                        $("#feed_verifica_atendente").html(valor);
                    });

            }
        </script>
        <div id="feed_verifica_atendente"></div>
    <?php } ?>

</body>

</html>
