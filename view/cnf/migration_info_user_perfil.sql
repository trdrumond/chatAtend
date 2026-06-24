-- Corrige carregamento do perfil (session.php / info_user / tbl_nivel)
-- Banco: web_chatlogos_piloto

USE web_chatlogos_piloto;

-- Colunas de menu/mosaico usadas em view/cnf/session.php
ALTER TABLE `tbl_nivel`
  ADD COLUMN IF NOT EXISTS `mosaico` varchar(255) DEFAULT NULL COMMENT 'Demandas, usuario, configuracao' AFTER `idx`,
  ADD COLUMN IF NOT EXISTS `menu_idx` varchar(255) DEFAULT NULL AFTER `mosaico`,
  ADD COLUMN IF NOT EXISTS `menu_cnf` varchar(255) DEFAULT NULL AFTER `menu_idx`,
  ADD COLUMN IF NOT EXISTS `cad_cnf` int(1) NOT NULL DEFAULT 0 AFTER `menu_cnf`,
  ADD COLUMN IF NOT EXISTS `ativo` int(1) NOT NULL DEFAULT 1 AFTER `cad_cnf`;

-- Valores padrao por nivel (flags 0/1 separados por virgula)
UPDATE `tbl_nivel` SET
  `mosaico` = '1,1,1',
  `menu_idx` = '1,1,1,1,1,1,1',
  `menu_cnf` = '1,1,1,1,1,1,1,1,1,1,1',
  `cad_cnf` = 1
WHERE `id_nivel` IN (0, 1);

UPDATE `tbl_nivel` SET
  `mosaico` = '1,0,0',
  `menu_idx` = '1,1,1,1,1,0,1',
  `menu_cnf` = '0,0,0,0,0,0,0,0,0,0,0',
  `cad_cnf` = 0
WHERE `id_nivel` = 2;

UPDATE `tbl_nivel` SET
  `mosaico` = '1,0,0',
  `menu_idx` = '0,1,1,0,1,1,0',
  `menu_cnf` = '0,0,0,0,0,0,0,0,0,0,0',
  `cad_cnf` = 0
WHERE `id_nivel` = 4;

UPDATE `tbl_nivel` SET
  `mosaico` = '1,0,0',
  `menu_idx` = '0,0,0,0,0,0,0',
  `menu_cnf` = '0,0,0,0,0,0,0,0,0,0,0',
  `cad_cnf` = 0
WHERE `id_nivel` = 5;

DROP VIEW IF EXISTS `info_user`;

CREATE VIEW `info_user` AS
SELECT
  a.id_user,
  a.nome_usuario,
  a.nome,
  a.senha_usuario,
  a.sobrenome,
  CONCAT(a.nome, ' ', a.sobrenome) AS nome_completo,
  a.contrato_id,
  a.contrato_id AS id_contrato,
  (SELECT nome_contrato FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS contrato,
  a.municipio_id,
  (SELECT nome_municipio FROM tbl_municipio WHERE id_municipio = a.municipio_id) AS municipio,
  a.agencia_id,
  a.regional_id,
  (SELECT nome_agencia FROM tbl_agencia WHERE id_agencia = a.agencia_id) AS agencia,
  a.uf_id,
  (SELECT nome_estado FROM tbl_estado WHERE id_estado = a.uf_id) AS uf,
  (SELECT uf FROM tbl_estado WHERE id_estado = a.uf_id) AS ufd,
  a.token,
  a.nivel_id,
  (SELECT nome_nivel FROM tbl_nivel WHERE id_nivel = a.nivel_id) AS nivel,
  (SELECT idx FROM tbl_nivel WHERE id_nivel = a.nivel_id) AS idx,
  (SELECT icon FROM tbl_nivel WHERE id_nivel = a.nivel_id) AS icon,
  (SELECT img FROM tbl_user_img_perfil WHERE user_id = a.id_user) AS img_perfil,
  a.fila_id,
  (SELECT multichat FROM tbl_config_fila WHERE id_fila = a.fila_id) AS multichat,
  (SELECT ativo FROM tbl_config_fila WHERE id_fila = a.fila_id) AS fila_status,
  (SELECT com FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS comunicacao,
  (SELECT new_conv FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS new_conv,
  (SELECT grupos FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS grupos,
  (SELECT men_massa FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS men_massa,
  (SELECT resp_men FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS resp_men,
  (SELECT env_img FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS env_img,
  (SELECT env_file FROM tbl_contrato WHERE id_contrato = a.contrato_id) AS env_file,
  a.flag_pass
FROM tbl_user a;
