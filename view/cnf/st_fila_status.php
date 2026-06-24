<?php
/**
 * Constantes de status_fila (tbl_chat_fila / tbl_situacao_chat).
 *
 * Fluxo: 1 Na fila → 11 Aguardando atendimento (BKO abriu sala) → 2 Em atendimento (ambos na sala).
 */

const ST_FILA_NA_FILA = 1;
const ST_FILA_EM_ATENDIMENTO = 2;
const ST_FILA_AGUARDANDO_ATENDIMENTO = 11;
const ST_FILA_CONCLUIDO = 4;

function stFilaSqlAtendimentoAtivo(): string
{
    return 'status_fila IN (' . ST_FILA_AGUARDANDO_ATENDIMENTO . ',' . ST_FILA_EM_ATENDIMENTO . ')';
}

function stFilaSqlChamarSolicitante(): string
{
    return stFilaSqlAtendimentoAtivo();
}

/** Fila com BKO atribuído: na fila (1), aguardando (11) ou em atendimento (2). */
function stFilaSqlSolicitantePodeEntrar(): string
{
    return 'status_fila IN (' . ST_FILA_NA_FILA . ',' . ST_FILA_AGUARDANDO_ATENDIMENTO . ',' . ST_FILA_EM_ATENDIMENTO . ')';
}

function stFilaDeveChamarSolicitante(int $statusFila): bool
{
    return $statusFila === ST_FILA_AGUARDANDO_ATENDIMENTO || $statusFila === ST_FILA_EM_ATENDIMENTO;
}

function stFilaEnsureSituacaoAguardando(PDO $PDO): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $stmt = $PDO->prepare('SELECT id_situacao FROM tbl_situacao_chat WHERE id_situacao = ? LIMIT 1');
    $stmt->execute([ST_FILA_AGUARDANDO_ATENDIMENTO]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return;
    }

    $ins = $PDO->prepare('INSERT INTO tbl_situacao_chat (id_situacao, nome_situacao) VALUES (?, ?)');
    $ins->execute([ST_FILA_AGUARDANDO_ATENDIMENTO, 'Aguardando atendimento']);
}

function stFilaAtendimentoEncerrado(int $statusFila): bool
{
    if ($statusFila === ST_FILA_NA_FILA
        || $statusFila === ST_FILA_EM_ATENDIMENTO
        || $statusFila === ST_FILA_AGUARDANDO_ATENDIMENTO
    ) {
        return false;
    }

    return $statusFila >= ST_FILA_CONCLUIDO;
}

function stFilaSqlNaoEncerrado(string $coluna = 'status_fila'): string
{
    return $coluna . ' IN (' . ST_FILA_NA_FILA . ',' . ST_FILA_AGUARDANDO_ATENDIMENTO . ',' . ST_FILA_EM_ATENDIMENTO . ')';
}

/**
 * Encerra fila e chat no banco (idempotente).
 *
 * @return array{id_chat:int,fila_chat_id:int,bko_resp:int,already_closed:bool}
 */
