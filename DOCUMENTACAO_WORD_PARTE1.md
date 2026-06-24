# DOCUMENTAÇÃO TÉCNICA - SISTEMA SOLVETASK
## Formatação para Microsoft Word

---

## 1. VISÃO GERAL DO SISTEMA

### 1.1 Descrição do Sistema
O Solvetask é um sistema de atendimento ao cliente baseado em chat em tempo real, desenvolvido em PHP com interface web responsiva. O sistema permite comunicação entre solicitantes e equipes de backoffice através de uma plataforma de chat integrada com sistema de filas e gestão de atendimentos.

### 1.2 Tecnologias Utilizadas
| Tecnologia | Versão | Descrição |
|------------|--------|-----------|
| **Backend** | PHP 7.4+ | Linguagem de programação principal |
| **Banco de Dados** | MySQL 5.7+ | Sistema de gerenciamento de dados |
| **Frontend** | HTML5, CSS3, JavaScript | Interface do usuário |
| **Comunicação** | WebSocket | Chat em tempo real |
| **Editor** | TinyMCE | Editor de texto rico |
| **Gráficos** | AmCharts | Visualização de dados |
| **Framework CSS** | Bootstrap 5 | Framework de interface |
| **Ícones** | Font Awesome | Biblioteca de ícones |

### 1.3 Arquitetura do Sistema
```
Estrutura de Diretórios:
├── access/                 # Configurações de acesso e conexão
├── api/                   # APIs REST do sistema
├── cache/                 # Cache e otimizações
├── css/                   # Estilos CSS globais
├── imagem/                # Imagens e ícones
├── painel/                # Painel administrativo
├── testes/                # Scripts de teste
├── view/                  # Interface principal do sistema
│   ├── cnf/              # Configurações centrais
│   ├── chat/             # Módulo de chat
│   ├── content/          # Conteúdo das páginas
│   ├── css/              # Estilos específicos
│   ├── file/             # Arquivos enviados
│   ├── img/              # Imagens do sistema
│   ├── js/               # Scripts JavaScript
│   ├── page/             # Páginas do sistema
│   └── staff/            # Processamento backend
└── docs/                 # Documentação
```

---

## 2. CONFIGURAÇÃO E INSTALAÇÃO

### 2.1 Requisitos do Sistema

#### 2.1.1 Requisitos Mínimos
- **Servidor Web**: Apache 2.4+ ou Nginx
- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior
- **Memória RAM**: 2GB mínimo
- **Espaço em Disco**: 1GB para instalação

#### 2.1.2 Extensões PHP Necessárias
- PDO (PHP Data Objects)
- PDO_MySQL
- JSON
- GD (para manipulação de imagens)
- mbstring (para strings multibyte)
- OpenSSL (para conexões seguras)

### 2.2 Configuração do Banco de Dados

#### 2.2.1 Parâmetros de Conexão
| Parâmetro | Valor |
|-----------|-------|
| **Host** | localhost |
| **Porta** | 3306 |
| **Database** | web_chatlogos_piloto |
| **Usuário** | acesso.sistemas |
| **Senha** | tDHMpeXVTzQAZsGD |
| **Charset** | utf8 |

#### 2.2.2 Arquivo de Configuração
**Localização**: `view/cnf/conexao.php`

```php
<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Fortaleza');

$host = 'localhost';
$usuario = 'acesso.sistemas';
$senha = 'tDHMpeXVTzQAZsGD';
$banco = 'web_chatlogos_piloto';

$dsn = "mysql:host={$host};port=3306;dbname={$banco};charset=utf8";

try {
    $PDO = new PDO($dsn, $usuario, $senha);
} catch (PDOException $e) {
    die($e->getMessage());
}
?>
```

### 2.3 Configuração do WebSocket

#### 2.3.1 Configuração para Desenvolvimento
**Arquivo**: `view/chat/assets/js/script.js`

```javascript
// Configuração para localhost
if(hostname == 'localhost') {
    var prot = 'ws';
    var port = ':8080';
} else {
    var prot = 'wss';
    var port = '/chatlogos/';
}

var host = prot + '://' + hostname + port;
var conn = new WebSocket(host);
```

