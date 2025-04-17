-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 18, 2025 at 01:38 AM
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
('CR01', 'P002', 2),
('CR01', 'P007', 1),
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
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `cust_id`, `order_date`, `total_amount`, `status`, `payment_id`, `payment_status`, `shipping_address`, `payment_method`) VALUES
(93, 'C0001', '2025-04-17 20:06:13', 2046.57, 'Pending', NULL, 'Unpaid', '31231231234123123121215', 'Debit/Credit Card'),
(94, 'C0001', '2025-04-17 20:07:20', 2996.34, 'Pending', NULL, 'Unpaid', '312312', 'Debit/Credit Card'),
(95, 'C0001', '2025-04-17 20:25:36', 2757.58, 'Pending', NULL, 'Unpaid', '312312', 'Debit/Credit Card'),
(96, 'C0001', '2025-04-17 20:26:29', 2517.75, 'Pending', NULL, 'Unpaid', '3123123', 'Debit/Credit Card'),
(97, 'C0001', '2025-04-17 20:26:39', 2517.75, 'Confirmed', 'pi_3RErWUFNb65u1viG1KdaTH7X', 'Paid', NULL, ''),
(98, 'C0001', '2025-04-17 20:45:46', 1934.24, 'Pending', NULL, 'Unpaid', '3123131', 'Debit/Credit Card'),
(99, 'C0001', '2025-04-17 20:46:21', 582.46, 'Pending', NULL, 'Unpaid', '31231231', 'Debit/Credit Card'),
(100, 'C0001', '2025-04-17 20:56:02', 211.47, 'Pending', NULL, 'Unpaid', '4123423', 'Debit/Credit Card'),
(101, 'C0001', '2025-04-17 20:59:51', 1087.03, 'Pending', NULL, 'Unpaid', '312312', 'Debit/Credit Card'),
(102, 'C0001', '2025-04-17 21:08:54', 582.46, 'Pending', NULL, 'Paid', '31231', 'Debit/Credit Card'),
(103, 'C0001', '2025-04-17 21:13:55', 582.46, 'Confirmed', 'pi_3REsGBFNb65u1viG05bRVACo', 'Paid', NULL, ''),
(104, 'C0001', '2025-04-17 21:33:36', 370.99, 'Pending', NULL, 'Paid', '3123123', 'Debit/Credit Card'),
(105, 'C0001', '2025-04-17 21:34:08', 1696.78, 'Confirmed', 'pi_3REsZRFNb65u1viG1kNJoiv5', 'Paid', NULL, ''),
(106, 'C0001', '2025-04-17 21:44:35', 211.47, 'Pending', NULL, 'Paid', '31231231', 'Debit/Credit Card'),
(107, 'C0001', '2025-04-17 21:45:20', 211.47, 'Pending', NULL, 'Paid', 'Sfafas', 'Debit/Credit Card'),
(108, 'C0001', '2025-04-17 21:46:48', 211.47, 'Pending', NULL, 'Unpaid', '321312', 'Debit/Credit Card'),
(109, 'C0001', '2025-04-17 21:48:05', 1882.02, 'Confirmed', 'pi_3REsmDFNb65u1viG08WmMBFW', 'Paid', NULL, ''),
(110, 'C0001', '2025-04-17 21:58:51', 211.47, 'Pending', NULL, 'Unpaid', '45234', 'Debit/Credit Card'),
(111, 'C0001', '2025-04-17 21:59:50', 1298.50, 'Confirmed', 'pi_3REsxsFNb65u1viG1bf8X2Uf', 'Paid', NULL, ''),
(112, 'C0001', '2025-04-17 22:00:11', 847.21, 'Pending', NULL, 'Unpaid', '312312', 'Debit/Credit Card'),
(113, 'C0001', '2025-04-17 22:00:33', 1058.68, 'Confirmed', 'pi_3REszAFNb65u1viG0HUrb1Qo', 'Paid', NULL, ''),
(114, 'C0001', '2025-04-17 22:06:54', 211.47, 'Pending', NULL, 'Unpaid', '3213123', 'Debit/Credit Card'),
(115, 'C0001', '2025-04-17 22:17:09', 211.47, 'Pending', NULL, 'Unpaid', '3421342', 'Debit/Credit Card'),
(116, 'C0001', '2025-04-17 22:20:31', 211.47, 'Pending', NULL, 'Unpaid', '3421342', 'Debit/Credit Card'),
(117, 'C0001', '2025-04-17 22:21:11', 211.47, 'Pending', NULL, 'Unpaid', '3421342', 'Debit/Credit Card'),
(118, 'C0001', '2025-04-17 22:22:09', 211.47, 'Pending', NULL, 'Unpaid', '3421342', 'Debit/Credit Card'),
(119, 'C0001', '2025-04-17 22:22:44', 211.47, 'Pending', NULL, 'Unpaid', '3421342', 'Debit/Credit Card'),
(120, 'C0001', '2025-04-17 22:25:08', 211.47, 'Pending', NULL, 'Unpaid', '3421342', 'Debit/Credit Card');

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
(65, 93, 'P001', 1, 899.99),
(66, 93, 'P004', 1, 349.99),
(67, 93, 'P008', 1, 680.75),
(68, 94, 'P003', 1, 1250.75),
(69, 94, 'P007', 1, 1575.99),
(70, 95, 'P006', 1, 1025.50),
(71, 95, 'P007', 1, 1575.99),
(72, 96, 'P005', 1, 799.25),
(73, 96, 'P007', 1, 1575.99),
(74, 97, 'P005', 1, 799.25),
(75, 97, 'P007', 1, 1575.99),
(76, 98, 'P005', 1, 799.25),
(77, 98, 'P006', 1, 1025.50),
(78, 99, 'P002', 1, 199.50),
(79, 99, 'P004', 1, 349.99),
(80, 100, 'P002', 1, 199.50),
(81, 101, 'P006', 1, 1025.50),
(82, 102, 'P002', 1, 199.50),
(83, 102, 'P004', 1, 349.99),
(84, 104, 'P004', 1, 349.99),
(85, 106, 'P002', 1, 199.50),
(86, 107, 'P002', 1, 199.50),
(87, 108, 'P002', 1, 199.50),
(88, 109, 'P002', 1, 199.50),
(89, 109, 'P007', 1, 1575.99),
(90, 110, 'P002', 1, 199.50),
(91, 111, 'P002', 1, 199.50),
(92, 111, 'P006', 1, 1025.50),
(93, 112, 'P005', 1, 799.25),
(94, 113, 'P002', 1, 199.50),
(95, 113, 'P005', 1, 799.25),
(96, 114, 'P002', 1, 199.50),
(97, 115, 'P002', 1, 199.50),
(98, 116, 'P002', 1, 199.50),
(99, 117, 'P002', 1, 199.50),
(100, 118, 'P002', 1, 199.50),
(101, 119, 'P002', 1, 199.50),
(102, 120, 'P002', 1, 199.50);

-- --------------------------------------------------------

--
-- Table structure for table `order_prod`
--

CREATE TABLE `order_prod` (
  `order_id` char(4) NOT NULL,
  `cart_id` char(4) NOT NULL,
  `prod_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD PRIMARY KEY (`cart_id`,`prod_id`),
  ADD KEY `fk_cart_item_product` (`prod_id`);

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
  ADD KEY `fk_orders_customer` (`cust_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_order_items_product` (`prod_id`);

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
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `stripe_payments`
--
ALTER TABLE `stripe_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `fk_cart_item_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE,
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