<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * Classe base Model — inspirada no Eloquent (Laravel)
 *
 * Oferece operações CRUD genéricas com interface fluente,
 * retornando instâncias do model ao invés de stdClass.
 *
 * @package App\Core
 */
abstract class Model
{
    /** @var PDO Conexão PDO */
    protected readonly PDO $db;

    /** @var string Nome da tabela associada */
    protected string $table = '';

    /** @var string Nome da chave primária */
    protected string $primaryKey = 'id';

    /** @var array Condições acumuladas */
    protected array $wheres = [];

    /** @var string|null Ordenação */
    protected ?string $orderBy = null;

    /** @var int|null Limite de resultados */
    protected ?int $limit = null;

    /**
     * Construtor: injeta conexão PDO
     */
    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Cria uma nova instância do model (factory)
     */
    public static function make(): static
    {
        return new static();
    }

    /**
     * Define a tabela (caso precise alterar dinamicamente)
     */
    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Adiciona cláusula WHERE fluente
     */
    public function where(string $column, string $operator, mixed $value): static
    {
        $this->wheres[] = [$column, $operator, $value];
        return $this;
    }

    /**
     * Define ordenação
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy = "{$column} " . strtoupper($direction);
        return $this;
    }

    /**
     * Define limite de resultados
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Monta SQL baseado em filtros acumulados
     */
    protected function buildSelectQuery(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($this->wheres)) {
            $conditions = [];
            foreach ($this->wheres as $i => [$col, $op, $val]) {
                $param = ":w{$i}";
                $conditions[] = "{$col} {$op} {$param}";
                $params[$param] = $val;
            }
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        return [$sql, $params];
    }

    /**
     * Executa query preparada com tratamento seguro
     */
    protected function execute(string $sql, array $params = []): ?PDOStatement
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("[DB ERROR] {$e->getMessage()} | SQL: {$sql}");
            return null;
        }
    }

    /**
     * Retorna todos resultados como objetos do model
     */
    public function get(): array
    {
        [$sql, $params] = $this->buildSelectQuery();
        $stmt = $this->execute($sql, $params);
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return array_map(fn($row) => $this->hydrate($row), $rows);
    }

    /**
     * Retorna apenas o primeiro resultado
     */
    public function first(): ?static
    {
        $this->limit(1);
        [$sql, $params] = $this->buildSelectQuery();
        $stmt = $this->execute($sql, $params);
        $row = $stmt?->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Busca por ID
     */
    public function find(int $id): ?static
    {
        $stmt = $this->execute(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1",
            ['id' => $id]
        );
        $row = $stmt?->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Cria novo registro e retorna instância criada
     */
    public function create(array $data): ?static
    {
        $fields = array_keys($data);
        $cols = implode(', ', $fields);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO {$this->table} ({$cols}) VALUES ({$placeholders})";

        $stmt = $this->execute($sql, $data);

        if (!$stmt) return null;

        $id = (int)$this->db->lastInsertId();
        return $this->find($id);
    }

    /**
     * Atualiza registro(s) conforme where
     */
    public function update(array $data): bool
    {
        if (empty($this->wheres)) {
            throw new RuntimeException('É necessário definir uma condição WHERE antes de atualizar.');
        }

        $fields = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
        [$whereSql, $params] = $this->buildSelectQuery();
        $whereSql = preg_replace('/^SELECT \* FROM [^ ]+/', "UPDATE {$this->table} SET {$fields}", $whereSql);

        return (bool)$this->execute($whereSql, array_merge($data, $params));
    }

    /**
     * Exclui registro(s) conforme condição atual
     */
    public function delete(): bool
    {
        if (empty($this->wheres)) {
            throw new RuntimeException('É necessário definir uma condição WHERE antes de excluir.');
        }

        [$sql, $params] = $this->buildSelectQuery();
        $sql = preg_replace('/^SELECT \* FROM [^ ]+/', "DELETE FROM {$this->table}", $sql);

        return (bool)$this->execute($sql, $params);
    }

    /**
     * Exclui registro diretamente por ID
     */
    public function deleteById(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return (bool)$this->execute($sql, ['id' => $id]);
    }

    /**
     * Cria ou atualiza registro existente
     */
    public function updateOrCreate(array $conditions, array $data): static
    {
        $existing = (clone $this)->where(
            key($conditions),
            '=',
            current($conditions)
        )->first();

        if ($existing) {
            $this->where($this->primaryKey, '=', $existing->{$this->primaryKey})
                ->update($data);
            return $this->find($existing->{$this->primaryKey});
        }

        return $this->create(array_merge($conditions, $data));
    }

    /**
     * Converte array em instância do model atual
     */
    protected function hydrate(array $data): static
    {
        $instance = new static();
        foreach ($data as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->{$key} = $value;
            }
        }
        return $instance;
    }

    /**
     * Retorna todos registros da tabela
     */
    public function all(): array
    {
        return $this->orderBy($this->primaryKey, 'DESC')->get();
    }
}
