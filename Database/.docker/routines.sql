CREATE USER 'student'@'mysql' [IDENTIFIED BY 'studentPass' ]

CREATE USER 'lecturer'@'mysql' [IDENTIFIED BY 'lecturerPass' ]

CREATE USER 'guest'@'mysql' [IDENTIFIED BY 'guestPass' ]

CREATE USER 'admin'@'mysql' [IDENTIFIED BY 'adminPass' ]


delimiter // ;

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

CREATE PROCEDURE addComment(IN messageId INT, IN guestName VARCHAR(100), IN content TEXT)
BEGIN
    INSERT INTO comments (message_id, guest_name, content, created_at)
    VALUES (messageId, guestName, content, NOW());
END //

CREATE PROCEDURE getCommentsByMessageId(IN messageId INT)
BEGIN
    SELECT id, message_id, guest_name, content, created_at
    FROM comments
    WHERE message_id = messageId
    ORDER BY created_at ASC;
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
        pin_code = pinCode,
        updated_at = NOW()
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

CREATE PROCEDURE createMessage(IN studentId INT, IN courseId INT, IN anonymousId CHAR(36), IN content TEXT)
BEGIN
    INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at, is_reported)
    VALUES (studentId, courseId, anonymousId, content, NOW(), 0);
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

CREATE PROCEDURE getMessageById(IN messageId INT)
BEGIN
    SELECT m.id AS message_id, m.content, m.reply, m.created_at,
           c.code AS course_code, c.name AS course_name
    FROM messages m
             JOIN courses c ON m.course_id = c.id
    WHERE m.id = messageId;
END //

CREATE PROCEDURE updateMessageReply(IN messageId INT, IN replyContent TEXT)
BEGIN
    UPDATE messages SET reply = replyContent, updated_at = NOW() WHERE id = messageId;
END //

CREATE PROCEDURE reportMessageById(IN messageId INT, IN reason TEXT)
BEGIN
    UPDATE messages SET is_reported = 1 WHERE id = messageId;
END //

CREATE PROCEDURE deleteMessageById(IN messageId INT)
BEGIN
    DELETE FROM messages WHERE id = messageId;
END //

CREATE PROCEDURE getPublicMessages()
BEGIN
    SELECT id AS message_id, content, created_at FROM messages;
END //

CREATE PROCEDURE updateMessage(IN message_id INT, IN content TEXT)
BEGIN
    UPDATE messages SET content = content, updated_at = NOW() WHERE id = message_id;
END //


-- StudentRepository

CREATE PROCEDURE sendMessage(IN studentId VARCHAR(255), IN courseId VARCHAR(255), IN anonymousId VARCHAR(255), IN content TEXT)
BEGIN
    INSERT INTO messages (student_id, course_id, anonymous_id, content, created_at)
    VALUES (studentId, courseId, anonymousId, content, NOW());
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

-- UserRepository

CREATE PROCEDURE createUser(
    IN firstName VARCHAR(100),
    IN lastName VARCHAR(100),
    IN fullName VARCHAR(100),
    IN email VARCHAR(100),
    IN password VARCHAR(255),
    IN role ENUM('student', 'lecturer', 'admin'),
    IN studyProgram VARCHAR(100),
    IN enrollmentYear INT,
    IN imagePath VARCHAR(255)
)
BEGIN
    INSERT INTO users (first_name, last_name, full_name, email, password, role, study_program, enrollment_year, image_path, created_at, updated_at)
    VALUES (firstName, lastName, fullName, email, password, role, studyProgram, enrollmentYear, imagePath, NOW(), NOW());
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
    IN email VARCHAR(100),
    IN password VARCHAR(255),
    IN role ENUM('student', 'lecturer', 'admin'),
    IN studyProgram VARCHAR(100),
    IN enrollmentYear INT,
    IN imagePath VARCHAR(255)
)
BEGIN
    UPDATE users
    SET first_name = firstName,
        last_name = lastName,
        full_name = fullName,
        email = email,
        password = password,
        role = role,
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
delimiter ; //