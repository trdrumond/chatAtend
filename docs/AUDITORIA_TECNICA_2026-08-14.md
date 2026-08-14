# Auditoria técnica — piloto_2.0

Data: 14/08/2026  
Escopo exclusivo: `piloto_2.0/`  
Ambiente validado: XAMPP local (Windows) + MariaDB remoto configurado em `view/cnf/conexao.php`  
Nível de confiança: **médio** para 1.000 usuários/dia em leitura de telas leves; **baixo-médio** para pico de polling de chat/dashboard sem autenticação de carga autenticada.

Este relatório **não** declara o sistema “100% sem problemas”. Declara o que foi inspecionado, o que foi corrigido com evidência, o que permanece aberto e o que exige confirmação explícita.

---

## 1. Resumo executivo

O `piloto_2.0` é um backoffice PHP procedural de atendimento (filas, chat, cadastros, relatórios, painel TV, API Monitora e insights de IA). Há ~658 arquivos, ~228 scripts em `view/staff/`, 99 tabelas no banco conectado e **nenhuma suíte de produto** (PHPUnit/Jest/Cypress) além do PHPMailer vendor.

Achados principais:

- Credenciais de banco saíram do PHP versionado (`*.local.php` gitignored). **Rotacionar no MariaDB** (a senha já esteve no Git).
- SQL injection generalizada em dezenas de endpoints `staff/` (crítico residual). Corrigido bind nos fluxos de login, sessão, cadastro de usuário, reset de senha, newpass, load_api e painel online.
- Endpoints sem sessão: `reset_senha.php` agora exige sessão (evidência HTTP 302). Cron IA HTTP exige token (403 sem token). Painel TV continua público.
- Senha mestra operacional centralizada em `MasterPassword` (comportamento preservado).
- Cookie de sessão agora HttpOnly + SameSite=Lax.
- Testes: **227 pass / 0 fail** (`php tests/run.php`).
- Carga limitada (20 GET em `index.php`, 1 VU): p50 ≈ 580–590 ms; p95 ≈ 900–1626 ms (variância de rede até o banco remoto). Sem HTTP 5xx.

**Conclusão:** o sistema é utilizável e o banco responde, mas **não está endurecido o suficiente** para exposição ampla à internet sem o plano residual abaixo. A meta de 1.000 usuários **diários** é plausível se o gargalo for o banco remoto e o número de agentes em polling simultâneo permanecer moderado (~dezenas, não centenas).

---

## 2. Arquitetura e tecnologias identificadas

| Camada | Tecnologia / evidência |
|---|---|
| Runtime | PHP 7/8 (XAMPP), Apache |
| App | PHP procedural multi-script (não MVC) |
| Frontend | HTML, jQuery, Bootstrap 5, DataTables, SweetAlert2, TinyMCE |
| Banco | MariaDB/MySQL remoto (`web_chatlogos_cred`, host interno), PDO |
| Sessão | PHP sessions, 6 h, bootstrap em `view/cnf/session.php` |
| AuthN | Login SHA1 + senha mestra (hash canônico) |
| AuthZ | `tbl_nivel` (flags de menu) + `tbl_permissao.chat` + flags em `info_user` |
| API | `api_monitora/` (Bearer / X-Api-Key, pull) |
| Painel | `painel/index.php` (sem login) |
| Jobs | `view/staff/cron_ia_analise_diaria.php` (HTTP) |
| E-mail | PHPMailer vendor duplicado (`view/staff/phpmailer` e `view/api/phpmailer`) |
| Cache | `view/cnf/cache_layout.php` |
| Testes de produto | inexistentes (criados em `piloto_2.0/tests/`) |

Pontos de entrada:

- `index.php` → `login.php?data=` (base64 login+hash)
- `view/index.php` (SPA via `action.php`)
- `access/login_chat.php` (portal central)
- `painel/index.php`
- `api_monitora/index.php`
- `view/staff/*.php` (AJAX)

Módulos de menu (`view/content/menu/`):

- Operação: Dashboard, Governança, Insights IA, Fila, Relatórios, Indicadores, Histórico, Pendências, Comunicação
- Atendente (nível 5): Fila, Histórico, Pendências, Comunicação
- BKO (nível 4): Fila, Meu Score, Histórico, Pendências, Comunicação
- Config: Usuários, Regional, Empresa, Agência, Assuntos, Prioridades, FAQ, Mensagem, Log Acesso, Filas, Contrato, Config. IA

---

## 3. Matriz de funcionalidades e validação

Legenda de validação: **EST** = inspeção estática de código; **HTTP** = probe HTTP; **AUTO** = teste automatizado; **E2E** = fluxo autenticado completo (não executado — sem credenciais de usuário de teste).

| Funcionalidade | Arquivos / componentes | Endpoints | Permissões | Tabelas | Testes | Criticidade | Validação |
|---|---|---|---|---|---|---|---|
| Login | `index.php`, `login.php`, `session.php` | GET/POST login | público | `tbl_user`, `tbl_nivel`, `info_user` | AUTO + HTTP | crítica | **AUTO/HTTP OK** (form 200; bind + regenerate) |
| Logout | `out.php` | — | sessão | `tbl_log_diario` | EST | alta | EST |
| Sessão / perfil | `view/cnf/session.php` | include | sessão | `info_user`, `tbl_nivel`, `tbl_permissao`, `tbl_log_diario` | AUTO | crítica | **AUTO OK** (guarda + bind) |
| Dashboard fila | `dash-fila.php`, `dash_fila_live.php`, `dash-fila-live.js` | `staff/dash_fila_live.php` | menu_idx[0], niveis ≠4/5 | `tbl_chat_fila`, `tbl_user` | EST | crítica | EST (polling 10s) |
| Fila atendente | `dash-cha.php`, `chat-fila.php`, `load_chat_ate.php` | vários staff | nível 5 | `tbl_chat_fila` | EST | crítica | EST |
| Chat BKO | `chat-bko.php`, `st-bko-distrib.js` | staff chat | nível 4 | `tbl_chat_*` | EST | crítica | EST (polling 2,5s) |
| Chat atendimento | `view/chat/chat_ind.php`, `save_msg.php` | staff save/load | sessão | `tbl_chat_msg`, `tbl_chat_info` | EST | crítica | EST; SQLi residual em `save_msg.php` |
| Pós-atendimento | `save_pos.php` | POST | sessão | `tbl_chat_fila`, forms pós | EST | alta | EST; interpolação SQL residual |
| Cadastro usuários | `cad-usu.php`, `save_user.php` | staff | menu_cnf[0] | `tbl_user` | AUTO (bind) | crítica | **AUTO** insert bind; E2E não |
| Import usuários | `import_users.php` | upload | sessão | `tbl_user` | EST | alta | EST; upload/token em nome de arquivo |
| Reset senha (logado) | `reset_senha.php`, `pass.php` | POST | sessão (novo) | `tbl_user`, `tbl_user_pass` | HTTP 302 + AUTO | crítica | **HTTP 302 sem sessão** |
| Reset senha (público) | `newpass.php`, `access/reset_senha.php` | POST | público | `tbl_user` | EST | alta | EST; bind aplicado; sem rate limit |
| Regional/Empresa/Agência | `cad-reg/emp/age`, `save_*` | staff | menu_cnf | cadastros | EST | média | EST; SQLi residual |
| Assuntos/Prioridades/FAQ/Mensagem | `cad-ass/pri/faq/men` | staff | menu_cnf | configs | EST | média | EST |
| Filas | `cad-fil.php`, `alt_filas_config.php` | staff | menu_cnf[9] | `tbl_config_fila` | EST | alta | EST |
| Contrato | `cad-ctt.php`, `alt_ctt_*.php` | staff | menu_cnf[10] | `tbl_contrato` | EST | alta | EST |
| Config IA | `cnf-ia.php`, `save_ia_config.php` | staff | nível ≤1 | configs IA | EST | alta | EST |
| Insights IA | `ia-insights.php`, `cron_ia_analise_diaria.php` | cron HTTP | token env/`cron.local.php` | análises | HTTP 403 sem token | alta | **AUTO/HTTP 403** |
| Governança | `gov-analytics.php`, `dash_gov_data.php` | staff | menu_idx[6] | analytics | EST | média | EST |
| Relatórios / indicadores | `rel-*.php`, `load_dados_*.php` | staff | menus | várias | EST | alta | EST; queries pesadas |
| Histórico / pendências | `hist-dash.php`, `hist-pend.php` | staff | menus | fila/pend | EST | alta | EST |
| Comunicação interna | `com-idx.php`, `save_msg_com.php` | staff | flag `comunicacao` | msgs com | EST | média | EST |
| Painel TV | `painel/index.php`, `painel_load_*.php` | sem login | nenhum | fila/users | HTTP 200 | alta | **HTTP 200 público** |
| API Monitora | `api_monitora/*` | `/monitora/*` | Bearer/X-Api-Key | contratos/filas/atend. | HTTP 401 | alta | **401 sem token OK** |
| API legado `load_api.php` | `view/staff/load_api.php` | POST user+datas | nome em `tbl_api_user` | `tbl_chat_fila` | AUTO bind | alta | bind OK; auth fraca residual |
| Upload chat | `save_file.php`, `save_img_perfil.php` | POST | sessão | files | EST | alta | EST |
| PHPMailer tests | `phpmailer/test*` | HTTP | — | — | HTTP 403 | média | **403 após .htaccess** |