function stChatEncerrarAtendimento(PDO $PDO, string $tokenChat, string $msg, int $contratoId): array
{
    $ret = [
        'id_chat' => 0,
        'fila_chat_id' => 0,
        'bko_resp' => 0,
        'already_closed' => false,
    ];

    $stmt = $PDO->prepare(
        'SELECT a.id_chat, a.fila_chat_id, a.status_chat, b.status_fila,'
        .' timediff(now(), b.hora_inicio) AS ta, b.bko_resp'
        .' FROM tbl_chat_info a'
        .' INNER JOIN tbl_chat_fila b ON a.fila_chat_id = b.id_fila_chat'
        .' WHERE a.token_chat=? LIMIT 1'
    );
    $stmt->execute([$tokenChat]);
    $infoChat = $stmt->fetch(PDO::FETCH_ASSOC);
    if (empty($infoChat['id_chat'])) {
        return $ret;
    }

    $ret['id_chat'] = (int)$infoChat['id_chat'];
    $ret['fila_chat_id'] = (int)$infoChat['fila_chat_id'];
    $ret['bko_resp'] = (int)$infoChat['bko_resp'];
    $statusFila = (int)$infoChat['status_fila'];
    $chatFechado = ((int)$infoChat['status_chat'] === ST_FILA_CONCLUIDO);
    $filaFechada = stFilaAtendimentoEncerrado($statusFila);

    if ($chatFechado && $filaFechada) {
        $ret['already_closed'] = true;
        return $ret;
    }

    if ($ret['bko_resp'] > 0 && !$filaFechada) {
        logAtendimento($PDO, $ret['bko_resp'], 'Pos');
    }

    if ($msg !== '' && !$chatFechado) {
        $stmt = $PDO->prepare(
            'INSERT INTO tbl_chat_msg (chat_id, contrato_id, rem_id, dest_id, msg) VALUES (?, ?, 0, 0, ?)'
        );
        $stmt->execute([$ret['id_chat'], $contratoId, $msg]);
    }

    if (!$filaFechada) {
        $stmt = $PDO->prepare(
            'UPDATE tbl_chat_fila SET status_fila=?, hora_fim=NOW(), ta=?'
            .' WHERE id_fila_chat=? AND ' . stFilaSqlNaoEncerrado('status_fila')
        );
        $stmt->execute([ST_FILA_CONCLUIDO, $infoChat['ta'], $ret['fila_chat_id']]);
    }

    $stmt = $PDO->prepare(
        'UPDATE tbl_chat_info SET status_chat=?, indice=0'
        .' WHERE fila_chat_id=? AND status_chat=1'
    );
    $stmt->execute([ST_FILA_CONCLUIDO, $ret['fila_chat_id']]);

    $stmt = $PDO->prepare(
        'SELECT id, timediff(now(), date_in) AS sla FROM tbl_tma_atend'
        .' WHERE fila_chat_id=? AND date_out IS NULL ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute([$ret['fila_chat_id']]);
    $infoAtend = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($infoAtend['id'])) {
        $stmt = $PDO->prepare('UPDATE tbl_tma_atend SET date_out=NOW(), sla=?, chat_id=? WHERE id=?');
        $stmt->execute([$infoAtend['sla'], $ret['id_chat'], (int)$infoAtend['id']]);
    }

    return $ret;
}

/**
 * Busca registro de fila do solicitante (ate_resp).
 *
 * @return array<string, mixed>
 */
function stChatAteQueryFilaSolicitante(PDO $PDO, int $userId, int $idFilaChat): array
{
    $sqlFila = 'SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id,'
        .' contrato_id, motivo, timediff(now(), data_hora) AS te'
        .' FROM tbl_chat_fila WHERE ate_resp=? AND '.stFilaSqlSolicitantePodeEntrar();
    if ($idFilaChat > 0) {
        $sqlFila .= ' AND id_fila_chat=? ORDER BY id_fila_chat DESC LIMIT 1';
        $stmt = $PDO->prepare($sqlFila);
        $stmt->execute([$userId, $idFilaChat]);
    } else {
        $sqlFila .= ' ORDER BY status_fila DESC, id_fila_chat DESC LIMIT 1';
        $stmt = $PDO->prepare($sqlFila);
        $stmt->execute([$userId]);
    }
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * @param mixed $value
 */
function stChatAteNormalizeBkoResp($value): int
{
    if ($value === null || $value === '' || $value === false) {
        return 0;
    }
    return (int)$value;
}

/**
 * @return array<string, mixed>
 */
function stChatAteFetchFilaSolicitante(PDO $PDO, int $userId, int $idFilaChatReq): array
{
    $infFila = stChatAteQueryFilaSolicitante($PDO, $userId, $idFilaChatReq);

    if (empty($infFila['id_fila_chat']) && $idFilaChatReq > 0) {
        $infFila = stChatAteQueryFilaSolicitante($PDO, $userId, 0);
    }

    if (empty($infFila['id_fila_chat'])) {
        return $infFila;
    }

    $bkoResp = stChatAteNormalizeBkoResp($infFila['bko_resp'] ?? 0);
    $statusFila = (int)$infFila['status_fila'];

    if ($statusFila === ST_FILA_NA_FILA && $bkoResp > 0) {
        stFilaEnsureSituacaoAguardando($PDO);
        $teVal = !empty($infFila['te']) ? $infFila['te'] : '';
        $upd = $PDO->prepare(
            'UPDATE tbl_chat_fila SET status_fila=?, te=COALESCE(NULLIF(te, \'\'), ?)'
            .' WHERE id_fila_chat=? AND ate_resp=? AND status_fila=?'
        );
        $upd->execute([
            ST_FILA_AGUARDANDO_ATENDIMENTO,
            $teVal,
            (int)$infFila['id_fila_chat'],
            $userId,
            ST_FILA_NA_FILA,
        ]);
        if ($upd->rowCount() > 0) {
            $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
        }
    }

    return $infFila;
}

/**
 * @return array<string, mixed>
 */
function stChatFindPairChat(PDO $PDO, int $contratoId, int $userA, int $userB, bool $onlyActive = false): array
{
    $sql = 'SELECT id_chat, token_chat, status_chat, indice, fila_chat_id, rem_chat, dest_chat'
        .' FROM tbl_chat_info WHERE contrato_id=?'
        .' AND ((rem_chat=? AND dest_chat=?) OR (rem_chat=? AND dest_chat=?))';
    if ($onlyActive) {
        $sql .= ' AND status_chat=1';
    }
    $sql .= ' ORDER BY id_chat DESC LIMIT 1';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$contratoId, $userA, $userB, $userB, $userA]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
}

