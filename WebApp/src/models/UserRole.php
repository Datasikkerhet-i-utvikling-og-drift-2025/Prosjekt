<?php

namespace models;

enum UserRole: string {
    case Lecturer = 'lecturer';
    case Student = 'student';
    case Admin = 'admin';

    public function getUserRole(): string {
        return match($this) {
            self::Lecturer => 'lecturer',
            self::Student => 'student',
            self::Admin => 'admin',
        };
    }
}
