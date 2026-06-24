# Solvetask Piloto - Documentacao de Levantamento

Este diretorio consolida o levantamento tecnico-funcional do core do sistema `piloto`, com foco em servir de base para a nova versao.

## Objetivo

Documentar:
- funcionalidades atuais;
- requisitos funcionais e nao funcionais;
- fluxos operacionais ponta a ponta;
- inventario tecnico (arquivos, endpoints, tabelas e riscos).

## Escopo analisado

- `piloto/access`
- `piloto/view`
- `piloto/api`
- `piloto/painel`
- scripts SQL em `piloto/view/cnf/bd` e `piloto/dashboard_indexes.sql`

## Baseline de banco de dados para evolucao

Arquivo principal oficial:

- `piloto/Documentacao/BD/bd_piloto.sql` (baseline principal de estrutura e registros de apoio)

Arquivos complementares de referencia:

- `piloto/view/cnf/bd/web_chatlogos.sql` (estrutura principal + dados de apoio)
- `piloto/view/cnf/bd/idx_relatorio_base.sql` (indices de suporte a relatorios)
- `piloto/dashboard_indexes.sql` (indices adicionais de performance)
- `piloto/docs/CREATE VIEWS.sql` (views de apoio analitico)

Diretriz: a nova versao deve usar `bd_piloto.sql` como base primaria, preservando compatibilidade de dados e aplicando migracoes incrementais sem ruptura com as entidades e regras atuais.

## Documentos

- `01_Levantamento_Funcional.md`: visao detalhada por modulo e funcionalidades atuais.
- `02_Requisitos_Sistema.md`: requisitos funcionais e nao funcionais para continuidade/evolucao.
- `03_Fluxos_Operacionais.md`: fluxos de autenticacao, atendimento, fila, pausa, transferencia e encerramento.
- `04_Inventario_Tecnico.md`: mapa de arquivos chave, endpoints staff, entidades e pontos de atencao.

## Notas importantes para a nova versao

- A base atual e majoritariamente procedural em PHP com forte acoplamento tela-endpoint-SQL.
- Existem arquivos com sufixos de backup (datas e `_`) no codigo fonte; devem ser tratados como historico tecnico e nao como fonte oficial.
- A nova versao deve usar esta documentacao como baseline para especificacao de arquitetura alvo.
