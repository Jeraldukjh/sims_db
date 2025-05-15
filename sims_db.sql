-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 07:36 PM
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
-- Database: `sims_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ApproveReturn` (IN `p_request_id` INT)   BEGIN
    DECLARE v_product_id INT;

    -- Get the product_id from return_requests
    SELECT product_id INTO v_product_id FROM return_requests WHERE request_id = p_request_id;

    -- Update the status
    UPDATE return_requests SET status = 'approved' WHERE request_id = p_request_id;

    -- Increase the stock in products
    UPDATE products SET stock = stock + 1 WHERE product_id = v_product_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RequestReturn` (IN `p_student_id` INT, IN `p_product_id` INT)   BEGIN
    INSERT INTO return_requests (student_id, product_id, status)
    VALUES (p_student_id, p_product_id, 'pending');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_products`
--

CREATE TABLE `borrowed_products` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `request_id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `product_id` int(11) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','approved','rejected','returned') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`request_id`, `student_id`, `product_id`, `barcode`, `due_date`, `status`, `request_date`) VALUES
(99, '2311600073', 135, 'barcode_682181beeb0ef', '2025-05-13', 'returned', '2025-05-12 05:06:06'),
(100, '2311600073', 144, 'barcode_682182679e8a6', '2025-05-13', 'returned', '2025-05-12 05:08:55'),
(101, '2311600073', 144, 'barcode_6824776f91ebc', '2025-05-15', 'returned', '2025-05-14 10:58:55'),
(102, '2311600073', 144, 'barcode_6824c715b7cde', '2025-05-15', 'returned', '2025-05-14 16:38:45'),
(103, '2311600073', 144, 'barcode_6824cc73d2c3c', '2025-05-15', 'returned', '2025-05-14 17:01:39'),
(104, '2311600073', 144, 'barcode_6824d2d6438a3', '2025-05-15', 'returned', '2025-05-14 17:28:54');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(50) DEFAULT NULL,
  `item_condition` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `student_id`, `message`, `is_read`, `created_at`) VALUES
(32, 0, NULL, 'A new inventory item (\'UTP cable\') has been submitted and is pending approval.', 0, '2025-05-14 17:02:39'),
(33, 0, NULL, 'A new inventory item (\'UTP cable\') has been submitted and is pending approval.', 0, '2025-05-14 17:05:40'),
(34, 0, NULL, 'A new inventory item (\'UTP cable\') has been submitted and is pending approval.', 0, '2025-05-14 17:12:24'),
(35, 0, NULL, 'A new inventory item (\'UTP cable\') has been submitted and is pending approval.', 0, '2025-05-14 17:14:43'),
(36, 0, NULL, 'A new inventory item (\'UTP cable\') has been submitted and is pending approval.', 0, '2025-05-14 17:16:29'),
(37, 0, NULL, 'A new inventory item (\'l\') has been submitted and is pending approval.', 0, '2025-05-14 17:17:45'),
(38, 0, NULL, 'A new inventory item (\'2\') has been submitted and is pending approval.', 0, '2025-05-14 17:28:28'),
(39, 0, '2311600073', 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6824d2d6438a3', 0, '2025-05-14 17:28:54'),
(40, 0, '2311600073', 'Your borrow request for test has been approved. Due date: May 15, 2025', 0, '2025-05-14 17:34:01'),
(41, 0, '2311600073', 'Your return request for 144 is pending admin approval.', 0, '2025-05-14 17:35:06'),
(42, 0, '2311600073', 'Your return request for test has been approved.', 0, '2025-05-14 17:35:15');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `stock`, `category`, `image_url`) VALUES
(135, 'Razer DeathAdder V3 Pro (Mouse)', 4, 'Input Devices', 'uploads/th.jpg'),
(138, 'HP LaserJet Pro (Printer)', 1, 'Output Devices', 'uploads/th (1).jpg'),
(142, 'Keychron C1 (Keyboard)', 1, 'Input Devices', 'uploads/th (2).jpg'),
(143, 'AVID AE-54 (Headphones)', 1, 'Input Devices', 'uploads/th (3).jpg'),
(144, 'test', 71, 'Output Devices', 'uploads/Screenshot 2025-05-03 170302.png');

-- --------------------------------------------------------

--
-- Table structure for table `retrieval_requests`
--

CREATE TABLE `retrieval_requests` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `preferred_date` date NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `request_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `processed_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_requests`
--

