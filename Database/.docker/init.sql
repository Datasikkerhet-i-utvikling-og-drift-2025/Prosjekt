-- Users Table
CREATE TABLE users (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       name VARCHAR(100) NOT NULL,
                       email VARCHAR(100) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       role ENUM('student', 'lecturer', 'admin') NOT NULL,
                       study_program VARCHAR(100),
                       study_year INT,
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
