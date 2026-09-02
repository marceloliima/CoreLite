# SecurePanel PHP 8.2

Sistema completo de autenticação e gerenciamento de usuários em **PHP 8.2 puro**, sem frameworks, sem Composer, sem Bootstrap, sem CDN, sem fontes externas e sem bibliotecas de terceiros.

## Recursos

- MVC próprio.
- Router próprio.
- PDO com prepared statements reais.
- Primeiro acesso web em `/setup`.
- Criação do primeiro administrador sem senha padrão.
- Bloqueio do instalador após a primeira configuração.
- `INSTALL_KEY` opcional para proteger o primeiro acesso.
- Cadastro público em `/register`.
- Cadastro público sempre cria `role=user` e `status=active` no backend.
- Cadastro público pode ser desligado no `.env`.
- Login/logout.
- Dashboard.
- CRUD completo de usuários.
- Perfis `admin`, `manager` e `user`.
- Busca, filtros e paginação.
- Ativar/desativar usuário.
- Soft delete.
- Proteção do último administrador ativo.
- Perfil do próprio usuário.
- Alteração de senha exigindo a senha atual.
- Auditoria administrativa.
- Rate limit de login e de cadastro.
- CSRF contextual e de uso único.
- `password_hash`, `password_verify` e rehash automático.
- Sessões endurecidas e regeneração de ID.
- Timeouts de sessão.
- CSP e cabeçalhos HTTP de segurança.
- CSS e JavaScript locais.

## Instalação no XAMPP

1. Extraia para `C:\xampp\htdocs\securepanel`.
2. Importe `database.sql` no phpMyAdmin.
3. Copie `.env.example` para `.env`.
4. Ajuste o banco no `.env`.
5. Configure o Apache para apontar o DocumentRoot para `C:/xampp/htdocs/securepanel/public`.

Exemplo de VirtualHost:

```apache
<VirtualHost *:80>
    ServerName securepanel.local
    DocumentRoot "C:/xampp/htdocs/securepanel/public"

    <Directory "C:/xampp/htdocs/securepanel/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

No arquivo `C:\Windows\System32\drivers\etc\hosts`:

```text
127.0.0.1 securepanel.local
```

No `.env`:

```env
APP_URL="http://securepanel.local"
```

## Primeiro acesso

Ao acessar pela primeira vez, `/login` redireciona para `/setup`.

Nessa tela você cria o primeiro administrador. O sistema não possui usuário ou senha padrão.

Se desejar proteger o instalador com uma chave adicional:

```env
INSTALL_KEY="uma-chave-longa-e-aleatoria"
```

Depois que o administrador é criado, o estado de instalação é marcado no banco e `/setup` deixa de funcionar.

## Cadastro público

A rota `/register` permite que qualquer visitante crie uma conta comum depois da instalação.

O formulário **não envia perfil nem status**. O backend força:

```text
role = user
status = active
```

Para desativar o cadastro público:

```env
PUBLIC_REGISTRATION=false
```

Administradores continuam podendo cadastrar usuários pelo painel.

## Perfis

- **admin:** acesso completo, usuários e auditoria.
- **manager:** visualiza usuários e detalhes, sem alterar contas.
- **user:** dashboard, perfil e senha própria.

## Senhas

Mínimo de 12 caracteres, incluindo:

- maiúscula;
- minúscula;
- número;
- símbolo.

As senhas são armazenadas com `PASSWORD_DEFAULT`.

## Banco

`database.sql` cria:

- `installation_state`
- `users`
- `login_attempts`
- `registration_attempts`
- `audit_logs`

## Rotas principais

```text
GET/POST /setup
GET/POST /login
GET/POST /register
POST     /logout
GET      /
GET/POST /profile
POST     /profile/password
GET      /users
GET      /users/create
POST     /users
GET      /users/{id}
GET      /users/{id}/edit
POST     /users/{id}/update
POST     /users/{id}/status
POST     /users/{id}/delete
GET      /audit
```

## Segurança

O projeto inclui CSRF, prepared statements, escaping de saída, rate limiting, sessão com cookies `HttpOnly`/`SameSite=Strict`, `Secure` em HTTPS, proteção contra session fixation, auditoria, soft delete e proteção do último administrador ativo.

Em produção use HTTPS, `APP_DEBUG=false`, mantenha PHP/Apache/MySQL atualizados e aponte o servidor apenas para a pasta `public/`.

Nenhuma aplicação deve ser considerada absolutamente segura; segurança também depende da configuração e operação do servidor.
