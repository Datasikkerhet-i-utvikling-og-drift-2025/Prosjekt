<?php

namespace Utilities;

class UserHandler
{

    public function createUserEntryInDatabase()
    {
        $query = "INSERT INTO users (user_id, email, password_hash, user_type, created_at) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->dbConnection->prepare($query);

        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->dbConnection->error);
        }

        $stmt->bind_param(
            "issss",
            $this->userId,
            $this->email,
            $this->passwordHash,
            $this->userType,
            $this->createdAt->format('Y-m-d H:i:s')
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }

        $stmt->close();
    }

}