Cobertura E2E autenticada (login real, pegar chat, finalizar, CRUD completo): **não executada**. Pendência explícita.

---

## 4. Problemas encontrados (por criticidade)

### Bloqueador (residual — exige confirmação para corrigir de ponta a ponta)

| ID | Problema | Causa | Impacto | Arquivos |
|---|---|---|---|---|
| B1 | Senha de banco em claro no repositório | `$senha` hardcoded | **Mitigado no código** (loader local). Rotacionar senha no SGBD (ops) | `conexao.local.php` (não versionado) |
| B2 | Cron IA executável sem token se `ST_IA_CRON_TOKEN` vazio | fail-open | **Corrigido**: HTTP 403 sem token; CLI permitido | `view/staff/cron_ia_analise_diaria.php` |

### Crítico

| ID | Problema | Status |
|---|---|---|
| C1 | SQL injection por interpolação de `$_POST` em dezenas de `staff/*.php` | **Parcialmente mitigado** nos fluxos de auth/user; residual amplo (ver grep) |
| C2 | `reset_senha.php` sem sessão (qualquer um alterava senha por `id`) | **Corrigido** (sessão + bind; HTTP 302) |
| C3 | Login SQL injection (`nome_usuario` concatenado) | **Corrigido** (bind) |
| C4 | Hash SHA1 + senha na querystring `login.php?data=` | Residual (contrato com portal; não alterado) |
| C5 | Painel TV sem autenticação | Residual (produto; confirmação) |
| C6 | `load_api.php` autentica só por nome de usuário + CORS `*` | Bind aplicado; auth fraca residual |

### Alto

| ID | Problema | Status |
|---|---|---|
| A1 | Sem CSRF em formulários/AJAX | **Parcial**: token + header no shell; POSTs autenticados exigem CSRF |
| A2 | `newpass.php` público sem rate limit (reset por login+e-mail) | Bind OK; abuso residual |
| A3 | AuthZ principalmente na UI de menu, não em cada `staff/*.php` | Residual |
| A4 | `prepare()` após concatenar **não** é bind real | Residual na maior parte do legado |
| A5 | Polling agressivo (2,5s–10s) em chat/dashboard | Residual (não “otimizado” sem medição autenticada) |
| A6 | `error_reporting(0)` + `set_time_limit(0)` | Residual |
| A7 | Upload (`import_users.php`) usa `$_POST['token']` no nome do arquivo | Residual |

### Médio

| ID | Problema | Status |
|---|---|---|
| M1 | Sobre-indexação (índice quase por coluna, inclusive `tbl_chat_msg.msg`) | Residual (DDL não aplicado) |
| M2 | PHPMailer antigo duplicado | HTTP de testes bloqueado |
| M3 | Sem observabilidade (métricas, health, logs estruturados) | Residual |
| M4 | `SELECT *` e subqueries correlatas em dashboards | Residual |
| M5 | Código morto / duplicado (`cad-usu.php_novo`, phpmailer duplo, `teste.php`) | Residual |
| M6 | Saudação “Boa noite” nunca disparava | **Corrigido** |

### Baixo

| ID | Problema | Status |
|---|---|---|
| L1 | Directory listing | **Corrigido** (`Options -Indexes`) |
| L2 | Config PHP acessível via HTTP | **Corrigido** (403) |
| L3 | Vazamento de `PDOException` no `die()` | **Corrigido** |

---

## 5. Alterações realizadas (justificativa)

Somente mudanças de baixo risco, compatíveis com o comportamento atual. **Não** houve ALTER TABLE, **não** houve troca de contrato da API Monitora, **não** houve remoção da senha mestra, **não** houve autenticação no painel TV.

| Arquivo | Justificativa |
|---|---|
| `view/cnf/MasterPassword.php` | Único ponto de verificação da mestra |
| `view/cnf/session_config.php` | HttpOnly, SameSite, strict mode |
| `view/cnf/session.php` | Recusa sessão vazia; bind de IDs; log diário bind; saudação |
| `view/cnf/conexao.php` | Não vaza erro PDO (credencial **permanece** até confirmação) |
| `view/cnf/func.php` | `logAtendimento` com bind |
| `login.php` | Bind + regenerate_id + MasterPassword |
| `access/login_chat.php` | Bind + MasterPassword + sanitização de URL |
| `access/conexao.php` / `conn_config.php` | Bind contrato; erro PDO genérico |
| `access/reset_senha.php` | Bind + json_encode (XSS) |
| `view/staff/reset_senha.php` | Exige sessão + bind |
| `view/staff/verifica_senha.php` | Exige sessão |
| `view/staff/loadText.php` | Exige sessão + bind chat_id |
| `view/staff/save_user.php` | Bind INSERT; não imprime senha |
| `view/staff/newpass.php` | Bind (fluxo público preservado) |
| `view/staff/load_api.php` | Bind + validação de data `Y-m-d` |
| `view/staff/painel_load_online.php` | Bind de fila/contrato (painel continua público) |
| `.htaccess` (raiz, `view/cnf`, `access`, phpmailer tests, `tests/`) | Bloqueio HTTP de configs/testes |
| `tests/run.php` e probes | Suíte mínima |
| `.gitignore` | `tests/output/`, cookies de teste da API |

---

## 6. Vulnerabilidades corrigidas e riscos remanescentes

Corrigidas com evidência HTTP/AUTO:

- SQLi no login e no cadastro de usuário (caminhos alterados)
- Reset de senha anônimo em `view/staff/reset_senha.php`
- Acesso HTTP a `conexao.php` / `conn_config.php` (403)
- Scripts de teste PHPMailer (403)
- Session fixation mitigada (`session_regenerate_id(true)`)
- Cookie sem HttpOnly
- Senha gerada ecoada na UI de cadastro
- Erro de banco exposto ao cliente

Remanescentes (não “aprovados como aceitáveis” — apenas não alterados sem confirmação):

- Segredo de banco no Git (**rotacionar** após mover para arquivo local)
- Cron IA fail-open
- Painel TV público
- SQLi residual em ~100 arquivos staff
- CSRF ausente
- SHA1
- Credenciais na URL de login
- `load_api.php` sem segredo
- IDOR potencial em resets administrativos (`id` POST de outro usuário, agora só autenticado)

---

## 7. Banco de dados, queries e índices

Inspeção somente leitura: `SHOW TABLES` (99 tabelas) + `SHOW INDEX` nas tabelas críticas.

Observações:

- `tbl_chat_fila`, `tbl_chat_msg`, `tbl_user` já possuem **muitos** índices (incluindo compostos úteis: `idx_chat_fila_fila_data`, `idx_chat_msg_chat_data`, `idx_user_painel`).
- Há índices redundantes (`idx_1`…`idx_N` quase coluna a coluna) e índice em `tbl_chat_msg.msg` (texto) — aumenta custo de escrita.
- **Nenhum CREATE/DROP INDEX foi aplicado.** Sem `EXPLAIN` sob carga autenticada, otimizar agora seria chute.

Recomendações (aguardam confirmação + backup):

1. Mover credenciais para `conexao.local.php` gitignored e **rotacionar** a senha atual.
2. Não criar índices novos até medir slow query log.
3. Avaliar (em janela) remoção de índices duplicados `idx_1`–`idx_N` após `EXPLAIN`.
4. Paginar relatórios `load_dados_*` sem `LIMIT`.

Rollback de schema: **N/A** (nenhum DDL).

---

## 8. Resultado dos testes automatizados

Comando: `php piloto_2.0/tests/run.php`

```
Resultado: 227 pass, 0 fail
```

Inclui:

- `php -l` nos arquivos alterados
- asserts estáticos de bind/sessão/httponly/MasterPassword
- HTTP GET `index.php` = 200 com formulário de login
- PDO `SELECT 1` + existência das tabelas críticas
- carga limitada 20 requests

Probes extras (`tests/http_security_probe.php`):

