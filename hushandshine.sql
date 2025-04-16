-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2025 at 09:12 AM
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
-- Database: `hushandshine`
--

-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `hushandshine`;
USE `hushandshine`;

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` char(4) NOT NULL,
  `admin_name` varchar(50) NOT NULL,
  `admin_contact` varchar(13) NOT NULL,
  `admin_email` varchar(50) NOT NULL,
  `admin_password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_contact`, `admin_email`, `admin_password`) VALUES
('A001', 'Alex Tan', '012-3242532', 'alextan@gmail.com', 'alextan135'),
('A002', 'Emily June', '014-12364346', 'emilyjune@gmail.com', 'emijune123');

-- --------------------------------------------------------

--
-- Table structure for table `cart_item`
--

CREATE TABLE `cart_item` (
  `cart_id` char(4) NOT NULL,
  `prod_id` char(4) NOT NULL,
  `quantity` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_item`
--

INSERT INTO `cart_item` (`cart_id`, `prod_id`, `quantity`) VALUES
('CR02', 'P003', 1),
('CR03', 'P001', 2),
('CR04', 'P006', 1),
('CR05', 'P010', 2),
('CR06', 'P002', 2),
('CR07', 'P005', 1),
('CR08', 'P007', 1),
('CR09', 'P002', 1),
('CR10', 'P008', 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` varchar(4) NOT NULL,
  `cat_name` varchar(50) NOT NULL,
  `material_type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `cat_name`, `material_type`) VALUES
('CT01', 'Necklaces', 'Gold, Silver, Platinum'),
('CT02', 'Bracelets', 'Gold, Silver, Leather'),
('CT03', 'Rings', 'Gold, Silver, Diamond, Platinum'),
('CT04', 'Earrings', 'Gold, Silver, Pearl, Diamond'),
('CT05', 'Pendants', 'Gold, Silver, Sapphire, Ruby'),
('CT06', 'Bangles', 'Gold, Silver, Copper'),
('CT07', 'Anklets', 'Gold, Silver, Platinum'),
('CT08', 'Brooches', 'Gold, Silver, Enamel'),
('CT09', 'Wedding Bands', 'Gold, Platinum, Titanium'),
('CT10', 'Charms', 'Gold, Silver, Gemstones');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cust_id` char(5) NOT NULL,
  `cust_name` varchar(50) NOT NULL,
  `cust_contact` varchar(13) NOT NULL,
  `cust_email` varchar(50) NOT NULL,
  `cust_gender` char(1) NOT NULL,
  `cust_password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cust_id`, `cust_name`, `cust_contact`, `cust_email`, `cust_gender`, `cust_password`) VALUES
('C0001', 'Aiman Rahman', '0134567890', 'aimanrahman@gmail.com', 'M', 'pass1234'),
('C0002', 'Siti Aminah', '0129876543', 'sitiaminah@gmail.com', 'F', 'securepass1'),
('C0003', 'Lim Wei Sheng', '0176543210', 'limweisheng@gmail.com', 'M', 'mypassword'),
('C0004', 'Kavitha Subramaniam', '0183456789', 'kavithas@gmail.com', 'F', 'kavi2023'),
('C0005', 'Tan Chong Leong', '0162233445', 'tanchong@gmail.com', 'M', 'chongleong88'),
('C0006', 'Nor Hidayah', '0141122334', 'norhidayah@gmail.com', 'F', 'hidayah987'),
('C0007', 'Rajesh Kumar', '0194455667', 'rajeshk@gmail.com', 'M', 'rajesh_secure'),
('C0008', 'Farah Nabilah', '0137788990', 'farahnabilah@gmail.com', 'F', 'farah_123'),
('C0009', 'Michael Wong', '0125566778', 'michaelwong@gmail.com', 'M', 'wongmike98'),
('C0010', 'Zarina Hassan', '0176677889', 'zarinahassan@gmail.com', 'F', 'zarina_pass'),
('C0011', 'Adam Firdaus', '0199988776', 'adamfirdaus@gmail.com', 'M', 'adamF_pass'),
('C0012', 'Priya Chandran', '0163344556', 'priyachandran@gmail.com', 'F', 'priya_c123'),
('C0013', 'Hafizullah Syed', '0182233445', 'hafizsyed@gmail.com', 'M', 'hafiz_987'),
('C0014', 'Jenny Lim', '0149988776', 'jennylim@gmail.com', 'F', 'jenny_2023'),
('C0015', 'Ahmad Danish', '0136677889', 'ahmadanish@gmail.com', 'M', 'danish_678'),
('C0016', 'Yasmin Zulkifli', '0191122334', 'yasmin@gmail.com', 'F', 'yasmin_abc'),
('C0017', 'Kelvin Tan', '0175566778', 'kelvintan@gmail.com', 'M', 'kelvinpass01'),
('C0018', 'Nabila Farhana', '0127788990', 'nabilafarhana@gmail.com', 'F', 'nabila_pass'),
('C0019', 'Syafiq Hazim', '0164455667', 'syafiqhazim@gmail.com', 'M', 'syafiq_321'),
('C0020', 'Cheryl Lee', '0183344556', 'cherylee@gmail.com', 'F', 'cheryllee999'),
('C0021', 'Samuel Chew', '0123053492', 'samchew323@gmail.com', 'M', '$2y$10$P.I.xEYJzEAjO5mYGkSDAuFKX3LJV1F71vEMMy9AiEKRsPl9vKQJe');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `order_date` datetime NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `payment_id` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `cust_id`, `order_date`, `total_amount`, `status`, `payment_id`, `payment_status`, `shipping_address`) VALUES
(1, 'C0001', '2025-04-13 15:00:43', 1695.97, 'Confirmed', 'pi_3RDKU8FNb65u1viG1rHkjHk2', 'Paid', NULL),
(2, 'C0001', '2025-04-13 15:01:45', 1695.97, 'Confirmed', 'pi_3RDKU8FNb65u1viG1rHkjHk2', 'Paid', NULL),
(3, 'C0001', '2025-04-13 15:04:17', 1695.97, 'Confirmed', 'pi_3RDKU8FNb65u1viG1rHkjHk2', 'Paid', NULL),
(4, 'C0001', '2025-04-13 15:04:23', 1695.97, 'Confirmed', 'pi_3RDKU8FNb65u1viG1rHkjHk2', 'Paid', NULL),
(5, 'C0001', '2025-04-13 15:04:36', 1695.97, 'Confirmed', 'pi_3RDKU8FNb65u1viG1rHkjHk2', 'Paid', NULL),
(6, 'C0001', '2025-04-13 15:07:50', 6945.05, 'Confirmed', 'pi_3RDKdnFNb65u1viG01RxAXxw', 'Paid', NULL),
(7, 'C0001', '2025-04-13 15:08:43', 721.60, 'Confirmed', 'pi_3RDKegFNb65u1viG0YlQa05g', 'Paid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `prod_id` char(4) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `prod_id`, `quantity`, `price`) VALUES
(1, 1, 'P001', 1, 899.99),
(2, 1, 'P004', 2, 349.99),
(3, 6, 'P001', 3, 899.99),
(4, 6, 'P004', 2, 349.99),
(5, 6, 'P007', 2, 1575.99),
(6, 7, 'P008', 1, 680.75);

-- --------------------------------------------------------

--
-- Table structure for table `order_prod`
--

CREATE TABLE `order_prod` (
  `order_id` char(4) NOT NULL,
  `cart_id` char(4) NOT NULL,
  `order_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_prod`
--

INSERT INTO `order_prod` (`order_id`, `cart_id`, `order_date`) VALUES
('O001', 'CR01', '2025-02-16'),
('O002', 'CR02', '2024-11-19'),
('O003', 'CR03', '2024-11-30'),
('O004', 'CR04', '2024-09-27'),
('O005', 'CR05', '2025-03-08'),
('O006', 'CR06', '2025-02-23'),
('O007', 'CR07', '2024-10-16'),
('O008', 'CR08', '2024-12-27'),
('O009', 'CR09', '2024-10-30'),
('O010', 'CR10', '2025-01-15');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` char(4) NOT NULL,
  `order_id` char(4) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` double(7,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_date`, `payment_method`, `total_amount`) VALUES
