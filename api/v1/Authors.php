<?php 
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Determine HTTP method
$method = $_SERVER['REQUEST_METHOD'];

if (isset($isMain)) {
    switch ($method) {
        case 'GET':
            getAuthors($db);
            break;
        case 'POST':
            createAuthor($db);
            break;
        case 'PUT':
            updateAuthor($db);
            break;
        case 'DELETE':
            deleteAuthor($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(["message" => "Method not allowed"]);
            break;
    }
}

function getAuthors($db) {
    // Check cache first
    $cacheKey = 'authors_list';
    $authors = apcu_fetch($cacheKey); // APCu caching
    
    if ($authors === false) { // Cache miss, perform database query
        $query = 'SELECT id, name, bio, birth_date FROM tb_authors'; // Only select required fields
        $stmt = $db->prepare($query);
        $stmt->execute();

        $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Store the results in cache
        apcu_store($cacheKey, $authors, 3600); // Cache for 1 hour
    }

    if (count($authors) > 0) {
        echo json_encode($authors);
    } else {
        echo json_encode(['message' => 'No authors found']);
    }
}

function getAuthorById($db, $authorId) {
    $cacheKey = 'author_' . $authorId;
    $author = apcu_fetch($cacheKey);

    if ($author === false) {
        $query = 'SELECT id, name, bio, birth_date FROM tb_authors WHERE id = :id LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $authorId);
        $stmt->execute();
        
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cache the author if found
        if ($author) {
            apcu_store($cacheKey, $author, 3600);
        }
    }

    if ($author) {
        echo json_encode($author);
    } else {
        echo json_encode(['message' => 'Author not found']);
    }
}

function createAuthor($db, $data = null) {
    // If no data is passed, fetch from php://input
    if (!$data) {
        $data = json_decode(file_get_contents("php://input"));
    }

    // Check for missing required fields
    if (empty($data->name)) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields: name"]);
        return;
    }

    $query = 'INSERT INTO tb_authors (name, bio, birth_date) VALUES (:name, :bio, :birth_date)';
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':name', $data->name);
    $stmt->bindParam(':bio', $data->bio);
    $stmt->bindParam(':birth_date', $data->birth_date);

    if ($stmt->execute()) {
        apcu_delete('authors_list'); // Clear cache on create
        echo json_encode(["message" => "Author created"]);
    } else {
        echo json_encode(["message" => "Author creation failed"]);
    }
}

function updateAuthor($db, $input = null) {
    $data = $input ? $input : json_decode(file_get_contents("php://input"));

    // Check if ID is provided
    if (empty($data->id)) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required field: id"]);
        return;
    }

    $query = 'UPDATE tb_authors SET name = :name, bio = :bio, birth_date = :birth_date WHERE id = :id';
    $stmt = $db->prepare($query);

    $stmt->bindParam(':id', $data->id);
    $stmt->bindParam(':name', $data->name);
    $stmt->bindParam(':bio', $data->bio);
    $stmt->bindParam(':birth_date', $data->birth_date);

    if ($stmt->execute()) {
        apcu_delete('authors_list'); // Clear cache on update
        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "Author updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Author not found"]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Author update failed"]);
    }
}

function deleteAuthor($db, $data = null) {
    // If no data is passed, fetch from php://input
    if (!$data) {
        $data = json_decode(file_get_contents("php://input"));
    }

    $query = 'DELETE FROM tb_authors WHERE id = :id';
    $stmt = $db->prepare($query);
    
    $stmt->bindParam(':id', $data->id);

    if ($stmt->execute()) {
        apcu_delete('authors_list'); // Clear cache on delete
        echo json_encode(["message" => "Author deleted"]);
    } else {
        echo json_encode(["message" => "Author deletion failed"]);
    }
}

function getBooksByAuthor($db, $author_id) {
    echo $author_id;
    $cacheKey = 'books_by_author_' . $author_id;
    $books = apcu_fetch($cacheKey); // APCu caching
    
    if ($books === false) { // Cache miss, perform database query
        $query = 'SELECT b.id, b.title, b.description, b.publish_date, a.name AS author_name
                  FROM tb_books b
                  JOIN tb_authors a ON b.author_id = a.id
                  WHERE b.author_id = :author_id';

        $stmt = $db->prepare($query);
        $stmt->bindParam(':author_id', $author_id);
        $stmt->execute();

        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Store the results in cache
        apcu_store($cacheKey, $books, 3600); // Cache for 1 hour
    }

    if (count($books) > 0) {
        echo json_encode($books);
    } else {
        echo json_encode(["message" => "No books found for the specified author"]);
    }
}
?>
