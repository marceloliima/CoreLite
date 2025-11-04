<h1>Detalhes do Usuário</h1>

<?php

use App\Core\Formatter;

 if ($usuario): ?>
    <ul>
        <li><strong>ID:</strong> <?= htmlspecialchars($usuario->id) ?></li>
        <li><strong>Nome:</strong> <?= htmlspecialchars($usuario->nome) ?></li>
        <li><strong>E-mail:</strong> <?= htmlspecialchars($usuario->email) ?></li>
        <li><strong>Função:</strong> <?= htmlspecialchars($usuario->funcao) ?></li>
        <li><strong>Status:</strong> <?= htmlspecialchars($usuario->status) ?></li>
        <li><strong>Criado em:</strong> <?= Formatter::datetime($usuario->criado_em) ?></li>
        <li><strong>Atualizado em:</strong> <?= Formatter::datetime($usuario->atualizado_em ?? '-') ?></li>
    </ul>

    <p>
        <a href="/usuarios/edit/<?= $usuario->id ?>">Editar</a> |
        <a href="/usuarios">Voltar para lista</a>
    </p>
<?php else: ?>
    <p>Usuário não encontrado.</p>
    <p><a href="/usuarios">Voltar para lista</a></p>
<?php endif; ?>
