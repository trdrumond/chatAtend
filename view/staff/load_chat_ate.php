<?php
include("../cnf/session.php");

/** @var array<string, mixed> $infoUser */
/** @var PDO $PDO */

header('Content-Type: application/json; charset=utf-8');

//depurador($_POST);
$idFila = isset($_POST['idFila']) ? (int)$_POST['idFila'] : 0;
$filaId = isset($_POST['fila']) ? (int)$_POST['fila'] : 0;
$userId = (int)$infoUser['id_user'];

if ($idFila > 0 && isset($_SESSION['fila_abandono_pendente'][$idFila])) {
    unset($_SESSION['fila_abandono_pendente'][$idFila]);
    if (empty($_SESSION['fila_abandono_pendente'])) {
        unset($_SESSION['fila_abandono_pendente']);
    }
}

$retorno = [
    'ok' => true,
    'redirect' => '',
    'posicao' => null,
    'chamarAtendimento' => false
];

$filaAtual = [];
if ($idFila > 0) {
    $stm = $PDO->prepare("SELECT id_fila_chat, fila_id, status_fila, bko_resp from tbl_chat_fila where id_fila_chat=? and ate_resp=? limit 1");
    $stm->execute([$idFila, $userId]);
    $filaAtual = $stm->fetch(PDO::FETCH_ASSOC);
}

if (!empty($filaAtual['id_fila_chat'])) {
    $statusAtual = (int)$filaAtual['status_fila'];
    if (stFilaDeveChamarSolicitante($statusAtual)
        || ($statusAtual === ST_FILA_NA_FILA && !empty($filaAtual['bko_resp']) && (int)$filaAtual['bko_resp'] > 0)) {
        $retorno['redirect'] = 'chat-ate';
        $retorno['status_fila'] = $statusAtual === ST_FILA_NA_FILA ? ST_FILA_AGUARDANDO_ATENDIMENTO : $statusAtual;
    } else if ($statusAtual === ST_FILA_NA_FILA) {
        $filaRef = (int)$filaAtual['fila_id'];
        if ($filaRef <= 0) {
            $filaRef = $filaId;
        }

        $stm = $PDO->prepare("SELECT count(id_fila_chat) as qtd from tbl_chat_fila where fila_id=? and id_fila_chat < ? and status_fila=".ST_FILA_NA_FILA);
        $stm->execute([$filaRef, $idFila]);
        $qtdFila = $stm->fetch(PDO::FETCH_ASSOC);
        $qtdAntes = isset($qtdFila['qtd']) ? (int)$qtdFila['qtd'] : 0;

        $retorno['posicao'] = $qtdAntes + 1;
        $retorno['aFrente'] = $qtdAntes;

        $stmTotal = $PDO->prepare("SELECT count(id_fila_chat) as qtd from tbl_chat_fila where fila_id=? and status_fila=".ST_FILA_NA_FILA);
        $stmTotal->execute([$filaRef]);
        $totalFila = $stmTotal->fetch(PDO::FETCH_ASSOC);
        $retorno['totalFila'] = isset($totalFila['qtd']) ? (int)$totalFila['qtd'] : $retorno['posicao'];

        if ($qtdAntes === 0) {
            $retorno['chamarAtendimento'] = true;
        }
    } else {
        $retorno['redirect'] = 'dash-cha';
    }
} else {
    // Fallback: verifica se ainda existe fila para este atendente.
    $stm = $PDO->prepare("SELECT id_fila_chat from tbl_chat_fila where ate_resp=? and status_fila=".ST_FILA_NA_FILA." order by id_fila_chat asc limit 1");
    $stm->execute([$userId]);
    $aindaEmFila = $stm->fetch(PDO::FETCH_ASSOC);
    if (!empty($aindaEmFila['id_fila_chat'])) {
        // Evita ciclo dash-cha <-> chat-fila quando houver troca de id_fila_chat.
        $retorno['redirect'] = 'chat-fila';
        echo json_encode($retorno);
        exit;
    }

    // Pode já ter sido direcionado para atendimento por outro fluxo.
    $stm = $PDO->prepare(
        'SELECT id_fila_chat from tbl_chat_fila where ate_resp=?'
        .' and '.stFilaSqlSolicitantePodeEntrar().' order by id_fila_chat desc limit 1'
    );
    $stm->execute([$userId]);
    $emAtendimento = $stm->fetch(PDO::FETCH_ASSOC);

    if (!empty($emAtendimento['id_fila_chat'])) {
        $retorno['redirect'] = 'chat-ate';
    } else {
        $retorno['redirect'] = 'dash-cha';
    }
}

echo json_encode($retorno);


