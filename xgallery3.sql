-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 15, 2019 at 12:27 PM
-- Server version: 5.7.25-0ubuntu0.16.04.2
-- PHP Version: 7.2.14-1+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xgallery3`
--
CREATE DATABASE IF NOT EXISTS `xgallery3` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `xgallery3`;

-- --------------------------------------------------------

--
-- Table structure for table `xgallery_flickr_contacts`
--

DROP TABLE IF EXISTS `xgallery_flickr_contacts`;
CREATE TABLE IF NOT EXISTS `xgallery_flickr_contacts` (
  `nsid` varchar(125) NOT NULL COMMENT 'NSID',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(255) NOT NULL,
  `iconserver` int(11) NOT NULL,
  `iconfarm` int(11) NOT NULL,
  `ignored` int(11) NOT NULL,
  `rev_ignored` int(11) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `friend` int(11) NOT NULL,
  `family` int(11) NOT NULL,
  `path_alias` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `total_photos` int(11) DEFAULT NULL,
  `last_fetched` datetime DEFAULT NULL,
  PRIMARY KEY (`nsid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `xgallery_flickr_photos`
--

DROP TABLE IF EXISTS `xgallery_flickr_photos`;
CREATE TABLE IF NOT EXISTS `xgallery_flickr_photos` (
  `id` varchar(125) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `owner` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `server` int(11) NOT NULL,
  `farm` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ispublic` int(11) NOT NULL,
  `isfriend` int(11) NOT NULL,
  `isfamily` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `params` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `owner` (`owner`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `xgallery_flickr_photos`
--
ALTER TABLE `xgallery_flickr_photos`
  ADD CONSTRAINT `xgallery_flickr_photos_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `xgallery_flickr_contacts` (`nsid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
