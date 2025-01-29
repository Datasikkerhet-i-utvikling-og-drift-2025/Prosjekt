namespace api\controllers;
namespace api\controllers;
namespace api\controllers;
namespace api\controllers;
namespace api\controllers;
namespace api\controllers;
namespace api\controllers;
namespace api\controllers;
/LoginUser.php
<?php



require_once __DIR__ . '/../database/Database.php';

class LoginUser
{
    private $dbConnection;

    public function __construct()
    {
        $database = new DB();
        $this->dbConnection = $database->getConnection();
        if ($this->dbConnection === null) {
            die("Database connection failed");
        }
    }

    public function loginUser()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $stmt = $this->dbConnection->prepare("SELECT email, password_hash FROM users WHERE email = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query preparation failed']);
            return;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password_hash'])) {
            http_response_code(200);
            echo json_encode(['message' => 'Login successful', 'user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
        }

        $stmt->close();
        $this->dbConnection->close();
    }
}