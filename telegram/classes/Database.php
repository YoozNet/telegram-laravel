<?php

class Database
{
    private static $db;
    public static function connect($host,$user,$pass,$db_name)
    {
        try {
            $connect = new \PDO('mysql:host='.$host.';dbname='.$db_name,$user,$pass);
            $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$db = $connect;
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    public static function create(string $table,array $columns,array $values)
    {
        $sql = "INSERT INTO ".$table." (".implode(",",$columns).") VALUES (".implode(",",array_fill(0,count($columns),"?")).")";
        $prepare = self::$db->prepare($sql);
        if ($prepare->execute($values)) {
            return self::$db->lastInsertId();
        }
        return false;
    }
    public static function query(string $query)
    {
        return self::$db->query($query);
    }
    public static function select(string $table, array $columns, string $where = "", array $bindings = [],$limit=null,$offset=null,$order_by=null): array
    {
        $sql = "SELECT " . implode(",", $columns) . " FROM " . $table;
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        if(!is_null($order_by)) {
            $sql .= " ORDER BY $order_by DESC";
        }
        if(!is_null($limit)) {
            $sql .= " LIMIT " . $limit;
            if(!is_null($offset)) {
                $sql .= " OFFSET $offset";
            }
        }
        $prepare = self::$db->prepare($sql);
        $prepare->execute($bindings);
        return $prepare->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function update(string $table, array $columns, array $values, string $where = "", array $bindings = []): bool
    {
        $set_clause = implode(",", array_map(fn($col) => "$col = ?", $columns));
        $sql = "UPDATE $table SET $set_clause";

        if ($where) {
            $sql .= " WHERE $where";
        }

        $all_bindings = array_merge($values, $bindings);
        $prepare = self::$db->prepare($sql);
        return $prepare->execute($all_bindings);
    }
}