/**
 * Localiza ou cria tbl_chat_info para o solicitante (mesma lógica do BKO).
 *
 * @param array<string, mixed> $infFila
 * @return array<string, mixed>
 */
function stChatResolveForFilaSolicitante(PDO $PDO, array $infFila, int $solId, int $bkoId, int $contratoFallback = 0): array
{
    $idFilaChat = (int)$infFila['id_fila_chat'];
    $contratoId = (int)($infFila['contrato_id'] ?? 0);
    if ($contratoId <= 0) {
        $contratoId = $contratoFallback;
    }
    $filaId = (int)($infFila['fila_id'] ?? 0);
    $assuntoId = (int)($infFila['assunto_id'] ?? 0);
    $infoChat = [];

    $stmt = $PDO->prepare(
        'SELECT id_chat, token_chat, status_chat, indice, fila_chat_id, rem_chat, dest_chat'
        .' FROM tbl_chat_info WHERE fila_chat_id=? ORDER BY id_chat DESC LIMIT 1'
    );
    $stmt->execute([$idFilaChat]);
    $byFila = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    if (!empty($byFila['id_chat'])) {
        if ((int)$byFila['status_chat'] === 1) {
            $infoChat = $byFila;
        } elseif (!stFilaAtendimentoEncerrado((int)$infFila['status_fila'])) {
            $stmtRe = $PDO->prepare(
                'UPDATE tbl_chat_info SET status_chat=1, indice=0, fila_chat_id=? WHERE id_chat=?'
            );
            $stmtRe->execute([$idFilaChat, (int)$byFila['id_chat']]);
            $infoChat = $byFila;
            $infoChat['status_chat'] = 1;
            $infoChat['fila_chat_id'] = $idFilaChat;
        }
    }

    // Não reutiliza chat por par de usuários em novos atendimentos:
    // cada id_fila_chat deve ter seu próprio id_chat para evitar histórico antigo.

    if (empty($infoChat['id_chat']) && $bkoId > 0 && $solId > 0 && $contratoId > 0
        && !stFilaAtendimentoEncerrado((int)$infFila['status_fila'])
    ) {
        $tokenChat = md5($bkoId . '|' . $solId . '|' . $idFilaChat . '|' . date('YmdHis'));
        $sqlIns = 'INSERT INTO tbl_chat_info (contrato_id, token_chat, assunto_id, fila_id, rem_chat, dest_chat, status_chat, fila_chat_id, indice)'
            .' VALUES (?, ?, ?, ?, ?, ?, 1, ?, 0)';
        try {
            $stmt = $PDO->prepare($sqlIns);
            $stmt->execute([
                $contratoId,
                $tokenChat,
                $assuntoId,
                $filaId,
                $bkoId,
                $solId,
                $idFilaChat,
            ]);
            $newId = (int)$PDO->lastInsertId();
            if ($newId > 0) {
                $infoChat = [
                    'id_chat' => $newId,
                    'token_chat' => $tokenChat,
                    'status_chat' => 1,
                    'indice' => 0,
                    'fila_chat_id' => $idFilaChat,
                ];
            }
        } catch (Throwable $e) {
            $stmt = $PDO->prepare(
                'SELECT id_chat, token_chat, status_chat, indice, fila_chat_id'
                .' FROM tbl_chat_info WHERE fila_chat_id=? AND status_chat=1 ORDER BY id_chat DESC LIMIT 1'
            );
            $stmt->execute([$idFilaChat]);
            $infoChat = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        }
    }

    return $infoChat;
}

