<?php

namespace models\user;

use db\DB;
use Exception;

require_once __DIR__ . '/User.php';
require_once __DIR__ . '/../../database/DB.php';

class Student extends User
{
    private string $studyProgram;
    private int $cohortYear;

    public function __construct(
        int $userId,
        string $firstName,
        string $lastName,
        string $email,
        string $unHashedPassword,
        string $userType,
        string $studyProgram,
        int $cohortYear,
        ?string $createdAt = null
    ) {
        parent::__construct($userId, $firstName, $lastName, $email, $unHashedPassword, $userType, $createdAt);
        $this->studyProgram = $studyProgram;
        $this->cohortYear = $cohortYear;
    }

    public function saveToDB()
    {
        $userId = parent::saveToDB();

        try {
            $db = new DB();
            $conn = $db->getConnection();

            $sql = "INSERT INTO students (student_id, first_name, last_name, study_program, cohort_year) 
                    VALUES (:student_id, :first_name, :last_name, :study_program, :cohort_year)";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':student_id' => $userId,
                ':first_name' => $this->firstName,
                ':last_name' => $this->lastName,
                ':study_program' => $this->studyProgram,
                ':cohort_year' => $this->cohortYear,
            ]);

            $db->closeConnection();
            return $userId;
        } catch (Exception $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
            throw new Exception("Error saving student");
        }
    }
}