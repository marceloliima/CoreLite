<?php

use App\Controllers\AuthController;
use App\Core\FlashMessage;

$auth = new AuthController();
$usuarioLogado = $auth->estaLogado();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Sistema MVC', ENT_QUOTES, 'UTF-8'); ?></title>
</head>

<body>
    <header>
        <nav>
            <a href="/">Início</a>
            <?php if ($usuarioLogado): ?>
                <li><a href="/usuarios/create">Novo Usuário</a></li>

                <?php if ($auth->temFuncao('admin')): ?>
                    <li><a href="/usuarios">Usuários</a></li>
                <?php endif; ?>

                <li><a href="/logout">Sair</a></li>
                <span style="margin-left: 10px; color: #333;">
                    <li>Olá, <?= htmlspecialchars($auth->getUsuario()->nome, ENT_QUOTES, 'UTF-8'); ?></li>
                </span>

            <?php else: ?>
                <li><a href="/login">Login</a></li>
                <li><a href="/registro">Registrar</a></li>
            <?php endif; ?>
        </nav>
        <hr>
    </header>

    <main>
        <!-- Mensagens Flash -->
        <?php FlashMessage::exibir(true); ?>

        <!-- Conteúdo da view -->
        <?= $content ?? '<p>Nenhum conteúdo carregado.</p>'; ?>
    </main>

    <footer>
        <hr>
        <p style="text-align:center; font-size:0.9em; color:#555;">
            &copy; <?= date('Y'); ?> - Sistema MVC em PHP 8.2
        </p>
    </footer>
</body>

</html>