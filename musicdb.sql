CREATE DATABASE IF NOT EXISTS music;
USE music;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    role ENUM('user','artist','admin') NOT NULL
);

CREATE TABLE albums (
    album_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL UNIQUE,
    picture_url VARCHAR(255),
    description TEXT,
    year YEAR,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE lists (
    list_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    picture_url VARCHAR(255),
    description TEXT,
    public BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE songs (
    song_id INT PRIMARY KEY AUTO_INCREMENT,
    album_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    duration INT NOT NULL,
    FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE
);

CREATE TABLE list_song (
    list_id INT NOT NULL,
    song_id INT NOT NULL,
    PRIMARY KEY (list_id, song_id),
    FOREIGN KEY (list_id) REFERENCES lists(list_id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE
);

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'admin', 'admin@gmail.com', '123', '2025-12-19 18:10:30', 'admin')