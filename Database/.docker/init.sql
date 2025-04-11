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

CREATE USER 'student'@'mysql' IDENTIFIED BY 'studentPass';

CREATE USER 'lecturer'@'mysql' IDENTIFIED BY 'lecturerPass';

CREATE USER 'guest'@'mysql' IDENTIFIED BY 'guestPass';

CREATE USER 'admin'@'mysql' IDENTIFIED BY 'adminPass';


delimiter //

-- AdminRepository

CREATE PROCEDURE deleteUserById(IN userId VARCHAR(255))
BEGIN
    DELETE FROM users WHERE id = userId;
END //

CREATE PROCEDURE deleteMessageById(IN messageId VARCHAR(255))
BEGIN
    DELETE FROM messages WHERE id = messageId;
END //

CREATE PROCEDURE updateMessageContent(IN messageId VARCHAR(255), IN newContent TEXT)
BEGIN
    UPDATE messages SET content = newContent, updated_at = NOW() WHERE id = messageId;
END //

CREATE PROCEDURE getAllReportedMessages()
BEGIN
    SELECT m.id AS message_id, m.content, r.report_reason, u.first_name AS reported_by, m.created_at
    FROM messages m
    LEFT JOIN reports r ON m.id = r.message_id
    LEFT JOIN users u ON r.reported_by = u.id;
END //

CREATE PROCEDURE getAllUsersByRole(IN userRole VARCHAR(255))
BEGIN
    SELECT * FROM users WHERE role = userRole;
END //

CREATE PROCEDURE findMessageSender(IN messageId VARCHAR(255))
BEGIN
    SELECT m.id AS message_id, m.content, u.id AS sender_id, u.first_name, u.email, u.study_program, u.enrollment_year
    FROM messages m
    JOIN users u ON m.student_id = u.id
    WHERE m.id = messageId;
END //


-- CommentRepository

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

CREATE PROCEDURE deleteComment(IN commentId INT)
BEGIN
    DELETE FROM comments 
    WHERE id = commentId;
END //


-- CourseRepository

CREATE PROCEDURE createCourse(IN courseCode VARCHAR(10), IN courseName VARCHAR(100), IN lecturerId INT, IN pinCode CHAR(4))
BEGIN
    INSERT INTO courses (code, name, lecturer_id, pin_code, created_at)
    VALUES (courseCode, courseName, lecturerId, pinCode, NOW());
END //

CREATE PROCEDURE getCourseById(IN courseId INT)
BEGIN
    SELECT * FROM courses WHERE id = courseId;
END //

CREATE PROCEDURE getAllCourses()
BEGIN
    SELECT * FROM courses;
END //

CREATE PROCEDURE updateCourse(IN courseId INT, IN courseCode VARCHAR(10), IN courseName VARCHAR(100), IN lecturerId INT, IN pinCode CHAR(4))
BEGIN
    UPDATE courses
    SET code = courseCode,
        name = courseName,
        lecturer_id = lecturerId,
        pin_code = pinCode
    WHERE id = courseId;
END //

CREATE PROCEDURE deleteCourse(IN courseId INT)
BEGIN
    DELETE FROM courses WHERE id = courseId;
END //


-- LecturerRepository

CREATE PROCEDURE getCourses(IN lecturerId VARCHAR(255))
BEGIN
    SELECT id, code, name, pin_code, created_at 
    FROM courses 
    WHERE lecturer_id = lecturerId;
END //

CREATE PROCEDURE getMessagesForCourse(IN courseId VARCHAR(255))
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
    FROM messages m
    WHERE m.course_id = courseId;
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

CREATE PROCEDURE reportMessage(IN messageId VARCHAR(255), IN reason TEXT)
BEGIN
    INSERT INTO reports (message_id, report_reason, created_at)
    VALUES (messageId, reason, NOW());
END //


-- MessageRepository

CREATE PROCEDURE createMessage(IN studentId INT, IN courseId INT, IN anonymousId CHAR(36), IN contentText TEXT)
BEGIN
    INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at, is_reported)
    VALUES (studentId, courseId, anonymousId, contentText, NOW(), 0);
END //

CREATE PROCEDURE getMessagesByCourse(IN courseId INT)
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at, m.anonymous_id
    FROM messages m WHERE m.course_id = courseId;
END //

CREATE PROCEDURE getMessagesByStudent(IN studentId INT)
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at, 
           c.code AS course_code, c.name AS course_name
    FROM messages m
    JOIN courses c ON m.course_id = c.id
    WHERE m.student_id = studentId;
END //

#CREATE PROCEDURE getMessageById(IN messageId INT)
#BEGIN
#    SELECT m.id AS message_id, m.content, m.reply, m.created_at,
#           c.code AS course_code, c.name AS course_name
#    FROM messages m
#    JOIN courses c ON m.course_id = c.id
#    WHERE m.id = messageId;
#END //

CREATE PROCEDURE updateMessageReply(IN messageId INT, IN replyContent TEXT)
BEGIN
    UPDATE messages SET reply = replyContent, updated_at = NOW() WHERE id = messageId;
END //

CREATE PROCEDURE reportMessageById(IN messageId INT)
BEGIN
    UPDATE messages SET is_reported = 1 WHERE id = messageId;
    # FIXME legg til report message
END //

#CREATE PROCEDURE deleteMessageById(IN messageId INT)
#BEGIN
#    DELETE FROM messages WHERE id = messageId;
#END //

CREATE PROCEDURE getPublicMessages()
BEGIN
    SELECT id AS message_id, content, created_at FROM messages;
END //

CREATE PROCEDURE updateMessage(IN message_id INT, IN contentText TEXT)
BEGIN
    UPDATE messages SET content = contentText, updated_at = NOW() WHERE id = message_id;
END //


-- StudentRepository

CREATE PROCEDURE sendMessage(IN studentId VARCHAR(255), IN courseId VARCHAR(255), IN anonymousId VARCHAR(255), IN contentText TEXT)
BEGIN
    INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
    VALUES (studentId, courseId, anonymousId, contentText, NOW());
END //

#CREATE PROCEDURE getMessagesByStudent(IN studentId VARCHAR(255))
#BEGIN
#    SELECT m.id AS message_id, m.content, m.reply, m.created_at,
#           c.code AS course_code, c.name AS course_name
#    FROM messages m
#    JOIN courses c ON m.course_id = c.id
#    WHERE m.student_id = studentId
#    ORDER BY m.created_at DESC;
#END //

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

-- UserRepository

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

#CREATE PROCEDURE deleteUserById(IN userId VARCHAR(255))
#BEGIN
#    DELETE FROM users WHERE id = userId;
#END //

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
delimiter ;

