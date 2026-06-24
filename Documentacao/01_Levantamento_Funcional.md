# 01 - Levantamento Funcional Detalhado

## 1. Visao geral do sistema

O Solvetask (base `piloto`) e uma plataforma de atendimento por chat com controle de fila, operacao de backoffice, monitoria, pos-atendimento e modulo administrativo completo.

Principais caracteristicas:
- atendimento em tempo real com WebSocket;
- abertura e distribuicao de chamados por fila e assunto;
- dashboards operacionais e relatorios;
- administracao de cadastros e parametros;
- controle de acesso por nivel e permissoes.

## 2. Modulos funcionais do core

## 2.1 Autenticacao, sessao e autorizacao

Arquivos principais:
- `access/login_chat.php`
- `login.php`
- `view/cnf/session.php`
- `view/cnf/session_config.php`
- `view/logout.php`

Capacidades:
- login por usuario/senha/contrato;
- inicializacao de sessao e contexto do usuario;
- carga de permissoes por nivel e permissoes individuais;
- redirecionamento por perfil (`idx`, `cnf`, `usu`);
- controle de troca de senha obrigatoria.

## 2.2 Roteamento dinamico da aplicacao

Arquivos principais:
- `view/index.php`
- `view/action.php`
- `view/page/page-idx.php`
- `view/page/page-cnf.php`
- `view/page/page-usu.php`

Capacidades:
- roteamento por secao (`sec`) e acao (`action`);
- montagem de tela por includes dinamicoss;
- carregamento de scripts CSS/JS conforme secao;
- renderizacao condicional por nivel/permissao.

## 2.3 Atendimento e ciclo de chat

Paginas e arquivos principais:
- `view/page/action/idx/dash-cha.php` (entrada do solicitante)
- `view/page/action/idx/chat-fila.php` (espera em fila)
- `view/page/action/idx/chat-ate.php` (chat solicitante)
- `view/page/action/idx/chat-bko.php` (chat backoffice)
- `view/chat/chat_ind.php` (interface de chat individual)
- `view/chat/assets/js/script.js` (tempo real)

Funcionalidades:
- abertura de chamada por fila/assunto;
- protocolo de atendimento;
- espera e acompanhamento de posicao;
- atendimento por agente backoffice;
- troca de mensagens em tempo real;
- notificacoes de mensagem;
- encerramento de atendimento;
- transferencia entre filas/agentes;
- avaliacao e classificacao pos-atendimento.

## 2.4 Gestao de filas

Arquivos principais:
- `view/page/action/idx/dash-fila.php`
- `view/staff/load_fila_ativa.php`
- `view/staff/load_chat_ate.php`
- `view/staff/load_cancel_fila.php`
- `view/staff/save_call.php`

Funcionalidades:
- configuracao e consulta de filas ativas;
- enfileiramento de solicitacoes;
- mudanca de estado da fila;
- cancelamento de espera;
- identificacao de atendente responsavel.

## 2.5 Pausa, ociosidade e tempos operacionais

Arquivos principais:
- `view/page/action/idx/dash-pause.php`
- `view/staff/save_pause.php`
- `view/staff/load_ta.php`
- `view/cnf/rotina_ocio.php`
- `view/cnf/horario_fila.php`

Funcionalidades:
- controle de entrada/saida de pausa;
- medicao de tempos de atendimento;
- suporte a calculo de produtividade;
- regras de disponibilidade operacional.

## 2.6 Pos-atendimento e monitoria

Arquivos principais:
- `view/staff/save_pos.php`
- `view/staff/save_class.php`
- `view/staff/load_monitoria.php`
- `view/staff/save_mon.php`
- `view/staff/pos_config_form.php`
- `view/staff/mon_config_form.php`

Funcionalidades:
- registro de classificacao final;
- registro de pendencias e situacoes;
- formularios dinamicos de pos-atendimento;
- monitoria da qualidade do atendimento;
- calculo de nota/avaliacao de monitoria.

