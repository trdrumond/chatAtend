# Baseline SQL da Versao Atual

Este documento define os arquivos SQL oficiais que devem orientar a evolucao da proxima versao do Solvetask em modelo de atualizacao (nao substituicao total).

## Fontes oficiais

- `piloto/Documentacao/BD/bd_piloto.sql`
  - Baseline principal oficial da versao atual.
  - Deve ser tratado como ponto de partida obrigatorio para evolucao do schema e dados base.

- `piloto/view/cnf/bd/web_chatlogos.sql`
  - Referencia complementar/legada para cruzamento de estrutura historica.

- `piloto/view/cnf/bd/idx_relatorio_base.sql`
  - Indices recomendados para consultas de relatorio.

- `piloto/dashboard_indexes.sql`
  - Indices adicionais para desempenho de dashboards e consultas operacionais.

- `piloto/docs/CREATE VIEWS.sql`
  - Views de suporte a leitura analitica.

## Diretrizes para a nova versao

- Tratar `bd_piloto.sql` como baseline primario e os demais SQLs como complementares.
- Aplicar mudancas via migracoes versionadas e reversiveis.
- Preservar compatibilidade de dados com os fluxos atuais (fila, atendimento, chat, relatorios, monitoria).
- Evitar quebra de contrato de dados sem plano de migracao e validacao.

## Checklist minimo de migracao

- Inventariar tabelas e chaves efetivamente usadas por cada modulo.
- Validar impacto de alteracoes de schema em relatorios existentes.
- Reproduzir indices essenciais antes de testes de carga.
- Garantir scripts de rollback para cada alteracao estrutural.
