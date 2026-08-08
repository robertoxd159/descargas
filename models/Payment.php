<?php
// models/Payment.php
class Payment {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    // Traer todos los pagos pendientes con los datos del usuario
    public function getPendingPayments() {
        $query = "SELECT p.*, u.nombre, u.email 
                  FROM pagos_yape p 
                  JOIN users u ON p.user_id = u.id 
                  WHERE p.estado = 'pendiente' 
                  ORDER BY p.fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPaymentById($id) {
        $query = "SELECT * FROM pagos_yape WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePaymentStatus($id, $estado) {
        $query = "UPDATE pagos_yape SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // Actualizamos para recibir el plan
    public function registrarPago($user_id, $numero_referencia, $plan) {
        $query = "INSERT INTO pagos_yape (user_id, numero_referencia, plan, estado) VALUES (?, ?, ?, 'pendiente')";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$user_id, $numero_referencia, $plan]);
    }

    // Obtener estadísticas financieras
    public function getDashboardStats() {
        $query = "SELECT 
                    COUNT(*) as total_ventas,
                    SUM(CASE 
                        WHEN plan = 'mensual' THEN 15 
                        WHEN plan = 'semestral' THEN 75 
                        WHEN plan = 'anual' THEN 120 
                        ELSE 0 
                    END) as ganancias_totales
                  FROM pagos_yape 
                  WHERE estado = 'aprobado'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener el historial de pagos de un usuario específico
    public function getPaymentsByUserId($user_id) {
        $query = "SELECT * FROM pagos_yape WHERE user_id = ? ORDER BY fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>