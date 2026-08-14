# DOCUMENTAÇÃO TÉCNICA - SISTEMA SOLVETASK

## 1. VISÃO GERAL DO SISTEMA

### 1.1 Descrição
O Solvetask é um sistema de atendimento ao cliente baseado em chat em tempo real, desenvolvido em PHP com interface web responsiva. O sistema permite comunicação entre solicitantes e equipes de backoffice através de uma plataforma de chat integrada com sistema de filas e gestão de atendimentos.

### 1.2 Tecnologias Utilizadas
- **Backend**: PHP 7.4+
- **Banco de Dados**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **Comunicação em Tempo Real**: WebSocket
- **Editor de Texto**: TinyMCE
- **Gráficos**: AmCharts
- **Framework CSS**: Bootstrap 5
- **Ícones**: Font Awesome

### 1.3 Arquitetura do Sistema
```
├── access/                 # Configurações de acesso e conexão
├── api/                   # APIs REST do sistema
├── cache/                 # Cache e otimizações
├── css/                   # Estilos CSS
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

## 2. CONFIGURAÇÃO E INSTALAÇÃO

### 2.1 Requisitos do Sistema
- **Servidor Web**: Apache 2.4+ ou Nginx
- **PHP**: 7.4 ou superior
- **MySQL**: 5.7 ou superior
- **Extensões PHP**: PDO, PDO_MySQL, JSON, GD, mbstring
- **WebSocket Server**: Para comunicação em tempo real

### 2.2 Configuração do Banco de Dados
```sql
-- Configuração principal
Host: localhost
Porta: 3306
Database: web_chatlogos_piloto
Usuario: acesso.sistemas
Senha: <SENHA_DB_LOCAL>
Charset: utf8
```

### 2.3 Configuração de Conexão
**Arquivo**: `view/cnf/conexao.php`
```php
$host = 'localhost';
$usuario = 'acesso.sistemas';
$senha = '<SENHA_DB_LOCAL>';
$banco = 'web_chatlogos_piloto';
$dsn = "mysql:host={$host};port=3306;dbname={$banco};charset=utf8";
```

### 2.4 Configuração do WebSocket
**Arquivo**: `view/chat/assets/js/script.js`
```javascript
// Configuração para localhost
var host = 'ws://localhost:8080';

