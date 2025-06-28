-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2025 at 11:32 PM
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
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','guard_admin','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `name`, `email`, `password`, `role`) VALUES
(1, 'admin', 'ECW admin', 'admin@ecw.lk', '$2y$10$2Kt69udia/eejzTd48D2zuiTb90TyXmBVFJU/8Q2xJL5QJONcICXi', 'admin'),
(2, 'superadmin', 'Super Admin', 'super@ecw.lk', '$2y$10$2Kt69udia/eejzTd48D2zuiTb90TyXmBVFJU/8Q2xJL5QJONcICXi', 'super_admin'),
(3, 'guardadmin', 'Guard Room Admin', 'guard@ecw.lk', '$2y$10$2Kt69udia/eejzTd48D2zuiTb90TyXmBVFJU/8Q2xJL5QJONcICXi', 'guard_admin');

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

--
-- Dumping data for table `meal_issuance_log`
--

INSERT INTO `meal_issuance_log` (`id`, `staff_id`, `meal_type`, `issued_by`, `issued_at`, `method`) VALUES
(1, 1, 'lunch', 4, '2025-06-28 03:04:41', 'manual'),
(2, 2, 'lunch', 4, '2025-06-28 03:06:28', 'manual');

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

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `staff_id`, `name`, `phone_number`, `staff_type`, `qr_code`, `meal_preferences`) VALUES
(1, 'ECW-111', 'Test 111', '456453453', 'ECW', 'ECW-111', NULL),
(2, 'INT-25', 'Test 25', '48454654', 'INT', 'INT-25', NULL);

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
-- Dumping data for table `staff_meals`
--

INSERT INTO `staff_meals` (`id`, `staff_id`, `meal_date`, `breakfast`, `lunch`, `dinner`, `date`, `egg`, `chicken`, `vegetarian`, `manual_order`, `breakfast_received`, `lunch_received`, `dinner_received`, `manual_breakfast`, `manual_lunch`, `manual_dinner`, `issued_by_pin`, `extra_reason`) VALUES
(1, 2, '2025-06-28', 0, 0, 1, '2025-06-28', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, NULL, NULL),
(2, 2, '2025-06-29', 1, 0, 0, '2025-06-28', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL),
(3, 1, '2025-06-28', 0, 1, 1, '2025-06-28', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `visitor_orders`
--

CREATE TABLE `visitor_orders` (
  `id` int(11) NOT NULL,
  `visitor_name` varchar(100) NOT NULL,
  `meal_date` date NOT NULL,
  `breakfast` tinyint(1) DEFAULT 0,
  `lunch` tinyint(1) DEFAULT 0,
  `dinner` tinyint(1) DEFAULT 0,
  `egg` tinyint(1) DEFAULT 0,
  `chicken` tinyint(1) DEFAULT 0,
  `vegetarian` tinyint(1) DEFAULT 0,
  `ordered_by_admin` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `breakfast_received` tinyint(1) DEFAULT 0,
  `lunch_received` tinyint(1) DEFAULT 0,
  `dinner_received` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_orders`
--

INSERT INTO `visitor_orders` (`id`, `visitor_name`, `meal_date`, `breakfast`, `lunch`, `dinner`, `egg`, `chicken`, `vegetarian`, `ordered_by_admin`, `created_at`, `breakfast_received`, `lunch_received`, `dinner_received`) VALUES
(1, 'Nasran', '2025-06-28', 1, 1, 0, 0, 1, 0, 'guardadmin', '2025-06-29 00:49:31', 0, 0, 0),
(2, 'Nasran 2', '2025-06-28', 1, 0, 1, 1, 0, 0, 'guardadmin', '2025-06-29 00:49:43', 0, 0, 0),
(3, 'nasran', '2025-06-29', 0, 1, 1, 0, 1, 0, 'guardadmin', '2025-06-29 00:55:02', 0, 0, 1),
(4, 'nasran84646', '2025-06-29', 1, 0, 0, 1, 0, 0, 'guardadmin', '2025-06-29 00:55:29', 0, 0, 0),
(5, '12321321', '2025-06-29', 1, 1, 0, 0, 1, 0, 'guardadmin', '2025-06-29 01:17:49', 1, 0, 0);

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
-- Indexes for table `visitor_orders`
--
ALTER TABLE `visitor_orders`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_pins`
--
ALTER TABLE `order_pins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff_meals`
--
ALTER TABLE `staff_meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `visitor_orders`
--
ALTER TABLE `visitor_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