#### 2.3.2 Configuração para Produção
- **Protocolo**: WSS (WebSocket Secure)
- **Host**: chat.logos-ma.com.br
- **Porta**: 443 (padrão HTTPS)

---

## 3. SISTEMA DE AUTENTICAÇÃO E SEGURANÇA

### 3.1 Fluxo de Autenticação

#### 3.1.1 Processo de Login
1. **Captura de Credenciais** (`index.php`)
   - Usuário insere login e senha
   - Dados são codificados em base64
   - Redirecionamento para validação

2. **Validação de Credenciais** (`login.php`)
   - Decodificação dos dados
   - Consulta no banco de dados
   - Geração de hash SHA1 da senha
   - Inicialização da sessão PHP

3. **Controle de Sessão** (`view/cnf/session.php`)
   - Verificação de sessão ativa
   - Carregamento de dados do usuário
   - Definição de permissões

### 3.2 Níveis de Usuário

#### 3.2.1 Hierarquia de Acesso
| Nível | Descrição | Permissões |
|-------|-----------|------------|
| **0** | Master/Administrador | Acesso total ao sistema |
| **1** | Administrador de Contrato | Gestão de contratos específicos |
| **2** | Acompanhamento | Visualização de relatórios |
| **4** | Backoffice/Atendente | Atendimento e gestão de filas |
| **5** | Solicitante | Acesso apenas ao chat |

#### 3.2.2 Sistema de Permissões
**Arquivo**: `view/cnf/session.php`

```php
$sql = "SELECT mosaico, menu_idx, menu_cnf, cad_cnf 
        FROM tbl_nivel 
        WHERE id_nivel = " . $infoUser['nivel_id'];

$mosaico = explode(",", $infoNivel['mosaico']);
$menu_idx = explode(",", $infoNivel['menu_idx']);
$menu_cnf = explode(",", $infoNivel['menu_cnf']);
```

### 3.3 Validação de Senhas

#### 3.3.1 Critérios de Segurança
- **Caracteres Especiais**: Obrigatório
- **Letras Maiúsculas**: Obrigatório
- **Letras Minúsculas**: Obrigatório
- **Números**: Obrigatório
- **Tamanho Mínimo**: 8 caracteres
- **Tamanho Máximo**: 20 caracteres

#### 3.3.2 Função de Validação
**Arquivo**: `view/cnf/func.php`

```php
function senhaValida($senha) {
    $prog = 0;
    
    // Verificação de caracteres especiais
    if(preg_match('/[^a-zA-Z\d]/', $senha)) {
        $prog++;
    }
    
    // Verificação de letras minúsculas
    if(preg_match('/[a-z]+/', $senha)) {
        $prog++;
    }
    
    // Verificação de letras maiúsculas
    if(preg_match('/[A-Z]+/', $senha)) {
        $prog++;
    }
    
    // Verificação de números
    if(preg_match('/[0-9]+/', $senha)) {
        $prog++;
    }
    
    // Verificação de tamanho
    if(strlen($senha) > 8 && strlen($senha) < 20) {
        $prog++;
    }
    
    return $prog * 20; // Retorna porcentagem de força
}
```

---

## 4. SISTEMA DE CHAT E COMUNICAÇÃO

### 4.1 Tipos de Chat

#### 4.1.1 Chat Individual
**Arquivo**: `view/chat/chat_ind.php`
- **Comunicação**: 1:1 entre solicitante e backoffice
- **Funcionalidades**:
  - Sistema de filas de atendimento
  - Transferência entre filas
  - Upload de arquivos (5MB)
  - Mensagens rápidas
  - Indicador de digitação

#### 4.1.2 Chat em Grupo
**Arquivo**: `view/chat/chat_com.php`
- **Comunicação**: Múltiplos participantes
- **Funcionalidades**:
  - Gestão de participantes
  - Upload de arquivos (10MB)
  - Mensagens em tempo real
  - Histórico de conversas