INSERT INTO `return_requests` (`request_id`, `student_id`, `product_id`, `request_date`, `status`, `admin_remarks`, `processed_date`) VALUES
(9, '2311600073', 144, '2025-05-12 12:53:50', '', NULL, NULL),
(10, '2311600073', 144, '2025-05-12 13:00:29', 'approved', NULL, NULL),
(11, '2311600073', 144, '2025-05-12 13:03:38', 'approved', NULL, NULL),
(12, '2311600073', 135, '2025-05-12 13:06:44', 'approved', NULL, NULL),
(13, '2311600073', 144, '2025-05-12 13:09:16', 'approved', NULL, NULL),
(14, '2311600073', 144, '2025-05-15 00:59:27', 'approved', NULL, NULL),
(15, '2311600073', 144, '2025-05-15 01:00:22', 'approved', NULL, NULL),
(16, '2311600073', 144, '2025-05-15 01:00:58', 'approved', NULL, NULL),
(17, '2311600073', 144, '2025-05-15 01:01:55', 'approved', NULL, NULL),
(18, '2311600073', 144, '2025-05-15 01:35:06', 'approved', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_admin` tinyint(1) DEFAULT 0,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT 'default.jpg',
  `approved` tinyint(1) DEFAULT 0,
  `token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `password`, `name`, `email`, `created_at`, `is_admin`, `role`, `profile_pic`, `approved`, `token`) VALUES
(13, '2311600073', '$2y$10$83a6OLjLNu5Nq5ONryHyAOGmwnc0EqdOT0R2C3P7SxngIdgrHxJtu', 'Jerald ', 'geraldmacafernandez@gmail.com', '2025-02-21 10:06:49', 0, 'user', 'uploads/67bcfaae8bdb5_download.jpg.jfif', 1, NULL),
(21, '2311600083', '$2y$10$Yh1l56zMYzP8BKzEWKIpVO5M/ZTBAumw9q6XYoy1.fBJC1BP/fVty', 'JeraldAdmin', 'jealdfern099@gmail.com', '2025-02-24 03:44:24', 1, 'admin', 'default.jpg', 0, '448850f76b9b88f211256bf24230b779'),
(22, '2311600099', '$2y$10$DxA9vxeVh8o2G9ovJYV5JuHvojjMQptn4KumclmSb.eyo4xAXPGZW', 'MACA', 'JERALD@gmail.com', '2025-04-05 05:28:56', 0, 'user', 'default.jpg', 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowed_products`
--
ALTER TABLE `borrowed_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`student_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `users` (`student_id`),
  ADD KEY `products` (`product_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `retrieval_requests`
--
ALTER TABLE `retrieval_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id_2` (`student_id`),
  ADD UNIQUE KEY `student_id_3` (`student_id`),
  ADD UNIQUE KEY `student_id_4` (`student_id`),
  ADD UNIQUE KEY `student_id_5` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowed_products`
--
ALTER TABLE `borrowed_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `retrieval_requests`
--
ALTER TABLE `retrieval_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowed_products`
--
ALTER TABLE `borrowed_products`
  ADD CONSTRAINT `borrowed_products_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `retrieval_requests`
--
ALTER TABLE `retrieval_requests`
  ADD CONSTRAINT `retrieval_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `retrieval_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD CONSTRAINT `return_requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`student_id`),
  ADD CONSTRAINT `return_requests_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
