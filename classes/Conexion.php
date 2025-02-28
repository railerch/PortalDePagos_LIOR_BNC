<?php

class Conexion
{
    protected $error    = null;
    protected $mysql    = null;
    protected $sqlsrv   = null;
    protected $message  = null;
    private static $instance = null;

    public function __construct($config)
    {
        $this->mysql    = $config[0]->mysql;
        $this->sqlsrv   = $config[0]->sqlsrv;
    }

    /**
     * Singleton
     */

    public static function get_instance($config)
    {
        if (self::$instance == null) {
            self::$instance = new Conexion($config);
            $i = 'New instance';
        } else {
            $i = 'Old instance';
        }
        $_SESSION['conn_instance'] = $i;
        return self::$instance;
    }

    /**
     * Conexion con servidor MySQL
     */
    public function conn_mysql(): mixed
    {
        $mysql  = $this->mysql;
        $dsn    = "mysql:host={$mysql->host};dbname={$mysql->dbname}";

        try {
            $dbh = new PDO($dsn, $mysql->user, $mysql->pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->message = "Conexión MySQL realizada correctamente";
            return $dbh;
        } catch (PDOException $e) {
            $this->error = "HA OCURRIDO UN ERROR: " . $e->getMessage();
        }
    }
}
