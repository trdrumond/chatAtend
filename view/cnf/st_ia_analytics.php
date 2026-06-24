<?php
/**
 * Análise diária com IA (D+1) — agregação segura e chamada OpenAI.
 */
declare(strict_types=1);

require_once __DIR__ . '/st_fila_status.php';

const ST_IA_STATUS_OK = 'ok';
const ST_IA_STATUS_SEM_CHAVE = 'sem_chave';
const ST_IA_STATUS_ERRO = 'erro';
const ST_IA_STATUS_LIMITE = 'limite';
const ST_IA_STATUS_METRICAS = 'metricas';

function stIaTableExists(PDO $PDO, string $table): bool
{
    static $cache = [];
    if (isset($cache[$table])) {
        return $cache[$table];
    }
    $stmt = $PDO->prepare(
        'SELECT 1 FROM information_schema.tables'
        . ' WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1'
    );
    $stmt->execute([$table]);
    $cache[$table] = (bool) $stmt->fetchColumn();

    return $cache[$table];
}

function stIaColumnExists(PDO $PDO, string $table, string $column): bool
{
    static $cache = [];
    $key = $table . '.' . $column;
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    $stmt = $PDO->prepare(
        'SELECT 1 FROM information_schema.columns'
        . ' WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1'
    );
    $stmt->execute([$table, $column]);
    $cache[$key] = (bool) $stmt->fetchColumn();

    return $cache[$key];
}

function stIaSchemaReady(PDO $PDO): bool
{
    return stIaColumnExists($PDO, 'tbl_config_sis', 'openai_api_key')
        && stIaTableExists($PDO, 'tbl_ia_analise_diaria')
        && stIaTableExists($PDO, 'tbl_ia_log_limite');
}

function stIaGetApiKey(PDO $PDO): ?string
{
    if (!stIaColumnExists($PDO, 'tbl_config_sis', 'openai_api_key')) {
        return null;
    }
    $stmt = $PDO->query('SELECT openai_api_key FROM tbl_config_sis LIMIT 1');
    $key = trim((string) ($stmt->fetchColumn() ?: ''));

    return $key !== '' ? $key : null;
}

function stIaUltimoDiaDisponivel(): string
{
    return date('Y-m-d', strtotime('-1 day'));
}

function stIaLogLimite(PDO $PDO, array $ctx): void
{
    if (!stIaTableExists($PDO, 'tbl_ia_log_limite')) {
        return;
    }
    $stmt = $PDO->prepare(
        'INSERT INTO tbl_ia_log_limite (ref_dia, contrato_id, fila_id, http_code, tipo, mensagem, resposta_api)'
        . ' VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $ctx['ref_dia'] ?? null,
        (int) ($ctx['contrato_id'] ?? 0),
        (int) ($ctx['fila_id'] ?? 0),
        isset($ctx['http_code']) ? (int) $ctx['http_code'] : null,
        (string) ($ctx['tipo'] ?? 'limite'),
        isset($ctx['mensagem']) ? (string) $ctx['mensagem'] : null,
        isset($ctx['resposta_api']) ? (string) $ctx['resposta_api'] : null,
    ]);
}

function stIaIsLimitResponse(int $httpCode, string $body): bool
{
    if ($httpCode === 429) {
        return true;
    }
    if ($httpCode === 402 || $httpCode === 403) {
        return true;
    }
    $lower = strtolower($body);

    return strpos($lower, 'rate_limit') !== false
        || strpos($lower, 'insufficient_quota') !== false
        || strpos($lower, 'quota') !== false
        || strpos($lower, 'billing') !== false
        || strpos($lower, 'limit') !== false && strpos($lower, 'exceeded') !== false;
}

function stIaFmtTime(?string $val, string $def = '--:--:--'): string
{
    if ($val === null || $val === '') {
        return $def;
    }
    $p = explode('.', (string) $val);

    return $p[0] !== '' ? $p[0] : $def;
}

/** Temas operacionais detectados no texto do motivo (regex). */
function stIaMotivoThemePatterns(): array
{
    return [
        '2ª via' => '/\b(2\s*ª?\s*via|segunda\s+via|2via|2\s+via|\bdv\b)/ui',
        'Boleto' => '/\bboletos?\b/ui',
        'Fatura' => '/\bfaturas?\b/ui',
        'Código de barras' => '/\b(c[oó]d(?:igo)?(?:\s*de\s*barras)?|cod\b)/ui',
        'Solicitação / pedido' => '/\b(solicit\w*|pedidos?)\b/ui',
        'Parcelamento' => '/\b(parcel\w*|parcelamento)\b/ui',
        'Vencimento / prazo' => '/\b(vencimentos?|prazos?|data\s+de\s+venc)/ui',
        'Débito / dívida' => '/\b(d[eé]bitos?|d[ií]vidas?|inadimpl)/ui',
        'Religação' => '/\b(religa\w*)/ui',
        'Cancelamento' => '/\b(cancel\w*)/ui',
        'Cadastro / atualização' => '/\b(cadast\w*|atualiza\w*)/ui',
        'Login / senha / acesso' => '/\b(login|senhas?|acessos?)\b/ui',
        'Reclamação' => '/\b(reclama\w*|ouvidoria)\b/ui',
        'Consumo / leitura' => '/\b(consumos?|leituras?|medidores?)\b/ui',
        'Titularidade' => '/\b(titular\w*|troca\s+de\s+titular)/ui',
    ];
}

