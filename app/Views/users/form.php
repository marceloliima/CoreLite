<div class="form-grid">
    <label class="field field-full"><span>Nome completo</span><input type="text" name="name" value="<?= e(old('name', $user['name'] ?? '')) ?>" minlength="2" maxlength="120" required autocomplete="name"></label>
    <label class="field field-full"><span>E-mail</span><input type="email" name="email" value="<?= e(old('email', $user['email'] ?? '')) ?>" maxlength="190" required autocomplete="email"></label>
    <label class="field"><span>Perfil</span><select name="role" required><?php $rv=old('role',$user['role']??'user'); ?><option value="user" <?= selected($rv,'user') ?>>Usuário</option><option value="manager" <?= selected($rv,'manager') ?>>Gerente</option><option value="admin" <?= selected($rv,'admin') ?>>Administrador</option></select></label>
    <label class="field"><span>Status</span><select name="status" required><?php $sv=old('status',$user['status']??'active'); ?><option value="active" <?= selected($sv,'active') ?>>Ativo</option><option value="inactive" <?= selected($sv,'inactive') ?>>Inativo</option></select></label>
    <label class="field"><span>Senha <?= isset($user)?'<small>(opcional)</small>':'' ?></span><input type="password" name="password" <?= isset($user)?'':'required' ?> autocomplete="new-password" minlength="12"></label>
    <label class="field"><span>Confirmar senha</span><input type="password" name="password_confirmation" <?= isset($user)?'':'required' ?> autocomplete="new-password" minlength="12"></label>
</div>
<p class="help">Senha: mínimo de 12 caracteres, incluindo letra maiúscula, minúscula, número e símbolo.</p>
