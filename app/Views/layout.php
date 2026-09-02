<?php
use App\Core\Auth;
use App\Core\Csrf;

$pageTitle = $pageTitle ?? config('app.name', 'SecurePanel PHP');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="color-scheme" content="light">
    <title><?= e($pageTitle) ?> | <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/app.css')) ?>">
    <script src="<?= e(url('/assets/js/app.js')) ?>" defer></script>
</head>
<body>
<?php if ($currentUser): ?>
<header class="topbar">
    <a class="brand" href="<?= e(url('/')) ?>" aria-label="Ir para dashboard">
        <span class="brand-mark">SP</span>
        <span><strong>SecurePanel</strong><small>PHP 8.2 puro</small></span>
    </a>

    <button class="menu-button" type="button" data-menu-button aria-expanded="false" aria-controls="main-nav">Menu</button>

    <nav id="main-nav" class="main-nav" data-menu>
        <a href="<?= e(url('/')) ?>">Dashboard</a>
        <?php if (Auth::hasRole('admin', 'manager')): ?><a href="<?= e(url('/users')) ?>">Usuários</a><?php endif; ?>
        <?php if (Auth::hasRole('admin')): ?><a href="<?= e(url('/audit')) ?>">Auditoria</a><?php endif; ?>
        <a href="<?= e(url('/profile')) ?>">Meu perfil</a>
        <div class="user-chip"><span><?= e($currentUser['name']) ?></span><small><?= e($currentUser['role']) ?></small></div>
        <form method="post" action="<?= e(url('/logout')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(Csrf::token('logout')) ?>">
            <button class="btn btn-quiet" type="submit">Sair</button>
        </form>
    </nav>
</header>
<?php endif; ?>

<main class="<?= $currentUser ? 'shell' : 'auth-shell' ?>">
    <?php foreach ($flashMessages as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>" role="status" data-flash>
            <span><?= e($flash['message']) ?></span>
            <button type="button" aria-label="Fechar mensagem" data-flash-close>×</button>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="footer">SecurePanel PHP · <?= date('Y') ?> · PHP, CSS e JavaScript puros</footer>
</body>
</html>
