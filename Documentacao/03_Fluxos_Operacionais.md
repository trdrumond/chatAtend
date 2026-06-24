# 03 - Fluxos Operacionais do Solvetask

## 1. Fluxo de autenticacao e entrada

## 1.1 Passos
1. Usuario envia credenciais em `access/login_chat.php`.
2. Sistema valida usuario e prepara redirecionamento para `login.php`.
3. `login.php` inicia sessao e carrega contexto de usuario.
4. `view/cnf/session.php` aplica permissoes, logs e regras de senha.
5. Usuario entra em `view/index.php` na secao correspondente.

## 1.2 Resultado do fluxo
- Sessao ativa com dados de perfil.
- Menus e funcionalidades carregados conforme nivel/permissao.

## 2. Fluxo de abertura de chamado (solicitante)

Arquivos base:
- `view/page/action/idx/dash-cha.php`
- `view/staff/save_call.php`

## 2.1 Passos
1. Solicitante seleciona fila e assunto.
2. Sistema registra atendimento na fila e gera protocolo.
3. Atendimento entra em estado de espera.
4. Solicitante e direcionado para tela de fila (`chat-fila`).

## 3. Fluxo de espera em fila

Arquivos base:
- `view/page/action/idx/chat-fila.php`
- `view/staff/load_chat_ate.php`
- `view/staff/load_cancel_fila.php`

## 3.1 Passos
1. Tela consulta periodicamente status da fila.
2. Se atendimento for assumido, usuario e enviado para `chat-ate`.
3. Usuario pode cancelar a espera, alterando estado do atendimento.

## 4. Fluxo de atendimento backoffice

Arquivos base:
- `view/page/action/idx/chat-bko.php`
- `view/staff/chat-bko.php`
- `view/page/action/idx/chat-ate.php`
- `view/staff/save_msg.php`

## 4.1 Passos
1. Backoffice assume item da fila.
2. Sistema altera estado para em atendimento.
3. Solicitante e backoffice trocam mensagens via chat.
4. Mensagens sao persistidas e sincronizadas em tempo real.

## 5. Fluxo de pausa operacional

Arquivos base:
- `view/page/action/idx/dash-pause.php`
- `view/staff/save_pause.php`

## 5.1 Passos
1. Operador entra em pausa.
2. Sistema registra inicio e motivo/tipo de pausa.
3. Operador retorna e sistema registra fim da pausa.

## 6. Fluxo de transferencia

Arquivo base:
- `view/staff/save_msg_transfer.php`

## 6.1 Passos
1. Backoffice aciona transferencia durante o atendimento.
2. Sistema fecha contexto atual e abre novo contexto operacional.
3. Atendimento retorna para fila/novo destino conforme regra.
4. Historico da transferencia e mantido para rastreio.

## 7. Fluxo de encerramento

Arquivo base:
- `view/staff/save_msg_fim.php`

## 7.1 Passos
1. Backoffice encerra atendimento.
2. Sistema grava tempos finais e estado de conclusao.
3. Atendimento segue para etapa de pos-atendimento quando aplicavel.

## 8. Fluxo de pos-atendimento e classificacao

Arquivos base:
- `view/staff/save_pos.php`
- `view/staff/save_class.php`

## 8.1 Passos
1. Usuario/operador preenche dados finais.
2. Sistema salva classificacao, pendencias e campos dinamicos.
3. Dados ficam disponiveis para relatorios.

## 9. Fluxo de monitoria

Arquivos base:
- `view/staff/load_monitoria.php`
- `view/staff/save_mon.php`

## 9.1 Passos
1. Monitor seleciona atendimento para avaliar.
2. Formulario de monitoria e carregado conforme configuracao da fila.
3. Respostas sao salvas e sistema calcula nota.
4. Avaliacao integra indicadores e relatorios de qualidade.

## 10. Fluxo de comunicacao em grupo

Arquivos base:
- `view/page/action/idx/com-idx.php`
- `view/chat/chat_com.php`
- `view/staff/save_msg_com.php`

## 10.1 Passos
1. Usuario acessa ambiente de comunicacao em grupo.
2. Mensagens sao trocadas e persistidas por conversa/grupo.
3. Historico e carregado sob demanda.

## 11. Fluxo de relatorios e indicadores

Arquivos base:
- `view/page/action/idx/rel-dash.php`
- `view/staff/load_dados_rel.php`
- `view/page/action/idx/rel-fila.php`
- `view/staff/load_dados_rel_fila.php`
- `view/page/action/idx/rel-ind.php`
- `view/staff/load_dados_ind.php`

## 11.1 Passos
1. Usuario filtra periodo e contexto (contrato/fila).
2. Sistema agrega dados de atendimento, tempos e qualidade.
3. Resultado e exibido em tabelas/graficos e exportacoes.

## 12. Maquina de estados (visao simplificada)

Estados operacionais observados:
- `Fila/Aguardando`
- `Em atendimento`
- `Pausa`
- `Transferido`
- `Concluido`
- `Cancelado`

Transicoes principais:
- abertura -> fila
- fila -> atendimento
- atendimento -> pausa -> atendimento
- atendimento -> transferido -> fila/novo atendimento
- atendimento -> concluido
- fila -> cancelado

## 13. Fluxos criticos para validacao na nova versao

- login e autorizacao por perfil;
- abertura de chamado e entrada em fila;
- assuncao pelo backoffice e troca de mensagens;
- encerramento com gravacao de tempos;
- relatorios coerentes com o ciclo operacional;
- monitoria e pos-atendimento.
