-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2024 at 10:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `assgroup`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `productid` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `userid`, `productid`, `quantity`) VALUES
(12, 3, 9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `userid` int(11) DEFAULT NULL,
  `billingaddress` varchar(255) DEFAULT NULL,
  `phoneno` varchar(20) DEFAULT NULL,
  `orderdate` date DEFAULT NULL,
  `deliverydate` date DEFAULT NULL,
  `delivery` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `userid`, `billingaddress`, `phoneno`, `orderdate`, `deliverydate`, `delivery`, `payment_status`, `total`, `delivery_fee`) VALUES
(1, 3, 'lorong pala 89', '0165232066', '2024-01-09', '2024-01-18', 'Express delivery', 'Yes', 293.00, 10.00),
(2, 4, 'phan', '4567890', '2024-01-09', '2024-01-26', 'Standard delivery', 'Yes', 338.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `productid` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `productid`, `quantity`) VALUES
(1, 1, 4, 1),
(2, 1, 7, 1),
(3, 1, 11, 1),
(4, 1, 15, 1),
(5, 2, 1, 1),
(6, 2, 8, 2),
(7, 2, 9, 1),
(8, 2, 14, 1),
(9, 2, 4, 1),
(10, 2, 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `productname` varchar(100) NOT NULL,
  `price` int(11) NOT NULL,
  `description` text NOT NULL,
  `availableunit` int(11) NOT NULL,
  `item` varchar(100) NOT NULL,
  `image` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `productname`, `price`, `description`, `availableunit`, `item`, `image`) VALUES
(1, 'White T-shirt', 45, 'Made in cotton', 99, 'clothes', 'c1.png'),
(2, 'Men Short Sleeve T-Shirt', 50, 'Hip-Hop Male O-neck Causal T-shirt Harajuku Tees Tops Shirts Cotton Mens Clothes 4XL', 100, 'clothes', 'c2.png'),
(3, ' T-shirt Women', 20, 'Tops Short Sleeve Round Neck Clothes', 100, 'clothes', 'c3.png'),
(4, 'Women Short Sleeve Top', 25, 'Summer New pocket panda Cartoon Printed T-shirt Korean fashion women tops', 98, 'clothes', 'c4.png'),
(5, 'Men\'s Fashion Casual Loose', 135, 'Pure Color Sports Long Sweatpants Pants Chinos Pants Men Slim Fit', 100, 'pants', 'p1.png'),
(6, 'Sports Pants Men', 50, 'Casual Pants Loose Beam Feet All-match Korean Version Fashion Jogging Pants Thin Slim', 99, 'pants', 'p2.png'),
(7, 'Casual Pants Female', 48, 'SUNTEK Slightly Fat Girls Wear Large Size Casual Pants Female', 99, 'pants', 'p3.png'),
(8, 'Khaki Wide Leg Pants', 55, 'High-Waist Denim Loose Mopping Jeans Women\'s Palazo Pants', 98, 'pants', 'p4.png'),
(9, 'Men\'s Running Dad Shoes', 100, 'Korean Trend Leisure Sports Student Travel Shoes Sneakers Men Platform Shoes', 99, 'shoes', 's1.png'),
(10, 'TP Sport Shoes Men Sneakers', 90, 'Korean Style Fashion Casual Original Low Top All-match Soft Shoes Sneaker', 100, 'shoes', 's2.png'),
(11, 'Nike women\'s shoes', 200, 'autumn and winter leather and velvet casual sports shoes', 99, 'shoes', 's3.png'),
(12, 'Women\'s Casual Shoes', 60, 'Summer New Board Shoes Comfortable Leather White Shoes', 100, 'shoes', 's4.png'),
(13, 'White Socks Striped', 8, 'Harajuku Kawaii Funny Cute Hip Hop Skateboard Streetwear', 100, 'socks', 'so1.png'),
(14, 'Socks for Girls', 8, 'Spring Autumn Anti Slip Floor Socks Cotton', 99, 'socks', 'so2.png'),
(15, 'Women\'s Spring Summer Casual Boat Socks', 10, 'Morandi Color Breathable Mesh Invisible Silicone Non-slip Shallow Mouth Socks', 99, 'socks', 'so3.png'),
(16, 'Sports Funny Socks', 5, 'Customized Cotton Sports Funny Socks', 100, 'socks', 'so4.png');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(25) NOT NULL,
  `lastname` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phoneno` varchar(20) NOT NULL,
  `address` varchar(120) NOT NULL,
  `password` varchar(100) NOT NULL,
  `isAdmin` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `phoneno`, `address`, `password`, `isAdmin`) VALUES
(1, 'Admin', 'Admin', 'admin@gmail.com', '-', '-', '202cb962ac59075b964b07152d234b70', 1),
(3, 'lau', 'jinhao', 'lau@gmail.com', '0165232066', 'lorong pala 89', '202cb962ac59075b964b07152d234b70', 0),
(4, 'phan', 'phan', 'phan@gmail.com', '4567890', 'phan', '202cb962ac59075b964b07152d234b70', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userid` (`userid`),
  ADD KEY `productid` (`productid`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `userid` (`userid`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `productid` (`productid`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`productid`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`productid`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
