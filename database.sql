CREATE DATABASE vps;
USE vps;

CREATE TABLE user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(50) NOT NULL,
  status TINYINT NOT NULL DEFAULT 1
);

INSERT INTO user (username, password, status) VALUES
('Student1', 'Student@123', 1),
('Student2', 'Student@456', 0);
