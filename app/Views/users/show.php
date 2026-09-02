<?php
$pageTitle = 'Detalhes do usuário';
use App\Core\Auth;
?>
<section class="page-head"><div><p class="eyebrow">Usuário #<?= e($user['id']) ?></p><h1><?= e($user['name']) ?></h1><p class="muted"><?= e($user['email']) ?></p></div><div class="head-actions"><a class="btn btn-quiet" href="<?= e(url('/users')) ?>">Voltar</a><?php if(Auth::hasRole('admin')):?><a class="btn btn-primary" href="<?= e(url('/users/' . $user['id'] . '/edit')) ?>">Editar</a><?php endif;?></div></section>
<section class="panel detail-grid">
<div><span>Perfil</span><strong><?= e($user['role']) ?></strong></div><div><span>Status</span><strong><?= $user['status']==='active'?'Ativo':'Inativo' ?></strong></div><div><span>Criado em</span><strong><?= e($user['created_at']) ?></strong></div><div><span>Atualizado em</span><strong><?= e($user['updated_at'] ?: '—') ?></strong></div><div><span>Último login</span><strong><?= e($user['last_login_at'] ?: 'Nunca') ?></strong></div><div><span>ID interno</span><strong>#<?= e($user['id']) ?></strong></div>
</section>
