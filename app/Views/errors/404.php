<?php $pageTitle = 'Página não encontrada'; ?>
<section class="error-card"><span>404</span><h1>Página não encontrada</h1><p><?= e($message ?? 'O endereço solicitado não existe ou foi removido.') ?></p><a class="btn btn-primary" href="<?= e(url('/')) ?>">Voltar ao início</a></section>