## 2.7 Comunicacao em grupo e mensagens rapidas

Arquivos principais:
- `view/page/action/idx/com-idx.php`
- `view/page/action/idx/com-idx-list.php`
- `view/chat/chat_com.php`
- `view/chat/chat_com_ind.php`
- `view/staff/save_msg_com.php`
- `view/staff/save_msg_com_ind.php`
- `view/staff/send_msg_massa.php`

Funcionalidades:
- comunicacao por grupos;
- lista e historico de conversas;
- mensagens em massa;
- mensagens iniciais/configuraveis por contrato/fila.

## 2.8 Upload e compartilhamento de arquivos

Arquivos principais:
- `view/staff/save_file.php`
- `view/staff/save_file_com.php`
- `view/staff/save_file_grupo.php`
- `view/staff/load_file.php`
- `view/staff/load_file_com.php`
- `view/staff/load_file_grupo.php`

Funcionalidades:
- envio de anexos no chat individual e em grupo;
- persistencia e recuperacao de arquivos;
- associacao do arquivo ao contexto da conversa.

## 2.9 Administracao de cadastros (configuracao)

Paginas principais em `view/page/action/cnf`:
- `cad-usu.php` (usuarios)
- `cad-fil.php` (filas)
- `cad-ass.php` (assuntos)
- `cad-faq.php` (FAQ)
- `cad-men.php` (mensagens)
- `cad-pri.php` (prioridades)
- `cad-ctt.php` (contratos)
- `cad-reg.php` (regional)
- `cad-emp.php` (empresa)
- `cad-age.php` (agencia)

Funcionalidades:
- CRUD de entidades administrativas;
- ativacao/desativacao;
- vinculos entre contrato, fila, assunto e estrutura organizacional;
- configuracao de flags de comunicacao por contrato.

## 2.10 Dashboards, indicadores e relatorios

Arquivos principais:
- `view/page/action/cnf/cnf-dash.php`
- `view/page/action/idx/dash-idx.php`
- `view/page/action/idx/rel-dash.php`
- `view/page/action/idx/rel-fila.php`
- `view/page/action/idx/rel-ind.php`
- `view/staff/load_dados_rel.php`
- `view/staff/load_dados_rel_fila.php`
- `view/staff/load_dados_ind.php`

Funcionalidades:
- indicadores operacionais e gerenciais;
- relatorios por periodo/contrato/fila;
- consolidacao de produtividade e tempos;
- exportacoes e visualizacoes analiticas.

## 2.11 API e painel

Arquivos principais:
- `api/index.php`
- `view/staff/load_api.php`
- `painel/index.php`
- `view/staff/load_painel.php`

Funcionalidades:
- exposicao de dados por API simples;
- autenticacao de usuario de API;
- painel operacional com status em tempo quase real.

## 3. Regras funcionais observadas no comportamento

- Usuarios de nivel solicitante (nivel 5) entram no fluxo de abertura e acompanhamento de atendimento.
- Usuarios de backoffice (nivel 4) atuam no fluxo de atendimento, pausa, transferencia e encerramento.
- Niveis administrativos acessam configuracoes e relatorios com menus controlados por `tbl_nivel` e `tbl_permissao`.
- O sistema utiliza estados de fila e chat para controlar transicoes operacionais.
- Parte relevante da operacao depende de polling + WebSocket.

## 4. Dependencias de negocio principais (dominio)

- Contrato
- Fila
- Assunto
- Usuario e Nivel
- Atendimento (chat/protocolo)
- Pausa
- Monitoria
- Pos-atendimento
- Mensagens de suporte (FAQ, mensagem inicial, massa, grupos)

## 5. Conclusao funcional para continuidade

O core atual cobre o ciclo completo de atendimento digital (entrada, fila, atendimento, encerramento e analise posterior), incluindo operacao e administracao. A nova versao deve preservar os fluxos criticos e evoluir arquitetura, seguranca, rastreabilidade e manutenibilidade.