#### 4.1.3 Chat Geral
**Arquivo**: `view/chat/chat_geral.php`
- **Comunicação**: Público via WebSocket
- **Funcionalidades**:
  - Comunicação em tempo real
  - Notificações instantâneas
  - Interface responsiva

### 4.2 Estrutura de Mensagens

#### 4.2.1 Formato JSON
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

#### 4.2.2 Tipos de Mensagem
| Tipo | Flag | Descrição |
|------|------|-----------|
| **Mensagem Normal** | flagMsg | Mensagem de usuário |
| **Sistema** | flagSys | Mensagem do sistema |
| **Atenção** | flagAtent | Solicitação de atenção |
| **Transferência** | flagTransfer | Transferência de atendimento |
| **Finalização** | flagFim | Encerramento de chat |
| **Arquivo** | flagFile | Upload de arquivo |
| **Digitação** | flagDig | Indicador de digitação |

### 4.3 Funcionalidades Avançadas

#### 4.3.1 Upload de Arquivos
- **Tamanho Máximo**: 5MB (individual), 10MB (grupo)
- **Tipos Permitidos**: .jpg, .png, .doc, .docx, .xls, .xlsx, .pdf
- **Validação**: Tamanho e tipo de arquivo
- **Armazenamento**: Diretórios específicos por tipo

#### 4.3.2 Sistema de Notificações
- **Sonoras**: Diferentes sons para tipos de notificação
- **Visuais**: Indicadores de nova mensagem
- **Push**: Notificações do navegador

---

## 5. SISTEMA DE FILAS E ATENDIMENTO

### 5.1 Gestão de Filas

#### 5.1.1 Configuração de Filas
**Tabela Principal**: `tbl_config_fila`
- **Campos**:
  - `id_fila`: Identificador único
  - `nome_fila`: Nome da fila
  - `contrato_id`: Contrato associado
  - `ativo`: Status da fila (1=ativo, 0=inativo)

#### 5.1.2 Estados do Atendimento
| Status | Código | Descrição |
|--------|--------|-----------|
| **Aguardando** | 1 | Na fila de espera |
| **Em Atendimento** | 2 | Sendo atendido |
| **Pausado** | 3 | Atendimento pausado |
| **Concluído** | 4 | Atendimento finalizado |
| **Cancelado** | 5 | Atendimento cancelado |

### 5.2 Métricas de Atendimento

#### 5.2.1 Indicadores de Performance
- **TMA (Tempo Médio de Atendimento)**: Tempo total do atendimento
- **TME (Tempo Médio de Espera)**: Tempo na fila de espera
- **TA (Tempo de Atendimento)**: Tempo efetivo de atendimento
- **TE (Tempo de Espera)**: Tempo de espera na fila

#### 5.2.2 Cálculo de Métricas
```sql
-- TMA (Tempo Médio de Atendimento)
SELECT sec_to_time(avg(time_to_sec(ta))) as tma 
FROM tbl_chat_fila 
WHERE ta IS NOT NULL 
AND status_fila >= 4 
AND fila_id = ?

-- TME (Tempo Médio de Espera)
SELECT sec_to_time(avg(time_to_sec(te))) as tme 
FROM tbl_chat_fila 
WHERE te IS NOT NULL 
AND te <> '' 
AND status_fila >= 4 
AND fila_id = ?
```

---

## 6. SISTEMA DE ARQUIVOS E UPLOAD

### 6.1 Configuração de Upload

#### 6.1.1 Limitações por Tipo de Chat
| Tipo de Chat | Tamanho Máximo | Diretório |
|--------------|----------------|-----------|
| **Individual** | 5MB | `view/file/` |
| **Grupo** | 10MB | `view/file_com/` |

#### 6.1.2 Tipos de Arquivo Permitidos
- **Imagens**: .jpg, .png
- **Documentos**: .doc, .docx
- **Planilhas**: .xls, .xlsx
- **PDFs**: .pdf

### 6.2 Processo de Upload

#### 6.2.1 Fluxo de Validação
1. **Verificação de Tamanho**
2. **Validação de Tipo**
3. **Geração de Nome Único**
4. **Upload para Diretório**
5. **Registro no Banco de Dados**
6. **Notificação no Chat**

