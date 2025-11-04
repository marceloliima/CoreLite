# 🧩 MVC Framework PHP — Sistema de Usuários

Um mini-framework PHP **moderno e seguro**, desenvolvido do zero em arquitetura **MVC**, inspirado no **Laravel**, com foco em aprendizado de **Análise e Desenvolvimento de Sistemas** e boas práticas de **Clean Code**, **Segurança** e **Organização de Projeto**.

---

## 👨‍💻 Autor

**Marcelo Lima**  
📍 Estudante de Análise e Desenvolvimento de Sistemas  
🌐 [github.com/marceloliima](https://github.com/marceloliima)

---

## 🚀 Tecnologias Utilizadas

- **PHP 8.2+** (tipagem estrita, PSR-4)
- **PDO (MySQL)** — conexão segura e orientada a objetos
- **HTML5 / CSS3 / JS (Vanilla)**
- **Arquitetura MVC pura**
- **.env** para variáveis de ambiente
- **Sessões seguras e CSRF Token**
- **Sistema de Rotas fluente (Router customizado)**
- **Modelo ORM simplificado (estilo Eloquent)**

---

## 🏗️ Estrutura do Projeto

```
📦 Projeto/
│
├── 📂 App/
│   ├── 📂 Controllers/
│   │   ├── AuthController.php
│   │   └── UsuarioController.php
│   │
│   ├── 📂 Core/
│   │   ├── Controller.php
│   │   ├── Model.php
│   │   ├── Router.php
│   │   ├── Csrf.php
│   │   ├── Database.php
│   │   └── Env.php
│   │
│   ├── 📂 Models/
│   │   └── UsuarioModel.php
│   │
│   ├── 📂 Views/
│   │   ├── login.php
│   │   ├── usuarios/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   └── show.php
│   │
│   └── bootstrap.php
│
├── 📂 public/
│   └── index.php   # ponto de entrada da aplicação
│
├── 📄 .env.example
└── 📄 README.md
```

---

## ⚙️ Configuração do Ambiente

### 1️⃣ Clonar o repositório

```bash
git clone https://github.com/marceloliima/CoreLite.git
cd CoreLite
```

### 2️⃣ Configurar o arquivo `.env`

Copie o exemplo:

```bash
cp .env.example .env
```

Edite as variáveis conforme seu ambiente:

```ini
DB_HOST=localhost
DB_NAME=meu_banco
DB_USER=root
DB_PASS=
APP_ENV=local
APP_DEBUG=true
```

### 3️⃣ Configurar o banco de dados

Crie a tabela `usuarios` no MySQL:

```sql
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  senha_hash VARCHAR(255) NOT NULL,
  funcao ENUM('admin', 'usuario') DEFAULT 'usuario',
  status ENUM('ativo', 'inativo') DEFAULT 'ativo',
  criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
  atualizado_em DATETIME NULL
);
```

### 4️⃣ Rodar o servidor embutido do PHP

```bash
php -S localhost:8000 -t public
```

Acesse em:  
👉 http://localhost:8000

---

## 🧱 Principais Componentes

### 🔸 Core/Model.php
- ORM leve inspirado no Eloquent
- Métodos: `where()`, `first()`, `get()`, `create()`, `update()`, `delete()`, `updateOrCreate()`

### 🔸 Core/Router.php
- Sistema de rotas com suporte a:
  - `GET`, `POST`, `PUT`, `DELETE`
  - Agrupamento com prefixo e middleware
  - Parâmetros dinâmicos `{id}`

### 🔸 Core/Csrf.php
- Geração e validação de **CSRF Tokens**
- Proteção automática em formulários POST

### 🔸 Controllers/
- `AuthController` → login/logout
- `UsuarioController` → CRUD de usuários

### 🔸 Models/
- `UsuarioModel` → lógica de autenticação, hashing de senhas e persistência

---

## 🔐 Segurança Implementada

- Hash de senha com `password_hash()` (Bcrypt)
- Proteção CSRF em todos os formulários
- Sessão segura (SameSite Strict, HttpOnly)
- Filtros e validação de entrada (filter_var, trim, etc.)
- Mensagens de erro via FlashMessage

---

## 🧠 Conceitos Aplicados

- Padrão **MVC**
- **Dependency Injection** (PDO, Controllers)
- **Fluent Interface** em consultas (`Model`)
- **Autoload PSR-4** manual (bootstrap)
- **Front Controller Pattern** (`index.php`)
- **Middleware Pattern** em rotas

---

## 🧩 Rotas Disponíveis

| Método | Rota | Controller | Ação |
|--------|------|-------------|------|
| GET | / | UsuarioController | index |
| GET | /login | AuthController | login |
| POST | /login | AuthController | login |
| GET | /logout | AuthController | logout |
| GET | /usuarios | UsuarioController | index |
| GET | /usuarios/show/{id} | UsuarioController | show |
| GET | /usuarios/create | UsuarioController | create |
| POST | /usuarios/store | UsuarioController | store |
| GET | /usuarios/edit/{id} | UsuarioController | edit |
| POST | /usuarios/update/{id} | UsuarioController | update |
| POST | /usuarios/delete/{id} | UsuarioController | delete |

---

## 📄 Licença

Este projeto é distribuído sob a licença **MIT**.  
Você pode usá-lo livremente para fins acadêmicos e comerciais.

---

## 💬 Contato

📧 **marceloliima.dev@gmail.com**  
🔗 GitHub: [@marceloliima](https://github.com/marceloliima)

---

> 💡 Projeto criado para fins educacionais e demonstração de arquitetura limpa em PHP, sem dependências externas, ideal para aprendizado de **MVC, segurança e boas práticas** em back-end.
