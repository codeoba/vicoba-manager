<?php
/**
 * VICOBA Database — PDO Wrapper
 * Replaces WordPress $wpdb with clean, fast PDO queries
 */

class Database
{
    private static ?PDO $pdo = null;
    private static string $prefix = '';

    public static function connect(): PDO
    {
        if (self::$pdo !== null) return self::$pdo;

        $cfg = config('db');
        self::$prefix = $cfg['prefix'];

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'], $cfg['port'], $cfg['name'], $cfg['charset']
        );

        self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ]);

        return self::$pdo;
    }

    /** Run any SQL query with bound parameters */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row as object (or null) */
    public static function get(string $sql, array $params = []): ?object
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    /** Fetch all rows as array of objects */
    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch a single scalar value */
    public static function scalar(string $sql, array $params = []): mixed
    {
        return self::query($sql, $params)->fetchColumn();
    }

    /** INSERT a row, returns last insert ID */
    public static function insert(string $table, array $data): int
    {
        $table = self::table($table);
        $cols  = implode(', ', array_keys($data));
        $holds = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO $table ($cols) VALUES ($holds)", array_values($data));
        return (int) self::connect()->lastInsertId();
    }

    /** UPDATE rows, returns affected row count */
    public static function update(string $table, array $data, array $where): int
    {
        $table   = self::table($table);
        $set     = implode(' = ?, ', array_keys($data)) . ' = ?';
        $cond    = implode(' = ? AND ', array_keys($where)) . ' = ?';
        $stmt    = self::query(
            "UPDATE $table SET $set WHERE $cond",
            [...array_values($data), ...array_values($where)]
        );
        return $stmt->rowCount();
    }

    /** DELETE rows, returns affected row count */
    public static function delete(string $table, array $where): int
    {
        $table = self::table($table);
        $cond  = implode(' = ? AND ', array_keys($where)) . ' = ?';
        return self::query("DELETE FROM $table WHERE $cond", array_values($where))->rowCount();
    }

    /** Get fully-qualified table name with prefix */
    public static function table(string $name): string
    {
        // If already prefixed, don't double-prefix
        if (str_starts_with($name, self::$prefix)) return $name;
        return self::$prefix . $name;
    }

    /** Alias for table() — shorter */
    public static function t(string $name): string
    {
        return self::table($name);
    }

    /** Begin a transaction */
    public static function beginTransaction(): void
    {
        self::connect()->beginTransaction();
    }

    /** Commit a transaction */
    public static function commit(): void
    {
        self::connect()->commit();
    }

    /** Rollback a transaction */
    public static function rollback(): void
    {
        self::connect()->rollBack();
    }

    /** Get the raw PDO instance (for advanced use) */
    public static function pdo(): PDO
    {
        return self::connect();
    }
}
