<?php $pageTitle = 'Entrar'; ?>
<section class="auth-card">
    <div class="auth-badge">SP</div>
    <p class="eyebrow">Área segura</p>
    <h1>SecurePanel PHP</h1>
    <p class="muted">Entre com sua conta para acessar o painel.</p>

    <form method="post" action="<?= e(url('/login')) ?>" class="form-grid auth-form" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <label class="field field-full">
            <span>E-mail</span>
            <input type="email" name="email" value="<?= e(old('email')) ?>" maxlength="190" required autocomplete="username" autofocus>
        </label>
        <label class="field field-full">
            <span>Senha</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn btn-primary btn-full field-full" type="submit">Entrar com segurança</button>
    </form>

    <?php if ($registrationEnabled): ?>
    <div class="auth-links">
        <span>Ainda não tem conta?</span>
        <a class="link" href="<?= e(url('/register')) ?>">Criar conta</a>
    </div>
    <?php endif; ?>

    <p class="login-note">Sessão protegida, CSRF, limite de tentativas e senhas com hash nativo do PHP.</p>
</section>
