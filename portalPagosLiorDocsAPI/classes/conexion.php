<?php

class Conexion
{
    public  $error;
    private $server;
    private $instance;
    private $user;
    private $pass;
    private $db;
    private $port;


    public function __construct(string $archivoUrl)
    {

        $archivo = file_get_contents($archivoUrl);
        $datos   = json_decode($archivo);

        $this->server   = $datos[0]->server;
        $this->instance = $datos[0]->instance;
        $this->user     = $datos[0]->user;
        $this->pass     = $datos[0]->pass;
        $this->db       = $datos[0]->db;
        $this->port     = $datos[0]->port;
    }

    public function db_conn($dbEngine)
    {

        switch ($dbEngine) {
            case 'mysql':
                $dsn = "mysql:host={$this->server};dbname={$this->db}";
                break;
            case 'sqlsrv':
                $dsn = "sqlsrv:server={$this->server};database={$this->db}";
                break;
            case 'sqlite':
                die('ERROR: SQLite no configurado.');
                break;
            case 'postgres':
                die('ERROR: PostgresSQL no configurado.');
                break;
            default:
                die("Debe especificar un tipo de base de datos valido.");
                break;
        }

        try {
            $conn = new PDO($dsn, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        } catch (PDOException $e) {
            $this->error = "ERROR INESPERADO: {$e->getMessage()}";
        }
    }

    public function __destruct()
    {
    }
};
