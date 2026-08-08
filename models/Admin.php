<?php
// models/Admin.php
class Admin {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function getPagosPendientes() {
        $query = "SELECT p.id, p.plan, p.comprobante_ref, p.fecha_solicitud, u.nombre 
                  FROM payments p 
                  INNER JOIN users u ON p.user_id = u.id 
                  WHERE p.estado = 'pendiente' 
                  ORDER BY p.fecha_solicitud ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function aprobarPago($id_pago) {
        try {
            $this->conn->beginTransaction();

            // 1. Obtener datos del pago
            $query = "SELECT user_id, plan FROM payments WHERE id = ? AND estado = 'pendiente'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_pago]);
            $pago = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pago) {
                $this->conn->rollBack();
                return false;
            }

            // 2. Calcular meses a sumar
            $meses = ['mensual' => 1, 'semestral' => 6, 'anual' => 12];
            $meses_agregar = $meses[$pago['plan']];
            $user_id = $pago['user_id'];
            
            // 3. Actualizar tiempo Premium del usuario
            $queryUpdateUser = "UPDATE users SET premium_hasta = 
                                CASE 
                                    WHEN premium_hasta > NOW() THEN DATE_ADD(premium_hasta, INTERVAL ? MONTH)
                                    ELSE DATE_ADD(NOW(), INTERVAL ? MONTH)
                                END 
                                WHERE id = ?";
            $stmtUser = $this->conn->prepare($queryUpdateUser);
            $stmtUser->execute([$meses_agregar, $meses_agregar, $user_id]);

            // 4. Cambiar estado del pago
            $queryUpdatePago = "UPDATE payments SET estado = 'aprobado' WHERE id = ?";
            $stmtPago = $this->conn->prepare($queryUpdatePago);
            $stmtPago->execute([$id_pago]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>