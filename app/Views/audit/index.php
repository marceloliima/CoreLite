<?php $pageTitle = 'Auditoria'; ?>
<section class="page-head">
    <div>
        <p class="eyebrow">Segurança</p>
        <h1>Auditoria</h1>
        <p class="muted">Histórico de eventos importantes da aplicação.</p>
    </div>
</section>

<section class="panel">
    <form method="get" action="<?= e(url('/audit')) ?>" class="filters filters-audit">
        <label>
            <span>Filtrar por ação</span>
            <input type="search" name="action" value="<?= e($action) ?>" maxlength="80" placeholder="Ex.: login_success">
        </label>
        <button class="btn btn-primary" type="submit">Filtrar</button>
        <a class="btn" href="<?= e(url('/audit')) ?>">Limpar</a>
    </form>
</section>

<section class="panel table-panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>Data</th><th>Ação</th><th>Usuário</th><th>Entidade</th><th>Detalhes</th></tr>
            </thead>
            <tbody>
            <?php if (!$result['items']): ?>
                <tr><td colspan="5" class="empty">Nenhum evento encontrado.</td></tr>
            <?php else: ?>
                <?php foreach ($result['items'] as $item): ?>
                    <?php
                    $details = '-';
                    if (is_string($item['details']) && $item['details'] !== '') {
                        $decoded = json_decode($item['details'], true);
                        if (is_array($decoded)) {
                            $details = (string) json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        }
                    }
                    ?>
                    <tr>
                        <td><?= e($item['created_at']) ?></td>
                        <td><span class="badge"><?= e($item['action']) ?></span></td>
                        <td>
                            <?= e($item['user_name'] ?? 'Sistema/visitante') ?>
                            <?php if (!empty($item['user_email'])): ?><small><?= e($item['user_email']) ?></small><?php endif; ?>
                        </td>
                        <td><?= e(($item['entity_type'] ?? '-') . (!empty($item['entity_id']) ? ' #' . $item['entity_id'] : '')) ?></td>
                        <td class="audit-details"><?= e($details) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($result['pages'] > 1): ?>
    <nav class="pagination" aria-label="Paginação">
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
            <a class="<?= $i === $result['page'] ? 'active' : '' ?>" href="<?= e(url('/audit?action=' . rawurlencode($action) . '&page=' . $i)) ?>"><?= e($i) ?></a>
        <?php endfor; ?>
    </nav>
    <?php endif; ?>
</section>
