CREATE VIEW ind_fila AS
SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=1 and fila_id=id_fila) as qtd, a.ativo from tbl_config_fila a order by a.nome_fila;


CREATE VIEW ind_concluido AS
SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila>=4 and date_format(hora_fim, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd, a.ativo from tbl_config_fila a order by a.nome_fila;


CREATE VIEW ind_atendimento AS
SELECT a.id_fila, a.nome_fila, (SELECT count(id_fila_chat) as qtd from tbl_chat_fila where status_fila=2 and date_format(hora_inicio, '%Y-%m-%d')=date_format(curdate(), '%Y-%m-%d') and fila_id=id_fila) as qtd, a.ativo from tbl_config_fila a order by a.nome_fila;


CREATE VIEW ind_tme AS
SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(te))) as tme from tbl_chat_fila where te is not null and te<>'' and status_fila>=4 and fila_id=id_fila and date_format(hora_fim, '%Y-%m-%d')=curdate()) as tme, a.ativo=1 as ativo, (SELECT count(*) from tbl_chat_fila where te is not null and fila_id=id_fila and te<>'' and date_format(hora_fim, '%Y-%m-%d')=curdate() and status_fila>=4) as qtd from tbl_config_fila a order by a.nome_fila;

CREATE VIEW ind_tma AS
SELECT a.id_fila, a.nome_fila, (SELECT sec_to_time(avg(time_to_sec(ta))) as tma from tbl_chat_fila where ta is not null and status_fila>=4 and fila_id=id_fila and date_format(hora_fim, '%Y-%m-%d')=curdate()) as tma, ativo, (SELECT count(*) from tbl_chat_fila where te is not null and fila_id=id_fila and te<>'' and date_format(hora_fim, '%Y-%m-%d')=curdate() and status_fila>=4) as qtd from tbl_config_fila a order by a.nome_fila;





CREATE VIEW infoAte AS
SELECT id_fila_chat, protocolo, ate_resp, nome, sobrenome, indice, motivo, bko_resp from tbl_chat_fila, tbl_user, tbl_chat_info where ate_resp=id_user and id_fila_chat=fila_chat_id and date_format(hora_inicio, '%Y-%m-%d')=curdate() and status_fila=2;


CREATE VIEW infoAtendimento AS
SELECT resp_id, contrato_id, date_disp from tbl_tma_atend where date_format(date_disp, '%Y-%m-%d')=curdate() and date_out is null;

CREATE VIEW infoQtd AS
SELECT id, resp_id, tbl_user.fila_id, date_disp, (SELECT acao from tbl_log_atendimento where user_id=id_user order by data_hora desc limit 1) as acao from tbl_tma_atend, tbl_user where date_format(date_disp, '%Y-%m-%d')=curdate() and fila_chat_id is null and tbl_tma_atend.resp_id=tbl_user.id_user and tbl_tma_atend.resp_id=tbl_user.id_user and (SELECT acao from tbl_log_atendimento where user_id=id_user order by data_hora desc limit 1)='Disponível';

CREATE VIEW infofila AS
SELECT id_fila_chat, ate_resp, protocolo, data_hora, bko_resp, fila_id, (SELECT nome_fila from tbl_config_fila where id_fila=fila_id) as nome_fila, assunto_id, (SELECT titulo_assunto from tbl_assunto where id_assunto=assunto_id) as nome_assunto, (SELECT prioridade_id from tbl_assunto where id_assunto=assunto_id) as prioridade_id, (SELECT peso from tbl_prioridade where id_prioridade=prioridade_id) as peso from tbl_chat_fila where status_fila=1;

CREATE VIEW filaSol AS
SELECT id_fila_chat,assunto_id, (SELECT prioridade_id from tbl_assunto where id_assunto=assunto_id) as prioridade_id, (SELECT peso from tbl_prioridade where id_prioridade=prioridade_id) as peso, ate_resp, status_fila, fila_id from tbl_chat_fila where status_fila=1;

CREATE VIEW filaQtd AS
SELECT id_fila_chat, assunto_id, fila_id, (SELECT prioridade_id from tbl_assunto where id_assunto=assunto_id) as prioridade_id, (SELECT peso from tbl_prioridade where id_prioridade=prioridade_id) as peso from tbl_chat_fila where fila_id=1 group by peso, fila_id;

