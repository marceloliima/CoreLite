<?php
$pageTitle = 'Dashboard';
use App\Core\Auth;
?>
<section class="page-head">
    <div>
        <p class="eyebrow">Visão geral</p>
        <h1>Dashboard</h1>
        <p class="muted">Olá, <?= e($currentUser['name']) ?>. Aqui está o estado atual do sistema.</p>
    </div>
    <?php if (Auth::hasRole('admin')): ?><a class="btn btn-primary" href="<?= e(url('/users/create')) ?>">Novo usuário</a><?php endif; ?>
</section>

<section class="stats-grid">
    <article class="stat-card"><span>Total de usuários</span><strong><?= e($stats['total']) ?></strong><small>Contas não removidas</small></article>
    <article class="stat-card"><span>Usuários ativos</span><strong><?= e($stats['active']) ?></strong><small>Com acesso permitido</small></article>
    <article class="stat-card"><span>Administradores</span><strong><?= e($stats['admins']) ?></strong><small>Acesso completo</small></article>
    <article class="stat-card"><span>Gerentes</span><strong><?= e($stats['managers']) ?></strong><small>Acesso de consulta</small></article>
</section>

<section class="panel">
    <div class="panel-head"><div><p class="eyebrow">Arquitetura</p><h2>Segurança por padrão</h2></div></div>
    <div class="feature-grid">
        <article><strong>Autenticação</strong><p>Password hashing nativo, rehash e sessão regenerada.</p></article>
        <article><strong>Proteção CSRF</strong><p>Tokens contextuais, temporários e de uso único.</p></article>
        <article><strong>Cadastro controlado</strong><p>Registro público sempre cria usuário comum; privilégios ficam no painel.</p></article>
        <article><strong>Auditoria</strong><p>Eventos administrativos relevantes ficam registrados.</p></article>
    </div>
</section>
