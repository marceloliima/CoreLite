<?php
$pageTitle = 'Usuários';
use App\Core\Auth;
use App\Core\Csrf;
?>
<section class="page-head">
    <div><p class="eyebrow">Gerenciamento</p><h1>Usuários</h1><p class="muted"><?= e($result['total']) ?> usuário(s) encontrado(s).</p></div>
    <?php if (Auth::hasRole('admin')): ?><a class="btn btn-primary" href="<?= e(url('/users/create')) ?>">Cadastrar usuário</a><?php endif; ?>
</section>

<section class="panel">
<form method="get" action="<?= e(url('/users')) ?>" class="filters">
    <label><span>Buscar</span><input type="search" name="q" value="<?= e($search) ?>" placeholder="Nome ou e-mail"></label>
    <label><span>Perfil</span><select name="role"><option value="">Todos</option><option value="admin" <?= selected($role,'admin') ?>>Admin</option><option value="manager" <?= selected($role,'manager') ?>>Manager</option><option value="user" <?= selected($role,'user') ?>>User</option></select></label>
    <label><span>Status</span><select name="status"><option value="">Todos</option><option value="active" <?= selected($status,'active') ?>>Ativo</option><option value="inactive" <?= selected($status,'inactive') ?>>Inativo</option></select></label>
    <button class="btn" type="submit">Filtrar</button>
    <a class="btn btn-quiet" href="<?= e(url('/users')) ?>">Limpar</a>
</form>
</section>

<section class="panel table-panel">
<div class="table-wrap">
<table>
<thead><tr><th>Usuário</th><th>Perfil</th><th>Status</th><th>Último acesso</th><th>Ações</th></tr></thead>
<tbody>
<?php foreach ($result['items'] as $user): ?>
<tr>
<td><strong><?= e($user['name']) ?></strong><small><?= e($user['email']) ?></small></td>
<td><span class="badge"><?= e($user['role']) ?></span></td>
<td><span class="badge <?= $user['status']==='active'?'badge-ok':'badge-muted' ?>"><?= $user['status']==='active'?'Ativo':'Inativo' ?></span></td>
<td><?= e($user['last_login_at'] ?: 'Nunca') ?></td>
<td class="actions">
<a class="link" href="<?= e(url('/users/' . $user['id'])) ?>">Ver</a>
<?php if (Auth::hasRole('admin')): ?>
<a class="link" href="<?= e(url('/users/' . $user['id'] . '/edit')) ?>">Editar</a>
<?php if (Auth::id() !== (int)$user['id']): ?>
<form method="post" action="<?= e(url('/users/' . $user['id'] . '/status')) ?>"><input type="hidden" name="csrf_token" value="<?= e(Csrf::token('users.status.' . $user['id'])) ?>"><button class="link link-button" type="submit"><?= $user['status']==='active'?'Desativar':'Ativar' ?></button></form>
<form method="post" action="<?= e(url('/users/' . $user['id'] . '/delete')) ?>" data-confirm="Remover este usuário? Esta ação fará soft delete."><input type="hidden" name="csrf_token" value="<?= e(Csrf::token('users.delete.' . $user['id'])) ?>"><button class="link link-danger link-button" type="submit">Remover</button></form>
<?php endif; endif; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$result['items']): ?><tr><td colspan="5" class="empty">Nenhum usuário encontrado.</td></tr><?php endif; ?>
</tbody></table></div>

<?php if ($result['pages'] > 1): ?>
<nav class="pagination" aria-label="Paginação">
<?php for ($p=1;$p<=$result['pages'];$p++): $qs=http_build_query(['q'=>$search,'role'=>$role,'status'=>$status,'page'=>$p]); ?>
<a class="<?= $p===$result['page']?'active':'' ?>" href="<?= e(url('/users?' . $qs)) ?>"><?= $p ?></a>
<?php endfor; ?>
</nav>
<?php endif; ?>
</section>
