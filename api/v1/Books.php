<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];

if (isset($isMain)) {
    switch ($method) {
        case 'GET':
            getBooks($db);
            break;
        case 'POST':
            createBook($db);
            break;
        case 'PUT':
            updateBook($db);
            break;
        case 'DELETE':
            deleteBook($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}

function getBooks($db) {
    $cacheKey = 'books_list';
    $books = apcu_fetch($cacheKey);

    if ($books === false) {
        $query = 'SELECT id, title, description, publish_date, author_id FROM tb_books';
        $stmt = $db->prepare($query);
        $stmt->execute();

        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Store in cache for 1 hour
        apcu_store($cacheKey, $books, 3600);
    }

    if (count($books) > 0) {
        echo json_encode($books);
    } else {
        echo json_encode(['message' => 'No books found']);
    }
}

function getBookById($db, $bookId) {
    $cacheKey = 'book_' . $bookId;
    $book = apcu_fetch($cacheKey);

    if ($book === false) {
        $query = 'SELECT id, title, description, publish_date, author_id FROM tb_books WHERE id = :id LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $bookId);
        $stmt->execute();
        
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cache the book if found
        if ($book) {
            apcu_store($cacheKey, $book, 3600);
        }
    }

    if ($book) {
        echo json_encode($book);
    } else {
        echo json_encode(['message' => 'Book not found']);
    }
}

function createBook($db, $input = null) {
    $data = $input ? $input : json_decode(file_get_contents("php://input"));

    if (empty($data->title) || empty($data->author_id)) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields: title, author_id"]);
        return;
    }

    $query = 'INSERT INTO tb_books (title, description, publish_date, author_id) 
              VALUES (:title, :description, :publish_date, :author_id)';
    $stmt = $db->prepare($query);

    $stmt->bindParam(':title', $data->title);
    $stmt->bindParam(':description', $data->description);
    $stmt->bindParam(':publish_date', $data->publish_date);
    $stmt->bindParam(':author_id', $data->author_id);

    if ($stmt->execute()) {
        apcu_delete('books_list');  // Invalidate book list cache
        http_response_code(201);
        echo json_encode(["message" => "Book created"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Book creation failed"]);
    }
}

function updateBook($db, $data = null) {
    if (!$data) {
        $data = json_decode(file_get_contents("php://input"));
    }

    if (empty($data->id)) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required field: id"]);
        return;
    }

    $query = 'UPDATE tb_books
            SET
                title = :title,
                description = :description,
                publish_date = :publish_date,
                author_id = :author_id
            WHERE id = :id';
    $stmt = $db->prepare($query);

    $stmt->bindParam(':id', $data->id);
    $stmt->bindParam(':title', $data->title);
    $stmt->bindParam(':description', $data->description);
    $stmt->bindParam(':publish_date', $data->publish_date);
    $stmt->bindParam(':author_id', $data->author_id);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            apcu_delete('books_list');         // Invalidate book list cache
            apcu_delete('book_' . $data->id);  // Invalidate specific book cache
            http_response_code(200);
            echo json_encode(["message" => "Book updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Book not found"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Book update failed"]);
    }
}

function deleteBook($db, $data = null) {
    if (!$data) {
        $data = json_decode(file_get_contents("php://input"));
    }

    $query = 'DELETE FROM tb_books WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);

    if ($stmt->execute()) {
        apcu_delete('books_list');          // Invalidate book list cache
        apcu_delete('book_' . $data->id);   // Invalidate specific book cache
        echo json_encode(["message" => "Book deleted"]);
    } else {
        echo json_encode(["message" => "Book deletion failed"]);
    }
}