#### 6.2.2 Geração de Nome de Arquivo
```php
$ext = explode(".", $_FILES['arquivo']['name']);
$ext = end($ext);
$nameFile = $_POST['token'] . "-" . strtotime(date('Y-m-d H:i:s')) . "." . $ext;
```

---

## 7. SISTEMA DE RELATÓRIOS E DASHBOARDS

### 7.1 Dashboard Administrativo

#### 7.1.1 Métricas Principais
**Arquivo**: `view/page/action/cnf/cnf-dash.php`

| Métrica | Descrição | Fonte |
|---------|-----------|-------|
| **Usuários Backoffice** | Total de atendentes ativos | `tbl_user` |
| **Usuários Solicitantes** | Total de solicitantes ativos | `tbl_user` |
| **Usuários Hoje** | Logins do dia atual | `tbl_log_diario` |
| **Filas Ativas** | Filas em funcionamento | `tbl_config_fila` |
| **Filas Inativas** | Filas desabilitadas | `tbl_config_fila` |

#### 7.1.2 Consultas SQL
```sql
-- Usuários Backoffice Ativos
SELECT COUNT(*) as total 
FROM tbl_user 
WHERE ativo = 1 AND nivel_id = 4

-- Usuários Solicitantes Ativos
SELECT COUNT(*) as total 
FROM tbl_user 
WHERE ativo = 1 AND nivel_id = 5

-- Usuários Logados Hoje
SELECT COUNT(DISTINCT user_id) as total 
FROM tbl_log_diario 
WHERE data_log = CURDATE()
```

### 7.2 Dashboard Operacional

#### 7.2.1 Indicadores de Fila
**Arquivo**: `view/page/action/idx/dash-idx.php`
- **Fila de Atendimentos**: Demandas pendentes
- **Status de Pausas**: Atendentes em pausa
- **Atendimentos em Andamento**: Chats ativos
- **Métricas de Performance**: TMA, TME, etc.

### 7.3 API de Relatórios

#### 7.3.1 Endpoint de Dados
**Arquivo**: `api/index.php`
- **Método**: GET
- **Parâmetros**:
  - `u`: Usuário da API
  - `dd`: Data início (YYYY-MM-DD)
  - `da`: Data fim (YYYY-MM-DD)
- **Retorno**: JSON com dados de atendimentos

#### 7.3.2 Exemplo de Uso
```
GET /api/index.php?u=usuario&dd=2024-01-01&da=2024-01-31
```

---

## 8. SISTEMA DE PERMISSÕES E MENUS

### 8.1 Estrutura de Menus

#### 8.1.1 Menu Administrativo
**Arquivo**: `view/content/menu/menu-cnf.php`

| Item | Ícone | Descrição | Nível |
|------|-------|-----------|-------|
| **Usuários** | fa-user | Gestão de usuários | 0,1 |
| **Regional** | fa-sitemap | Gestão regional | 0,1 |
| **Empresa** | fa-building | Gestão de empresas | 0,1 |
| **Agência** | fa-building | Gestão de agências | 0,1 |
| **Assuntos** | fa-tasks | Gestão de assuntos | 0,1 |
| **Prioridades** | fa-layer-group | Gestão de prioridades | 0,1 |
| **FAQ** | fa-question-circle | Perguntas frequentes | 0,1 |
| **Mensagem** | fa-comment-dots | Mensagens rápidas | 0,1 |
| **Log Acesso** | fa-clipboard-list | Logs de acesso | 0,1 |
| **Filas** | fa-tasks | Gestão de filas | 0,1 |
| **Contrato** | fa-file-alt | Gestão de contratos | 0,1 |

### 8.2 Controle de Acesso

#### 8.2.1 Verificação de Permissões
```php
// Verificação de nível de usuário
if($infoUser['nivel_id'] <= 1) {
    // Acesso administrativo
} elseif($infoUser['nivel_id'] == 4) {
    // Acesso de backoffice
} elseif($infoUser['nivel_id'] == 5) {
    // Acesso de solicitante
}
```

