-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 05, 2025 at 07:46 PM
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
  `due_date` date NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`request_id`, `student_id`, `product_id`, `due_date`, `status`, `request_date`) VALUES
(79, '2311600073', 144, '2025-05-12', '', '2025-05-05 17:34:21'),
(80, '2311600073', 144, '2025-05-06', '', '2025-05-05 17:37:01'),
(81, '2311600073', 144, '2025-05-06', 'approved', '2025-05-05 17:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `student_id`) VALUES
(51, 0, 'Your borrow request for item ID 111 is pending admin approval. Barcode: barcode_68189d5944f1a', 0, '2025-05-05 11:13:29', 2147483647),
(52, 0, 'Your borrow request for item ID 113 is pending admin approval. Barcode: barcode_68189e1704faf', 0, '2025-05-05 11:16:39', 2147483647),
(53, 0, 'Your borrow request for item ID 111 is pending admin approval. Barcode: barcode_6818e56bd55a5', 0, '2025-05-05 16:20:59', 2147483647),
(54, 0, 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6818ee5428835', 0, '2025-05-05 16:59:00', 2147483647),
(55, 0, 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6818f22ec4402', 0, '2025-05-05 17:15:26', 2147483647),
(56, 0, 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6818f3346ca59', 0, '2025-05-05 17:19:48', 2147483647),
(57, 0, 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6818f69d433a6', 0, '2025-05-05 17:34:21', 2147483647),
(58, 0, 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6818f73d553a0', 0, '2025-05-05 17:37:01', 2147483647),
(59, 0, 'Your borrow request for item ID 144 is pending admin approval. Barcode: barcode_6818f8d103ed6', 0, '2025-05-05 17:43:45', 2147483647);

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
(135, 'Razer DeathAdder V3 Pro (Mouse)', 1, 'Input Devices', 'uploads/th.jpg'),
(138, 'HP LaserJet Pro (Printer)', 1, 'Output Devices', 'uploads/th (1).jpg'),
(142, 'Keychron C1 (Keyboard)', 1, 'Input Devices', 'uploads/th (2).jpg'),
(143, 'AVID AE-54 (Headphones)', 1, 'Input Devices', 'uploads/th (3).jpg'),
(144, 'test', 94, 'Output Devices', 'uploads/Screenshot 2025-05-03 170302.png');

-- --------------------------------------------------------

--
-- Table structure for table `return_requests`
--

CREATE TABLE `return_requests` (
  `request_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `return_requests`
--
DELIMITER $$
CREATE TRIGGER `After_Return_Approval` AFTER UPDATE ON `return_requests` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE products SET stock = stock + 1 WHERE product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

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
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `password`, `name`, `email`, `created_at`, `is_admin`, `role`, `profile_pic`, `approved`) VALUES
(13, '2311600073', '$2y$10$83a6OLjLNu5Nq5ONryHyAOGmwnc0EqdOT0R2C3P7SxngIdgrHxJtu', 'Jerald ', 'geraldmacafernandez@gmail.com', '2025-02-21 10:06:49', 0, 'user', 'uploads/67bcfaae8bdb5_download.jpg.jfif', 1),
(21, '2311600083', '$2y$10$Yh1l56zMYzP8BKzEWKIpVO5M/ZTBAumw9q6XYoy1.fBJC1BP/fVty', 'JeraldAdmin', 'jealdfern099@gmail.com', '2025-02-24 03:44:24', 1, 'admin', 'default.jpg', 0),
(22, '2311600099', '$2y$10$DxA9vxeVh8o2G9ovJYV5JuHvojjMQptn4KumclmSb.eyo4xAXPGZW', 'MACA', 'JERALD@gmail.com', '2025-04-05 05:28:56', 0, 'user', 'default.jpg', 1);

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
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `return_requests`
--
ALTER TABLE `return_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `fk_student` (`student_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `return_requests`
--
ALTER TABLE `return_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
