-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2025 at 09:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookverse`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `publication_year` year(4) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `pages` int(11) DEFAULT NULL,
  `synopsis` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `genre`, `publication_year`, `publisher`, `pages`, `synopsis`, `cover_image`, `created_at`, `updated_at`) VALUES
(1, 'Cien años de soledad', 'Gabriel García Márquez', '9780307474728', 'Realismo mágico', '1967', 'Editorial Sudamericana', 417, 'La novela narra la historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo.', 'cien_anos_soledad.jpg', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(2, 'Don Quijote de la Mancha', 'Miguel de Cervantes', '9788424902402', 'Novela', '0000', 'Editorial Espasa', 863, 'Las aventuras de un hidalgo manchego que pierde la razón por leer demasiados libros de caballerías.', 'don_quijote.jpg', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(3, '1984', 'George Orwell', '9780451524935', 'Distopía', '1949', 'Secker & Warburg', 328, 'Una novela distópica sobre un régimen totalitario que controla todos los aspectos de la vida.', '1984.jpg', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(4, 'El Principito', 'Antoine de Saint-Exupéry', '9782070408504', 'Literatura infantil', '1943', 'Reynal & Hitchcock', 96, 'La historia de un pequeño príncipe que viaja por el universo visitando diferentes planetas.', 'principito.jpg', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(5, 'Orgullo y prejuicio', 'Jane Austen', '9780141439518', 'Romance', '0000', 'T. Egerton', 432, 'La historia de Elizabeth Bennet y su compleja relación con el aparentemente arrogante Sr. Darcy.', 'orgullo_prejuicio.jpg', '2025-05-23 07:05:48', '2025-05-23 07:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `interactions`
--

CREATE TABLE `interactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `interaction_type` enum('like','dislike') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interactions`
--

INSERT INTO `interactions` (`id`, `user_id`, `book_id`, `interaction_type`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(2, 2, 2, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(3, 2, 3, 'dislike', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(4, 3, 1, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(5, 3, 4, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(6, 4, 1, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(7, 4, 2, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(8, 4, 5, 'like', '2025-05-23 07:05:48', '2025-05-23 07:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@bookverse.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador del Sistema', 'admin', 'active', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(2, 'juan_lector', 'juan@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'user', 'active', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(3, 'maria_books', 'maria@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María González', 'user', 'active', '2025-05-23 07:05:48', '2025-05-23 07:05:48'),
(4, 'carlos_reader', 'carlos@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Carlos Rodríguez', 'user', 'active', '2025-05-23 07:05:48', '2025-05-23 07:05:48');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `idx_books_title` (`title`),
  ADD KEY `idx_books_author` (`author`),
  ADD KEY `idx_books_genre` (`genre`),
  ADD KEY `idx_books_year` (`publication_year`);

--
-- Indexes for table `interactions`
--
ALTER TABLE `interactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_book_interaction` (`user_id`,`book_id`),
  ADD KEY `idx_interactions_user` (`user_id`),
  ADD KEY `idx_interactions_book` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `interactions`
--
ALTER TABLE `interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `interactions`
--
ALTER TABLE `interactions`
  ADD CONSTRAINT `interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `interactions_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
