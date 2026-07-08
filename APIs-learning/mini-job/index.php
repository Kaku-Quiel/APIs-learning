<?php
header('Content-Type: application/json');

$movies = [
  ["id" => 1, "title" => "El Padrino", "director" => "Francis Ford", "date" => 1972, "genre" => "drama", "rate" => 9.2],
  ["id" => 2, "title" => "El Perro", "director" => "Jeremy Salas", "date" => 2026, "genre" => "suspenso", "rate" => 10.0],
  ["id" => 3, "title" => "GOT", "director" => "Maria Isabel", "date" => 2000, "genre" => "fantasia", "rate" => 7.8]
];

$method = $_SERVER['REQUEST_METHOD']; # Obtiene el metodo (GET, POST, PUT, DELETE)

$route = explode('/', trim($_SERVER['REQUEST_URI'], '/')); # array con la ruta dividia por indices

$source = $route[0] ?? '';
$sec = $route[1] ?? '';

if (str_starts_with($source, "movies")){

  if ($method === "GET"){
    $sort = $_GET['sort'] ?? '';
    $dir = $_GET['dir'] ?? '';

    if ($sec === ''){
      if ($sort === '' and $dir === '') {
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => $movies]);
        exit;
      }

      $sort_movies = sort_movies($movies, $sort, $dir);
      
      if ($sort_movies === ''){
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "can not sort the movies"]);
        exit;
      }

      http_response_code(200);
      echo json_encode(["status" => "success", "message" => $sort_movies]);
      exit;
    }

    foreach ($movies as &$movie){
      if ($movie['id'] == $sec){
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => $movie]);
        exit;
      }
    }
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Movie not found"]);
    exit;
  }

  if ($method === "POST"){
    $data = json_decode(file_get_contents('php://input'), true); # Obtener los datos del cuerpo de la peticion

    $title = $data['title'] ?? '';
    $director = $data['director'] ?? '';
    $date = (int) $data['date'] ?? '';
    $genre = $data['genre'] ?? '';
    $rate = (float) $data['rate'] ?? 0;

    $rate = ($rate > 10.0) ? 10.0 : $rate;

    $date = ($date >= 1888 && $date <= (int) date('Y')) ? $date : '';

    if ($title === '' || $director === '' || $date === '' || $genre === ''){
      http_response_code(400);
      echo json_encode(["status" => "error", "message" => "Bad request, fill all information or fill correctly"]);
      exit;
    }

    $new_id = $movies[count($movies) - 1]['id'] + 1;
    $new_movie = ["id" => $new_id, "title" => $title, "director" => $director, "date" => $date, "genre" => $genre, "rate" => $rate];
    
    $movies[] = $new_movie;

    http_response_code(201);
    echo json_encode(["status" => "success", "message" => $new_movie]);
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





<?php

function sort_movies($movies, $sort, $dir){
  $valid_sort = ["date", "rate"];
  $valid_dir = ["asc", "desc"];
  if (!in_array($sort, $valid_sort)){
    return '';
  }
  if (!in_array($dir, $valid_dir)){
    return '';
  }

  $final_movies = [];

  $sort_movies = [];

  foreach ($movies as $movie){
    $sort_movies[] = $movie[$sort];
  }

  if ($dir === "desc"){
    rsort($sort_movies);
  }
  else{
    sort($sort_movies);
  }

  foreach ($sort_movies as $num){
    foreach ($movies as $movie){
      if ($movie[$sort] == $num){
        $final_movies[] = $movie;
        break;
      }
    }
  }

  return $final_movies;
}

?>