<?php
use PHPUnit\Framework\TestCase;

include_once 'v1/Books.php';

class BooksTest extends TestCase
{
    protected $db;
    protected $stmt;

    protected function setUp(): void
    {
        // Mock the PDO object and the PDOStatement object
        $this->db = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
    }

    // Test for retrieving books
    public function testGetBooks()
    {
        // Simulated data for books
        $booksData = [
            [
                'id' => 1,
                'title' => 'Book One',
                'description' => 'Description One',
                'publish_date' => '2020-01-01',
                'author_id' => 1
            ],
            [
                'id' => 2,
                'title' => 'Book Two',
                'description' => 'Description Two',
                'publish_date' => '2021-05-05',
                'author_id' => 2
            ]
        ];

        // Simulate cache miss (so we go to the database)
        apcu_store('books_list', false); // Set cache to false for testing purposes

        // Mock the fetchAll method to return the booksData array
        $this->stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($booksData);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        getBooks($this->db);
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode($booksData);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for retrieving an book by ID
    public function testGetBookById()
    {
        // Simulated data for a single book
        $bookData = [
            'id' => 1,
            'title' => 'Book One',
            'description' => 'Description One',
            'publish_date' => '2020-01-01',
            'author_id' => 1
        ];

        // Simulate a cache miss (so we go to the database)
        $cacheKey = 'book_1';
        apcu_store($cacheKey, false); // Set cache to false for testing cache miss

        // Mock the fetch method to return the bookData array for a single book
        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn($bookData);

        // Expect the prepare method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        getBookById($this->db, 1);  // Call the getBookById method with id 1
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode($bookData);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for creating a book
    public function testCreateBook()
    {
        // Simulated input data for a new book
        $inputData = (object) [
            'title' => 'New Book',
            'description' => 'New Description',
            'publish_date' => '2022-12-12',
            'author_id' => 3
        ];

        // Mock the execute() method to return true (success)
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        createBook($this->db, $inputData); // Pass input data directly
        $output = ob_get_clean();

        // Expected JSON output for success
        $expected = json_encode(["message" => "Book created"]);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for updating a book
    public function testUpdateBook()
    {
        // Simulated input data for updating a book
        $inputData = (object) [
            'id' => 1, // ID of the book to update
            'title' => 'Updated Book Title',
            'description' => 'Updated Description',
            'publish_date' => '2023-01-01',
            'author_id' => 2
        ];

        // Mock the execute() method to return true (success)
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Mock the rowCount() method to return 1, simulating that one row was updated
        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        updateBook($this->db, $inputData); // Pass input data directly
        $output = ob_get_clean();

        // Expected JSON output for success
        $expected = json_encode(["message" => "Book updated"]);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for deleting a book
    public function testDeleteBook()
    {
        // Simulated input data for deleting a book
        $inputData = (object) [
            'id' => 1 // ID of the book to delete
        ];

        // Mock the execute() method to return true (indicating successful deletion)
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        deleteBook($this->db, $inputData); // Pass input data directly
        $output = ob_get_clean();

        // Expected JSON output for success
        $expected = json_encode(["message" => "Book deleted"]);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Extra tests for edge cases
    public function testCreateBook_MissingFields()
    {
        // Simulated input data without required fields
        $inputData = (object) [
            'description' => 'A great book',
            'publish_date' => '2020-01-01'
        ];

        // Mock the prepare() method, should not be called due to missing fields
        $this->db->expects($this->never())
            ->method('prepare');

        // Capture the output
        ob_start();
        createBook($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response for missing fields
        $expected = json_encode(["message" => "Missing required fields: title, author_id"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testCreateBook_DatabaseFailure()
    {
        // Simulated input data with required fields
        $inputData = (object) [
            'title' => 'Book Title',
            'description' => 'A great book',
            'publish_date' => '2020-01-01',
            'author_id' => 1
        ];

        // Mock the execute() method to return false (simulating a DB failure)
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(false);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        createBook($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response for a database failure
        $expected = json_encode(["message" => "Book creation failed"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testUpdateBook_MissingId()
    {
        // Simulated input data without an ID
        $inputData = (object) [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'publish_date' => '1990-01-01'
        ];

        // Mock the prepare() method, should not be called due to missing ID
        $this->db->expects($this->never())
            ->method('prepare');

        // Capture the output
        ob_start();
        updateBook($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response for missing ID
        $expected = json_encode(["message" => "Missing required field: id"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testUpdateBook_BookNotFound()
    {
        // Simulated input data with an ID
        $inputData = (object) [
            'id' => 999, // Non-existent book ID
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'publish_date' => '1990-01-01'
        ];

        // Mock the execute() method to simulate a book not being found
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Mock rowCount() to return 0 (indicating no rows were updated)
        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(0);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        updateBook($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response when book is not found
        $expected = json_encode(["message" => "Book not found"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testDeleteBookSuccess()
    {
        // Simulated input data for deletion
        $inputData = (object)[
            'id' => 1 // Book ID to delete
        ];

        // Mock the prepare method to return the statement mock
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Prepare the statement mock for the DELETE query
        $this->stmt->expects($this->once())
            ->method('bindParam')
            ->with(':id', $inputData->id); // Ensure bindParam is called with the correct ID

        // Simulating a successful delete operation
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true); // Simulating successful execution of the query

        // Capture the output
        ob_start();
        deleteBook($this->db, $inputData);  // Call the deleteBook function
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode(["message" => "Book deleted"]);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testDeleteBookFailure()
    {
        // Simulated input data for deletion
        $inputData = (object)[
            'id' => 1 // Book ID to delete
        ];

        // Mock the prepare method to return the statement mock
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Prepare the statement mock for the DELETE query
        $this->stmt->expects($this->once())
            ->method('bindParam')
            ->with(':id', $inputData->id); // Ensure bindParam is called with the correct ID

        // Simulating a failed delete operation
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(false); // Simulating failed execution of the query

        // Capture the output
        ob_start();
        deleteBook($this->db, $inputData);  // Call the deleteBook function
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode(["message" => "Book deletion failed"]);

        // Assert that the output matches the expected failure message
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

}
