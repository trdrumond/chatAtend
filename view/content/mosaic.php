<?php

if (!isset($mosaico) || !is_array($mosaico)) {

    include(__DIR__ . '/../cnf/session.php');

}

if (!isset($mosaico) || !is_array($mosaico)) {

    $mosaico = array_fill(0, 3, '0');

}



$mosaicModules = [];

if ($mosaico[0] == '1') {

    $mosaicModules[] = [

        'href'  => 'index.php?sec=idx',

        'icon'  => 'far fa-clipboard',

        'label' => 'Atendimento',

        'class' => 'mosaic-tile-idx',

    ];

}

if ($mosaico[1] == '1') {

    $mosaicModules[] = [

        'href'  => 'index.php?sec=usu',

        'icon'  => 'fas fa-users',

        'label' => 'Usuário',

        'class' => 'mosaic-tile-usu',

    ];

}

if ($mosaico[2] == '1') {

    $mosaicModules[] = [

        'href'  => 'index.php?sec=cnf',

        'icon'  => 'fas fa-cogs',

        'label' => 'Configurações',

        'class' => 'mosaic-tile-cnf',

    ];

}

$mosaicCount = count($mosaicModules);

?>

<div class="btn-group dropstart mosaic-menu-wrap">

    <button type="button"

            class="mosaic-menu-btn"

            data-bs-toggle="dropdown"

            aria-expanded="false"

            title="Menu de módulos"

            <?= $mosaicCount === 0 ? 'disabled' : '' ?>>

        <i class="fas fa-th-large" aria-hidden="true"></i>

    </button>



    <?php if ($mosaicCount > 0) { ?>

    <ul class="dropdown-menu mosaic-panel"

        id="menu-mosaic"

        data-mosaic-count="<?= (int) $mosaicCount ?>">

        <li class="mosaic-panel-head">

            <span class="mosaic-panel-title">Módulos do sistema</span>

            <span class="mosaic-panel-sub">Acesso rápido entre áreas</span>

        </li>

        <li class="mosaic-panel-grid">

            <div class="mosaic-grid" role="group" aria-label="Módulos disponíveis">

                <?php foreach ($mosaicModules as $module) { ?>

                <a href="<?= htmlspecialchars($module['href']) ?>"

                   class="mosaic-tile <?= htmlspecialchars($module['class']) ?>">

                    <span class="mosaic-tile-icon" aria-hidden="true">

                        <i class="<?= htmlspecialchars($module['icon']) ?>"></i>

                    </span>

                    <span class="mosaic-tile-label"><?= htmlspecialchars($module['label']) ?></span>

                </a>

                <?php } ?>

            </div>

        </li>

    </ul>

    <?php } ?>

</div>

