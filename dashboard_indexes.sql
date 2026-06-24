-- Índices para otimizar telas de dashboard / fila / online
-- Execute este arquivo no banco da aplicação (ex.: via mysql, phpMyAdmin, etc.).
-- Cada bloco só cria o índice se ele ainda não existir.

SET @old_sql_notes := @@sql_notes;

SET sql_notes = 0;

-- 1) tbl_user: filtros por ativo/nivel/fila/contrato (painel online)
SET @idx_name := 'idx_user_painel';

SET @tbl_name := 'tbl_user';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (ativo, nivel_id, fila_id, contrato_id)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 2) tbl_log_atendimento: consultas por usuário + data + ação (status do dia)
SET @idx_name := 'idx_log_atendimento_user_data';

SET @tbl_name := 'tbl_log_atendimento';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (user_id, data_hora, acao)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 3) tbl_log_diario: status online/offline por usuário no dia
SET @idx_name := 'idx_log_diario_data_user';

SET @tbl_name := 'tbl_log_diario';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (data_log, user_id)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 4) tbl_chat_fila: atendimentos ativos por status e responsável (dashboard / fila ativa)
SET @idx_name := 'idx_chat_fila_status_resp';

SET @tbl_name := 'tbl_chat_fila';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (status_fila, bko_resp)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 5) tbl_classificacao: média de estrelas por atendente ao longo do tempo
SET @idx_name := 'idx_classificacao_ate_data';

SET @tbl_name := 'tbl_classificacao';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (ate, data_hora)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 6) tbl_tma_atend: TMA por atendente (mantido para uso futuro, mesmo não estando em uso agora)
SET @idx_name := 'idx_tma_atend_resp_datas';

SET @tbl_name := 'tbl_tma_atend';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (resp_id, date_out, date_disp, date_in)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------------------------------
-- ÍNDICES DO RELATÓRIO DE BASE (view/cnf/bd/idx_relatorio_base.sql)
-- Convertidos para o padrão “cria se não existir” e consolidados aqui.
-- -----------------------------------------------------------------------------

-- 7) tbl_chat_fila_secondary: filtros por contrato e intervalo de data
SET @idx_name := 'idx_contrato_data';

SET @tbl_name := 'tbl_chat_fila_secondary';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (contrato_id, data_hora)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 8) tbl_chat_fila_secondary: quando também filtrar por fila
SET @idx_name := 'idx_contrato_fila_data';

SET @tbl_name := 'tbl_chat_fila_secondary';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (contrato_id, fila_id, data_hora)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 9) tbl_chat_fila: usado em TMA/TME do relatório de base
SET @idx_name := 'idx_contrato_data';

SET @tbl_name := 'tbl_chat_fila';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (contrato_id, data_hora)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 10) tbl_chat_fila: contrato + fila + data
SET @idx_name := 'idx_contrato_fila_data';

SET @tbl_name := 'tbl_chat_fila';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (contrato_id, fila_id, data_hora)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 11) tbl_classificacao: busca por chat_fila_id (carregamento em lote)
SET @idx_name := 'idx_chat_fila_id';

SET @tbl_name := 'tbl_classificacao';

SELECT IF(
        NOT EXISTS (
            SELECT 1
            FROM information_schema.statistics
            WHERE
                table_schema = DATABASE()
                AND table_name = @tbl_name
                AND index_name = @idx_name
        ), CONCAT(
            'ALTER TABLE ', @tbl_name, ' ADD INDEX ', @idx_name, ' (chat_fila_id)'
        ), 'SELECT 1'
    ) INTO @sql;

PREPARE stmt FROM @sql;

EXECUTE stmt;

DEALLOCATE PREPARE stmt;

-- 12) Tabelas dinâmicas tbl_in_pos_{fila_id}_{contrato_id}: índice em chat_id
--     Não há como automatizar o nome das tabelas aqui sem uma procedure,
--     então mantenho como orientação (um exemplo):
--
--     ALTER TABLE tbl_in_pos_1_16 ADD INDEX idx_chat_id (chat_id);

SET sql_notes = @old_sql_notes;