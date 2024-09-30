<?php
use PHPUnit\Framework\TestCase;

include_once 'v1/Authors.php';

class AuthorsTest extends TestCase
{
    private $db;
    private $stmt;

    // This method runs before each test
    protected function setUp(): void
    {
        // Mock the PDO and the statement
        $this->db = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
    }

    // Test for retrieving all authors
    public function testGetAuthors()
    {
        // Simulated data for authors
        $authorsData = [
            [
                'id' => 1,
                'name' => 'Author One',
                'bio' => 'Bio of Author One',
                'birth_date' => '1990-01-01'
            ],
            [
                'id' => 2,
                'name' => 'Author Two',
                'bio' => 'Bio of Author Two',
                'birth_date' => '1985-05-15'
            ]
        ];

        // Simulate cache miss (so we go to the database)
        apcu_store('authors_list', false); // Set cache to false for testing purposes

        // Mock the fetchAll method to return the authorsData array
        $this->stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($authorsData);

        // Expect the prepare method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        getAuthors($this->db);  // Call the getAuthors method
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode($authorsData);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for retrieving an author by ID
    public function testGetAuthorById()
    {
        // Simulated data for a single author
        $authorData = [
            'id' => 1,
            'name' => 'Author One',
            'bio' => 'Bio of Author One',
            'birth_date' => '1990-01-01'
        ];

        // Simulate a cache miss (so we go to the database)
        $cacheKey = 'author_1';
        apcu_store($cacheKey, false); // Set cache to false for testing cache miss

        // Mock the fetch method to return the authorData array for a single author
        $this->stmt->expects($this->once())
            ->method('fetch')
            ->willReturn($authorData);

        // Expect the prepare method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        getAuthorById($this->db, 1);  // Call the getAuthorById method with id 1
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode($authorData);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for creating a new author
    public function testCreateAuthor()
    {
        $data = (object)[
            'name' => 'New Author',
            'bio' => 'New Bio',
            'birth_date' => '1990-10-10'
        ];

        // Set up the mock statement execution
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Expect the prepare method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        createAuthor($this->db, $data);  // Passing the $data directly
        $output = ob_get_clean();

        // Verify the output
        $expected = json_encode(["message" => "Author created"]);
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for updating an author
    public function testUpdateAuthor()
    {
        // Simulated data for the author update
        $data = (object)[
            'id' => 1,
            'name' => 'Updated Author',
            'bio' => 'Updated Bio',
            'birth_date' => '1980-10-10'
        ];

        // Mock the execute() method to return true (successful execution)
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Mock the rowCount() method to return 1 (indicating the author was found and updated)
        $this->stmt->expects($this->once())
            ->method('rowCount')
            ->willReturn(1);

        // Expect the prepare() method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        updateAuthor($this->db, $data);  // Pass the $data directly
        $output = ob_get_clean();

        // Verify the output
        $expected = json_encode(["message" => "Author updated"]);
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for deleting an author
    public function testDeleteAuthor()
    {
        // Simulated data for the author delete
        $data = (object)[
            'id' => 1
        ];

        // Mock the statement to return true for successful execution
        $this->stmt->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        // Expect the prepare method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        deleteAuthor($this->db, $data);  // Passing the $data directly
        $output = ob_get_clean();

        // Verify the output
        $expected = json_encode(["message" => "Author deleted"]);
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Test for retrieving books by an author
    public function testGetBooksByAuthor()
    {
        // Simulated data for books by the author
        $booksData = [
            [
                'id' => 3,
                'title' => '1984',
                'description' => 'A dystopian novel that explores the dangers of totalitarianism. In a world where Big Brother watches every move, Winston Smith dares to defy the oppressive regime, fighting for truth and individuality in a society where freedom of thought is suppressed.',
                'publish_date' => '2018-11-17',
                'author_name' => 'Robby Suryanata'
            ],
            [
                'id' => 5,
                'title' => 'Sapiens: A Brief History of Humankind',
                'description' => 'This non-fiction book takes readers on a journey through the history of humanity, from the emergence of Homo sapiens in the Stone Age to the political and technological revolutions of the 21st century. Harari explores how biology, culture, and technology have shaped human societies and the world as we know it.',
                'publish_date' => '2020-05-28',
                'author_name' => 'Robby Suryanata'
            ],
            [
                'id' => 7,
                'title' => 'The Road',
                'description' => 'A post-apocalyptic novel that follows a father and his young son as they travel through a desolate, ash-covered landscape in search of safety. Facing starvation, harsh conditions, and bands of cannibals, the bond between father and son is tested in this bleak yet powerful story of survival and love.',
                'publish_date' => '2004-12-14',
                'author_name' => 'Robby Suryanata'
            ]
        ];

        // Mock the fetchAll method to return the books data
        $this->stmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($booksData);

        // Expect the prepare method to be called and return the mocked statement
        $this->db->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmt);

        // Capture the output
        ob_start();
        getBooksByAuthor($this->db, 1);  // Call the getBooksByAuthor method with author_id 1
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode($booksData);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    // Extra tests for edge cases
    public function testCreateAuthor_MissingFields()
    {
        // Simulated input data without required fields
        $inputData = (object) [
            'bio' => 'A great author',
            'birth_date' => '2020-01-01'
        ];

        // Mock the prepare() method, should not be called due to missing fields
        $this->db->expects($this->never())
            ->method('prepare');

        // Capture the output
        ob_start();
        createAuthor($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response for missing fields
        $expected = json_encode(["message" => "Missing required fields: name"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testCreateAuthor_DatabaseFailure()
    {
        // Simulated input data with required fields
        $inputData = (object) [
            'name' => 'New Author',
            'bio' => 'A great author',
            'birth_date' => '2020-01-01'
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
        createAuthor($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response for a database failure
        $expected = json_encode(["message" => "Author creation failed"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testUpdateAuthor_MissingId()
    {
        // Simulated input data without an ID
        $inputData = (object) [
            'name' => 'Updated Name',
            'bio' => 'Updated Bio',
            'birth_date' => '1990-01-01'
        ];

        // Mock the prepare() method, should not be called due to missing ID
        $this->db->expects($this->never())
            ->method('prepare');

        // Capture the output
        ob_start();
        updateAuthor($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response for missing ID
        $expected = json_encode(["message" => "Missing required field: id"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testUpdateAuthor_AuthorNotFound()
    {
        // Simulated input data with an ID
        $inputData = (object) [
            'id' => 999, // Non-existent author ID
            'name' => 'Updated Name',
            'bio' => 'Updated Bio',
            'birth_date' => '1990-01-01'
        ];

        // Mock the execute() method to simulate an author not being found
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
        updateAuthor($this->db, $inputData);
        $output = ob_get_clean();

        // Expected response when author is not found
        $expected = json_encode(["message" => "Author not found"]);

        // Assert that the output matches the expected response
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testDeleteAuthorSuccess()
    {
        // Simulated input data for deletion
        $inputData = (object)[
            'id' => 1 // Author ID to delete
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
        deleteAuthor($this->db, $inputData);  // Call the deleteAuthor function
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode(["message" => "Author deleted"]);

        // Assert that the output matches the expected data
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

    public function testDeleteAuthorFailure()
    {
        // Simulated input data for deletion
        $inputData = (object)[
            'id' => 1 // Author ID to delete
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
        deleteAuthor($this->db, $inputData);  // Call the deleteAuthor function
        $output = ob_get_clean();

        // Expected JSON output
        $expected = json_encode(["message" => "Author deletion failed"]);

        // Assert that the output matches the expected failure message
        $this->assertJsonStringEqualsJsonString($expected, $output);
    }

}
?>
