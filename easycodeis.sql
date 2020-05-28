-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 28, 2020 at 04:15 AM
-- Server version: 5.7.24
-- PHP Version: 7.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easycodeis`
--
CREATE DATABASE IF NOT EXISTS `easycodeis` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `easycodeis`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` text COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `key_value` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `user_level` int(11) NOT NULL DEFAULT '0',
  `fb_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `fb_access_token` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `tw_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `oauth_token` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `oauth_token_secret` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `twitch_user_id` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `twitch_access_token` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `twitch_refresh_token` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
