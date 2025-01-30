<?php



enum UserType: string {
    case STUDENT = 'student';
    case LECTURER = 'lecturer';
    case ADMIN = 'admin';
    case GUEST = 'guest';
}