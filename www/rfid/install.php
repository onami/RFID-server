<?php

$app->get('/install/', function() {
	ORM::get_db()->exec(@"-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 10, 2012 at 08:54 AM
-- Server version: 5.1.40
-- PHP Version: 5.3.3

SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";

--
-- Database: `rfid`
--

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `location_id` int(11) NOT NULL,
  `time_zone` tinyint(1) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `location_id` (`location_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `key`, `location_id`, `time_zone`, `description`, `status`) VALUES
(1, 'bgvlKW3ZAsLwzqLPkMXjG0oQJ6G4ax7eJxuZNbgN', 1, 6, 'Стационарный считыватель DL 6970 №1', 0),
(2, 'd3d21d444d4c27f9fdb9de2699b6b45571f14c18', 1, 5, 'Переносной считыватель DL 770 №1', 0),
(3, 'b3e1290cc6e9cc2a197078f607481303348a981b', 1, 5, 'Переносной считыватель DL 770 №2', 0),
(6, 'WKmlkvVPopnzcRHDuOxPhvKiySzs3znE561ho21h', 1, 6, 'Переносной считыватель DL 770 №3', 0),
(7, 'tphBNWqt9JpwM0zF2DwMSUnvMZUaFSEZ3mUGInKj', 1, 6, 'Переносной считыватель DL 770 №4', 0);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `key`, `description`) VALUES
(1, '292f00aa9449566d1765691213406cc17c599589', 'Abitech Ltd');

-- --------------------------------------------------------

--
-- Table structure for table `reading_sessions`
--

CREATE TABLE IF NOT EXISTS `reading_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `checksum` varchar(40) NOT NULL,
  `count` int(11) NOT NULL,
  `time_marker` datetime NOT NULL,
  `location_id` int(11) NOT NULL,
  `reading_status` int(11) NOT NULL,
  `session_mode` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `reading_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `description`) VALUES
(1, 'Кладовщик'),
(2, 'Буровой мастер'),
(3, 'Руководитель буровых мастеров');

-- --------------------------------------------------------

--
-- Table structure for table `tags_list`
--

CREATE TABLE IF NOT EXISTS `tags_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_session_id` int(11) NOT NULL,
  `tag` varchar(60) NOT NULL,
  PRIMARY KEY (`tag`),
  KEY `last_session_id` (`last_session_id`),
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `tags_list`
--


-- --------------------------------------------------------

--
-- Table structure for table `tubes`
--

CREATE TABLE IF NOT EXISTS `tubes` (
  `tag` varchar(60) NOT NULL,
  `session_id` int(11) NOT NULL,
  PRIMARY KEY (`session_id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

--
-- Dumping data for table `tubes`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `description` varchar(120) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `description`) VALUES
(3, 1, 'Кладовщик №1'),
(4, 2, 'Буровой мастер №1'),
(5, 2, 'Буровой мастер №2'),
(6, 2, 'Буровой мастер №3'),
(7, 2, 'Буровой мастер №4'),
(8, 3, 'Руководитель №1'),
(9, 3, 'Руководитель №2');

-- --------------------------------------------------------

--
-- Table structure for table `users_rel`
--

CREATE TABLE IF NOT EXISTS `users_rel` (
  `user_id` int(11) NOT NULL,
  `rel_user_id` int(11) DEFAULT NULL,
  `device_id` int(11) DEFAULT NULL,
  KEY `device_id` (`device_id`),
  KEY `user_id` (`user_id`),
  KEY `rel_user_id` (`rel_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

--
-- Dumping data for table `users_rel`
--

INSERT INTO `users_rel` (`user_id`, `rel_user_id`, `device_id`) VALUES
(3, NULL, 1),
(4, NULL, 2),
(5, NULL, 3),
(6, NULL, 6),
(7, NULL, 7),
(8, 4, NULL),
(8, 5, NULL),
(9, 6, NULL),
(9, 7, NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `reading_sessions`
--
ALTER TABLE `reading_sessions`
  ADD CONSTRAINT `reading_sessions_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `reading_sessions_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);

--
-- Constraints for table `tags_list`
--
ALTER TABLE `tags_list`
  ADD CONSTRAINT `tags_list_ibfk_1` FOREIGN KEY (`last_session_id`) REFERENCES `reading_sessions` (`id`);

--
-- Constraints for table `tubes`
--
ALTER TABLE `tubes`
  ADD CONSTRAINT `tubes_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `reading_sessions` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `users_rel`
--
ALTER TABLE `users_rel`
  ADD CONSTRAINT `users_rel_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_rel_ibfk_3` FOREIGN KEY (`rel_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_rel_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`);
");
});


