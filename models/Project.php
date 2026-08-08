<?php
// models/Project.php
class Project {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    // Obtenemos todas las categorías únicas que existen
    public function getCategories() {
        $query = "SELECT DISTINCT categoria FROM projects WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        // FETCH_COLUMN devuelve un arreglo simple solo con los nombres de las categorías
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Modificamos para aceptar categoría y búsqueda
    // Contar el total de proyectos (útil para saber cuántas páginas dibujar)
    public function getTotalProjectsCount($categoria = null, $busqueda = null) {
        $query = "SELECT COUNT(*) as total FROM projects WHERE 1=1";
        $params = [];

        if ($categoria) {
            $query .= " AND categoria = :categoria";
            $params['categoria'] = $categoria;
        }
        if ($busqueda) {
            $query .= " AND (titulo LIKE :busqueda OR descripcion LIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Traer proyectos con límite de paginación
    public function getAllProjects($categoria = null, $busqueda = null, $limit = 6, $offset = 0) {
        $query = "SELECT * FROM projects WHERE 1=1";
        $params = [];

        if ($categoria) {
            $query .= " AND categoria = :categoria";
            $params['categoria'] = $categoria;
        }
        if ($busqueda) {
            $query .= " AND (titulo LIKE :busqueda OR descripcion LIKE :busqueda)";
            $params['busqueda'] = '%' . $busqueda . '%';
        }
        
        // Agregamos el límite y desde dónde empezar de forma segura
        $limit = (int)$limit;
        $offset = (int)$offset;
        $query .= " ORDER BY fecha_publicacion DESC LIMIT $limit OFFSET $offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getProjectById($id) {
        $query = "SELECT * FROM projects WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
}
?>