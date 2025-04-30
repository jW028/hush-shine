-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 04:48 AM
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
  `admin_password` varchar(100) NOT NULL,
  `admin_photo` varchar(255) NOT NULL DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_contact`, `admin_email`, `admin_password`, `admin_photo`) VALUES
('A001', 'Alex Tan', '012-3242532', 'alextan@gmail.com', '$2y$12$/dYbUsT7VSx5sE768oKOLeU3D2XQyBTF2eUV1m1NEBsYrXEq96Com', '6811861b5b10e.jpg'),
('A002', 'Emily June', '014-12364346', 'emilyjune@gmail.com', 'emijune123', 'default.png');

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
('CR01', 'P023', 1),
('CR07', 'P008', 1);

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
('CT01', 'Necklaces', 'Gold, Silver, Plati'),
('CT02', 'Bracelets', 'Gold, Silver, Leather'),
('CT03', 'Rings', 'Gold, Silver, Diamond, Platinum'),
('CT04', 'Earrings', 'Gold, Silver, Pearl, Diamond'),
('CT05', 'Pendants', 'Gold, Silver, Sapphire, Ruby'),
('CT06', 'Bangles', 'Gold, Silver, Copper'),
('CT07', 'Anklets', 'Gold, Silver, Platinum'),
('CT08', 'Brooches', 'Gold, Silver, Enamel'),
('CT09', 'Wedding Bands', 'Gold, Platinum, Titanium'),
('CT10', 'Charms', 'Gold, Silver, Gemstones'),
('CT11', 'Ken Design', 'Gold, Silver, Plati');

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
  `cust_password` varchar(100) NOT NULL,
  `cust_photo` varchar(100) NOT NULL,
  `status` enum('active','blocked') DEFAULT 'active',
  `blocked_until` datetime DEFAULT NULL,
  `block_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cust_id`, `cust_name`, `cust_contact`, `cust_email`, `cust_gender`, `cust_password`, `cust_photo`, `status`, `blocked_until`, `block_reason`) VALUES
('C0001', 'John Down', '012-3465633', 'samchew323@gmail.com', 'M', '$2y$10$E/8D1ZI7qdU6NzSq23x7MOwf3YoDvSQjR0yOR4IH2GQQXIAPmUZgq', '68051890029db.jpg', 'active', NULL, NULL),
('C0002', 'walker', '012-3456783', 'walker@gmail.com', 'M', '$2y$10$5BiFFhzPVi.Qdyki00lKEOrHfSHFqcqZoHguyQNpw66CB80t5hd5a', 'default.png', 'active', NULL, NULL),
('C0003', 'sdf', '0123456789', 'test@gmail.com', 'F', '$2y$12$r2Wl22pvinDRhclQ12e2jezpL0T2Gl.vcfnOx4ttfT1uC6s4Mryq6', 'default.png', 'active', NULL, NULL),
('C0004', 'sigma', '0123456773', 'tanjw05@gmail.com', 'M', '$2y$12$206LAJPkBd0paexSaMg4xO9zOtuNpM0KKfka3ooaVh7NH87jUy8f2', 'default.png', 'active', NULL, NULL),
('C0005', 'John Pork', '0123456789', 'tanjw-wp23@student.tarc.edu.my', 'F', '$2y$12$hg0cNXtDVgUflKFgybDOnOjX6XNR5VRLky1YpW8C1tGkjsTvZMN6.', 'default.png', 'active', NULL, NULL),
('C0006', 'banana', '0123545234', 'gg@gmail.com', 'M', '$2y$12$PoRRZ2JB6.TgjzF2jh37JOaoIVgVNcwPRcEd2Kep4umqRggYPrNey', 'default.png', 'active', NULL, NULL),
('C0007', 'SuperIdol', '0123456789', '3@gmail.com', 'F', '$2y$10$VbOArB1yWpBC3F6o2Ln3S.madT3ejGgU117GahEgZM6pXo.4bSM2q', 'default.png', 'active', NULL, NULL),
('C0008', 'Double Love Bracelet', '0123456789', 'hello@gmail.com', 'M', '$2y$10$atLzbUjgIwl2QIjLV7/85eeoC/NobCKTKhubDSrNTB5IwVIUu5iQW', 'default.png', 'active', NULL, NULL),
('C0009', 'Martin', '0123028343', 'martin@gmail.com', 'M', '$2y$10$MVrT5HnT6iKfyDAJI7tqPumqTrN3p2Or8/Py/kPxYRIJsV7SeXrpm', 'default.png', 'active', NULL, NULL),
('C0010', 'Tung Tung Tung Sahur', '0123456789', 'tung@gmail.com', 'M', '$2y$10$azqRVx6Q.a8nYjbnhmtNrOi4MYpgWPPlEvm.ffqrfwXkeL20GW23.', 'default.png', 'active', NULL, NULL),
('C0011', 'Ken', '0123053492', 'samuelcch-wp23@student.tarc.edu.my', 'M', '$2y$10$HpAIGp3.9gPtIWSYxzF/TOlz8FGph0qsw.3YIv.n0H5cLGrk055v.', 'default.png', 'active', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt_time` datetime DEFAULT NULL,
  `user_type` enum('customer','admin') NOT NULL,
  `user_id` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `login_attempts`, `last_attempt_time`, `user_type`, `user_id`) VALUES
