<?php
// models/User.php

class User {
    private $api_url = "https://rzc-telegram-bot.onrender.com/api/usuarios";

    public function __construct($db = null) {
        // Ya no necesitamos la conexión PDO local, pero mantenemos el parámetro por compatibilidad
    }

    // Método auxiliar para hacer peticiones POST a la API de Render
    private function peticionAPI($data) {
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => 10,
            ]
        ];
        $context  = stream_context_create($options);
        $result = @file_get_contents($this->api_url, false, $context);
        
        if ($result === FALSE) {
            return ['success' => false, 'error' => 'No se pudo conectar con el servidor de autenticación'];
        }
        
        return json_decode($result, true);
    }

    // Registrar un nuevo usuario
    public function register($nombre, $email, $password) {
        $nombre = htmlspecialchars(strip_tags($nombre));
        $email = htmlspecialchars(strip_tags($email));
        // Ciframos la contraseña antes de mandarla
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $response = $this->peticionAPI([
            'accion' => 'register',
            'nombre' => $nombre,
            'email' => $email,
            'password' => $password_hashed
        ]);

        return isset($response['success']) && $response['success'];
    }

    // Verificar si el email ya existe / Autenticar login
    public function emailExists($email) {
        $response = $this->peticionAPI([
            'accion' => 'login_check', // O puedes usar tu lógica de login
            'email' => $email
        ]);
        
        // Si la API devuelve los datos del usuario, los retornamos igual que PDO
        if (isset($response['user']) && $response['user']) {
            return $response['user'];
        }
        return false;
    }

    // Validar login por email y contraseña (añadido para soportar tu flujo actual)
    public function login($email, $password) {
        $response = $this->peticionAPI([
            'accion' => 'login',
            'email' => $email,
            'password' => $password // Nota: Validaremos el hash en Python o aquí
        ]);

        if (isset($response['success']) && $response['success'] && isset($response['user'])) {
            return $response['user'];
        }
        return false;
    }

    public function isPremium($user_id) {
        $response = $this->peticionAPI([
            'accion' => 'get_user',
            'id' => $user_id
        ]);
        
        if (isset($response['user']) && $response['user'] && $response['user']['premium_hasta']) {
            return (strtotime($response['user']['premium_hasta']) > time());
        }
        return false;
    }

    public function getUserDetails($id) {
        $response = $this->peticionAPI([
            'accion' => 'get_user',
            'id' => $id
        ]);
        return $response['user'] ?? null;
    }

    // Obtener todos los usuarios para el panel
    public function getAllUsers() {
        $response = $this->peticionAPI([
            'accion' => 'get_all_users'
        ]);
        return $response['users'] ?? [];
    }

    // Dar o quitar días Premium
    public function updatePremiumStatus($id, $dias) {
        $response = $this->peticionAPI([
            'accion' => 'update_premium',
            'id' => $id,
            'dias' => $dias
        ]);
        return isset($response['success']) && $response['success'];
    }

    // Contar usuarios con Premium activo hoy
    public function getActivePremiumCount() {
        $usuarios = $this->getAllUsers();
        $activos = 0;
        foreach ($usuarios as $u) {
            if (!empty($u['premium_hasta']) && strtotime($u['premium_hasta']) > time()) {
                $activos++;
            }
        }
        return $activos;
    }

    // Obtener el hash de la contraseña actual
    public function getPasswordHash($id) {
        $user = $this->getUserDetails($id);
        return $user['password'] ?? '';
    }

    // Actualizar la contraseña
    public function updatePassword($id, $nuevo_hash) {
        $response = $this->peticionAPI([
            'accion' => 'update_password',
            'id' => $id,
            'password' => $nuevo_hash
        ]);
        return isset($response['success']) && $response['success'];
    }
}
?>