| URL | HTTP | Interpretação |
|---|---|---|
| `view/cnf/conexao.php` | 403 | config bloqueada |
| `view/staff/reset_senha.php` | 302 → `../out.php` | sessão obrigatória |
| `view/staff/cron_ia_analise_diaria.php` | 403 | cron fail-closed |
| `api_monitora/index.php` | 401 | auth API OK |
| `painel/index.php` | 200 | painel público |
| `access/conn_config.php` | 403 | config bloqueada |
| `phpmailer/test_script/index.php` | 403 | testes vendor bloqueados |

Não executado (limitação):

- PHPUnit/Jest/Cypress de produto (não existiam)
- E2E autenticado (chat, fila, CRUD)
- Scan CVE completo de vendor JS (FontAwesome CDN, DataTables, PHPMailer antigo)
- Lint de todo o legado (centenas de arquivos)

---

## 9. Teste de performance (1.000 usuários/dia)

### Modelo de carga

1.000 usuários **diários** ≠ 1.000 concorrentes. Hipótese operacional:

- Turno ~8 h → média ~125 sessões/hora
- Pico 3× → ~0,1 login/s
- Hot path real: **polling** (BKO 2,5 s, dashboard ~10 s, acompanhamento 3 s)

Se 40 agentes com polling 2,5 s → ~16 req/s só de AJAX, todos batendo MariaDB remoto.

### O que foi medido (seguro, local, 1 VU)

Alvo: `GET /solvetask/piloto_2.0/index.php` (inclui conexão PDO remota + `tbl_config_sis`).

| Run | n | p50 | p95 | p99 | avg | erros | throughput |
|---|---|---|---|---|---|---|---|
| 1 | 20 | 589 ms | 905 ms | 905 ms | 653 ms | 0 | ~1,5 req/s |
| 2 | 20 | 581 ms | 1626 ms | 1626 ms | 772 ms | 0 | ~1,3 req/s |

Não foi rodada carga agressiva nem em produção. Não houve 20 VUs simultâneos (risco à máquina/XAMPP compartilhado e ao banco remoto corporativo).

### Interpretação vs meta 1.000 DAU

- A página de login já leva **0,6–1,6 s** por causa da latência do host `10.33.29.106`.
- Isso é compatível com 1.000 DAU de **navegação esparsa**.
- **Não está comprovado** para dezenas de painéis/chats abertos com polling. Gargalo mais provável: PHP `session.php` (várias queries por request) + round-trip remoto + `set_time_limit(0)`.
- CPU/memória/disco do host local **não** foram amostrados com profiler (carga 1 VU não satura).

Confiança na meta 1.000 DAU: **média**, condicional a (a) poucos pollers simultâneos, (b) banco remoto saudável, (c) correção do cron público.

---

## 10. Evidências (comandos)

```text
php piloto_2.0/tests/run.php
php piloto_2.0/tests/show_indexes.php
php piloto_2.0/tests/http_security_probe.php
php -l piloto_2.0/login.php
php -l piloto_2.0/view/cnf/session.php
```

Logs: sem erros críticos na suíte. `error_reporting(0)` no app **esconde** erros de runtime — ausência de log de aplicação não prova ausência de falha.

Relatórios gerados nesta pasta:

- este arquivo
- saída da suíte (stdout)
- `tests/show_indexes.php` (CLI; HTTP negado)

Nenhum segredo foi copiado para o relatório.

---

## 11. Pendências, limitações, riscos residuais e recomendações

Pendências explícitas (funcionalidades mapeadas mas sem E2E):

- Chat ponta a ponta, distribuição BKO, transferência, finalização, pós
- CRUDs de config (regional, empresa, agência, assunto, fila, contrato)
- Relatórios com filtro de data
- Import Excel de usuários
- Insights IA com migration aplicada
- Integração Monitora autenticada (token local não usado nos testes)

Recomendações futuras (ordem):

1. **Confirmar** B1: `conexao.local.php` + rotação de senha + exemplo sem segredo  
2. **Confirmar** B2: cron fail-closed (CLI ou token obrigatório)  
3. **Confirmar** C5: token/sessão no painel TV  
4. Programa de bind em lote nos `save_*.php` / `load_*.php` restantes (sem mudar regra de negócio)  
5. CSRF token nos POSTs staff  
6. `password_hash` (migração de SHA1) — mudança de contrato de senha  
7. Slow query log + EXPLAIN nos relatórios antes de novos índices  
8. Health `/healthz` sem dados sensíveis  
9. Substituir polling por intervalo maior ou SSE/websocket já esboçado e comentado  

Limitações desta auditoria:

- Especialistas em paralelo foram acionados; a consolidação acima baseia-se em inspeção direta do código, HTTP local e `SHOW INDEX`.
- Não houve teste autenticado de regressão funcional de chat.
- PHPMailer/JS de terceiros não passaram por `composer audit`/`npm audit` (não há composer.json de produto).

---

## 12. Plano de rollback

Alterações desta leva são só PHP/.htaccess/testes. Sem DDL.

Rollback Git (se esta entrega for commitada):

```text
git checkout -- piloto_2.0/login.php piloto_2.0/view/cnf/session.php ...
```

Ou reverter o commit específico.

Efeitos colaterais se reverter:

- `reset_senha.php` volta a ser explorável sem login
- configs PHP voltam a ser baixáveis via HTTP
- login volta a interpolar SQL

Rollback de banco: **não aplicável**.

---

## Confirmação solicitada (não executar sem aceite)

Responda explicitamente quais itens autoriza na próxima leva:

1. Extrair credenciais de `conexao.php` / `conn_config.php` para arquivo local gitignored **e rotacionar a senha do banco** (a atual deve ser considerada comprometida por estar no Git).
2. Cron IA: negar HTTP se `ST_IA_CRON_TOKEN` não estiver definido (quebra agendamentos que chamam a URL sem token).
3. Exigir autenticação (ou token de wallboard) no `painel/`.
4. Programa de prepared statements nos demais `staff/save_*.php` e `staff/load_*.php` (mesmo SQL, só bind).
5. CSRF nos POSTs (exige ajuste JS em `action.js` e formulários).
6. Carga autenticada limitada (5–10 VUs / 1–2 min) contra staging, **não** contra o MariaDB de produção compartilhado.

Até esse aceite, o código permanece compatível com o AS-IS, com o endurecimento já evidenciado acima.

---

## 13. Adendo — consolidação dos especialistas (14/08/2026)

Especialistas acionados (inspeção; vários relatórios foram feitos sobre o estado **antes** do hardening desta leva). O que já estava corrigido no código **não** foi revertido: senha mestra **permanece** (centralizada em `MasterPassword`); bind no login/sessão; cookies HttpOnly/SameSite; `reset_senha` com sessão; `.htaccess` de configs/PHPMailer; suíte `tests/run.php`.

### Banco canônico (Maria — evidência viva)

- Host `10.33.29.106` (MariaDB 10.4.18), schema **`web_chatlogos_cred`** (~2,5 GB, 99 objetos).
- `web_chatlogos_piloto_20` **não existe**. `web_chatlogos_piloto` existe (~8,9 GB) mas **não** é o DSN atual.
- Instância compartilhada com outros schemas `web_chatlogos_*`; `read_only=0`; **binlog OFF**; **sem replica**; **sem EVENT** de dump.
- Backup **não evidenciado** no SGBD. Sem PITR.
- Deadlock em `tbl_chat_fila` em 14/08/2026 08:58 (job de abandono vs assunção de ticket). `Innodb_row_lock_waits` alto no uptime.
- `tbl_chat_msg` ~4,5 M linhas / ~1,5 GB (índices maiores que os dados).
- Índices de `dashboard_indexes.sql` **já existem** no banco real (o dump 2021 com “só PK” está desatualizado).
- Ausência útil: `(status_fila, grace_abandon_at)` para o job de abandono. **DDL não aplicado.**
- Usuário da app com grants excessivos (`ALL PRIVILEGES` + GRANT) — risco operacional (James/Maria).

### Arquitetura ([Astolfo](2ac4a1d2-7469-484d-a41f-411eb55c781f))

Manter AS-IS procedural; sem MVC big-bang. Ilha moderna a replicar: `api_monitora/`. Rotinas de purge/fila no bootstrap de sessão são risco de confiabilidade. Cadastro `view/api/cadastro_usuario.php` sem auth — **não fechado** (pode ser integração viva; exige PO).

### Segurança ([James](35e917b3-32b9-4908-ab00-231bd9da7dc2))

Reprovação do AS-IS original permanece válida para o **legado não tocado** (SQLi em massa, CSRF, uploads, cron fail-open, painel). Itens B2/A2 do relatório James (mestra “remover”, cookies “ausentes”) estão **desatualizados** em relação a esta leva: mestra **mantida** e centralizada; cookies e `session_regenerate_id` já aplicados.

### Backend / QA / Front / Perf / DevOps / SRE