(5, 'gg@gmail.com', 3, '2025-04-27 00:57:46', 'customer', NULL),
(7, 'emilyjune@gmail.com', 1, '2025-04-27 11:59:13', 'customer', NULL);

--
-- Triggers `login_attempts`
--
DELIMITER $$
CREATE TRIGGER `before_login_attempt_insert` BEFORE INSERT ON `login_attempts` FOR EACH ROW BEGIN
    IF NEW.user_type = 'customer' AND NEW.user_id NOT IN (SELECT cust_id FROM customer) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid customer ID';
    ELSEIF NEW.user_type = 'admin' AND NEW.user_id NOT IN (SELECT admin_id FROM admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid admin ID';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `order_date` datetime NOT NULL,
  `reward_used` decimal(10,2) DEFAULT 0.00,
  `reward_get` decimal(10,2) DEFAULT 0.00,
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

INSERT INTO `orders` (`order_id`, `cust_id`, `order_date`, `reward_used`, `reward_get`, `total_amount`, `status`, `payment_id`, `payment_status`, `shipping_address`, `payment_method`) VALUES
(97, 'C0001', '2025-04-17 20:26:39', 0.00, 0.00, 2517.75, 'Received', 'pi_3RErWUFNb65u1viG1KdaTH7X', 'Paid', NULL, ''),
(102, 'C0001', '2025-04-17 21:08:54', 0.00, 0.00, 582.46, 'Cancelled', NULL, 'Paid', '31231', 'Debit/Credit Card'),
(103, 'C0001', '2025-04-17 21:13:55', 0.00, 0.00, 582.46, 'Confirmed', 'pi_3REsGBFNb65u1viG05bRVACo', 'Paid', NULL, ''),
(104, 'C0001', '2025-04-17 21:33:36', 0.00, 0.00, 370.99, 'Cancelled', NULL, 'Paid', '3123123', 'Debit/Credit Card'),
(105, 'C0001', '2025-04-17 21:34:08', 0.00, 0.00, 1696.78, 'Shipped', 'pi_3REsZRFNb65u1viG1kNJoiv5', 'Paid', NULL, ''),
(106, 'C0001', '2025-04-17 21:44:35', 0.00, 0.00, 211.47, 'Cancelled', NULL, 'Paid', '31231231', 'Debit/Credit Card'),
(107, 'C0001', '2025-04-17 21:45:20', 0.00, 0.00, 211.47, 'Refunded', NULL, 'Paid', 'Sfafas', 'Debit/Credit Card'),
(109, 'C0001', '2025-04-17 21:48:05', 0.00, 0.00, 1882.02, 'Cancelled', 'pi_3REsmDFNb65u1viG08WmMBFW', 'Paid', NULL, ''),
(111, 'C0001', '2025-04-17 21:59:50', 0.00, 0.00, 1298.50, 'Cancelled', 'pi_3REsxsFNb65u1viG1bf8X2Uf', 'Paid', NULL, ''),
(113, 'C0001', '2025-04-17 22:00:33', 0.00, 0.00, 1058.68, 'Received', 'pi_3REszAFNb65u1viG0HUrb1Qo', 'Paid', NULL, ''),
(121, 'C0001', '2025-04-25 12:35:21', 0.00, 0.00, 2012.93, 'Refunded', NULL, 'Paid', 'sdfsdfsdfsdfsdfsdfsdfsdfsdfsdfdsf', 'Debit/Credit Card'),
(122, 'C0001', '2025-04-25 12:44:11', 0.00, 0.00, 5087.96, 'Refunded', NULL, 'Paid', 'skdjfsdfsdgsd', 'Debit/Credit Card'),
(123, 'C0001', '2025-04-25 12:46:26', 0.00, 0.00, 5083.23, 'Received', NULL, 'Paid', 'adfksdfsdf', 'Debit/Credit Card'),
(124, 'C0001', '2025-04-25 15:13:17', 0.00, 0.00, 2278.98, 'Shipped', NULL, 'Paid', 'sdfsdfsdfsdf\n\n--TRACKING INFO--\nCourier: J&T\nTracking Number: FF2tg22', 'Debit/Credit Card'),
(125, 'C0001', '2025-04-25 15:48:56', 0.00, 0.00, 1695.99, 'Refunded', NULL, 'Paid', 'sdfsdfsdfsdfsd', 'Debit/Credit Card'),
(126, 'C0001', '2025-04-26 16:19:16', 0.00, 0.00, 688.99, 'Refunded', NULL, 'Paid', 'asdfasdfasdfsd', 'Debit/Credit Card'),
(127, 'C0001', '2025-04-26 16:53:40', 0.00, 15.90, 1589.99, 'Refunded', NULL, 'Paid', 'sjfsdfksdfasdgasdf', 'Debit/Credit Card'),
(128, 'C0004', '2025-04-27 01:33:55', 0.00, 30.74, 3073.98, 'Received', NULL, 'Paid', 'skdjfsdfdsfasd', 'Debit/Credit Card'),
(129, 'C0001', '2025-04-27 14:26:35', 0.00, 31.80, 3179.98, 'Confirmed', NULL, 'Paid', 'Super Idol Lane 123 Ni ma ma', 'Debit/Credit Card'),
(130, 'C0001', '2025-04-27 14:35:12', 0.00, 8.47, 847.21, 'Confirmed', NULL, 'Paid', 'Super Idol Man Man Liao', 'Debit/Credit Card'),
(131, 'C0001', '2025-04-27 14:41:30', 0.00, 13.78, 1377.99, 'Confirmed', NULL, 'Paid', 'jgbfdjbgldguidggrteggrt', 'Debit/Credit Card'),
(132, 'C0001', '2025-04-27 14:51:21', 0.00, 2.11, 211.47, 'Confirmed', NULL, 'Paid', 'LianLIanLian', 'Debit/Credit Card'),
(133, 'C0001', '2025-04-27 14:54:44', 0.00, 14.84, 1483.99, 'Confirmed', NULL, 'Paid', '43242352352323525252', 'Debit/Credit Card'),
(134, 'C0001', '2025-04-27 21:31:05', 1000.00, 31.34, 3133.97, 'Confirmed', NULL, 'Paid', 'Sigma Lane 123, Desa Setapak 47000 Kuala Lumpur', 'Debit/Credit Card'),
(135, 'C0001', '2025-04-27 21:31:48', 0.00, 8.47, 847.21, 'Confirmed', NULL, 'Paid', '3412312313312312312123', 'Debit/Credit Card'),
(136, 'C0001', '2025-04-27 21:37:35', 0.00, 2.11, 211.47, 'Confirmed', NULL, 'Paid', '64256345645674', 'DuitNow QR'),
(138, 'C0001', '2025-04-27 21:42:19', 0.00, 2.11, 211.47, 'Confirmed', NULL, 'Paid', '54353463463463463', 'DuitNow QR'),
(139, 'C0001', '2025-04-27 21:49:17', 0.00, 7.22, 721.60, 'Received', NULL, 'Paid', '564235634634', 'Debit/Credit Card'),
(143, 'C0001', '2025-04-27 21:55:29', 0.00, 10.59, 1058.68, 'Received', NULL, 'Paid', '53456345634634', 'Debit/Credit Card'),
(146, 'C0001', '2025-04-27 22:32:00', 600.00, 33.22, 3321.97, 'Received', NULL, 'Paid', 'Sigma Lane 123, Desa Setapak 47000, Kuala Lumpur, Malaysia', 'Debit/Credit Card'),
(147, 'C0001', '2025-04-28 02:01:03', 0.00, 31.80, 3179.98, 'Received', NULL, 'Paid', 'Helloooooooooooooooo', 'DuitNow QR'),
(148, 'C0001', '2025-04-29 19:23:03', 0.00, 14.43, 1443.19, 'Received', NULL, 'Paid', 'Sigma Lane 123', 'Debit/Credit Card'),
(149, 'C0001', '2025-04-30 08:31:11', 0.00, 21.20, 2119.99, 'Received', NULL, 'Paid', 'adfsgsdfsdfdsdf', 'Debit/Credit Card'),
(150, 'C0008', '2025-04-30 09:02:10', 0.00, 15.90, 1589.99, 'Delivered', NULL, 'Paid', 'Hello hello 1232141', 'Debit/Credit Card'),
(151, 'C0001', '2025-04-30 09:08:20', 30.00, 9.24, 923.99, 'Refunded', NULL, 'Paid', 'uhwgwrgergheuguierhiugerigerg', 'Debit/Credit Card'),
(152, 'C0001', '2025-04-30 09:12:20', 0.00, 7.22, 721.60, 'Refunded', NULL, 'Paid', '54254252452352', 'Debit/Credit Card'),
(153, 'C0001', '2025-04-30 09:34:39', 0.00, 7.22, 721.60, 'Received', NULL, 'Paid', 'Hello789327498252', 'Debit/Credit Card'),
(154, 'C0001', '2025-04-30 09:37:20', 0.00, 13.78, 1377.99, 'Received', NULL, 'Paid', 'gvgvytyfiyffkuk', 'Debit/Credit Card'),
(155, 'C0011', '2025-04-30 10:04:03', 0.00, 5.30, 530.00, 'Received', NULL, 'Paid', '3123123123131231\n\n--TRACKING INFO--\nCourier: agaga\nTracking Number: 123123', 'Debit/Credit Card'),
(156, 'C0011', '2025-04-30 10:07:12', 5.00, 10.55, 1055.00, 'Refunded', NULL, 'Paid', '443432423423', 'Debit/Credit Card'),
(157, 'C0011', '2025-04-30 10:18:51', 0.00, 233.20, 23320.00, 'Received', NULL, 'Paid', '525245525343543354', 'Debit/Credit Card');

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
(115, 129, 'P011', 2, 1499.99),
(116, 130, 'P005', 1, 799.25),
(117, 131, 'P014', 1, 1299.99),
(118, 132, 'P002', 1, 199.50),
(119, 133, 'P013', 1, 1399.99),
(120, 134, 'P013', 1, 1399.99),
(121, 134, 'P015', 1, 899.99),
(122, 134, 'P017', 1, 1599.99),
(123, 135, 'P005', 1, 799.25),
(124, 136, 'P002', 1, 199.50),
(126, 138, 'P002', 1, 199.50),
(127, 139, 'P008', 1, 680.75),
(135, 143, 'P002', 1, 199.50),
(136, 143, 'P005', 1, 799.25),
(141, 146, 'P013', 2, 1399.99),
(142, 146, 'P015', 1, 899.99),
(143, 147, 'P016', 2, 1499.99),
(144, 148, 'P008', 2, 680.75),
(145, 149, 'P011', 1, 1499.99),
(146, 149, 'P042', 1, 500.00),
(147, 150, 'P011', 1, 1499.99),
(148, 151, 'P001', 1, 899.99),
(149, 152, 'P008', 1, 680.75),
(150, 153, 'P008', 1, 680.75),
(151, 154, 'P014', 1, 1299.99),
(152, 155, 'P050', 5, 100.00),
(153, 156, 'P020', 1, 1000.00),
(154, 157, 'P051', 11, 2000.00);

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
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`prod_id`, `prod_name`, `prod_desc`, `price`, `quantity`, `cat_id`, `image`) VALUES
('P001', 'Gold Necklace', 'Elegant 18K gold necklace with a delicate chain.', 899.99, 9, 'CT01', '[\"680eb5d81292a_1.webp\",\"680eb5d813011_2.webp\",\"680eb5d830136_3.webp\"]'),
('P002', 'Silver Bracelet', 'Sterling silver bracelet with intricate design.', 199.50, 10, 'CT02', '[\"680eb59fb1cf7_1.webp\",\"680eb59fb2f13_2.webp\",\"680eb59fb3ab8_3.webp\"]'),
('P003', 'Diamond Ring', '14K white gold ring with a 0.5-carat diamond.', 1250.75, 10, 'CT03', '[\"680363320f445_1.jpg\",\"680363320f778_2.jpg\"]'),
('P004', 'Pearl Earrings', 'Classic pearl earrings with sterling silver backing.', 349.99, 10, 'CT04', '[\"680eb551237e1_1.webp\",\"680eb5512577d_2.webp\",\"680eb5513f43c_3.webp\"]'),
('P005', 'Sapphire Pendant', 'Blue sapphire pendant set in 18K gold.', 799.25, 10, 'CT05', '[\"680eb515d47ff_1.webp\",\"680eb515d6106_2.webp\",\"680eb515d67eb_3.webp\"]'),
('P008', 'Emerald Stud Earrings', 'Green emerald stud earrings set in platinum.', 680.75, 6, 'CT04', '[\"6802563074830_1.webp\",\"6802563074b29_2.webp\",\"6802563074c18_3.webp\"]'),
('P009', 'Platinum Wedding Band', 'Simple yet elegant platinum wedding band.', 1150.00, 10, 'CT09', '[\"680eb4c21a97d_1.png\",\"680eb4c21c4d5_2.png\",\"680eb4c21d3a0_3.png\"]'),
('P011', 'Pearl Earrings', 'pearl earrings', 1499.99, 13, 'CT04', '[\"680267e954da1_1.webp\",\"680267e9550ba_2.webp\",\"680267e9551a8_3.webp\"]'),
('P012', 'Gold Heart Earrings', 'Gold Heart Earrrings', 649.99, 15, 'CT04', '[\"68058243380d7_1.webp\",\"6805824338424_2.webp\",\"6805824338581_3.webp\"]'),
('P013', 'Emerald Necklace', 'Emerald Necklace', 1399.99, 10, 'CT01', '[\"6805826f066b1_1.webp\",\"6805826f067e8_2.webp\",\"6805826f068b8_3.webp\"]'),
('P014', 'Pearl Necklace', 'Pearl Necklace', 1299.99, 17, 'CT01', '[\"680582964fd21_1.png\",\"680582964fe23_2.webp\",\"680582964ff2d_3.webp\"]'),
('P015', 'Silver Heart Necklace', 'Silver Heart Necklace', 899.99, 10, 'CT01', '[\"680582b980bfb_1.webp\",\"680582b980d2d_2.webp\",\"680582b980ec7_3.webp\"]'),
('P016', 'Open Heart Necklace with Pearl', 'Open Heart Necklace with Pearl', 1499.99, 12, 'CT01', '[\"680583143042f_1.webp\",\"680583143059d_2.webp\",\"6805831430689_3.webp\"]'),
('P017', 'Silver Olive Leaf Band Ring', 'Silver Olive Leaf Band Ring', 1599.99, 12, 'CT03', '[\"68058359a60f2_1.webp\",\"68058359a6228_2.webp\",\"68058359a6301_3.webp\"]'),
('P018', 'Sapphire Ring', 'Sapphire Ring', 1299.99, 15, 'CT03', '[\"68059a817e326_1.webp\",\"68059a817e4ac_2.webp\",\"68059a817e5a2_3.webp\"]'),
('P019', 'Cat', 'A expensive Cat', 1600.00, 4, 'CT09', '[\"680dccd49adb6_1.png\",\"680db67c895e8_1.png\",\"680dae3f29b58_1.jpg\"]'),
('P020', 'SuperIdol', 'Testing', 1000.00, 19, 'CT02', '[\"680e4558f023e_1.jpg\",\"680e4558f0fb0_2.jpeg\",\"680e4558f1cfb_3.png\"]'),
('P021', 'Ru Yi  Jade Bracelet', 'A golden and Jade bracelet made from China.', 2000.00, 20, 'CT02', '[\"680e660c3100c_1.webp\",\"680e660c3289d_2.webp\",\"680e660c32d52_3.webp\"]'),
('P022', 'Ru Yi Jade Pendant', 'A golden and jade pendant made from China.', 2500.00, 20, 'CT05', '[\"680e663b51bf6_1.webp\",\"680e663ce64d4_2.webp\",\"680e663d19565_3.webp\"]'),
('P023', 'Knot Pendant', 'A knot like pendant made out of gold. Special for putting a knot around your neck.', 1400.00, 20, 'CT05', '[\"680e678d949c8_1.webp\",\"680e678d95bca_2.webp\",\"680e678daf384_3.webp\"]'),
('P024', 'T1 Rings', 'Rings made out of gold. Heavy but Tiny.', 900.00, 20, 'CT03', '[\"680e67b8377cc_1.webp\",\"680e67b8397b7_2.webp\",\"680e67b839e7e_3.webp\"]'),
('P025', 'Knot Earrings', 'A knot like earring made out of gold and diamonds.', 1700.00, 20, 'CT04', '[\"680e67ee52b6c_1.webp\",\"680e67ee548d6_2.webp\",\"680e67ee54e36_3.webp\"]'),
('P026', 'Chain Earrings', 'Gold Chain Earrings. Hang and Chain.', 500.00, 20, 'CT04', '[\"680e6825405a5_1.webp\",\"680e68258c3fa_2.webp\",\"680e68258c891_3.webp\"]'),
('P027', 'Signature Earrings', 'Our signature earrings made from pearls.', 400.00, 20, 'CT04', '[\"680e684a99bc9_1.webp\",\"680e684a9b1b7_2.webp\",\"680e684ae635a_3.webp\"]'),
('P028', 'Lariat Pendant', 'A pendant worn by many pretty womans.', 1000.00, 20, 'CT05', '[\"680e687bc69ae_1.webp\",\"680e687bc90f4_2.webp\",\"680e687bc99b2_3.webp\"]'),
('P029', 'Peretti Diamond Ring', 'A popular choice for marrying your partner.', 1600.00, 20, 'CT03', '[\"680e68b43a4be_1.webp\",\"680e68b43b880_2.webp\",\"680e68b43bd3f_3.webp\"]'),
('P030', 'Picasso Earrings', 'An earring that looks like leafy picasso.', 700.00, 20, 'CT04', '[\"680e68e6b72bd_1.webp\",\"680e68e70d276_2.webp\",\"680e68e70e230_3.webp\"]'),
('P031', 'Picasso Rings', 'A beautiful ring.', 1000.00, 20, 'CT03', '[\"680e696333905_1.webp\",\"680e696336024_2.webp\",\"680e6964d371d_3.webp\"]'),
('P032', 'Circle Necklace', 'Circle life', 1200.00, 20, 'CT01', '[\"680ea33470315_1.webp\",\"680ea33471c56_2.webp\",\"680ea334bbce0_3.webp\"]'),
('P033', 'Double Love Bracelet', 'Double kill', 300.00, 15, 'CT02', '[\"680ea356e41b0_1.webp\",\"680ea356e5d14_2.webp\",\"680ea356e62a0_3.webp\"]'),
('P034', 'Freshwater Bracelet', 'Sparklers Brace', 500.00, 10, 'CT02', '[\"680ea37de0632_1.webp\",\"680ea37de1694_2.webp\",\"680ea37de2cdb_3.webp\"]'),
('P035', 'Katana Earrings', 'Shing Shang Ching Chang', 2000.00, 20, 'CT04', '[\"680ea3c47f7e1_1.webp\",\"680ea3c4cb035_2.webp\",\"680ea3c4ccf41_3.webp\"]'),
('P036', 'Link Pendant', 'Link to your life', 190.00, 15, 'CT05', '[\"680ea40c7ea97_1.webp\",\"680ea40c7f78c_2.webp\",\"680ea40c9af33_3.webp\"]'),
('P037', 'Lock Pendant', 'Lock to your life', 260.00, 20, 'CT05', '[\"680ea4303408d_1.webp\",\"680ea43036f68_2.webp\",\"680ea43037f84_3.webp\"]'),
('P038', 'Olive Necklace', 'Jamie Oliver', 1000.00, 20, 'CT01', '[\"680ea44db3992_1.webp\",\"680ea44db4acc_2.webp\",\"680ea44db5039_3.webp\"]'),
('P039', 'Open Heart Bracelet', 'Open your heart', 400.00, 20, 'CT02', '[\"680ea468e9a64_1.webp\",\"680ea468ea96e_2.webp\",\"680ea468eb093_3.webp\"]'),
('P040', 'Peanut Necklace', 'Peanut', 500.00, 20, 'CT01', '[\"680ea484029b1_1.webp\",\"680ea48405367_2.webp\",\"680ea4841eb2c_3.webp\"]'),
('P041', 'Single Diamond Pendant', 'Diamond singles for single people', 1200.00, 20, 'CT05', '[\"680ea4a27d42d_1.webp\",\"680ea4a27ffdf_2.webp\",\"680ea4a280f37_3.webp\"]'),
('P042', 'Smile Bracelet', 'Smile to be happy', 500.00, 19, 'CT02', '[\"680ea4c89e96e_1.webp\",\"680ea4c8a1e05_2.webp\",\"680ea4c8a259d_3.webp\"]'),
('P043', 'Soleste Necklace', 'Solesbian', 500.00, 20, 'CT01', '[\"680ea4dfdaa0e_1.webp\",\"680ea4dff3f3d_2.webp\",\"680ea4e0000c2_3.webp\"]'),
('P044', 'Solitaire Pendant', 'Gamble', 700.00, 20, 'CT05', '[\"680ea4f6b1c01_1.webp\",\"680ea4f6b39e7_2.webp\",\"680ea4f6b471a_3.webp\"]'),
('P045', 'Sprinkle Pendant', 'Sprinkle some water', 800.00, 15, 'CT05', '[\"680ea50f9a00b_1.webp\",\"680ea50f9bbce_2.webp\",\"680ea50f9c040_3.webp\"]'),
('P046', 'Teeth Ring', 'Made out of teeth', 500.00, 20, 'CT03', '[\"680ea52b6d92b_1.webp\",\"680ea52b7058c_2.webp\",\"680ea52b71053_3.webp\"]'),
('P047', 'V Band Ring', 'V bucks', 600.00, 20, 'CT03', '[\"680ea53eeb05a_1.webp\",\"680ea53f106a7_2.webp\",\"680ea53f10bf0_3.webp\"]'),
('P048', 'X Necklace', 'X X X Necklace X X X', 700.00, 20, 'CT01', '[\"680ea55b1fab9_1.webp\",\"680ea55b213a3_2.webp\",\"680ea55b3b635_3.webp\"]'),
('P049', 'New New', 'New', 1000.00, 20, 'CT06', '[\"6810e13d595d2_1.jpeg\",\"6810e13d59d78_2.png\",\"6810e13d5a38e_3.jpeg\"]'),
('P050', 'Ken ten Rings', 'vfg\r\nf]f\r\n\r\nff\r\nf\r\nf\r\nf', 100.00, 0, 'CT11', '[\"6811840cab1f7_1.png\",\"6811840cabf1f_2.png\",\"6811840cb3622_3.png\"]'),
('P051', 'Tung Tung Tung Sahur', 'Tung Tung Tung Tung Tung Tung Tung Tung Tung Sahur', 2000.00, 9, 'CT05', '[\"6811886638e8a_1.png\",\"6811886639c98_2.png\",\"681188663abeb_3.png\"]');

