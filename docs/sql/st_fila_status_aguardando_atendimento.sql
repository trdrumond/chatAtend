-- Status intermediário: BKO abriu o chat e aguarda o solicitante (e ambos) entrarem na sala.
-- Relatórios usam tbl_situacao_chat.nome_situacao = 'Aguardando atendimento'.
INSERT INTO tbl_situacao_chat (id_situacao, nome_situacao)
SELECT 11, 'Aguardando atendimento'
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_situacao_chat WHERE id_situacao = 11
);
