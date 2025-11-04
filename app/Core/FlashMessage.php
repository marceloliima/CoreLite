<?php

namespace App\Core;

/**
 * ===============================================================
 * 💬 Classe FlashMessage
 * ===============================================================
 *
 * Classe utilitária para gerenciar mensagens temporárias ("flash messages").
 * Armazena mensagens na sessão PHP e exibe usando classes CSS genéricas.
 *
 * 🔹 Tipos suportados: sucesso, erro, aviso, info
 * 🔹 Remove automaticamente mensagens após exibição
 * ===============================================================
 */
class FlashMessage
{
    // Tipos de mensagem
    public const SUCESSO = 'sucesso';
    public const ERRO    = 'erro';
    public const AVISO   = 'aviso';
    public const INFO    = 'info';

    /**
     * Garante que a sessão PHP esteja ativa
     */
    private static function garantirSessao(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Define uma nova mensagem flash
     *
     * @param string $tipo Tipo da mensagem (sucesso, erro, aviso, info)
     * @param string $mensagem Conteúdo da mensagem
     */
    public static function definir(string $tipo, string $mensagem): void
    {
        self::garantirSessao();

        $mapa = [
            'sucesso' => self::SUCESSO,
            'erro'    => self::ERRO,
            'aviso'   => self::AVISO,
            'info'    => self::INFO
        ];

        $tipo = strtolower($tipo);
        $tipo = $mapa[$tipo] ?? self::INFO;

        $_SESSION['flash'][] = [
            'tipo'     => $tipo,
            'mensagem' => $mensagem
        ];
    }

    /**
     * Retorna todas as mensagens flash e remove da sessão
     *
     * @return array
     */
    public static function obter(): array
    {
        self::garantirSessao();
        $mensagens = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']); // remove após exibição
        return $mensagens;
    }

    /**
     * Exibe todas as mensagens flash usando classes CSS genéricas
     *
     * @param bool $autoFechar Se true, a mensagem desaparece automaticamente
     */
    public static function exibir(bool $autoFechar = false): void
    {
        $mensagens = self::obter();
        if (empty($mensagens)) return;

        foreach ($mensagens as $msg) {
            $tipo  = htmlspecialchars($msg['tipo'], ENT_QUOTES, 'UTF-8');
            $texto = htmlspecialchars($msg['mensagem'], ENT_QUOTES, 'UTF-8');
            $classeAuto = $autoFechar ? ' flash-auto-fechar' : '';

            echo <<<HTML
            <div class="flash-message flash-{$tipo}{$classeAuto}">
                {$texto}
            </div>
            HTML;
        }

        // Script para auto-fechar
        if ($autoFechar) {
            echo <<<HTML
            <script>
            document.addEventListener('DOMContentLoaded', () => {
                const mensagens = document.querySelectorAll('.flash-auto-fechar');
                mensagens.forEach(msg => {
                    setTimeout(() => {
                        msg.style.display = 'none';
                    }, 5000); // 5 segundos
                });
            });
            </script>
            HTML;
        }
    }

    /**
     * Remove todas as mensagens flash da sessão sem exibir
     */
    public static function limpar(): void
    {
        self::garantirSessao();
        unset($_SESSION['flash']);
    }
}