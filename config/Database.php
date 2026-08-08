<?php
// config/Database.php

class Database {
    private $host = "sql200.infinityfree.com";
    private $db_name = "if0_42605864_premium_db";
    private $username = "if0_42605864"; // Usuario por defecto en XAMPP
    private $password = "LObJPzSnh9eAnV7";     // Sin contraseña por defecto en XAMPP
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Se utiliza charset utf8mb4 para soportar todo tipo de caracteres
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            // Configuramos PDO para que lance excepciones si hay errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>