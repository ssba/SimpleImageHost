<?php


namespace Core;

class Storage
{
    static private $instance = null;

    private $connection = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();

            if(!file_exists(DB_PATH)){
                fopen(DB_PATH, "w");
                self::$instance->connection = new \PDO('sqlite:'.DB_PATH);
                self::$instance->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                self::$instance->connection->exec("CREATE TABLE IF NOT EXISTS upload (
                                                id TEXT PRIMARY KEY, 
                                                path TEXT, 
                                                till_to INTEGER)");
            }
            self::$instance->connection = new \PDO('sqlite:'.DB_PATH);
            self::$instance->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        }
        return self::$instance;
    }

    private function __clone()
    {
    }

    private function __construct()
    {
    }


    public static function record(string $id, string $path, string $time) : bool
    {
        return Storage::getInstance()->_record($id, $path, $time);
    }

    public static function get(string $id) : array
    {
        return Storage::getInstance()->_get($id);
    }

    public static function unlink(string $id) : int
    {
        return Storage::getInstance()->_unlink($id);
    }

    public static function obsolete(string $time, bool $clear = true) : array
    {
        return Storage::getInstance()->_obsolete($time, $clear);
    }

    private function _record(string $id, string $path, string $time) : bool {
        $statement = $this->connection->prepare("INSERT INTO upload(id, path, till_to)  VALUES(?, ?, ?)");
        return $statement->execute([$id, $path, $time]);
    }

    private function _get(string $id) : array {
        $statement = $this->connection->query("SELECT * FROM upload WHERE id='".$id."' LIMIT 1", \PDO::FETCH_ASSOC);
        $data = $statement->fetch();
        if(!$data)
            throw new \Exception("Wrong ID", 404);
        return $data;
    }

    private function _unlink(string $id) : int {
        return $this->connection->exec("DELETE FROM upload WHERE id='".$id."'");
    }

    private function _obsolete(string $time, bool $clear) : array {
        $statement = $this->connection->query("SELECT * FROM upload WHERE till_to < '".$time."'", \PDO::FETCH_ASSOC);
        $data = $statement->fetchAll();

        if($clear)
            $this->connection->exec("DELETE FROM upload WHERE till_to < '".$time."'");

        return $data;
    }

}
