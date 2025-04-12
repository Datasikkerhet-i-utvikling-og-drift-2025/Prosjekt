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
     * Creates a user object based on the role provided in the user data array.
     *
     * This factory method dynamically creates an instance of `Student`, `Lecturer`, or `Admin`
     * depending on the `role` field in the `$userData` array.
     *
     * @param array $userData Associative array containing user information, including a `role` key.
     *
     * @return Admin|Lecturer|Student Returns an instance of `Admin`, `Lecturer`, or `Student`
     *                                based on the `role` value in `$userData`.
     *
     * @throws DateMalformedStringException If date formatting is incorrect in user data.
     * @throws InvalidArgumentException If an invalid role is provided in `$userData`.
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