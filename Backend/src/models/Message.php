<?php

namespace models;

// Message class
class Message {
    private int $messageId;
    private int $courseId;
    private int $studentId;
    private string $messageText;
    private bool $isAnonymous;
    private string $createdAt;

    public function __construct(int $messageId, int $courseId, int $studentId, string $messageText, bool $isAnonymous, string $createdAt)
    {
        $this->messageId = $messageId;
        $this->courseId = $courseId;
        $this->studentId = $studentId;
        $this->messageText = $messageText;
        $this->isAnonymous = $isAnonymous;
        $this->createdAt = $createdAt;
    }


    public function sendMessage()
    {

    }

    public function getMessageByCourse()
    {

    }

    public function respondToMessage()
    {

    }

    public function getMessageId(): int
    {
        return $this->messageId;
    }

    public function setMessageId(int $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function setCourseId(int $courseId): void
    {
        $this->courseId = $courseId;
    }

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function setStudentId(int $studentId): void
    {
        $this->studentId = $studentId;
    }

    public function getMessageText(): string
    {
        return $this->messageText;
    }

    public function setMessageText(string $messageText): void
    {
        $this->messageText = $messageText;
    }

    public function isAnonymous(): bool
    {
        return $this->isAnonymous;
    }

    public function setIsAnonymous(bool $isAnonymous): void
    {
        $this->isAnonymous = $isAnonymous;
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