Consenso: CSRF ausente; `action.php` era include dinâmico (agora com **whitelist**); polling 1,5–2,5 s + `UPDATE tbl_log_diario` em todo request; WS hardcoded `wss://solvetask-mt.logos-ma.com.br/celpe`; XSS via `.html()` no chat legado; sem health check real; jobs no login; `status_cli.php` era público (agora só `127.0.0.1`/`::1`); pasta `testes/` e `view/log/` negadas via HTTP.

### Follow-up aplicado neste adendo (baixo risco)

| Item | Arquivo |
|---|---|
| Whitelist `sec`/`action` + `realpath` | `view/action.php` |
| OPcache status só localhost | `cache/status_cli.php` |
| Deny HTTP | `testes/.htaccess`, `view/log/.htaccess` |

Não aplicados (continuam na lista de confirmação): auth do painel TV, bind em massa nos demais `staff/*`, índices novos, fechar `cadastro_usuario.php`, rotacionar senha do banco (ops).

---

## 14. Adendo — segunda onda de endurecimento (14/08/2026)

Aplicado após autorização para seguir. Escopo exclusivo `piloto_2.0/`. Senha mestra operacional **mantida**. Sem DDL. Sem carga agressiva no MariaDB compartilhado.

| Item | Status | Evidência |
|---|---|---|
| Cron IA fail-closed (HTTP) | **Aplicado** | Sem token → HTTP 403; CLI segue permitido |
| DSN fora do Git | **Aplicado** | `conexao.php` / `conn_config.php` leem `*.local.php` gitignored; examples versionados |
| Bind writes críticos | **Aplicado** | `save_msg.php`, `alt_user.php`, `img.php`, `save_file.php`, `save_cancelFila.php` |
| Bug `save_cancelFila` (`prepare($sql)` no UPDATE) | **Corrigido** | UPDATE em `tbl_chat_info` com bind |
| Allowlist upload | **Aplicado** | `load_file.php` + `view/file/.htaccess` nega PHP |
| CSRF no shell autenticado | **Aplicado** | Token em sessão; `X-CSRF-Token` via `action.js`; campo `st_csrf` no `sendBeacon` |
| Throttle presença | **Aplicado** | `UPDATE tbl_log_diario` no máximo 1×/60s |
| Painel TV auth | **Não** | Continua público (produto); CSRF só se houver sessão |
| Rotação da senha do banco | **Ops** | A senha já esteve no Git; rotacionar no MariaDB e no `*.local.php` |

**Produção que puxar Git:** copiar `conexao.local.example.php` → `conexao.local.php` (e o equivalente em `access/`) com o DSN real. Sem esse arquivo o sistema recusa conectar.

**Cron HTTP:** definir `ST_IA_CRON_TOKEN` ou `view/cnf/cron.local.php`. Agendamentos que chamam a URL sem token passam a receber 403. CLI (`php cron_ia_analise_diaria.php`) não exige token.

---

## 15. Adendo — terceira onda (bind chat/fila · 14/08/2026)

Prepared statements nos fluxos de atendimento de maior criticidade (hot path chat/fila/pós):

| Arquivo | Alteração |
|---|---|
| `load_cancel_fila.php` | Bind completo; abandono de fila sem concatenação de motivo |
| `save_msg_transfer.php` | Bind token, mensagens, updates de fila/chat/TMA |
| `save_pend_info.php` | Bind pendência e encerramento fila/chat |
| `save_call.php` | Bind abertura de protocolo na fila |
| `save_pos.php` | Bind pós-atendimento; tabela dinâmica `tbl_in_pos_*` com whitelist numérica + nomes de coluna |
| `save_new_chat.php` | Bind comunicação interna |
| `save_img_perfil.php` | Bind perfil; saída JS com `json_encode` (XSS reduzido) |

Testes: **83 pass / 0 fail** (`php tests/run.php`).

---

## 16. Adendo — quarta onda (bind cadastros CNF · 14/08/2026)

Prepared statements nos INSERTs de configuração (mesmo SQL, só bind):

| Arquivo | Tabela |
|---|---|
| `save_reg.php` | `tbl_regional` |
| `save_emp.php` | `tbl_empresa` |
| `save_age.php` | `tbl_agencia` |
| `save_ass.php` | `tbl_assunto` |
| `save_pri.php` | `tbl_prioridade` |
| `save_ctt.php` | `tbl_contrato` |
| `save_faq_config.php` | `tbl_faq` |
| `save_men_config.php` | `tbl_config_men_ini` |
| `save_fil.php` | `tbl_config_fila` + CREATE `tbl_in_pos_*` / `tbl_in_mon_*` com IDs numéricos |

Em `save_fil.php` também foi removido o `echo $sql` (vazava o INSERT na UI). Nomes de tabela dinâmica continuam montados no PHP (DDL não aceita bind) com whitelist `tbl_in_(pos|mon)_\\d+_\\d+`.

Testes: **102 pass / 0 fail** (`php tests/run.php`).

---

## 17. Adendo — quinta onda (bind UPDATE cadastros CNF · 14/08/2026)

Prepared statements nos UPDATEs correspondentes aos INSERTs da 4ª onda:

| Arquivo | Tabela |
|---|---|
| `alt_reg.php` | `tbl_regional` (+ bind em agências vinculadas) |
| `alt_emp.php` | `tbl_empresa` |
| `alt_age.php` | `tbl_agencia` |
| `alt_ass.php` | `tbl_assunto` |
| `alt_pri.php` | `tbl_prioridade` |
| `alt_ctt.php` | `tbl_contrato` |
| `alt_faq_config.php` | `tbl_faq` |
| `alt_men_config.php` | `tbl_config_men_ini` |
| `alt_fil.php` | `tbl_config_fila` |

IDs de modal no JS passaram a `json_encode` (evita XSS no seletor).

Testes: **120 pass / 0 fail** (`php tests/run.php`).

---

## 18. Adendo — sexta onda (bind relatórios load_dados_* · 14/08/2026)

Prepared statements / sanitização de IDs e datas nos relatórios e KPIs:

| Arquivo | Foco |
|---|---|
| `load_dados_tme.php` / `tma.php` / `fila.php` / `atend.php` / `concluido.php` | KPIs dashboard |
| `load_dados_pendente.php` | tabela dinâmica `tbl_in_dem_*` com whitelist |
| `load_dados_pend_ate.php` | bind `ate_resp` |
| `load_dados_hist.php` | datas + contrato + fila + `tbl_in_mon_*` |
| `load_dados_rel.php` | BETWEEN datas, monitoria, indicadores |
| `load_dados_ind.php` | bind do dia |

`load_dados_rel_fila.php` já usava bind (`:contrato` / `:fila`).

Testes (pós 6ª onda): **136 pass / 0 fail** (`php tests/run.php`).

## 19. Adendo — sétima onda (bind fila polling + flags contrato · 14/08/2026)

Prepared statements no hot path de fila e nos toggles de flags de contrato:

| Arquivo | Foco |
|---|---|
| `load_fila.php` / `load_fila_user.php` | `contrato_id=?` no SELECT de filas |
| `load_fila_atend.php` | `id_fila=?` |
| `load_fila_ativa.php` | `where id_fila=?` (opcional) |
| `load_fila_pos.php` | `fila_id = ?` |
| `load_fila_bko.php` | bind de `resp_id` / `user_id` (SELECT/INSERT/UPDATE) |
| `alt_ctt_com.php` | `SET com=?` |
| `alt_ctt_env_img.php` | `SET env_img=?` |
| `alt_ctt_env_file.php` | `SET env_file=?` |
| `alt_ctt_com_new_conv.php` | `SET new_conv=?` |
| `alt_ctt_com_grupos.php` | `SET grupos=?` |
| `alt_ctt_com_men_massa.php` | `SET men_massa=?` |
| `alt_ctt_com_resp_men.php` | `SET resp_men=?` |

`alt_ctt_nome_robo.php` já usava bind. Comportamento dos flags permanece: status vazio = 0, caso contrário = 1; `clearLayoutCacheByContrato` após UPDATE.

Senha mestra operacional permanece. Sem DDL, sem fechar painel TV, sem `password_hash`.

Testes (pós 7ª onda): **162 pass / 0 fail** (`php tests/run.php`).

## 20. Adendo — oitava onda (bind chat/fila runtime + dropdowns · 14/08/2026)

Prepared statements no polling de atendimento, pausa, recall e dropdowns CNF:

