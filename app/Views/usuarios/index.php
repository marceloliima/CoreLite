<h1>Lista de Usuários</h1>

<a href="/usuarios/create">Cadastrar Novo Usuário</a>

<?php

use App\Core\Formatter;
use App\Controllers\AuthController;

if (!empty($usuarios)): ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Função</th>
                <th>Status</th>
                <?php if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']['funcao'] === 'admin'): ?>
                    <th>Criado em</th>
                    <th>Atualizado em</th>
                    <th>Ações</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario->id, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($usuario->nome, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($usuario->email, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($usuario->funcao, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($usuario->status, ENT_QUOTES, 'UTF-8') ?></td>
                    <?php if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado']['funcao'] === 'admin'): ?>
                        <td><?= Formatter::datetime($usuario->criado_em) ?></td>
                        <td><?= Formatter::datetime($usuario->atualizado_em) ?></td>
                        <td>
                            <!-- Links usando parâmetro ID na URL -->
                            <a href="/usuarios/show/<?= urlencode($usuario->id) ?>">Visualizar</a> |
                            <a href="/usuarios/edit/<?= urlencode($usuario->id) ?>">Editar</a> |

                            <!-- Form de exclusão seguro via POST -->
                            <form action="/usuarios/delete/<?= urlencode($usuario->id) ?>" method="POST" style="display:inline;"
                                onsubmit="return confirm('Deseja realmente excluir este usuário?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">Excluir</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Nenhum usuário cadastrado.</p>
<?php endif; ?>