#### 8.2.2 Controle de Menu
```php
// Exibição condicional de itens de menu
<?php if($menu_cnf[0] == '1') { ?>
<span class="span-menu" id="cad-usu-cnf">
    <div class="pad"><i class="far fa-user"></i></div> Usuários
</span>
<?php } ?>
```

---

## 9. CONFIGURAÇÕES E PERSONALIZAÇÃO

### 9.1 Configurações do Sistema

#### 9.1.1 Tabela de Configuração
**Tabela**: `tbl_config_sis`

| Campo | Descrição | Tipo |
|-------|-----------|------|
| `titulo_sistema` | Nome do sistema | VARCHAR |
| `titulo_app` | Título da aplicação | VARCHAR |
| `sistema_img` | Logo do sistema | VARCHAR |
| `color` | Cor principal | VARCHAR |
| `contrato` | Contrato ativo | VARCHAR |
| `tempoDash` | Tempo de atualização | INT |

#### 9.1.2 Carregamento de Configurações
```php
$sql = "SELECT * FROM tbl_config_sis";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$config_sis = $stmt->fetch(PDO::FETCH_ASSOC);

$titulo = $config_sis['titulo_sistema'];
$color = $config_sis['color'];
$tmpDash = $config_sis['tempoDash'];
```

### 9.2 Configurações de Usuário

#### 9.2.1 Perfil do Usuário
**Tabela**: `tbl_user`

| Campo | Descrição | Tipo |
|-------|-----------|------|
| `id_user` | ID único | INT |
| `nome_usuario` | Login | VARCHAR |
| `senha_usuario` | Senha (hash) | VARCHAR |
| `nivel_id` | Nível de acesso | INT |
| `contrato_id` | Contrato associado | INT |
| `fila_id` | Fila de atendimento | INT |
| `multichat` | Chat múltiplo | BOOLEAN |
| `comunicacao` | Comunicação | BOOLEAN |
| `env_file` | Upload de arquivos | BOOLEAN |
| `env_img` | Upload de imagens | BOOLEAN |

---

## 10. SEGURANÇA E VALIDAÇÕES

### 10.1 Validação de Dados

#### 10.1.1 Sanitização de Entrada
```php
// Escape de caracteres especiais
$login = mysqli_real_escape_string($connection, $_POST['login']);
$senha = mysqli_real_escape_string($connection, $_POST['senha']);

// Validação de email
if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Email válido
}

// Validação de tamanho
if(strlen($senha) >= 8 && strlen($senha) <= 20) {
    // Tamanho adequado
}
```

#### 10.1.2 Validação de Arquivos
```php
// Verificação de tipo de arquivo
$allowed_types = ['jpg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'pdf'];
$file_extension = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));

if(in_array($file_extension, $allowed_types)) {
    // Tipo permitido
}

// Verificação de tamanho
$max_size = 5 * 1024 * 1024; // 5MB
if($_FILES['arquivo']['size'] <= $max_size) {
    // Tamanho adequado
}
```

### 10.2 Controle de Sessão

#### 10.2.1 Configurações de Sessão
```php
// Configuração de timeout
ini_set('session.gc_maxlifetime', 3600); // 1 hora

// Configuração de cookies
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS apenas
ini_set('session.use_only_cookies', 1);
```

#### 10.2.2 Verificação de Sessão
```php
// Verificação de sessão ativa
if(!isset($_SESSION['dados']['id_user']) || $_SESSION['dados']['id_user'] == '') {
    session_destroy();
    header('Location: ../out.php');
    exit;
}
```

---

## 11. APIS E INTEGRAÇÕES

### 11.1 API de Relatórios

#### 11.1.1 Endpoint Principal
**Arquivo**: `api/index.php`