| Arquivo | Foco |
|---|---|
| `load_chat_ate.php` | bind `id_fila_chat` / `ate_resp` / `fila_id` |
| `load_chat_bko.php` | pausa, TMA, `infoQtd`, IN de filas do usuário |
| `verificaBko.php` | `id_fila_chat=?` |
| `verifica_atendente.php` | `ate_resp=?` |
| `save_pause.php` | `user_id=?` em UPDATE/INSERT |
| `save_recall.php` | UPDATE fila/chat + INSERT com bind |
| `save_new_fila.php` | `SET fila_id=?` |
| `load_ass.php` / `load_assunto_men.php` | `id_fila=?` + placeholders no `IN` |
| `load_regional.php` / `load_rel_emp.php` | `contrato_id=?` |
| `loadId.php` | `protocolo=?` |
| `alt_filas_config.php` | filas do usuário (IDs sanitizados) |
| `alt_config_filas.php` | `id_user=?` + IN de contratos |
| `alt_reset_img.php` | `SET img=?` |
| `load_pend_info.php` | `id_chat=?` |

Constantes `ST_FILA_*` / `stFilaSql*()` permanecem interpoladas (não são input). Senha mestra operacional permanece. Sem DDL, sem fechar painel TV, sem `password_hash`.

Testes (pós 8ª onda): **198 pass / 0 fail** (`php tests/run.php`).

## 21. Adendo — nona onda (bind comunicação interna COM · 14/08/2026)

Prepared statements no módulo de mensagens internas (COM):

| Arquivo | Foco |
|---|---|
| `save_msg_com.php` / `save_msg_com_ind.php` | SELECT/INSERT/UPDATE com bind; `src` de imagem via placeholder |
| `save_file_com.php` / `save_file_grupo.php` | arquivos COM |
| `save_new_grupo.php` | novo grupo + `cols` com IDs sanitizados |
| `send_msg_massa.php` | massa: lookup/INSERT `tbl_com_info` |
| `loadText_com.php` / `loadText_com_ind.php` | marca visualização |
| `load_com.php` / `load_com_hist.php` | abertura da conversa |
| `loadChatCom.php` | histórico de grupo (`chat_group=?`, LIMIT int) |
| `load_com_list.php` / `load_com_count.php` | lista e badge |

JS de envio passou a usar `json_encode` nos argumentos. Senha mestra operacional permanece. Sem DDL, sem fechar painel TV, sem `password_hash`.

Testes: **227 pass / 0 fail** (`php tests/run.php`).

## 22. Adendo — décima onda (bind pós-atendimento / monitoria POS·MON · 14/08/2026)

Prepared statements e whitelist de tabelas dinâmicas no módulo de formulários pós-atendimento (POS) e monitoria (MON):

| Arquivo | Foco |
|---|---|
| `pos_alt_campo.php` / `pos_alt_campo_obg.php` / `pos_alt_sel.php` | toggles de campo POS |
| `pos_alt_ordem_input.php` | reordenação de campos com bind |
| `pos_save_form_config.php` / `pos_save_form_config_exi.php` | DDL legado `tbl_in_pos_*` + whitelist |
| `pos_save_input_option.php` | opções de input POS |
| `pos_config_form_options.php` | configuração de opções por `campo_id` |
| `mon_alt_campo.php` / `mon_alt_campo_obg.php` / `mon_alt_campo_qualif.php` | toggles de campo MON |
| `mon_alt_sel.php` / `mon_alt_sel_opt.php` / `mon_alt_sel_mon.php` | selects MON (removido `echo $sql`) |
| `mon_alt_ordem_input.php` | reordenação de campos MON |
| `mon_save_form_config.php` / `mon_save_input_option.php` | config formulário MON |
| `mon_config_form_options.php` | opções MON + INSERT inline com bind |
| `save_mon.php` | INSERT dinâmico em `tbl_in_mon_{fila}_{contrato}` (padrão `save_pos.php`) |
| `load_monitoria.php` | leitura/formulário monitoria com whitelist `tbl_in_mon_*` |

Padrão: IDs `(int)`, flags 0/1, colunas `[a-zA-Z0-9_]+`, tabelas `tbl_in_(pos|mon)_\d+_\d+`. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **261 pass / 0 fail** (`php tests/run.php`).

## 23. Adendo — 11ª onda (bind demandas / serviços / horários / pendências · 14/08/2026)

Prepared statements e whitelist de tabelas dinâmicas:

| Arquivo | Foco |
|---|---|
| `save_dem.php` | UPDATE/INSERT em `tbl_in_dados_*` / `tbl_in_dem_*`, TMA, pendência, pause |
| `save_ser.php` / `save_ser_config.php` | cadastro de serviços e campos |
| `save_input_option.php` / `config_serv_options.php` | opções checkbox/select de serviços |
| `alt_ser.php` | toggle ativo serviço |
| `hr_save_form_config.php` / `hr_alt_campo.php` / `hr_del_campo.php` | horários de fila (removido debug) |
| `alt_pend_bko.php` / `alt_pend_sol.php` | reassignment BKO/solicitante em pendências |

Padrão: IDs `(int)`, colunas dinâmicas `[a-zA-Z0-9_]+`, tabelas `tbl_in_(dados|dem)_\d+_\d+`. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **286 pass / 0 fail** (`php tests/run.php`).

## 24. Adendo — 12ª onda (bind histórico / pendências / assuntos / fila · 14/08/2026)

Prepared statements no módulo de histórico de chats, relatório de pendências e operações auxiliares:

| Arquivo | Foco |
|---|---|
| `load_hist.php` | histórico secundário + monitoria + arquivos |
| `load_hist_pend.php` | detalhe pendência + marca visualização |
| `load_rel_pend.php` | relatório pendências com filtros dinâmicos bindados |
| `load_assunto.php` / `load_assunto_fila.php` | dropdown assuntos por contrato |
| `alt_grupo.php` | edição grupo COM (`cols` sanitizado) |
| `derruba_fila.php` | desativa fila + cancelamento chats ativos |
| `load_deposit_file_hist.php` / `load_deposit_file.php` | depósito de arquivos por token/chat |

Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **306 pass / 0 fail** (`php tests/run.php`).

## 25. Adendo — 13ª onda (bind dashboard / gráficos demandas · 14/08/2026)

Prepared statements e whitelist de tabelas dinâmicas no dashboard e gráficos:

| Arquivo | Foco |
|---|---|
| `load_conc.php` / `load_top_five.php` / `load_chart_3.php` | gráficos `tbl_in_dem_*` |
| `load_online.php` | BKO online + queries em lote com IN bindado |
| `alt_form_user.php` | alteração form_id do usuário |
| `load_dados_dash_ind.php` | dashboard índice: filas, log, pendências |
| `load_dados_dash_ind_painel.php` | dashboard painel TV (IDs sanitizados) |

Padrão: whitelist `tbl_in_dem_\d+_\d+`, IN lists via placeholders, filtros contrato/fila com `(int)` + sanitize CSV. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **324 pass / 0 fail** (`php tests/run.php`).

## 26. Adendo — 14ª onda (bind logs / import / mail / pend / painel · 14/08/2026)

Prepared statements em chat, logs, importação de usuários, e-mail de cadastro e painel online:

| Arquivo | Foco |
|---|---|
| `loadText_group.php` | UPDATE visualização mensagem grupo (`group_chat`, `user_id`) |
| `log_dados.php` | filtros dia/user + lookup protocolo com coluna whitelist |
| `load_dados_pend.php` | contagem/lista pendências por BKO/fila |
| `envio_mail_cad.php` | SELECT/UPDATE usuário por `id_user` |
| `import_users.php` | município/regional/agência, INSERT user/pass/img, `flag_mail` |
| `painel_load_online.php` | log diário, TMA, atendimento, estrelas IN bindado |

Padrão: `(int)` nos IDs de POST, IN via placeholders, INSERT pass `(?, curdate(), ?)`. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **341 pass / 0 fail** (`php tests/run.php`).

## 27. Adendo — 15ª onda (bind GET/PDF / gráficos / cadastros aux · 14/08/2026)

Prepared statements e sanitização de entrada em imagens, relatório PDF, gráficos e cadastros auxiliares:

| Arquivo | Foco |
|---|---|
| `img_group.php` | imagem comunicação interna (`com_id`, `chave`) — espelha `img.php` |
| `dadosPdf.php` | relatório diário PDF — `dia` sanitizado + bind nas 3 queries |
| `load_municipio.php` | municípios por UF (`id_estado`, `uf`) |
| `load_rank.php` | ranking por fila |
| `painel_load_fila_ativa.php` | fila ativa painel TV (`fila_id`) |
| `load_info_graf.php` / `load_graf_2.php` | gráficos por fila |
| `load_col.php` | colaboradores (contrato/nivel) |
| `del_pri.php` | exclusão prioridade |
| `alt_sel.php` / `alt_campo.php` | toggle opções/campos serviço |

Padrão: `(int)` em IDs POST, `preg_replace` + regex em datas GET, bind em filtros dinâmicos. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **364 pass / 0 fail** (`php tests/run.php`).

