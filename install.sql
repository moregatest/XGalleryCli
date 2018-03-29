-- phpMyAdmin SQL Dump
-- version 4.8.0-rc1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 29, 2018 at 10:46 AM
-- Server version: 5.7.21-0ubuntu0.16.04.1
-- PHP Version: 7.0.28-1+ubuntu16.04.1+deb.sury.org+1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `soulevil_xgallery`
--

-- --------------------------------------------------------

--
-- Table structure for table `xgallery_flickr_contacts`
--

DROP TABLE IF EXISTS `xgallery_flickr_contacts`;
CREATE TABLE `xgallery_flickr_contacts` (
  `id` int(11) NOT NULL,
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
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `params` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `xgallery_flickr_contact_photos`
--

DROP TABLE IF EXISTS `xgallery_flickr_contact_photos`;
CREATE TABLE `xgallery_flickr_contact_photos` (
  `pid` int(11) NOT NULL,
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
  `params` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `xgallery_photos`
--

DROP TABLE IF EXISTS `xgallery_photos`;
CREATE TABLE `xgallery_photos` (
  `id` int(11) NOT NULL,
  `type` varchar(125) NOT NULL,
  `dir` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `width` float NOT NULL,
  `height` float NOT NULL,
  `ratio` float NOT NULL,
  `isWallpaper` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `params` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `xgallery_flickr_contacts`
--
ALTER TABLE `xgallery_flickr_contacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nsid` (`nsid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `xgallery_flickr_contact_photos`
--
ALTER TABLE `xgallery_flickr_contact_photos`
  ADD PRIMARY KEY (`pid`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `owner` (`owner`),
  ADD KEY `state` (`state`);

--
-- Indexes for table `xgallery_photos`
--
ALTER TABLE `xgallery_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `xgallery_flickr_contacts`
--
ALTER TABLE `xgallery_flickr_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `xgallery_flickr_contact_photos`
--
ALTER TABLE `xgallery_flickr_contact_photos`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `xgallery_photos`
--
ALTER TABLE `xgallery_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `xgallery_flickr_contact_photos`
--
ALTER TABLE `xgallery_flickr_contact_photos`
  ADD CONSTRAINT `xgallery_flickr_contact_photos_ibfk_1` FOREIGN KEY (`owner`) REFERENCES `xgallery_flickr_contacts` (`nsid`) ON DELETE NO ACTION ON UPDATE NO ACTION;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
