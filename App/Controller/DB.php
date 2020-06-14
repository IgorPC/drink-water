<?php


namespace App\Controller;


class DB
{

    private $db;

    public function __construct()
    {
        $this->db = $this->getDB();
    }

    public function getDB()
    {
        return mysqli_connect('localhost', 'root', '', 'processo_seletivo', '3308');
    }

    public function select($condition, $table, $where, $value)
    {
        $query = "SELECT {$condition} FROM {$table} WHERE {$where} = '{$value}'";
        $data = mysqli_query($this->db, $query) or die('SELECT DATABASE ERROR');
        $result = mysqli_fetch_assoc($data);
        return $result;
    }

    public function insert($table, $values)
    {
        $query = "INSERT INTO {$table} VALUES ({$values})";
        mysqli_query($this->db, $query) or die('INSERT DATABASE ERROR');
    }

    public function update($table,$column, $value, $where, $condition)
    {
        $query = "UPDATE {$table} SET {$column} = '{$value}' WHERE {$where} = '{$condition}'";
        mysqli_query($this->db, $query) or die('UPDATE DATABASE ERROR');
    }

    public function delete($table, $column, $value)
    {
        $query = "DELETE FROM {$table} WHERE {$column} = {$value}";
        mysqli_query($this->db, $query) or die('DELETE DATABASE ERROR');
    }

    public function raw($query)
    {
        $data = mysqli_query($this->db, $query) or die('RAW REQUEST DATABASE ERROR');
        $result = mysqli_fetch_assoc($data);
        return $result;
    }
}