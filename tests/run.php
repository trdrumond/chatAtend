<?php
/**
 * Runner de testes estáticos, lint, smoke HTTP e carga limitada.
 * Uso: php tests/run.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$failed = 0;
$passed = 0;
$lines = [];

function stAssert(bool $ok, string $msg): void
{
    global $failed, $passed, $lines;
    if ($ok) {
        $passed++;
        $lines[] = "[PASS] $msg";
    } else {
        $failed++;
        $lines[] = "[FAIL] $msg";
    }
}

$lintFiles = [
    $root . '/login.php',
    $root . '/view/cnf/session.php',
    $root . '/view/cnf/session_config.php',
    $root . '/view/cnf/conexao.php',
    $root . '/view/cnf/MasterPassword.php',
    $root . '/view/cnf/func.php',
    $root . '/view/cnf/replace.php',
    $root . '/view/cnf/replace_msg.php',
    $root . '/view/cnf/replace_08042026.php',
    $root . '/view/cnf/st_fila_status.php',
    $root . '/view/staff/save_user.php',
    $root . '/view/staff/reset_senha.php',
    $root . '/view/staff/verifica_senha.php',
    $root . '/view/staff/loadText.php',
    $root . '/view/staff/newpass.php',
    $root . '/view/staff/load_api.php',
    $root . '/view/staff/painel_load_online.php',
    $root . '/view/staff/cron_ia_analise_diaria.php',
    $root . '/view/staff/save_msg.php',
    $root . '/view/staff/save_ia_config.php',
    $root . '/view/staff/save_msg_fim.php',
    $root . '/view/staff/alt_user.php',
    $root . '/view/staff/save_cancelFila.php',
    $root . '/view/staff/load_file.php',
    $root . '/view/staff/load_cancel_fila.php',
    $root . '/view/staff/save_msg_transfer.php',
    $root . '/view/staff/save_pend_info.php',
    $root . '/view/staff/save_call.php',
    $root . '/view/staff/save_pos.php',
    $root . '/view/staff/save_new_chat.php',
    $root . '/view/staff/save_img_perfil.php',
    $root . '/view/staff/save_reg.php',
    $root . '/view/staff/save_emp.php',
    $root . '/view/staff/save_age.php',
    $root . '/view/staff/save_ass.php',
    $root . '/view/staff/save_pri.php',
    $root . '/view/staff/save_ctt.php',
    $root . '/view/staff/save_faq_config.php',
    $root . '/view/staff/save_men_config.php',
    $root . '/view/staff/save_fil.php',
    $root . '/view/staff/alt_reg.php',
    $root . '/view/staff/alt_emp.php',
    $root . '/view/staff/alt_age.php',
    $root . '/view/staff/alt_ass.php',
    $root . '/view/staff/alt_pri.php',
    $root . '/view/staff/alt_ctt.php',
    $root . '/view/staff/alt_faq_config.php',
    $root . '/view/staff/alt_men_config.php',
    $root . '/view/staff/alt_fil.php',
    $root . '/view/staff/load_dados_tme.php',
    $root . '/view/staff/load_dados_tma.php',
    $root . '/view/staff/load_dados_fila.php',
    $root . '/view/staff/load_dados_atend.php',
    $root . '/view/staff/load_dados_concluido.php',
    $root . '/view/staff/load_dados_pendente.php',
    $root . '/view/staff/load_dados_pend_ate.php',
    $root . '/view/staff/load_dados_hist.php',
    $root . '/view/staff/load_dados_rel.php',
    $root . '/view/staff/load_dados_ind.php',
    $root . '/view/staff/load_fila.php',
    $root . '/view/staff/load_fila_user.php',
    $root . '/view/staff/load_fila_atend.php',
    $root . '/view/staff/load_fila_ativa.php',
    $root . '/view/staff/load_fila_pos.php',
    $root . '/view/staff/load_fila_bko.php',
    $root . '/view/staff/alt_ctt_com.php',
    $root . '/view/staff/alt_ctt_env_img.php',
    $root . '/view/staff/alt_ctt_env_file.php',
    $root . '/view/staff/alt_ctt_com_new_conv.php',
    $root . '/view/staff/alt_ctt_com_grupos.php',
    $root . '/view/staff/alt_ctt_com_men_massa.php',
    $root . '/view/staff/alt_ctt_com_resp_men.php',
    $root . '/view/staff/load_chat_ate.php',
    $root . '/view/staff/load_chat_bko.php',
    $root . '/view/staff/verificaBko.php',
    $root . '/view/staff/verifica_atendente.php',
    $root . '/view/staff/save_pause.php',
    $root . '/view/staff/save_recall.php',
    $root . '/view/staff/save_new_fila.php',
    $root . '/view/staff/load_ass.php',
    $root . '/view/staff/load_assunto_men.php',
    $root . '/view/staff/load_ass_json.php',
    $root . '/view/staff/load_pend_alt_sol.php',
    $root . '/view/staff/load_pend_alt_bko.php',
    $root . '/view/staff/load_regional.php',
    $root . '/view/staff/load_rel_emp.php',
    $root . '/view/staff/loadId.php',
    $root . '/view/staff/alt_filas_config.php',
    $root . '/view/staff/alt_config_filas.php',
    $root . '/view/staff/alt_reset_img.php',
    $root . '/view/staff/load_pend_info.php',
    $root . '/view/staff/save_msg_com.php',
    $root . '/view/staff/save_msg_com_ind.php',
    $root . '/view/staff/save_file_com.php',
    $root . '/view/staff/save_file_grupo.php',
    $root . '/view/staff/save_new_grupo.php',
    $root . '/view/staff/send_msg_massa.php',
    $root . '/view/staff/loadText_com.php',
    $root . '/view/staff/loadText_com_ind.php',
    $root . '/view/staff/load_com.php',
    $root . '/view/staff/load_com_hist.php',
    $root . '/view/staff/loadChatCom.php',
    $root . '/view/staff/load_com_list.php',
    $root . '/view/staff/load_com_count.php',
    $root . '/view/staff/pos_alt_campo.php',
    $root . '/view/staff/pos_alt_campo_obg.php',
    $root . '/view/staff/pos_alt_sel.php',
    $root . '/view/staff/pos_alt_ordem_input.php',
    $root . '/view/staff/pos_save_form_config.php',
    $root . '/view/staff/pos_save_form_config_exi.php',
    $root . '/view/staff/pos_save_input_option.php',
    $root . '/view/staff/pos_config_form_options.php',
    $root . '/view/staff/mon_alt_campo.php',
    $root . '/view/staff/mon_alt_campo_obg.php',
    $root . '/view/staff/mon_alt_campo_qualif.php',
    $root . '/view/staff/mon_alt_sel.php',
    $root . '/view/staff/mon_alt_sel_opt.php',
    $root . '/view/staff/mon_alt_sel_mon.php',
    $root . '/view/staff/mon_alt_ordem_input.php',
    $root . '/view/staff/mon_save_form_config.php',
    $root . '/view/staff/mon_save_input_option.php',
    $root . '/view/staff/mon_config_form_options.php',
    $root . '/view/staff/save_mon.php',
    $root . '/view/staff/load_monitoria.php',
    $root . '/view/staff/save_dem.php',
    $root . '/view/staff/save_ser.php',
    $root . '/view/staff/save_ser_config.php',
    $root . '/view/staff/save_input_option.php',
    $root . '/view/staff/alt_ser.php',
    $root . '/view/staff/config_serv_options.php',
    $root . '/view/staff/hr_save_form_config.php',
    $root . '/view/staff/hr_alt_campo.php',
    $root . '/view/staff/hr_del_campo.php',
    $root . '/view/staff/alt_pend_bko.php',
    $root . '/view/staff/alt_pend_sol.php',
    $root . '/view/staff/load_hist.php',
    $root . '/view/staff/load_hist_pend.php',
    $root . '/view/staff/load_rel_pend.php',
    $root . '/view/staff/load_assunto.php',
    $root . '/view/staff/load_assunto_fila.php',
    $root . '/view/staff/alt_grupo.php',
    $root . '/view/staff/derruba_fila.php',
    $root . '/view/staff/load_deposit_file_hist.php',
    $root . '/view/staff/load_deposit_file.php',
    $root . '/view/staff/load_conc.php',
    $root . '/view/staff/load_top_five.php',
    $root . '/view/staff/load_chart_3.php',
    $root . '/view/staff/load_online.php',
    $root . '/view/staff/alt_form_user.php',
    $root . '/view/staff/load_dados_dash_ind.php',
    $root . '/view/staff/load_dados_dash_ind_painel.php',
    $root . '/view/staff/loadText_group.php',
    $root . '/view/staff/log_dados.php',
    $root . '/view/staff/load_dados_pend.php',
    $root . '/view/staff/envio_mail_cad.php',
    $root . '/view/staff/import_users.php',
    $root . '/view/staff/img_group.php',
    $root . '/view/staff/dadosPdf.php',
    $root . '/view/staff/load_municipio.php',
    $root . '/view/staff/load_rank.php',
    $root . '/view/staff/painel_load_fila_ativa.php',
    $root . '/view/staff/load_info_graf.php',
    $root . '/view/staff/load_graf_1.php',
    $root . '/view/staff/load_graf_2.php',
    $root . '/view/staff/load_col.php',
    $root . '/view/staff/del_pri.php',
    $root . '/view/staff/alt_sel.php',
    $root . '/view/staff/alt_campo.php',
    $root . '/view/staff/load_star.php',
    $root . '/view/staff/tbl_sel.php',
    $root . '/view/staff/tbl_config_servicos.php',
    $root . '/view/staff/pos_tbl_sel.php',
    $root . '/view/staff/mon_tbl_sel.php',
    $root . '/view/staff/hr_tbl_config_form.php',
    $root . '/view/staff/graf_rel_1.php',
    $root . '/view/staff/chat-bko.php',
    $root . '/view/staff/pos_tbl_config_form.php',
    $root . '/view/staff/mon_tbl_config_form.php',
    $root . '/view/staff/alt_cad_usu.php',
    $root . '/view/staff/load_painel.php',
    $root . '/view/page/action/idx/chat-fila.php',
    $root . '/view/action.php',
    $root . '/access/login_chat.php',
    $root . '/access/conexao.php',
    $root . '/access/session.php',
    $root . '/access/func.php',
    $root . '/api/index.php',
    $root . '/testes/functxt.php',
    $root . '/view/api/new_pass.php',
    $root . '/view/staff/dash_fila_live.php',
    $root . '/view/staff/load_contrato.php',
    $root . '/view/staff/load_empresa.php',
    $root . '/view/staff/load_agencia.php',
    $root . '/view/staff/load_usuarios.php',
    $root . '/view/staff/export_usuarios_excel.php',
    $root . '/view/staff/dash-fila.php',
    $root . '/view/page/action/cnf/cad-usu.php',
    $root . '/view/page/action/cnf/cad-ass.php',
    $root . '/view/page/action/cnf/cad-faq.php',
    $root . '/view/page/action/cnf/cad-men.php',
    $root . '/view/page/action/cnf/cad-age.php',
    $root . '/view/page/action/cnf/cad-emp.php',
    $root . '/view/page/action/cnf/cad-reg.php',
    $root . '/view/page/action/cnf/cad-fil.php',
    $root . '/view/page/action/cnf/cad-ctt.php',
    $root . '/view/page/action/cnf/cnf-dash.php',
    $root . '/view/page/action/idx/ia-insights.php',
    $root . '/view/page/action/idx/gov-analytics.php',
    $root . '/view/staff/dash_gov_data.php',
    $root . '/view/staff/dash_ia_insights_data.php',
    $root . '/view/staff/load_info_fila.php',
    $root . '/view/staff/load_info_fila_painel.php',
    $root . '/view/staff/painel_load_info_fila.php',
    $root . '/view/staff/alt_config_mail.php',
    $root . '/view/staff/save_msg_fim.php',
    $root . '/view/staff/outChat.php',
    $root . '/view/staff/config_servicos.php',
    $root . '/api_monitora/index.php',
    $root . '/api_monitora/src/MonitoraAuth.php',
];

foreach ($lintFiles as $file) {
    $out = [];
    $code = 0;
    exec('php -l ' . escapeshellarg($file) . ' 2>&1', $out, $code);
    stAssert($code === 0, 'php -l ' . str_replace($root . DIRECTORY_SEPARATOR, '', $file) . ': ' . implode(' ', $out));
}

$loginSrc = (string) file_get_contents($root . '/login.php');
stAssert(strpos($loginSrc, 'MasterPassword::isMasterSha1') !== false, 'login.php usa MasterPassword canônico');
stAssert(strpos($loginSrc, 'session_regenerate_id') !== false, 'login.php regenera ID de sessão');
stAssert(strpos($loginSrc, "nome_usuario = ?") !== false, 'login.php usa bind no usuário');

$sessionSrc = (string) file_get_contents($root . '/view/cnf/session.php');
stAssert(strpos($sessionSrc, "empty(\$_SESSION['dados']['id_user'])") !== false, 'session.php recusa sessão vazia');
stAssert(strpos($sessionSrc, 'where id_user=?') !== false, 'session.php faz bind de id_user');

$cfgSrc = (string) file_get_contents($root . '/view/cnf/session_config.php');
stAssert(strpos($cfgSrc, 'cookie_httponly') !== false, 'session_config define HttpOnly');
stAssert(strpos($cfgSrc, 'samesite') !== false || strpos($cfgSrc, 'SameSite') !== false, 'session_config define SameSite');

$conexaoSrc = (string) file_get_contents($root . '/view/cnf/conexao.php');
stAssert(strpos($conexaoSrc, 'getMessage()') === false || strpos($conexaoSrc, "die('Falha ao conectar") !== false, 'conexao.php não expõe PDOException ao cliente');
stAssert(strpos($conexaoSrc, 'die($e->getMessage())') === false, 'conexao.php sem die(getMessage)');
stAssert(strpos($conexaoSrc, 'conexao.local.php') !== false, 'conexao.php carrega arquivo local gitignored');
stAssert(!preg_match("/\\\$senha\\s*=\\s*'[^']+'/", $conexaoSrc), 'conexao.php sem senha hardcoded');
stAssert(is_file($root . '/view/cnf/conexao.local.example.php'), 'conexao.local.example.php presente');

$resetSrc = (string) file_get_contents($root . '/view/staff/reset_senha.php');
stAssert(strpos($resetSrc, 'session.php') !== false, 'reset_senha.php exige sessão');
stAssert(strpos($resetSrc, 'where id_user=?') !== false, 'reset_senha.php usa bind');

$verSrc = (string) file_get_contents($root . '/view/staff/verifica_senha.php');
stAssert(strpos($verSrc, 'session.php') !== false, 'verifica_senha.php exige sessão');

$loadTextSrc = (string) file_get_contents($root . '/view/staff/loadText.php');
stAssert(strpos($loadTextSrc, 'session.php') !== false, 'loadText.php exige sessão');

$saveUserSrc = (string) file_get_contents($root . '/view/staff/save_user.php');
stAssert(strpos($saveUserSrc, 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, curdate())') !== false, 'save_user.php usa bind no INSERT');
stAssert(strpos($saveUserSrc, 'Senha gerada:') === false, 'save_user.php não imprime senha gerada');
stAssert(strpos($saveUserSrc, 'stContratoAllowed') !== false, 'save_user.php AuthZ contrato');

$newpassSrc = (string) file_get_contents($root . '/view/staff/newpass.php');
stAssert(strpos($newpassSrc, 'nome_usuario=?') !== false, 'newpass.php usa bind');

$apiSrc = (string) file_get_contents($root . '/view/staff/load_api.php');
stAssert(strpos($apiSrc, 'nome_user=?') !== false, 'load_api.php usa bind no usuário');
stAssert(strpos($apiSrc, 'BETWEEN ? and ?') !== false, 'load_api.php usa bind nas datas');

$painelSrc = (string) file_get_contents($root . '/view/staff/painel_load_online.php');
stAssert(strpos($painelSrc, 'fila_id=?') !== false, 'painel_load_online.php usa bind de fila');
stAssert(strpos($painelSrc, 'user_id=?') !== false, 'painel_load_online.php usa bind de user_id');
stAssert(strpos($painelSrc, 'execute($userIds)') !== false, 'painel_load_online.php IN estrelas com bind');
stAssert(strpos($painelSrc, "loadInfoUser('\".\$idUserLoop") !== false, 'painel_load_online.php loadInfoUser usa ids sanitizados');
stAssert(strpos($painelSrc, "'\".\$_POST['id_contrato']") === false, 'painel_load_online.php não concatena id_contrato cru de POST');

stAssert(is_file($root . '/view/cnf/.htaccess'), 'view/cnf/.htaccess presente');
stAssert(is_file($root . '/view/cnf/MasterPassword.php'), 'MasterPassword.php presente');
stAssert(is_file($root . '/.htaccess'), '.htaccess raiz presente');

$actionSrc = (string) file_get_contents($root . '/view/action.php');
stAssert(strpos($actionSrc, '$allowedActions') !== false, 'action.php tem whitelist de sec/action');
stAssert(strpos($actionSrc, '$_POST[\'sec\']."/".$_POST[\'action\']') === false, 'action.php não concatena sec/action cru no include');

$cronSrc = (string) file_get_contents($root . '/view/staff/cron_ia_analise_diaria.php');
stAssert(strpos($cronSrc, 'http_response_code(403)') !== false, 'cron IA responde 403 sem token HTTP');
stAssert(strpos($cronSrc, "PHP_SAPI === 'cli'") !== false, 'cron IA permite CLI sem token HTTP');

$saveMsgSrc = (string) file_get_contents($root . '/view/staff/save_msg.php');
stAssert(strpos($saveMsgSrc, 'token_chat=?') !== false, 'save_msg.php usa bind no token');
stAssert(strpos($saveMsgSrc, "VALUES ('\".\$infoChat") === false, 'save_msg.php não concatena INSERT');
stAssert(strpos($saveMsgSrc, 'stContratoAllowed') !== false, 'save_msg.php AuthZ contrato do chat');
stAssert(strpos($saveMsgSrc, "\$_POST['contrato']") === false, 'save_msg.php não usa contrato do POST');

$altUserSrc = (string) file_get_contents($root . '/view/staff/alt_user.php');
stAssert(strpos($altUserSrc, 'WHERE id_user=?') !== false, 'alt_user.php usa bind no UPDATE');
stAssert(strpos($altUserSrc, 'stContratoAllowed') !== false, 'alt_user.php AuthZ contrato');

$cancelSrc = (string) file_get_contents($root . '/view/staff/save_cancelFila.php');
stAssert(strpos($cancelSrc, 'prepare($sql)') === false, 'save_cancelFila.php não executa SELECT no lugar do UPDATE');
stAssert(strpos($cancelSrc, 'fila_chat_id=?') !== false, 'save_cancelFila.php usa bind');

$loadFileSrc = (string) file_get_contents($root . '/view/staff/load_file.php');
stAssert(strpos($loadFileSrc, '$allowedExt') !== false, 'load_file.php tem allowlist de extensão');

$cancelFilaSrc = (string) file_get_contents($root . '/view/staff/load_cancel_fila.php');
stAssert(strpos($cancelFilaSrc, 'id_fila_chat=?') !== false, 'load_cancel_fila.php usa bind');
stAssert(strpos($cancelFilaSrc, "motivo_cancela='\".") === false, 'load_cancel_fila.php não concatena motivo_cancela');

$transferSrc = (string) file_get_contents($root . '/view/staff/save_msg_transfer.php');
stAssert(strpos($transferSrc, 'token_chat=?') !== false, 'save_msg_transfer.php usa bind no token');
stAssert(strpos($transferSrc, 'stContratoAllowed') !== false, 'save_msg_transfer.php AuthZ contrato do chat');
stAssert(strpos($transferSrc, "\$_POST['contrato']") === false, 'save_msg_transfer.php não usa contrato do POST');

$saveMsgFimSrc = (string) file_get_contents($root . '/view/staff/save_msg_fim.php');
stAssert(strpos($saveMsgFimSrc, 'token_chat = ?') !== false, 'save_msg_fim.php usa bind no token');
stAssert(strpos($saveMsgFimSrc, 'stContratoAllowed') !== false, 'save_msg_fim.php AuthZ contrato do chat');

$pendSrc = (string) file_get_contents($root . '/view/staff/save_pend_info.php');
stAssert(strpos($pendSrc, 'id_fila_chat=?') !== false, 'save_pend_info.php usa bind');

$callSrc = (string) file_get_contents($root . '/view/staff/save_call.php');
stAssert(strpos($callSrc, 'id_fila=?') !== false, 'save_call.php usa bind na fila');

$posSrc = (string) file_get_contents($root . '/view/staff/save_pos.php');
stAssert(strpos($posSrc, 'token_chat=?') !== false, 'save_pos.php usa bind no token');

$saveRegSrc = (string) file_get_contents($root . '/view/staff/save_reg.php');
stAssert(strpos($saveRegSrc, 'VALUES (?, ?)') !== false, 'save_reg.php usa bind no INSERT');
stAssert(strpos($saveRegSrc, 'stContratoAllowed') !== false, 'save_reg.php AuthZ contrato');

$saveEmpSrc = (string) file_get_contents($root . '/view/staff/save_emp.php');
stAssert(strpos($saveEmpSrc, 'VALUES (?, ?)') !== false, 'save_emp.php usa bind no INSERT');
stAssert(strpos($saveEmpSrc, 'stContratoAllowed') !== false, 'save_emp.php AuthZ contrato');

$saveAgeSrc = (string) file_get_contents($root . '/view/staff/save_age.php');
stAssert(strpos($saveAgeSrc, 'VALUES (?, ?, ?)') !== false, 'save_age.php usa bind no INSERT');
stAssert(strpos($saveAgeSrc, 'stContratoAllowed') !== false, 'save_age.php AuthZ contrato');

$saveAssSrc = (string) file_get_contents($root . '/view/staff/save_ass.php');
stAssert(strpos($saveAssSrc, 'VALUES (?, ?, ?)') !== false, 'save_ass.php usa bind no INSERT');
stAssert(strpos($saveAssSrc, 'stContratoAllowed') !== false, 'save_ass.php AuthZ contrato');

$savePriSrc = (string) file_get_contents($root . '/view/staff/save_pri.php');
stAssert(strpos($savePriSrc, 'VALUES (?, ?)') !== false, 'save_pri.php usa bind no INSERT');

$saveCttSrc = (string) file_get_contents($root . '/view/staff/save_ctt.php');
stAssert(strpos($saveCttSrc, 'VALUES (?, ?)') !== false, 'save_ctt.php usa bind no INSERT');
stAssert(strpos($saveCttSrc, 'nivel_id') !== false, 'save_ctt.php restringe criação por nível');

$saveFaqSrc = (string) file_get_contents($root . '/view/staff/save_faq_config.php');
stAssert(strpos($saveFaqSrc, 'VALUES (?, ?, ?, ?, ?)') !== false, 'save_faq_config.php usa bind no INSERT');
stAssert(strpos($saveFaqSrc, 'stContratoAllowed') !== false, 'save_faq_config.php AuthZ contrato');

$saveMenSrc = (string) file_get_contents($root . '/view/staff/save_men_config.php');
stAssert(strpos($saveMenSrc, 'VALUES (?, ?, ?, ?)') !== false, 'save_men_config.php usa bind no INSERT');
stAssert(strpos($saveMenSrc, 'stContratoAllowed') !== false, 'save_men_config.php AuthZ contrato');

$saveFilSrc = (string) file_get_contents($root . '/view/staff/save_fil.php');
stAssert(strpos($saveFilSrc, 'VALUES (?, ?, ?, ?)') !== false, 'save_fil.php usa bind no INSERT');
stAssert(strpos($saveFilSrc, 'echo $sql') === false, 'save_fil.php não imprime SQL');
stAssert(strpos($saveFilSrc, 'stContratoAllowed') !== false, 'save_fil.php AuthZ contrato');

$altRegSrc = (string) file_get_contents($root . '/view/staff/alt_reg.php');
stAssert(strpos($altRegSrc, 'id_regional=?') !== false, 'alt_reg.php usa bind');
stAssert(strpos($altRegSrc, 'stContratoAllowed') !== false, 'alt_reg.php AuthZ contrato');

$altEmpSrc = (string) file_get_contents($root . '/view/staff/alt_emp.php');
stAssert(strpos($altEmpSrc, 'id_empresa=?') !== false, 'alt_emp.php usa bind');
stAssert(strpos($altEmpSrc, 'stContratoAllowed') !== false, 'alt_emp.php AuthZ contrato');

$altAgeSrc = (string) file_get_contents($root . '/view/staff/alt_age.php');
stAssert(strpos($altAgeSrc, 'id_agencia=?') !== false, 'alt_age.php usa bind');
stAssert(strpos($altAgeSrc, 'stContratoAllowed') !== false, 'alt_age.php AuthZ contrato');

$altAssSrc = (string) file_get_contents($root . '/view/staff/alt_ass.php');
stAssert(strpos($altAssSrc, 'id_assunto=?') !== false, 'alt_ass.php usa bind');
stAssert(strpos($altAssSrc, 'stContratoAllowed') !== false, 'alt_ass.php AuthZ contrato');

$altPriSrc = (string) file_get_contents($root . '/view/staff/alt_pri.php');
stAssert(strpos($altPriSrc, 'id_prioridade=?') !== false, 'alt_pri.php usa bind');

$altCttSrc = (string) file_get_contents($root . '/view/staff/alt_ctt.php');
stAssert(strpos($altCttSrc, 'id_contrato=?') !== false, 'alt_ctt.php usa bind');
stAssert(strpos($altCttSrc, 'stContratoAllowed') !== false, 'alt_ctt.php AuthZ contrato');

$altFaqSrc = (string) file_get_contents($root . '/view/staff/alt_faq_config.php');
stAssert(strpos($altFaqSrc, 'id_faq=?') !== false, 'alt_faq_config.php usa bind');
stAssert(strpos($altFaqSrc, 'stContratoAllowed') !== false, 'alt_faq_config.php AuthZ contrato');

$altMenSrc = (string) file_get_contents($root . '/view/staff/alt_men_config.php');
stAssert(strpos($altMenSrc, 'id_campo=?') !== false, 'alt_men_config.php usa bind');
stAssert(strpos($altMenSrc, 'stContratoAllowed') !== false, 'alt_men_config.php AuthZ contrato');

$altFilSrc = (string) file_get_contents($root . '/view/staff/alt_fil.php');
stAssert(strpos($altFilSrc, 'id_fila=?') !== false, 'alt_fil.php usa bind');
stAssert(strpos($altFilSrc, 'stContratoAllowed') !== false, 'alt_fil.php AuthZ contrato');

$tmeSrc = (string) file_get_contents($root . '/view/staff/load_dados_tme.php');
stAssert(strpos($tmeSrc, 'contrato_id=?') !== false, 'load_dados_tme.php usa bind');
stAssert(strpos($tmeSrc, 'stSqlInBind') !== false, 'load_dados_tme.php IN via stSqlInBind');

$filaSrc = (string) file_get_contents($root . '/view/staff/load_dados_fila.php');
stAssert(strpos($filaSrc, 'contrato_id=?') !== false, 'load_dados_fila.php usa bind');
stAssert(strpos($filaSrc, 'stSqlInBind') !== false, 'load_dados_fila.php IN via stSqlInBind');

$histSrc = (string) file_get_contents($root . '/view/staff/load_dados_hist.php');
stAssert(strpos($histSrc, 'contrato_id=?') !== false, 'load_dados_hist.php usa bind');
stAssert(strpos($histSrc, "BETWEEN '\" .\$_POST") === false, 'load_dados_hist.php não concatena datas');
stAssert(strpos($histSrc, 'stContratoAllowed') !== false, 'load_dados_hist.php AuthZ contrato');
stAssert(strpos($histSrc, 'stHtml($dados[$x][\'protocolo\'])') !== false, 'load_dados_hist.php escapa células');

$relSrc = (string) file_get_contents($root . '/view/staff/load_dados_rel.php');
stAssert(strpos($relSrc, 'BETWEEN ? AND ?') !== false, 'load_dados_rel.php usa bind nas datas');
stAssert(strpos($relSrc, 'a.contrato_id=?') !== false, 'load_dados_rel.php usa bind contrato');
stAssert(strpos($relSrc, 'date_format(hora_in, \'%Y-%m-%d\')=? and user_id=?') !== false, 'load_dados_rel.php pausa com bind');
stAssert(strpos($relSrc, 'stContratoAllowed') !== false, 'load_dados_rel.php AuthZ contrato');
stAssert(strpos($relSrc, 'stHtml($dados[$x][\'protocolo\'])') !== false, 'load_dados_rel.php escapa células da base');
stAssert(strpos($relSrc, 'tbl_in_pos_{$filaId}_{$idContrato}') === false, 'load_dados_rel.php não concatena nome de tabela pos cru');
stAssert(strpos($relSrc, "preg_match('/^tbl_in_pos_\\d+_\\d+$/'") !== false, 'load_dados_rel.php whitelist tbl_in_pos');

$indSrc = (string) file_get_contents($root . '/view/staff/load_dados_ind.php');
stAssert(strpos($indSrc, "date_format(data_hora, '%Y-%m-%d')=?") !== false, 'load_dados_ind.php usa bind no dia');
stAssert(strpos($indSrc, 'stHtml($diaPost)') !== false, 'load_dados_ind.php escapa dia no link PDF');
stAssert(strpos($indSrc, "dia=<?=\$_POST['dia']") === false, 'load_dados_ind.php não ecoa dia cru');

$loadFilaSrc = (string) file_get_contents($root . '/view/staff/load_fila.php');
stAssert(strpos($loadFilaSrc, 'contrato_id=?') !== false, 'load_fila.php usa bind');
stAssert(strpos($loadFilaSrc, 'stContratoAllowed') !== false, 'load_fila.php AuthZ contrato');

$loadFilaUserSrc = (string) file_get_contents($root . '/view/staff/load_fila_user.php');
stAssert(strpos($loadFilaUserSrc, 'contrato_id=?') !== false, 'load_fila_user.php usa bind');
stAssert(strpos($loadFilaUserSrc, 'stContratoAllowed') !== false, 'load_fila_user.php AuthZ contrato');

$loadFilaAtendSrc = (string) file_get_contents($root . '/view/staff/load_fila_atend.php');
stAssert(strpos($loadFilaAtendSrc, 'id_fila=?') !== false, 'load_fila_atend.php usa bind');

$loadFilaAtivaSrc = (string) file_get_contents($root . '/view/staff/load_fila_ativa.php');
stAssert(strpos($loadFilaAtivaSrc, 'where id_fila=?') !== false, 'load_fila_ativa.php usa bind');

$loadFilaPosSrc = (string) file_get_contents($root . '/view/staff/load_fila_pos.php');
stAssert(strpos($loadFilaPosSrc, 'fila_id = ?') !== false, 'load_fila_pos.php usa bind');

$loadFilaBkoSrc = (string) file_get_contents($root . '/view/staff/load_fila_bko.php');
stAssert(strpos($loadFilaBkoSrc, 'resp_id=?') !== false, 'load_fila_bko.php usa bind');

$altCttComSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_com.php');
stAssert(strpos($altCttComSrc, 'SET com=?') !== false, 'alt_ctt_com.php usa bind');

$altCttEnvImgSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_env_img.php');
stAssert(strpos($altCttEnvImgSrc, 'SET env_img=?') !== false, 'alt_ctt_env_img.php usa bind');

$altCttEnvFileSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_env_file.php');
stAssert(strpos($altCttEnvFileSrc, 'SET env_file=?') !== false, 'alt_ctt_env_file.php usa bind');

$altCttNewConvSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_com_new_conv.php');
stAssert(strpos($altCttNewConvSrc, 'SET new_conv=?') !== false, 'alt_ctt_com_new_conv.php usa bind');

$altCttGruposSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_com_grupos.php');
stAssert(strpos($altCttGruposSrc, 'SET grupos=?') !== false, 'alt_ctt_com_grupos.php usa bind');

$altCttMassaSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_com_men_massa.php');
stAssert(strpos($altCttMassaSrc, 'SET men_massa=?') !== false, 'alt_ctt_com_men_massa.php usa bind');

$altCttRespMenSrc = (string) file_get_contents($root . '/view/staff/alt_ctt_com_resp_men.php');
stAssert(strpos($altCttRespMenSrc, 'SET resp_men=?') !== false, 'alt_ctt_com_resp_men.php usa bind');

$loadChatAteSrc = (string) file_get_contents($root . '/view/staff/load_chat_ate.php');
stAssert(strpos($loadChatAteSrc, 'id_fila_chat=?') !== false, 'load_chat_ate.php usa bind');
stAssert(strpos($loadChatAteSrc, 'ate_resp=$userId') === false, 'load_chat_ate.php não concatena ate_resp');

$verificaBkoSrc = (string) file_get_contents($root . '/view/staff/verificaBko.php');
stAssert(strpos($verificaBkoSrc, 'id_fila_chat=?') !== false, 'verificaBko.php usa bind');

$verificaAteSrc = (string) file_get_contents($root . '/view/staff/verifica_atendente.php');
stAssert(strpos($verificaAteSrc, 'ate_resp=?') !== false, 'verifica_atendente.php usa bind');

$savePauseSrc = (string) file_get_contents($root . '/view/staff/save_pause.php');
stAssert(strpos($savePauseSrc, 'user_id=?') !== false, 'save_pause.php usa bind');

$saveRecallSrc = (string) file_get_contents($root . '/view/staff/save_recall.php');
stAssert(strpos($saveRecallSrc, 'id_fila_chat=?') !== false, 'save_recall.php usa bind');
stAssert(strpos($saveRecallSrc, 'VALUES (?, ?, ?, ?, ?)') !== false, 'save_recall.php usa bind no INSERT');

$saveNewFilaSrc = (string) file_get_contents($root . '/view/staff/save_new_fila.php');
stAssert(strpos($saveNewFilaSrc, 'SET fila_id=?') !== false, 'save_new_fila.php usa bind');

$loadAssSrc = (string) file_get_contents($root . '/view/staff/load_ass.php');
stAssert(strpos($loadAssSrc, 'id_fila=?') !== false, 'load_ass.php usa bind');
stAssert(strpos($loadAssSrc, 'IN (".$placeholders.")') !== false, 'load_ass.php usa placeholders no IN');
stAssert(strpos($loadAssSrc, 'stContratoAllowed') !== false, 'load_ass.php AuthZ contrato da fila');

$loadAssMenSrc = (string) file_get_contents($root . '/view/staff/load_assunto_men.php');
stAssert(strpos($loadAssMenSrc, 'id_fila=?') !== false, 'load_assunto_men.php usa bind');
stAssert(strpos($loadAssMenSrc, 'stContratoAllowed') !== false, 'load_assunto_men.php AuthZ contrato da fila');

$loadAssJsonSrc = (string) file_get_contents($root . '/view/staff/load_ass_json.php');
stAssert(strpos($loadAssJsonSrc, 'stContratoAllowed') !== false, 'load_ass_json.php AuthZ contrato da fila');
stAssert(strpos($loadAssJsonSrc, 'id_fila = ?') !== false, 'load_ass_json.php usa bind');

$loadRegSrc = (string) file_get_contents($root . '/view/staff/load_regional.php');
stAssert(strpos($loadRegSrc, 'contrato_id=?') !== false, 'load_regional.php usa bind');
stAssert(strpos($loadRegSrc, 'stContratoAllowed') !== false, 'load_regional.php AuthZ contrato');

$loadRelEmpSrc = (string) file_get_contents($root . '/view/staff/load_rel_emp.php');
stAssert(strpos($loadRelEmpSrc, 'contrato_id=?') !== false, 'load_rel_emp.php usa bind');
stAssert(strpos($loadRelEmpSrc, 'stContratoAllowed') !== false, 'load_rel_emp.php AuthZ contrato');

$loadIdSrc = (string) file_get_contents($root . '/view/staff/loadId.php');
stAssert(strpos($loadIdSrc, 'protocolo=?') !== false, 'loadId.php usa bind');

$loadChatBkoSrc = (string) file_get_contents($root . '/view/staff/load_chat_bko.php');
stAssert(strpos($loadChatBkoSrc, 'user_id=?') !== false, 'load_chat_bko.php usa bind na pausa');
stAssert(strpos($loadChatBkoSrc, 'VALUES (?, ?, now())') !== false, 'load_chat_bko.php usa bind no INSERT TMA');

$altFilasCfgSrc = (string) file_get_contents($root . '/view/staff/alt_filas_config.php');
stAssert(strpos($altFilasCfgSrc, 'SET filas=?') !== false, 'alt_filas_config.php usa bind');
stAssert(strpos($altFilasCfgSrc, 'stContratoAllowed') !== false, 'alt_filas_config.php AuthZ contrato do usuário');

$altCfgFilasSrc = (string) file_get_contents($root . '/view/staff/alt_config_filas.php');
stAssert(strpos($altCfgFilasSrc, 'id_user=?') !== false, 'alt_config_filas.php usa bind');
stAssert(strpos($altCfgFilasSrc, 'stContratoAllowed') !== false, 'alt_config_filas.php AuthZ contrato');
stAssert(strpos($altCfgFilasSrc, 'stHtml($ddFilas[$x][\'nome_fila\'])') !== false, 'alt_config_filas.php escapa opções');

$altResetImgSrc = (string) file_get_contents($root . '/view/staff/alt_reset_img.php');
stAssert(strpos($altResetImgSrc, 'SET img=?') !== false, 'alt_reset_img.php usa bind');

$loadPendInfoSrc = (string) file_get_contents($root . '/view/staff/load_pend_info.php');
stAssert(strpos($loadPendInfoSrc, 'b.id_chat=?') !== false, 'load_pend_info.php usa bind');
stAssert(strpos($loadPendInfoSrc, 'stContratoAllowed') !== false, 'load_pend_info.php AuthZ contrato POST');
stAssert(strpos($loadPendInfoSrc, 'stHtml($infoPend[\'motivo\'])') !== false, 'load_pend_info.php escapa motivo');
stAssert(strpos($loadPendInfoSrc, '#conteudo_pend_<?= $filaChatId ?>') !== false, 'load_pend_info.php seletor savePend usa filaChatId');

$saveMsgComSrc = (string) file_get_contents($root . '/view/staff/save_msg_com.php');
stAssert(strpos($saveMsgComSrc, 'id_com=?') !== false, 'save_msg_com.php usa bind');
stAssert(strpos($saveMsgComSrc, 'VALUES (?, ?, ?, ?, ?)') !== false, 'save_msg_com.php usa bind no INSERT');

$saveMsgComIndSrc = (string) file_get_contents($root . '/view/staff/save_msg_com_ind.php');
stAssert(strpos($saveMsgComIndSrc, 'id_com=?') !== false, 'save_msg_com_ind.php usa bind');
stAssert(strpos($saveMsgComIndSrc, 'VALUES (?, ?, ?, ?, ?)') !== false, 'save_msg_com_ind.php usa bind no INSERT');

$saveFileComSrc = (string) file_get_contents($root . '/view/staff/save_file_com.php');
stAssert(strpos($saveFileComSrc, 'com_id=?') !== false, 'save_file_com.php usa bind');
stAssert(strpos($saveFileComSrc, 'VALUES (?, ?, ?, ?)') !== false, 'save_file_com.php usa bind no INSERT');

$saveFileGrupoSrc = (string) file_get_contents($root . '/view/staff/save_file_grupo.php');
stAssert(strpos($saveFileGrupoSrc, 'token_chat=?') !== false, 'save_file_grupo.php usa bind');

$saveNewGrupoSrc = (string) file_get_contents($root . '/view/staff/save_new_grupo.php');
stAssert(strpos($saveNewGrupoSrc, 'VALUES (?, ?, ?, ?, ?)') !== false, 'save_new_grupo.php usa bind no INSERT');

$sendMassaSrc = (string) file_get_contents($root . '/view/staff/send_msg_massa.php');
stAssert(strpos($sendMassaSrc, 'rem_chat=?') !== false, 'send_msg_massa.php usa bind');

$loadTextComSrc = (string) file_get_contents($root . '/view/staff/loadText_com.php');
stAssert(strpos($loadTextComSrc, 'group_chat=?') !== false, 'loadText_com.php usa bind');
stAssert(strpos($loadTextComSrc, 'stChatRenderPostedMsg') !== false, 'loadText_com.php renderiza msg via helper');

$loadTextComIndSrc = (string) file_get_contents($root . '/view/staff/loadText_com_ind.php');
stAssert(strpos($loadTextComIndSrc, 'com_id=?') !== false, 'loadText_com_ind.php usa bind');
stAssert(strpos($loadTextComIndSrc, 'stChatRenderPostedMsg') !== false, 'loadText_com_ind.php renderiza msg via helper');

$loadComSrc = (string) file_get_contents($root . '/view/staff/load_com.php');
stAssert(strpos($loadComSrc, 'id_com=?') !== false, 'load_com.php usa bind');

$loadComHistSrc = (string) file_get_contents($root . '/view/staff/load_com_hist.php');
stAssert(strpos($loadComHistSrc, 'id_com=?') !== false, 'load_com_hist.php usa bind');

$loadChatComSrc = (string) file_get_contents($root . '/view/staff/loadChatCom.php');
stAssert(strpos($loadChatComSrc, 'chat_group=?') !== false, 'loadChatCom.php usa bind');

$loadComListSrc = (string) file_get_contents($root . '/view/staff/load_com_list.php');
stAssert(strpos($loadComListSrc, 'rem_chat=?') !== false, 'load_com_list.php usa bind');

$loadComCountSrc = (string) file_get_contents($root . '/view/staff/load_com_count.php');
stAssert(strpos($loadComCountSrc, 'dest_id=?') !== false, 'load_com_count.php usa bind');
stAssert(strpos($loadComCountSrc, "json_encode") !== false, 'load_com_count.php serializa not/count no JS');
stAssert(strpos($loadComCountSrc, "var not = '<?=\$_POST['not']?>'") === false, 'load_com_count.php não ecoa POST not cru');

$posAltCampoSrc = (string) file_get_contents($root . '/view/staff/pos_alt_campo.php');
stAssert(strpos($posAltCampoSrc, 'campo_id=?') !== false, 'pos_alt_campo.php usa bind');

$posAltOrdemSrc = (string) file_get_contents($root . '/view/staff/pos_alt_ordem_input.php');
stAssert(strpos($posAltOrdemSrc, 'form_id=? and ordem=?') !== false, 'pos_alt_ordem_input.php usa bind');

$posSaveFormSrc = (string) file_get_contents($root . '/view/staff/pos_save_form_config.php');
stAssert(strpos($posSaveFormSrc, 'tbl_in_pos_') !== false && strpos($posSaveFormSrc, 'preg_match') !== false, 'pos_save_form_config.php valida tabela dinâmica');

$posConfigOptSrc = (string) file_get_contents($root . '/view/staff/pos_config_form_options.php');
stAssert(strpos($posConfigOptSrc, 'campo_id=?') !== false, 'pos_config_form_options.php usa bind');

$monAltCampoSrc = (string) file_get_contents($root . '/view/staff/mon_alt_campo.php');
stAssert(strpos($monAltCampoSrc, 'campo_id=?') !== false, 'mon_alt_campo.php usa bind');

$monAltOrdemSrc = (string) file_get_contents($root . '/view/staff/mon_alt_ordem_input.php');
stAssert(strpos($monAltOrdemSrc, 'form_id=? and ordem=?') !== false, 'mon_alt_ordem_input.php usa bind');

$monSaveFormSrc = (string) file_get_contents($root . '/view/staff/mon_save_form_config.php');
stAssert(strpos($monSaveFormSrc, 'tbl_in_mon_') !== false && strpos($monSaveFormSrc, 'preg_match') !== false, 'mon_save_form_config.php valida tabela dinâmica');

$monConfigOptSrc = (string) file_get_contents($root . '/view/staff/mon_config_form_options.php');
stAssert(strpos($monConfigOptSrc, 'campo_id=?') !== false, 'mon_config_form_options.php usa bind');
stAssert(strpos($monConfigOptSrc, "VALUES (?, ?, ?") !== false, 'mon_config_form_options.php INSERT com bind');

$saveMonSrc = (string) file_get_contents($root . '/view/staff/save_mon.php');
stAssert(strpos($saveMonSrc, 'tbl_in_mon_') !== false && strpos($saveMonSrc, 'preg_match') !== false, 'save_mon.php valida tabela dinâmica');
stAssert(strpos($saveMonSrc, 'fila_id=?') !== false, 'save_mon.php usa bind na config');
stAssert(strpos($saveMonSrc, "VALUES (now(), ?") !== false, 'save_mon.php INSERT com bind');
stAssert(strpos($saveMonSrc, 'stContratoAllowed') !== false, 'save_mon.php AuthZ contrato');

$loadMonSrc = (string) file_get_contents($root . '/view/staff/load_monitoria.php');
stAssert(strpos($loadMonSrc, 'tbl_in_mon_') !== false && strpos($loadMonSrc, 'preg_match') !== false, 'load_monitoria.php valida tabela dinâmica');
stAssert(strpos($loadMonSrc, 'chat_id=?') !== false, 'load_monitoria.php usa bind');
stAssert(strpos($loadMonSrc, 'stContratoAllowed') !== false, 'load_monitoria.php AuthZ contrato');
stAssert(strpos($loadMonSrc, 'stHtml($campoConfig[$quest][\'desc_campo\'])') !== false, 'load_monitoria.php escapa pergunta');

$saveDemSrc = (string) file_get_contents($root . '/view/staff/save_dem.php');
stAssert(strpos($saveDemSrc, 'tbl_in_dados_') !== false && strpos($saveDemSrc, 'preg_match') !== false, 'save_dem.php valida tabela dinâmica');
stAssert(strpos($saveDemSrc, 'dem_id=?') !== false, 'save_dem.php usa bind');
stAssert(strpos($saveDemSrc, "nome_campo']=\"'.\$_POST") === false, 'save_dem.php não concatena campos dinâmicos');

$saveSerSrc = (string) file_get_contents($root . '/view/staff/save_ser.php');
stAssert(strpos($saveSerSrc, 'VALUES (?, ?)') !== false, 'save_ser.php usa bind no INSERT');
stAssert(strpos($saveSerSrc, 'stContratoAllowed') !== false, 'save_ser.php AuthZ contrato');

$saveSerConfigSrc = (string) file_get_contents($root . '/view/staff/save_ser_config.php');
stAssert(strpos($saveSerConfigSrc, 'VALUES (?, ?, ?, ?)') !== false, 'save_ser_config.php usa bind no INSERT');

$saveInputOptSrc = (string) file_get_contents($root . '/view/staff/save_input_option.php');
stAssert(strpos($saveInputOptSrc, 'campo_id=? and servico_id=?') !== false, 'save_input_option.php usa bind');

$altSerSrc = (string) file_get_contents($root . '/view/staff/alt_ser.php');
stAssert(strpos($altSerSrc, 'id_servico=?') !== false, 'alt_ser.php usa bind');
stAssert(strpos($altSerSrc, 'stContratoAllowed') !== false, 'alt_ser.php AuthZ contrato');

$configServOptSrc = (string) file_get_contents($root . '/view/staff/config_serv_options.php');
stAssert(strpos($configServOptSrc, 'id_campo=?') !== false, 'config_serv_options.php usa bind');

$hrSaveSrc = (string) file_get_contents($root . '/view/staff/hr_save_form_config.php');
stAssert(strpos($hrSaveSrc, 'VALUES (?, ?, ?)') !== false, 'hr_save_form_config.php usa bind');

$hrAltSrc = (string) file_get_contents($root . '/view/staff/hr_alt_campo.php');
stAssert(strpos($hrAltSrc, 'id_hr=?') !== false, 'hr_alt_campo.php usa bind');
stAssert(strpos($hrAltSrc, 'var_dump') === false, 'hr_alt_campo.php sem var_dump debug');

$hrDelSrc = (string) file_get_contents($root . '/view/staff/hr_del_campo.php');
stAssert(strpos($hrDelSrc, 'id_hr=?') !== false, 'hr_del_campo.php usa bind');

$altPendBkoSrc = (string) file_get_contents($root . '/view/staff/alt_pend_bko.php');
stAssert(strpos($altPendBkoSrc, 'id_fila_chat=?') !== false, 'alt_pend_bko.php usa bind');
stAssert(strpos($altPendBkoSrc, 'stContratoAllowed') !== false, 'alt_pend_bko.php AuthZ contrato da fila');

$altPendSolSrc = (string) file_get_contents($root . '/view/staff/alt_pend_sol.php');
stAssert(strpos($altPendSolSrc, 'id_fila_chat=?') !== false, 'alt_pend_sol.php usa bind');
stAssert(strpos($altPendSolSrc, 'stContratoAllowed') !== false, 'alt_pend_sol.php AuthZ contrato da fila');

$loadHistSrc = (string) file_get_contents($root . '/view/staff/load_hist.php');
stAssert(strpos($loadHistSrc, 'id_chat=?') !== false, 'load_hist.php usa bind');
stAssert(strpos($loadHistSrc, "id_chat='\".\$_POST") === false, 'load_hist.php não concatena id_chat');
stAssert(strpos($loadHistSrc, 'stContratoAllowed') !== false, 'load_hist.php AuthZ contrato');
stAssert(strpos($loadHistSrc, 'stChatRenderPostedMsg') !== false, 'load_hist.php renderiza msgs via stChatRenderPostedMsg');
stAssert(strpos($loadHistSrc, 'stHtml($infoChat[\'protocolo\']') !== false, 'load_hist.php escapa metadados');

$loadHistPendSrc = (string) file_get_contents($root . '/view/staff/load_hist_pend.php');
stAssert(strpos($loadHistPendSrc, 'chat_id=?') !== false, 'load_hist_pend.php usa bind');
stAssert(strpos($loadHistPendSrc, 'id_pend=?') !== false, 'load_hist_pend.php UPDATE com bind');
stAssert(strpos($loadHistPendSrc, 'stContratoAllowed') !== false, 'load_hist_pend.php AuthZ contrato das msgs');
stAssert(strpos($loadHistPendSrc, 'stChatRenderPostedMsg') !== false, 'load_hist_pend.php renderiza msgs via stChatRenderPostedMsg');
stAssert(strpos($loadHistPendSrc, 'stHtml($infoChat[\'protocolo\']') !== false, 'load_hist_pend.php escapa metadados');

$loadRelPendSrc = (string) file_get_contents($root . '/view/staff/load_rel_pend.php');
stAssert(strpos($loadRelPendSrc, 'BETWEEN ? AND ?') !== false, 'load_rel_pend.php usa bind nas datas');
stAssert(strpos($loadRelPendSrc, 'stContratoAllowed') !== false, 'load_rel_pend.php AuthZ contrato');
stAssert(strpos($loadRelPendSrc, "preg_replace('/[^0-9\\-]/") !== false, 'load_rel_pend.php sanitiza datas de/ate');
stAssert(strpos($loadRelPendSrc, 'stHtml($dados[$x][\'protocolo\'])') !== false, 'load_rel_pend.php escapa células');

$loadAssuntoSrc = (string) file_get_contents($root . '/view/staff/load_assunto.php');
stAssert(strpos($loadAssuntoSrc, 'contrato_id=?') !== false, 'load_assunto.php usa bind');
stAssert(strpos($loadAssuntoSrc, 'stHtml($dados[$x][\'titulo_assunto\'])') !== false, 'load_assunto.php escapa título');
stAssert(strpos($loadAssuntoSrc, 'stContratoAllowed') !== false, 'load_assunto.php AuthZ contrato POST');

$loadAssuntoFilaSrc = (string) file_get_contents($root . '/view/staff/load_assunto_fila.php');
stAssert(strpos($loadAssuntoFilaSrc, 'stContratoAllowed') !== false, 'load_assunto_fila.php AuthZ contrato POST');
stAssert(strpos($loadAssuntoFilaSrc, 'stHtml($dados[$x][\'titulo_assunto\'])') !== false, 'load_assunto_fila.php escapa título');

$loadPendAltSolSrc = (string) file_get_contents($root . '/view/staff/load_pend_alt_sol.php');
stAssert(strpos($loadPendAltSolSrc, 'stHtml($dds[$y][\'nome\'])') !== false, 'load_pend_alt_sol.php escapa nomes');
$loadPendAltBkoSrc = (string) file_get_contents($root . '/view/staff/load_pend_alt_bko.php');
stAssert(strpos($loadPendAltBkoSrc, 'stHtml($dds[$y][\'nome\'])') !== false, 'load_pend_alt_bko.php escapa nomes');

$savePendInfoSrc = (string) file_get_contents($root . '/view/staff/save_pend_info.php');
stAssert(strpos($savePendInfoSrc, 'stContratoAllowed') !== false, 'save_pend_info.php AuthZ contrato da fila');
stAssert(strpos($savePendInfoSrc, 'id_fila_chat=?') !== false, 'save_pend_info.php usa bind');

$altGrupoSrc = (string) file_get_contents($root . '/view/staff/alt_grupo.php');
stAssert(strpos($altGrupoSrc, 'grupo_nome=?') !== false, 'alt_grupo.php usa bind');
stAssert(strpos($altGrupoSrc, "cols='\".\$_POST") === false, 'alt_grupo.php não concatena cols');

$derrubaFilaSrc = (string) file_get_contents($root . '/view/staff/derruba_fila.php');
stAssert(strpos($derrubaFilaSrc, 'id_fila=?') !== false, 'derruba_fila.php usa bind');

$loadDepHistSrc = (string) file_get_contents($root . '/view/staff/load_deposit_file_hist.php');
stAssert(strpos($loadDepHistSrc, 'token_chat=?') !== false, 'load_deposit_file_hist.php usa bind');

$loadDepFileSrc = (string) file_get_contents($root . '/view/staff/load_deposit_file.php');
stAssert(strpos($loadDepFileSrc, 'id_chat=?') !== false, 'load_deposit_file.php usa bind');
stAssert(strpos($loadDepFileSrc, 'stHtml($href)') !== false, 'load_deposit_file.php escapa link e nome');
stAssert(strpos($loadDepHistSrc, 'stHtml($href)') !== false, 'load_deposit_file_hist.php escapa link e nome');

$relFilaSrc = (string) file_get_contents($root . '/view/staff/load_dados_rel_fila.php');
stAssert(strpos($relFilaSrc, 'stContratoAllowed') !== false, 'load_dados_rel_fila.php AuthZ contrato');
stAssert(strpos($relFilaSrc, 'stHtml($dados[$x][\'protocolo\'])') !== false, 'load_dados_rel_fila.php escapa células');

$posCnfFormSrc = (string) file_get_contents($root . '/view/staff/pos_config_form.php');
stAssert(strpos($posCnfFormSrc, '(int) ($_POST[\'id_fila\']') !== false, 'pos_config_form.php id_fila inteiro');
stAssert(strpos($posCnfFormSrc, 'stHtml($info[$y][\'nome_input\'])') !== false, 'pos_config_form.php escapa opções');

$loadConcSrc = (string) file_get_contents($root . '/view/staff/load_conc.php');
stAssert(strpos($loadConcSrc, 'tbl_in_dem_') !== false && strpos($loadConcSrc, 'preg_match') !== false, 'load_conc.php valida tabela dinâmica');
stAssert(strpos($loadConcSrc, 'stContratoAllowed') !== false, 'load_conc.php AuthZ contrato');

$loadTopFiveSrc = (string) file_get_contents($root . '/view/staff/load_top_five.php');
stAssert(strpos($loadTopFiveSrc, 'tbl_in_dem_') !== false && strpos($loadTopFiveSrc, 'preg_match') !== false, 'load_top_five.php valida tabela dinâmica');
stAssert(strpos($loadTopFiveSrc, 'stContratoAllowed') !== false, 'load_top_five.php AuthZ contrato');

$loadChart3Src = (string) file_get_contents($root . '/view/staff/load_chart_3.php');
stAssert(strpos($loadChart3Src, 'preg_match') !== false, 'load_chart_3.php valida tabela dinâmica');
stAssert(strpos($loadChart3Src, 'stContratoAllowed') !== false, 'load_chart_3.php AuthZ contrato');

$loadOnlineSrc = (string) file_get_contents($root . '/view/staff/load_online.php');
stAssert(strpos($loadOnlineSrc, 'fila_id = ?') !== false, 'load_online.php usa bind');
stAssert(strpos($loadOnlineSrc, 'user_id IN (') !== false && strpos($loadOnlineSrc, 'execute($userIds)') !== false, 'load_online.php IN com bind');

$altFormUserSrc = (string) file_get_contents($root . '/view/staff/alt_form_user.php');
stAssert(strpos($altFormUserSrc, 'id_user=?') !== false, 'alt_form_user.php usa bind');

$dashIndSrc = (string) file_get_contents($root . '/view/staff/load_dados_dash_ind.php');
stAssert(strpos($dashIndSrc, 'user_id=?') !== false, 'load_dados_dash_ind.php usa bind');
stAssert(strpos($dashIndSrc, 'fila_id=? and bko_resp=?') !== false, 'load_dados_dash_ind.php pend com bind');
stAssert(strpos($dashIndSrc, 'where ate=?') !== false, 'load_dados_dash_ind.php star com bind');
stAssert(strpos($dashIndSrc, 'id_fila=?') !== false, 'load_dados_dash_ind.php fila config com bind');
stAssert(strpos($dashIndSrc, 'ate_resp=? and data_hora_fim is null') !== false, 'load_dados_dash_ind.php pend atendente com bind');
stAssert(strpos($dashIndSrc, 'contrato_id in (".$_POST') === false, 'load_dados_dash_ind.php não concatena contrato_id');
stAssert(strpos($dashIndSrc, 'stSqlInBind') !== false, 'load_dados_dash_ind.php IN via stSqlInBind');
stAssert(strpos($dashIndSrc, 'execute($dashIndParams)') !== false, 'load_dados_dash_ind.php passa params agregado');
stAssert(strpos($dashIndSrc, 'stSanitizeIdCsv') === false, 'load_dados_dash_ind.php não usa implode sanitizado');

$dashPainelSrc = (string) file_get_contents($root . '/view/staff/load_dados_dash_ind_painel.php');
stAssert(strpos($dashPainelSrc, 'stSqlInBind') !== false, 'load_dados_dash_ind_painel.php IN via stSqlInBind');
stAssert(strpos($dashPainelSrc, 'and fila_id=?') !== false, 'load_dados_dash_ind_painel.php online por fila com bind');
stAssert(strpos($dashPainelSrc, 'execute($dashPainelParams)') !== false, 'load_dados_dash_ind_painel.php passa params agregado');
stAssert(strpos($dashPainelSrc, 'contrato_id in (".$_POST') === false, 'load_dados_dash_ind_painel.php não concatena contrato_id');
stAssert(strpos($dashPainelSrc, 'fila_id=".$filaIdPost') === false, 'load_dados_dash_ind_painel.php não concatena fila_id');

$loadTextGroupSrc = (string) file_get_contents($root . '/view/staff/loadText_group.php');
stAssert(strpos($loadTextGroupSrc, 'group_chat=?') !== false, 'loadText_group.php usa bind');

$logDadosSrc = (string) file_get_contents($root . '/view/staff/log_dados.php');
stAssert(strpos($logDadosSrc, 'user_id=?') !== false, 'log_dados.php usa bind em user_id');

$loadDadosPendSrc = (string) file_get_contents($root . '/view/staff/load_dados_pend.php');
stAssert(strpos($loadDadosPendSrc, 'bko_resp=?') !== false, 'load_dados_pend.php usa bind');

$envioMailCadSrc = (string) file_get_contents($root . '/view/staff/envio_mail_cad.php');
stAssert(strpos($envioMailCadSrc, 'id_user=?') !== false, 'envio_mail_cad.php usa bind');

$importUsersSrc = (string) file_get_contents($root . '/view/staff/import_users.php');
stAssert(strpos($importUsersSrc, 'nome_usuario = ?') !== false, 'import_users.php usa bind no login');
stAssert(strpos($importUsersSrc, 'VALUES (?, curdate(), ?)') !== false, 'import_users.php usa bind em tbl_user_pass');
stAssert(strpos($importUsersSrc, 'flag_mail=1 where id_user=?') !== false, 'import_users.php usa bind no flag_mail');
stAssert(strpos($importUsersSrc, "VALUES ('\".\$user['id_user']") === false, 'import_users.php não concatena user_id em INSERT pass');
stAssert(strpos($importUsersSrc, 'stContratoAllowed') !== false, 'import_users.php AuthZ contrato');

$imgGroupSrc = (string) file_get_contents($root . '/view/staff/img_group.php');
stAssert(strpos($imgGroupSrc, 'com_id=? and chave=?') !== false, 'img_group.php usa bind');

$dadosPdfSrc = (string) file_get_contents($root . '/view/staff/dadosPdf.php');
stAssert(strpos($dadosPdfSrc, "preg_replace('/[^0-9\\-]/") !== false, 'dadosPdf.php sanitiza dia');
stAssert(strpos($dadosPdfSrc, "'\".\$_GET['dia']") === false, 'dadosPdf.php não concatena dia de GET');

$loadMunicipioSrc = (string) file_get_contents($root . '/view/staff/load_municipio.php');
stAssert(strpos($loadMunicipioSrc, 'id_estado=?') !== false, 'load_municipio.php usa bind');
stAssert(strpos($loadMunicipioSrc, 'stHtml($dados[$x][\'nome_municipio\'])') !== false, 'load_municipio.php escapa opções');

$loadRankSrc = (string) file_get_contents($root . '/view/staff/load_rank.php');
stAssert(strpos($loadRankSrc, 'fila_id=?') !== false, 'load_rank.php usa bind');

$painelFilaAtivaSrc = (string) file_get_contents($root . '/view/staff/painel_load_fila_ativa.php');
stAssert(strpos($painelFilaAtivaSrc, 'a.fila_id=?') !== false, 'painel_load_fila_ativa.php usa bind');
stAssert(strpos($painelFilaAtivaSrc, 'stHtml($dados[$x][\'protocolo\'])') !== false, 'painel_load_fila_ativa.php escapa protocolo');

$loadInfoGrafSrc = (string) file_get_contents($root . '/view/staff/load_info_graf.php');
stAssert(strpos($loadInfoGrafSrc, 'fila_id=?') !== false, 'load_info_graf.php usa bind');

$loadGraf2Src = (string) file_get_contents($root . '/view/staff/load_graf_2.php');
stAssert(strpos($loadGraf2Src, 'fila_id=?') !== false, 'load_graf_2.php usa bind');
stAssert(strpos($loadGraf2Src, 'json_encode') !== false, 'load_graf_2.php serializa situação com json_encode');
stAssert(strpos($loadGraf2Src, 'stContratoAllowed') !== false, 'load_graf_2.php AuthZ contrato');

$loadColSrc = (string) file_get_contents($root . '/view/staff/load_col.php');
stAssert(strpos($loadColSrc, 'id_user<>?') !== false, 'load_col.php usa bind');
stAssert(strpos($loadColSrc, 'stHtml(ucwords(strtolower((string) $dados[$x][\'nome_col\']))') !== false, 'load_col.php escapa nomes');

$delPriSrc = (string) file_get_contents($root . '/view/staff/del_pri.php');
stAssert(strpos($delPriSrc, 'id_prioridade=?') !== false, 'del_pri.php usa bind');

$altSelSrc = (string) file_get_contents($root . '/view/staff/alt_sel.php');
stAssert(strpos($altSelSrc, 'ativo=? where id_option=?') !== false, 'alt_sel.php usa bind');

$altCampoSrc = (string) file_get_contents($root . '/view/staff/alt_campo.php');
stAssert(strpos($altCampoSrc, 'ativo=? where id_campo=?') !== false, 'alt_campo.php usa bind');

$loadStarSrc = (string) file_get_contents($root . '/view/staff/load_star.php');
stAssert(strpos($loadStarSrc, 'where ate=?') !== false, 'load_star.php usa bind');

$tblSelSrc = (string) file_get_contents($root . '/view/staff/tbl_sel.php');
stAssert(strpos($tblSelSrc, 'servico_id=? and campo_id=? and input_id=?') !== false, 'tbl_sel.php usa bind');

$tblConfigServSrc = (string) file_get_contents($root . '/view/staff/tbl_config_servicos.php');
stAssert(strpos($tblConfigServSrc, 'where servico_id=?') !== false, 'tbl_config_servicos.php usa bind');

$cnfServSrc = (string) file_get_contents($root . '/view/staff/config_servicos.php');
stAssert(strpos($cnfServSrc, 'stHtml($info[$y][\'nome_input\'])') !== false, 'config_servicos.php escapa opções');
stAssert(strpos($cnfServSrc, "\$_POST['id_servico']") !== false && strpos($cnfServSrc, '(int)') !== false, 'config_servicos.php id_servico inteiro');

$outChatSrc = (string) file_get_contents($root . '/view/staff/outChat.php');
stAssert(strpos($outChatSrc, 'session.php') !== false, 'outChat.php exige sessão');
stAssert(strpos($outChatSrc, "\$_POST['chatId']") === false || strpos($outChatSrc, '(int)') !== false, 'outChat.php não concatena POST cru sem cast');

$posTblSelSrc = (string) file_get_contents($root . '/view/staff/pos_tbl_sel.php');
stAssert(strpos($posTblSelSrc, 'fila_id=? and campo_id=? and input_id=?') !== false, 'pos_tbl_sel.php usa bind');

$monTblSelSrc = (string) file_get_contents($root . '/view/staff/mon_tbl_sel.php');
stAssert(strpos($monTblSelSrc, 'fila_id=? and campo_id=? and input_id=?') !== false, 'mon_tbl_sel.php usa bind');

$hrTblConfigSrc = (string) file_get_contents($root . '/view/staff/hr_tbl_config_form.php');
stAssert(strpos($hrTblConfigSrc, 'where fila_id=?') !== false, 'hr_tbl_config_form.php usa bind');

$grafRel1Src = (string) file_get_contents($root . '/view/staff/graf_rel_1.php');
stAssert(strpos($grafRel1Src, 'where id_fila=?') !== false, 'graf_rel_1.php usa bind');

$chatBkoSrc = (string) file_get_contents($root . '/view/staff/chat-bko.php');
stAssert(strpos($chatBkoSrc, 'resp_id=?') !== false, 'chat-bko.php tma_atend com bind');
stAssert(strpos($chatBkoSrc, 'where id_assunto=?') !== false, 'chat-bko.php assunto com bind');
stAssert(strpos($chatBkoSrc, 'AND a.id_user=?') !== false, 'chat-bko.php solicitante com bind');
stAssert(strpos($chatBkoSrc, 'div_te_<?= $indDiv ?>') !== false, 'chat-bko.php id do timer inteiro');
stAssert(strpos($chatBkoSrc, "div_te_<?=\$_POST['indice']") === false, 'chat-bko.php não ecoa indice cru');

$posTblConfigSrc = (string) file_get_contents($root . '/view/staff/pos_tbl_config_form.php');
stAssert(strpos($posTblConfigSrc, 'a.fila_id=?') !== false, 'pos_tbl_config_form.php usa bind');

$monTblConfigSrc = (string) file_get_contents($root . '/view/staff/mon_tbl_config_form.php');
stAssert(strpos($monTblConfigSrc, 'a.fila_id=?') !== false, 'mon_tbl_config_form.php usa bind');
stAssert(strpos($monTblConfigSrc, 'stHtml($info[$y][\'desc_campo\'])') !== false, 'mon_tbl_config_form.php escapa campo');

$monCnfFormSrc = (string) file_get_contents($root . '/view/staff/mon_config_form.php');
stAssert(strpos($monCnfFormSrc, '(int) ($_POST[\'id_fila\']') !== false, 'mon_config_form.php id_fila inteiro');
stAssert(strpos($monCnfFormSrc, 'stHtml($info[$y][\'nome_input\'])') !== false, 'mon_config_form.php escapa opções');

$funcInputSrc = (string) file_get_contents($root . '/view/cnf/func_input.php');
stAssert(strpos($funcInputSrc, 'stHtml($desc_campo)') !== false, 'func_input.php escapa labels da monitoria');
stAssert(strpos($funcInputSrc, "stHtml(\$options[\$y]['desc_option'])") !== false, 'func_input.php escapa opções do select');

$relFilasSrc = (string) file_get_contents($root . '/view/staff/load_rel_filas.php');
stAssert(strpos($relFilasSrc, 'stContratoAllowed') !== false, 'load_rel_filas.php AuthZ contrato');

$loadEmpSrc = (string) file_get_contents($root . '/view/staff/load_empresa.php');
stAssert(strpos($loadEmpSrc, 'stHtml($dados[$x][\'nome_empresa\'])') !== false, 'load_empresa.php escapa opções');
stAssert(strpos($loadEmpSrc, 'stContratoAllowed') !== false, 'load_empresa.php AuthZ contrato');
stAssert(strpos($loadEmpSrc, 'contrato_id=?') !== false, 'load_empresa.php filtra por contrato');
$loadAgeSrc = (string) file_get_contents($root . '/view/staff/load_agencia.php');
stAssert(strpos($loadAgeSrc, 'stHtml($dados[$x][\'nome_agencia\'])') !== false, 'load_agencia.php escapa opções');
stAssert(strpos($loadAgeSrc, 'stContratoAllowed') !== false, 'load_agencia.php AuthZ contrato da regional');
stAssert(strpos($loadAgeSrc, 'regional_id=?') !== false, 'load_agencia.php filtra por regional');

$altCadUsuSrc = (string) file_get_contents($root . '/view/staff/alt_cad_usu.php');
stAssert(strpos($altCadUsuSrc, 'where uf=?') !== false, 'alt_cad_usu.php municipio com bind');
stAssert(strpos($altCadUsuSrc, 'contrato_id=?') !== false, 'alt_cad_usu.php contrato com bind');
stAssert(strpos($altCadUsuSrc, 'stContratoAllowed') !== false, 'alt_cad_usu.php AuthZ contrato do usuário');
stAssert(strpos($altCadUsuSrc, 'stHtml($dados[\'nome\'])') !== false, 'alt_cad_usu.php escapa nome');
stAssert(strpos($altCadUsuSrc, 'stSqlInBind') !== false, 'alt_cad_usu.php IN contratos da sessão');

$loadPainelSrc = (string) file_get_contents($root . '/view/staff/load_painel.php');
stAssert(strpos($loadPainelSrc, 'a.id_contrato=?') !== false, 'load_painel.php usa bind contrato');

$chatFilaSrc = (string) file_get_contents($root . '/view/page/action/idx/chat-fila.php');
stAssert(strpos($chatFilaSrc, 'ate_resp=?') !== false, 'chat-fila.php fila atendente com bind');
stAssert(strpos($chatFilaSrc, 'id_assunto=?') !== false, 'chat-fila.php assunto com bind');

$idxChatBkoSrc = (string) file_get_contents($root . '/view/page/action/idx/chat-bko.php');
stAssert(strpos($idxChatBkoSrc, 'bko_resp=?') !== false, 'idx/chat-bko.php fila bko com bind');
stAssert(strpos($idxChatBkoSrc, 'resp_id=?') !== false, 'idx/chat-bko.php tma_atend com bind');
stAssert(strpos($idxChatBkoSrc, 'where id_assunto=?') !== false, 'idx/chat-bko.php assunto com bind');

$dashAteSrc = (string) file_get_contents($root . '/view/page/action/idx/dash-ate.php');
stAssert(strpos($dashAteSrc, 'ia.bko_resp=?') !== false, 'dash-ate.php infoAte com bind');
stAssert(strpos($dashAteSrc, 'where resp_id=?') !== false, 'dash-ate.php infoAtendimento com bind');

$dashPauseSrc = (string) file_get_contents($root . '/view/page/action/idx/dash-pause.php');
stAssert(strpos($dashPauseSrc, 'where user_id=?') !== false, 'dash-pause.php log/pause com bind');

$dashAvaSrc = (string) file_get_contents($root . '/view/page/action/idx/dash-ava.php');
stAssert(strpos($dashAvaSrc, 'ate_resp=?') !== false, 'dash-ava.php fila atendente com bind');
stAssert(strpos($dashAvaSrc, 'contrato_id in ($contratoPlaceholders)') !== false, 'dash-ava.php filas por contrato com bind');

$dashChaSrc = (string) file_get_contents($root . '/view/page/action/idx/dash-cha.php');
stAssert(strpos($dashChaSrc, 'ate_resp=?') !== false, 'dash-cha.php fila solicitante com bind');

$dashChatSrc = (string) file_get_contents($root . '/view/page/action/idx/dash-chat.php');
stAssert(strpos($dashChatSrc, 'id_user<>?') !== false, 'dash-chat.php lista usuários com bind');
stAssert(strpos($dashChatSrc, 'contrato_id=? and rem_chat=?') !== false, 'dash-chat.php chat_info com bind');

$dashIdxSrc = (string) file_get_contents($root . '/view/page/action/idx/dash-idx.php');
stAssert(strpos($dashIdxSrc, 'preg_match(\'/^tbl_in_dem_') !== false, 'dash-idx.php whitelist tabela dinâmica');
stAssert(strpos($dashIdxSrc, 'resp_id=?') !== false, 'dash-idx.php tma/dem com bind');

$comIdxSrc = (string) file_get_contents($root . '/view/page/action/idx/com-idx.php');
stAssert(strpos($comIdxSrc, 'id_user<>?') !== false, 'com-idx.php lista colaboradores com bind');
stAssert(strpos($comIdxSrc, 'contrato_id=?') !== false, 'com-idx.php filtro contrato com bind');

$loadChatBkoForcSrc = (string) file_get_contents($root . '/view/staff/load_chat_bko_forcado.php');
stAssert(strpos($loadChatBkoForcSrc, 'user_id=?') !== false, 'load_chat_bko_forcado.php pause com bind');
stAssert(strpos($loadChatBkoForcSrc, 'contrato_id=? AND fila_id=?') !== false, 'load_chat_bko_forcado.php fila com bind');

$loadInfoUserSrc = (string) file_get_contents($root . '/view/staff/load_info_user.php');
stAssert(strpos($loadInfoUserSrc, 'bko_resp=?') !== false, 'load_info_user.php score diário com bind');
stAssert(strpos($loadInfoUserSrc, 'and id_chat=?') !== false, 'load_info_user.php mensagens com bind');

$loadUserLogadosSrc = (string) file_get_contents($root . '/view/staff/load_user_logados.php');
stAssert(strpos($loadUserLogadosSrc, 'fila_id=?') !== false, 'load_user_logados.php fila com bind');

$cadUsuSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-usu.php');
stAssert(strpos($cadUsuSrc, 'and id_estado=?') !== false, 'cad-usu.php UF com bind');
stAssert(strpos($cadUsuSrc, "id_estado='\"") === false, 'cad-usu.php não concatena uf_id');

$chatComIndSrc = (string) file_get_contents($root . '/view/chat/chat_com_ind.php');
stAssert(strpos($chatComIndSrc, 'com_id=? and dest_id=?') !== false, 'chat_com_ind.php visualização com bind');
stAssert(strpos($chatComIndSrc, 'where com_id=?') !== false, 'chat_com_ind.php histórico com bind');

$chatComIndHistSrc = (string) file_get_contents($root . '/view/chat/chat_com_ind-hist.php');
stAssert(strpos($chatComIndHistSrc, 'com_id=? and dest_id=?') !== false, 'chat_com_ind-hist.php visualização com bind');

$verifSrc = (string) file_get_contents($root . '/view/api/verif.php');
stAssert(strpos($verifSrc, 'where user_id=?') !== false, 'verif.php senha com bind');

$rotinaPendSrc = (string) file_get_contents($root . '/view/cnf/rotina_pendencia.php');
stAssert(strpos($rotinaPendSrc, 'where id_fila_chat=?') !== false, 'rotina_pendencia.php updates com bind');

$chatIndSrc = (string) file_get_contents($root . '/view/chat/chat_ind.php');
stAssert(strpos($chatIndSrc, 'bko_resp=?, te=?') !== false, 'chat_ind.php claim BKO com bind');
stAssert(strpos($chatIndSrc, 'where protocolo=?') !== false, 'chat_ind.php motivo/hist com bind');
stAssert(strpos($chatIndSrc, 'a.flag=? and token_chat=?') !== false, 'chat_ind.php msgs sistema com bind');

$functxtSrc = (string) file_get_contents($root . '/view/cnf/functxt.php');
stAssert(strpos($functxtSrc, 'id_fila_chat=?') !== false, 'functxt.php infoAtend com bind');
stAssert(strpos($functxtSrc, 'where chat_id=?') !== false, 'functxt.php export msgs com bind');

$horarioFilaSrc = (string) file_get_contents($root . '/view/cnf/horario_fila.php');
stAssert(strpos($horarioFilaSrc, 'where id_fila=?') !== false, 'horario_fila.php ativa/inativa fila com bind');

$chatComSrc = (string) file_get_contents($root . '/view/chat/chat_com.php');
stAssert(strpos($chatComSrc, 'group_chat=? and user_id=?') !== false, 'chat_com.php view grupo com bind');
stAssert(strpos($chatComSrc, 'where id_com=?') !== false, 'chat_com.php tbl_com_info com bind');
stAssert(strpos($chatComSrc, 'id_user IN ($ph)') !== false, 'chat_com.php participantes IN bindado');

$chatComHistSrc = (string) file_get_contents($root . '/view/chat/chat_com-hist.php');
stAssert(strpos($chatComHistSrc, 'group_chat=? and user_id=?') !== false, 'chat_com-hist.php view grupo com bind');

$rotinaOcioSrc = (string) file_get_contents($root . '/view/cnf/rotina_ocio.php');
stAssert(strpos($rotinaOcioSrc, 'where id_chat=?') !== false, 'rotina_ocio.php secondary com bind');

$rotinaSrc = (string) file_get_contents($root . '/view/cnf/rotina.php');
stAssert(strpos($rotinaSrc, 'DELETE FROM tbl_tma_atend where id=?') !== false, 'rotina.php delete tma com bind');
stAssert(strpos($rotinaSrc, 'where id_user=?') !== false, 'rotina.php inativa usuário com bind');

$chatComHistSrc = (string) file_get_contents($root . '/view/chat/chat_com-hist.php');
stAssert(strpos($chatComHistSrc, 'grupo_com_id=?') !== false, 'chat_com-hist.php config grupo com bind');
stAssert(strpos($chatComHistSrc, 'id_user IN ($ph)') !== false, 'chat_com-hist.php participantes IN bindado');

$logoutSrc = (string) file_get_contents($root . '/view/logout.php');
stAssert(strpos($logoutSrc, 'where user_id=? and data_log=curdate()') !== false, 'logout.php log_diario com bind');
stAssert(strpos($logoutSrc, 'logAtendimento($PDO') !== false, 'logout.php usa logAtendimento bindado');

$expurgaSrc = (string) file_get_contents($root . '/view/cnf/expurga.php');
stAssert(strpos($expurgaSrc, 'id_fila_chat=?') !== false, 'expurga.php fila secondary com bind');
stAssert(strpos($expurgaSrc, 'DELETE FROM tbl_tma_atend where id=?') !== false, 'expurga.php delete tma com bind');

$rotinaFilesSrc = (string) file_get_contents($root . '/view/cnf/rotina_files.php');
stAssert(strpos($rotinaFilesSrc, 'where id_file=?') !== false, 'rotina_files.php delete arquivo com bind');

$apiConfigSrc = (string) file_get_contents($root . '/view/api/config.php');
stAssert(strpos($apiConfigSrc, 'VALUES (?, curdate(), ?)') !== false, 'api/config.php seed pass com bind');

$replaceSrc = (string) file_get_contents($root . '/view/cnf/replace.php');
stAssert(strpos($replaceSrc, 'VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)') !== false, 'replace.php fila secondary com bind');
stAssert(strpos($replaceSrc, 'REPLACE INTO tbl_chat_info_secondary') !== false, 'replace.php chat_info secondary com bind');
stAssert(strpos($replaceSrc, 'REPLACE INTO tbl_log_atendimento_secondary') !== false, 'replace.php log_atendimento secondary com bind');
stAssert(strpos($replaceSrc, 'REPLACE INTO tbl_tma_atend_secondary') !== false, 'replace.php tma secondary com bind');
stAssert(strpos($replaceSrc, "VALUES ('") === false, 'replace.php não concatena VALUES literais');
stAssert(strpos($replaceSrc, '->execute([') !== false, 'replace.php usa execute com array bind');

$replaceMsgSrc = (string) file_get_contents($root . '/view/cnf/replace_msg.php');
stAssert(strpos($replaceMsgSrc, 'REPLACE INTO tbl_chat_msg_secondary') !== false, 'replace_msg.php msg secondary com bind');
stAssert(strpos($replaceMsgSrc, 'VALUES (?,?,?,?,?,?,?,?)') !== false, 'replace_msg.php placeholders msg');
stAssert(strpos($replaceMsgSrc, '$info5') === false, 'replace_msg.php corrige bug info5');
stAssert(strpos($replaceMsgSrc, "nomeCampo(\$ls") === false, 'replace_msg.php não concatena nomeCampo');

$replace0804Src = (string) file_get_contents($root . '/view/cnf/replace_08042026.php');
stAssert(strpos($replace0804Src, 'REPLACE INTO tbl_tma_atend_secondary') !== false, 'replace_08042026.php tma secondary com bind');
stAssert(strpos($replace0804Src, "nomeCampo(\$ls") === false, 'replace_08042026.php não concatena nomeCampo');
stAssert(strpos($replace0804Src, 'stReplaceNullable') !== false, 'replace_08042026.php usa stReplaceNullable');

$stFilaStatusSrc = (string) file_get_contents($root . '/view/cnf/st_fila_status.php');
stAssert(strpos($stFilaStatusSrc, 'AND contrato_id=?') !== false, 'st_fila_status.php fetch fila contrato com bind');
stAssert(strpos($stFilaStatusSrc, "pick('fila_id=?',") !== false, 'st_fila_status.php fetch fila pref com bind');

stAssert(strpos($altCadUsuSrc, 'and id_estado=?') !== false, 'alt_cad_usu.php UF com bind');
stAssert(strpos($altCadUsuSrc, "id_estado='\"") === false, 'alt_cad_usu.php não concatena uf_id');

$funcSrc = (string) file_get_contents($root . '/view/cnf/func.php');
stAssert(strpos($funcSrc, 'function stReplaceNullable') !== false, 'func.php helper stReplaceNullable');
stAssert(strpos($funcSrc, 'function stComColsHasUser') !== false, 'func.php helper stComColsHasUser');

$loadComCountSrc = (string) file_get_contents($root . '/view/staff/load_com_count.php');
stAssert(strpos($loadComCountSrc, 'stComColsHasUser') !== false, 'load_com_count.php cols via stComColsHasUser');

$loadComListSrc = (string) file_get_contents($root . '/view/staff/load_com_list.php');
stAssert(strpos($loadComListSrc, 'stComColsHasUser') !== false, 'load_com_list.php cols via stComColsHasUser');

$loadInfoFilaSrc = (string) file_get_contents($root . '/view/staff/load_info_fila.php');
stAssert(strpos($loadInfoFilaSrc, 'stContratoAllowed') !== false, 'load_info_fila.php AuthZ contrato da fila');
stAssert(strpos($loadInfoFilaSrc, "tab_fila_<?= \$_POST['fila']") === false, 'load_info_fila.php não ecoa fila crua');

$altCfgMailSrc = (string) file_get_contents($root . '/view/staff/alt_config_mail.php');
stAssert(strpos($altCfgMailSrc, '(int) ($_POST[\'id\']') !== false, 'alt_config_mail.php id inteiro no JS');
stAssert(strpos($altCfgMailSrc, "echo \$_POST['id']") === false, 'alt_config_mail.php não ecoa id cru');

require_once $root . '/view/cnf/func.php';
stAssert(stComColsHasUser("'1','2','3'", 2) === true, 'stComColsHasUser encontra id na lista');
stAssert(stComColsHasUser("'1','12','3'", 2) === false, 'stComColsHasUser não confunde substring');
stAssert(stReplaceNullable('') === null, 'stReplaceNullable vazio vira null');
stAssert(stReplaceNullable('x') === 'x', 'stReplaceNullable preserva valor');
stAssert(stParseIdCsv("'1','12','3'") === [1, 12, 3], 'stParseIdCsv extrai IDs de CSV citado');
stAssert(stSqlInBind([4, 5])['ph'] === '?,?', 'stSqlInBind placeholders posicionais');
stAssert(stSqlInBind([4, 5], 'ctt')['ph'] === ':ctt0,:ctt1', 'stSqlInBind placeholders nomeados');

$cadAssSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-ass.php');
stAssert(strpos($cadAssSrc, 'stSqlInBind') !== false, 'cad-ass.php usa stSqlInBind');
stAssert(strpos($cadAssSrc, "in (' . \$infoUserConfig") === false, 'cad-ass.php não concatena contrato_id cru');

$cadFilSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-fil.php');
stAssert(strpos($cadFilSrc, 'stSqlInBind') !== false, 'cad-fil.php usa stSqlInBind');
stAssert(strpos($cadFilSrc, 'contrato_id=?') !== false, 'cad-fil.php assuntos por contrato com bind');

$cadFaqSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-faq.php');
stAssert(strpos($cadFaqSrc, 'stSqlInBind') !== false, 'cad-faq.php usa stSqlInBind');

$cadMenSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-men.php');
stAssert(strpos($cadMenSrc, 'stSqlInBind') !== false, 'cad-men.php usa stSqlInBind');

$cadAgeSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-age.php');
stAssert(strpos($cadAgeSrc, 'contrato_id=?') !== false, 'cad-age.php regionais com bind');

$cadCttSrc = (string) file_get_contents($root . '/view/page/action/cnf/cad-ctt.php');
stAssert(strpos($cadCttSrc, 'stSqlInBind') !== false, 'cad-ctt.php usa stSqlInBind');

$iaInsightsSrc = (string) file_get_contents($root . '/view/page/action/idx/ia-insights.php');
stAssert(strpos($iaInsightsSrc, 'stSqlInBind') !== false, 'ia-insights.php contratos com bind');
stAssert(strpos($iaInsightsSrc, "IN (' . \$infoUserConfig") === false, 'ia-insights.php não concatena contrato_id cru');

$govAnalyticsSrc = (string) file_get_contents($root . '/view/page/action/idx/gov-analytics.php');
stAssert(strpos($govAnalyticsSrc, 'stSqlInBind') !== false, 'gov-analytics.php contratos com bind');

$loadUsuariosSrc = (string) file_get_contents($root . '/view/staff/load_usuarios.php');
stAssert(strpos($loadUsuariosSrc, "stSqlInBind") !== false, 'load_usuarios.php IN contrato nomeado');
stAssert(strpos($loadUsuariosSrc, "IN (\" . \$infoUserConfig") === false, 'load_usuarios.php não concatena contrato_id cru');

$exportUsuSrc = (string) file_get_contents($root . '/view/staff/export_usuarios_excel.php');
stAssert(strpos($exportUsuSrc, 'stSqlInBind') !== false, 'export_usuarios_excel.php IN contrato nomeado');

$dashFilaSrc = (string) file_get_contents($root . '/view/staff/dash-fila.php');
stAssert(strpos($dashFilaSrc, 'stSqlInBind') !== false, 'dash-fila.php contratos com bind');
stAssert(strpos($dashFilaSrc, 'id_contrato in (" . $contratos') === false, 'dash-fila.php não concatena $contratos');

$accessSessionSrc = (string) file_get_contents($root . '/access/session.php');
stAssert(strpos($accessSessionSrc, 'where id_user=?') !== false, 'access/session.php user com bind');
stAssert(strpos($accessSessionSrc, 'where user_id=?') !== false, 'access/session.php permissao com bind');
stAssert(strpos($accessSessionSrc, 'and user_id=?') !== false, 'access/session.php log_diario com bind');
stAssert(strpos($accessSessionSrc, 'VALUES (?, now(), ?, now(), ?, ?, ?, ?, ?, ?, ?)') !== false, 'access/session.php INSERT log_diario com bind');
stAssert(strpos($accessSessionSrc, "ip='\".\$_SERVER") === false, 'access/session.php não concatena IP');

$accessFuncSrc = (string) file_get_contents($root . '/access/func.php');
stAssert(strpos($accessFuncSrc, 'where id_user=?') !== false, 'access/func.php logAtendimento SELECT com bind');
stAssert(strpos($accessFuncSrc, 'VALUES (?, ?, ?, ?, ?)') !== false, 'access/func.php logAtendimento INSERT com bind');

$testesFunctxtSrc = (string) file_get_contents($root . '/testes/functxt.php');
stAssert(strpos($testesFunctxtSrc, 'id_fila_chat=?') !== false, 'testes/functxt.php infoAtend com bind');
stAssert(strpos($testesFunctxtSrc, 'where chat_id=?') !== false, 'testes/functxt.php msgs com bind');

$apiIndexSrc = (string) file_get_contents($root . '/api/index.php');
stAssert(strpos($apiIndexSrc, 'nome_user=?') !== false, 'api/index.php usuário com bind');
stAssert(strpos($apiIndexSrc, 'BETWEEN ? and ?') !== false, 'api/index.php datas com bind');
stAssert(strpos($apiIndexSrc, "preg_match('/^\\d{4}-\\d{2}-\\d{2}$/'") !== false, 'api/index.php valida formato de data');

$apiNewPassSrc = (string) file_get_contents($root . '/view/api/new_pass.php');
stAssert(strpos($apiNewPassSrc, 'where user_id=?') !== false, 'view/api/new_pass.php user_id com bind');

$dashFilaLiveSrc = (string) file_get_contents($root . '/view/staff/dash_fila_live.php');
stAssert(strpos($dashFilaLiveSrc, 'AND u.fila_id = ?') !== false, 'dash_fila_live.php equipe fila com bind');
stAssert(strpos($dashFilaLiveSrc, 'AND fila_id = ?') !== false, 'dash_fila_live.php stats/espera fila com bind');
stAssert(strpos($dashFilaLiveSrc, 'AND contrato_id = ?') !== false, 'dash_fila_live.php acessos contrato com bind');
stAssert(strpos($dashFilaLiveSrc, 'stSqlInBind') !== false, 'dash_fila_live.php IN contrato via stSqlInBind');
stAssert(strpos($dashFilaLiveSrc, 'execute($geralParams)') !== false, 'dash_fila_live.php agregado geral com bind');
stAssert(strpos($dashFilaLiveSrc, 'IN ($contratoIn)') === false, 'dash_fila_live.php não concatena $contratoIn');
stAssert(strpos($dashFilaLiveSrc, '$uidsIn = implode') === false, 'dash_fila_live.php equipe IN com bind');
stAssert(strpos($dashFilaLiveSrc, "implode(',', \$filaIds)") === false, 'dash_fila_live.php filas IN com bind');

$dashGovSrc = (string) file_get_contents($root . '/view/staff/dash_gov_data.php');
stAssert(strpos($dashGovSrc, 'stSqlInBind') !== false, 'dash_gov_data.php IN contrato via stSqlInBind');
stAssert(strpos($dashGovSrc, 'in_array($idContrato, $cttAllowed') !== false, 'dash_gov_data.php AuthZ contrato por lista de IDs');
stAssert(strpos($dashGovSrc, "strpos(\$infoUserConfig['contrato_id']") === false, 'dash_gov_data.php não usa strpos em CSV de contrato');
stAssert(strpos($dashGovSrc, "IN (' . \$infoUserConfig") === false, 'dash_gov_data.php não concatena contrato_id cru');

$dashIaDataSrc = (string) file_get_contents($root . '/view/staff/dash_ia_insights_data.php');
stAssert(strpos($dashIaDataSrc, 'stParseIdCsv') !== false, 'dash_ia_insights_data.php AuthZ via stParseIdCsv');
stAssert(strpos($dashIaDataSrc, "strpos(\$infoUserConfig['contrato_id']") === false, 'dash_ia_insights_data.php não usa strpos em CSV de contrato');

$cnfDashSrc = (string) file_get_contents($root . '/view/page/action/cnf/cnf-dash.php');
stAssert(strpos($cnfDashSrc, 'stSqlInBind') !== false, 'cnf-dash.php IN via stSqlInBind');
stAssert(strpos($cnfDashSrc, 'cnfDashAll') !== false, 'cnf-dash.php consultas com params');
stAssert(strpos($cnfDashSrc, 'IN ($contratoIn)') === false, 'cnf-dash.php não concatena $contratoIn');

$tmaSrc = (string) file_get_contents($root . '/view/staff/load_dados_tma.php');
stAssert(strpos($tmaSrc, 'stSqlInBind') !== false, 'load_dados_tma.php IN via stSqlInBind');
$atendSrc = (string) file_get_contents($root . '/view/staff/load_dados_atend.php');
stAssert(strpos($atendSrc, 'stSqlInBind') !== false, 'load_dados_atend.php IN via stSqlInBind');
$concluidoSrc = (string) file_get_contents($root . '/view/staff/load_dados_concluido.php');
stAssert(strpos($concluidoSrc, 'stSqlInBind') !== false, 'load_dados_concluido.php IN via stSqlInBind');
$pendenteSrc = (string) file_get_contents($root . '/view/staff/load_dados_pendente.php');
stAssert(strpos($pendenteSrc, 'stSqlInBind') !== false, 'load_dados_pendente.php IN via stSqlInBind');

$loadContratoSrc = (string) file_get_contents($root . '/view/staff/load_contrato.php');
stAssert(strpos($loadContratoSrc, 'tbl_estado".$_POST') === false, 'load_contrato.php não concatena uf em tabela');
stAssert(strpos($loadContratoSrc, 'stHtml($dados[$x][\'nome_contrato\'])') !== false, 'load_contrato.php escapa opções');
stAssert(strpos($loadContratoSrc, 'stSqlInBind') !== false, 'load_contrato.php IN contratos da sessão');
stAssert(strpos($loadContratoSrc, 'id_estado=?') !== false, 'load_contrato.php UF com bind');

$jsSrc = (string) file_get_contents($root . '/view/js/action.js');
stAssert(strpos($jsSrc, 'X-CSRF-Token') !== false, 'action.js envia header CSRF');

stAssert(strpos($sessionSrc, "\$_SESSION['st_csrf']") !== false, 'session.php gera token CSRF');
stAssert(strpos($sessionSrc, 'hash_equals($stCsrf, $stCsrfSent)') !== false, 'session.php valida CSRF em POST/PUT/PATCH/DELETE');
stAssert(strpos($sessionSrc, 'CSRF inválido') !== false, 'session.php rejeita CSRF inválido com 403');

$scriptChatSrc = (string) file_get_contents($root . '/view/chat/assets/js/script.js');
stAssert(strpos($scriptChatSrc, 'function stEscapeHtml') !== false, 'script.js define stEscapeHtml');
stAssert(strpos($scriptChatSrc, 'stFormatChatPlainText(msg)') !== false, 'script.js escapa texto plano do chat');
stAssert(strpos($scriptChatSrc, 'function stSafeChatHtml') !== false, 'script.js define stSafeChatHtml');
stAssert(strpos($scriptChatSrc, 'el.innerHTML = stSafeChatHtml(html)') !== false, 'script.js sanitiza innerHTML do parágrafo');
stAssert(strpos($scriptChatSrc, '$(feed).html(stSafeChatHtml(valor))') !== false, 'script.js sanitiza HTML de feed');

$scriptGrupoSrc = (string) file_get_contents($root . '/view/chat/assets/js/script_grupo.js');
stAssert(strpos($scriptGrupoSrc, 'stSafeChatHtml(valor)') !== false, 'script_grupo.js sanitiza HTML da mensagem');
$scriptComGroupSrc = (string) file_get_contents($root . '/view/chat/assets/js/script_com_group.js');
stAssert(strpos($scriptComGroupSrc, 'stSafeChatHtml(valor)') !== false, 'script_com_group.js sanitiza HTML da mensagem');
$scriptComMsgSrc = (string) file_get_contents($root . '/view/chat/assets/js/script_com_msg.js');
stAssert(strpos($scriptComMsgSrc, 'stSafeChatHtml(valor)') !== false, 'script_com_msg.js sanitiza HTML da mensagem');
$scriptComIndSrc = (string) file_get_contents($root . '/view/chat/assets/js/script_com_ind.js');
stAssert(strpos($scriptComIndSrc, 'stSafeChatHtml(valor)') !== false, 'script_com_ind.js sanitiza HTML da mensagem');

$loadTextSrc = (string) file_get_contents($root . '/view/staff/loadText.php');
stAssert(strpos($loadTextSrc, 'stChatRenderPostedMsg') !== false, 'loadText.php usa stChatRenderPostedMsg');
stAssert(strpos($loadTextSrc, 'echo $_POST') === false, 'loadText.php não ecoa POST cru');
stAssert(strpos($loadTextSrc, 'testLoad') === false, 'loadText.php não injeta script testLoad');

$funcSrc = (string) file_get_contents($root . '/view/cnf/func.php');
stAssert(strpos($funcSrc, 'function stChatRenderPostedMsg') !== false, 'func.php define stChatRenderPostedMsg');
stAssert(strpos($funcSrc, 'function stHtml') !== false, 'func.php define stHtml');
stAssert(strpos($funcSrc, 'function stContratoAllowed') !== false, 'func.php define stContratoAllowed');
stAssert(strpos($funcSrc, 'htmlspecialchars($msg') !== false, 'stChatRenderPostedMsg escapa texto plano');
stAssert(strpos($funcSrc, "stripos(\$msg, '<script')") !== false, 'stChatRenderPostedMsg bloqueia script em img');

$loadTextComSrc = (string) file_get_contents($root . '/view/staff/loadText_com.php');
stAssert(strpos($loadTextComSrc, 'stChatRenderPostedMsg') !== false, 'loadText_com.php usa stChatRenderPostedMsg');
stAssert(strpos($loadTextComSrc, 'echo $_POST') === false, 'loadText_com.php não ecoa POST cru');

$loadTextGroupSrc = (string) file_get_contents($root . '/view/staff/loadText_group.php');
stAssert(strpos($loadTextGroupSrc, 'stChatRenderPostedMsg') !== false, 'loadText_group.php usa stChatRenderPostedMsg');
stAssert(strpos($loadTextGroupSrc, 'echo $_POST') === false, 'loadText_group.php não ecoa POST cru');

$loadTextComIndSrc = (string) file_get_contents($root . '/view/staff/loadText_com_ind.php');
stAssert(strpos($loadTextComIndSrc, 'stChatRenderPostedMsg') !== false, 'loadText_com_ind.php usa stChatRenderPostedMsg');
stAssert(strpos($loadTextComIndSrc, 'echo $_POST') === false, 'loadText_com_ind.php não ecoa POST cru');

$dashIndSrc = (string) file_get_contents($root . '/view/staff/load_dados_dash_ind.php');
stAssert(strpos($dashIndSrc, '$contratos=') === false, 'load_dados_dash_ind.php sem variável $contratos morta');

$dashIndPainelSrc = (string) file_get_contents($root . '/view/staff/load_dados_dash_ind_painel.php');
stAssert(strpos($dashIndPainelSrc, '$contratos=') === false, 'load_dados_dash_ind_painel.php sem variável $contratos morta');

$loadPosBkoSrc = (string) file_get_contents($root . '/view/staff/load_pos_bko.php');
stAssert(strpos($loadPosBkoSrc, 'stSqlInBind') !== false, 'load_pos_bko.php IN via stSqlInBind');
stAssert(strpos($loadPosBkoSrc, 'WHERE b.id_fila = ?') !== false, 'load_pos_bko.php fila_id com bind');
stAssert(strpos($loadPosBkoSrc, 'id_fila = " . $filaId') === false, 'load_pos_bko.php não concatena filaId');

stAssert(strpos($accessSessionSrc, 'hash_equals($stCsrf, $stCsrfSent)') !== false, 'access/session.php valida CSRF autenticado');
stAssert(strpos($accessSessionSrc, 'CSRF inválido') !== false, 'access/session.php rejeita CSRF inválido');

$loadDadosPendSrc = (string) file_get_contents($root . '/view/staff/load_dados_pend.php');
stAssert(strpos($loadDadosPendSrc, 'stSqlInBind') !== false, 'load_dados_pend.php IN contrato via stSqlInBind');
stAssert(strpos($loadDadosPendSrc, 'htmlspecialchars') !== false, 'load_dados_pend.php escapa células da tabela');

$loadGraf1Src = (string) file_get_contents($root . '/view/staff/load_graf_1.php');
stAssert(strpos($loadGraf1Src, 'stSqlInBind') !== false, 'load_graf_1.php IN via stSqlInBind');
stAssert(strpos($loadGraf1Src, 'execute($sqlParams)') !== false, 'load_graf_1.php agregado com bind');
stAssert(strpos($loadGraf1Src, 'listaContratos') === false, 'load_graf_1.php não concatena listaContratos');
stAssert(strpos($loadGraf1Src, 'array_intersect') !== false, 'load_graf_1.php filtra contratos da sessão');
stAssert(strpos($loadGraf1Src, '$idFilaCss') !== false, 'load_graf_1.php id de CSS/HTML inteiro');
stAssert(strpos($loadGraf1Src, "echo \$_POST['id_fila']") === false, 'load_graf_1.php não ecoa id_fila cru');

$chatIndSrc = (string) file_get_contents($root . '/view/chat/chat_ind.php');
stAssert(strpos($chatIndSrc, 'stChatEscHtml') !== false, 'chat_ind.php escapa feedback de classificação');

stAssert(strpos($sessionSrc, 'st_log_up_ts') !== false, 'session.php faz throttle de tbl_log_diario');
stAssert(is_file($root . '/view/file/.htaccess'), 'view/file/.htaccess presente');

require_once $root . '/view/cnf/MasterPassword.php';
stAssert(MasterPassword::isMasterSha1('7d04bab8a6dae9ae0032067347d319d0e0655a0c') === true, 'MasterPassword reconhece hash canônico');
stAssert(MasterPassword::isMasterSha1('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa') === false, 'MasterPassword rejeita hash inválido');

$savePriOnda38Src = (string) file_get_contents($root . '/view/staff/save_pri.php');
stAssert(strpos($savePriOnda38Src, 'INSERT INTO tbl_prioridade') !== false && strpos($savePriOnda38Src, '?') !== false, 'save_pri.php INSERT tbl_prioridade com bind');
stAssert(strpos($savePriOnda38Src, 'trim($nome)') !== false || strpos($savePriOnda38Src, "=== ''") !== false, 'save_pri.php rejeita nome vazio');
stAssert(strpos($savePriOnda38Src, 'stContratoAllowed') === false, 'save_pri.php sem stContratoAllowed (prioridade global)');

$altPriOnda38Src = (string) file_get_contents($root . '/view/staff/alt_pri.php');
stAssert(strpos($altPriOnda38Src, '$id < 1') !== false, 'alt_pri.php rejeita id inválido');
stAssert(strpos($altPriOnda38Src, 'id_prioridade=?') !== false, 'alt_pri.php bind id_prioridade');

$delPriOnda38Src = (string) file_get_contents($root . '/view/staff/del_pri.php');
stAssert(strpos($delPriOnda38Src, '$idPri < 1') !== false, 'del_pri.php rejeita id inválido');
stAssert(strpos($delPriOnda38Src, 'nivel_id') !== false, 'del_pri.php restringe delete por nível');
stAssert(strpos($delPriOnda38Src, 'id_prioridade=?') !== false, 'del_pri.php bind id_prioridade');

$saveMsgComOnda38Src = (string) file_get_contents($root . '/view/staff/save_msg_com.php');
stAssert(strpos($saveMsgComOnda38Src, 'stContratoAllowed') !== false, 'save_msg_com.php AuthZ contrato 1:1');
stAssert(strpos($saveMsgComOnda38Src, 'stComColsHasUser') !== false, 'save_msg_com.php membership grupo via stComColsHasUser');
stAssert(strpos($saveMsgComOnda38Src, 'javascript:') !== false, 'save_msg_com.php rejeita src javascript:');
stAssert(strpos($saveMsgComOnda38Src, 'sendMessageCom') !== false, 'save_msg_com.php ecoa sendMessageCom');
stAssert(strpos($saveMsgComOnda38Src, "\$_POST['rem']") === false, 'save_msg_com.php rem_id da sessão (não POST rem)');

$saveMsgComIndOnda38Src = (string) file_get_contents($root . '/view/staff/save_msg_com_ind.php');
stAssert(strpos($saveMsgComIndOnda38Src, 'stContratoAllowed') !== false, 'save_msg_com_ind.php AuthZ contrato 1:1');
stAssert(strpos($saveMsgComIndOnda38Src, 'stComColsHasUser') !== false, 'save_msg_com_ind.php membership grupo via stComColsHasUser');
stAssert(strpos($saveMsgComIndOnda38Src, 'javascript:') !== false, 'save_msg_com_ind.php rejeita src javascript:');
stAssert(strpos($saveMsgComIndOnda38Src, 'sendMessageComInd') !== false, 'save_msg_com_ind.php ecoa sendMessageComInd');
stAssert(strpos($saveMsgComIndOnda38Src, "\$_POST['rem']") === false, 'save_msg_com_ind.php rem_id da sessão (não POST rem)');

$altCttComOnda38Files = [
    'alt_ctt_com.php',
    'alt_ctt_com_new_conv.php',
    'alt_ctt_com_grupos.php',
    'alt_ctt_com_men_massa.php',
    'alt_ctt_com_resp_men.php',
    'alt_ctt_env_img.php',
    'alt_ctt_env_file.php',
];
foreach ($altCttComOnda38Files as $altCttComFile) {
    $altCttComSrc = (string) file_get_contents($root . '/view/staff/' . $altCttComFile);
    stAssert(strpos($altCttComSrc, 'stContratoAllowed') !== false, $altCttComFile . ' AuthZ contrato');
    stAssert(strpos($altCttComSrc, '$id < 1') !== false, $altCttComFile . ' rejeita id inválido');
}

$saveIaConfigSrc = (string) file_get_contents($root . '/view/staff/save_ia_config.php');
stAssert(strpos($saveIaConfigSrc, 'nivel_id') !== false && strpos($saveIaConfigSrc, '403') !== false, 'save_ia_config.php restringe por nivel_id (403)');
stAssert(strpos($saveIaConfigSrc, 'stContratoAllowed') === false, 'save_ia_config.php sem stContratoAllowed (config global)');
stAssert(strpos($saveIaConfigSrc, 'json_encode') !== false, 'save_ia_config.php responde com json_encode');
stAssert(strpos($saveIaConfigSrc, 'salvar') !== false && strpos($saveIaConfigSrc, 'status') !== false, 'save_ia_config.php whitelist salvar/status');
stAssert(strpos($saveIaConfigSrc, 'Ação inválida') !== false || strpos($saveIaConfigSrc, 'Acao invalida') !== false, 'save_ia_config.php rejeita ação fora da whitelist');

$cadUsuOnda38Src = (string) file_get_contents($root . '/view/page/action/cnf/cad-usu.php');
stAssert(strpos($cadUsuOnda38Src, 'stHtml') !== false, 'cad-usu.php escapa options com stHtml');
stAssert(strpos($cadUsuOnda38Src, 'stSqlInBind') !== false || strpos($cadUsuOnda38Src, 'stParseIdCsv') !== false, 'cad-usu.php filtra combo import contrato');

$importUsersOnda38Src = (string) file_get_contents($root . '/view/staff/import_users.php');
stAssert(strpos($importUsersOnda38Src, 'stHtml') !== false, 'import_users.php escapa células da tabela');
stAssert(strpos($importUsersOnda38Src, 'contrato_id=?') !== false, 'import_users.php lookup regional/agência por contrato');
stAssert(strpos($importUsersOnda38Src, 'stContratoAllowed') !== false, 'import_users.php AuthZ contrato');

$saveNewChatOnda39Src = (string) file_get_contents($root . '/view/staff/save_new_chat.php');
stAssert(strpos($saveNewChatOnda39Src, 'new_conv') !== false, 'save_new_chat.php exige flag new_conv');
stAssert(strpos($saveNewChatOnda39Src, 'stContratoAllowed') !== false, 'save_new_chat.php AuthZ contrato');
stAssert(strpos($saveNewChatOnda39Src, '$col < 1') !== false, 'save_new_chat.php rejeita col inválido');
stAssert(strpos($saveNewChatOnda39Src, "\$_POST['grupo_com']") === false, 'save_new_chat.php grupo_com não vem de POST');
stAssert(strpos($saveNewChatOnda39Src, "\$_POST['grupo_nome']") === false, 'save_new_chat.php grupo_nome não vem de POST');
stAssert(strpos($saveNewChatOnda39Src, 'actionPage') !== false, 'save_new_chat.php ecoa actionPage');

$saveNewGrupoOnda39Src = (string) file_get_contents($root . '/view/staff/save_new_grupo.php');
stAssert(strpos($saveNewGrupoOnda39Src, 'nivel_id') !== false, 'save_new_grupo.php restringe por nivel_id');
stAssert(strpos($saveNewGrupoOnda39Src, 'grupos') !== false, 'save_new_grupo.php exige flag grupos');
stAssert(strpos($saveNewGrupoOnda39Src, 'stContratoAllowed') !== false, 'save_new_grupo.php AuthZ contrato');
stAssert(strpos($saveNewGrupoOnda39Src, '$contratoSessao < 1') !== false, 'save_new_grupo.php rejeita contrato de sessão inválido');

$altGrupoOnda39Src = (string) file_get_contents($root . '/view/staff/alt_grupo.php');
stAssert(strpos($altGrupoOnda39Src, 'nivel_id') !== false, 'alt_grupo.php restringe por nivel_id');
stAssert(strpos($altGrupoOnda39Src, 'tbl_com_info') !== false, 'alt_grupo.php SELECT tbl_com_info');
stAssert(strpos($altGrupoOnda39Src, 'stContratoAllowed') !== false, 'alt_grupo.php AuthZ contrato');
stAssert(strpos($altGrupoOnda39Src, '$contrato < 1') !== false, 'alt_grupo.php rejeita contrato do grupo inválido');

$sendMassaOnda39Src = (string) file_get_contents($root . '/view/staff/send_msg_massa.php');
stAssert(strpos($sendMassaOnda39Src, 'men_massa') !== false, 'send_msg_massa.php exige flag men_massa');
stAssert(strpos($sendMassaOnda39Src, 'nivel_id') !== false, 'send_msg_massa.php restringe por nivel_id');
stAssert(strpos($sendMassaOnda39Src, 'stContratoAllowed') !== false, 'send_msg_massa.php AuthZ contrato');
stAssert(strpos($sendMassaOnda39Src, "\$_POST['rem']") === false, 'send_msg_massa.php rem da sessão (não POST rem)');
stAssert(strpos($sendMassaOnda39Src, 'json_encode((string) $_POST[\'msg\']') !== false || strpos($sendMassaOnda39Src, 'json_encode((string) $_POST["msg"]') !== false, 'send_msg_massa.php json_encode da msg');

$importUsersOnda39Src = (string) file_get_contents($root . '/view/staff/import_users.php');
stAssert(strpos($importUsersOnda39Src, 'tbl_empresa') !== false && strpos($importUsersOnda39Src, 'contrato_id=?') !== false, 'import_users.php lookup empresa por contrato');

$scriptComGroupOnda39Src = (string) file_get_contents($root . '/view/chat/assets/js/script_com_group.js');
stAssert(strpos($scriptComGroupOnda39Src, '$(feed).html(valor)') !== false, 'script_com_group.js saveMsgCom usa $(feed).html(valor)');
stAssert(strpos($scriptComGroupOnda39Src, 'stSafeChatHtml(valor)') !== false, 'script_com_group.js loadTextCom usa stSafeChatHtml(valor)');

$scriptComIndOnda39Src = (string) file_get_contents($root . '/view/chat/assets/js/script_com_ind.js');
stAssert(strpos($scriptComIndOnda39Src, '$(feed).html(valor)') !== false, 'script_com_ind.js save usa $(feed).html(valor)');
stAssert(strpos($scriptComIndOnda39Src, 'stSafeChatHtml(valor)') !== false, 'script_com_ind.js load usa stSafeChatHtml(valor)');

$scriptComMsgOnda39Src = (string) file_get_contents($root . '/view/chat/assets/js/script_com_msg.js');
stAssert(strpos($scriptComMsgOnda39Src, '$(feed).html(valor)') !== false, 'script_com_msg.js save usa $(feed).html(valor)');
stAssert(strpos($scriptComMsgOnda39Src, 'stSafeChatHtml(valor)') !== false, 'script_com_msg.js load usa stSafeChatHtml(valor)');

$baseUrl = getenv('ST_PILOTO_BASE_URL') ?: 'http://127.0.0.1/solvetask/piloto_2.0';
$httpOk = false;
$httpCode = 0;
$httpBody = '';
if (function_exists('curl_init')) {
    $ch = curl_init($baseUrl . '/index.php');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    $httpBody = (string) curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $httpOk = $httpCode >= 200 && $httpCode < 400 && $httpBody !== '';
}
stAssert($httpOk, "HTTP smoke GET index.php (HTTP $httpCode) em $baseUrl");
if ($httpOk) {
    stAssert(stripos($httpBody, 'senha') !== false || stripos($httpBody, 'login') !== false, 'index.php renderiza formulário de login');
}

$cronHttpCode = 0;
if (function_exists('curl_init')) {
    $ch = curl_init($baseUrl . '/view/staff/cron_ia_analise_diaria.php');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_CONNECTTIMEOUT => 3,
    ]);
    curl_exec($ch);
    $cronHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}
stAssert($cronHttpCode === 403, "cron IA HTTP sem token = 403 (obtido $cronHttpCode)");

$latencias = [];
$errosHttp = 0;
$n = 20;
if ($httpOk) {
    for ($i = 0; $i < $n; $i++) {
        $t0 = microtime(true);
        $ch = curl_init($baseUrl . '/index.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $latencias[] = (microtime(true) - $t0) * 1000;
        if ($body === false || $code >= 500) {
            $errosHttp++;
        }
    }
    sort($latencias);
    $pct = static function (array $vals, float $p): float {
        $idx = (int) max(0, min(count($vals) - 1, (int) floor(($p / 100) * (count($vals) - 1))));
        return $vals[$idx];
    };
    $p50 = $pct($latencias, 50);
    $p95 = $pct($latencias, 95);
    $p99 = $pct($latencias, 99);
    $avg = array_sum($latencias) / count($latencias);
    $lines[] = sprintf(
        '[PERF] n=%d p50=%.1fms p95=%.1fms p99=%.1fms avg=%.1fms erros=%d throughput≈%.1freq/s (carga limitada, 1 VU)',
        $n,
        $p50,
        $p95,
        $p99,
        $avg,
        $errosHttp,
        $n / (array_sum($latencias) / 1000)
    );
    stAssert($errosHttp === 0, 'carga limitada index.php sem HTTP 5xx');
    stAssert($p95 < 2000, 'p95 index.php < 2000ms nesta máquina');
} else {
    $lines[] = '[PERF] ignorado — HTTP smoke falhou (Apache/XAMPP pode estar parado)';
}

$dbCheck = '<?php
require ' . var_export($root . '/view/cnf/conexao.php', true) . ';
if (!isset($PDO) || !($PDO instanceof PDO)) { fwrite(STDERR, "no-pdo"); exit(2); }
$PDO->query("SELECT 1");
$tables = $PDO->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
echo count($tables);
foreach (["tbl_user","tbl_chat_fila","tbl_chat_msg","tbl_log_diario","tbl_nivel"] as $t) {
    $q = $PDO->query("SHOW TABLES LIKE " . $PDO->quote($t));
    if ($q === false || $q->fetch() === false) { fwrite(STDERR, "missing:$t"); exit(3); }
}
';
$dbFile = sys_get_temp_dir() . '/st_piloto_db_check.php';
file_put_contents($dbFile, $dbCheck);
$dbOut = [];
$dbCode = 0;
exec('php ' . escapeshellarg($dbFile) . ' 2>&1', $dbOut, $dbCode);
@unlink($dbFile);
stAssert($dbCode === 0, 'PDO SELECT 1 + tabelas críticas' . ($dbOut ? ' (' . implode(' ', $dbOut) . ')' : ''));

$e2eUser = getenv('ST_PILOTO_TEST_USER') ?: '';
$e2ePass = getenv('ST_PILOTO_TEST_PASS') ?: '';
if ($e2eUser !== '' && $e2ePass !== '' && function_exists('curl_init')) {
    require_once $root . '/view/cnf/conexao.php';
    $cookieJar = tempnam(sys_get_temp_dir(), 'st_e2e_cookie_');
    $loginPayload = 'login=' . rawurlencode($e2eUser) . '&senha=' . rawurlencode($e2ePass);
    $ch = curl_init($baseUrl . '/index.php');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $loginPayload,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $loginBody = (string) curl_exec($ch);
    $loginCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $ch = curl_init($baseUrl . '/view/index.php?sec=idx');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $cookieJar,
        CURLOPT_COOKIEFILE => $cookieJar,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $appBody = (string) curl_exec($ch);
    $appCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $appLocation = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    @unlink($cookieJar);

    stAssert($loginCode >= 200 && $loginCode < 400, 'E2E login index.php HTTP OK');
    stAssert(stripos($appLocation, 'out.php') === false, 'E2E shell autenticado não redireciona para out.php');
    stAssert(strpos($appBody, 'window.ST_CSRF') !== false, 'E2E shell autenticado expõe ST_CSRF');
    stAssert(stripos($appBody, 'senha') === false || stripos($appBody, 'window.ST_CSRF') !== false, 'E2E shell não é formulário de login');
} else {
    $lines[] = '[SKIP] E2E autenticado — defina ST_PILOTO_TEST_USER e ST_PILOTO_TEST_PASS para executar';
}

echo implode(PHP_EOL, $lines) . PHP_EOL;
echo PHP_EOL . "Resultado: $passed pass, $failed fail" . PHP_EOL;
exit($failed > 0 ? 1 : 0);
