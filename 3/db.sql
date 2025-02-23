CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fio VARCHAR(150) NOT NULL,
    tel VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    bdate DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    bio TEXT,
    ccheck BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE abilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE user (
    user_id INT NOT NULL,
    lang_id INT NOT NULL,
    PRIMARY KEY (user_id, lang_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES abilities(id) ON DELETE CASCADE
);

INSERT INTO abilities (name) VALUES
('Pascal'), ('C'), ('C++'), ('JavaScript'), ('PHP'), ('Python'), ('Java'), ('Haskell'), ('Clojure'), ('Prolog'), ('Scala'), ('Go');