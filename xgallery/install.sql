-- phpMyAdmin SQL Dump
-- version 4.7.8
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 17, 2018 at 06:48 AM
-- Server version: 5.7.21-log
-- PHP Version: 7.2.3

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `xgallery`
--

-- --------------------------------------------------------

--
-- Table structure for table `esemx_xgallery_flickr_contacts`
--
-- Creation: Mar 05, 2018 at 01:39 PM
--

DROP TABLE IF EXISTS `esemx_xgallery_flickr_contacts`;
CREATE TABLE IF NOT EXISTS `esemx_xgallery_flickr_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nsid` varchar(125) NOT NULL,
  `username` varchar(255) NOT NULL,
  `iconserver` varchar(50) NOT NULL,
  `iconfarm` varchar(50) NOT NULL,
  `ignored` tinyint(4) NOT NULL,
  `rev_ignored` tinyint(4) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `friend` int(11) NOT NULL,
  `family` int(11) NOT NULL,
  `path_alias` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `photos` int(11) DEFAULT NULL,
  `scores` int(11) DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nsid` (`nsid`),
  UNIQUE KEY `username` (`username`),
  KEY `id` (`id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONSHIPS FOR TABLE `esemx_xgallery_flickr_contacts`:
--   `created_by`
--       `esemx_users` -> `id`
--

--
-- Truncate table before insert `esemx_xgallery_flickr_contacts`
--

TRUNCATE TABLE `esemx_xgallery_flickr_contacts`;
-- --------------------------------------------------------

--
-- Table structure for table `esemx_xgallery_flickr_contact_photos`
--
-- Creation: Mar 05, 2018 at 01:39 PM
--

DROP TABLE IF EXISTS `esemx_xgallery_flickr_contact_photos`;
CREATE TABLE IF NOT EXISTS `esemx_xgallery_flickr_contact_photos` (
  `pid` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(125) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `secret` varchar(125) NOT NULL,
  `server` varchar(50) NOT NULL,
  `farm` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `ispublic` tinyint(4) NOT NULL,
  `isfriend` tinyint(4) NOT NULL,
  `isfamily` tinyint(4) NOT NULL,
  `urls` text NOT NULL,
  `state` tinyint(4) NOT NULL,
  `hash` varchar(32) DEFAULT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`pid`),
  UNIQUE KEY `id` (`id`),
  KEY `owner` (`owner`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONSHIPS FOR TABLE `esemx_xgallery_flickr_contact_photos`:
--   `owner`
--       `esemx_xgallery_flickr_contacts` -> `nsid`
--

--
-- Truncate table before insert `esemx_xgallery_flickr_contact_photos`
--

TRUNCATE TABLE `esemx_xgallery_flickr_contact_photos`;
-- --------------------------------------------------------

--
-- Table structure for table `esemx_xgallery_photos`
--
-- Creation: Mar 05, 2018 at 01:39 PM
--

DROP TABLE IF EXISTS `esemx_xgallery_photos`;
CREATE TABLE IF NOT EXISTS `esemx_xgallery_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(125) NOT NULL,
  `dir` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `width` float NOT NULL,
  `height` float NOT NULL,
  `ratio` float NOT NULL,
  `isWallpaper` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONSHIPS FOR TABLE `esemx_xgallery_photos`:
--

--
-- Truncate table before insert `esemx_xgallery_photos`
--

TRUNCATE TABLE `esemx_xgallery_photos`;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `esemx_xgallery_flickr_contacts`
--
ALTER TABLE `esemx_xgallery_flickr_contacts`
  ADD CONSTRAINT `esemx_xgallery_flickr_contacts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `esemx_users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `esemx_xgallery_flickr_contact_photos`
--
ALTER TABLE `esemx_xgallery_flickr_contact_photos`
  ADD CONSTRAINT `esemx_xgallery_flickr_contact_photos_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `esemx_xgallery_flickr_contacts` (`nsid`) ON DELETE NO ACTION ON UPDATE NO ACTION;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
