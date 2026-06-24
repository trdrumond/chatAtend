# DOCUMENTAÇÃO TÉCNICA - SISTEMA SOLVETASK
## Sistema de Chat e Atendimento

---

## 1. VISÃO GERAL DO SISTEMA

### 1.1 O que é o Solvetask
O Solvetask é um sistema de chat para atendimento ao cliente desenvolvido em PHP. O sistema permite comunicação entre solicitantes e equipes de backoffice através de uma interface web com chat em tempo real.

### 1.2 Tecnologias Utilizadas
- **PHP** - Linguagem principal
- **MySQL** - Banco de dados
- **JavaScript/jQuery** - Interface do usuário
- **WebSocket** - Chat em tempo real
- **Bootstrap** - Framework CSS
- **TinyMCE** - Editor de texto

### 1.3 Estrutura do Projeto
```
solvetask/piloto/
├── access/          # Configurações de acesso
├── api/             # API do sistema
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

## 2. CONFIGURAÇÃO DO SISTEMA

### 2.1 Banco de Dados
**Arquivo**: `view/cnf/conexao.php`
- **Host**: localhost
- **Database**: web_chatlogos_piloto
- **Usuário**: acesso.sistemas
- **Senha**: tDHMpeXVTzQAZsGD

### 2.2 Configurações do Sistema
**Arquivo**: `view/cnf/config.php`
- Carrega configurações da tabela `tbl_config_sis`
- Define título, cores e configurações gerais

### 2.3 WebSocket
**Arquivo**: `view/chat/assets/js/script.js`
- **Desenvolvimento**: ws://localhost:8080
- **Produção**: wss://chat.logos-ma.com.br/chatlogos/

---

## 3. SISTEMA DE LOGIN E ACESSO

### 3.1 Fluxo de Login
1. **Página inicial** (`index.php`) - Formulário de login
2. **Validação** (`login.php`) - Verifica credenciais
3. **Sessão** (`view/cnf/session.php`) - Inicia sessão do usuário

### 3.2 Níveis de Usuário
- **Nível 0**: Master/Administrador
- **Nível 1**: Administrador de Contrato  
- **Nível 2**: Acompanhamento
- **Nível 4**: Backoffice/Atendente
- **Nível 5**: Solicitante

### 3.3 Validação de Senha
**Arquivo**: `view/cnf/func.php`
- Função `senhaValida()` verifica complexidade da senha
- Requer caracteres especiais, maiúsculas, minúsculas e números

---

## 4. SISTEMA DE CHAT

### 4.1 Tipos de Chat

#### 4.1.1 Chat Individual
**Arquivo**: `view/chat/chat_ind.php`
- Comunicação 1:1 entre solicitante e backoffice
- Sistema de filas de atendimento
- Upload de arquivos (5MB)
- Mensagens rápidas pré-definidas

#### 4.1.2 Chat em Grupo
**Arquivo**: `view/chat/chat_com.php`
- Comunicação em grupo
- Múltiplos participantes
- Upload de arquivos (10MB)
- Gestão de participantes

#### 4.1.3 Chat Geral
**Arquivo**: `view/chat/chat_geral.php`
- Chat público via WebSocket
- Comunicação em tempo real

### 4.2 Funcionalidades do Chat
- **Mensagens em tempo real** via WebSocket
- **Upload de arquivos** com validação
- **Indicador de digitação** ("usuário está digitando...")
- **Mensagens rápidas** com templates
- **Transferência de atendimento** entre filas
- **Notificações sonoras** e visuais

---

## 5. SISTEMA DE FILAS

### 5.1 Gestão de Filas
- **Tabela**: `tbl_config_fila`
- **Configuração**: Filas por contrato
- **Status**: Ativo/Inativo

### 5.2 Estados do Atendimento
- **Aguardando**: Na fila de espera
- **Em Atendimento**: Sendo atendido
- **Pausado**: Atendimento pausado
- **Concluído**: Atendimento finalizado
- **Cancelado**: Atendimento cancelado

### 5.3 Métricas
- **TMA**: Tempo Médio de Atendimento
- **TME**: Tempo Médio de Espera
- **TA**: Tempo de Atendimento
- **TE**: Tempo de Espera

---

## 6. SISTEMA DE ARQUIVOS

### 6.1 Upload de Arquivos
**Arquivos**: `view/staff/load_file.php` e `view/staff/save_file.php`
- **Chat Individual**: Máximo 5MB
- **Chat Grupo**: Máximo 10MB
- **Tipos permitidos**: .jpg, .png, .doc, .docx, .xls, .xlsx, .pdf

### 6.2 Armazenamento
- **Chat Individual**: `view/file/`
- **Chat Grupo**: `view/file_com/`
- **Nomenclatura**: Token + timestamp + extensão

---

## 7. DASHBOARDS E RELATÓRIOS

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

## 8. SISTEMA DE PERMISSÕES

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

## 9. CONFIGURAÇÕES

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

## 10. SEGURANÇA

### 10.1 Validação de Dados
- Escape de caracteres especiais
- Validação de tipos de arquivo
- Limitação de tamanho de upload
- Filtros de conteúdo HTML

### 10.2 Controle de Sessão
- Timeout automático
- Verificação de sessão ativa
- Logout automático por inatividade

---

## 11. MANUTENÇÃO

### 11.1 Logs do Sistema
- **Log de Acesso**: `tbl_log_diario`
- **Log de Atendimento**: `tbl_log_atendimento`
- **Log de Mensagens**: Tabelas de chat

### 11.2 Rotinas de Manutenção
**Arquivo**: `view/cnf/rotina.php`
- Limpeza de dados antigos
- Atualização de estatísticas
- Verificação de integridade

---

## 12. PROBLEMAS COMUNS

### 12.1 Conexão WebSocket
- Verificar se o servidor WebSocket está rodando
- Verificar configuração de porta (8080)
- Testar conexão em localhost

### 12.2 Upload de Arquivos
- Verificar permissões do diretório
- Verificar limite de upload no PHP
- Verificar espaço em disco

### 12.3 Sessão Expirada
- Verificar configuração de timeout
- Verificar se o servidor está mantendo sessões

---

## 13. INFORMAÇÕES DE ACESSO

### 13.1 URLs
- **Desenvolvimento**: https://localhost/solvetask/piloto/
- **Produção**: https://chat.logos-ma.com.br/

### 13.2 Usuários de Teste
- **admin** / logos@1 (Master)
- **01** / 01@logos (Backoffice)
- **02** / 02@logos (Solicitante)
- **03** / 03@logos (Backoffice)
- **04** / 04@logos (Solicitante)

---

## 14. ESTRUTURA DE ARQUIVOS PRINCIPAIS

### 14.1 Arquivos de Configuração
- `access/conexao.php` - Conexão com banco
- `view/cnf/config.php` - Configurações do sistema
- `view/cnf/session.php` - Controle de sessão

### 14.2 Arquivos de Chat
- `view/chat/chat_ind.php` - Chat individual
- `view/chat/chat_com.php` - Chat em grupo
- `view/chat/chat_geral.php` - Chat geral
- `view/chat/assets/js/script.js` - JavaScript do chat

### 14.3 Arquivos de Interface
- `index.php` - Página de login
- `login.php` - Validação de login
- `view/index.php` - Interface principal
- `view/page/page-cnf.php` - Página administrativa
- `view/page/page-idx.php` - Página operacional

### 14.4 Arquivos de Processamento
- `view/staff/` - Processamento backend
- `api/index.php` - API de relatórios
- `view/cnf/rotina.php` - Rotinas de manutenção

---

**Fim da Documentação Técnica - Sistema Solvetask**

*Documento baseado na análise real do código-fonte*
*Versão: 1.0*

