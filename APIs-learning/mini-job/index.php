<?php

declare(strict_types=1);

header('Content-Type: application/json');

// -----------------------------------------------------------------------------
// Constants
// -----------------------------------------------------------------------------
const YEAR_MIN = 1888;
const RATE_MAX = 10.0;
const VALID_SORT_FIELDS = ['date', 'rate'];
const VALID_SORT_DIRECTIONS = ['asc', 'desc'];

// -----------------------------------------------------------------------------
// Response helper
// -----------------------------------------------------------------------------
function jsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// -----------------------------------------------------------------------------
// Validation & sanitization
// -----------------------------------------------------------------------------
/**
 * @throws InvalidArgumentException
 */
function validateAndSanitizeMovieData(array $data, ?array $existing = null): array
{
    $sanitized = [];

    // Title
    $title = $data['title'] ?? ($existing['title'] ?? null);
    if ($title === null || trim((string) $title) === '') {
        throw new InvalidArgumentException('Title is required and must not be empty.');
    }
    $sanitized['title'] = trim((string) $title);

    // Director
    $director = $data['director'] ?? ($existing['director'] ?? null);
    if ($director === null || trim((string) $director) === '') {
        throw new InvalidArgumentException('Director is required and must not be empty.');
    }
    $sanitized['director'] = trim((string) $director);

    // Date
    $date = $data['date'] ?? ($existing['date'] ?? null);
    if ($date === null) {
        throw new InvalidArgumentException('Date is required.');
    }
    $dateInt = (int) $date;
    $currentYear = (int) date('Y');
    if ($dateInt < YEAR_MIN || $dateInt > $currentYear) {
        throw new InvalidArgumentException("Date must be between " . YEAR_MIN . " and $currentYear.");
    }
    $sanitized['date'] = $dateInt;

    // Genre
    $genre = $data['genre'] ?? ($existing['genre'] ?? null);
    if ($genre === null || trim((string) $genre) === '') {
        throw new InvalidArgumentException('Genre is required and must not be empty.');
    }
    $sanitized['genre'] = trim((string) $genre);

    // Rate
    $rate = $data['rate'] ?? ($existing['rate'] ?? null);
    if ($rate === null) {
        throw new InvalidArgumentException('Rate is required.');
    }
    $rateFloat = (float) $rate;
    if ($rateFloat < 0) {
        $rateFloat = 0.0;
    } elseif ($rateFloat > RATE_MAX) {
        $rateFloat = RATE_MAX;
    }
    $sanitized['rate'] = $rateFloat;

    return $sanitized;
}

// -----------------------------------------------------------------------------
// Movie repository (in‑memory storage)
// -----------------------------------------------------------------------------
class MovieRepository
{
    private array $movies;

    public function __construct(array $initialMovies = [])
    {
        $this->movies = $initialMovies;
    }

