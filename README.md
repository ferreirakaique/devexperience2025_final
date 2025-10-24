# Youtan – Sistema de Ativos (2025)

Sistema web em **PHP + MySQL** com:
- Cadastro/gestão de **Ativos**, **Colaboradores** (usuários) e **Manutenções**
- **Dashboard** com gráficos (Chart.js)
- **Previsão/Insights (IA)** no dashboard (mock)  
- **Chatbot (IA)** usando OpenAI (via API)
- **Alertas & Notificações** (manutenções programadas, licenças, garantias, devoluções, eventos de ativos: criar/editar/excluir)

## Sumário
- [Requisitos](#requisitos)
- [Estrutura de Pastas](#estrutura-de-pastas)
- [Instalação (Windows/XAMPP)](#instalação-windowsxampp)
- [Banco de Dados](#banco-de-dados)
- [Configurar Chave da OpenAI](#configurar-chave-da-openai)
- [Executar o Projeto](#executar-o-projeto)
- [Módulos Principais](#módulos-principais)
  - [Ativos](#ativos)
  - [Colaboradores (Usuários)](#colaboradores-usuários)
  - [Manutenções](#manutenções)
  - [Dashboard](#dashboard)
  - [Chatbot (IA)](#chatbot-ia)
  - [Alertas & Notificações](#alertas--notificações)
- [Agendamento (Windows)](#agendamento-windows)
- [Boas Práticas com Segredos](#boas-práticas-com-segredos)
- [Problemas Comuns (FAQ)](#problemas-comuns-faq)

---

## Requisitos

- **PHP 8.x** (XAMPP ou WAMP)
- **MySQL/MariaDB**
- **Composer** (para instalar PHPMailer)
- **Git** (opcional, mas recomendado)
- Navegador moderno (Chrome/Edge/Firefox)

---

## Estrutura de Pastas

```
/seu-projeto
  /css
    chatbot.css
    dashboard.css
    colaboradores.css
    editar_ativos.css
    ... (demais CSS)
  /img
    youtan.png
  /php
    ativos.php
    cadastrar_ativos.php
    editar_ativos.php
    excluir_ativos.php
    colaboradores.php
    cadastrar_usuario.php
    editar_usuarios.php
    manutencoes.php
    cadastrar_manutencoes.php
    notificacoes_ativos.php
    verificar_alertas.php
    resolver_alerta.php
    dashboard.php
    chatbot.php
    chatbot_api.php
    nav.php
    conexao.php
    /config
      openai.php
      secrets.php             (NÃO versionar)
      secrets.example.php     (modelo com placeholders)
      notificacao_helper.php
      email_helper.php        (PHPMailer)
  /vendor
    ... (composer / phpmailer)
index.php
README.md
```

---

## Instalação (Windows/XAMPP)

1. **Instale o XAMPP**  
   - Baixe e instale (Apache + MySQL).
   - Inicie **Apache** e **MySQL** pelo XAMPP Control Panel.

2. **Clone ou copie o projeto** para a pasta do servidor:
   - Ex.: `C:\xampp\htdocs\youtan`  
   - O projeto ficará acessível em `http://localhost/youtan/index.php`.

3. **Composer (PHPMailer)**  
   No terminal (PowerShell/CMD) na pasta do projeto:
   ```bash
   composer require phpmailer/phpmailer
   ```

4. **Habilite cURL no PHP** (se ainda não estiver):
   - Abra `C:\xampp\php\php.ini`
   - Garanta que **não** está comentado:
     ```
     extension=curl
     ```
   - (Se precisar de certificados SSL):  
     Baixe `cacert.pem` e configure no `php.ini`:
     ```
     curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"
     openssl.cafile = "C:\xampp\php\extras\ssl\cacert.pem"
     ```
   - Reinicie **Apache**.

---

## Banco de Dados

1. **Crie o banco** (ex.: `youtan_db`) no **phpMyAdmin**.
2. **Crie as tabelas base** (adeque se já existirem). Exemplos:

```sql
CREATE TABLE usuarios (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nome_usuario VARCHAR(120) NOT NULL,
  email_usuario VARCHAR(160) NOT NULL UNIQUE,
  senha_usuario VARCHAR(255) NOT NULL,
  tipo_usuario ENUM('Admin','Colaborador') NOT NULL DEFAULT 'Colaborador',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ativos (
  id_ativo INT AUTO_INCREMENT PRIMARY KEY,
  nome_ativo VARCHAR(160) NOT NULL,
  categoria_ativo VARCHAR(80) NOT NULL,
  valor_ativo DECIMAL(12,2) NOT NULL DEFAULT 0,
  data_aquisicao_ativo DATE NOT NULL,
  numero_serie_ativo VARCHAR(120),
  status_ativo ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
  localizacao_ativo VARCHAR(160),
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE manutencoes (
  id_manutencao INT AUTO_INCREMENT PRIMARY KEY,
  id_ativo INT NOT NULL,
  tipo_manutencao ENUM('Preventiva','Corretiva') NOT NULL,
  data_manutencao DATE NOT NULL,
  responsavel_manutencao INT NOT NULL,       -- FK usuários
  custo_manutencao DECIMAL(12,2) NOT NULL DEFAULT 0,
  descricao_manutencao TEXT,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_ativo) REFERENCES ativos(id_ativo) ON DELETE CASCADE,
  FOREIGN KEY (responsavel_manutencao) REFERENCES usuarios(id_usuario)
);

-- Notificações simples (por eventos de ativo)
CREATE TABLE notificacao (
  id_notificacao INT AUTO_INCREMENT PRIMARY KEY,
  fk_id_ativo INT NULL,
  fk_id_usuario INT NOT NULL,
  descricao_notificacao VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (fk_id_ativo)   REFERENCES ativos(id_ativo)    ON DELETE SET NULL,
  FOREIGN KEY (fk_id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- (Opcional) tabelas para alertas automáticos (licenças/garantias/devoluções/manut. programadas)
CREATE TABLE licencas (
  id_licenca INT AUTO_INCREMENT PRIMARY KEY,
  id_ativo INT NOT NULL,
  nome_licenca VARCHAR(120) NOT NULL,
  data_validade DATE NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_ativo) REFERENCES ativos(id_ativo) ON DELETE CASCADE
);

CREATE TABLE garantias (
  id_garantia INT AUTO_INCREMENT PRIMARY KEY,
  id_ativo INT NOT NULL,
  fornecedor VARCHAR(120),
  data_fim DATE NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_ativo) REFERENCES ativos(id_ativo) ON DELETE CASCADE
);

CREATE TABLE devolucoes (
  id_devolucao INT AUTO_INCREMENT PRIMARY KEY,
  id_ativo INT NOT NULL,
  responsavel VARCHAR(120),
  data_prevista DATE NOT NULL,
  status ENUM('pendente','devolvido','atrasado') DEFAULT 'pendente',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_ativo) REFERENCES ativos(id_ativo) ON DELETE CASCADE
);

CREATE TABLE manutencoes_programadas (
  id_mp INT AUTO_INCREMENT PRIMARY KEY,
  id_ativo INT NOT NULL,
  tipo ENUM('Preventiva','Corretiva') DEFAULT 'Preventiva',
  proxima_data DATE NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_ativo) REFERENCES ativos(id_ativo) ON DELETE CASCADE
);
```

3. **Configure a conexão** em `php/conexao.php`:
```php
<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'youtan_db';

$conexao = new mysqli($host, $user, $pass, $db);
if ($conexao->connect_errno) {
  die('Erro ao conectar: ' . $conexao->connect_error);
}
$conexao->set_charset('utf8mb4');
```

---

## Configurar Chave da OpenAI

1. **NÃO** comite sua chave.  
2. Crie `php/config/secrets.php` (não versionar; deve estar no `.gitignore`):

```php
<?php
define('OPENAI_API_KEY', 'sk-COLOQUE-SUA-CHAVE-AQUI');
define('OPENAI_MODEL',  'gpt-4.1-mini');
```

3. `php/config/openai.php` já usa esse arquivo.  
4. Opcional: distribua `secrets.example.php` com placeholders.

> Se o GitHub bloquear push por segredo no histórico, veja [Boas Práticas com Segredos](#boas-práticas-com-segredos).

---

## Executar o Projeto

- Inicie **Apache** e **MySQL** no XAMPP.  
- Acesse: `http://localhost/youtan/index.php`  
- Faça login (crie um usuário Admin diretamente no banco se necessário).

---

## Módulos Principais

### Ativos
- **Listar**: `/php/ativos.php`  
- **Cadastrar**: `/php/cadastrar_ativos.php`  
- **Editar**: `/php/editar_ativos.php?id=...`  
- **Excluir**: `/php/excluir_ativos.php?id=...`  
- Ao **cadastrar/editar/excluir**, é registrada uma **notificação** na tabela `notificacao`.

### Colaboradores (Usuários)
- **Listar**: `/php/colaboradores.php`  
- **Cadastrar**: `/php/cadastrar_usuario.php`  
- **Editar**: `/php/editar_usuarios.php?id=...`  
- Ao tentar editar **perfil de outro usuário** via atalhos, mostra alerta orientando a ir ao **próprio perfil**.

### Manutenções
- **Registrar**: `/php/cadastrar_manutencoes.php`  
- **Listar (cards)**: `/php/manutencoes.php`

### Dashboard
- **URL**: `/php/dashboard.php`  
- Gráficos com Chart.js:  
  - **Ativos por Categoria** (solo)  
  - **Ativos por Status** e **Manutenções por Tipo** (lado a lado)  
- Labels/títulos brancos; exportar **PDF** (print) e **Excel** (stub).

### Chatbot (IA)
- **Frontend**: `/php/chatbot.php`  
- **API**: `/php/chatbot_api.php` (usa `config/openai.php`)  
- Chips de sugestão, indicador “digitando…”, botões de ação contextuais.

### Alertas & Notificações
- **Eventos de Ativo** (cadastrar/editar/excluir) → grava `notificacao`  
  - Tela: `/php/notificacoes_ativos.php` (cards, filtro, link pro ativo)
- **Alertas automáticos** (prazos e datas críticas) – opcional:  
  - Job diário: `/php/verificar_alertas.php` (gera alertas + e-mail)  
  - Endpoint: `/php/resolver_alerta.php` (marca lido/resolvido)

---

## Agendamento (Windows)

Para rodar checagens diárias (alertas automáticos):

1. Abra **Agendador de Tarefas** (Task Scheduler).  
2. Crie uma tarefa → **Diariamente** (ex.: 09:00).  
3. Ação:  
   - Programa/script: `C:\xampp\php\php.exe`  
   - Argumentos: `C:\xampp\htdocs\youtan\php\verificar_alertas.php`  
4. Salve.  

> Alternativa: acesse manualmente `http://localhost/youtan/php/verificar_alertas.php`.

---

## Boas Práticas com Segredos

- Coloque `php/config/secrets.php` no **.gitignore**:
  ```
  /php/config/secrets.php
  ```
- Disponibilize `secrets.example.php` (placeholders).  
- Se um segredo foi comitado:
  1. **Revogue** a chave no provedor e gere outra.  
  2. Limpe o histórico (escolha uma):  
     ```bash
     pip install --user git-filter-repo
     git filter-repo --path php/config/secrets.php --invert-paths
     git push origin --force main
     ```
  3. Se a `main` for protegida, crie uma branch (ex.: `cleaned-main`) e abra **PR**.

---

## Problemas Comuns (FAQ)

**1) Chatbot não responde**  
- Verifique o **caminho** do `fetch` (ex.: `./chatbot_api.php`).  
- Habilite `extension=curl` no `php.ini`.  
- Configure `curl.cainfo`/`openssl.cafile` no Windows.  
- Confirme `php/config/secrets.php` com **chave válida**.  
- Veja **DevTools → Network** → status e resposta JSON.

**2) Erro SSL (`unable to get local issuer certificate`)**  
- Configure `cacert.pem` no `php.ini`.

**3) GitHub bloqueou push por segredo**  
- Siga [Boas Práticas com Segredos](#boas-práticas-com-segredos).

**4) Gráficos não aparecem**  
- Confirme `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`.  
- Valide as queries no banco.

**5) E-mails não chegam**  
- Ajuste SMTP em `php/config/email_helper.php` (host, porta, usuário, senha).  
- Teste porta **587** (STARTTLS) ou **465** (SMTPS).

---

## Licença
Uso acadêmico/educacional. Ajuste conforme necessário.
