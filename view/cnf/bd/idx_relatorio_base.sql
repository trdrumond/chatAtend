-- Índices recomendados para o relatório de base (load_dados_rel.php)
-- Executar no banco conforme necessário. Verifique se o índice já existe antes de criar.

-- tbl_chat_fila_secondary: filtros por contrato e intervalo de data
ALTER TABLE tbl_chat_fila_secondary
  ADD INDEX idx_contrato_data (contrato_id, data_hora);

-- Opcional: quando filtrar também por fila
ALTER TABLE tbl_chat_fila_secondary
  ADD INDEX idx_contrato_fila_data (contrato_id, fila_id, data_hora);

-- tbl_chat_fila: usado na consulta de TMA/TME do relatório
ALTER TABLE tbl_chat_fila
  ADD INDEX idx_contrato_data (contrato_id, data_hora);

ALTER TABLE tbl_chat_fila
  ADD INDEX idx_contrato_fila_data (contrato_id, fila_id, data_hora);

-- tbl_classificacao: busca por chat_fila_id (carregamento em lote)
ALTER TABLE tbl_classificacao
  ADD INDEX idx_chat_fila_id (chat_fila_id);

-- Tabelas dinâmicas tbl_in_pos_{fila_id}_{contrato_id}: índice em chat_id
-- Executar para cada tabela existente do tipo tbl_in_pos_* (ex.: tbl_in_pos_1_16)
-- ALTER TABLE tbl_in_pos_1_16 ADD INDEX idx_chat_id (chat_id);
