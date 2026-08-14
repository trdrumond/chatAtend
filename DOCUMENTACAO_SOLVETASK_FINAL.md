# DOCUMENTAÇÃO TÉCNICA - SISTEMA SOLVETASK

---

## 1. VISÃO GERAL - TECNOLOGIAS E ARQUITETURA

### 1.1 Descrição do Sistema
O Solvetask é um sistema de chat para atendimento ao cliente desenvolvido em PHP. Permite comunicação entre solicitantes e equipes de backoffice através de interface web com chat em tempo real.

### 1.2 Tecnologias Utilizadas
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **Comunicação**: WebSocket
- **Editor**: TinyMCE
- **Framework CSS**: Bootstrap 5
- **Ícones**: Font Awesome

### 1.3 Arquitetura
```
solvetask/piloto/
├── access/          # Configurações de acesso
├── api/             # APIs do sistema
├── view/            # Interface principal
│   ├── cnf/        # Configurações centrais
│   ├── chat/       # Sistema de chat
│   ├── content/    # Páginas do sistema
│   ├── page/       # Páginas principais
│   └── staff/      # Processamento backend
├── css/             # Estilos
├── imagem/          # Imagens
└── docs/            # Documentação
```

---

## 2. CONFIGURAÇÃO - REQUISITOS E INSTALAÇÃO

### 2.1 Requisitos do Sistema
- **Servidor Web**: Apache 2.4+ ou Nginx
- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior
- **Extensões PHP**: PDO, PDO_MySQL, JSON, GD, mbstring

### 2.2 Configuração do Banco de Dados
**Arquivo**: `view/cnf/conexao.php`
- **Host**: localhost
- **Database**: web_chatlogos_piloto
- **Usuário**: acesso.sistemas
- **Senha**: <SENHA_DB_LOCAL>

### 2.3 Configuração do WebSocket
**Arquivo**: `view/chat/assets/js/script.js`
- **Desenvolvimento**: ws://localhost:8080
- **Produção**: wss://chat.logos-ma.com.br/chatlogos/

---

## 3. AUTENTICAÇÃO - SEGURANÇA E NÍVEIS DE ACESSO

### 3.1 Fluxo de Autenticação
1. **Login** (`index.php`) - Formulário de credenciais
2. **Validação** (`login.php`) - Verificação no banco
3. **Sessão** (`view/cnf/session.php`) - Inicialização da sessão

### 3.2 Níveis de Usuário
| Nível | Descrição | Permissões |
|-------|-----------|------------|
| **0** | Master/Administrador | Acesso total |
| **1** | Administrador de Contrato | Gestão de contratos |
| **2** | Acompanhamento | Visualização de relatórios |
| **4** | Backoffice/Atendente | Atendimento e filas |
| **5** | Solicitante | Acesso ao chat |

### 3.3 Validação de Senha
**Arquivo**: `view/cnf/func.php`
- Função `senhaValida()` verifica complexidade
- Requer caracteres especiais, maiúsculas, minúsculas e números

---

## 4. SISTEMA DE CHAT - COMUNICAÇÃO EM TEMPO REAL

### 4.1 Tipos de Chat
- **Chat Individual** (`view/chat/chat_ind.php`) - 1:1 entre solicitante e backoffice
- **Chat em Grupo** (`view/chat/chat_com.php`) - Múltiplos participantes
- **Chat Geral** (`view/chat/chat_geral.php`) - Público via WebSocket

### 4.2 Funcionalidades
- Mensagens em tempo real via WebSocket
- Upload de arquivos (5MB individual, 10MB grupo)
- Indicador de digitação
- Mensagens rápidas pré-definidas
- Transferência de atendimento
- Notificações sonoras e visuais

### 4.3 Estrutura de Mensagens
```javascript
var msg = {
    'flagMsg': 'true',
    'chatId': chatId,
    'userRemetente': userRemetente.value,
    'userDestinatario': userDestinatario.value,
    'name': inp_name.value,
    'msg': inp_message.value,
    'dataHora': str_data,
    'img': inp_img.value
};
```

