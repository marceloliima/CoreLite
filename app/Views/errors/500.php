<?php $pageTitle = 'Erro interno'; ?>
<section class="error-card"><span>500</span><h1>Erro interno</h1><p><?= e($message ?? 'Ocorreu um erro inesperado. Tente novamente em instantes.') ?></p><a class="btn btn-primary" href="<?= e(url('/')) ?>">Voltar ao início</a></section>
