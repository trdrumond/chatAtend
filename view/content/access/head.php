<?php
/* Telas de acesso (raiz de piloto_2.0_glassy): tema antes da primeira pintura + CSS Aurora Glass.
   Incluir no <head>, depois de qualquer <style> inline legado. */
$stAccessRoot = dirname(__DIR__, 3);
$stAccessVer = static function (string $rel) use ($stAccessRoot): string {
    $file = $stAccessRoot . '/' . $rel;
    return is_file($file) ? (string) filemtime($file) : '1';
};
?>
    <script>(function (d) { var t = 'light'; try { var s = window.localStorage.getItem('st-glassy-theme'); if (s === 'light' || s === 'dark') { t = s; } } catch (e) {} d.documentElement.setAttribute('data-st-theme', t); })(document);</script>
    <link rel="stylesheet" href="view/css/glassy-tokens.css?v=<?= $stAccessVer('view/css/glassy-tokens.css') ?>">
    <link rel="stylesheet" href="css/glassy-login.css?v=<?= $stAccessVer('css/glassy-login.css') ?>">
    <script src="view/js/glassy-theme-core.js?v=<?= $stAccessVer('view/js/glassy-theme-core.js') ?>" defer></script>
