-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2026 at 10:49 AM
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
-- Database: `greenloop`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `co2_per_kg` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `co2_per_kg`) VALUES
(1, 'โทรศัพท์ / แท็บเล็ต', 2.00),
(2, 'คอมพิวเตอร์ / โน้ตบุ๊ก', 1.80),
(3, 'แบตเตอรี่ / สายชาร์จ', 2.50),
(4, 'อุปกรณ์ IT อื่นๆ', 1.50);

-- --------------------------------------------------------

--
-- Table structure for table `contents`
--

CREATE TABLE `contents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contents`
--

INSERT INTO `contents` (`id`, `user_id`, `title`, `body`, `start_at`, `end_at`, `created_at`) VALUES
(1, 1, 'E-Waste คืออะไร ทำไมต้องรีไซเคิล', 'ขยะอิเล็กทรอนิกส์มีทั้งโลหะมีค่าและสารอันตราย การส่งรีไซเคิลช่วยนำวัสดุกลับมาใช้ใหม่และลดก๊าซเรือนกระจก', '2026-09-20 10:16:50', NULL, '2026-10-15 10:16:50'),
(2, 1, '5 วิธีลดคาร์บอนฟุตพริ้นท์ในบ้าน', 'ตั้งแอร์ 26 องศา ถอดปลั๊กเมื่อไม่ใช้ แยกขยะ ใช้ขนส่งสาธารณะ และลดอาหารเหลือทิ้ง', '2026-10-05 10:16:50', NULL, '2026-10-15 10:16:50'),
(3, 1, 'เปิดจุดรับ E-Waste แห่งใหม่', 'ข่าวนี้ตั้งเวลาเผยแพร่ล่วงหน้า', '2026-10-18 10:16:50', '2026-11-14 10:16:50', '2026-10-15 10:16:50'),
(4, 1, 'สรุปกิจกรรม Big Cleaning Day', 'ข่าวนี้หมดเวลาแสดงผลแล้ว', '2026-08-16 10:16:50', '2026-09-05 10:16:50', '2026-10-15 10:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_redemptions`
--

CREATE TABLE `coupon_redemptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_spent` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','used','expired') NOT NULL DEFAULT 'pending',
  `coupon_code` varchar(32) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `used_at` datetime DEFAULT NULL,
  `stock_reserved` tinyint(1) NOT NULL DEFAULT 0,
  `recipient_name` varchar(100) DEFAULT NULL,
  `recipient_phone` varchar(20) DEFAULT NULL,
  `shipping_address` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupon_redemptions`
--

INSERT INTO `coupon_redemptions` (`id`, `user_id`, `reward_id`, `points_spent`, `status`, `coupon_code`, `expires_at`, `used_at`, `stock_reserved`, `recipient_name`, `recipient_phone`, `shipping_address`, `created_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 5, 1, 1, 'approved', 'GL-9A9EF0E1CB87', '2026-11-24 14:23:12', NULL, 1, NULL, NULL, NULL, '2026-10-25 14:22:57', '2026-10-25 14:23:12', 1);

-- --------------------------------------------------------

--
-- Table structure for table `factors`
--

CREATE TABLE `factors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `factor` decimal(8,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `factors`
--

INSERT INTO `factors` (`id`, `name`, `unit`, `factor`) VALUES
(1, 'ไฟฟ้าที่ใช้ในบ้าน', 'หน่วย (kWh)', 0.500),
(2, 'น้ำมันเบนซิน', 'ลิตร', 2.200),
(3, 'ก๊าซหุงต้ม', 'กก.', 3.100),
(4, 'ขยะทั่วไปที่ทิ้ง', 'กก.', 0.700),
(5, 'มื้ออาหารที่มีเนื้อสัตว์', 'มื้อ', 1.500);

-- --------------------------------------------------------

--
-- Table structure for table `footprints`
--

CREATE TABLE `footprints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `year` smallint(6) NOT NULL,
  `total_kg` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `footprints`
--

INSERT INTO `footprints` (`id`, `user_id`, `year`, `total_kg`, `created_at`) VALUES
(1, 2, 2025, 3700.00, '2026-06-19 10:16:49'),
(2, 2, 2026, 3040.00, '2026-09-15 10:16:49'),
(3, 3, 2026, 2310.00, '2026-08-06 10:16:49'),
(4, 4, 2026, 4400.00, '2026-09-07 10:16:49');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `message` varchar(500) NOT NULL,
  `link` varchar(100) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, NULL, 'สมาชิกใหม่', 'วิชัย ประหยัดไฟ (wichai@greenloop.local)', 'admin/users.php', 1, '2026-09-05 10:16:50'),
