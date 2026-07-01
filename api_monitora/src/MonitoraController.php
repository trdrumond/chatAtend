<?php

declare(strict_types=1);

final class MonitoraController
{
    /** @var MonitoraRepository */
    private $repo;

    /** @var array<string, mixed> */
    private $config;

    /** @param array<string, mixed> $config */
    public function __construct(MonitoraRepository $repo, array $config)
    {
        $this->repo = $repo;
        $this->config = $config;
    }

  /** @param list<string> $segmentos */
    public function despachar(array $segmentos): void
    {
        if ($segmentos === [] || $segmentos[0] !== 'monitora') {
            MonitoraResponse::erro(404, 'NAO_ENCONTRADO', 'Endpoint não encontrado.');
        }

        $recurso = $segmentos[1] ?? '';

        switch ($recurso) {
            case 'status':
                $this->status();
                break;
            case 'contratos':
                $this->contratos();
                break;
            case 'filas':
                $this->filas();
                break;
            case 'atendimentos':
                if (isset($segmentos[2]) && $segmentos[2] !== '') {
                    $this->atendimentoDetalhe(urldecode($segmentos[2]));
                } else {
                    $this->atendimentos();
                }
                break;
            default:
                MonitoraResponse::erro(404, 'NAO_ENCONTRADO', 'Endpoint não encontrado.');
        }
    }

    private function status(): void
    {
        MonitoraResponse::json([
            'sistema' => (string) ($this->config['sistema'] ?? 'Solvetask'),
            'integracao' => (string) ($this->config['integracao'] ?? 'monitora'),
            'versao' => (string) ($this->config['versao'] ?? '1.0.0'),
            'status' => 'online',
        ]);
    }

    private function contratos(): void
    {
        MonitoraResponse::json([
            'contratos' => $this->repo->listarContratos(),
        ]);
    }

    private function filas(): void
    {
        $contrato = trim((string) ($_GET['contrato'] ?? ''));
        if ($contrato === '') {
            MonitoraResponse::erro(400, 'PARAMETROS_INVALIDOS', 'Parâmetro contrato é obrigatório.');
        }

        MonitoraResponse::json($this->repo->listarFilas($contrato));
    }

    private function atendimentos(): void
    {
        $dataInicio = trim((string) ($_GET['data_inicio'] ?? ''));
        $dataFim = trim((string) ($_GET['data_fim'] ?? ''));
        $contrato = trim((string) ($_GET['contrato'] ?? ''));
        $fila = isset($_GET['fila']) ? trim((string) $_GET['fila']) : null;

        if ($dataInicio === '' || $dataFim === '' || $contrato === '') {
            MonitoraResponse::erro(
                400,
                'PARAMETROS_INVALIDOS',
                'Parâmetros data_inicio, data_fim e contrato são obrigatórios.'
            );
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataFim)) {
            MonitoraResponse::erro(400, 'PARAMETROS_INVALIDOS', 'Datas devem estar no formato AAAA-MM-DD.');
        }

        if ($dataInicio > $dataFim) {
            MonitoraResponse::erro(400, 'PARAMETROS_INVALIDOS', 'data_inicio não pode ser posterior a data_fim.');
        }

        $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
        $porPagina = (int) ($_GET['por_pagina'] ?? ($this->config['por_pagina_padrao'] ?? 100));
        $maximo = (int) ($this->config['por_pagina_maximo'] ?? 500);
        $porPagina = max(1, min($porPagina, $maximo));

        MonitoraResponse::json($this->repo->listarAtendimentos(
            $dataInicio,
            $dataFim,
            $contrato,
            $fila,
            $pagina,
            $porPagina
        ));
    }

    private function atendimentoDetalhe(string $protocolo): void
    {
        if ($protocolo === '') {
            MonitoraResponse::erro(400, 'PARAMETROS_INVALIDOS', 'Protocolo inválido.');
        }

        MonitoraResponse::json($this->repo->detalharAtendimento($protocolo));
    }
}
