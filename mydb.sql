-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Jun 02, 2025 at 02:40 PM
-- Server version: 8.0.42
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`id`, `name`, `phone`, `is_active`) VALUES
(1, 'Иванов Иван', '+79001234567', 1),
(2, 'Петров Петр', '+79009876543', 1),
(3, 'Сидоров Сидор', '+79007894561', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `delivery_type` varchar(20) DEFAULT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `house` varchar(10) DEFAULT NULL,
  `apartment` varchar(10) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `driver_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `delivery_type`, `client_name`, `city`, `street`, `house`, `apartment`, `phone`, `price`, `user_id`, `created_at`, `driver_id`) VALUES
(3, 'standard', 'Стэн Ли', 'фыв', 'фыв', '123', '123', '+71233321123', 123.00, 1, '2025-06-02 11:45:54', NULL),
(4, 'express', 'asfas', 'Fasd', '11', '1', '1', '+71111111111', 1.00, 1, '2025-06-02 11:46:50', 1),
(5, 'standard', 'Стэн Ли', 'фыв', 'фыв', '123', '1', '+72133333333', 333.00, 1, '2025-06-02 14:49:02', 2),
(6, 'standard', 'фыв', 'Лог', 'Лесная', '1', '123', '+71222222222', 123.00, 1, '2025-06-02 15:32:43', NULL),
(7, 'standard', 'Стэн Ли', 'Фаыц', '123', '123', '123', '+71231111111', 213.00, 3, '2025-06-02 15:35:13', NULL),
(8, 'standard', 'Стэн Ли', 'Лог', 'Лесная', 'e', '2', '+72222222222', 222.00, 1, '2025-06-02 16:21:03', NULL),
(9, 'standard', 'ФЫв', 'фыв', 'фыв', 'фыв', '1', '+71111111111', 11.00, 1, '2025-06-02 16:52:01', NULL),
(10, 'standard', 'фыв', 'фыв', 'фыв', '11', '', '+71111111111', 11.00, 1, '2025-06-02 16:52:42', NULL),
(11, 'standard', 'фыв', 'фыв', 'фыв', 'у', '', '+72222222222', 23.00, 1, '2025-06-02 16:53:24', NULL),
(12, 'standard', 'фцу', 'цйфу', 'фывфы', 'уу', '', '+72133333333', 21231.00, 1, '2024-06-02 16:53:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, '1', '$2y$13$.q7LRxpditoHU4CaHnitA.dal.dQzuyKOYvpUliNV/HFNNz4TxYP2', 'user'),
(2, '0', '$2y$13$JBoluq6tU8AaKyBIOpzVQuNtsKV.miOYW9fDA.YDP.cf.V.aqk6gC', 'admin'),
(3, '2', '$2y$13$X9sfvk.QlmetPMOydG/NneN3kE8W46rHP5ipGrE/PcxzRZ34bcY7u', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`),
  ADD KEY `FK_orders_driver` (`driver_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `FK_orders_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`id`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
