<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOStatement;

/**
 * Modelo base. Usa PDO con prepared statements siempre.
 */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Ejecuta una consulta preparada con parámetros nombrados o posicionales.
     *
     * @param string $sql
     * @param array<string, mixed> $params
     * @return PDOStatement
     */
    protected function execute(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Ejecuta y devuelve todas las filas.
     *
     * @param string $sql
     * @param array<string, mixed> $params
     * @return array<int, array<string, mixed>>
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result !== false ? $result : [];
    }

    /**
     * Ejecuta y devuelve una sola fila o null.
     *
     * @param string $sql
     * @param array<string, mixed> $params
     * @return array<string, mixed>|null
     */
    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->execute($sql, $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /**
     * Ejecuta INSERT/UPDATE/DELETE y devuelve número de filas afectadas.
     */
    protected function executeStatement(string $sql, array $params = []): int
    {
        $stmt = $this->execute($sql, $params);
        $count = $stmt->rowCount();
        return $count !== false ? $count : 0;
    }

    /**
     * Devuelve el último ID insertado.
     */
    protected function lastInsertId(): string
    {
        return (string) $this->db->lastInsertId();
    }
}
