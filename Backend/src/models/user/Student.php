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
    private int $userId;

    public function __construct(
        string $studyProgram,
        int $cohortYear,
        int $userId,
    ) {
        parent::__construct($);
        $this->studyProgram = $studyProgram;
        $this->cohortYear = $cohortYear;
        $this->userId = $userId;
    }

    // public function saveToDB()
    // {
    //     $userId = parent::saveToDB();

    //     try {
    //         $db = new DB();
    //         $conn = $db->getConnection();

    //         $sql = "INSERT INTO students (student_id, first_name, last_name, study_program, cohort_year) 
    //                 VALUES (:student_id, :first_name, :last_name, :study_program, :cohort_year)";

    //         $stmt = $conn->prepare($sql);
    //         $stmt->execute([
    //             ':student_id' => $userId,
    //             ':first_name' => $this->firstName,
    //             ':last_name' => $this->lastName,
    //             ':study_program' => $this->studyProgram,
    //             ':cohort_year' => $this->cohortYear,
    //         ]);

    //         $db->closeConnection();
    //         return $userId;
    //     } catch (Exception $e) {
    //         error_log($e->getMessage(), 3, __DIR__ . '/../../logs/error.log');
    //         throw new Exception("Error saving student");
    //     }
    // }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function setUserType(string $userType): void
    {
        $this->userType = $userType;
    }

    public function getStudyProgram(): string
    {
        return $this->studyProgram;
    }

    public function setStudyProgram(string $studyProgram): void
    {
        $this->studyProgram = $studyProgram;
    }

    public function getCohortYear(): int
    {
        return $this->cohortYear;
    }

    public function setCohortYear(int $cohortYear): void
    {
        $this->cohortYear = $cohortYear;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
}