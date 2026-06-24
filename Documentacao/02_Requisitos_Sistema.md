# 02 - Requisitos do Sistema (Base Atual e Evolucao)

## 1. Requisitos funcionais (RF)

## RF-01 Autenticacao e sessao
- Permitir login por usuario/senha/contrato.
- Iniciar sessao autenticada com dados de perfil.
- Encerrar sessao por logout.
- Forcar troca de senha quando politica exigir.

## RF-02 Controle de acesso
- Controlar menus e acoes por nivel de usuario.
- Aplicar permissoes individuais por usuario.
- Restringir acesso as areas `idx`, `cnf` e `usu` conforme perfil.

## RF-03 Abertura de atendimento
- Permitir abertura de atendimento por fila e assunto.
- Gerar protocolo para cada atendimento.
- Registrar metadados de abertura (usuario, data/hora, motivo quando aplicavel).

## RF-04 Gestao de fila
- Inserir chamados em fila com estado inicial.
- Exibir posicao/status da fila ao solicitante.
- Permitir cancelamento da espera.
- Mover chamado para atendimento quando backoffice assumir.

## RF-05 Atendimento em chat individual
- Permitir troca de mensagens entre solicitante e backoffice.
- Exibir notificacoes e atualizacoes em tempo real.
- Registrar historico da conversa.
- Permitir encerramento do atendimento.

## RF-06 Transferencia de atendimento
- Permitir transferencia entre filas e/ou responsaveis.
- Registrar evento de transferencia e manter rastreabilidade.
- Reenfileirar atendimento quando aplicavel.

## RF-07 Pausa operacional
- Permitir iniciar e finalizar pausa de operador.
- Registrar tempo e tipo de pausa.
- Refletir indisponibilidade no painel operacional.

## RF-08 Pos-atendimento
- Coletar classificacao final do atendimento.
- Registrar pendencias e situacoes.
- Salvar campos dinamicos de formulario de pos.

## RF-09 Monitoria
- Permitir avaliacao de atendimentos por formulario configuravel.
- Registrar respostas e nota da monitoria.
- Disponibilizar monitoria em relatorios.

## RF-10 Comunicacao em grupo e massa
- Permitir chat em grupo.
- Permitir envio de mensagens em massa quando habilitado.
- Exibir historico e listas de comunicacao.

## RF-11 Upload de arquivos
- Permitir envio de arquivos no chat individual e em grupo.
- Validar anexos conforme regras de tipo/tamanho.
- Manter vinculo entre arquivo e atendimento/conversa.

## RF-12 Cadastros administrativos
- Gerenciar usuarios, contratos, filas, assuntos, prioridades, FAQ, mensagens rapidas.
- Gerenciar estrutura organizacional (regional, empresa, agencia, municipio/UF).
- Ativar/desativar registros sem perda historica.

## RF-13 Dashboards e relatorios
- Exibir indicadores operacionais e gerenciais.
- Permitir filtros por periodo, contrato, fila e usuario.
- Disponibilizar relatorios de base, fila, individual e monitoria.
- Permitir exportacoes para analise externa.

## RF-14 API de consulta
- Expor dados de atendimento por API autenticada.
- Retornar dados estruturados (JSON) para integracao.

## 2. Requisitos nao funcionais (RNF)

## RNF-01 Performance
- Atualizacao de status de fila e chat em tempo aceitavel para operacao.
- Suporte a concorrencia de multiplos usuarios simultaneos por contrato.
- Consultas de relatorio otimizadas com indices apropriados.

## RNF-02 Disponibilidade
- Sistema disponivel no horario de operacao do atendimento.
- Tratamento de reconexao para eventos de tempo real.

## RNF-03 Seguranca
- Proteger credenciais e sessoes.
- Evitar SQL injection e manipulacao indevida de parametros.
- Garantir autorizacao no backend para endpoints sensiveis.
- Registrar logs de acesso e operacao.

## RNF-04 Auditabilidade e rastreabilidade
- Rastrear eventos-chave: login, abertura, assuncao, pausa, transferencia, encerramento.
- Manter historico consultavel para auditoria.

## RNF-05 Usabilidade
- Interface web responsiva para operacao continua.
- Fluxos com feedback claro (loading, notificacao, sucesso/erro).

## RNF-06 Manutenibilidade
- Codigo modular por dominio funcional.
- Reducao de duplicidade de scripts e backups manuais no codigo.
- Padronizacao de contratos de endpoint e nomes de parametros.

## RNF-07 Escalabilidade evolutiva
- Separacao progressiva entre camada de apresentacao, negocio e dados.
- Preparacao para APIs mais robustas e desacopladas na nova versao.

## 3. Requisitos de dados (RD)

## RD-01 Entidades essenciais
- Usuario, nivel, permissao
- Contrato, fila, assunto, prioridade
- Atendimento (fila/chat/info/mensagem)
- Pausa, classificacao, monitoria, pendencia
- Logs e configuracoes de sistema

## RD-02 Integridade
- Manter consistencia entre estados de fila e chat.
- Garantir relacao correta entre atendimento, mensagens e anexos.
- Preservar historico para relatorios.

## RD-03 Historico e retencao
- Preservar dados historicos para analise operacional.
- Definir politicas de expurgo e arquivamento no redesenho.

## RD-04 Baseline SQL obrigatoria para continuidade
- Considerar `piloto/Documentacao/BD/bd_piloto.sql` como baseline principal de estrutura e dados de apoio.
- Considerar `piloto/view/cnf/bd/web_chatlogos.sql` como referencia complementar/legada.
- Considerar `piloto/view/cnf/bd/idx_relatorio_base.sql` e `piloto/dashboard_indexes.sql` como baseline de performance para relatorios.
- Considerar `piloto/docs/CREATE VIEWS.sql` como baseline de visoes de consulta.
- Toda evolucao deve partir de migracoes incrementais sobre `bd_piloto.sql`, sem descaracterizar entidades criticas do sistema atual.

## 4. Requisitos de evolucao para a nova versao

## REV-01 Seguranca obrigatoria
- Substituir hash legado de senha por algoritmo robusto.
- Eliminar credenciais hardcoded no codigo.
- Aplicar validacao server-side e autorizacao por endpoint.

## REV-02 Arquitetura
- Adotar camadas claras (controller/service/repository ou equivalente).
- Isolar regras de negocio do HTML/JS.
- Padronizar retorno de API e tratamento de erros.
- Planejar camada de migracao/compatibilidade para estrutura SQL legada.

## REV-03 Observabilidade
- Criar logs estruturados por evento de negocio.
- Adicionar metricas de desempenho por fluxo.

## REV-04 Qualidade
- Criar suite minima de testes para fluxos criticos.
- Definir estrategia de migracao controlada (paralela por modulo).

## 5. Criterios de aceite macro para continuidade

- Fluxo completo de atendimento executa sem regressao funcional.
- Dados de relatorio mantem coerencia com sistema atual.
- Permissoes por perfil permanecem equivalentes ou mais seguras.
- Principais riscos tecnicos da base atual sao mitigados na nova arquitetura.