/** Atalhos digitados isoladamente no campo motivo. */
function stIaMotivoTokenAliases(): array
{
    return [
        'dv' => '2ª via',
        '2via' => '2ª via',
        '2 via' => '2ª via',
        'cod' => 'Código de barras',
        'cs' => 'Atendimento CS',
    ];
}

function stIaMotivoStopwords(): array
{
    static $words = null;
    if ($words !== null) {
        return $words;
    }
    $words = array_flip([
        'a', 'o', 'e', 'de', 'da', 'do', 'das', 'dos', 'em', 'no', 'na', 'nos', 'nas', 'um', 'uma', 'uns', 'umas',
        'para', 'por', 'com', 'sem', 'ao', 'aos', 'à', 'às', 'eu', 'ele', 'ela', 'me', 'se', 'que', 'ou', 'mas',
        'como', 'qual', 'quais', 'quando', 'onde', 'sobre', 'preciso', 'precisar', 'precisaria', 'gostaria',
        'queria', 'quero', 'favor', 'obrigado', 'obrigada', 'ola', 'oi', 'bom', 'dia', 'tarde', 'noite', 'boa',
        'estou', 'está', 'esta', 'esse', 'essa', 'isso', 'aqui', 'ali', 'meu', 'minha', 'seu', 'sua', 'nos', 'vos',
        'the', 'and', 'ver', 'verificar', 'consultar', 'informacao', 'informação', 'duvida', 'dúvida', 'ajuda',
        'atendimento', 'chat', 'mensagem', 'msg', 'teste', 'ok', 'sim', 'nao', 'não', 'sr', 'sra', 'senhor', 'senhora',
    ]);

    return $words;
}

function stIaMotivoStripAccents(string $text): string
{
    $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

    return $t !== false ? strtolower($t) : mb_strtolower($text);
}

function stIaMotivoIsNoiseToken(string $token): bool
{
    if ($token === '') {
        return true;
    }
    if (mb_strlen($token) < 3) {
        return true;
    }
    if (preg_match('/^\d+$/', $token)) {
        return true;
    }
    if (preg_match('/^[a-z]$/u', $token)) {
        return true;
    }
    if (isset(stIaMotivoStopwords()[$token])) {
        return true;
    }

    return false;
}

function stIaMbStrtolower(string $text): string
{
    return function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);
}

function stIaMbConvertCaseTitle(string $text): string
{
    if (function_exists('mb_convert_case')) {
        return mb_convert_case($text, MB_CASE_TITLE, 'UTF-8');
    }

    return ucwords(strtolower($text));
}

/**
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array{termo: string, motivo_resumo: string, qtd: int}>
 */
function stIaNormalizeMotivoRows(array $rows): array
{
    $out = [];
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $termo = trim((string) ($row['termo'] ?? $row['motivo_resumo'] ?? $row['titulo'] ?? ''));
        if ($termo === '') {
            continue;
        }
        $qtd = (int) ($row['qtd'] ?? 0);
        $out[] = [
            'termo' => $termo,
            'motivo_resumo' => $termo,
            'qtd' => $qtd,
        ];
    }

    return $out;
}

/**
 * Agrega motivos em temas e termos úteis (evita fragmentos como "1", "m", "dv").
 *
 * @param array<int, string> $motivos
 * @return array<int, array{termo: string, qtd: int}>
 */