('PY01', 'O001', '2025-02-20', 'Bank Transfer', 917.99),
('PY02', 'O002', '2024-11-23', 'Debit/Credit Card', 1261.13),
('PY03', 'O003', '2024-12-01', 'Touch N Go', 1823.54),
('PY04', 'O004', '2024-09-30', 'FPX', 1030.59),
('PY05', 'O005', '2025-03-10', 'Bank Transfer', 576.36),
('PY06', 'O006', '2025-02-25', 'Debit/Credit Card', 447.26),
('PY07', 'O007', '2024-10-17', 'Bank Transfer', 831.65),
('PY08', 'O008', '2024-12-29', 'FPX', 1583.01),
('PY09', 'O009', '2024-11-01', 'FPX', 215.64),
('PY10', 'O010', '2025-01-17', 'Debit/Credit Card', 688.83);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `prod_id` char(4) NOT NULL,
  `prod_name` varchar(100) NOT NULL,
  `prod_desc` varchar(200) NOT NULL,
  `price` double(7,2) NOT NULL,
  `status` char(1) NOT NULL,
  `cat_id` char(4) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`prod_id`, `prod_name`, `prod_desc`, `price`, `status`, `cat_id`, `image`) VALUES
('P001', 'Gold Necklace', 'Elegant 18K gold necklace with a delicate chain.', 899.99, 'A', 'CT01', ''),
('P002', 'Silver Bracelet', 'Sterling silver bracelet with intricate design.', 199.50, 'A', 'CT02', ''),
('P003', 'Diamond Ring', '14K white gold ring with a 0.5-carat diamond.', 1250.75, 'A', 'CT03', ''),
('P004', 'Pearl Earrings', 'Classic pearl earrings with sterling silver backing.', 349.99, 'A', 'CT04', ''),
('P005', 'Sapphire Pendant', 'Blue sapphire pendant set in 18K gold.', 799.25, 'A', 'CT05', ''),
('P006', 'Gold Bangle', 'Traditional 22K gold bangle with intricate patterns.', 1025.50, 'A', 'CT06', ''),
('P007', 'Ruby Necklace', 'Ruby gemstone necklace with gold chain.', 1575.99, 'A', 'CT01', ''),
('P008', 'Emerald Stud Earrings', 'Green emerald stud earrings set in platinum.', 680.75, 'A', 'CT04', ''),
('P009', 'Platinum Wedding Band', 'Simple yet elegant platinum wedding band.', 1150.00, 'A', 'CT09', ''),
('P010', 'Gold Anklet', 'Delicate 18K gold anklet with small charms.', 275.49, 'A', 'CT07', '');

