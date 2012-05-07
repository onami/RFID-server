<?php

$app->get('/install/', function() {
	ORM::get_db()->exec(@"-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 07, 2012 at 10:19 AM
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
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `key`, `location_id`, `time_zone`, `description`, `status`) VALUES
(1, 'bgvlKW3ZAsLwzqLPkMXjG0oQJ6G4ax7eJxuZNbgN', 1, 6, 'Стационарный считыватель DL 6970 №1', 0),
(2, 'd3d21d444d4c27f9fdb9de2699b6b45571f14c18', 1, 56, 'Переносной считыватель DL 770 №1', 0),
(3, 'b3e1290cc6e9cc2a197078f607481303348a981b', 2, 5, 'Переносной считыватель DL 770 №2', 0);

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `key`, `description`) VALUES
(1, '292f00aa9449566d1765691213406cc17c599589', 'Abitech Ltd.'),
(2, 'c17c8fae131c597d656c1e928117a69f42d703cb', 'Скважина №1'),
(3, '30f4376867416d76ae8e993659762239eb074b83', 'Скважина №2'),
(4, '7a5ffdc34c3389872649516845ad51b9d730f7cf', 'Скважина №3'),
(5, '49599f6f1d20143566577d01307ab586207ea3ca', 'Скважина №4'),
(6, '6fb65843d4d3ad68f30ba8d1a94e53294db75769', 'Склад №1'),
(7, 'c0165983ffdcb67b26755f9b7d7df0d21fb81087', 'Склад №2');

-- --------------------------------------------------------

--
-- Table structure for table `reading_sessions`
--

CREATE TABLE IF NOT EXISTS `reading_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `checksum` varchar(40) NOT NULL,
  `time_marker` datetime NOT NULL,
  `location_id` int(11) NOT NULL,
  `reading_status` int(11) NOT NULL,
  `session_mode` int(11) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `reading_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `tags_list`
--

CREATE TABLE IF NOT EXISTS `tags_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `last_session_id` int(11) NOT NULL,
  `tag` varchar(60) NOT NULL,
  PRIMARY KEY (`tag`),
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

");
});