```php
<?php
include("../view/cnf/conn.php");

// Validação de usuário
$sql = "SELECT nome_user FROM tbl_api_user WHERE nome_user = '" . $_GET['u'] . "'";
$stmt = $PDO->prepare($sql);
$result = $stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($user['nome_user'] != '') {
    if($_GET['dd'] != '' && $_GET['da'] != '') {
        // Consulta de dados
        $sql = "SELECT a.protocolo as Protocolo, 
                       a.data_hora as Hora_Registro,
                       (sec_to_time(time_to_sec(ta) + time_to_sec(te))) as TD,
                       d.nome_fila as Fila,
                       b.titulo_assunto as Assunto
                FROM tbl_chat_fila a, tbl_assunto b, tbl_config_fila d
                WHERE a.assunto_id = b.id_assunto
                AND a.fila_id = d.id_fila
                AND DATE_FORMAT(a.data_hora, '%Y-%m-%d') BETWEEN '" . $_GET['dd'] . "' AND '" . $_GET['da'] . "'";
        
        $stmt = $PDO->prepare($sql);
        $result = $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }
}
?>
```

#### 11.1.2 Parâmetros da API
| Parâmetro | Obrigatório | Descrição | Exemplo |
|-----------|-------------|-----------|---------|
| `u` | Sim | Usuário da API | admin |
| `dd` | Sim | Data início | 2024-01-01 |
| `da` | Sim | Data fim | 2024-01-31 |

### 11.2 WebSocket

#### 11.2.1 Configuração do Servidor
```javascript
// Configuração de conexão
var hostname = window.location.hostname;

if(hostname == 'localhost') {
    var prot = 'ws';
    var port = ':8080';
} else {
    var prot = 'wss';
    var port = '/chatlogos/';
}

var host = prot + '://' + hostname + port;
var conn = new WebSocket(host);
```

#### 11.2.2 Eventos WebSocket
```javascript
// Conexão estabelecida
conn.onopen = function(e) {
    console.log("Conectado ao servidor");
};

// Recebimento de mensagem
conn.onmessage = function(e) {
    var data = JSON.parse(e.data);
    // Processar mensagem
};

// Conexão fechada
conn.onclose = function(e) {
    console.log("Conexão fechada");
    // Tentar reconectar
};

// Erro de conexão
conn.onerror = function(err) {
    console.error("Erro na conexão:", err);
};
```

---

## 12. MANUTENÇÃO E MONITORAMENTO

### 12.1 Logs do Sistema

#### 12.1.1 Tipos de Log
| Tipo | Tabela | Descrição |
|------|--------|-----------|
| **Acesso Diário** | `tbl_log_diario` | Logins e acessos |
| **Atendimento** | `tbl_log_atendimento` | Ações de atendimento |
| **Mensagens** | `tbl_chat_msg` | Histórico de mensagens |
| **Arquivos** | `tbl_chat_files` | Upload de arquivos |

#### 12.1.2 Consulta de Logs
```sql
-- Log de acessos do dia
SELECT user_id, data_log, ip, date_up 
FROM tbl_log_diario 
WHERE data_log = CURDATE();

-- Log de atendimentos
SELECT user_id, acao, data_hora 
FROM tbl_log_atendimento 
WHERE DATE(data_hora) = CURDATE();
```

### 12.2 Rotinas de Manutenção

#### 12.2.1 Limpeza de Dados
**Arquivo**: `view/cnf/rotina.php`

```php
// Limpeza de logs antigos (30 dias)
$sql = "DELETE FROM tbl_log_diario 
        WHERE data_log < DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

// Limpeza de mensagens antigas (90 dias)
$sql = "DELETE FROM tbl_chat_msg 
        WHERE data_hora < DATE_SUB(NOW(), INTERVAL 90 DAY)";
```

#### 12.2.2 Otimização de Performance
```sql
-- Análise de tabelas
ANALYZE TABLE tbl_chat_fila;
ANALYZE TABLE tbl_chat_msg;
ANALYZE TABLE tbl_user;

-- Otimização de tabelas
OPTIMIZE TABLE tbl_chat_fila;
OPTIMIZE TABLE tbl_chat_msg;
```

---

## 13. TROUBLESHOOTING E SOLUÇÃO DE PROBLEMAS

### 13.1 Problemas Comuns

#### 13.1.1 Conexão WebSocket
**Problema**: Chat não conecta
**Soluções**:
1. Verificar se o servidor WebSocket está rodando
2. Verificar configuração de porta (8080)
3. Verificar firewall e proxy
4. Testar conexão em localhost

