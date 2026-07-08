<?php
header('Content-Type: application/json');

$movie = [
  ["id" => 1, "title" => "El Padrino", "director" => "Francis Ford", "date" => 1972, "genre" => "drama", "rate" => 9.2],
  ["id" => 2, "title" => "El Perro", "director" => "Jeremy Salas", "date" => 2026, "genre" => "suspenso", "rate" => 10],
  ["id" => 3, "title" => "GOT", "director" => "Maria Isabel", "date" => 2000, "genre" => "fantasia", "rate" => 7.8]
];

$next_id = 4;

$method = $_SERVER['REQUEST_METHOD']; # Obtiene el metodo (GET, POST, PUT, DELETE)

$route = explode('/', trim($_SERVER['REQUEST_URI'], '/')); # array con la ruta dividia por indices

$source = $route[0];

if ($source === "movies"){
  
  if ($method === "GET"){
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "GET Success"]);
    exit;
  }

  if ($method === "POST"){
    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "POST Success"]);
    exit;
  }

  if ($method === "PUT"){
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "PUT Success"]);
    exit;
  }

  if ($method === "DELETE"){
    http_response_code(204);
    exit;
  }
}

http_response_code(404);
echo json_encode(["status" => "error", "message" => "endpoint not found, check your address or run the server correctly"]);
?>