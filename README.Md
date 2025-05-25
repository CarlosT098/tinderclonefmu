# Projeto de Autenticação com MVC em PHP

Este é um projeto FULLSTACK em PHP com estrutura MVC (Model-View-Controller), buscando emular um clone de tinder. Foi realizado como um projeto da minha faculdade no 5º semestre. Ele consiste de um front end basico e um back end fazendo a função de uma API restful. 

---

## 📁 Estrutura de Pastas

```
├── frontend/             # Arquivos estáticos do frontend (ex: React, Vue ou HTML puro)
│   ├── components/       # Componentes reutilizáveis da interface
│   ├── pages/            # Páginas específicas (login, home, etc)
│   └── scripts/          # Scripts JS de comportamento
│
├── public/               # Pasta pública acessível pelo navegador
│   ├── assets/           # Arquivos públicos como imagens, JS, CSS
│   └── index.php         # Ponto de entrada da aplicação
│
├── src/                  # Código-fonte principal da aplicação
│   ├── Config/           # Arquivos de configuração
│   ├── Controllers/      # Controladores das rotas
│   │   └── HomeController.php
│   ├── Core/             # Núcleo da aplicação (autoload, roteamento, etc)
│   │   └── App.php
│   └── Views/            # Arquivos HTML/PHP das views
│       └── home.php
│
├── storage/              # Pasta para arquivos gerados, cache ou logs
│
├── vendor/               # Bibliotecas e dependências do Composer
│
├── .gitignore            # Arquivos ignorados pelo Git
├── .htaccess             # Regras de reescrita para o Apache
├── db-credentials.env    # Arquivo com credenciais do banco de dados
├── LICENSE               # Licença do projeto
└── README.md             # Este arquivo :)
```

---

## 🛠️ Requisitos

- PHP 7.4+
- Composer
- Servidor Web (Apache ou servidor embutido)
- MySQL ou MariaDB
- Extensões PHP: `mbstring`, `openssl`, `pdo_mysql`

---

## 🚀 Como rodar o projeto

### 💻 No Linux

```bash
git clone https://github.com/Dehrangerz9/tinderclonefmu.git
cd tinderclonefmu

composer install

cp db-credentials.env.example db-credentials.env
# Edite db-credentials.env com seus dados

php -S localhost:8000 -t public
```

### 🪟 No Windows

```powershell
git clone https://github.com/Dehrangerz9/tinderclonefmu.git
cd tinderclonefmu

composer install

copy db-credentials.env.example db-credentials.env
# Edite db-credentials.env com seus dados

php -S localhost:8000 -t public
```
---

## 📂 Configurar o banco de dados

As configurações de acesso ao banco de dados devem ser definidas no arquivo `.env` com o nome `db-credentials.env`, localizado na raiz do projeto. Exemplo de configuração:

```dotenv
PGHOST=localhost
PGDATABASE=meubanco
PGUSER=postgres
PGPASSWORD=senha123
```

> 🔒 Certifique-se de que o arquivo `.env` **não está versionado** (já está no `.gitignore` por padrão).

---

### 🛠️ Criando o banco de dados

Você pode criar o banco de dados manualmente no seu PostgreSQL com os comandos abaixo.

#### 1. Acesse o terminal do PostgreSQL:
No Linux:
```bash
sudo -u postgres psql
```

No Windows (via PowerShell ou terminal do pgAdmin):
```powershell
psql -U postgres
```

#### 2. Crie o banco de dados:
```sql
CREATE DATABASE meubanco;
```

#### 3. Conecte-se ao banco:
```sql
\c meubanco
```

#### 4. Execute os comandos abaixo para criar as tabelas e tipos necessários:

```sql
-- Tipos ENUM personalizados
CREATE TYPE genero_enum AS ENUM ('masculino', 'feminino', 'não-binario', 'outro');
CREATE TYPE genero_interesse_enum AS ENUM ('masculino', 'feminino', 'todos');
CREATE TYPE orientacao_enum AS ENUM ('hetero', 'gay', 'lesbica', 'bi', 'pan', 'outro');

-- Tabela de usuários
CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    genero genero_enum NOT NULL,
    bio TEXT,
    nascimento DATE NOT NULL,
    idade INT,                          
    gostos TEXT[],                     
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    genero_interesse genero_interesse_enum,
    orientacao orientacao_enum NOT NULL,
    online BOOLEAN DEFAULT FALSE,       
    ativo BOOLEAN DEFAULT FALSE,        
    token VARCHAR(255),                  
    data_ultimo_login TIMESTAMP
);


-- Tabela de fotos de perfil
CREATE TABLE fotos (
    id SERIAL PRIMARY KEY,
    usuario_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    caminho VARCHAR(255) NOT NULL,
    posicao INT,
    is_main_photo BOOLEAN DEFAULT FALSE
);

-- Tabela de curtidas
CREATE TABLE curtidas (
    id SERIAL PRIMARY KEY,
    quem_curtiu INT REFERENCES usuarios(id) ON DELETE CASCADE,
    quem_foi_curtido INT REFERENCES usuarios(id) ON DELETE CASCADE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de matches
CREATE TABLE matches (
    id SERIAL PRIMARY KEY,
    usuario1_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    usuario2_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de mensagens
CREATE TABLE mensagens (
    id SERIAL PRIMARY KEY,
    match_id INT REFERENCES matches(id) ON DELETE CASCADE,
    remetente_id INT REFERENCES usuarios(id) ON DELETE CASCADE,
    texto TEXT NOT NULL,
    enviada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de recuperação de senha
CREATE TABLE password_resets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES usuarios(id) ON DELETE CASCADE,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Usuário de exemplo
INSERT INTO usuarios (nome, email, senha, genero, genero_interesse, orientacao)
VALUES (
    'Enzo Boomer',
    'enzo.boomer@example.com',
    '$2b$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36HhB5Z/mrulYfDFpZfBd1C', -- bcrypt da senha "123456"
    'masculino',
    'feminino',
    'hetero'
);
```

---


### 🔐 Rotas da API

| Método    | Rota                | Descrição                                                   |
|-----------|---------------------|------------------------------------------------------------ |
| **POST**  | `/register`         | Registra um novo usuário                                    |
| **POST**  | `/login`            | Realiza login com email e senha                             |
| **POST**  | `/recover-password` | Envia link de recuperação de senha para o e-mail informado  |
| **POST**  | `/reset-password`   | Redefine a senha do usuário usando token de recuperação     |
| **POST**  | `/like`             | Dá like em outro usuário                                    |
| **GET**   | `/user-matches`     | Lista todos os matches do usuário logado                    |
| **GET**   | `/to-like-list`     | Retorna uma lista de usuários disponíveis para dar like     |
| **GET**   | `/set-session`      | Rota de teste: cria sessão com usuário ID 1                 |

> ⚠️ A rota `/set-session` serve apenas para testes e desenvolvimento. Não use em produção!
> As rotas são gerenciadas através do `index.php` na pasta `public` e do roteador presente no `App.php`.

---

Se quiser, posso gerar o `README.md` final como arquivo para você baixar ou colar direto no repositório!

---

## ✅ Exemplo de Registro (`POST /register`)

```json
{
  "nome": "Maria",
  "email": "maria@exemplo.com",
  "senha": "senhaSegura123",
  "genero": "feminino",
  "orientacao": "bi"
}
```

---



## 📝 Licença

Este projeto está sob a Licença MIT.

---
