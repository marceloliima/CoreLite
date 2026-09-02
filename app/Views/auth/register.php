<?php $pageTitle = 'Criar conta'; ?>
<section class="auth-card auth-card-wide">
    <div class="auth-badge">SP</div>
    <p class="eyebrow">Cadastro público</p>
    <h1>Crie sua conta</h1>
    <p class="muted">O cadastro público cria somente contas do perfil Usuário. Permissões administrativas só podem ser concedidas dentro do painel.</p>

    <form method="post" action="<?= e(url('/register')) ?>" class="form-grid auth-form" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <label class="field field-full">
            <span>Nome completo</span>
            <input type="text" name="name" value="<?= e(old('name')) ?>" minlength="2" maxlength="120" required autocomplete="name" autofocus>
        </label>
        <label class="field field-full">
            <span>E-mail</span>
            <input type="email" name="email" value="<?= e(old('email')) ?>" maxlength="190" required autocomplete="email">
        </label>
        <label class="field field-full">
            <span>Senha</span>
            <input type="password" name="password" minlength="12" required autocomplete="new-password">
        </label>
        <label class="field field-full">
            <span>Confirmar senha</span>
            <input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password">
        </label>

        <p class="help field-full">Mínimo de 12 caracteres, com maiúscula, minúscula, número e símbolo.</p>
        <button class="btn btn-primary btn-full field-full" type="submit">Criar minha conta</button>
    </form>

    <div class="auth-links">
        <span>Já possui uma conta?</span>
        <a class="link" href="<?= e(url('/login')) ?>">Entrar</a>
    </div>
</section>
