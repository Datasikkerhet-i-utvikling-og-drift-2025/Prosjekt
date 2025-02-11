<?php

namespace factories;

use DateMalformedStringException;
use InvalidArgumentException;
use models\Admin;
use models\Lecturer;
use models\Student;


class UserFactory
{
    /**
     * @throws DateMalformedStringException
     */
    public static function createUser(array $userData): Admin|Lecturer|Student
    {
        return match ($userData['role']) {
            'student'  => new Student($userData),
            'lecturer' => new Lecturer($userData),
            'admin'    => new Admin($userData),
            default    => throw new InvalidArgumentException("Invalid user role:" . $userData['role']),
        };
    }

}