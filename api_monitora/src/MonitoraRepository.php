<?php

declare(strict_types=1);

final class MonitoraRepository
{
    /** @var PDO */
    private $pdo;

    /** @var array<string, mixed> */
    private $config;

    /** @param array<string, mixed> $config */
    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    /** @return list<array{codigo: string, nome: string, ativo: bool}> */
    public function listarContratos(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id_contrato, nome_contrato, uf, ativo
             FROM tbl_contrato
             WHERE ativo = 1
             ORDER BY nome_contrato ASC'
        );

        $contratos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $contratos[] = [
                'codigo' => $this->codigoContrato((int) $row['id_contrato']),
                'nome' => trim($row['nome_contrato'] . ($row['uf'] ? ' - ' . $row['uf'] : '')),
                'ativo' => (bool) $row['ativo'],
            ];
        }

        return $contratos;
    }

    public function contratoExiste(string $codigoContrato): bool
    {
        $id = $this->parseContratoCodigo($codigoContrato);
        if ($id === null) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT id_contrato FROM tbl_contrato WHERE id_contrato = ? AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([$id]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @return array{contrato: string, filas: list<array{codigo: string, nome: string, ativo: bool}>}
     */
    public function listarFilas(string $codigoContrato): array
    {
        $contratoId = $this->parseContratoCodigo($codigoContrato);
        if ($contratoId === null || !$this->contratoExiste($codigoContrato)) {
            MonitoraResponse::erro(404, 'NAO_ENCONTRADO', 'Contrato ' . $codigoContrato . ' não encontrado ou inativo.');
        }

        $stmt = $this->pdo->prepare(
            'SELECT id_fila, nome_fila, ativo
             FROM tbl_config_fila
             WHERE contrato_id = ? AND ativo = 1
             ORDER BY nome_fila ASC'
        );
        $stmt->execute([$contratoId]);

        $filas = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $filas[] = [
                'codigo' => (string) $row['id_fila'],
                'nome' => (string) $row['nome_fila'],
                'ativo' => (bool) $row['ativo'],
            ];
        }

        return [
            'contrato' => $this->codigoContrato($contratoId),
            'filas' => $filas,
        ];
    }

    /**
     * @return array{
     *   total: int,
     *   pagina: int,
     *   por_pagina: int,
     *   total_paginas: int,
     *   atendimentos: list<array<string, mixed>>
     * }
     */
    public function listarAtendimentos(
        string $dataInicio,
        string $dataFim,
        string $codigoContrato,
        ?string $codigoFila,
        int $pagina,
        int $porPagina
    ): array {
        $contratoId = $this->parseContratoCodigo($codigoContrato);
        if ($contratoId === null || !$this->contratoExiste($codigoContrato)) {
            MonitoraResponse::erro(404, 'NAO_ENCONTRADO', 'Contrato ' . $codigoContrato . ' não encontrado ou inativo.');
        }

        $filaId = null;
        if ($codigoFila !== null && $codigoFila !== '') {
            if (!ctype_digit($codigoFila)) {
                MonitoraResponse::erro(400, 'PARAMETROS_INVALIDOS', 'Código de fila inválido.');
            }
            $filaId = (int) $codigoFila;
            if (!$this->filaPertenceContrato($filaId, $contratoId)) {
                MonitoraResponse::erro(403, 'SEM_PERMISSAO', 'Fila ' . $codigoFila . ' não pertence ao contrato informado.');
            }
        }

        $tabela = $this->tabelaFila();
        $where = 'f.contrato_id = :contrato_id
                  AND f.hora_fim IS NOT NULL
                  AND DATE(f.hora_fim) BETWEEN :data_inicio AND :data_fim';
        $params = [
            ':contrato_id' => $contratoId,
            ':data_inicio' => $dataInicio,
            ':data_fim' => $dataFim,
        ];

        if ($filaId !== null) {
            $where .= ' AND f.fila_id = :fila_id';
            $params[':fila_id'] = $filaId;
        }

        $sqlCount = "SELECT COUNT(*) AS total FROM {$tabela} f WHERE {$where}";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = (int) ($stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT
                    f.protocolo,
                    f.contrato_id,
                    f.fila_id,
                    f.status_fila,
                    f.data_hora,
                    f.hora_inicio,
                    f.hora_fim,
                    f.ta,
                    cf.nome_fila,
                    ass.titulo_assunto,
                    CONCAT(bko.nome, ' ', bko.sobrenome) AS operador_nome,
                    bko.nome_usuario AS operador_login,
                    cl.star AS csat
                FROM {$tabela} f
                INNER JOIN tbl_config_fila cf ON cf.id_fila = f.fila_id
                INNER JOIN tbl_assunto ass ON ass.id_assunto = f.assunto_id
                LEFT JOIN tbl_user bko ON bko.id_user = f.bko_resp
                LEFT JOIN tbl_classificacao cl ON cl.chat_fila_id = f.id_fila_chat
                WHERE {$where}
                ORDER BY f.hora_fim ASC, f.protocolo ASC
                LIMIT {$offset}, {$porPagina}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $atendimentos = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $atendimentos[] = $this->montarResumoAtendimento($row);
        }

        return [
            'total' => $total,
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total_paginas' => $totalPaginas,
            'atendimentos' => $atendimentos,
        ];
    }

    /** @return array<string, mixed> */
    public function detalharAtendimento(string $protocolo): array
    {
        $tabela = $this->tabelaFila();
        $sql = "SELECT
                    f.id_fila_chat,
                    f.protocolo,
                    f.contrato_id,
                    f.fila_id,
                    f.status_fila,
                    f.data_hora,
                    f.hora_inicio,
                    f.hora_fim,
                    f.ta,
                    f.te,
                    f.motivo,
                    f.motivo_cancela,
                    cf.nome_fila,
                    ass.titulo_assunto,
                    CONCAT(bko.nome, ' ', bko.sobrenome) AS operador_nome,
                    bko.nome_usuario AS operador_login,
                    CONCAT(sol.nome, ' ', sol.sobrenome) AS cliente_nome,
                    sol.email AS cliente_email,
                    sol.nome_usuario AS cliente_login,
                    f.ate_resp,
                    f.bko_resp,
                    cl.star AS csat
                FROM {$tabela} f
                INNER JOIN tbl_config_fila cf ON cf.id_fila = f.fila_id
                INNER JOIN tbl_assunto ass ON ass.id_assunto = f.assunto_id
                LEFT JOIN tbl_user bko ON bko.id_user = f.bko_resp
                LEFT JOIN tbl_user sol ON sol.id_user = f.ate_resp
                LEFT JOIN tbl_classificacao cl ON cl.chat_fila_id = f.id_fila_chat
                WHERE f.protocolo = ?
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$protocolo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            MonitoraResponse::erro(404, 'NAO_ENCONTRADO', 'Protocolo ' . $protocolo . ' não encontrado.');
        }

        $detalhe = $this->montarResumoAtendimento($row);
        $detalhe['operador_login'] = (string) ($row['operador_login'] ?? '');
        $detalhe['cliente'] = [
            'nome' => trim((string) ($row['cliente_nome'] ?? '')),
            'documento' => null,
            'email' => $row['cliente_email'] !== '' ? (string) $row['cliente_email'] : null,
            'telefone' => null,
        ];
        $detalhe['metricas'] = [
            'nps' => null,
            'csat' => isset($row['csat']) && $row['csat'] !== null ? (int) $row['csat'] : null,
            'fcr' => null,
            'tempo_resolucao_min' => $this->tempoMinutos($row['ta'] ?? null),
        ];
        $detalhe['tags'] = $this->montarTags($row);
        $detalhe['mensagens'] = $this->buscarMensagens(
            (int) $row['id_fila_chat'],
            (int) ($row['ate_resp'] ?? 0),
            (int) ($row['bko_resp'] ?? 0),
            trim((string) ($row['cliente_nome'] ?? '')),
            trim((string) ($row['operador_nome'] ?? ''))
        );

        unset($detalhe['nps'], $detalhe['csat'], $detalhe['fcr'], $detalhe['tempo_resolucao_min']);

        $detalhe['assunto'] = (string) ($row['titulo_assunto'] ?? '');
        if (!empty($row['motivo'])) {
            $detalhe['observacao'] = (string) $row['motivo'];
        }
        if (!empty($row['motivo_cancela'])) {
            $detalhe['motivo_cancelamento'] = (string) $row['motivo_cancela'];
        }

        return $detalhe;
    }

    private function tabelaFila(): string
    {
        if ($this->tabelaExiste('tbl_chat_fila_secondary')) {
            return 'tbl_chat_fila_secondary';
        }

        return 'tbl_chat_fila';
    }

    private function tabelaExiste(string $nome): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS qtd
             FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?'
        );
        $stmt->execute([$nome]);

        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['qtd'] ?? 0) > 0;
    }

    private function filaPertenceContrato(int $filaId, int $contratoId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_fila FROM tbl_config_fila WHERE id_fila = ? AND contrato_id = ? AND ativo = 1 LIMIT 1'
        );
        $stmt->execute([$filaId, $contratoId]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

  /** @param array<string, mixed> $row */
    private function montarResumoAtendimento(array $row): array
    {
        $inicio = $row['hora_inicio'] ?? $row['data_hora'] ?? null;
        $resumo = [
            'protocolo' => (string) $row['protocolo'],
            'contrato' => $this->codigoContrato((int) $row['contrato_id']),
            'fila' => (string) $row['fila_id'],
            'operador' => trim((string) ($row['operador_nome'] ?? '')),
            'canal' => 'chat',
            'status' => $this->mapearStatus((int) ($row['status_fila'] ?? 0)),
            'data_inicio' => $this->toIso8601($inicio),
            'data_fim' => $this->toIso8601($row['hora_fim'] ?? null),
            'tempo_resolucao_min' => $this->tempoMinutos($row['ta'] ?? null),
            'nps' => null,
            'csat' => isset($row['csat']) && $row['csat'] !== null ? (int) $row['csat'] : null,
            'fcr' => null,
        ];

        return array_filter($resumo, function ($valor) {
            return $valor !== null;
        });
    }

    /** @param array<string, mixed> $row
     * @return list<string>
     */
    private function montarTags(array $row): array
    {
        $tags = [];
        if (!empty($row['titulo_assunto'])) {
            $tags[] = (string) $row['titulo_assunto'];
        }
        if (!empty($row['nome_fila'])) {
            $tags[] = (string) $row['nome_fila'];
        }

        return array_values(array_unique($tags));
    }

    /**
     * @return list<array{autor: string, nome: string, texto: string, timestamp: string, tipo?: string}>
     */
    private function buscarMensagens(
        int $filaChatId,
        int $ateResp,
        int $bkoResp,
        string $nomeCliente,
        string $nomeOperador
    ): array {
        $pares = [
            ['info' => 'tbl_chat_info_secondary', 'msg' => 'tbl_chat_msg_secondary'],
            ['info' => 'tbl_chat_info', 'msg' => 'tbl_chat_msg'],
        ];

        foreach ($pares as $par) {
            if (!$this->tabelaExiste($par['info']) || !$this->tabelaExiste($par['msg'])) {
                continue;
            }

            $sql = "SELECT m.rem_id, m.msg, m.data_hora, u.nome, u.sobrenome, u.nivel_id
                    FROM {$par['info']} i
                    INNER JOIN {$par['msg']} m ON m.chat_id = i.id_chat
                    LEFT JOIN tbl_user u ON u.id_user = m.rem_id AND m.rem_id <> 0
                    WHERE i.fila_chat_id = ?
                    ORDER BY m.data_hora ASC, m.id_msg ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$filaChatId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rows !== []) {
                return $this->formatarMensagens($rows, $ateResp, $bkoResp, $nomeCliente, $nomeOperador);
            }
        }

        return [];
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @return list<array{autor: string, nome: string, texto: string, timestamp: string, tipo: string}>
     */
    private function formatarMensagens(
        array $rows,
        int $ateResp,
        int $bkoResp,
        string $nomeCliente,
        string $nomeOperador
    ): array {
        $mensagens = [];

        foreach ($rows as $row) {
            $remId = (int) ($row['rem_id'] ?? 0);
            $texto = trim((string) ($row['msg'] ?? ''));
            if ($texto === '') {
                continue;
            }

            if ($remId === 0) {
                $autor = 'sistema';
                $nome = 'Sistema';
            } elseif ($remId === $ateResp || (int) ($row['nivel_id'] ?? 0) === 5) {
                $autor = 'cliente';
                $nome = trim(((string) ($row['nome'] ?? '')) . ' ' . ((string) ($row['sobrenome'] ?? '')));
                if ($nome === '') {
                    $nome = $nomeCliente !== '' ? $nomeCliente : 'Cliente';
                }
            } elseif ($remId === $bkoResp || (int) ($row['nivel_id'] ?? 0) === 4) {
                $autor = 'operador';
                $nome = trim(((string) ($row['nome'] ?? '')) . ' ' . ((string) ($row['sobrenome'] ?? '')));
                if ($nome === '') {
                    $nome = $nomeOperador !== '' ? $nomeOperador : 'Operador';
                }
            } else {
                $autor = 'sistema';
                $nome = trim(((string) ($row['nome'] ?? '')) . ' ' . ((string) ($row['sobrenome'] ?? '')));
                if ($nome === '') {
                    $nome = 'Sistema';
                }
            }

            $mensagens[] = [
                'autor' => $autor,
                'nome' => $nome,
                'texto' => $texto,
                'timestamp' => $this->toIso8601($row['data_hora'] ?? null) ?? '',
                'tipo' => 'texto',
            ];
        }

        return $mensagens;
    }

    private function codigoContrato(int $contratoId): string
    {
        $prefixo = (string) ($this->config['contrato_prefixo'] ?? 'ST');

        return $prefixo . '-' . $contratoId;
    }

    private function parseContratoCodigo(string $codigo): ?int
    {
        $codigo = trim($codigo);
        $prefixo = preg_quote((string) ($this->config['contrato_prefixo'] ?? 'ST'), '/');

        if (preg_match('/^' . $prefixo . '-(\d+)$/i', $codigo, $m)) {
            return (int) $m[1];
        }

        if (ctype_digit($codigo)) {
            return (int) $codigo;
        }

        return null;
    }

    private function mapearStatus(int $statusFila): string
    {
        if (in_array($statusFila, [5, 8, 9], true)) {
            return 'cancelado';
        }

        if ($statusFila === 4) {
            return 'encerrado';
        }

        return 'encerrado';
    }

    private function toIso8601(?string $datetime): ?string
    {
        if ($datetime === null || trim($datetime) === '' || strpos($datetime, '0000-00-00') === 0) {
            return null;
        }

        $tz = new DateTimeZone((string) ($this->config['timezone'] ?? 'America/Fortaleza'));
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $datetime, $tz)
            ?: DateTime::createFromFormat('Y-m-d H:i:s.u', $datetime, $tz)
            ?: new DateTime($datetime, $tz);

        return $dt->format('c');
    }

    private function tempoMinutos(?string $time): ?int
    {
        if ($time === null || trim($time) === '') {
            return null;
        }

        $partes = array_map('intval', explode(':', $time));
        $horas = $partes[0] ?? 0;
        $minutos = $partes[1] ?? 0;
        $segundos = $partes[2] ?? 0;

        return (int) round(($horas * 3600 + $minutos * 60 + $segundos) / 60);
    }
}