---

## 5. FILAS E ATENDIMENTO - GESTÃO DE DEMANDAS

### 5.1 Gestão de Filas
- **Tabela**: `tbl_config_fila`
- **Configuração**: Filas por contrato
- **Status**: Ativo/Inativo

### 5.2 Estados do Atendimento
| Status | Código | Descrição |
|--------|--------|-----------|
| **Aguardando** | 1 | Na fila de espera |
| **Em Atendimento** | 2 | Sendo atendido |
| **Pausado** | 3 | Atendimento pausado |
| **Concluído** | 4 | Atendimento finalizado |
| **Cancelado** | 5 | Atendimento cancelado |

### 5.3 Métricas de Atendimento
- **TMA**: Tempo Médio de Atendimento
- **TME**: Tempo Médio de Espera
- **TA**: Tempo de Atendimento
- **TE**: Tempo de Espera

---

## 6. ARQUIVOS E UPLOAD - SISTEMA DE UPLOAD

### 6.1 Configuração de Upload
- **Chat Individual**: Máximo 5MB
- **Chat Grupo**: Máximo 10MB
- **Tipos permitidos**: .jpg, .png, .doc, .docx, .xls, .xlsx, .pdf

### 6.2 Processo de Upload
**Arquivos**: `view/staff/load_file.php` e `view/staff/save_file.php`
1. Validação de tamanho e tipo
2. Geração de nome único (token + timestamp)
3. Upload para diretório específico
4. Registro no banco de dados
5. Notificação no chat

### 6.3 Armazenamento
- **Chat Individual**: `view/file/`
- **Chat Grupo**: `view/file_com/`

---

## 7. RELATÓRIOS - DASHBOARDS E MÉTRICAS

### 7.1 Dashboard Administrativo
**Arquivo**: `view/page/action/cnf/cnf-dash.php`
- Total de usuários por nível
- Usuários ativos hoje
- Filas ativas/inativas

### 7.2 Dashboard Operacional
**Arquivo**: `view/page/action/idx/dash-idx.php`
- Fila de atendimentos
- Status de pausas
- Atendimentos em andamento

### 7.3 API de Relatórios
**Arquivo**: `api/index.php`
- **Parâmetros**: u=usuario, dd=data_inicio, da=data_fim
- **Retorno**: JSON com dados de atendimentos

---

## 8. PERMISSÕES - CONTROLE DE ACESSO

### 8.1 Menus por Nível
**Arquivo**: `view/content/menu/menu-cnf.php`
- **Usuários**: Gestão de usuários
- **Regional**: Gestão regional
- **Empresa**: Gestão de empresas
- **Agência**: Gestão de agências
- **Assuntos**: Gestão de assuntos
- **Prioridades**: Gestão de prioridades
- **FAQ**: Perguntas frequentes
- **Mensagem**: Mensagens rápidas
- **Log Acesso**: Logs de acesso
- **Filas**: Gestão de filas
- **Contrato**: Gestão de contratos

### 8.2 Controle de Acesso
- Verificação de nível de usuário
- Exibição condicional de menus
- Controle de funcionalidades por permissão

---

## 9. CONFIGURAÇÕES - PERSONALIZAÇÃO

### 9.1 Configurações do Sistema
**Tabela**: `tbl_config_sis`
- Título do sistema
- Cores da interface
- Configurações de tempo
- Imagens e logos

### 9.2 Configurações de Usuário
**Tabela**: `tbl_user`
- Dados pessoais
- Nível de acesso
- Permissões específicas
- Configurações de chat

---

## 10. SEGURANÇA - VALIDAÇÕES E PROTEÇÕES

### 10.1 Validação de Dados
- Escape de caracteres especiais
- Validação de tipos de arquivo
- Limitação de tamanho de upload
- Filtros de conteúdo HTML

### 10.2 Controle de Sessão
- Timeout automático
- Verificação de sessão ativa
- Logout automático por inatividade

