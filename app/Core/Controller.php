<?php
namespace App\Core;

/**
 * Class Controller
 * 
 * Classe base abstrata para todos os controllers do sistema.
 * Fornece métodos comuns como renderização de views e validação CSRF.
 *
 * @package App\Core
 */
abstract class Controller
{
    /**
     * Renderiza uma view dentro do layout principal.
     *
     * Este método recebe o caminho da view (relativo à pasta Views)
     * e um array de parâmetros que serão extraídos como variáveis.
     * A view é carregada dentro de um buffer de saída e depois incluída
     * no layout principal.
     *
     * @param string $path   Caminho relativo da view (ex: 'usuarios/index')
     * @param array  $params Parâmetros a serem passados para a view
     *
     * @return string HTML renderizado com layout
     */
    protected function view(string $path, array $params = []): string
    {
        // Extrai os parâmetros para variáveis dentro do escopo da view
        extract($params, EXTR_SKIP);

        // Inicia buffer de saída e carrega a view
        ob_start();
        require __DIR__ . '/../Views/' . $path . '.php';
        $content = ob_get_clean(); // captura o conteúdo da view

        // Injeta a view no layout principal
        ob_start();
        require __DIR__ . '/../Views/layout.php'; // layout.php
        return ob_get_clean(); // retorna HTML completo
    }

    /**
     * Valida o token CSRF enviado em formulários POST.
     *
     * Este método compara o token enviado pelo formulário com o token
     * armazenado na sessão. Caso sejam diferentes, a execução é abortada
     * com código 403.
     *
     * Uso recomendado:
     * ```php
     * $this->checkCsrf();
     * ```
     *
     * @return void
     */
    public function checkCsrf(): void
    {
        // Pega o token enviado pelo formulário, ou string vazia se não existir
        $token = $_POST['csrf_token'] ?? '';

        // Verifica se o token existe na sessão e se é igual ao enviado
        if (
            empty($_SESSION['csrf_token']) || 
            !hash_equals($_SESSION['csrf_token'], $token)
        ) {
            // Caso inválido, retorna erro 403 e encerra execução
            http_response_code(403);
            exit('Token CSRF inválido.');
        }
    }
}