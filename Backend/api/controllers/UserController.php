<?php

require_once __DIR__ . '/../database/Database.php';

/**
 * Class UsersController
 *
 * Handles user-related API operations:
 * - GET getAllUsers(): Retrieve all users from the database.
 * - POST createUser(): Create a new user in the database.
 */
class UsersController
{
    /**
     * @var mysqli $dbConnection The database connection instance.
     */
    private $dbConnection;

    /**
     * UsersController constructor.
     *
     * Initializes a database connection.
     */
    public function __construct()
    {
        $database = new Database();
        $this->dbConnection = $database->getConnection();
    }

    /**
     * Retrieves all users from the 'users' table.
     *
     * Endpoint: GET /api/index.php?route=users
     *
     * @return void Outputs a JSON-encoded array of users.
     */
    public function getAllUsers()
    {
        $sql = "SELECT user_id, email, user_type, created_at FROM users";
        $result = $this->dbConnection->query($sql);

        $users = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }

        // Output JSON response
        header('Content-Type: application/json');
        echo json_encode($users);

        $this->dbConnection->close();
    }

    /**
     * Creates a new user in the 'users' table.
     *
     * Endpoint: POST /api/index.php?route=users
     *
     * Expected JSON Request Body:
     * {
     *   "email": "user@example.com",
     *   "password": "plaintext_password",
     *   "user_type": "admin|user"
     * }
     *
     * @return void Outputs a success or error message in JSON format.
     *
     * @throws Exception When database errors occur.
     */
    public function createUser()
    {
        // Read and decode JSON input
        $jsonData = file_get_contents("php://input");
        $data = json_decode($jsonData, true);

        // Validate input fields
        if (!isset($data['email'], $data['password'], $data['user_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Missing required fields: email, password, user_type"]);
            return;
        }

        $email = $data['email'];
        $plainPassword = $data['password'];
        $userType = $data['user_type'];

        // Hash the password
        $passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);

        // Prepare SQL statement
        $stmt = $this->dbConnection->prepare("INSERT INTO users (email, password_hash, user_type) VALUES (?, ?, ?)");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(["error" => "Database prepare failed"]);
            return;
        }

        // Bind parameters and execute
        $stmt->bind_param("sss", $email, $passwordHash, $userType);

        if ($stmt->execute()) {
            // Success response
            http_response_code(201);
            echo json_encode(["message" => "User created successfully"]);
        } else {
            // Error response
            http_response_code(500);
            echo json_encode(["error" => $stmt->error]);
        }

        // Cleanup
        $stmt->close();
        $this->dbConnection->close();
    }
}