#### 13.1.2 Upload de Arquivos
**Problema**: Arquivos não são enviados
**Soluções**:
1. Verificar permissões do diretório `view/file/`
2. Verificar limite de upload no PHP (`upload_max_filesize`)
3. Verificar limite de POST (`post_max_size`)
4. Verificar espaço em disco

#### 13.1.3 Sessão Expirada
**Problema**: Usuário é deslogado frequentemente
**Soluções**:
1. Verificar configuração de `session.gc_maxlifetime`
2. Verificar configuração de `session.cookie_lifetime`
3. Verificar se o servidor está mantendo sessões

### 13.2 Logs de Debug

#### 13.2.1 Ativação de Debug
```php
// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log de erros
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

#### 13.2.2 Monitoramento de Performance
```php
// Medição de tempo de execução
$start_time = microtime(true);
// ... código ...
$end_time = microtime(true);
$execution_time = $end_time - $start_time;
error_log("Tempo de execução: " . $execution_time . " segundos");
```

---

## 14. BACKUP E RECUPERAÇÃO

### 14.1 Estratégia de Backup

#### 14.1.1 Backup do Banco de Dados
```bash
# Backup completo
mysqldump -u usuario -p web_chatlogos_piloto > backup_completo.sql

# Backup apenas dados
mysqldump -u usuario -p --no-create-info web_chatlogos_piloto > backup_dados.sql

# Backup apenas estrutura
mysqldump -u usuario -p --no-data web_chatlogos_piloto > backup_estrutura.sql
```

#### 14.1.2 Backup de Arquivos
```bash
# Backup dos arquivos enviados
tar -czf backup_arquivos.tar.gz view/file/ view/file_com/

# Backup das imagens
tar -czf backup_imagens.tar.gz view/img/ imagem/
```

### 14.2 Processo de Recuperação

#### 14.2.1 Restauração do Banco
```bash
# Restaurar backup completo
mysql -u usuario -p web_chatlogos_piloto < backup_completo.sql

# Restaurar apenas dados
mysql -u usuario -p web_chatlogos_piloto < backup_dados.sql
```

#### 14.2.2 Restauração de Arquivos
```bash
# Restaurar arquivos
tar -xzf backup_arquivos.tar.gz

# Restaurar imagens
tar -xzf backup_imagens.tar.gz
```

---

## 15. CONSIDERAÇÕES DE PERFORMANCE

### 15.1 Otimizações Implementadas

#### 15.1.1 Cache de Consultas
```php
// Cache de configurações do sistema
if(!isset($_SESSION['config_cache'])) {
    $sql = "SELECT * FROM tbl_config_sis";
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $_SESSION['config_cache'] = $stmt->fetch(PDO::FETCH_ASSOC);
}
```

#### 15.1.2 Compressão de Arquivos
```apache
# .htaccess - Compressão GZIP
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### 15.2 Recomendações de Performance

#### 15.2.1 Otimização de Consultas SQL
```sql
-- Usar índices apropriados
CREATE INDEX idx_chat_fila_status ON tbl_chat_fila(status_fila);
CREATE INDEX idx_chat_fila_data ON tbl_chat_fila(data_hora);
CREATE INDEX idx_user_nivel ON tbl_user(nivel_id);

-- Usar LIMIT em consultas grandes
SELECT * FROM tbl_chat_msg 
WHERE chat_id = ? 
ORDER BY data_hora DESC 
LIMIT 50;
```

#### 15.2.2 Configurações do Servidor
```apache
# Apache - Configurações de performance
KeepAlive On
MaxKeepAliveRequests 100
KeepAliveTimeout 5

# PHP - Configurações de memória
memory_limit = 256M
max_execution_time = 30
max_input_time = 60
```

---

## 16. DOCUMENTAÇÃO DE DESENVOLVIMENTO

### 16.1 Padrões de Código

#### 16.1.1 Nomenclatura
- **Arquivos PHP**: `snake_case.php`
- **Funções**: `camelCase()`
- **Variáveis**: `$snake_case`
- **Constantes**: `UPPER_CASE`
- **Classes**: `PascalCase`

