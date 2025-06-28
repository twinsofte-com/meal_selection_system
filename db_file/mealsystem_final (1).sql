-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 10:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `attendance_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `name`, `email`, `password`) VALUES
(1, 'admin', 'ECW admin', 'admin@ecw.lk', '$2y$10$2Kt69udia/eejzTd48D2zuiTb90TyXmBVFJU/8Q2xJL5QJONcICXi');

-- --------------------------------------------------------

--
-- Table structure for table `extra_meal_issues`
--

CREATE TABLE `extra_meal_issues` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `breakfast` tinyint(1) DEFAULT 0,
  `lunch` tinyint(1) DEFAULT 0,
  `dinner` tinyint(1) DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `issued_by_pin` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `issue_pins`
--

CREATE TABLE `issue_pins` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `pin_code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issue_pins`
--

INSERT INTO `issue_pins` (`id`, `role`, `pin_code`) VALUES
(2, 'issue', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `meal_confirmation`
--

CREATE TABLE `meal_confirmation` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_issuance`
--

CREATE TABLE `meal_issuance` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `meal_type` enum('Breakfast','Lunch','Dinner') NOT NULL,
  `confirmed` tinyint(1) DEFAULT 0,
  `issued_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meal_issuance_log`
--

CREATE TABLE `meal_issuance_log` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner') NOT NULL,
  `issued_by` int(11) NOT NULL,
  `issued_at` datetime DEFAULT current_timestamp(),
  `method` enum('scan','manual') DEFAULT 'scan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_pins`
--

CREATE TABLE `order_pins` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `pin_code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_pins`
--

INSERT INTO `order_pins` (`id`, `role`, `pin_code`) VALUES
(4, 'order', '123456');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `staff_id` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `staff_type` enum('ECW','INT') NOT NULL DEFAULT 'ECW',
  `qr_code` varchar(255) DEFAULT NULL,
  `meal_preferences` set('Breakfast','Lunch','Dinner') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_meals`
--

CREATE TABLE `staff_meals` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `meal_date` date NOT NULL,
  `breakfast` tinyint(1) DEFAULT 0,
  `lunch` tinyint(1) DEFAULT 0,
  `dinner` tinyint(1) DEFAULT 0,
  `date` date NOT NULL,
  `egg` tinyint(1) NOT NULL DEFAULT 0,
  `chicken` tinyint(1) NOT NULL DEFAULT 0,
  `vegetarian` tinyint(1) NOT NULL DEFAULT 0,
  `manual_order` tinyint(1) DEFAULT 0,
  `breakfast_received` tinyint(1) NOT NULL DEFAULT 0,
  `lunch_received` tinyint(1) NOT NULL DEFAULT 0,
  `dinner_received` tinyint(1) NOT NULL DEFAULT 0,
  `manual_breakfast` tinyint(1) NOT NULL DEFAULT 0,
  `manual_lunch` tinyint(1) NOT NULL DEFAULT 0,
  `manual_dinner` tinyint(1) NOT NULL DEFAULT 0,
  `issued_by_pin` int(11) DEFAULT NULL,
  `extra_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `extra_meal_issues`
--
ALTER TABLE `extra_meal_issues`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `issue_pins`
--
ALTER TABLE `issue_pins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_confirmation`
--
ALTER TABLE `meal_confirmation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meal_confirmation_ibfk_1` (`staff_id`);

--
-- Indexes for table `meal_issuance`
--
ALTER TABLE `meal_issuance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `meal_issuance_log`
--
ALTER TABLE `meal_issuance_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `order_pins`
--
ALTER TABLE `order_pins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`),
  ADD UNIQUE KEY `staff_id` (`staff_id`);

--
-- Indexes for table `staff_meals`
--
ALTER TABLE `staff_meals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_meal` (`staff_id`,`meal_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `extra_meal_issues`
--
ALTER TABLE `extra_meal_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `issue_pins`
--
ALTER TABLE `issue_pins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `meal_confirmation`
--
ALTER TABLE `meal_confirmation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `meal_issuance`
--
ALTER TABLE `meal_issuance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_issuance_log`
--
ALTER TABLE `meal_issuance_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_pins`
--
ALTER TABLE `order_pins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff_meals`
--
ALTER TABLE `staff_meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meal_confirmation`
--
ALTER TABLE `meal_confirmation`
  ADD CONSTRAINT `meal_confirmation_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meal_issuance`
--
ALTER TABLE `meal_issuance`
  ADD CONSTRAINT `meal_issuance_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `meal_issuance_log`
--
ALTER TABLE `meal_issuance_log`
  ADD CONSTRAINT `meal_issuance_log_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `staff_meals`
--
ALTER TABLE `staff_meals`
  ADD CONSTRAINT `staff_meals_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