(2, NULL, 'คำขอรีไซเคิลใหม่', 'วิชัย ประหยัดไฟ: สายชาร์จชำรุดหลายเส้น', 'admin/recycles.php?status=pending', 1, '2026-10-14 10:16:50'),
(3, 2, 'รีไซเคิลสำเร็จ รับ 46 แต้ม', 'โน้ตบุ๊กจอแตก ลดคาร์บอนได้ 4.14 kgCO₂e', 'points.php', 1, '2026-08-21 10:16:50'),
(4, NULL, 'สมาชิกใหม่', 'ศิลา (sila224@gmail.com)', 'admin/users.php', 1, '2026-10-20 16:38:23'),
(5, 5, 'ยินดีต้อนรับสู่ GreenLoop', 'เริ่มส่งขยะอิเล็กทรอนิกส์ไปรีไซเคิลและสะสมแต้มได้เลย', 'recycle.php', 1, '2026-10-20 16:38:23'),
(6, NULL, 'คำขอแลกคูปองใหม่', 'ศิลา ขอแลก coupon', 'admin/coupons.php', 1, '2026-10-25 14:22:57'),
(7, 5, 'คูปองได้รับการอนุมัติ', 'ใช้รหัส GL-9A9EF0E1CB87 แลก coupon ภายใน 24/11/2026 14:23', 'coupons.php', 0, '2026-10-25 14:23:12'),
(8, NULL, 'คำขอรีไซเคิลใหม่', 'ศิลา: zaw', 'admin/recycles.php?status=pending', 0, '2026-10-25 14:43:43'),
(9, NULL, 'คำขอรีไซเคิลใหม่', 'ศิลา: zaw', 'admin/recycles.php?status=pending', 0, '2026-10-25 14:43:54'),
(10, 5, 'รีไซเคิลสำเร็จ รับ 220 แต้ม', 'zaw ลดคาร์บอนได้ 22 kgCO₂e', 'points.php', 0, '2026-10-25 14:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `point_transactions`
--

CREATE TABLE `point_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `related_type` varchar(30) NOT NULL DEFAULT '',
  `related_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `point_transactions`
--

INSERT INTO `point_transactions` (`id`, `user_id`, `amount`, `description`, `related_type`, `related_id`, `created_at`) VALUES
(1, 1, 0, 'ยอดแต้มตั้งต้นก่อนเริ่มบันทึกประวัติ', 'opening', NULL, '2026-10-15 10:16:49'),
(2, 2, 114, 'ยอดแต้มตั้งต้นก่อนเริ่มบันทึกประวัติ', 'opening', NULL, '2026-10-15 10:16:49'),
(3, 3, 48, 'ยอดแต้มตั้งต้นก่อนเริ่มบันทึกประวัติ', 'opening', NULL, '2026-10-15 10:16:49'),
(4, 4, 120, 'ยอดแต้มตั้งต้นก่อนเริ่มบันทึกประวัติ', 'opening', NULL, '2026-10-15 10:16:49'),
(5, 5, -1, 'แลกของรางวัล: coupon', 'coupon', 1, '2026-10-25 14:22:57'),
(6, 5, 220, 'แต้มจากรีไซเคิล: zaw', 'recycle', 9, '2026-10-25 14:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `recycles`
--

CREATE TABLE `recycles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `weight_kg` decimal(6,2) NOT NULL,
  `image` varchar(50) NOT NULL DEFAULT '' COMMENT 'ชื่อไฟล์รูปสินค้าใน uploads/',
  `status` enum('pending','done','rejected') NOT NULL DEFAULT 'pending',
  `points` int(11) NOT NULL DEFAULT 0,
  `co2_kg` decimal(8,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `delivery_type` enum('นำส่งเอง','รับที่บ้าน','','') NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recycles`
--

INSERT INTO `recycles` (`id`, `user_id`, `category_id`, `item_name`, `weight_kg`, `image`, `status`, `points`, `co2_kg`, `created_at`, `delivery_type`, `address`) VALUES
(1, 2, 1, 'โทรศัพท์เก่าเปิดไม่ติด', 0.40, '', 'done', 8, 0.80, '2026-07-10 10:16:50', 'นำส่งเอง', ''),
(2, 2, 2, 'โน้ตบุ๊กจอแตก', 2.30, '', 'done', 46, 4.14, '2026-08-21 10:16:50', 'นำส่งเอง', ''),
(3, 3, 3, 'แบตเตอรี่สำรองบวม', 0.90, '', 'done', 18, 2.25, '2026-09-02 10:16:50', 'นำส่งเอง', ''),
(4, 4, 4, 'เครื่องพิมพ์เสีย', 4.50, '', 'done', 90, 6.75, '2026-09-30 10:16:50', 'นำส่งเอง', ''),
(5, 2, 4, 'เมาส์พัง', 0.20, '', 'rejected', 0, 0.00, '2026-10-06 10:16:50', 'นำส่งเอง', ''),
(6, 3, 1, 'แท็บเล็ตแบตเสื่อม', 0.50, '', 'pending', 0, 0.00, '2026-10-12 10:16:50', 'นำส่งเอง', ''),
(7, 4, 3, 'สายชาร์จชำรุดหลายเส้น', 0.30, '', 'pending', 0, 0.00, '2026-10-14 10:16:50', 'นำส่งเอง', ''),
(8, 5, 1, 'zaw', 22.00, '6addb32f8ffde.png', 'pending', 0, 0.00, '2026-10-25 14:43:43', '', '321'),
(9, 5, 1, 'zaw', 11.00, '', 'done', 220, 22.00, '2026-10-25 14:43:54', '', '213');

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '',
  `points_cost` int(11) NOT NULL,
  `stock` int(11) DEFAULT NULL COMMENT 'NULL = unlimited',
  `coupon_valid_days` smallint(5) UNSIGNED NOT NULL DEFAULT 30,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`id`, `title`, `description`, `points_cost`, `stock`, `coupon_valid_days`, `is_active`, `created_at`, `image`) VALUES
(1, 'coupon', 'ืnew', 1, 2, 30, 1, '2026-10-25 14:20:01', '6addcb7d5bf73.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `avatar` varchar(50) NOT NULL DEFAULT '' COMMENT 'ชื่อไฟล์รูปโปรไฟล์ใน uploads/',
  `password` varchar(255) NOT NULL,
  `question` varchar(100) NOT NULL COMMENT 'คำถามกันลืมรหัสผ่านที่สมาชิกเลือก',
  `answer` varchar(255) NOT NULL COMMENT 'คำตอบกันลืมรหัสผ่าน (เข้ารหัส)',
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `points` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `avatar`, `password`, `question`, `answer`, `role`, `status`, `points`, `created_at`) VALUES
(1, 'ผู้ดูแลระบบ', 'admin@greenloop.local', '0800000000', '', '$2y$10$Hlspc86w66632ObfEZvRJ.wq7fHdnVnTd6YJFsJAqLV5qfTt78sbC', 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ', '$2y$10$XH5vVtX06Oezhe.zHP.j1.Y0InpB1KEstUe65NM6WLd9cua7wBfaa', 'admin', 'active', 0, '2026-05-18 10:16:49'),
(2, 'สมชาย ใจดี', 'demo@greenloop.local', '0811111111', '', '$2y$10$2otFyK2pF5rcaI5rtbnbvuWkcaJncBeHlcx5LYGHIQnMXmDRKRIrK', 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ', '$2y$10$XH5vVtX06Oezhe.zHP.j1.Y0InpB1KEstUe65NM6WLd9cua7wBfaa', 'user', 'active', 114, '2026-06-17 10:16:49'),
(3, 'สมหญิง รักษ์โลก', 'ying@greenloop.local', '0822222222', '', '$2y$10$2otFyK2pF5rcaI5rtbnbvuWkcaJncBeHlcx5LYGHIQnMXmDRKRIrK', 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ', '$2y$10$XH5vVtX06Oezhe.zHP.j1.Y0InpB1KEstUe65NM6WLd9cua7wBfaa', 'user', 'active', 48, '2026-07-27 10:16:49'),
(4, 'วิชัย ประหยัดไฟ', 'wichai@greenloop.local', '0833333333', '', '$2y$10$2otFyK2pF5rcaI5rtbnbvuWkcaJncBeHlcx5LYGHIQnMXmDRKRIrK', 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ', '$2y$10$XH5vVtX06Oezhe.zHP.j1.Y0InpB1KEstUe65NM6WLd9cua7wBfaa', 'user', 'active', 120, '2026-09-05 10:16:49'),
(5, 'ศิลา', 'sila224@gmail.com', '0627984402', '', '$2y$10$.9XbzfUD0qNxZgeEfw6pTOidWd9Ol2pGvcSSIHY.zWT2tVmAUfrA.', 'ชื่อสัตว์เลี้ยงตัวแรกของคุณ', '$2y$10$q0o0CVAqY6ICZna4bn34EO8fdQLDvDNUJVr3Ijmft4w7Ef56bu1lG', 'user', 'active', 1218, '2026-10-20 16:38:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contents`
--
ALTER TABLE `contents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_code` (`coupon_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `factors`
--
ALTER TABLE `factors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `footprints`
--
ALTER TABLE `footprints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`year`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `recycles`
--
ALTER TABLE `recycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contents`
--
ALTER TABLE `contents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `factors`
--
ALTER TABLE `factors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `footprints`
--
ALTER TABLE `footprints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `point_transactions`
--
ALTER TABLE `point_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `recycles`
--
ALTER TABLE `recycles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contents`
--
ALTER TABLE `contents`
  ADD CONSTRAINT `contents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_redemptions`
--
ALTER TABLE `coupon_redemptions`
  ADD CONSTRAINT `coupon_redemptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_redemptions_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`),
  ADD CONSTRAINT `coupon_redemptions_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `footprints`
--
ALTER TABLE `footprints`
  ADD CONSTRAINT `footprints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD CONSTRAINT `point_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `recycles`
--
ALTER TABLE `recycles`
  ADD CONSTRAINT `recycles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recycles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