    public function getAll(): array
    {
        return $this->movies;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->movies as $movie) {
            if ($movie['id'] === $id) {
                return $movie;
            }
        }
        return null;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function add(array $data): array
    {
        $sanitized = validateAndSanitizeMovieData($data);

        // Generate new ID
        $lastId = !empty($this->movies) ? end($this->movies)['id'] : 0;
        $newId = $lastId + 1;

        $newMovie = array_merge(['id' => $newId], $sanitized);
        $this->movies[] = $newMovie;

        return $newMovie;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function update(int $id, array $data): ?array
    {
        $index = $this->getIndexById($id);
        if ($index === null) {
            return null;
        }

        $existing = $this->movies[$index];
        $sanitized = validateAndSanitizeMovieData($data, $existing);

        $updatedMovie = array_merge($existing, $sanitized);
        $updatedMovie['id'] = $id; // ensure id stays
        $this->movies[$index] = $updatedMovie;

        return $updatedMovie;
    }

    public function delete(int $id): bool
    {
        $index = $this->getIndexById($id);
        if ($index === null) {
            return false;
        }
        array_splice($this->movies, $index, 1);
        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getSorted(string $sortField, string $direction): array
    {
        if (!in_array($sortField, VALID_SORT_FIELDS, true)) {
            throw new InvalidArgumentException("Invalid sort field. Allowed: " . implode(', ', VALID_SORT_FIELDS));
        }
        if (!in_array($direction, VALID_SORT_DIRECTIONS, true)) {
            throw new InvalidArgumentException("Invalid sort direction. Allowed: asc, desc");
        }

        $movies = $this->movies;
        usort($movies, function (array $a, array $b) use ($sortField, $direction): int {
            $valueA = $a[$sortField];
            $valueB = $b[$sortField];
            $result = $valueA <=> $valueB;
            return $direction === 'desc' ? -$result : $result;
        });

        return $movies;
    }

    private function getIndexById(int $id): ?int
    {
        foreach ($this->movies as $index => $movie) {
            if ($movie['id'] === $id) {
                return $index;
            }
        }
        return null;
    }
}

// -----------------------------------------------------------------------------
// Initial data
// -----------------------------------------------------------------------------
$initialMovies = [
    ['id' => 1, 'title' => 'El Padrino', 'director' => 'Francis Ford', 'date' => 1972, 'genre' => 'drama', 'rate' => 9.2],
    ['id' => 2, 'title' => 'El Perro', 'director' => 'Jeremy Salas', 'date' => 2026, 'genre' => 'suspenso', 'rate' => 10.0],
    ['id' => 3, 'title' => 'GOT', 'director' => 'Maria Isabel', 'date' => 2000, 'genre' => 'fantasia', 'rate' => 7.8],
];

$repository = new MovieRepository($initialMovies);

// -----------------------------------------------------------------------------
// Simple router
// -----------------------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$uri = trim($_SERVER['REQUEST_URI'], '/');
$segments = $uri !== '' ? explode('/', $uri) : [];

$resource = $segments[0] ?? '';
$id = isset($segments[1]) ? (int) $segments[1] : null;

// Only "/movies" resource is supported
if ($resource !== 'movies') {
    jsonResponse(404, ['status' => 'error', 'message' => 'Endpoint not found.']);
}

try {
    switch ($method) {
        case 'GET':
            if ($id === null) {
                // GET /movies or GET /movies?sort=...&dir=...
                $sort = $_GET['sort'] ?? '';
                $dir = $_GET['dir'] ?? '';

                if ($sort === '' && $dir === '') {
                    $movies = $repository->getAll();
                } else {
                    $movies = $repository->getSorted($sort, $dir);
                }

                jsonResponse(200, ['status' => 'success', 'message' => $movies]);
            }

            // GET /movies/{id}
            $movie = $repository->findById($id);
            if ($movie === null) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Movie not found.']);
            }
            jsonResponse(200, ['status' => 'success', 'message' => $movie]);

        case 'POST':
            // POST /movies
            if ($id !== null) {
                jsonResponse(400, ['status' => 'error', 'message' => 'ID not allowed when creating a movie.']);
            }

            $rawData = json_decode(file_get_contents('php://input'), true);
            if (!is_array($rawData)) {
                jsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON body.']);
            }

            $newMovie = $repository->add($rawData);
            jsonResponse(201, ['status' => 'success', 'message' => $newMovie]);

        case 'PUT':
            // PUT /movies/{id}
            if ($id === null) {
                jsonResponse(400, ['status' => 'error', 'message' => 'Movie ID is required for update.']);
            }

            $rawData = json_decode(file_get_contents('php://input'), true);
            if (!is_array($rawData)) {
                jsonResponse(400, ['status' => 'error', 'message' => 'Invalid JSON body.']);
            }

            $updatedMovie = $repository->update($id, $rawData);
            if ($updatedMovie === null) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Movie not found.']);
            }
            jsonResponse(200, ['status' => 'success', 'message' => $updatedMovie]);

        case 'DELETE':
            // DELETE /movies/{id}
            if ($id === null) {
                jsonResponse(400, ['status' => 'error', 'message' => 'Movie ID is required for deletion.']);
            }

            $deleted = $repository->delete($id);
            if (!$deleted) {
                jsonResponse(404, ['status' => 'error', 'message' => 'Movie not found.']);
            }

            // 204 No Content – no body
            http_response_code(204);
            exit;

        default:
            jsonResponse(405, ['status' => 'error', 'message' => 'Method not allowed.']);
    }
} catch (InvalidArgumentException $e) {
    jsonResponse(400, ['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    jsonResponse(500, ['status' => 'error', 'message' => 'Internal server error.']);
}