## 28. Adendo — 16ª onda (bind tbl/rel/star/chat · 14/08/2026)

Prepared statements em estrelas, relatórios, tabelas de configuração e chat BKO:

| Arquivo | Foco |
|---|---|
| `load_star.php` | média estrela por atendente (`ate`) |
| `load_dados_dash_ind.php` | residual fila config + star |
| `load_dados_rel.php` | contrato/fila/empresa + loop horário (pausa/log/tma) |
| `tbl_sel.php` / `tbl_config_servicos.php` | opções/campos serviço |
| `pos_tbl_sel.php` / `mon_tbl_sel.php` | opções POS/MON |
| `hr_tbl_config_form.php` | horários da fila |
| `graf_rel_1.php` | status fila BKO |
| `chat-bko.php` | TMA atend, FAQ, procedimento assunto |

Padrão: `(int)` em IDs POST/include, bind em filtros dinâmicos e loops N+1. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **385 pass / 0 fail** (`php tests/run.php`).

## 29. Adendo — 17ª onda (bind chat-fila / forms / cad-usu / painel · 14/08/2026)

Prepared statements em chat solicitante, config forms POS/MON, edição de usuário e painel operacional:

| Arquivo | Foco |
|---|---|
| `chat-bko.php` | residual solicitante (`id_user`) |
| `chat-fila.php` | fila atendente, FAQ, assunto |
| `load_dados_dash_ind.php` | pendências atendente (`ate_resp`) |
| `load_dados_dash_ind_painel.php` | online por fila no loop |
| `pos_tbl_config_form.php` / `mon_tbl_config_form.php` | campos form por fila |
| `alt_cad_usu.php` | selects UF/contrato/empresa/regional/agência/fila |
| `load_painel.php` | filas por contrato no painel |

Padrão: `(int)` em IDs, bind em loops e includes parciais. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **400 pass / 0 fail** (`php tests/run.php`).

## 30. Adendo — 18ª onda (bind dashboards idx · 14/08/2026)

Prepared statements nos fluxos de dashboard do solicitante/atendente em `view/page/action/idx/`:

| Arquivo | Foco |
|---|---|
| `chat-bko.php` | fila BKO, UPDATE claim, chat_info, tma_atend, assunto |
| `dash-ate.php` | infoAte, infoAtendimento, INSERT tma, log_atendimento, pause |
| `dash-pause.php` | log_atendimento, tbl_pause |
| `dash-ava.php` | fila atendente, verificação status, filas por contrato (IN dinâmico) |
| `dash-cha.php` | fila solicitante, protocolos do dia |
| `dash-chat.php` | lista usuários, tbl_chat_info SELECT |
| `dash-idx.php` | pause, tma_atend, tabela dinâmica `tbl_in_dem_*` (whitelist + bind) |

Padrão: `(int)` em IDs de sessão, bind em filtros e IN dinâmico sanitizado, whitelist regex em tabela dinâmica. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **413 pass / 0 fail** (`php tests/run.php`).

## 31. Adendo — 19ª onda (bind com-idx / BKO forcado / acompanhamento / cad-usu · 14/08/2026)

Prepared statements em comunicação interna, bootstrap BKO forçado, painel de acompanhamento e cadastro de usuário:

| Arquivo | Foco |
|---|---|
| `com-idx.php` | selects colaboradores (nova conversa, massa, grupo) |
| `load_chat_bko_forcado.php` | pause, fila por contrato/fila, IN filas do usuário |
| `load_info_user.php` | score diário secondary, mensagens do chat |
| `load_user_logados.php` | contagem log_diario por fila |
| `cad-usu.php` | selects UF (cadastro e importação) |

Padrão: espelha `load_chat_bko.php` no forcado; `(int)` + bind em filtros de contrato/UF/fila. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **422 pass / 0 fail** (`php tests/run.php`).

## 32. Adendo — 20ª onda (bind chat / rotinas / verif · 14/08/2026)

Prepared statements nos partials de chat, rotinas incluídas e verificação de senha:

| Arquivo | Foco |
|---|---|
| `chat_com_ind.php` / `chat_com_ind-hist.php` | dt_visual, tbl_com_info, histórico msgs |
| `chat_ind.php` | claim BKO, histórico, motivo, msgs sistema, men_ini, POS, transferência fila |
| `rotina_pendencia.php` | loop pendências (UPDATE/DELETE por id_fila_chat) |
| `verif.php` | tbl_user_pass por user_id |
| `functxt.php` | export TXT atendimento |
| `horario_fila.php` | horário e ativa/inativa fila |

Padrão: bind em partials incluídos (`chat_ind.php`), rotinas de bootstrap e IN dinâmico sanitizado. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **433 pass / 0 fail** (`php tests/run.php`).

## 33. Adendo — 21ª onda (bind chat grupo / rotinas matinais · 14/08/2026)

Prepared statements em comunicação em grupo e rotinas cron incluídas no bootstrap:

| Arquivo | Foco |
|---|---|
| `chat_com.php` | view grupo, tbl_com_info, histórico, config/participantes IN, alteração grupo |
| `chat_com-hist.php` | espelha binds do chat_com (histórico) |
| `rotina_ocio.php` | encerra chats secondary antigos |
| `rotina.php` | limpeza TMA, dedup date_disp, inativa usuários inativos |

Padrão: corrige SQL quebrado em `tbl_com_msg_group` (faltava `AND`); IN de participantes sanitizado com `(int)`; loops de rotina com bind por ID. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **440 pass / 0 fail** (`php tests/run.php`).

## 34. Adendo — 22ª onda (bind logout / expurga / arquivos / chat_com-hist residual · 14/08/2026)

Prepared statements em logout, rotinas de expurgo de arquivos/TMA e residual do histórico de grupo:

| Arquivo | Foco |
|---|---|
| `chat_com-hist.php` | config grupo + participantes IN (residual) |
| `logout.php` | UPDATE log_diario; log atendimento via `logAtendimento()` |
| `expurga.php` | limpeza TMA órfão (secondary + delete) |
| `rotina_files.php` / `rotina_files_.php` | DELETE tbl_chat_files por id_file |
| `api/config.php` | seed inicial tbl_user_pass |

Padrão: reutiliza `logAtendimento()` no logout; IN de participantes sanitizado; loops com bind por ID. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **448 pass / 0 fail** (`php tests/run.php`).

## 35. Adendo — 23ª onda (replace secondary / alt_cad_usu UF / cols grupo · 14/08/2026)

Prepared statements em script de espelhamento secondary, bind residual de UF no cadastro de usuário e parsing seguro de `cols` em comunicação interna:

| Arquivo | Foco |
|---|---|
| `cnf/replace.php` | REPLACE linha a linha (fila, chat_info, log_atendimento, tma) |
| `cnf/func.php` | helpers `stReplaceNullable` e `stComColsHasUser` |
| `staff/alt_cad_usu.php` | filtro UF com `id_estado=?` |
| `staff/load_com_count.php` | membership grupo via `stComColsHasUser` |
| `staff/load_com_list.php` | membership grupo via `stComColsHasUser` |

Padrão: elimina bulk `VALUES (...)` concatenado; `(int)` em IDs; comparação de `cols` por token CSV em vez de `strpos`. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **464 pass / 0 fail** (`php tests/run.php`).

## 36. Adendo — 24ª onda (replace_msg / replace clone / painel BKO / st_fila_status · 14/08/2026)

Prepared statements em clones de migração secondary, bind residual no painel de indicadores e fetch de fila BKO:

| Arquivo | Foco |
|---|---|
| `cnf/replace_msg.php` | REPLACE msg linha a linha; corrige bug `$info5` → `$info1` |
| `cnf/replace_08042026.php` | espelha `replace.php` com logs ops |
| `staff/load_dados_dash_ind_painel.php` | agregado BKO: `fila_id=?` + `$dashPainelParams` |
| `cnf/st_fila_status.php` | `stChatBkoFetchFila`: `contrato_id=?`, `fila_id=?` |
| `staff/loadText_com.php` / `loadText_com_ind.php` | bloco img comentado com `chat_id=?` |

Padrão: elimina bulk `nomeCampo`/`VALUES` concatenado; reutiliza `stReplaceNullable`; params repetidos em subqueries agregadas. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **481 pass / 0 fail** (`php tests/run.php`).

## 37. Adendo — 25ª onda (access/session / API pública / dash_fila_live · 14/08/2026)

Prepared statements no bootstrap paralelo de sessão, API GET legado e dashboard operacional:

