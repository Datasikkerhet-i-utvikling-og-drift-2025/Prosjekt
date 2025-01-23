-- Test data for schema creation er nå forskjellig og må eventuelt oppdateres

-- Insert users
INSERT INTO users (email, password_hash, user_type) VALUES
                                                        ('student1@example.com', 'hashedpassword1', 'student'),
                                                        ('student2@example.com', 'hashedpassword2', 'student'),
                                                        ('lecturer1@example.com', 'hashedpassword3', 'lecturer'),
                                                        ('lecturer2@example.com', 'hashedpassword4', 'lecturer'),
                                                        ('admin@example.com', 'hashedpassword5', 'admin');

-- Insert students
INSERT INTO students (student_id, first_name, last_name, study_program, cohort_year) VALUES
                                                                                         (1, 'Alice', 'Anderson', 'Computer Science', 2023),
                                                                                         (2, 'Bob', 'Brown', 'Cybersecurity', 2024);

-- Insert lecturers
INSERT INTO lecturers (lecturer_id, first_name, last_name, profile_image_path) VALUES
                                                                                   (3, 'Dr. Charlie', 'Clark', '/images/charlie.jpg'),
                                                                                   (4, 'Dr. Diana', 'Doe', NULL);

-- Insert courses
INSERT INTO courses (course_code, course_name, pin_code) VALUES
                                                             ('CS101', 'Introduction to Computer Science', '1234'),
                                                             ('CY201', 'Cybersecurity Basics', '5678');

-- Associate lecturers with courses
INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES
                                                          (3, 1), -- Dr. Charlie teaches CS101
                                                          (4, 2); -- Dr. Diana teaches CY201

-- Insert messages
INSERT INTO messages (course_id, student_id, message_text, is_anonymous) VALUES
                                                                             (1, 1, 'What is the deadline for Assignment 1?', FALSE),
                                                                             (2, 2, 'Can you explain the hashing algorithms?', TRUE);

-- Insert message responses
INSERT INTO message_responses (message_id, lecturer_id, response_text) VALUES
                                                                           (1, 3, 'The deadline for Assignment 1 is next Friday.'),
                                                                           (2, 4, 'Sure, I will cover hashing algorithms in the next lecture.');

-- Insert guest comments
INSERT INTO guest_comments (message_id, comment_text, user_id) VALUES
                                                                   (1, 'This question is relevant for me too!', NULL), -- Anonymous guest
                                                                   (2, 'Great explanation, thank you!', 1); -- Comment by a logged-in user

-- Insert reported messages
INSERT INTO reported_messages (message_id, report_reason) VALUES
    (2, 'Inappropriate language.');

-- Insert password reset tokens
INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES
                                                                   (1, 'resettoken123', '2025-01-31 23:59:59'),
                                                                   (2, 'resettoken456', '2025-01-31 23:59:59');
