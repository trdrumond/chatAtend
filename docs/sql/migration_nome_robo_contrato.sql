-- Nome do robô virtual por contrato (tela de fila / dash-cha)
-- Banco: web_chatlogos_piloto

USE web_chatlogos_piloto_20;

ALTER TABLE `tbl_contrato`
  ADD COLUMN IF NOT EXISTS `nome_robo` varchar(80) DEFAULT NULL
  COMMENT 'Nome exibido no assistente virtual da fila; vazio = Robô Logos'
  AFTER `env_img`;
