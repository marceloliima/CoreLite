<?php $pageTitle = 'Acesso negado'; ?>
<section class="error-card"><span>403</span><h1>Acesso negado</h1><p><?= e($message ?? 'Você não possui permissão para acessar este recurso.') ?></p><a class="btn btn-primary" href="<?= e(url('/')) ?>">Voltar ao início</a></section>
