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

if (str_starts_with($source, "movies")){ // /movies*

  if ($method === "GET"){

    if ($sec === ''){ // GET /movies || GET /movies?sort=''&dir=''
      $sort = $_GET['sort'] ?? '';
      $dir = $_GET['dir'] ?? '';

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

    if ($sec !== ''){ // GET /movies/{id}
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

  }

  if ($method === "POST"){
    
    if ($sec === ''){// POST /movies + body_json
      $data = json_decode(file_get_contents('php://input'), true); # Obtener los datos del cuerpo de la peticion

      $title = isset($data['title']) ? $data['title'] : '';
      $director = isset($data['director']) ? $data['director'] : '';
      $date = isset($data['date']) ? (int) $data['date'] : '';
      $genre = isset($data['genre']) ? $data['genre'] : '';
      $rate = isset($data['rate']) ? (float) $data['rate'] : 0;

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

  }

  if ($method === "PUT"){

    if ($sec != ''){ // PUT /movies/{id} + body_json
      foreach ($movies as &$movie) {
        if ($movie['id'] == $sec) {
          $data = json_decode(file_get_contents('php://input'), true);

          $title = isset($data['title']) ? $data['title'] : $movie['title'];
          $director = isset($data['director']) ? $data['director'] : $movie['director'];
          $date = isset($data['date']) ? (int) $data['date'] : (int) $movie['date'];
          $genre = isset($data['genre']) ? $data['genre'] : $movie['genre'];
          $rate = isset($data['rate']) ? (float) $data['rate'] : (float) $movie['rate'];

          $rate = ($rate > 10.0) ? 10.0 : $rate;

          $date = ($date >= 1888 && $date <= (int) date('Y')) ? $date : '';

          if ($date === ''){
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Bad request, Date must be between 1888 and current year"]);
            exit;
          }

          $movie['title'] = $title;
          $movie['director'] = $director;
          $movie['date'] = $date;
          $movie['genre'] = $genre;
          $movie['rate'] = $rate;

          http_response_code(200);
          echo json_encode(["status" => "success", "message" => $movie]);
          exit;
        }
      }

      http_response_code(404);
      echo json_encode(["status" => "error", "message" => "Movie not found"]);
      exit;
    }


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