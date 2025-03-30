-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 30, 2025 at 04:54 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

DROP DATABASE hushandshine;
CREATE DATABASE hushandshine;
USE hushandshine;

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
('A001', 'Alex Tan', '012-3242532', 'alextan@gmail.com', '$2y$12$uBKnN4HbRlNDAPjT938fleBEjp8wNPqLzidFuKODV6vQwpvb0EoYq'),
('A002', 'Emily June', '014-12364346', 'emilyjune@gmail.com', '$2y$12$R7dzX8Pmmkk6MAHpQhO5MOwkOVzDRVhlppFgF..wbEq2gZIMQuR1u');

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
('CR01', 'P001', 1),
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

-- INSERT INTO `order_prod` (`order_id`, `cart_id`, `order_date`) VALUES
-- ('O001', 'CR01', '2025-02-16'),
-- ('O002', 'CR02', '2024-11-19'),
-- ('O003', 'CR03', '2024-11-30'),
-- ('O004', 'CR04', '2024-09-27'),
-- ('O005', 'CR05', '2025-03-08'),
-- ('O006', 'CR06', '2025-02-23'),
-- ('O007', 'CR07', '2024-10-16'),
-- ('O008', 'CR08', '2024-12-27'),
-- ('O009', 'CR09', '2024-10-30'),
-- ('O010', 'CR10', '2025-01-15');

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

-- INSERT INTO `payment` (`payment_id`, `order_id`, `payment_date`, `payment_method`, `total_amount`) VALUES
-- ('PY01', 'O001', '2025-02-20', 'Bank Transfer', 917.99),
-- ('PY02', 'O002', '2024-11-23', 'Debit/Credit Card', 1261.13),
-- ('PY03', 'O003', '2024-12-01', 'Touch N Go', 1823.54),
-- ('PY04', 'O004', '2024-09-30', 'FPX', 1030.59),
-- ('PY05', 'O005', '2025-03-10', 'Bank Transfer', 576.36),
-- ('PY06', 'O006', '2025-02-25', 'Debit/Credit Card', 447.26),
-- ('PY07', 'O007', '2024-10-17', 'Bank Transfer', 831.65),
-- ('PY08', 'O008', '2024-12-29', 'FPX', 1583.01),
-- ('PY09', 'O009', '2024-11-01', 'FPX', 215.64),
-- ('PY10', 'O010', '2025-01-17', 'Debit/Credit Card', 688.83);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `prod_id` char(4) NOT NULL,
  `prod_name` varchar(100) NOT NULL,
  `prod_desc` varchar(200) NOT NULL,
  `price` double(7,2) NOT NULL,
  `quantity` int(4) NOT NULL,
  `cat_id` char(4) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`prod_id`, `prod_name`, `prod_desc`, `price`, `quantity`, `cat_id`, `image`) VALUES
('P001', 'Olive Leaf Band Ring', 'With beautifully sculpted leaves in sterling silver, we honor the olive branch, a symbol of peace and abundance.', 530.00, 60, 'CT03', '[\"67e4269aefc77_1.webp\",\"67e4269aefdc2_2.webp\",\"67e4269aefed1_3.webp\"]'),
('P002', 'Ruby Ring', 'Ring in platinum with rubies.', 1500.00, 20, 'CT03', '[\"67e427a710d01_1.jpg\",\"67e427a710de4_2.jpg\"]'),
('P003', 'Pearl Necklace', 'Pearl necklace', 780.00, 15, 'CT01', '[\"67e8aed1e315c_1.png\",\"67e8aed1e337b_2.webp\",\"67e8aed1e34d7_3.webp\"]');

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

-- INSERT INTO `shipping` (`ship_id`, `order_id`, `ship_type`, `ship_address`, `ship_charge`) VALUES
-- ('S001', 'O001', 'Standard', '200 Jalan Tun Razak, Kuala Lumpur, Malaysia', 18.00),
-- ('S002', 'O002', 'Economy', '80 Jalan Abdul, Kuala Lumpur, Malaysia', 10.38),
-- ('S003', 'O003', 'Standard', '101 Nguyen Hue Street, Ho Chi Minh City, Vietnam', 23.56),
-- ('S004', 'O004', 'Standard', '123 Main Street, Kuala Lumpur, Malaysia', 5.09),
-- ('S005', 'O005', 'Economy', '150 Jalan Rawang, Kuala Lumpur, Malaysia', 25.38),
-- ('S006', 'O006', 'Standard', '77 Petaling Street, Kuala Lumpur, Malaysia', 48.26),
-- ('S007', 'O007', 'Standard', '34 Jalan Bakri,  Johor, Malaysia', 32.40),
-- ('S008', 'O008', 'Express', '789 Sukhumvit Road, Bangkok, Thailand', 7.02),
-- ('S009', 'O009', 'Economy', '123 Main Street, Kuala Lumpur, Malaysia', 16.14),
-- ('S010', 'O010', 'Standard', '55 Nathan Road, Hong Kong', 8.08);

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

-- INSERT INTO `shopping_cart` (`cart_id`, `cust_id`) VALUES
-- ('CR01', 'C0001'),
-- ('CR02', 'C0002'),
-- ('CR03', 'C0003'),
-- ('CR04', 'C0004'),
-- ('CR05', 'C0005'),
-- ('CR06', 'C0006'),
-- ('CR07', 'C0007'),
-- ('CR08', 'C0008'),
-- ('CR09', 'C0009'),
-- ('CR10', 'C0010');

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
-- Indexes for table `update_prod`
--
ALTER TABLE `update_prod`
  ADD PRIMARY KEY (`admin_id`,`prod_id`),
  ADD KEY `prod_id` (`prod_id`);

--
-- Constraints for dumped tables
--

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
-- Constraints for table `update_prod`
--
ALTER TABLE `update_prod`
  ADD CONSTRAINT `update_prod_ibfk_1` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`),
  ADD CONSTRAINT `update_prod_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
