<?php
// models/Setting.php
class Setting {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    // Traer todas las configuraciones en un formato fácil de leer
    public function getAll() {
        $query = "SELECT clave, valor FROM configuracion";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $config = [];
        foreach($resultados as $row) {
            $config[$row['clave']] = $row['valor'];
        }
        return $config;
    }

    // Actualizar un valor específico
    public function update($clave, $valor) {
        $query = "UPDATE configuracion SET valor = ? WHERE clave = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$valor, $clave]);
    }
}
?>