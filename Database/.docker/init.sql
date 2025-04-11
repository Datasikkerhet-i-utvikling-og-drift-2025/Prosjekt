-- Users Table
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       first_name VARCHAR(100) NOT NULL,
                       last_name VARCHAR(100) NOT NULL,
                       full_name VARCHAR(100) NOT NULL,
                       email VARCHAR(100) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       role ENUM('student', 'lecturer', 'admin') NOT NULL,
                       study_program VARCHAR(100),
                       enrollment_year INT NULL,
                       image_path VARCHAR(255),
                       reset_token VARCHAR(255), -- Added for storing reset tokens
                       reset_token_created_at TIMESTAMP NULL, -- Added for tracking token creation time
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE courses (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         code VARCHAR(10) UNIQUE NOT NULL,
                         name VARCHAR(100) NOT NULL,
                         lecturer_id INT NOT NULL,
                         pin_code CHAR(4) NOT NULL,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (lecturer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages Table
CREATE TABLE messages (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          course_id INT NOT NULL,
                          student_id INT NOT NULL,
                          anonymous_id CHAR(36) NOT NULL,
                          content TEXT NOT NULL,
                          reply TEXT,
                          is_reported BOOLEAN DEFAULT FALSE,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
                          FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments Table
CREATE TABLE comments (
                          id INT AUTO_INCREMENT PRIMARY KEY,
                          message_id INT NOT NULL,
                          guest_name VARCHAR(100) NOT NULL,
                          content TEXT NOT NULL,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
);

-- Reports Table
CREATE TABLE reports (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         message_id INT NOT NULL,
                         reported_by INT,
                         report_reason TEXT,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
                         FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE SET NULL
);

delimiter //

-- GuestRepository

CREATE PROCEDURE addComment(IN messageId INT, IN guestName VARCHAR(100), IN contentText TEXT)
BEGIN
    INSERT INTO comments (message_id, guest_name, content, created_at)
    VALUES (messageId, guestName, contentText, NOW());
END //

CREATE PROCEDURE getCommentsByMessageId(IN messageId INT)
BEGIN
    SELECT id, message_id, guest_name, content, created_at 
    FROM comments 
    WHERE message_id = messageId 
    ORDER BY created_at;
END //

CREATE PROCEDURE getLecturerById(IN lecturerId INT)
BEGIN
    SELECT full_name, image_path FROM users WHERE id = lecturerId AND role = 'lecturer';
END //

CREATE PROCEDURE getCourseByPinCode(IN pinCode INT)
BEGIN
    SELECT id, code, name, pin_code, lecturer_id FROM courses WHERE pin_code = pinCode;
END //


-- For Lecturer

CREATE PROCEDURE createCourse(IN courseCode VARCHAR(10), IN courseName VARCHAR(100), IN lecturerId INT, IN pinCode CHAR(4))
BEGIN
    INSERT INTO courses (code, name, lecturer_id, pin_code, created_at)
    VALUES (courseCode, courseName, lecturerId, pinCode, NOW());
END //

CREATE PROCEDURE getCourses(IN lecturerId VARCHAR(255))
BEGIN
    SELECT id, code, name, pin_code, created_at 
    FROM courses 
    WHERE lecturer_id = lecturerId;
END //

CREATE PROCEDURE replyToMessage(IN messageId VARCHAR(255), IN replyContent TEXT)
BEGIN
    UPDATE messages 
    SET reply = replyContent, updated_at = NOW() 
    WHERE id = messageId;
END //

CREATE PROCEDURE getMessageById(IN messageId VARCHAR(255))
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
    FROM messages m
    WHERE m.id = messageId;
END //


-- For Student

CREATE PROCEDURE sendMessage(IN studentId VARCHAR(255), IN courseId VARCHAR(255), IN anonymousId VARCHAR(255), IN contentText TEXT)
BEGIN
    INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
    VALUES (studentId, courseId, anonymousId, contentText, NOW());
END //

CREATE PROCEDURE getMessagesByStudent(IN studentId VARCHAR(255))
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at,
           c.code AS course_code, c.name AS course_name
    FROM messages m
    JOIN courses c ON m.course_id = c.id
    WHERE m.student_id = studentId
    ORDER BY m.created_at DESC;
END //

CREATE PROCEDURE getMessageWithReply(IN messageId VARCHAR(255), IN studentId VARCHAR(255))
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
           c.code AS course_code, c.name AS course_name
    FROM messages m
    JOIN courses c ON m.course_id = c.id
    WHERE m.id = messageId AND m.student_id = studentId;
END //

CREATE PROCEDURE getAvailableCourses()
BEGIN
    SELECT id, code, name 
    FROM courses;
END //

-- For User

CREATE PROCEDURE createUser(
    IN firstName VARCHAR(100),
    IN lastName VARCHAR(100),
    IN fullName VARCHAR(100),
    IN emailValue VARCHAR(100),
    IN passwordValue VARCHAR(255),
    IN roleValue ENUM('student', 'lecturer', 'admin'),
    IN studyProgram VARCHAR(100),
    IN enrollmentYear INT,
    IN imagePath VARCHAR(255)
)
BEGIN
    INSERT INTO users (first_name, last_name, full_name, email, password, role, study_program, enrollment_year, image_path, created_at, updated_at)
    VALUES (firstName, lastName, fullName, emailValue, passwordValue, roleValue, studyProgram, enrollmentYear, imagePath, NOW(), NOW());
END //

CREATE PROCEDURE getUserByEmail(IN userEmail VARCHAR(255))
BEGIN
    SELECT * FROM users WHERE email = userEmail LIMIT 1;
END //

CREATE PROCEDURE updateUser(
    IN userId INT,
    IN firstName VARCHAR(100),
    IN lastName VARCHAR(100),
    IN fullName VARCHAR(100),
    IN emailValue VARCHAR(100),
    IN passwordValue VARCHAR(255),
    IN roleValue ENUM('student', 'lecturer', 'admin'),
    IN studyProgram VARCHAR(100),
    IN enrollmentYear INT,
    IN imagePath VARCHAR(255)
)
BEGIN
    UPDATE users
    SET first_name = firstName,
        last_name = lastName,
        full_name = fullName,
        email = emailValue,
        password = passwordValue,
        role = roleValue,
        study_program = studyProgram,
        enrollment_year = enrollmentYear,
        image_path = imagePath,
        updated_at = NOW()
    WHERE id = userId;
END //

CREATE PROCEDURE deleteUserById(IN userId VARCHAR(255))
BEGIN
    DELETE FROM users WHERE id = userId;
END //

CREATE PROCEDURE deleteUserByEmail(IN userEmail VARCHAR(255))
BEGIN
    DELETE FROM users WHERE email = userEmail;
END //

CREATE PROCEDURE getUserById(IN userId VARCHAR(255))
BEGIN
    SELECT * FROM users WHERE id = userId LIMIT 1;
END //

CREATE PROCEDURE getAllUsers()
BEGIN
    SELECT * FROM users;
END //

CREATE PROCEDURE savePasswordResetToken(IN userId VARCHAR(255), IN token VARCHAR(255))
BEGIN
    UPDATE users
    SET reset_token = token,
        reset_token_created_at = NOW()
    WHERE id = userId;
END //

CREATE PROCEDURE getUserByResetToken(IN token VARCHAR(255))
BEGIN
    SELECT *
    FROM users
    WHERE reset_token = token
      AND reset_token_created_at >= NOW() - INTERVAL 1 HOUR;
END //

CREATE PROCEDURE updatePasswordAndClearToken(IN userId VARCHAR(255), IN hashedPassword VARCHAR(255))
BEGIN
    UPDATE users
    SET password = hashedPassword,
        reset_token = NULL,
        reset_token_created_at = NULL
    WHERE id = userId;
END //

# --For Lecturer and Guest--

CREATE PROCEDURE reportMessage(IN messageId VARCHAR(255), IN reason TEXT)
BEGIN
    INSERT INTO reports (message_id, report_reason, created_at)
    VALUES (messageId, reason, NOW());
END //

CREATE PROCEDURE getMessagesForCourse(IN courseId VARCHAR(255))
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
    FROM messages m
    WHERE m.course_id = courseId;
END //

delimiter ;


CREATE USER 'admin'@'mysql' IDENTIFIED BY 'adminPass';
GRANT ALL PRIVILEGES
ON database.*
TO 'admin'@'mysql'
IDENTIFIED BY 'adminPass'
WITH GRANT OPTION;


CREATE USER 'student'@'mysql' IDENTIFIED BY 'studentPass';
GRANT EXECUTE ON PROCEDURE sendMessage TO 'student'@'mysql';
GRANT EXECUTE ON PROCEDURE getMessageWithReply TO 'student'@'mysql';
GRANT EXECUTE ON PROCEDURE getAvailableCourses TO 'student'@'mysql';
GRANT EXECUTE ON PROCEDURE getMessagesByStudent TO 'student'@'mysql';

CREATE USER 'lecturer'@'mysql' IDENTIFIED BY 'lecturerPass';
GRANT EXECUTE ON PROCEDURE createCourse TO 'lecturer'@'mysql';
GRANT EXECUTE ON PROCEDURE getCourses TO 'lecturer'@'mysql';
GRANT EXECUTE ON PROCEDURE replyToMessage TO 'lecturer'@'mysql';
GRANT EXECUTE ON PROCEDURE getMessageById TO 'lecturer'@'mysql';
GRANT EXECUTE ON PROCEDURE reportMessage TO 'lecturer'@'mysql';
GRANT EXECUTE ON PROCEDURE getMessagesForCourse TO 'lecturer'@'mysql';

CREATE USER 'guest'@'mysql' IDENTIFIED BY 'guestPass';
GRANT EXECUTE ON PROCEDURE addComment TO 'guest'@'mysql';
GRANT EXECUTE ON PROCEDURE getCommentsByMessageId TO 'guest'@'mysql';
GRANT EXECUTE ON PROCEDURE getLecturerById TO 'guest'@'mysql';
GRANT EXECUTE ON PROCEDURE getCourseByPinCode TO 'guest'@'mysql';
GRANT EXECUTE ON PROCEDURE reportMessage TO 'guest'@'mysql';
GRANT EXECUTE ON PROCEDURE getMessagesForCourse TO 'guest'@'mysql';

CREATE USER 'user'@'mysql' IDENTIFIED BY 'userPass';
GRANT EXECUTE ON PROCEDURE createUser TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE getUserByEmail TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE updateUser TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE deleteUserById TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE deleteUserByEmail TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE getUserById TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE getAllUsers TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE savePasswordResetToken TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE getUserByResetToken TO 'user'@'mysql';
GRANT EXECUTE ON PROCEDURE updatePasswordAndClearToken TO 'user'@'mysql';