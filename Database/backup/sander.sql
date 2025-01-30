-- Bruker tabellen - base tabell for alle typer brukere
CREATE TABLE users (
                       user_id INT PRIMARY KEY AUTO_INCREMENT,
                       email VARCHAR(255) UNIQUE NOT NULL,
                       password_hash VARCHAR(255) NOT NULL,
                       user_type ENUM('student', 'lecturer', 'admin') NOT NULL,
                       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Student tabellen
CREATE TABLE students (
                          student_id INT PRIMARY KEY,
                          first_name VARCHAR(100) NOT NULL,
                          last_name VARCHAR(100) NOT NULL,
                          study_program VARCHAR(100) NOT NULL,
                          cohort_year INT NOT NULL,
                          FOREIGN KEY (student_id) REFERENCES users(user_id)
);

-- Foreleser tabellen
CREATE TABLE lecturers (
                           lecturer_id INT PRIMARY KEY,
                           first_name VARCHAR(100) NOT NULL,
                           last_name VARCHAR(100) NOT NULL,
                           profile_image_path VARCHAR(255),
                           FOREIGN KEY (lecturer_id) REFERENCES users(user_id)
);

-- Emne tabellen
CREATE TABLE courses (
                         course_id INT PRIMARY KEY AUTO_INCREMENT,
                         course_code VARCHAR(20) UNIQUE NOT NULL,
                         course_name VARCHAR(255) NOT NULL,
                         pin_code CHAR(4) NOT NULL,
                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Kobling mellom foreleser og emne (mange-til-mange)
CREATE TABLE lecturer_courses (
                                  lecturer_id INT,
                                  course_id INT,
                                  PRIMARY KEY (lecturer_id, course_id),
                                  FOREIGN KEY (lecturer_id) REFERENCES lecturers(lecturer_id),
                                  FOREIGN KEY (course_id) REFERENCES courses(course_id)
);

-- Meldinger tabellen
CREATE TABLE messages (
                          message_id INT PRIMARY KEY AUTO_INCREMENT,
                          course_id INT NOT NULL,
                          student_id INT NOT NULL,  -- For admin å kunne spore, men ikke vises til andre
                          message_text TEXT NOT NULL,
                          is_anonymous BOOLEAN DEFAULT TRUE,
                          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (course_id) REFERENCES courses(course_id),
                          FOREIGN KEY (student_id) REFERENCES students(student_id)
);

-- Svar på meldinger
CREATE TABLE message_responses (
                                   response_id INT PRIMARY KEY AUTO_INCREMENT,
                                   message_id INT NOT NULL,
                                   lecturer_id INT NOT NULL,
                                   response_text TEXT NOT NULL,
                                   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                   FOREIGN KEY (message_id) REFERENCES messages(message_id),
                                   FOREIGN KEY (lecturer_id) REFERENCES lecturers(lecturer_id)
);

-- Kommentarer fra gjestebrukere
CREATE TABLE guest_comments (
                                comment_id INT PRIMARY KEY AUTO_INCREMENT,
                                message_id INT NOT NULL,
                                comment_text TEXT NOT NULL,
                                user_id INT,  -- Null hvis anonym, ellers koblet til innlogget bruker
                                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                FOREIGN KEY (message_id) REFERENCES messages(message_id),
                                FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Rapporterte meldinger
CREATE TABLE reported_messages (
                                   report_id INT PRIMARY KEY AUTO_INCREMENT,
                                   message_id INT NOT NULL,
                                   report_reason TEXT NOT NULL,
                                   reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                   FOREIGN KEY (message_id) REFERENCES messages(message_id)
);

-- Passord reset tokens
CREATE TABLE password_reset_tokens (
                                       token_id INT PRIMARY KEY AUTO_INCREMENT,
                                       user_id INT NOT NULL,
                                       token VARCHAR(255) NOT NULL,
                                       expires_at TIMESTAMP NOT NULL,
                                       FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Indekser for optimalisering
CREATE INDEX idx_messages_course ON messages(course_id);
CREATE INDEX idx_message_responses_message ON message_responses(message_id);
CREATE INDEX idx_guest_comments_message ON guest_comments(message_id);
CREATE INDEX idx_reported_messages_message ON reported_messages(message_id);
