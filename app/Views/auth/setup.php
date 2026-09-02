<?php $pageTitle = 'Primeiro acesso'; ?>
<section class="auth-card auth-card-wide">
    <div class="auth-badge">01</div>
    <p class="eyebrow">Instalação inicial</p>
    <h1>Crie o primeiro administrador</h1>
    <p class="muted">O SecurePanel não possui credencial padrão. Esta tela funciona somente até a criação do primeiro administrador.</p>

    <div class="security-note">
        <strong>Primeiro acesso protegido</strong>
        <span>Após concluir esta etapa, o instalador é bloqueado pelo banco de dados.</span>
    </div>

    <form method="post" action="<?= e(url('/setup')) ?>" class="form-grid auth-form" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">

        <?php if ($requiresInstallKey): ?>
        <label class="field field-full">
            <span>Chave de instalação</span>
            <input type="password" name="installation_key" required autocomplete="off">
            <small class="help">Definida em INSTALL_KEY no arquivo .env.</small>
        </label>
        <?php endif; ?>

        <label class="field field-full">
            <span>Nome do administrador</span>
            <input type="text" name="name" value="<?= e(old('name')) ?>" minlength="2" maxlength="120" required autocomplete="name" autofocus>
        </label>
        <label class="field field-full">
            <span>E-mail</span>
            <input type="email" name="email" value="<?= e(old('email')) ?>" maxlength="190" required autocomplete="email">
        </label>
        <label class="field field-full">
            <span>Senha administrativa</span>
            <input type="password" name="password" minlength="12" required autocomplete="new-password">
        </label>
        <label class="field field-full">
            <span>Confirmar senha</span>
            <input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password">
        </label>

        <p class="help field-full">Use uma senha exclusiva com 12 ou mais caracteres, maiúscula, minúscula, número e símbolo.</p>
        <button class="btn btn-primary btn-full field-full" type="submit">Concluir instalação</button>
    </form>
</section>
