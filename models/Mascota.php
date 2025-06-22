<?php
require_once __DIR__ . '/../core/Model.php';

class Mascota extends Model {
    protected $table = 'mascotas';

    public function __construct() {
        parent::__construct();
    }

    public function getMascotasByUser($usuario_id) {
        return $this->findAll(['usuario_id' => $usuario_id]);
    }

    public function createMascota($data) {
        // Solo permitir los campos válidos
        $allowed = ['nombre', 'especie', 'tamano', 'fecha_nacimiento', 'usuario_id', 'estado', 'genero'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->create($filtered);
    }

    public function updateMascota($id, $data) {
        $allowed = ['nombre', 'especie', 'tamano', 'fecha_nacimiento', 'usuario_id', 'estado', 'genero'];
        $filtered = array_intersect_key($data, array_flip($allowed));
        return $this->update($id, $filtered, 'id_mascota');
    }

    public function deleteMascota($id) {
        // Primero, desasociar dispositivos
        $dispositivoModel = new DispositivoModel();
        $dispositivoModel->desasociarDeMascota($id);
        
        // Luego, eliminar la mascota
        return $this->delete($id, 'id_mascota');
    }

    public function getEstadisticas($usuario_id) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT especie) as especies,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id";
        $result = $this->query($sql, [':usuario_id' => $usuario_id]);
        return $result ? $result[0] : [
            'total' => 0,
            'especies' => 0,
            'edad_promedio' => 0
        ];
    }

    public function getMascotasByEspecie($usuario_id, $especie) {
        return $this->findAll([
            'usuario_id' => $usuario_id,
            'especie' => $especie
        ]);
    }

    public function getMascotasByRaza($usuario_id, $raza) {
        return $this->findAll([
            'usuario_id' => $usuario_id,
            'raza' => $raza
        ]);
    }

    public function getMascotasByEdad($usuario_id, $edad_minima, $edad_maxima) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN :edad_minima AND :edad_maxima";
        return $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':edad_minima' => $edad_minima,
            ':edad_maxima' => $edad_maxima
        ]);
    }

    public function getMascotasConDispositivos($usuario_id) {
        $sql = "SELECT m.*, COUNT(d.id_dispositivo) as total_dispositivos 
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id_mascota = d.mascota_id 
                WHERE m.usuario_id = :usuario_id 
                GROUP BY m.id_mascota";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasSinDispositivos($usuario_id) {
        $sql = "SELECT m.* 
                FROM {$this->table} m 
                WHERE m.usuario_id = :usuario_id
                AND NOT EXISTS (
                    SELECT 1 FROM dispositivos d WHERE d.mascota_id = m.id_mascota
                )";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasPorVeterinario($veterinario_id) {
        $sql = "SELECT m.*, u.nombre as dueno_nombre 
                FROM {$this->table} m 
                JOIN usuarios u ON m.usuario_id = u.id_usuario 
                GROUP BY m.id_mascota";
        return $this->query($sql, [':veterinario_id' => $veterinario_id]);
    }

    public function getEstadisticasAvanzadas($usuario_id) {
        $sql = "SELECT 
                    COUNT(*) as total_mascotas,
                    COUNT(DISTINCT especie) as total_especies,
                    COUNT(DISTINCT raza) as total_razas,
                    AVG(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_promedio,
                    MAX(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_maxima,
                    MIN(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE())) as edad_minima,
                    COUNT(DISTINCT d.id_dispositivo) as total_dispositivos,
                    -- COUNT(DISTINCT hm.id) as total_registros_medicos
                    0 as total_registros_medicos
                FROM {$this->table} m
                LEFT JOIN dispositivos d ON d.mascota_id = m.id_mascota
                -- LEFT JOIN historial_medico hm ON hm.mascota_id = m.id_mascota
                WHERE m.usuario_id = :usuario_id";
        
        $estadisticas = $this->query($sql, [':usuario_id' => $usuario_id])[0];
        
        // Agregar distribución por edad
        $estadisticas['edad_0_1'] = $this->getMascotasPorRangoEdad($usuario_id, 0, 1);
        $estadisticas['edad_1_3'] = $this->getMascotasPorRangoEdad($usuario_id, 1, 3);
        $estadisticas['edad_3_5'] = $this->getMascotasPorRangoEdad($usuario_id, 3, 5);
        
        return $estadisticas;
    }

    private function getMascotasPorRangoEdad($usuario_id, $edad_min, $edad_max) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                AND TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) 
                BETWEEN :edad_min AND :edad_max";
        $result = $this->query($sql, [
            ':usuario_id' => $usuario_id,
            ':edad_min' => $edad_min,
            ':edad_max' => $edad_max
        ]);
        return $result[0]['total'];
    }

    private function getDistribucionPorEspecie($usuario_id) {
        $sql = "SELECT especie, COUNT(*) as total 
                FROM {$this->table} 
                WHERE usuario_id = :usuario_id 
                GROUP BY especie 
                ORDER BY total DESC";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function getMascotasPorEstado($usuario_id) {
        $sql = "SELECT 
                    m.*,
                    CASE 
                        WHEN COUNT(d.id_dispositivo) = 0 THEN 'sin_dispositivo'
                        -- WHEN COUNT(a.id) > 0 THEN 'con_alerta'
                        ELSE 'normal'
                    END as estado
                FROM {$this->table} m 
                LEFT JOIN dispositivos d ON m.id_mascota = d.mascota_id 
                -- LEFT JOIN alertas a ON d.id_dispositivo = a.dispositivo_id AND a.leida = 0
                WHERE m.usuario_id = :usuario_id
                GROUP BY m.id_mascota";
        return $this->query($sql, [':usuario_id' => $usuario_id]);
    }

    public function findById($id) {
        return $this->find($id, 'id_mascota');
    }

    public function buscarMascotasPorTermino($termino, $userId, $soloPropias = false) {
        $sql = "SELECT m.*, u.nombre as propietario_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                WHERE (
                    LOWER(m.nombre) LIKE :t1
                    OR LOWER(m.especie) LIKE :t2
                    OR LOWER(u.nombre) LIKE :t3
                    OR LOWER(m.estado) LIKE :t4
                )";
        $params = [
            ':t1' => '%' . strtolower($termino) . '%',
            ':t2' => '%' . strtolower($termino) . '%',
            ':t3' => '%' . strtolower($termino) . '%',
            ':t4' => '%' . strtolower($termino) . '%'
        ];
        if ($soloPropias) {
            $sql .= " AND m.usuario_id = :uid";
            $params[':uid'] = $userId;
        }
        $sql .= " ORDER BY m.nombre ASC";
        // Log temporal para depuración
        error_log('SQL Mascotas: ' . $sql);
        error_log('PARAMS Mascotas: ' . json_encode($params));
        return $this->query($sql, $params);
    }

    public function findAll($conditions = [], $orderBy = '') {
        $sql = "SELECT m.*, u.nombre as propietario_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario";
        $params = [];
        if (!empty($conditions)) {
            $sql .= " WHERE ";
            $condiciones = [];
            foreach ($conditions as $campo => $valor) {
                $condiciones[] = "m.$campo = :$campo";
                $params[":$campo"] = $valor;
            }
            $sql .= implode(' AND ', $condiciones);
        }
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        } else {
            $sql .= " ORDER BY m.nombre ASC";
        }
        return $this->query($sql, $params);
    }

    public function getMascotasPorDias($dias) {
        $placeholders = str_repeat('?,', count($dias) - 1) . '?';
        $sql = "SELECT DATE(creado_en) as fecha, COUNT(*) as total 
                FROM mascotas 
                WHERE DATE(creado_en) IN ($placeholders)
                GROUP BY DATE(creado_en)
                ORDER BY fecha";
        return $this->db->query($sql, $dias);
    }

    public function getDistribucionEspecies() {
        $query = "SELECT 
            especie,
            COUNT(*) as total,
            ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM mascotas), 1) as porcentaje
            FROM mascotas 
            GROUP BY especie
            ORDER BY total DESC";
        $result = $this->query($query);
        $distribucion = [];
        foreach ($result as $row) {
            $distribucion[] = [
                'especie' => $row['especie'],
                'total' => (int)$row['total'],
                'porcentaje' => (float)$row['porcentaje']
            ];
        }
        return $distribucion;
    }

    public function getDistribucionEspeciesPorDias($dias) {
        $placeholders = str_repeat('?,', count($dias) - 1) . '?';
        $sql = "SELECT especie, COUNT(*) as total FROM mascotas WHERE DATE(creado_en) IN ($placeholders) GROUP BY especie ORDER BY total DESC";
        return $this->db->query($sql, $dias);
    }

    public function getTotalRegistradas() {
        $query = "SELECT COUNT(*) as total FROM mascotas";
        $result = $this->query($query);
        return $result && isset($result[0]['total']) ? (int)$result[0]['total'] : 0;
    }

    /**
     * Obtiene todas las mascotas junto con el id_dispositivo asociado (si existe en la tabla mascotas)
     */
    public function getMascotasConDispositivo() {
        $sql = "SELECT m.*, u.nombre as propietario_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                ORDER BY m.nombre ASC";
        return $this->query($sql);
    }

    /**
     * Obtiene mascotas filtradas y paginadas para DataTables.
     * @param array $params Parámetros de DataTables (draw, start, length, search, order, columns)
     * @param int|null $usuario_id ID del usuario para filtrar por sus propias mascotas
     * @param bool $verTodas Si se tienen permisos para ver todas las mascotas (admin)
     * @return array
     */
    public function getMascotasFiltradas(array $params, ?int $usuario_id = null, bool $verTodas = false): array {
        $draw = $params['draw'] ?? 0;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $searchValue = $params['search']['value'] ?? '';
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = $params['order'][0]['dir'] ?? 'asc';
        $columns = $params['columns'] ?? [];

        // Nombres de columna válidos para ordenar (debe coincidir con las columnas de tu tabla y DataTables)
        $columnMap = [
            0 => 'm.id_mascota',
            1 => 'm.nombre',
            2 => 'm.especie',
            3 => 'm.tamano',
            4 => 'm.genero',
            5 => 'u.nombre', // propietario_nombre
            6 => 'm.fecha_nacimiento', // para calcular edad
            7 => 'm.estado',
            // 8 es Acciones, no es una columna de DB
        ];

        $orderColumn = $columnMap[$orderColumnIndex] ?? 'm.id_mascota';

        // Consulta base para obtener las mascotas
        $sql = "SELECT m.*, u.nombre as propietario_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario";
        $countSql = "SELECT COUNT(m.id_mascota) FROM {$this->table} m LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario";

        $whereClauses = [];
        $queryParams = [];

        // Filtro por usuario si no es admin o no tiene permiso de ver todas
        if (!$verTodas && $usuario_id !== null) {
            $whereClauses[] = "m.usuario_id = :usuario_id";
            $queryParams[':usuario_id'] = $usuario_id;
        }

        // Búsqueda global (si hay un término de búsqueda)
        if (!empty($searchValue)) {
            $whereClauses[] = "(LOWER(m.nombre) LIKE :search1 OR 
                                LOWER(m.especie) LIKE :search2 OR 
                                LOWER(m.tamano) LIKE :search3 OR
                                LOWER(m.genero) LIKE :search4 OR
                                LOWER(u.nombre) LIKE :search5 OR
                                LOWER(m.estado) LIKE :search6)";
            $queryParams[':search1'] = '%' . strtolower($searchValue) . '%';
            $queryParams[':search2'] = '%' . strtolower($searchValue) . '%';
            $queryParams[':search3'] = '%' . strtolower($searchValue) . '%';
            $queryParams[':search4'] = '%' . strtolower($searchValue) . '%';
            $queryParams[':search5'] = '%' . strtolower($searchValue) . '%';
            $queryParams[':search6'] = '%' . strtolower($searchValue) . '%';
        }

        // Aplicar WHERE si hay cláusulas
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(" AND ", $whereClauses);
            $countSql .= " WHERE " . implode(" AND ", $whereClauses);
        }

        // Obtener el total de registros filtrados (después de aplicar filtros de búsqueda)
        $totalFilteredResult = $this->query($countSql, $queryParams);
        $totalFiltered = $totalFilteredResult[0]['COUNT(m.id_mascota)'] ?? 0;

        // Añadir ordenamiento y paginación
        $sql .= " ORDER BY {$orderColumn} {$orderDir} LIMIT :limit OFFSET :start";

        // Ejecutar la consulta con bindValue para LIMIT y OFFSET
        $stmt = $this->db->getConnection()->prepare($sql);
        foreach ($queryParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', (int)$length, PDO::PARAM_INT);
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->execute();
        $mascotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener el total de registros sin filtrar (para recordsTotal de DataTables)
        $totalRecordsResult = $this->query("SELECT COUNT(*) FROM {$this->table}");
        $totalRecords = $totalRecordsResult[0]['COUNT(*)'] ?? 0;

        return [
            'draw' => intval($draw),
            'recordsTotal' => intval($totalRecords),
            'recordsFiltered' => intval($totalFiltered),
            'data' => $mascotas
        ];
    }

    public function getPaginatedMascotas($params, $propietarioId = null)
    {
        $searchValue = $params['search']['value'] ?? '';
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderColumnName = $params['columns'][$orderColumnIndex]['data'] ?? 'm.id_mascota';
        $orderDir = $params['order'][0]['dir'] ?? 'asc';

        $columnMap = [
            'id' => 'm.id_mascota',
            'nombre' => 'm.nombre',
            'especie' => 'm.especie',
            'tamano' => 'm.tamano',
            'genero' => 'm.genero',
            'propietario' => 'u.nombre'
        ];
        $orderColumn = $columnMap[$orderColumnName] ?? 'm.id_mascota';

        $baseQuery = "FROM mascotas m
                      LEFT JOIN usuarios u ON m.usuario_id = u.id_usuario
                      LEFT JOIN dispositivos d ON m.id_mascota = d.mascota_id";

        $whereClauses = [];
        $queryParams = [];

        if ($propietarioId !== null) {
            $whereClauses[] = "m.usuario_id = :propietarioId";
            $queryParams[':propietarioId'] = $propietarioId;
        }

        if (!empty($searchValue)) {
            $searchFields = ['m.nombre', 'm.especie', 'm.tamano', 'u.nombre'];
            $searchWhere = [];
            foreach ($searchFields as $field) {
                $searchWhere[] = "$field LIKE :searchValue";
            }
            $whereClauses[] = "(" . implode(' OR ', $searchWhere) . ")";
            $queryParams[':searchValue'] = '%' . $searchValue . '%';
        }
        
        $whereSql = '';
        if (!empty($whereClauses)) {
            $whereSql = " WHERE " . implode(' AND ', $whereClauses);
        }

        $recordsTotalQuery = "SELECT COUNT(m.id_mascota) as total " . $baseQuery;
        $recordsFilteredQuery = "SELECT COUNT(m.id_mascota) as total " . $baseQuery . $whereSql;
        
        $recordsTotal = $this->query($recordsTotalQuery)[0]['total'];
        $recordsFiltered = $this->query($recordsFilteredQuery, $queryParams)[0]['total'];

        $dataQuery = "SELECT m.id_mascota as id, m.nombre, m.especie, m.tamano, m.genero, u.nombre as propietario, m.estado,
                             CASE 
                                WHEN d.id_dispositivo IS NULL THEN 'sin asignar'
                                WHEN d.estado = 'activo' THEN 'conectado'
                                ELSE 'desconectado'
                             END as dispositivo_estado
                      " . $baseQuery . $whereSql . "
                      ORDER BY $orderColumn $orderDir
                      LIMIT :start, :length";
        
        $queryParams[':start'] = (int)$start;
        $queryParams[':length'] = (int)$length;

        $data = $this->query($dataQuery, $queryParams, PDO::FETCH_ASSOC);

        return [
            "draw" => intval($params['draw'] ?? 0),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        ];
    }
}
?>