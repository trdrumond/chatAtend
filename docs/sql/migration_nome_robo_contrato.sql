-- Nome do robô virtual por contrato (tela de fila / dash-cha)
-- Banco: web_chatlogos_piloto


ALTER TABLE `tbl_contrato`
  ADD COLUMN IF NOT EXISTS `nome_robo` varchar(80) DEFAULT NULL
  COMMENT 'Nome exibido no assistente virtual da fila; vazio = Logosnildson'
  AFTER `env_img`;
