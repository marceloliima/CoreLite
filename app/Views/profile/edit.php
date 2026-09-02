<?php $pageTitle = 'Meu perfil'; ?>
<section class="page-head">
    <div>
        <p class="eyebrow">Conta</p>
        <h1>Meu perfil</h1>
        <p class="muted">Atualize seus dados pessoais e mantenha sua senha protegida.</p>
    </div>
</section>

<section class="panel form-panel">
    <div class="panel-head">
        <div><p class="eyebrow">Dados pessoais</p><h2>Informações da conta</h2></div>
    </div>

    <form method="post" action="<?= e(url('/profile')) ?>" class="form-grid">
        <input type="hidden" name="csrf_token" value="<?= e($csrfProfile) ?>">
        <label class="field field-full">
            <span>Nome completo</span>
            <input type="text" name="name" value="<?= e($currentUser['name']) ?>" minlength="2" maxlength="120" required autocomplete="name">
        </label>
        <label class="field field-full">
            <span>E-mail</span>
            <input type="email" name="email" value="<?= e($currentUser['email']) ?>" maxlength="190" required autocomplete="email">
        </label>
        <div class="form-actions field-full">
            <button class="btn btn-primary" type="submit">Salvar perfil</button>
        </div>
    </form>
</section>

<section class="panel form-panel">
    <div class="panel-head">
        <div><p class="eyebrow">Segurança</p><h2>Alterar senha</h2></div>
    </div>

    <form method="post" action="<?= e(url('/profile/password')) ?>" class="form-grid" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e($csrfPassword) ?>">
        <label class="field field-full">
            <span>Senha atual</span>
            <input type="password" name="current_password" required autocomplete="current-password">
        </label>
        <label class="field">
            <span>Nova senha</span>
            <input type="password" name="password" minlength="12" required autocomplete="new-password">
        </label>
        <label class="field">
            <span>Confirmar nova senha</span>
            <input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password">
        </label>
        <p class="help field-full">Mínimo de 12 caracteres, com maiúscula, minúscula, número e símbolo.</p>
        <div class="form-actions field-full">
            <button class="btn btn-primary" type="submit">Alterar senha</button>
        </div>
    </form>
</section>
