<h1>Editar Usuário</h1>

<?php if ($usuario): ?>
<form action="/usuarios/update/<?= $usuario->id ?>" method="POST">
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">

    <!-- Nome -->
    <div>
        <label for="nome">Nome completo:</label><br>
        <input
            type="text"
            id="nome"
            name="nome"
            required
            value="<?= htmlspecialchars($usuario->nome, ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <!-- Email -->
    <div>
        <label for="email">E-mail:</label><br>
        <input
            type="email"
            id="email"
            name="email"
            required
            value="<?= htmlspecialchars($usuario->email, ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <!-- Senha (opcional) -->
    <div>
        <label for="senha">Senha (deixe em branco para manter a atual):</label><br>
        <input
            type="password"
            id="senha"
            name="senha"
            minlength="6"
            placeholder="Nova senha se desejar alterar">
    </div>

    <!-- Função -->
    <div>
        <label for="funcao">Função:</label><br>
        <select id="funcao" name="funcao">
            <option value="usuario" <?= $usuario->funcao === 'usuario' ? 'selected' : '' ?>>Usuário</option>
            <option value="admin" <?= $usuario->funcao === 'admin' ? 'selected' : '' ?>>Administrador</option>
        </select>
    </div>

    <!-- Status -->
    <div>
        <label for="status">Status:</label><br>
        <select id="status" name="status">
            <option value="ativo" <?= $usuario->status === 'ativo' ? 'selected' : '' ?>>Ativo</option>
            <option value="inativo" <?= $usuario->status === 'inativo' ? 'selected' : '' ?>>Inativo</option>
        </select>
    </div>

    <!-- Botões de ação -->
    <div>
        <button type="submit">Atualizar Usuário</button>
        <!-- Link de cancelamento volta para o show usando rota com parâmetro -->
        <a href="/usuarios/show/<?= $usuario->id ?>">Cancelar</a>
    </div>
</form>
<?php else: ?>
    <!-- Mensagem caso o usuário não exista -->
    <p>Usuário não encontrado.</p>
    <p><a href="/usuarios">Voltar para lista</a></p>
<?php endif; ?>