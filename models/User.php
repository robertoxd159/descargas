<?php
// models/User.php

class User {
    private $conn;
    private $table = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Registrar un nuevo usuario
    public function register($nombre, $email, $password) {
        $query = "INSERT INTO " . $this->table . " (nombre, email, password) VALUES (:nombre, :email, :password)";
        $stmt = $this->conn->prepare($query);

        // Limpiar datos y cifrar contraseña
        $nombre = htmlspecialchars(strip_tags($nombre));
        $email = htmlspecialchars(strip_tags($email));
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password_hashed);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verificar si el email ya existe
    public function emailExists($email) {
        $query = "SELECT id, nombre, email, password, rol, premium_hasta FROM " . $this->table . " WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function isPremium($user_id) {
        $query = "SELECT premium_hasta FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && $user['premium_hasta']) {
            return (strtotime($user['premium_hasta']) > time());
        }
        return false;
    }

    public function getUserDetails($id) {
        // Cambiamos 'correo' por 'email'
        $query = "SELECT nombre, email, rol, premium_hasta FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todos los usuarios para el panel
    public function getAllUsers() {
        $query = "SELECT id, nombre, email, rol, premium_hasta FROM users ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Dar o quitar días Premium
    public function updatePremiumStatus($id, $dias) {
        if ($dias == 0) {
            $query = "UPDATE users SET premium_hasta = NULL WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id]);
        } else {
            $fecha_vencimiento = date('Y-m-d H:i:s', strtotime("+$dias days"));
            $query = "UPDATE users SET premium_hasta = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$fecha_vencimiento, $id]);
        }
    }

    // Contar usuarios con Premium activo hoy
    public function getActivePremiumCount() {
        $query = "SELECT COUNT(*) as activos FROM users WHERE premium_hasta IS NOT NULL AND premium_hasta > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['activos'];
    }

    // Obtener el hash de la contraseña actual
    public function getPasswordHash($id) {
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetchColumn(); // Devuelve solo el string de la contraseña
    }

    // Actualizar la contraseña
    public function updatePassword($id, $nuevo_hash) {
        $query = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nuevo_hash, $id]);
    }
}
?>