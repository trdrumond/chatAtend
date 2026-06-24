# 04 - Inventario Tecnico (Arquivos, Endpoints, Entidades e Riscos)

## 1. Estrutura tecnica principal

- `access/`: entrada de autenticacao e configuracao de conexao/tenant.
- `view/`: nucleo funcional web (UI, roteamento, endpoints e configuracoes).
- `api/`: endpoint de consulta para integracao externa.
- `painel/`: painel operacional dedicado.
- `view/cnf/bd/`: scripts SQL e base de estrutura.

## 2. Arquivos chave do core

## 2.1 Entrada e autenticacao
- `index.php`
- `access/login_chat.php`
- `login.php`
- `view/cnf/session.php`
- `view/cnf/session_config.php`
- `view/logout.php`

## 2.2 Roteamento e shell da aplicacao
- `view/index.php`
- `view/action.php`
- `view/page/page-idx.php`
- `view/page/page-cnf.php`
- `view/page/page-usu.php`

## 2.3 Chat, fila e atendimento
- `view/page/action/idx/dash-cha.php`
- `view/page/action/idx/chat-fila.php`
- `view/page/action/idx/chat-ate.php`
- `view/page/action/idx/chat-bko.php`
- `view/chat/chat_ind.php`
- `view/chat/assets/js/script.js`

## 2.4 Configuracao e administracao
- `view/page/action/cnf/cad-usu.php`
- `view/page/action/cnf/cad-fil.php`
- `view/page/action/cnf/cad-ass.php`
- `view/page/action/cnf/cad-ctt.php`
- `view/page/action/cnf/cad-faq.php`
- `view/page/action/cnf/cad-pri.php`
- `view/page/action/cnf/cnf-dash.php`

## 2.5 Relatorios e analitico
- `view/page/action/idx/rel-dash.php`
- `view/page/action/idx/rel-fila.php`
- `view/page/action/idx/rel-ind.php`
- `view/staff/load_dados_rel.php`
- `view/staff/load_dados_rel_fila.php`
- `view/staff/load_dados_ind.php`

## 3. Endpoints operacionais em `view/staff` (inventario funcional)

## 3.1 Ciclo de atendimento
- `save_call.php` (abertura de chamado)
- `load_chat_ate.php` (status de espera/encaminhamento)
- `load_cancel_fila.php` (cancelamento de fila)
- `chat-bko.php` e `load_chat_bko.php` (assuncao e carga de atendimento)
- `save_msg.php` (mensagens)
- `save_msg_fim.php` (encerramento)
- `save_msg_transfer.php` (transferencia)
- `save_pause.php` (pausa operacional)
- `load_ta.php` (tempo de atendimento)

## 3.2 Pos e monitoria
- `save_pos.php`
- `save_class.php`
- `load_monitoria.php`
- `save_mon.php`
- `pos_config_form.php`
- `mon_config_form.php`

## 3.3 Cadastros e suporte administrativo
- `save_user.php`, `alt_cad_usu.php`, `load_usuarios.php`
- `save_fil.php`, `alt_fil.php`, `load_fila.php`
- `save_ass.php`, `alt_ass.php`, `load_assunto.php`
- `save_ctt.php`, `alt_ctt.php`, `load_contrato.php`
- `save_pri.php`, `alt_pri.php`, `del_pri.php`
- `save_faq_config.php`, `alt_faq_config.php`

## 3.4 Arquivos e mensagens de grupo
- `save_file.php`, `load_file.php`
- `save_file_com.php`, `load_file_com.php`
- `save_file_grupo.php`, `load_file_grupo.php`
- `save_msg_com.php`, `save_msg_com_ind.php`
- `send_msg_massa.php`

## 4. Entidades de dados principais (inferidas do codigo e SQL)

## 4.1 Seguranca e usuarios
- `tbl_user`
- `tbl_nivel`
- `tbl_permissao`
- `tbl_user_pass`
- `tbl_user_pass_config`
- `tbl_user_img_perfil`
- `tbl_log_diario`

## 4.2 Atendimento e comunicacao
- `tbl_chat_fila`
- `tbl_chat_info`
- `tbl_chat_msg`
- `tbl_chat_files`
- `tbl_pause`
- `tbl_tma_atend`
- `tbl_log_atendimento`

## 4.3 Configuracao operacional
- `tbl_config_fila`
- `tbl_assunto`
- `tbl_faq`
- `tbl_config_men_ini`
- `tbl_situacao_chat`
- `tbl_classificacao`
- `tbl_pend_info`

## 4.4 Organizacao e estrutura
- `tbl_contrato`
- `tbl_regional`
- `tbl_empresa`
- `tbl_agencia`
- `tbl_municipio`
- `tbl_estado`

## 4.5 Monitoria e formularios dinamicos
- `tbl_forms_pos_input*`
- `tbl_forms_mon_input*`
- `tbl_in_pos_*_*`
- `tbl_in_mon_*_*`

## 5. Bibliotecas e componentes relevantes

- jQuery
- Bootstrap 5
- DataTables
- TinyMCE
- SweetAlert2
- Toastify
- amCharts
- PHPMailer
- WebSocket (cliente JS)

## 6. Observacoes de engenharia encontradas

- Arquitetura procedural com acoplamento entre camada de tela e SQL.
- Uso de includes dinamicos para roteamento.
- Presenca de varios arquivos de backup no repositorio com sufixos de data e `_`.
- Regras de negocio distribuidas em varios endpoints `staff`.
- Relatorios com alto custo potencial de consulta (existem scripts de indices no projeto).

## 7. Riscos tecnicos mapeados (para tratar na nova versao)

- Credenciais de banco embutidas em arquivos de configuracao.
- Algoritmo de hash legado e fluxo de autenticacao com pontos frageis.
- SQL por concatenacao em multiplos pontos (risco de injecao).
- Autorizacao em alguns fluxos mais baseada em UI do que em verificacao robusta de endpoint.
- Falta de padronizacao de respostas de erro/sucesso nos endpoints.

## 8. Recomendacoes imediatas de baseline para reescrita

- Definir matriz oficial de modulos e ownership tecnico.
- Catalogar endpoints oficiais e descontinuar backups legados.
- Criar dicionario de dados formal (tabela, coluna, regra de negocio).
- Mapear contratos de API interna (request/response por endpoint).
- Priorizar hardening de autenticacao, sessao e SQL antes da expansao funcional.