-- --------------------------------------------------------

--
-- Table structure for table `return_refund`
--

CREATE TABLE `return_refund` (
  `return_id` char(4) NOT NULL,
  `payment_id` char(4) NOT NULL,
  `return_desc` varchar(100) NOT NULL,
  `refund_amount` double(7,2) NOT NULL,
  `refund_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--

CREATE TABLE `shipping` (
  `ship_id` char(4) NOT NULL,
  `order_id` char(4) NOT NULL,
  `ship_type` varchar(50) NOT NULL,
  `ship_address` varchar(200) NOT NULL,
  `ship_charge` double(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping`
--

INSERT INTO `shipping` (`ship_id`, `order_id`, `ship_type`, `ship_address`, `ship_charge`) VALUES
('S001', 'O001', 'Standard', '200 Jalan Tun Razak, Kuala Lumpur, Malaysia', 18.00),
('S002', 'O002', 'Economy', '80 Jalan Abdul, Kuala Lumpur, Malaysia', 10.38),
('S003', 'O003', 'Standard', '101 Nguyen Hue Street, Ho Chi Minh City, Vietnam', 23.56),
('S004', 'O004', 'Standard', '123 Main Street, Kuala Lumpur, Malaysia', 5.09),
('S005', 'O005', 'Economy', '150 Jalan Rawang, Kuala Lumpur, Malaysia', 25.38),
('S006', 'O006', 'Standard', '77 Petaling Street, Kuala Lumpur, Malaysia', 48.26),
('S007', 'O007', 'Standard', '34 Jalan Bakri,  Johor, Malaysia', 32.40),
('S008', 'O008', 'Express', '789 Sukhumvit Road, Bangkok, Thailand', 7.02),
('S009', 'O009', 'Economy', '123 Main Street, Kuala Lumpur, Malaysia', 16.14),
('S010', 'O010', 'Standard', '55 Nathan Road, Hong Kong', 8.08);

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` char(4) NOT NULL,
  `cust_id` char(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_cart`
--

INSERT INTO `shopping_cart` (`cart_id`, `cust_id`) VALUES
('CR01', 'C0001'),
('CR02', 'C0002'),
('CR03', 'C0003'),
('CR04', 'C0004'),
('CR05', 'C0005'),
('CR06', 'C0006'),
('CR07', 'C0007'),
('CR08', 'C0008'),
('CR09', 'C0009'),
('CR10', 'C0010');

-- --------------------------------------------------------

--
-- Table structure for table `stripe_payments`
--

CREATE TABLE `stripe_payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_intent_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'myr',
  `status` varchar(50) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `update_prod`
--

CREATE TABLE `update_prod` (
  `admin_id` char(4) NOT NULL,
  `prod_id` char(4) NOT NULL,
  `edit_date` date NOT NULL,
  `edit_desc` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD PRIMARY KEY (`cart_id`,`prod_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cust_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `cust_id` (`cust_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- Indexes for table `order_prod`
--
ALTER TABLE `order_prod`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `cart_id` (`cart_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `return_refund`
--
ALTER TABLE `return_refund`
  ADD PRIMARY KEY (`return_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`ship_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cust_id` (`cust_id`);

--
-- Indexes for table `stripe_payments`
--
ALTER TABLE `stripe_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `update_prod`
--
ALTER TABLE `update_prod`
  ADD PRIMARY KEY (`admin_id`,`prod_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stripe_payments`
--
ALTER TABLE `stripe_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`);

--
-- Constraints for table `order_prod`
--
ALTER TABLE `order_prod`
  ADD CONSTRAINT `order_prod_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart` (`cart_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_prod` (`order_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`);

--
-- Constraints for table `return_refund`
--
ALTER TABLE `return_refund`
  ADD CONSTRAINT `return_refund_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`);

--
-- Constraints for table `shipping`
--
ALTER TABLE `shipping`
  ADD CONSTRAINT `shipping_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `order_prod` (`order_id`);

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`);

--
-- Constraints for table `stripe_payments`
--
ALTER TABLE `stripe_payments`
  ADD CONSTRAINT `stripe_payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `update_prod`
--
ALTER TABLE `update_prod`
  ADD CONSTRAINT `update_prod_ibfk_1` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`),
  ADD CONSTRAINT `update_prod_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
