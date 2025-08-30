--1st initial code for Student Management System Database
CREATE DATABASE student_management_system;

USE student_management_system;

-- Table for all users (Admin, Teacher, Student)
CREATE TABLE users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL
);

-- Table for classes/subjects
CREATE TABLE classes (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(100) NOT NULL UNIQUE,
    teacher_id INT(11),
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Table to store grades for students
CREATE TABLE grades (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    student_id INT(11) NOT NULL,
    class_id INT(11) NOT NULL,
    grade_value VARCHAR(10) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);


--2nd code for Student Management System Database
USE student_management_system;

-- Table to link students to classes
CREATE TABLE student_classes (
    student_id INT(11) NOT NULL,
    class_id INT(11) NOT NULL,
    PRIMARY KEY (student_id, class_id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
);

-- For Initial Admin User
INSERT INTO `users` (`first_name`, `last_name`, `email`, `password`, `role`) VALUES ('Admin', 'User', 'admin@example.com', '$2y$10$tF.D7sX3bK8.jH7gQ3cQd.kO4hQ4q8.f4x5fC0X.b.L7c0zL4L.e.', 'admin');


-- last code for Student Management System Database
ALTER TABLE classroom_posts ADD COLUMN file_path VARCHAR(255) NULL AFTER post_content;
ALTER TABLE assignments ADD COLUMN file_path VARCHAR(255) NULL AFTER deadline;