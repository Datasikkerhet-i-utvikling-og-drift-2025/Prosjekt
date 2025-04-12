-- Insert an admin user
INSERT INTO users (first_name, last_name, full_name, email, password, role)
VALUES
    ('Admin', 'User', 'Admin User', 'admin@admin.com', SHA2('admin', 256), 'admin'); -- Using SHA2 for password hashing

-- Insert lecturers
INSERT INTO users (first_name, last_name, full_name, email, password, role)
VALUES
    ('Lecturer', 'One', 'Lecturer One', 'lecturer1@example.com', SHA2('password123', 256), 'lecturer'),
    ('Lecturer', 'Two', 'Lecturer Two', 'lecturer2@example.com', SHA2('password123', 256), 'lecturer');

-- Insert students
INSERT INTO users (first_name, last_name, full_name, email, password, role, study_program, enrollment_year)
VALUES
    ('Student', 'One', 'Student One', 'student1@example.com', SHA2('password123', 256), 'student', 'Computer Science', 1),
    ('Student', 'Two', 'Student Two', 'student2@example.com', SHA2('password123', 256), 'student', 'Cyber Security', 2);

-- Insert courses
INSERT INTO courses (code, name, lecturer_id, pin_code)
VALUES
    ('CS101', 'Introduction to Computer Science', 2, '1234'),
    ('CYB201', 'Advanced Cyber Security', 3, '5678');

-- Insert messages
INSERT INTO messages (course_id, student_id, anonymous_id, content)
VALUES
    (1, 4, UUID(), 'Can you explain recursion in detail?'),
    (2, 5, UUID(), 'What are the best practices for password security?');

-- Insert comments
INSERT INTO comments (message_id, guest_name, content)
VALUES
    (1, 'Guest User', 'Recursion is like a function calling itself.'),
    (2, 'Anonymous Guest', 'Always use a strong password.');

-- Insert reports
INSERT INTO reports (message_id, reported_by, report_reason)
VALUES
    (1, 4, 'This message contains inappropriate content.'),
    (2, 5, 'Potential spam detected.');

-- Test script execution (display the data)
SELECT * FROM users;
SELECT * FROM courses;
SELECT * FROM messages;
SELECT * FROM comments;
SELECT * FROM reports;