-- --------------------------------------------------------

--
-- Table structure for table `prod_fav`
--

CREATE TABLE `prod_fav` (
  `favorite_id` int(11) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `prod_id` char(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prod_fav`
--

INSERT INTO `prod_fav` (`favorite_id`, `cust_id`, `prod_id`) VALUES
(0, 'C0001', 'P013'),
(0, 'C0001', 'P015'),
(0, 'C0001', 'P008'),
(0, 'C0001', 'P002'),
(0, 'C0011', 'P004');

-- --------------------------------------------------------

--
-- Table structure for table `prod_reviews`
--

CREATE TABLE `prod_reviews` (
  `review_id` int(11) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `prod_id` char(4) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review` varchar(300) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prod_reviews`
--

INSERT INTO `prod_reviews` (`review_id`, `cust_id`, `prod_id`, `order_id`, `rating`, `review`, `created_at`) VALUES
(1, 'C0011', 'P050', 155, 5, 'I love cha eun woo', '2025-04-30 10:12:43'),
(2, 'C0011', 'P051', 157, 5, 'Tung Tung Tung Tung Tung Tung Tung Tung Tung Sahur', '2025-04-30 10:20:38');

-- --------------------------------------------------------

--
-- Table structure for table `return_refund_requests`
--

CREATE TABLE `return_refund_requests` (
  `request_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `reason` text NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Request Pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_refund_requests`
--

INSERT INTO `return_refund_requests` (`request_id`, `order_id`, `cust_id`, `reason`, `photo`, `status`, `created_at`) VALUES
(1, 122, 'C0001', 'broken', 'uploads/return_refund/IMG_6123.HEIC', 'Request Pending', '2025-04-25 12:48:21'),
(2, 124, 'C0001', 'broken', 'uploads/return_refund/IMG_6109.HEIC', 'Request Pending', '2025-04-25 15:14:22'),
(3, 125, 'C0001', 'borken', 'uploads/return_refund/IMG_6109.HEIC', 'Refunded', '2025-04-25 15:49:32'),
(4, 126, 'C0001', 'sdfsdf', 'uploads/return_refund/IMG_6123.HEIC', 'Refunded', '2025-04-26 16:19:50'),
(5, 127, 'C0001', 'dsfasdfasdf', 'uploads/return_refund/IMG_6123.HEIC', 'Refunded', '2025-04-26 16:57:30'),
(6, 152, 'C0001', 'giwrregiee', 'uploads/return_refund/fucker.png', 'Refunded', '2025-04-30 09:13:16'),
(7, 156, 'C0011', 'gewgwegw', 'uploads/return_refund/dab.png', 'Refunded', '2025-04-30 10:10:47');

--
-- Triggers `return_refund_requests`
--
DELIMITER $$
CREATE TRIGGER `after_refund_completed` AFTER UPDATE ON `return_refund_requests` FOR EACH ROW BEGIN
    -- Check if the status is updated to 'Refund Completed'
    IF NEW.status = 'Refunded' AND OLD.status != 'Refunded' THEN
        -- Insert reward points into the reward_points table
        INSERT INTO reward_points (cust_id, order_id, points, description, created_at)
        SELECT NEW.cust_id, NEW.order_id, o.total_amount, CONCAT('Refund for Order #', NEW.order_id), NOW()
        FROM orders o
        WHERE o.order_id = NEW.order_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reward_points`
--

CREATE TABLE `reward_points` (
  `reward_id` int(11) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `points` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward_points`
--

INSERT INTO `reward_points` (`reward_id`, `cust_id`, `order_id`, `points`, `description`, `created_at`) VALUES
(1, 'C0001', 127, 1589.99, 'Refund for Order #127', '2025-04-26 16:57:39'),
(2, 'C0001', 152, 721.60, 'Refund for Order #152', '2025-04-30 09:13:34'),
(3, 'C0011', 156, 1055.00, 'Refund for Order #156', '2025-04-30 10:11:02');

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` char(4) NOT NULL,
  `cust_id` char(5) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shopping_cart`
--

INSERT INTO `shopping_cart` (`cart_id`, `cust_id`, `created_at`) VALUES
('CR01', 'C0001', '2025-04-30 09:00:27'),
('CR02', 'C0002', '2025-04-30 09:00:27'),
('CR03', 'C0003', '2025-04-30 09:00:27'),
('CR04', 'C0004', '2025-04-30 09:00:27'),
('CR05', 'C0005', '2025-04-30 09:00:27'),
('CR06', 'C0006', '2025-04-30 09:00:27'),
('CR07', 'C0010', '2025-04-30 09:28:29'),
('CR08', 'C0011', '2025-04-30 10:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `token_id` varchar(100) NOT NULL,
  `expire` datetime NOT NULL,
  `id` varchar(5) NOT NULL,
  `user_type` enum('customer','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `token`
--
DELIMITER $$
CREATE TRIGGER `before_token_insert` BEFORE INSERT ON `token` FOR EACH ROW BEGIN
    IF NEW.user_type = 'customer' AND NEW.id NOT IN (SELECT cust_id FROM customer) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid customer ID';
    ELSEIF NEW.user_type = 'admin' AND NEW.id NOT IN (SELECT admin_id FROM admin) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid admin ID';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `update_prod`
--

CREATE TABLE `update_prod` (
  `update_id` int(11) NOT NULL,
  `admin_id` char(4) NOT NULL,
  `prod_id` char(4) NOT NULL,
  `edit_date` date NOT NULL,
  `edit_desc` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `update_prod`
--

INSERT INTO `update_prod` (`update_id`, `admin_id`, `prod_id`, `edit_date`, `edit_desc`) VALUES
(1, 'A001', 'P018', '2025-04-26', 'miscounted product stock'),
(2, 'A001', 'P018', '2025-04-26', 'restock'),
(3, 'A001', 'P019', '2025-04-27', 'Super Idol'),
(4, 'A001', 'P019', '2025-04-27', 'Hello'),
(5, 'A001', 'P009', '2025-04-28', 'Wedding bands'),
(6, 'A001', 'P005', '2025-04-28', 'Sapphire pendant'),
(7, 'A001', 'P004', '2025-04-28', 'Paerly Pearl Pearl'),
(8, 'A001', 'P002', '2025-04-28', 'Silver cheap bracelet'),
(9, 'A001', 'P001', '2025-04-28', 'Gold Gold Gold'),
(10, 'A001', 'P050', '2025-04-30', 'Chang name'),
(11, 'A001', 'P050', '2025-04-30', 'space');

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
  ADD KEY `fk_cart_item_product` (`prod_id`),
  ADD KEY `idx_cart_item_shopping_cart` (`cart_id`),
  ADD KEY `idx_product` (`prod_id`);

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
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_id_type` (`user_id`,`user_type`);

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
  ADD KEY `fk_order_items_product` (`prod_id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_product` (`prod_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`prod_id`),
  ADD KEY `cat_id` (`cat_id`),
  ADD KEY `idx_category` (`cat_id`),
  ADD KEY `idx_product_category` (`cat_id`);

--
-- Indexes for table `prod_fav`
--
ALTER TABLE `prod_fav`
  ADD KEY `fk_fav_customer` (`cust_id`),
  ADD KEY `fk_fav_product` (`prod_id`);

--
-- Indexes for table `prod_reviews`
--
ALTER TABLE `prod_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_prod_reviews_customer` (`cust_id`),
  ADD KEY `fk_prod_reviews_product` (`prod_id`),
  ADD KEY `fk_prod_reviews_order` (`order_id`);

--
-- Indexes for table `return_refund_requests`
--
ALTER TABLE `return_refund_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_return_refund_customer` (`cust_id`),
  ADD KEY `fk_return_refund_order` (`order_id`),
  ADD KEY `idx_return_refund_order` (`order_id`),
  ADD KEY `idx_return_refund_customer` (`cust_id`);

--
-- Indexes for table `reward_points`
--
ALTER TABLE `reward_points`
  ADD PRIMARY KEY (`reward_id`),
  ADD KEY `fk_reward_points_order` (`order_id`),
  ADD KEY `fk_reward_points_customer` (`cust_id`),
  ADD KEY `idx_reward_points_customer` (`cust_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cust_id` (`cust_id`),
  ADD KEY `idx_customer` (`cust_id`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`token_id`),
  ADD KEY `idx_token_user` (`id`,`user_type`);

--
-- Indexes for table `update_prod`
--
ALTER TABLE `update_prod`
  ADD PRIMARY KEY (`update_id`),
  ADD KEY `prod_id` (`prod_id`),
  ADD KEY `fk_update_prod_admin` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `prod_reviews`
--
ALTER TABLE `prod_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `return_refund_requests`
--
ALTER TABLE `return_refund_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reward_points`
--
ALTER TABLE `reward_points`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `update_prod`
--
ALTER TABLE `update_prod`
  MODIFY `update_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_item`
--
ALTER TABLE `cart_item`
  ADD CONSTRAINT `fk_cart_item_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_item_shopping_cart` FOREIGN KEY (`cart_id`) REFERENCES `shopping_cart` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD CONSTRAINT `fk_login_attempts_admin` FOREIGN KEY (`user_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_login_attempts_customer` FOREIGN KEY (`user_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON UPDATE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `fk_product_category` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON UPDATE CASCADE;

--
-- Constraints for table `prod_fav`
--
ALTER TABLE `prod_fav`
  ADD CONSTRAINT `fk_fav_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fav_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prod_reviews`
--
ALTER TABLE `prod_reviews`
  ADD CONSTRAINT `fk_prod_reviews_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prod_reviews_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `return_refund_requests`
--
ALTER TABLE `return_refund_requests`
  ADD CONSTRAINT `fk_return_refund_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_return_refund_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reward_points`
--
ALTER TABLE `reward_points`
  ADD CONSTRAINT `fk_reward_points_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `fk_shopping_cart_customer` FOREIGN KEY (`cust_id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `fk_token_admin` FOREIGN KEY (`id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_token_customer` FOREIGN KEY (`id`) REFERENCES `customer` (`cust_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `update_prod`
--
ALTER TABLE `update_prod`
  ADD CONSTRAINT `fk_update_prod_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_update_prod_product` FOREIGN KEY (`prod_id`) REFERENCES `product` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