| Arquivo | Foco |
|---|---|
| `access/session.php` | user / permissão / log_diario INSERT+UPDATE com bind |
| `access/func.php` | `logAtendimento()` SELECT+INSERT com bind |
| `testes/functxt.php` | export atendimento/mensagens com `id_fila_chat=?` / `chat_id=?` |
| `api/index.php` | `nome_user=?` + `BETWEEN ? and ?` + validação `YYYY-MM-DD` |
| `view/api/new_pass.php` | `user_id=?` (script debug) |
| `staff/dash_fila_live.php` | `fila_id=?` / `contrato_id=?` em equipe, TATE, espera e acessos |
| `staff/load_contrato.php` | SQL comentado sem concatenar `$_POST['uf']` no nome da tabela |

Padrão: espelha `view/cnf/session.php` e `view/staff/load_api.php`; datas de API só aceitam formato ISO. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **505 pass / 0 fail** (`php tests/run.php`).

## 38. Adendo — 26ª onda (IN de contrato cad-* / analytics / usuários · 14/08/2026)

Helper `stParseIdCsv` + `stSqlInBind` para cláusula IN de contrato (CSV legado `'1','2'`) com placeholders:

| Arquivo | Foco |
|---|---|
| `cnf/func.php` | helpers `stParseIdCsv` / `stSqlInBind` (posicional e nomeado) |
| `cnf/cad-ass.php`, `cad-faq.php`, `cad-men.php`, `cad-age.php`, `cad-emp.php`, `cad-reg.php` | lista + dropdown IN bindado |
| `cnf/cad-fil.php`, `cad-ctt.php` | lista (nível > 1) + dropdown / assuntos por `contrato_id=?` |
| `idx/ia-insights.php`, `idx/gov-analytics.php` | combo de contratos com IN bindado |
| `staff/load_usuarios.php`, `export_usuarios_excel.php` | IN nomeado (`:ctt0`) junto com `:q` |
| `staff/dash-fila.php` | abas de contrato/fila com IN bindado |

Padrão: parse CSV → `(int)` → `IN (?,?,?)`; listagens com LIKE nomeado usam `:cttN` para não misturar estilos PDO. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **537 pass / 0 fail** (`php tests/run.php`).

## 39. Adendo — 27ª onda (dash_gov / dash_ia / dash_fila_live IN contrato · 14/08/2026)

Prepared statements no IN de contrato das APIs JSON de governança, insights e dashboard operacional; AuthZ de contrato deixa de usar `strpos` no CSV (falso positivo em IDs como `1` vs `12`):

| Arquivo | Foco |
|---|---|
| `staff/dash_gov_data.php` | `stSqlInBind` em unified/pendências/logins; `in_array` na permissão |
| `staff/dash_ia_insights_data.php` | AuthZ contrato via `stParseIdCsv` + `in_array` |
| `staff/dash_fila_live.php` | `$qryContrato` / `$qryFilaCtt` / `$qryUserCtt` / `$qryPendFilaCtt` com placeholders; params repetidos no agregado geral |

Padrão: CSV de sessão → IDs inteiros → `IN (?,?,?)`; AuthZ por lista, não substring. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **548 pass / 0 fail** (`php tests/run.php`).

## 40. Adendo — 28ª onda (cnf-dash / indicadores IN bind · 14/08/2026)

Prepared statements no dashboard de configurações e nos indicadores que ainda montavam `IN` com CSV sanitizado:

| Arquivo | Foco |
|---|---|
| `cnf/cnf-dash.php` | `stSqlInBind` + `cnfDashScalar/All/Row` com params |
| `staff/load_dados_dash_ind.php` | agregado contrato/fila com placeholders |
| `staff/load_dados_dash_ind_painel.php` | contrato IN bindado (fila já era `?`) |
| `load_dados_tme.php`, `tma.php`, `fila.php`, `atend.php`, `concluido.php`, `pendente.php` | ramo "todos os contratos" com `IN (?,?,?)` |

Padrão: elimina `$contratoIn` concatenado e `implode` de IDs no SQL. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **561 pass / 0 fail** (`php tests/run.php`).

## 41. Adendo — 29ª onda (XSS chat / pendências / graf_1 · 14/08/2026)

Endurecimento de XSS no fluxo de mensagens e bind residual em widgets de pendência/gráfico:

| Arquivo | Foco |
|---|---|
| `chat/assets/js/script.js` | `stEscapeHtml` / `stFormatChatPlainText`; texto plano não usa mais `.html(msg)` cru |
| `staff/loadText.php` | msg sem `<img` válido → `htmlspecialchars`; bloqueia `<script` em ramo img |
| `chat/chat_ind.php` | feedback de classificação (`retorno.msg`) escapado |
| `staff/load_dados_pend.php` | IN contrato bindado; tabela/protocolo com `htmlspecialchars` |
| `staff/load_user_logados.php` | remove variável `$contratos` morta |
| `staff/load_graf_1.php` | `stSqlInBind` + `execute($sqlParams)` no agregado do dia |

CSRF: validação central permanece em `view/cnf/session.php` (token + `hash_equals` em POST/PUT/PATCH/DELETE; `action.js` envia `X-CSRF-Token`). Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **573 pass / 0 fail** (`php tests/run.php`).

## 42. Adendo — 30ª onda (XSS com/grupo / CSRF access / limpeza dash_ind · 14/08/2026)

Helper central `stChatRenderPostedMsg` e endurecimento da comunicação interna; CSRF no bootstrap paralelo; limpeza de código morto:

| Arquivo | Foco |
|---|---|
| `cnf/func.php` | `stChatRenderPostedMsg` (texto escapado; img validada + link) |
| `staff/loadText.php` | delega ao helper (DRY com onda 29) |
| `staff/loadText_com.php`, `loadText_com_ind.php`, `loadText_group.php` | deixa de ecoar `$_POST['msg']` cru |
| `access/session.php` | CSRF espelhado (`hash_equals` em POST autenticado) |
| `load_dados_dash_ind.php`, `load_dados_dash_ind_painel.php` | remove `$contratos` morto (IN já bindado) |
| `tests/run.php` | E2E autenticado opcional via `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` |

Padrão: um ponto de renderização para msg POST; endpoints com/sem img compartilham regra. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **586 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 43. Adendo — 31ª onda (XSS client-side chat / IN dash_fila / pós-atendimento · 14/08/2026)

Sanitização DOM no cliente para HTML do chat e bind residual em dashboard operacional e pós-atendimento:

| Arquivo | Foco |
|---|---|
| `chat/assets/js/script.js` | `stSafeChatHtml` remove script/iframe/on*; `renderChatParagrafo` deixa de usar `innerHTML` cru |
| `script_grupo.js`, `script_com_group.js`, `script_com_msg.js`, `script_com_ind.js` | `.html(valor)` da bolha passa por `stSafeChatHtml` |
| `staff/loadText.php` | remove `<script>testLoad</script>` residual |
| `staff/dash_fila_live.php` | IN de usuários/filas com `stSqlInBind` (equipe, stats, pendência, espera) |
| `staff/load_pos_bko.php` | `fila_id=?` + IN de campo/assunto bindado |

Padrão: defesa em profundidade (servidor escapa + cliente remove vetores XSS); IDs inteiros em IN via placeholders. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **599 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 44. Adendo — 32ª onda (relatórios XSS/AuthZ / depósito / pos_config · 14/08/2026)

Escape de saída nos relatórios, AuthZ de contrato, whitelist da tabela dinâmica de pós e endurecimento de arquivos/feeds:

| Arquivo | Foco |
|---|---|
| `cnf/func.php` | helpers `stHtml` e `stContratoAllowed` |
| `staff/load_dados_rel.php` | AuthZ contrato; `stHtml` nas células; whitelist `tbl_in_pos_*` no lote da base |
| `staff/load_dados_rel_fila.php` | AuthZ + `stHtml`; feed de cancelamento/derruba via `stSafeChatHtml` |
| `staff/load_deposit_file.php` / `load_deposit_file_hist.php` | href só `file/...`; nome/link escapados |
| `staff/load_assunto.php` | título de opção escapado |
| `staff/pos_config_form.php` | `id_fila` inteiro; opções escapadas |
| `script.js` + JS com/grupo | `.html(valor)` de feed passa por `stSafeChatHtml` (upload com `<script>` operacional permanece) |

Padrão: saída HTML via `stHtml`; contrato POST conferido com CSV da sessão; tabela dinâmica só com regex numérica. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **612 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 45. Adendo — 33ª onda (monitoria XSS / AuthZ combos e gráficos · 14/08/2026)

Mesmo padrão da onda 32 na monitoria, combos de contrato e widgets de gráfico:

