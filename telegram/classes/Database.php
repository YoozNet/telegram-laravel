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
    public static function create(string $table,array $columns,array $values): bool
    {
        $sql = "INSERT INTO ".$table." (".implode(",",$columns).") VALUES (".implode(",",array_fill(0,count($columns),"?")).")";
        $prepare = self::$db->prepare($sql);
        return $prepare->execute($values);
    }
    public static function query(string $query)
    {
        return self::$db->query($query);
    }
    public static function select(string $table, array $columns, string $where = "", array $bindings = [])
    {
        $sql = "SELECT " . implode(",", $columns) . " FROM " . $table;
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        $prepare = self::$db->prepare($sql);
        $prepare->execute($bindings);
        return $prepare->fetchAll(PDO::FETCH_ASSOC);
    }
}