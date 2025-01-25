<?php

class Course {
    private $id;
    private $code;
    private $name;
    private $lecturerId;
    private $pinCode;

    public function __construct($id = null, $code = null, $name = null, $lecturerId = null, $pinCode = null) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->lecturerId = $lecturerId;
        $this->pinCode = $pinCode;
    }

    public function getId() {
        return $this->id;
    }

    public function getCode() {
        return $this->code;
    }

    public function getName() {
        return $this->name;
    }

    public function getLecturerId() {
        return $this->lecturerId;
    }

    public function getPinCode() {
        return $this->pinCode;
    }

    // Optional: Static method to create a Course instance from a database row
    public static function fromArray(array $data) {
        return new self(
            $data['id'] ?? null,
            $data['code'] ?? null,
            $data['name'] ?? null,
            $data['lecturer_id'] ?? null,
            $data['pin_code'] ?? null
        );
    }
}