// Configuração para produção
var host = 'wss://chat.logos-ma.com.br/chatlogos/';
```

## 3. SISTEMA DE AUTENTICAÇÃO E SEGURANÇA

### 3.1 Fluxo de Autenticação
1. **Login Inicial**: `index.php`
   - Captura credenciais do usuário
   - Codifica dados em base64
   - Redireciona para `login.php`

2. **Validação**: `login.php`
   - Decodifica dados de login
   - Valida credenciais no banco de dados
   - Gera hash SHA1 da senha
   - Inicia sessão PHP

3. **Controle de Sessão**: `view/cnf/session.php`
   - Verifica sessão ativa
   - Carrega dados do usuário
   - Define permissões e níveis de acesso

### 3.2 Níveis de Usuário
- **Nível 0**: Master/Administrador
- **Nível 1**: Administrador de Contrato
- **Nível 2**: Acompanhamento
- **Nível 4**: Backoffice/Atendente
- **Nível 5**: Solicitante

### 3.3 Sistema de Permissões
**Arquivo**: `view/cnf/session.php`
```php
$sql = "SELECT mosaico, menu_idx, menu_cnf, cad_cnf from tbl_nivel where id_nivel=".$infoUser['nivel_id'];
$mosaico = explode(",", $infoNivel['mosaico']);
$menu_idx = explode(",", $infoNivel['menu_idx']);
$menu_cnf = explode(",", $infoNivel['menu_cnf']);
```

## 4. SISTEMA DE CHAT E COMUNICAÇÃO

### 4.1 Tipos de Chat
1. **Chat Individual**: `view/chat/chat_ind.php`
   - Comunicação 1:1 entre solicitante e backoffice
   - Sistema de filas de atendimento
   - Transferência entre filas

2. **Chat em Grupo**: `view/chat/chat_com.php`
   - Comunicação em grupo
   - Múltiplos participantes
   - Gestão de participantes

3. **Chat Geral**: `view/chat/chat_geral.php`
   - Chat público
   - Comunicação em tempo real via WebSocket

### 4.2 Funcionalidades do Chat
- **Mensagens em Tempo Real**: WebSocket
- **Upload de Arquivos**: Até 10MB para grupos, 5MB para individuais
- **Indicador de Digitação**: "Usuário está digitando..."
- **Mensagens Rápidas**: Templates pré-definidos
- **Transferência de Atendimento**: Entre diferentes filas
- **Sistema de Notificações**: Sonoras e visuais

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

## 5. SISTEMA DE FILAS E ATENDIMENTO

### 5.1 Gestão de Filas
**Tabela Principal**: `tbl_config_fila`
- Configuração de filas de atendimento
- Status ativo/inativo
- Associação com contratos

### 5.2 Estados do Atendimento
1. **Aguardando**: Status 1
2. **Em Atendimento**: Status 2
3. **Pausado**: Status 3
4. **Concluído**: Status 4
5. **Cancelado**: Status 5

### 5.3 Métricas de Atendimento
- **TMA (Tempo Médio de Atendimento)**: Tempo total do atendimento
- **TME (Tempo Médio de Espera)**: Tempo na fila
- **TA (Tempo de Atendimento)**: Tempo efetivo de atendimento
- **TE (Tempo de Espera)**: Tempo de espera na fila

## 6. SISTEMA DE ARQUIVOS E UPLOAD

### 6.1 Configuração de Upload
- **Tamanho Máximo**: 5MB (chat individual), 10MB (chat grupo)
- **Tipos Permitidos**: .jpg, .png, .doc, .docx, .xls, .xlsx, .pdf
- **Diretório de Armazenamento**: `view/file/` e `view/file_com/`

### 6.2 Processo de Upload
1. **Validação**: Tamanho e tipo de arquivo
2. **Geração de Nome**: Token + timestamp + extensão
3. **Armazenamento**: Upload para diretório específico
4. **Registro**: Inserção na tabela de arquivos
5. **Notificação**: Envio de mensagem no chat

## 7. SISTEMA DE RELATÓRIOS E DASHBOARDS

### 7.1 Dashboard Administrativo
**Arquivo**: `view/page/action/cnf/cnf-dash.php`
- Total de usuários por nível
- Usuários ativos hoje
- Filas ativas/inativas
- Métricas gerais do sistema

### 7.2 Dashboard Operacional
**Arquivo**: `view/page/action/idx/dash-idx.php`
- Fila de atendimentos
- Status de pausas
- Atendimentos em andamento
- Métricas de performance

### 7.3 Relatórios via API
**Endpoint**: `api/index.php`
```php
// Parâmetros: u=usuario, dd=data_inicio, da=data_fim
// Retorna: JSON com dados de atendimentos
```

## 8. SISTEMA DE PERMISSÕES E MENUS

### 8.1 Estrutura de Menus
**Arquivo**: `view/content/menu/menu-cnf.php`
- Menu baseado em níveis de usuário
- Controle granular de acesso
- Interface responsiva

### 8.2 Permissões por Nível
- **Master**: Acesso total ao sistema
- **Admin Contrato**: Gestão de contratos específicos
- **Acompanhamento**: Visualização de relatórios
- **Backoffice**: Atendimento e gestão de filas
- **Solicitante**: Acesso apenas ao chat

## 9. CONFIGURAÇÕES E PERSONALIZAÇÃO

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

## 10. SEGURANÇA E VALIDAÇÕES

### 10.1 Validação de Senhas
**Arquivo**: `view/cnf/func.php`
```php
function senhaValida($senha) {
    // Validação de complexidade
    // Caracteres especiais, maiúsculas, minúsculas, números
    // Tamanho mínimo de 8 caracteres
}
```

### 10.2 Sanitização de Dados
- Escape de caracteres especiais
- Validação de tipos de arquivo
- Limitação de tamanho de upload
- Filtros de conteúdo HTML

### 10.3 Controle de Sessão
- Timeout automático
- Verificação de sessão ativa
- Logout automático por inatividade
- Controle de IP

## 11. APIS E INTEGRAÇÕES

### 11.1 API de Relatórios
**Endpoint**: `/api/index.php`
- **Método**: GET
- **Parâmetros**: 
  - `u`: Usuário da API
  - `dd`: Data início (YYYY-MM-DD)
  - `da`: Data fim (YYYY-MM-DD)
- **Retorno**: JSON com dados de atendimentos

### 11.2 WebSocket
- **Protocolo**: WS/WSS
- **Porta**: 8080 (desenvolvimento)
- **Funcionalidades**: Chat em tempo real, notificações

## 12. MANUTENÇÃO E MONITORAMENTO

### 12.1 Logs do Sistema
- **Log de Acesso**: `tbl_log_diario`
- **Log de Atendimento**: `tbl_log_atendimento`
- **Log de Mensagens**: Tabelas de chat

### 12.2 Rotinas de Manutenção
**Arquivo**: `view/cnf/rotina.php`
- Limpeza de dados antigos
- Atualização de estatísticas
- Verificação de integridade

### 12.3 Monitoramento de Performance
- Controle de tempo de resposta
- Monitoramento de conexões WebSocket
- Análise de uso de recursos

## 13. TROUBLESHOOTING E SOLUÇÃO DE PROBLEMAS

### 13.1 Problemas Comuns
1. **Conexão WebSocket**: Verificar porta e configuração
2. **Upload de Arquivos**: Verificar permissões de diretório
3. **Sessão Expirada**: Verificar configuração de timeout
4. **Erro de Banco**: Verificar credenciais e conexão

### 13.2 Logs de Debug
- Ativar `error_reporting` em desenvolvimento
- Verificar logs do servidor web
- Monitorar logs do banco de dados

## 14. BACKUP E RECUPERAÇÃO

### 14.1 Backup do Banco de Dados
```bash
mysqldump -u usuario -p web_chatlogos_piloto > backup.sql
```

### 14.2 Backup de Arquivos
- Diretório `view/file/`
- Diretório `view/file_com/`
- Configurações do sistema

## 15. ATUALIZAÇÕES E VERSIONAMENTO

### 15.1 Controle de Versão
- Sistema de versionamento interno
- Log de alterações
- Testes antes de produção

### 15.2 Deploy
1. Backup do sistema atual
2. Upload dos novos arquivos
3. Execução de scripts de atualização
4. Testes de funcionalidade
5. Ativação em produção

## 16. CONSIDERAÇÕES DE PERFORMANCE

### 16.1 Otimizações Implementadas
- Cache de consultas frequentes
- Compressão de arquivos
- Minificação de CSS/JS
- Otimização de imagens

### 16.2 Recomendações
- Monitoramento de uso de memória
- Otimização de consultas SQL
- Implementação de CDN
- Balanceamento de carga

## 17. DOCUMENTAÇÃO DE DESENVOLVIMENTO

### 17.1 Padrões de Código
- Nomenclatura em português
- Comentários em português
- Estrutura modular
- Separação de responsabilidades

### 17.2 Convenções
- Arquivos PHP com extensão `.php`
- JavaScript com extensão `.js`
- CSS com extensão `.css`
- Imagens em diretórios específicos

## 18. CONTATOS E SUPORTE

### 18.1 Desenvolvedor
- **Sistema**: Solvetask
- **Versão**: Piloto
- **Ambiente**: Desenvolvimento/Produção

### 18.2 Acesso ao Sistema
- **Desenvolvimento**: https://localhost/solvetask/piloto/
- **Produção**: https://chat.logos-ma.com.br/

---

**Data de Criação**: $(date)
**Versão da Documentação**: 1.0
**Última Atualização**: $(date)

