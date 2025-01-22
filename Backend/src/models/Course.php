<?php

namespace models;

// Course class
class Course {
    private int $courseId;
    private string $courseCode;
    private string $courseName;
    private string $pinCode;
    private string $createdAt;

    public function __construct(int $courseId, string $courseCode, string $courseName, string $pinCode, string $createdAt)
    {
        $this->courseId = $courseId;
        $this->courseCode = $courseCode;
        $this->courseName = $courseName;
        $this->pinCode = $pinCode;
        $this->createdAt = $createdAt;
    }


    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function setCourseId(int $courseId): void
    {
        $this->courseId = $courseId;
    }

    public function getCourseCode(): string
    {
        return $this->courseCode;
    }

    public function setCourseCode(string $courseCode): void
    {
        $this->courseCode = $courseCode;
    }

    public function getCourseName(): string
    {
        return $this->courseName;
    }

    public function setCourseName(string $courseName): void
    {
        $this->courseName = $courseName;
    }

    public function getPinCode(): string
    {
        return $this->pinCode;
    }

    public function setPinCode(string $pinCode): void
    {
        $this->pinCode = $pinCode;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }


}