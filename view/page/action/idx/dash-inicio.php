<?php
include_once(__DIR__ . '/../../../cnf/session.php');

?>
<style>
    .dash-inicio .card {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s;
    }

    .dash-inicio .card:hover {
        transform: translateY(-2px);
    }

    .dash-inicio .card-icon {
        font-size: 2.4rem;
        margin-bottom: 0.5rem;
    }

    .dash-inicio .card-value {
        font-size: 2.1rem;
        font-weight: bold;
    }

    .dash-inicio .card-title {
        font-size: 1.05rem;
        color: #555;
    }

    .dash-inicio .link-card {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .dash-inicio .link-card:hover {
        color: inherit;
    }

    .col-lg-12 {
        width: 32% !important;
    }
</style>

<div class="dash-inicio container-fluid py-3">
    <div class="text-center mb-3">
        <h5 class="mb-0">Visão geral do sistema</h5>
        <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card h-100 border-primary">
                <div class="card-body text-center py-3">
                    <div class="card-icon text-primary"><i class="fas fa-check-circle"></i></div>
                    <p class="card-value mb-0" id="ini_concluido"></p>
                    <p class="card-title mb-0">Concluídos hoje</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card h-100 border-warning">
                <div class="card-body text-center py-3">
                    <div class="card-icon text-warning"><i class="fas fa-clock"></i></div>
                    <p class="card-value mb-0" id="ini_aguardando"></p>
                    <p class="card-title mb-0">Aguardando</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card h-100 border-info">
                <div class="card-body text-center py-3">
                    <div class="card-icon text-info"><i class="fas fa-headset"></i></div>
                    <p class="card-value mb-0" id="ini_atendimento"></p>
                    <p class="card-title mb-0">Em atendimento</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card h-100 border-danger">
                <div class="card-body text-center py-3">
                    <div class="card-icon text-danger"><i class="fas fa-exclamation-circle"></i></div>
                    <p class="card-value mb-0" id="ini_pendencias"></p>
                    <p class="card-title mb-0">Pendências abertas</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card h-100 border-success">
                <div class="card-body text-center py-3">
                    <div class="card-icon text-success"><i class="fas fa-stopwatch"></i></div>
                    <p class="card-value mb-0" id="ini_tma"></p>
                    <p class="card-title mb-0">TMA (hoje)</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-2">
            <div class="card h-100 border-secondary">
                <div class="card-body text-center py-3">
                    <div class="card-icon text-secondary"><i class="fas fa-hourglass-half"></i></div>
                    <p class="card-value mb-0" id="ini_tme"></p>
                    <p class="card-title mb-0">TME (hoje)</p>
                </div>
            </div>
        </div>
    </div>

</div>