| Arquivo | Foco |
|---|---|
| `mon_config_form.php` / `mon_tbl_config_form.php` | `id_fila` inteiro; labels escapados |
| `pos_tbl_config_form.php` | labels escapados (espelho) |
| `load_monitoria.php` / `save_mon.php` | AuthZ contrato; pergunta/resposta com `stHtml`; JS só com nomes `[a-zA-Z0-9_]` |
| `cnf/func_input.php` | labels/opções da monitoria escapados |
| `load_fila.php`, `load_fila_user.php`, `load_rel_filas.php` | AuthZ contrato no combo |
| `load_empresa.php`, `load_agencia.php` | opções escapadas |
| `load_graf_1.php` | `array_intersect` com contratos da sessão |
| `load_graf_2.php` | AuthZ + `json_encode` no nome da situação |
| `load_chart_3.php`, `load_top_five.php`, `load_conc.php` | AuthZ contrato |

Padrão: combo/POST de contrato conferido com CSV da sessão; saída HTML via `stHtml`. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **631 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 46. Adendo — 34ª onda (pendências XSS/AuthZ / assuntos · 14/08/2026)

Pendências, relatório histórico e combos de assunto no mesmo padrão das ondas 32–33:

| Arquivo | Foco |
|---|---|
| `load_pend_info.php` | AuthZ contrato POST; motivo com `stHtml`; seletor `savePend` alinhado a `conteudo_pend_{filaChatId}` |
| `load_hist_pend.php` | AuthZ pelo `contrato_id` das msgs; metadados com `stHtml`; mensagens via `stChatRenderPostedMsg`; img rejeita `javascript:`; token via `json_encode`; depósito com `stSafeChatHtml` |
| `load_rel_pend.php` | AuthZ contrato; datas `de`/`ate` sanitizadas; células com `stHtml` |
| `load_assunto.php` / `load_assunto_fila.php` | AuthZ contrato POST; título escapado |
| `load_ass.php` / `load_assunto_men.php` | AuthZ pelo `contrato_id` da fila |
| `load_pend_alt_sol.php` / `load_pend_alt_bko.php` | nomes escapados; IDs inteiros |
| `alt_pend_sol.php` / `alt_pend_bko.php` / `save_pend_info.php` | AuthZ contrato da fila antes do UPDATE |

Não sanitizar no cliente respostas AJAX que devolvem `<script>` operacional (`load_pend_alt_*`, `load_hist_pend`, `detail()`). Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **653 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 47. Adendo — 35ª onda (histórico XSS/AuthZ / combos cadastro · 14/08/2026)

Histórico de atendimento e combos de cadastro no mesmo padrão das ondas 32–34:

| Arquivo | Foco |
|---|---|
| `load_hist.php` | AuthZ contrato; metadados com `stHtml`; msgs via `stChatRenderPostedMsg`; img rejeita `javascript:`; token via `json_encode`; depósito com `stSafeChatHtml` |
| `load_dados_hist.php` | AuthZ contrato; datas sanitizadas; células com `stHtml` |
| `load_contrato.php` | UF com bind; IN dos contratos da sessão; nomes escapados |
| `load_empresa.php` / `load_rel_emp.php` / `load_regional.php` | AuthZ + filtro `contrato_id=?` |
| `load_agencia.php` | AuthZ pelo contrato da regional; filtro `regional_id=?` |
| `load_col.php` / `load_municipio.php` | opções escapadas; IDs inteiros |
| `load_graf_1.php` | `id_fila` inteiro no CSS/HTML |
| `load_ass_json.php` | AuthZ pelo `contrato_id` da fila |

Cascata de cadastro (contrato → regional/empresa → agência) passa a respeitar o POST, em vez de listar todos os registros. Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **677 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 48. Adendo — 36ª onda (AuthZ cadastros / XSS POST em HTML · 14/08/2026)

Writes de configuração e eco de POST no HTML:

| Arquivo | Foco |
|---|---|
| `save_emp/reg/age/ass/fil/faq/men` | AuthZ contrato antes do INSERT |
| `alt_emp/reg/age/ass/fil/faq/men` | AuthZ pelo `contrato_id` do registro antes do UPDATE |
| `save_user.php` / `import_users.php` | AuthZ contrato; IDs inteiros no INSERT de usuário |
| `load_com_count.php` | `not` e badge via `json_encode` |
| `chat-bko.php` | timer usa `$indDiv` inteiro |
| `load_dados_ind.php` | dia sanitizado no link do PDF |
| `load_info_fila.php` | AuthZ da fila; id HTML inteiro |
| `load_info_fila_painel.php` / `painel_load_info_fila.php` | `fila` inteiro (painel TV permanece público) |
| `alt_config_mail.php` | `id` inteiro no JS |
| `painel_load_fila_ativa.php` | protocolo/tempo escapados; IDs inteiros no JS |

Painel TV continua sem login. Senha mestra operacional permanece. Sem DDL destrutivo, sem `password_hash`.

Testes: **708 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 49. Adendo — 37ª onda (AuthZ usuário/chat/serviço · 14/08/2026)

Writes de usuário, mensagem de chat e cadastro de serviço/contrato:

| Arquivo | Foco |
|---|---|
| `alt_user.php` | AuthZ do usuário alvo e do contrato novo; IDs inteiros |
| `alt_cad_usu.php` | AuthZ; campos/opções com `stHtml`; combo de contrato filtrado pela sessão |
| `alt_config_filas.php` / `alt_filas_config.php` | AuthZ; nomes de fila escapados |
| `save_msg.php` / `save_msg_transfer.php` / `save_msg_fim.php` | `contrato_id` vem do chat, não do POST |
| `save_ser.php` / `alt_ser.php` | AuthZ contrato |
| `save_ctt.php` | criação só para nível ≤ 1 |
| `alt_ctt.php` | AuthZ no `id_contrato` |
| `config_servicos.php` | `id_servico` inteiro; opções escapadas |
| `outChat.php` | exige sessão; POST sanitizado |

Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **733 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

## 50. Adendo — 38ª onda (AuthZ COM/flags / XSS cad-usu · 14/08/2026)

AuthZ de comunicação interna e flags de contrato; XSS no cadastro/import de usuário; prioridades e config IA permanecem globais:

| Arquivo | Foco |
|---|---|
| `save_pri.php` | Nome vazio aborta INSERT; prioridade global (sem `stContratoAllowed`) |
| `alt_pri.php` | `$id < 1` antes do UPDATE |
| `del_pri.php` | Só nível 0; `$idPri < 1`; reset incondicional de assuntos; modal `json_encode` |
| `save_msg_com.php` / `save_msg_com_ind.php` | 1:1: participante + contrato da conversa; grupo: equipe/`stComColsHasUser`; `rem_id` da sessão; rejeita `javascript:` no src |
| `alt_ctt_com*` + `alt_ctt_env_img/file` | `$id < 1` + `stContratoAllowed` antes do UPDATE |
| `save_ia_config.php` | Whitelist `salvar`/`status`; 403 se nível > 1; global |
| `cad-usu.php` | `stHtml` nas options; combo import filtrado (`stSqlInBind`); `urlDownload` via `json_encode` |
| `import_users.php` | Células `stHtml`; lookup regional/agência com `contrato_id=?` |

Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **777 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

QA (Kai) e Security (James): aprovado com ressalvas, sem blocker. Residuais para onda 39: `save_new_chat.php`, `save_new_grupo.php`, `alt_grupo.php`, `send_msg_massa.php`; `empresa_id` no import sem amarra a contrato; SMTP/token no upload (já conhecidos).

## 51. Adendo — 39ª onda (AuthZ COM writes / save JS · 14/08/2026)

AuthZ nos writes de comunicação interna (nova conversa, grupo, massa) e restauração do script operacional no save COM; `empresa_id` do import amarrado ao contrato:

| Arquivo | Foco |
|---|---|
| `save_new_chat.php` | Flag `new_conv`; dest válido; contrato sessão+dest; `grupo_com`/`grupo_nome` vazios |
| `save_new_grupo.php` | Nível 0 + flag `grupos`; destinos extras omitidos se inválidos |
| `alt_grupo.php` | Só nível 0; AuthZ pelo contrato do grupo; script só após UPDATE |
| `send_msg_massa.php` | Nível &lt; 2 + `men_massa`; skip dest inválido; `rem` da sessão |
| `import_users.php` | `empresa_id` só se bater com o contrato; SMTP/token não mexidos |
| `script_com_group.js` / `script_com_ind.js` / `script_com_msg.js` | Save: `$(feed).html(valor)` sem `stSafeChatHtml`; load continua sanitizado |

Senha mestra operacional permanece. Sem DDL destrutivo, sem fechar painel TV, sem `password_hash`.

Testes: **803 pass / 0 fail** (`php tests/run.php`; E2E +3 quando `ST_PILOTO_TEST_USER` / `ST_PILOTO_TEST_PASS` definidos).

QA (Kai) e Security (James): aprovado com ressalvas, sem blocker. Residuais para onda 40: validar `grupo_com` não vazio em `alt_grupo`; escapar `grupo_nome` nas views; SMTP/token no upload (já conhecidos).