#### 16.1.2 Estrutura de Arquivos
```
view/
├── cnf/           # Configurações
├── chat/          # Módulo de chat
├── content/       # Conteúdo das páginas
├── css/           # Estilos
├── file/          # Arquivos enviados
├── img/           # Imagens
├── js/            # Scripts JavaScript
├── page/          # Páginas do sistema
└── staff/         # Processamento backend
```

### 16.2 Convenções de Desenvolvimento

#### 16.2.1 Comentários
```php
/**
 * Função para validar senha do usuário
 * @param string $senha Senha a ser validada
 * @return int Porcentagem de força da senha (0-100)
 */
function senhaValida($senha) {
    // Código da função
}
```

#### 16.2.2 Tratamento de Erros
```php
try {
    $stmt = $PDO->prepare($sql);
    $result = $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na consulta: " . $e->getMessage());
    return false;
}
```

---

## 17. INFORMAÇÕES DE CONTATO E SUPORTE

### 17.1 Acesso ao Sistema

#### 17.1.1 URLs de Acesso
| Ambiente | URL | Descrição |
|----------|-----|-----------|
| **Desenvolvimento** | https://localhost/solvetask/piloto/ | Ambiente local |
| **Produção** | https://chat.logos-ma.com.br/ | Ambiente de produção |

#### 17.1.2 Credenciais de Teste
| Usuário | Senha | Nível | Descrição |
|---------|-------|-------|-----------|
| **admin** | logos@1 | 0 | Administrador Master |
| **01** | 01@logos | 4 | Backoffice |
| **02** | 02@logos | 5 | Solicitante |
| **03** | 03@logos | 4 | Backoffice |
| **04** | 04@logos | 5 | Solicitante |

### 17.2 Informações Técnicas

#### 17.2.1 Especificações do Sistema
- **Versão**: Piloto
- **Linguagem**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Servidor Web**: Apache 2.4+
- **WebSocket**: Porta 8080 (dev), 443 (prod)

#### 17.2.2 Estrutura de Dados
- **Tabelas Principais**: 15+ tabelas
- **Views**: 10+ views para relatórios
- **Procedures**: Rotinas de manutenção
- **Triggers**: Controle de auditoria

---

## 18. ANEXOS

### 18.1 Scripts de Instalação

#### 18.1.1 Criação de Tabelas
```sql
-- Tabela de usuários
CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nome_usuario` varchar(50) NOT NULL,
  `senha_usuario` varchar(255) NOT NULL,
  `nivel_id` int(11) NOT NULL,
  `contrato_id` int(11) NOT NULL,
  `fila_id` int(11) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id_user`),
  KEY `idx_nivel` (`nivel_id`),
  KEY `idx_contrato` (`contrato_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

#### 18.1.2 Inserção de Dados Iniciais
```sql
-- Inserir usuário administrador
INSERT INTO `tbl_user` (`nome_usuario`, `senha_usuario`, `nivel_id`, `contrato_id`, `ativo`) 
VALUES ('admin', '7d04bab8a6dae9ae0032067347d319d0e0655a0c', 0, 1, 1);

-- Inserir configurações do sistema
INSERT INTO `tbl_config_sis` (`titulo_sistema`, `titulo_app`, `color`, `contrato`) 
VALUES ('Solvetask', 'Sistema de Atendimento', '#3498db', '1');
```

### 18.2 Configurações de Servidor

#### 18.2.1 Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Segurança
<Files "*.php">
    Order Allow,Deny
    Allow from all
</Files>

# Cache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
</IfModule>
```

#### 18.2.2 PHP (php.ini)
```ini
; Configurações de upload
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

; Configurações de sessão
session.gc_maxlifetime = 3600
session.cookie_lifetime = 3600
session.cookie_httponly = 1
session.cookie_secure = 1

; Configurações de memória
memory_limit = 256M
max_execution_time = 30
max_input_time = 60
```

---

**Fim da Documentação Técnica - Sistema Solvetask**

*Documento gerado em: $(date)*
*Versão: 1.0*
*Formato: Microsoft Word*
