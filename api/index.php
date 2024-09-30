<?php
include_once 'includes/Database.php';

// Initialize database connection
$database = new Database();
$db = $database->connect();

// Determine HTTP method and route
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri);

// Routing
if ($_SERVER['REQUEST_URI'] == '/api/v1/authors') {
    $isMain = true;
    require 'v1/Authors.php';
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($uri_segments[4]) && $uri_segments[3] === 'authors' && $uri_segments[5] === 'books') {
    require 'v1/Authors.php';
    getBooksByAuthor($db, $uri_segments[4]);
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($uri_segments[4]) && $uri_segments[3] === 'authors') {
    require 'v1/Authors.php';
    getAuthorById($db, $uri_segments[4]);
}
else if ($_SERVER['REQUEST_URI'] == '/api/v1/books') {
    $isMain = true;
    require 'v1/Books.php';
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($uri_segments[4]) && $uri_segments[3] === 'books') {
    require 'v1/Books.php';
    getBookById($db, $uri_segments[4]);
}
else {
    http_response_code(404);
    echo json_encode(['message' => 'Endpoint not found']);
}

?>