### 10.3 Validação de Senha
- Complexidade obrigatória
- Caracteres especiais, maiúsculas, minúsculas, números
- Tamanho mínimo de 8 caracteres

---

## 11. APIS - INTEGRAÇÕES E ENDPOINTS

### 11.1 API de Relatórios
**Endpoint**: `api/index.php`
- **Método**: GET
- **Parâmetros**: u=usuario, dd=data_inicio, da=data_fim
- **Retorno**: JSON com dados de atendimentos

### 11.2 WebSocket
- **Protocolo**: WS/WSS
- **Porta**: 8080 (desenvolvimento)
- **Funcionalidades**: Chat em tempo real, notificações

---

## 12. MANUTENÇÃO - LOGS E MONITORAMENTO

### 12.1 Logs do Sistema
- **Log de Acesso**: `tbl_log_diario`
- **Log de Atendimento**: `tbl_log_atendimento`
- **Log de Mensagens**: Tabelas de chat

### 12.2 Rotinas de Manutenção
**Arquivo**: `view/cnf/rotina.php`
- Limpeza de dados antigos
- Atualização de estatísticas
- Verificação de integridade

---

## 13. TROUBLESHOOTING - SOLUÇÃO DE PROBLEMAS

### 13.1 Problemas Comuns
1. **Conexão WebSocket**: Verificar servidor e porta
2. **Upload de Arquivos**: Verificar permissões e limites
3. **Sessão Expirada**: Verificar configuração de timeout
4. **Erro de Banco**: Verificar credenciais e conexão

### 13.2 Logs de Debug
- Ativar `error_reporting` em desenvolvimento
- Verificar logs do servidor web
- Monitorar logs do banco de dados

---

## 14. BACKUP - ESTRATÉGIAS DE RECUPERAÇÃO

### 14.1 Backup do Banco de Dados
```bash
mysqldump -u usuario -p web_chatlogos_piloto > backup.sql
```

### 14.2 Backup de Arquivos
- Diretório `view/file/`
- Diretório `view/file_com/`
- Configurações do sistema

---

## 15. PERFORMANCE - OTIMIZAÇÕES

### 15.1 Otimizações Implementadas
- Cache de consultas frequentes
- Compressão de arquivos
- Minificação de CSS/JS

### 15.2 Recomendações
- Monitoramento de uso de memória
- Otimização de consultas SQL
- Implementação de CDN

---

## 16. DESENVOLVIMENTO - PADRÕES DE CÓDIGO

### 16.1 Padrões de Código
- Nomenclatura em português
- Comentários em português
- Estrutura modular
- Separação de responsabilidades

### 16.2 Convenções
- Arquivos PHP com extensão `.php`
- JavaScript com extensão `.js`
- CSS com extensão `.css`

---

## 17. CONTATO - INFORMAÇÕES DE SUPORTE

### 17.1 Acesso ao Sistema
- **Desenvolvimento**: https://localhost/solvetask/piloto/
- **Produção**: https://chat.logos-ma.com.br/

### 17.2 Usuários de Teste
- **admin** / logos@1 (Master)
- **01** / 01@logos (Backoffice)
- **02** / 02@logos (Solicitante)
- **03** / 03@logos (Backoffice)
- **04** / 04@logos (Solicitante)

---

## 18. ANEXOS - SCRIPTS E CONFIGURAÇÕES

### 18.1 Arquivos Principais
- `index.php` - Página de login
- `login.php` - Validação de login
- `view/index.php` - Interface principal
- `view/cnf/conexao.php` - Conexão com banco
- `view/cnf/session.php` - Controle de sessão

### 18.2 Configurações de Servidor
- **Apache**: Configuração de rewrite e cache
- **PHP**: Configurações de upload e sessão
- **MySQL**: Configurações de conexão

---

**Fim da Documentação Técnica - Sistema Solvetask**

*Documento baseado na análise real do código-fonte*
*Versão: 1.0*

