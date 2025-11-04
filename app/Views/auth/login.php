<form action="/login" method="POST">
    <h2>Login</h2>

    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES); ?>">

    <label for="email">E-mail:</label>
    <input
        type="email"
        id="email"
        name="email"
        required
        placeholder="Digite seu e-mail"
        autocomplete="email"
        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES); ?>"
    >

    <label for="senha">Senha:</label>
    <input
        type="password"
        id="senha"
        name="senha"
        required
        placeholder="Digite sua senha"
        autocomplete="current-password"
    >

    <button type="submit" class="btn-login">Entrar</button>
</form>