function stIaAggregateMotivos(array $motivos): array
{
    $counts = [];
    $themes = stIaMotivoThemePatterns();
    $aliases = stIaMotivoTokenAliases();

    foreach ($motivos as $raw) {
        $text = trim((string) $raw);
        if ($text === '') {
            continue;
        }

        $norm = preg_replace('/\s+/u', ' ', stIaMbStrtolower($text)) ?? '';
        $matchedTheme = false;

        if (isset($aliases[$norm])) {
            $label = $aliases[$norm];
            $counts[$label] = ($counts[$label] ?? 0) + 1;
            $matchedTheme = true;
        }

        foreach ($themes as $label => $pattern) {
            if (preg_match($pattern, $text)) {
                $counts[$label] = ($counts[$label] ?? 0) + 1;
                $matchedTheme = true;
            }
        }

        if ($matchedTheme) {
            continue;
        }

        // Texto curto mas legível (ex.: "boleto vencido")
        if (mb_strlen($norm) >= 8 && mb_strlen($norm) <= 80 && !preg_match('/^\d+$/', $norm)) {
            $display = mb_convert_case($norm, MB_CASE_TITLE, 'UTF-8');
            if ($display === '' || $display === false) {
                $display = stIaMbConvertCaseTitle($norm);
            }
            $counts[$display] = ($counts[$display] ?? 0) + 1;
            continue;
        }

        $plain = stIaMotivoStripAccents($norm);
        $plain = preg_replace('/[^a-z0-9\s]/', ' ', $plain) ?? '';
        $tokens = preg_split('/\s+/', trim($plain), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($tokens as $token) {
            if (isset($aliases[$token])) {
                $label = $aliases[$token];
                $counts[$label] = ($counts[$label] ?? 0) + 1;
                continue;
            }
            if (stIaMotivoIsNoiseToken($token)) {
                continue;
            }
            $display = stIaMbConvertCaseTitle($token);
            $counts[$display] = ($counts[$display] ?? 0) + 1;
        }
    }

    arsort($counts);
    $out = [];
    foreach ($counts as $termo => $qtd) {
        $out[] = ['termo' => (string) $termo, 'qtd' => (int) $qtd];
        if (count($out) >= 10) {
            break;
        }
    }

    return stIaNormalizeMotivoRows($out);
}

/** Busca motivos do período e agrega em temas (sempre recalculado, não usa cache diário). */
function stIaFetchMotivosForRange(PDO $PDO, string $deDt, string $ateDt, int $contratoId, int $filaId): array
{
    $deInicio = $deDt . ' 00:00:00';
    $ateFim = $ateDt . ' 23:59:59';

    $whereExtra = '';
    $paramsBase = [$deInicio, $ateFim];
    if ($contratoId > 0) {
        $whereExtra .= ' AND x.contrato_id = ?';
        $paramsBase[] = $contratoId;
    }
    if ($filaId > 0) {
        $whereExtra .= ' AND x.fila_id = ?';
        $paramsBase[] = $filaId;
    }

    $sqlUnified = ''
        . ' SELECT x.motivo'
        . ' FROM ('
        . '   SELECT cf.motivo, cf.contrato_id, cf.fila_id, cf.data_hora'
        . '   FROM tbl_chat_fila cf'
        . '   WHERE cf.data_hora >= ? AND cf.data_hora <= ?'
        . '   UNION ALL'
        . '   SELECT cs.motivo, cs.contrato_id, cs.fila_id, cs.data_hora'
        . '   FROM tbl_chat_fila_secondary cs'
        . '   WHERE cs.data_hora >= ? AND cs.data_hora <= ?'
        . '     AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)'
        . ' ) x WHERE 1=1' . $whereExtra
        . " AND x.motivo IS NOT NULL AND TRIM(x.motivo) <> ''";

    $params = array_merge([$deInicio, $ateFim, $deInicio, $ateFim], array_slice($paramsBase, 2));
    $stmt = $PDO->prepare($sqlUnified);
    $stmt->execute($params);

    return stIaAggregateMotivos($stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
}

/** Coleta métricas agregadas de um único dia (sem dados pessoais). */
function stIaCollectDayMetrics(PDO $PDO, string $refDia, int $contratoId = 0, int $filaId = 0): array
{
    $deInicio = $refDia . ' 00:00:00';
    $ateFim = $refDia . ' 23:59:59';

    $whereExtra = '';
    $paramsBase = [$deInicio, $ateFim];
    if ($contratoId > 0) {
        $whereExtra .= ' AND x.contrato_id = ?';
        $paramsBase[] = $contratoId;
    }
    if ($filaId > 0) {
        $whereExtra .= ' AND x.fila_id = ?';
        $paramsBase[] = $filaId;
    }

    $sqlUnified = ''
        . ' SELECT x.id_fila_chat, x.data_hora, x.hora_inicio, x.hora_fim, x.ta, x.te, x.status_fila,'
        . ' x.assunto_id, x.bko_resp, x.ate_resp, x.fila_id, x.contrato_id, x.motivo'
        . ' FROM ('
        . '   SELECT cf.id_fila_chat, cf.data_hora, cf.hora_inicio, cf.hora_fim, cf.ta, cf.te, cf.status_fila,'
        . '     cf.assunto_id, cf.bko_resp, cf.ate_resp, cf.fila_id, cf.contrato_id, cf.motivo'
        . '   FROM tbl_chat_fila cf'
        . '   WHERE cf.data_hora >= ? AND cf.data_hora <= ?'
        . '   UNION ALL'
        . '   SELECT cs.id_fila_chat, cs.data_hora, cs.hora_inicio, cs.hora_fim, cs.ta, cs.te, cs.status_fila,'
        . '     cs.assunto_id, cs.bko_resp, cs.ate_resp, cs.fila_id, cs.contrato_id, cs.motivo'
        . '   FROM tbl_chat_fila_secondary cs'
        . '   WHERE cs.data_hora >= ? AND cs.data_hora <= ?'
        . '     AND NOT EXISTS (SELECT 1 FROM tbl_chat_fila cf2 WHERE cf2.id_fila_chat = cs.id_fila_chat)'
        . ' ) x WHERE 1=1' . $whereExtra;

    $paramsUnified = array_merge([$deInicio, $ateFim, $deInicio, $ateFim], array_slice($paramsBase, 2));

    $statusAtendido = 'status_fila IN (2,3,4,6,7,10)';
    $statusAbandono = 'status_fila IN (5,8,9)';
    $taPositivo = "ta <> '' AND ta IS NOT NULL AND TIME_TO_SEC(ta) > 0";
    $tePositivo = "te <> '' AND te IS NOT NULL AND TIME_TO_SEC(te) > 0";

    $sqlKpi = 'SELECT'
        . ' COUNT(*) AS entradas,'
        . ' SUM(CASE WHEN ' . $statusAtendido . ' THEN 1 ELSE 0 END) AS atendidos,'
        . ' SUM(CASE WHEN ' . $statusAbandono . ' THEN 1 ELSE 0 END) AS abandonos,'
        . ' SEC_TO_TIME(AVG(CASE WHEN ' . $statusAtendido . ' AND ' . $taPositivo . ' THEN TIME_TO_SEC(ta) END)) AS tma,'
        . ' SEC_TO_TIME(AVG(CASE WHEN ' . $statusAtendido . ' AND ' . $tePositivo . ' THEN TIME_TO_SEC(te) END)) AS tme'
        . ' FROM (' . $sqlUnified . ') u';
    $stmt = $PDO->prepare($sqlKpi);
    $stmt->execute($paramsUnified);
    $kpi = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $entradas = (int) ($kpi['entradas'] ?? 0);
    $atendidos = (int) ($kpi['atendidos'] ?? 0);

    $sqlHora = 'SELECT HOUR(data_hora) AS hora, COUNT(*) AS qtd FROM (' . $sqlUnified . ') u GROUP BY HOUR(data_hora) ORDER BY hora ASC';
    $stmt = $PDO->prepare($sqlHora);
    $stmt->execute($paramsUnified);
    $porHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sqlAss = 'SELECT u.assunto_id,'
        . ' (SELECT titulo_assunto FROM tbl_assunto WHERE id_assunto = u.assunto_id LIMIT 1) AS titulo,'
        . ' COUNT(*) AS qtd'
        . ' FROM (' . $sqlUnified . ') u'
        . ' WHERE u.bko_resp > 0 GROUP BY u.assunto_id ORDER BY qtd DESC LIMIT 10';
    $stmt = $PDO->prepare($sqlAss);
    $stmt->execute($paramsUnified);
    $topAssuntos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($topAssuntos as &$a) {
        $a['titulo'] = $a['titulo'] ?: 'Assunto #' . $a['assunto_id'];
    }
    unset($a);

    $sqlMotivosRaw = 'SELECT motivo FROM (' . $sqlUnified . ') u'
        . " WHERE motivo IS NOT NULL AND TRIM(motivo) <> ''";
    $stmt = $PDO->prepare($sqlMotivosRaw);
    $stmt->execute($paramsUnified);
    $topMotivos = stIaAggregateMotivos($stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);

    $sqlStar = 'SELECT ROUND(AVG(c.star), 1) AS media, COUNT(*) AS total'
        . ' FROM tbl_classificacao c'
        . ' WHERE c.data_hora >= ? AND c.data_hora <= ? AND c.star IS NOT NULL AND c.star <> \'\''
        . ' AND c.chat_fila_id IN (SELECT id_fila_chat FROM (' . $sqlUnified . ') su)';
    $starParams = array_merge([$deInicio, $ateFim], $paramsUnified);
    $stmt = $PDO->prepare($sqlStar);
    $stmt->execute($starParams);
    $starRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    $horaPico = null;
    $qtdPico = 0;
    foreach ($porHora as $h) {
        $q = (int) ($h['qtd'] ?? 0);
        if ($q > $qtdPico) {
            $qtdPico = $q;
            $horaPico = (int) ($h['hora'] ?? 0);
        }
    }

    return [
        'ref_dia' => $refDia,
        'contrato_id' => $contratoId,
        'fila_id' => $filaId,
        'kpis' => [
            'entradas' => $entradas,
            'atendidos' => $atendidos,
            'abandonos' => (int) ($kpi['abandonos'] ?? 0),
            'taxa_atendimento' => $entradas > 0 ? round(($atendidos * 100) / $entradas, 1) : 0,
            'tma' => stIaFmtTime($kpi['tma'] ?? null),
            'tme' => stIaFmtTime($kpi['tme'] ?? null),
            'satisfacao_media' => $starRow['media'] ?? null,
            'satisfacao_total' => (int) ($starRow['total'] ?? 0),
            'hora_pico' => $horaPico,
            'hora_pico_qtd' => $qtdPico,
        ],
        'por_hora' => $porHora,
        'top_assuntos' => $topAssuntos,
        'top_motivos' => $topMotivos,
    ];
}

function stIaCallOpenAI(PDO $PDO, array $metrics, string $refDia, int $contratoId, int $filaId): array
{
    return stIaRequestOpenAI($PDO, [
        [
            'role' => 'system',
            'content' => 'Você é um analista de operações de atendimento. Responda em português do Brasil, de forma objetiva e executiva. '
                . 'Use apenas os dados JSON fornecidos. Não invente números. Destaque: principais dúvidas/motivos, horário de pico, assuntos mais demandados e recomendações práticas.',
        ],
        [
            'role' => 'user',
            'content' => 'Analise os indicadores operacionais do dia ' . $refDia . ' (dados consolidados D+1): '
                . json_encode($metrics, JSON_UNESCAPED_UNICODE),
        ],
    ], [
        'ref_dia' => $refDia,
        'contrato_id' => $contratoId,
        'fila_id' => $filaId,
    ], 1200);
}

/**
 * @param array<int, array{role: string, content: string}> $messages
 * @param array{ref_dia?: ?string, contrato_id?: int, fila_id?: int} $logCtx
 */
function stIaRequestOpenAI(PDO $PDO, array $messages, array $logCtx, int $maxTokens = 1500): array
{
    $apiKey = stIaGetApiKey($PDO);
    if ($apiKey === null) {
        return ['ok' => false, 'status' => ST_IA_STATUS_SEM_CHAVE, 'text' => null, 'error' => 'Chave OpenAI não configurada'];
    }

    $payload = [
        'model' => 'gpt-4o-mini',
        'temperature' => 0.3,
        'max_tokens' => $maxTokens,
        'messages' => $messages,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    ]);

    $body = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $contratoId = (int) ($logCtx['contrato_id'] ?? 0);
    $filaId = (int) ($logCtx['fila_id'] ?? 0);
    $refDia = $logCtx['ref_dia'] ?? null;

    if ($body === false || $curlErr !== '') {
        return ['ok' => false, 'status' => ST_IA_STATUS_ERRO, 'text' => null, 'error' => $curlErr ?: 'Falha na comunicação com OpenAI'];
    }

    if (stIaIsLimitResponse($httpCode, (string) $body)) {
        stIaLogLimite($PDO, [
            'ref_dia' => $refDia,
            'contrato_id' => $contratoId,
            'fila_id' => $filaId,
            'http_code' => $httpCode,
            'tipo' => 'limite',
            'mensagem' => 'Limite ou cota da API OpenAI atingido',
            'resposta_api' => mb_substr((string) $body, 0, 4000),
        ]);

        return ['ok' => false, 'status' => ST_IA_STATUS_LIMITE, 'text' => null, 'error' => 'Limite da API OpenAI atingido'];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $decodedErr = json_decode((string) $body, true);
        $errMsg = is_array($decodedErr) ? ($decodedErr['error']['message'] ?? 'Erro HTTP ' . $httpCode) : 'Erro HTTP ' . $httpCode;

        if (stIaIsLimitResponse($httpCode, (string) $body)) {
            stIaLogLimite($PDO, [
                'ref_dia' => $refDia,
                'contrato_id' => $contratoId,
                'fila_id' => $filaId,
                'http_code' => $httpCode,
                'tipo' => 'limite',
                'mensagem' => (string) $errMsg,
                'resposta_api' => mb_substr((string) $body, 0, 4000),
            ]);

            return ['ok' => false, 'status' => ST_IA_STATUS_LIMITE, 'text' => null, 'error' => (string) $errMsg];
        }

        return ['ok' => false, 'status' => ST_IA_STATUS_ERRO, 'text' => null, 'error' => (string) $errMsg];
    }

    $decoded = json_decode((string) $body, true);
    $text = trim((string) ($decoded['choices'][0]['message']['content'] ?? ''));
    if ($text === '') {
        return ['ok' => false, 'status' => ST_IA_STATUS_ERRO, 'text' => null, 'error' => 'Resposta vazia da OpenAI'];
    }

    $lower = strtolower($text);
    if (strpos($lower, 'rate limit') !== false || strpos($lower, 'quota') !== false) {
        stIaLogLimite($PDO, [
            'ref_dia' => $refDia,
            'contrato_id' => $contratoId,
            'fila_id' => $filaId,
            'http_code' => $httpCode,
            'tipo' => 'limite',
            'mensagem' => 'Resposta da IA indicou limite de uso',
            'resposta_api' => mb_substr($text, 0, 4000),
        ]);

        return ['ok' => false, 'status' => ST_IA_STATUS_LIMITE, 'text' => null, 'error' => 'Limite da API OpenAI atingido'];
    }

    return ['ok' => true, 'status' => ST_IA_STATUS_OK, 'text' => $text, 'error' => null];
}

/** Monta payload consolidado do período para a IA. */
function stIaBuildPeriodPayload(string $de, string $ate, int $contratoId, int $filaId, array $periodo): array
{
    $porHora = [];
    foreach ($periodo['por_hora'] ?? [] as $h) {
        $hora = (int) ($h['hora'] ?? -1);
        if ($hora >= 0 && $hora < 24) {
            $porHora[] = ['hora' => $hora, 'qtd' => (int) ($h['qtd'] ?? 0)];
        }
    }

    return [
        'periodo' => ['de' => $de, 'ate' => $ate],
        'escopo' => ['contrato_id' => $contratoId, 'fila_id' => $filaId],
        'dias_com_dados' => (int) ($periodo['dias_com_dados'] ?? 0),
        'kpis' => $periodo['kpis'] ?? [],
        'por_hora' => $porHora,
        'top_assuntos' => array_slice($periodo['top_assuntos'] ?? [], 0, 10),
        'top_motivos' => array_slice($periodo['top_motivos'] ?? [], 0, 10),
    ];
}

function stIaLoadPeriodAnalysis(PDO $PDO, string $de, string $ate, int $contratoId, int $filaId): ?array
{
    if (!stIaTableExists($PDO, 'tbl_ia_analise_periodo')) {
        return null;
    }
    $stmt = $PDO->prepare(
        'SELECT dados_hash, analise_ia, status_ia, criado_em FROM tbl_ia_analise_periodo'
        . ' WHERE de_dia = ? AND ate_dia = ? AND contrato_id = ? AND fila_id = ? LIMIT 1'
    );
    $stmt->execute([$de, $ate, $contratoId, $filaId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row ?: null;
}

function stIaSavePeriodAnalysis(
    PDO $PDO,
    string $de,
    string $ate,
    int $contratoId,
    int $filaId,
    string $dadosHash,
    ?string $analise,
    string $statusIa
): void {
    if (!stIaTableExists($PDO, 'tbl_ia_analise_periodo')) {
        return;
    }
    $stmt = $PDO->prepare(
        'INSERT INTO tbl_ia_analise_periodo (de_dia, ate_dia, contrato_id, fila_id, dados_hash, analise_ia, status_ia)'
        . ' VALUES (?, ?, ?, ?, ?, ?, ?)'
        . ' ON DUPLICATE KEY UPDATE dados_hash = VALUES(dados_hash), analise_ia = VALUES(analise_ia),'
        . ' status_ia = VALUES(status_ia), criado_em = CURRENT_TIMESTAMP'
    );
    $stmt->execute([$de, $ate, $contratoId, $filaId, $dadosHash, $analise, $statusIa]);
}

/** Gera ou recupera do cache a análise textual consolidada do período. */
function stIaGetOrCreatePeriodAnalysis(
    PDO $PDO,
    string $de,
    string $ate,
    int $contratoId,
    int $filaId,
    array $periodo
): array {
    $payload = stIaBuildPeriodPayload($de, $ate, $contratoId, $filaId, $periodo);
    $entradas = (int) (($payload['kpis']['entradas'] ?? 0));
    if ($entradas <= 0) {
        return [
            'ok' => false,
            'status' => ST_IA_STATUS_ERRO,
            'text' => null,
            'error' => 'Sem atendimentos no período',
            'from_cache' => false,
        ];
    }

    $dadosHash = hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE));
    $cached = stIaLoadPeriodAnalysis($PDO, $de, $ate, $contratoId, $filaId);
    if (
        $cached
        && ($cached['dados_hash'] ?? '') === $dadosHash
        && ($cached['status_ia'] ?? '') === ST_IA_STATUS_OK
        && !empty($cached['analise_ia'])
    ) {
        return [
            'ok' => true,
            'status' => ST_IA_STATUS_OK,
            'text' => (string) $cached['analise_ia'],
            'error' => null,
            'from_cache' => true,
        ];
    }

    if (stIaGetApiKey($PDO) === null) {
        return [
            'ok' => false,
            'status' => ST_IA_STATUS_SEM_CHAVE,
            'text' => null,
            'error' => 'Chave OpenAI não configurada',
            'from_cache' => false,
        ];
    }

    $dias = (int) ($payload['dias_com_dados'] ?? 0);
    $labelPeriodo = $de === $ate
        ? ('dia ' . $de)
        : ('período de ' . $de . ' a ' . $ate . ($dias > 0 ? ' (' . $dias . ' dias)' : ''));

    $ia = stIaRequestOpenAI($PDO, [
        [
            'role' => 'system',
            'content' => 'Você é um analista de operações de atendimento. Responda em português do Brasil, de forma objetiva e executiva. '
                . 'Produza um único resumo consolidado do período (não separe por dia). '
                . 'Use apenas os dados JSON. Não invente números. '
                . 'Inclua: visão geral, principais dúvidas/temas, horários de pico, assuntos mais demandados, pontos de atenção e recomendações práticas.',
        ],
        [
            'role' => 'user',
            'content' => 'Analise os indicadores consolidados do ' . $labelPeriodo
                . '. Valores numéricos são soma do período (TMA/TME são médias ponderadas). Dados D+1: '
                . json_encode($payload, JSON_UNESCAPED_UNICODE),
        ],
    ], [
        'ref_dia' => $de,
        'contrato_id' => $contratoId,
        'fila_id' => $filaId,
    ], 1800);

    stIaSavePeriodAnalysis($PDO, $de, $ate, $contratoId, $filaId, $dadosHash, $ia['text'] ?? null, $ia['status']);

    $ia['from_cache'] = false;

    return $ia;
}

function stIaSaveDaily(PDO $PDO, string $refDia, int $contratoId, int $filaId, array $metrics, ?string $analise, string $statusIa): void
{
    $stmt = $PDO->prepare(
        'INSERT INTO tbl_ia_analise_diaria (ref_dia, contrato_id, fila_id, dados_json, analise_ia, status_ia)'
        . ' VALUES (?, ?, ?, ?, ?, ?)'
        . ' ON DUPLICATE KEY UPDATE dados_json = VALUES(dados_json), analise_ia = VALUES(analise_ia),'
        . ' status_ia = VALUES(status_ia), criado_em = CURRENT_TIMESTAMP'
    );
    $stmt->execute([
        $refDia,
        $contratoId,
        $filaId,
        json_encode($metrics, JSON_UNESCAPED_UNICODE),
        $analise,
        $statusIa,
    ]);
}

function stIaGenerateDaily(PDO $PDO, string $refDia, int $contratoId = 0, int $filaId = 0): array
{
    if (!stIaSchemaReady($PDO)) {
        return ['ok' => false, 'status' => ST_IA_STATUS_SEM_CHAVE, 'error' => 'Estrutura de IA não instalada'];
    }

    $metrics = stIaCollectDayMetrics($PDO, $refDia, $contratoId, $filaId);
    stIaSaveDaily($PDO, $refDia, $contratoId, $filaId, $metrics, null, ST_IA_STATUS_METRICAS);

    return ['ok' => true, 'status' => ST_IA_STATUS_METRICAS, 'metrics' => $metrics];
}

/** Lista dias Y-m-d entre $de e $ate (inclusive). */
function stIaIterateDays(string $de, string $ate): array
{
    $days = [];
    $cur = strtotime($de);
    $end = strtotime($ate);
    while ($cur !== false && $end !== false && $cur <= $end) {
        $days[] = date('Y-m-d', $cur);
        $cur = strtotime('+1 day', $cur);
    }

    return $days;
}

function stIaDayExists(PDO $PDO, string $refDia, int $contratoId, int $filaId): bool
{
    if (!stIaTableExists($PDO, 'tbl_ia_analise_diaria')) {
        return false;
    }
    $stmt = $PDO->prepare(
        'SELECT 1 FROM tbl_ia_analise_diaria WHERE ref_dia = ? AND contrato_id = ? AND fila_id = ? LIMIT 1'
    );
    $stmt->execute([$refDia, $contratoId, $filaId]);

    return (bool) $stmt->fetchColumn();
}

/**
 * Gera e persiste análises diárias ausentes no período (on-demand ao abrir o relatório).
 *
 * @return array{generated: array, skipped: array, hit_limit: bool}
 */
function stIaEnsurePeriodGenerated(PDO $PDO, string $de, string $ate, int $contratoId, int $filaId = 0): array
{
    $generated = [];
    $skipped = [];
    $hitLimit = false;

    if (!stIaSchemaReady($PDO)) {
        return ['generated' => $generated, 'skipped' => $skipped, 'hit_limit' => false];
    }

    $ultimo = stIaUltimoDiaDisponivel();
    if ($ate > $ultimo) {
        $ate = $ultimo;
    }
    if ($de > $ultimo) {
        return ['generated' => $generated, 'skipped' => $skipped, 'hit_limit' => false];
    }

    foreach (stIaIterateDays($de, $ate) as $dia) {
        if (stIaDayExists($PDO, $dia, $contratoId, $filaId)) {
            $skipped[] = $dia;
            continue;
        }

        $res = stIaGenerateDaily($PDO, $dia, $contratoId, $filaId);
        $generated[] = [
            'ref_dia' => $dia,
            'status' => $res['status'] ?? ST_IA_STATUS_ERRO,
            'ok' => !empty($res['ok']),
        ];

        if (($res['status'] ?? '') === ST_IA_STATUS_LIMITE) {
            $hitLimit = true;
            break;
        }
    }

    return ['generated' => $generated, 'skipped' => $skipped, 'hit_limit' => $hitLimit];
}

function stIaLoadDailyRange(PDO $PDO, string $de, string $ate, int $contratoId, int $filaId): array
{
    if (!stIaTableExists($PDO, 'tbl_ia_analise_diaria')) {
        return [];
    }
    $stmt = $PDO->prepare(
        'SELECT ref_dia, contrato_id, fila_id, dados_json, analise_ia, status_ia, criado_em'
        . ' FROM tbl_ia_analise_diaria'
        . ' WHERE ref_dia >= ? AND ref_dia <= ? AND contrato_id = ? AND fila_id = ?'
        . ' ORDER BY ref_dia ASC'
    );
    $stmt->execute([$de, $ate, $contratoId, $filaId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function stIaTimeToSec(?string $time): int
{
    if ($time === null || $time === '') {
        return 0;
    }
    $parts = explode(':', (string) $time);

    return ((int) ($parts[0] ?? 0)) * 3600 + ((int) ($parts[1] ?? 0)) * 60 + (int) ($parts[2] ?? 0);
}

function stIaSecToTime(int $sec): string
{
    if ($sec <= 0) {
        return '--:--:--';
    }

    return function_exists('sec_to_time') ? sec_to_time($sec) : gmdate('H:i:s', $sec);
}

/** Soma métricas de vários dias armazenados (período). */
function stIaSumPeriodFromDaily(array $rows): array
{
    if (!$rows) {
        return [
            'dias_com_dados' => 0,
            'kpis' => [],
            'por_hora' => [],
            'top_assuntos' => [],
            'top_motivos' => [],
        ];
    }

    $entradas = 0;
    $atendidos = 0;
    $abandonos = 0;
    $tmaWeighted = 0;
    $tmeWeighted = 0;
    $starWeighted = 0;
    $starCount = 0;
    $porHora = array_fill(0, 24, 0);
    $assuntos = [];
    $motivos = [];
    $horaPicoQtd = 0;
    $horaPico = null;

    foreach ($rows as $row) {
        $data = json_decode((string) ($row['dados_json'] ?? ''), true);
        if (!is_array($data)) {
            continue;
        }
        $k = $data['kpis'] ?? [];
        $e = (int) ($k['entradas'] ?? 0);
        $a = (int) ($k['atendidos'] ?? 0);
        $entradas += $e;
        $atendidos += $a;
        $abandonos += (int) ($k['abandonos'] ?? 0);

        $tmaSec = stIaTimeToSec($k['tma'] ?? null);
        $tmeSec = stIaTimeToSec($k['tme'] ?? null);
        if ($a > 0 && $tmaSec > 0) {
            $tmaWeighted += $tmaSec * $a;
        }
        if ($a > 0 && $tmeSec > 0) {
            $tmeWeighted += $tmeSec * $a;
        }
        if (!empty($k['satisfacao_media']) && (int) ($k['satisfacao_total'] ?? 0) > 0) {
            $starWeighted += (float) $k['satisfacao_media'] * (int) $k['satisfacao_total'];
            $starCount += (int) $k['satisfacao_total'];
        }

        foreach ($data['por_hora'] ?? [] as $h) {
            $hora = (int) ($h['hora'] ?? -1);
            $qtd = (int) ($h['qtd'] ?? 0);
            if ($hora >= 0 && $hora < 24) {
                $porHora[$hora] += $qtd;
            }
        }

        foreach ($data['top_assuntos'] ?? [] as $ass) {
            $id = (int) ($ass['assunto_id'] ?? 0);
            if (!isset($assuntos[$id])) {
                $assuntos[$id] = ['assunto_id' => $id, 'titulo' => $ass['titulo'] ?? '', 'qtd' => 0];
            }
            $assuntos[$id]['qtd'] += (int) ($ass['qtd'] ?? 0);
        }

        foreach ($data['top_motivos'] ?? [] as $mot) {
            $key = (string) ($mot['termo'] ?? $mot['motivo_resumo'] ?? '');
            if ($key === '') {
                continue;
            }
            if (!isset($motivos[$key])) {
                $motivos[$key] = ['termo' => $key, 'qtd' => 0];
            }
            $motivos[$key]['qtd'] += (int) ($mot['qtd'] ?? 0);
        }
    }

    foreach ($porHora as $h => $q) {
        if ($q > $horaPicoQtd) {
            $horaPicoQtd = $q;
            $horaPico = $h;
        }
    }

    usort($assuntos, static function (array $a, array $b): int {
        return $b['qtd'] <=> $a['qtd'];
    });
    usort($motivos, static function (array $a, array $b): int {
        return $b['qtd'] <=> $a['qtd'];
    });

    $porHoraOut = [];
    for ($h = 0; $h < 24; $h++) {
        $porHoraOut[] = ['hora' => $h, 'qtd' => $porHora[$h]];
    }

    return [
        'dias_com_dados' => count($rows),
        'kpis' => [
            'entradas' => $entradas,
            'atendidos' => $atendidos,
            'abandonos' => $abandonos,
            'taxa_atendimento' => $entradas > 0 ? round(($atendidos * 100) / $entradas, 1) : 0,
            'tma' => $atendidos > 0 ? stIaSecToTime((int) round($tmaWeighted / max(1, $atendidos))) : '--:--:--',
            'tme' => $atendidos > 0 ? stIaSecToTime((int) round($tmeWeighted / max(1, $atendidos))) : '--:--:--',
            'satisfacao_media' => $starCount > 0 ? round($starWeighted / $starCount, 1) : null,
            'satisfacao_total' => $starCount,
            'hora_pico' => $horaPico,
            'hora_pico_qtd' => $horaPicoQtd,
        ],
        'por_hora' => $porHoraOut,
        'top_assuntos' => array_slice(array_values($assuntos), 0, 10),
        'top_motivos' => stIaNormalizeMotivoRows(array_slice(array_values($motivos), 0, 10)),
    ];
}

function stIaCollectPeriodLive(PDO $PDO, string $de, string $ate, int $contratoId, int $filaId): array
{
    $rows = [];
    $cur = strtotime($de);
    $end = strtotime($ate);
    while ($cur !== false && $end !== false && $cur <= $end) {
        $rows[] = ['dados_json' => json_encode(stIaCollectDayMetrics($PDO, date('Y-m-d', $cur), $contratoId, $filaId), JSON_UNESCAPED_UNICODE)];
        $cur = strtotime('+1 day', $cur);
    }

    $periodo = stIaSumPeriodFromDaily($rows);
    $periodo['top_motivos'] = stIaFetchMotivosForRange($PDO, $de, $ate, $contratoId, $filaId);

    return $periodo;
}

function stIaActiveContratoIds(PDO $PDO): array
{
    $stmt = $PDO->query('SELECT id_contrato FROM tbl_contrato WHERE ativo = 1 ORDER BY id_contrato');

    return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
}
