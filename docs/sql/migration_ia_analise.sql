-- Análise diária com IA (D+1) — SolveTask piloto_2.0
-- Executar no banco web_chatlogos_piloto_20 (ou equivalente)

-- Chave OpenAI em tbl_config_sis (se já existir, ignorar erro)
ALTER TABLE `tbl_config_sis`
  ADD COLUMN `openai_api_key` VARCHAR(512) DEFAULT NULL COMMENT 'Chave API OpenAI para insights' AFTER `tempoDash`;

-- Snapshots diários de métricas + texto da IA
CREATE TABLE IF NOT EXISTS `tbl_ia_analise_diaria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ref_dia` date NOT NULL COMMENT 'Dia analisado (sempre D-1 em relação à geração)',
  `contrato_id` int(11) NOT NULL DEFAULT 0 COMMENT '0 = todos os contratos',
  `fila_id` int(11) NOT NULL DEFAULT 0 COMMENT '0 = todas as filas do contrato',
  `dados_json` longtext NOT NULL COMMENT 'Métricas agregadas do dia (JSON)',
  `analise_ia` text DEFAULT NULL COMMENT 'Texto gerado pela IA',
  `status_ia` varchar(20) NOT NULL DEFAULT 'ok' COMMENT 'ok|sem_chave|erro|limite',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ia_dia_escopo` (`ref_dia`,`contrato_id`,`fila_id`),
  KEY `idx_ia_ref_dia` (`ref_dia`),
  KEY `idx_ia_contrato` (`contrato_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Log de limites/erros da API OpenAI
CREATE TABLE IF NOT EXISTS `tbl_ia_log_limite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ref_dia` date DEFAULT NULL,
  `contrato_id` int(11) DEFAULT 0,
  `fila_id` int(11) DEFAULT 0,
  `http_code` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'limite',
  `mensagem` text DEFAULT NULL,
  `resposta_api` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ia_log_data` (`data_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resumo textual da IA por período (filtro do painel)
CREATE TABLE IF NOT EXISTS `tbl_ia_analise_periodo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `de_dia` date NOT NULL,
  `ate_dia` date NOT NULL,
  `contrato_id` int(11) NOT NULL DEFAULT 0,
  `fila_id` int(11) NOT NULL DEFAULT 0,
  `dados_hash` varchar(64) NOT NULL COMMENT 'Hash SHA-256 das métricas consolidadas',
  `analise_ia` text DEFAULT NULL,
  `status_ia` varchar(20) NOT NULL DEFAULT 'ok',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_ia_periodo_escopo` (`de_dia`,`ate_dia`,`contrato_id`,`fila_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
