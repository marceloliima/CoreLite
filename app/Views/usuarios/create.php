<form action="/usuarios/store" method="POST">
    <h2>Cadastrar Novo Usuário</h2>

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
            placeholder="Ex: João da Silva">
    </div>

    <!-- Email -->
    <div>
        <label for="email">E-mail:</label><br>
        <input 
            type="email" 
            id="email" 
            name="email" 
            required 
            placeholder="exemplo@dominio.com">
    </div>

    <!-- Senha -->
    <div>
        <label for="senha">Senha:</label><br>
        <input 
            type="password" 
            id="senha" 
            name="senha" 
            required 
            minlength="6" 
            placeholder="Mínimo 6 caracteres">
    </div>

    <!-- Função -->
    <div>
        <label for="funcao">Função:</label><br>
        <select id="funcao" name="funcao">
            <option value="usuario">Usuário</option>
            <option value="admin">Administrador</option>
        </select>
    </div>

    <!-- Status -->
    <div>
        <label for="status">Status:</label><br>
        <select id="status" name="status">
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
        </select>
    </div>

    <!-- Botões -->
    <div>
        <button type="submit">Salvar Usuário</button>
        <a href="/usuarios">Cancelar</a>
    </div>
</form>