<?php
// 1. Configurar el encabezado para JSON
header('Content-Type: application/json');

// 2. Base de datos simulada (en memoria)
$tareas = [
    ["id" => 1, "titulo" => "matematicas", "completada" => false],
    ["id" => 2, "titulo" => "sociales", "completada" => true]
];

// 3. Obtener el método HTTP (GET, POST, PUT, DELETE)
$metodo = $_SERVER['REQUEST_METHOD'];

// 4. Obtener la URL y dividirla en partes
//    Ej: /tareas/1 → ["tareas", "1"]
$ruta = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

// 5. Extraer el recurso y el ID (si existe)
$recurso = $ruta[0] ?? '';  // Primer elemento: "tareas"
$id = $ruta[1] ?? null;     // Segundo elemento: "1" o null

// 6. Enrutamiento manual (el "libro de registro")
if ($recurso == 'tareas') {
    
    // --- GET /tareas ---
    if ($metodo === 'GET' && $id === null) {
        echo json_encode($tareas);
        exit;
    }
    
    // --- GET /tareas/{id} ---
    if ($metodo === 'GET' && $id !== null) {
        foreach ($tareas as $tarea) {
            if ($tarea['id'] == $id) {
                echo json_encode($tarea);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(["error" => "Tarea no encontrada"]);
        exit;
    }
    
    // --- POST /tareas ---
    if ($metodo === 'POST') {
        // Leer el cuerpo de la petición (JSON)
        $datos = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($datos['titulo'])) {
            http_response_code(400);
            echo json_encode(["error" => "El campo 'titulo' es obligatorio"]);
            exit;
        }
        
        // Crear nueva tarea
        $nuevo_id = count($tareas) + 1;
        $nueva_tarea = [
            "id" => $nuevo_id,
            "titulo" => $datos['titulo'],
            "completada" => $datos['completada'] ?? false
        ];
        $tareas[] = $nueva_tarea;
        
        http_response_code(201);
        echo json_encode($nueva_tarea);
        exit;
    }
    
    // --- PUT /tareas/{id} ---
    if ($metodo === 'PUT' && $id !== null) {
        $datos = json_decode(file_get_contents('php://input'), true);
        
        foreach ($tareas as &$tarea) {
            if ($tarea['id'] == $id) {
                if (isset($datos['titulo'])) {
                    $tarea['titulo'] = $datos['titulo'];
                }
                if (isset($datos['completada'])) {
                    $tarea['completada'] = $datos['completada'];
                }
                http_response_code(200);
                echo json_encode($tarea);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(["error" => "Tarea no encontrada"]);
        exit;
    }
    
    // --- DELETE /tareas/{id} ---
    if ($metodo === 'DELETE' && $id !== null) {
        foreach ($tareas as $indice => $tarea) {
            if ($tarea['id'] == $id) {
                unset($tareas[$indice]);
                // Reindexar el array (opcional)
                $tareas = array_values($tareas);
                http_response_code(204);
                exit; // 204 No Content, sin cuerpo
            }
        }
        http_response_code(404);
        echo json_encode(["error" => "Tarea no encontrada"]);
        exit;
    }
}

// 7. Si la ruta no coincide con nada, devolver 404
http_response_code(404);
echo json_encode(["error" => "Ruta no encontrada (asegurate correr el server en la carpeta exacta)"]);
?>