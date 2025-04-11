<?php

namespace models;

enum UserRole: string {
    case STUDENT = 'student';
    case LECTURER = 'lecturer';
    case ADMIN = 'admin';
}


