create table if not exists users
(
    user_id       int auto_increment
        primary key,
    email         varchar(255)                        not null,
    password_hash varchar(255)                        not null,
    created_at    timestamp default CURRENT_TIMESTAMP null,
    first_name    varchar(90)                         not null,
    last_name     varchar(90)                         null,
    constraint email
        unique (email)
);

create table if not exists lecturers
(
    lecturer_id        int          not null
        primary key,
    profile_image_path varchar(255) null,
    constraint lecturers_ibfk_1
        foreign key (lecturer_id) references users (user_id)
);

create table if not exists courses
(
    course_id       int auto_increment
        primary key,
    course_code     varchar(20)                         not null,
    course_name     varchar(255)                        not null,
    pin_code        char(4)                             not null,
    created_at      timestamp default CURRENT_TIMESTAMP null,
    course_owner_fk int                                 null,
    constraint course_code
        unique (course_code),
    constraint courses_lecturers_lecturer_id_fk
        foreign key (course_owner_fk) references lecturers (lecturer_id)
);

create table if not exists messages
(
    message_id        int auto_increment
        primary key,
    course_id         int                                  not null,
    message_text      text                                 not null,
    created_at        timestamp  default CURRENT_TIMESTAMP null,
    user_id           int                                  null,
    is_anonymous      tinyint(1) default 0                 null,
    parent_message_id int                                  null,
    constraint course_fk
        foreign key (course_id) references courses (course_id),
    constraint parent_message_fk
        foreign key (parent_message_id) references messages (message_id)
);

create index idx_messages_course
    on messages (course_id);

create table if not exists password_reset_tokens
(
    token_id   int auto_increment
        primary key,
    user_id    int          not null,
    token      varchar(255) not null,
    expires_at timestamp    not null,
    constraint password_reset_tokens_ibfk_1
        foreign key (user_id) references users (user_id)
);

create index user_id
    on password_reset_tokens (user_id);

create table if not exists reported_messages
(
    report_id     int auto_increment
        primary key,
    message_id    int                                 not null,
    report_reason text                                not null,
    reported_at   timestamp default CURRENT_TIMESTAMP null,
    constraint reported_messages_ibfk_1
        foreign key (message_id) references messages (message_id)
);

create index idx_reported_messages_message
    on reported_messages (message_id);

create table if not exists students
(
    student_id    int          not null
        primary key,
    study_program varchar(100) not null,
    cohort_year   int          not null,
    constraint students_ibfk_1
        foreign key (student_id) references users (user_id)
);