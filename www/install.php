<?php
$app->get('/rfid/install/:dbname/', function($dbname) {
	ORM::get_db()->exec(@"
-- phpMyAdmin SQL Dump
-- version 3.2.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 03, 2012 at 11:42 AM
-- Server version: 5.1.40
-- PHP Version: 5.3.3

SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";

--
-- Database: `{$dbname}`
--

-- --------------------------------------------------------

--
-- Table structure for table `reading_sessions`
--

CREATE TABLE IF NOT EXISTS `reading_sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `checksum` varchar(40) NOT NULL,
  `time_stamp` datetime NOT NULL,
  `coords` point NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `reading_sessions`
--


-- --------------------------------------------------------

--
-- Table structure for table `tubes`
--

CREATE TABLE IF NOT EXISTS `tubes` (
  `tag` varchar(40) NOT NULL,
  `session_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251;

 -- UNIQUE KEY `tag` (`tag`,`session_id`)
--
-- Dumping data for table `tubes`
--


-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(40) NOT NULL,
  `pass` varchar(40) NOT NULL,
  `session_id` varchar(40) NOT NULL,
  `last_auth` datetime NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `login`, `pass`, `session_id`, `last_auth`, `status`) VALUES
(1, 'project', '2cf2260e621842818f540ca8d7ded4425dd31220', '', '0000-00-00 00:00:00', 0);");
});