/**
 * Estado da abertura do chat para o solicitante.
 *
 * @return array{state:string, infFila:array<string,mixed>, infoChat:array<string,mixed>, chatId:int, bkoResp:int, message:string}
 */
function stChatAteSolBootstrap(PDO $PDO, int $userId, int $idFilaChatReq, int $contratoId = 0): array
{
    $infFila = stChatAteFetchFilaSolicitante($PDO, $userId, $idFilaChatReq);
    $result = [
        'state' => 'wait_fila',
        'infFila' => $infFila,
        'infoChat' => [],
        'chatId' => 0,
        'bkoResp' => 0,
        'message' => 'Aguardando sua posição na fila...',
    ];

    if (empty($infFila['id_fila_chat'])) {
        return $result;
    }

    if (stFilaAtendimentoEncerrado((int)$infFila['status_fila'])) {
        $result['state'] = 'closed';
        $result['message'] = 'Atendimento encerrado.';
        return $result;
    }

    $bkoResp = stChatAteNormalizeBkoResp($infFila['bko_resp'] ?? 0);
    $result['bkoResp'] = $bkoResp;
    $statusFila = (int)$infFila['status_fila'];

    if ($bkoResp <= 0) {
        if (stFilaDeveChamarSolicitante($statusFila)) {
            $stmt = $PDO->prepare(
                'SELECT bko_resp FROM tbl_chat_fila WHERE id_fila_chat=? AND ate_resp=? LIMIT 1'
            );
            $stmt->execute([(int)$infFila['id_fila_chat'], $userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $bkoResp = stChatAteNormalizeBkoResp($row['bko_resp'] ?? 0);
            if ($bkoResp > 0) {
                $result['bkoResp'] = $bkoResp;
                $infFila['bko_resp'] = $bkoResp;
            }
        }
    }

    if ($bkoResp <= 0 && stFilaDeveChamarSolicitante($statusFila)) {
        $stmt = $PDO->prepare(
            'SELECT rem_chat, dest_chat FROM tbl_chat_info WHERE fila_chat_id=? ORDER BY id_chat DESC LIMIT 1'
        );
        $stmt->execute([(int)$infFila['id_fila_chat']]);
        $chatRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($chatRow['rem_chat']) || !empty($chatRow['dest_chat'])) {
            if ((int)$chatRow['dest_chat'] === $userId && (int)$chatRow['rem_chat'] > 0) {
                $bkoResp = (int)$chatRow['rem_chat'];
            } elseif ((int)$chatRow['rem_chat'] === $userId && (int)$chatRow['dest_chat'] > 0) {
                $bkoResp = (int)$chatRow['dest_chat'];
            }
            if ($bkoResp > 0) {
                $result['bkoResp'] = $bkoResp;
                $infFila['bko_resp'] = $bkoResp;
            }
        }
    }

    if ($bkoResp <= 0) {
        $result['state'] = 'wait_bko';
        $result['message'] = 'Aguardando o atendente aceitar o chamado...';
        return $result;
    }

    $infoChat = stChatResolveForFilaSolicitante($PDO, $infFila, $userId, $bkoResp, $contratoId);
    $result['infoChat'] = $infoChat;
    $result['chatId'] = !empty($infoChat['id_chat']) ? (int)$infoChat['id_chat'] : 0;

    if ($result['chatId'] > 0) {
        $result['state'] = 'ready';
        $result['message'] = 'Chat pronto.';
    } else {
        $result['state'] = 'wait_chat';
        $result['message'] = 'Preparando sala de chat...';
    }

    return $result;
}

/**
 * @return array<string, mixed>
 */
function stChatBkoFetchFila(PDO $PDO, int $bkoId, int $contratoId, string $protocolo, int $filaIdPref = 0, string $filasIn = ''): array
{
    $cols = 'id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, te,'
        .' timediff(now(), data_hora) as te_diff';

    if ($protocolo !== '') {
        $sql = 'SELECT '.$cols.' FROM tbl_chat_fila WHERE protocolo=? AND contrato_id=?'
            .' AND status_fila IN ('.ST_FILA_NA_FILA.','.ST_FILA_AGUARDANDO_ATENDIMENTO.','.ST_FILA_EM_ATENDIMENTO.')'
            .' ORDER BY id_fila_chat DESC LIMIT 1';
        $stmt = $PDO->prepare($sql);
        $stmt->execute([$protocolo, $contratoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($row['id_fila_chat'])) {
            if ($row['te'] === '' || $row['te'] === null) {
                $row['te'] = $row['te_diff'] ?? '';
            }
            return $row;
        }
    }

    $pick = function (string $whereFila) use ($PDO, $cols, $contratoId): array {
        $sql = 'SELECT '.$cols.' FROM tbl_chat_fila WHERE status_fila='.ST_FILA_NA_FILA
            .' AND contrato_id='.$contratoId.' AND (bko_resp IS NULL OR bko_resp=\'\' OR bko_resp=0)'
            .' AND '.$whereFila.' ORDER BY id_fila_chat ASC LIMIT 1';
        $stmt = $PDO->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($row['id_fila_chat']) && ($row['te'] === '' || $row['te'] === null)) {
            $row['te'] = $row['te_diff'] ?? '';
        }
        return $row;
    };

    if ($filaIdPref > 0) {
        $row = $pick('fila_id='.$filaIdPref);
        if (!empty($row['id_fila_chat'])) {
            return $row;
        }
    }
    if ($filasIn !== '') {
        return $pick('fila_id IN ('.$filasIn.')');
    }
    return [];
}

/**
 * @param array<string, mixed> $infFila
 * @return array<string, mixed>
 */
function stChatBkoClaimFila(PDO $PDO, array $infFila, int $bkoId): array
{
    if (empty($infFila['id_fila_chat'])) {
        return $infFila;
    }
    $bkoResp = stChatAteNormalizeBkoResp($infFila['bko_resp'] ?? 0);
    if ($bkoResp > 0 && $bkoResp !== $bkoId) {
        return [];
    }
    if ($bkoResp <= 0) {
        stFilaEnsureSituacaoAguardando($PDO);
        $teVal = !empty($infFila['te']) ? (string)$infFila['te'] : '';
        $stmt = $PDO->prepare(
            'UPDATE tbl_chat_fila SET status_fila=?, bko_resp=?, te=COALESCE(NULLIF(te, \'\'), ?)'
            .' WHERE id_fila_chat=? AND (bko_resp IS NULL OR bko_resp=\'\' OR bko_resp=0)'
        );
        $stmt->execute([
            ST_FILA_AGUARDANDO_ATENDIMENTO,
            $bkoId,
            $teVal,
            (int)$infFila['id_fila_chat'],
        ]);
        if ($stmt->rowCount() > 0) {
            logAtendimento($PDO, $bkoId, 'Tratamento');
        }
        $infFila['bko_resp'] = $bkoId;
        $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
    } elseif ((int)$infFila['status_fila'] === ST_FILA_NA_FILA && $bkoResp === $bkoId) {
        stFilaEnsureSituacaoAguardando($PDO);
        $stmt = $PDO->prepare(
            'UPDATE tbl_chat_fila SET status_fila=? WHERE id_fila_chat=? AND bko_resp=? AND status_fila=?'
        );
        $stmt->execute([
            ST_FILA_AGUARDANDO_ATENDIMENTO,
            (int)$infFila['id_fila_chat'],
            $bkoId,
            ST_FILA_NA_FILA,
        ]);
        $infFila['status_fila'] = ST_FILA_AGUARDANDO_ATENDIMENTO;
    }
    return $infFila;
}

/**
 * Estado da abertura do chat para o backoffice.
 *
 * @return array{state:string, infFila:array<string,mixed>, infoChat:array<string,mixed>, chatId:int, message:string}
 */
function stChatBkoBootstrap(
    PDO $PDO,
    int $bkoId,
    string $protocolo,
    int $contratoId,
    int $filaIdPref = 0,
    string $filasIn = '',
    int $indiceTab = 0,
    int $idFilaChatReq = 0
): array {
    $result = [
        'state' => 'no_fila',
        'infFila' => [],
        'infoChat' => [],
        'chatId' => 0,
        'message' => 'Nenhum atendimento disponível.',
    ];

    $infFila = stChatBkoFetchFila($PDO, $bkoId, $contratoId, $protocolo, $filaIdPref, $filasIn);

    if (empty($infFila['id_fila_chat']) && $idFilaChatReq > 0) {
        $stmt = $PDO->prepare(
            'SELECT id_fila_chat, protocolo, status_fila, ate_resp, bko_resp, hora_inicio, fila_id, assunto_id, contrato_id, te,'
            .' timediff(now(), data_hora) as te_diff'
            .' FROM tbl_chat_fila WHERE id_fila_chat=? AND contrato_id=?'
            .' AND status_fila IN ('.ST_FILA_NA_FILA.','.ST_FILA_AGUARDANDO_ATENDIMENTO.','.ST_FILA_EM_ATENDIMENTO.')'
            .' LIMIT 1'
        );
        $stmt->execute([$idFilaChatReq, $contratoId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($row['id_fila_chat'])) {
            if ($row['te'] === '' || $row['te'] === null) {
                $row['te'] = $row['te_diff'] ?? '';
            }
            $infFila = $row;
        }
    }

    if (empty($infFila['id_fila_chat'])) {
        return $result;
    }

    if (stFilaAtendimentoEncerrado((int)$infFila['status_fila'])) {
        $result['state'] = 'closed';
        $result['message'] = 'Atendimento encerrado.';
        $result['infFila'] = $infFila;
        return $result;
    }

    $filaContrato = (int)($infFila['contrato_id'] ?? 0);
    if ($filaContrato > 0 && $contratoId > 0 && $filaContrato !== $contratoId) {
        $result['state'] = 'no_fila';
        $result['message'] = 'Atendimento indisponível para este contrato.';
        return $result;
    }
    if ($filaContrato <= 0 && $contratoId > 0) {
        $infFila['contrato_id'] = $contratoId;
    }

    $bkoResp = stChatAteNormalizeBkoResp($infFila['bko_resp'] ?? 0);
    if ($bkoResp > 0 && $bkoResp !== $bkoId) {
        $result['state'] = 'taken';
        $result['message'] = 'Atendimento já vinculado a outro operador.';
        $result['infFila'] = $infFila;
        return $result;
    }

    $infFila = stChatBkoClaimFila($PDO, $infFila, $bkoId);
    if (empty($infFila['id_fila_chat'])) {
        $result['state'] = 'taken';
        $result['message'] = 'Atendimento indisponível.';
        return $result;
    }

    $solId = (int)$infFila['ate_resp'];
    $contratoResolve = $filaContrato > 0 ? $filaContrato : $contratoId;
    $infoChat = stChatResolveForFilaSolicitante($PDO, $infFila, $solId, $bkoId, $contratoResolve);

    if (empty($infoChat['id_chat']) && $solId <= 0) {
        $stmt = $PDO->prepare(
            'SELECT id_chat, token_chat, status_chat, indice, fila_chat_id, rem_chat, dest_chat'
            .' FROM tbl_chat_info WHERE fila_chat_id=? ORDER BY id_chat DESC LIMIT 1'
        );
        $stmt->execute([(int)$infFila['id_fila_chat']]);
        $orphan = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        if (!empty($orphan['id_chat']) && (int)$orphan['status_chat'] !== 1
            && !stFilaAtendimentoEncerrado((int)$infFila['status_fila'])
        ) {
            $stmtRe = $PDO->prepare(
                'UPDATE tbl_chat_info SET status_chat=1, indice=0, fila_chat_id=? WHERE id_chat=?'
            );
            $stmtRe->execute([(int)$infFila['id_fila_chat'], (int)$orphan['id_chat']]);
            $orphan['status_chat'] = 1;
            $orphan['fila_chat_id'] = (int)$infFila['id_fila_chat'];
            $infoChat = $orphan;
        } elseif (!empty($orphan['id_chat']) && (int)$orphan['status_chat'] === 1) {
            $infoChat = $orphan;
        }
    }

    if (!empty($infoChat['id_chat']) && $indiceTab > 0) {
        $stmtIdx = $PDO->prepare(
            'UPDATE tbl_chat_info SET indice=?, fila_chat_id=?, status_chat=1 WHERE id_chat=?'
        );
        $stmtIdx->execute([(string)$indiceTab, (int)$infFila['id_fila_chat'], (int)$infoChat['id_chat']]);
        $infoChat['indice'] = (string)$indiceTab;
        $infoChat['fila_chat_id'] = (int)$infFila['id_fila_chat'];
        $infoChat['status_chat'] = 1;
    }

    $result['infFila'] = $infFila;
    $result['infoChat'] = $infoChat;
    $result['chatId'] = !empty($infoChat['id_chat']) ? (int)$infoChat['id_chat'] : 0;

    if ($result['chatId'] > 0) {
        $result['state'] = 'ready';
        $result['message'] = 'Chat pronto.';
    } else {
        $result['state'] = 'wait_chat';
        $result['message'] = 'Preparando sala de chat...';
    }

    return $result;
}

/**
 * Atendimentos ativos do BKO (status 11/2), com ou sem tbl_chat_info.
 *
 * @return array<int, array<string, mixed>>
 */
function stBkoListarAtendimentosAtivos(PDO $PDO, int $bkoId, int $contratoId): array
{
    $sql = 'SELECT cf.id_fila_chat, cf.protocolo, cf.ate_resp, u.nome, u.sobrenome,'
        .' cf.motivo, cf.bko_resp, cf.status_fila, ci.indice AS indice_chat'
        .' FROM tbl_chat_fila cf'
        .' INNER JOIN tbl_user u ON u.id_user = cf.ate_resp'
        .' LEFT JOIN tbl_chat_info ci ON ci.fila_chat_id = cf.id_fila_chat AND ci.status_chat = 1'
        .' WHERE cf.bko_resp = ? AND cf.contrato_id = ? AND cf.status_fila IN ('
        .ST_FILA_AGUARDANDO_ATENDIMENTO.','.ST_FILA_EM_ATENDIMENTO.')'
        .' ORDER BY cf.id_fila_chat ASC';
    $stmt = $PDO->prepare($sql);
    $stmt->execute([$bkoId, $contratoId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    $out = [];
    $pos = 0;
    foreach ($rows as $row) {
        $pos++;
        $indiceChat = isset($row['indice_chat']) ? (int)$row['indice_chat'] : 0;
        $out[] = [
            'id_fila_chat' => (int)$row['id_fila_chat'],
            'protocolo' => (string)$row['protocolo'],
            'ate_resp' => (int)$row['ate_resp'],
            'nome' => (string)$row['nome'],
            'sobrenome' => (string)$row['sobrenome'],
            'indice' => $indiceChat > 0 ? $indiceChat : $pos,
            'motivo' => (string)($row['motivo'] ?? ''),
            'bko_resp' => (int)$row['bko_resp'],
        ];
    }
    return $out;
}

/**
 * Protocolo do atendimento ativo do BKO para reabrir a aba após refresh.
 */
function stBkoFindProtocoloAtivo(PDO $PDO, int $bkoId, int $contratoId, int $indice): string
{
    $lista = stBkoListarAtendimentosAtivos($PDO, $bkoId, $contratoId);
    if (empty($lista)) {
        return '';
    }
    foreach ($lista as $item) {
        if ((int)$item['indice'] === $indice) {
            return (string)$item['protocolo'];
        }
    }
    if (count($lista) === 1) {
        return (string)$lista[0]['protocolo'];
    }
    foreach ($lista as $item) {
        if ($indice === 1) {
            return (string)$item['protocolo'];
        }
    }
    return '';
}

/**
 * Emite script para reabrir chat BKO na aba, se necessário.
 */
function stBkoEmitRestoreChatScript(int $indice, string $protocolo, bool $skipRestore): void
{
    if ($skipRestore || $protocolo === '') {
        return;
    }
    echo "<script>setTimeout(function(){ if(typeof actionPageChat==='function'){actionPageChat("
        .(int)$indice.", '".addslashes($protocolo)."');}}, 0);</script>";
}
