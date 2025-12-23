<?php
// Clase para la conexión a la base de datos usando PDO
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database Handler
    private $stmt; // Statement
    private $error;

    public function __construct(){
        // Configurar DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname. ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];

        // Crear una nueva instancia de PDO
        try{
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e){
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    // Prepara la sentencia
    public function query($sql){
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincula los valores
    public function bind($param, $value, $type = null){
        if(is_null($type)){
            switch(true){
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Ejecuta la sentencia preparada
    public function execute(){
        return $this->stmt->execute();
    }

    // Obtiene el conjunto de resultados como un array de objetos
    public function resultSet(){
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Obtiene un solo registro como objeto
    public function single(){
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Obtiene el número de filas
    public function rowCount(){
        return $this->stmt->rowCount();
    }
}
?>
