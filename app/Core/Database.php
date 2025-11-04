<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

/**
 * Classe Database
 *
 * Gerencia a conexão com o banco de dados MySQL utilizando PDO.
 * Implementa o padrão Singleton para garantir que apenas uma instância exista.
 * Suporta carregamento de variáveis de ambiente a partir de um arquivo .env.
 *
 * @package App\Core
 */
final class Database
{
    /**
     * Instância única de PDO (Singleton)
     *
     * @var PDO|null
     */
    private static ?PDO $pdo = null;

    /**
     * Caminho do arquivo .env
     */
    private const ENV_PATH = __DIR__ . '/../../.env';

    /**
     * Impede instanciação externa (Singleton)
     */
    private function __construct() {}
    private function __clone(): void {}

    /**
     * Impede que a instância seja recriada via unserialize().
     *
     * @throws \RuntimeException
     */
    public function __wakeup(): void
    {
        throw new \RuntimeException('A desserialização da classe Database não é permitida.');
    }

    /**
     * Retorna a instância de conexão PDO.
     *
     * @return PDO
     * @throws RuntimeException Caso falhe a conexão com o banco.
     */
    public static function getConnection(): PDO
    {
        // Retorna a instância existente se já estiver criada
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        // Carrega variáveis do arquivo .env, se existir
        if (is_readable(self::ENV_PATH)) {
            self::loadEnv(self::ENV_PATH);
        }

        // Variáveis obrigatórias
        $requiredVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
        foreach ($requiredVars as $var) {
            if (!getenv($var)) {
                throw new RuntimeException("Variável de ambiente {$var} não definida no .env");
            }
        }

        // Monta as configurações de conexão
        $host    = getenv('DB_HOST');
        $port    = getenv('DB_PORT') ?: '3306';
        $name    = getenv('DB_NAME');
        $user    = getenv('DB_USER');
        $pass    = getenv('DB_PASS');
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        // DSN seguro
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Exceções em caso de erro
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Arrays associativos
                    PDO::ATTR_PERSISTENT         => false,                  // Desabilita conexões persistentes (segurança)
                    PDO::ATTR_EMULATE_PREPARES   => false,                  // Usa prepared statements reais
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
                ]
            );
        } catch (PDOException $e) {
            // Loga erro seguro sem expor credenciais
            error_log('[DATABASE ERROR] ' . $e->getMessage());
            throw new RuntimeException('Falha ao conectar ao banco de dados MySQL.');
        }

        return self::$pdo;
    }

    /**
     * Carrega variáveis de ambiente a partir de um arquivo .env.
     *
     * @param string $file Caminho completo do arquivo .env
     * @return void
     */
    private static function loadEnv(string $file): void
    {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignora comentários e linhas vazias
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Divide chave e valor
            [$key, $value] = array_pad(explode('=', $line, 2), 2, null);
            $key = trim($key);
            $value = trim((string)$value);

            if ($key === '' || $value === '') {
                continue;
            }

            // Remove aspas se existirem
            $value = trim($value, "\"'");

            // Define variável apenas se ainda não existir
            if (getenv($key) === false) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}
