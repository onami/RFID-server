<?php
$app->get('/rfid/install/:dbname/', function($dbname) {
	ORM::get_db()->exec(@"
	  -- phpMyAdmin SQL Dump
	  -- version 3.2.3
	  -- http://www.phpmyadmin.net
	  --
	  -- Host: localhost
	  -- Generation Time: Apr 20, 2012 at 04:26 AM
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
		`time_marker` datetime NOT NULL,
		`location_id` varchar(40) DEFAULT NULL,
		`status` int(11) NOT NULL,
		`mode` int(11) NOT NULL,
		PRIMARY KEY (`session_id`)
	  ) ENGINE=InnoDb DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

	  --
	  -- Dumping data for table `reading_sessions`
	  --


	  -- --------------------------------------------------------

	  --
	  -- Table structure for table `tags_list`
	  --

	  CREATE TABLE IF NOT EXISTS `tags_list` (
		`tag` varchar(32) NOT NULL,
		`last_mode` int(11) NOT NULL,
		PRIMARY KEY (`tag`)
	  ) ENGINE=InnoDb DEFAULT CHARSET=cp1251;

	  --
	  -- Dumping data for table `tags_list`
	  --


	  -- --------------------------------------------------------

	  --
	  -- Table structure for table `tubes`
	  --

	  CREATE TABLE IF NOT EXISTS `tubes` (
		`tag` varchar(32) NOT NULL,
		`session_id` int(11) NOT NULL,
		PRIMARY KEY (`session_id`,`tag`)
	  ) ENGINE=InnoDb DEFAULT CHARSET=cp1251;

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
	  ) ENGINE=InnoDb  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

	  --
	  -- Dumping data for table `users`
	  --

	  INSERT INTO `users` (`login`, `pass`, `session_id`, `last_auth`, `status`) VALUES
	  ('project', '2cf2260e621842818f540ca8d7ded4425dd31220', '', '0000-00-00 00:00:00', 0),
	  ('bgvlKW3ZAsLwzqLPkMXjG0oQJ6G4ax7eJxuZNbgN', 'f10f0c327ad8c1c192c85b06da730249806dcbe9', '56408478bfaf569d72b7c5f8fe40196dd842f3a2', '2012-04-20 04:02:01', 1);
	");
});


