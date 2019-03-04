-- phpMyAdmin SQL Dump
-- version 2.9.0.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jan 05, 2007 at 02:59 AM
-- Server version: 4.1.21
-- PHP Version: 4.4.2
-- 
-- Database: `darkain_cosplay`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_img`
-- 

CREATE TABLE `cosplay_img` (
  `image_id` int(11) NOT NULL auto_increment,
  `image_url` mediumtext NOT NULL,
  `image_desc` text,
  `image_width` smallint(6) NOT NULL default '0',
  `image_height` smallint(6) NOT NULL default '0',
  `image_twidth` mediumint(9) NOT NULL default '0',
  `image_theight` mediumint(9) NOT NULL default '0',
  `image_size` int(11) NOT NULL default '0',
  `image_hash` varchar(32) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=111 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_imglink`
-- 

CREATE TABLE `cosplay_imglink` (
  `image_id` int(11) NOT NULL default '0',
  `meta_id` int(11) NOT NULL default '0',
  KEY `image_id` (`image_id`,`meta_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_imgpend`
-- 

CREATE TABLE `cosplay_imgpend` (
  `image_id` int(11) NOT NULL default '0',
  `meta_id` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `pend_action` enum('add','remove') NOT NULL default 'add',
  `pend_vote` enum('yes','no') NOT NULL default 'yes',
  KEY `image_id` (`image_id`,`meta_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_meta`
-- 

CREATE TABLE `cosplay_meta` (
  `meta_id` int(11) NOT NULL auto_increment,
  `metatype_id` int(11) NOT NULL default '0',
  `meta_data` tinytext NOT NULL,
  `meta_desc` text,
  PRIMARY KEY  (`meta_id`),
  KEY `metatype_id` (`metatype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=888 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_metalink`
-- 

CREATE TABLE `cosplay_metalink` (
  `meta_id` int(11) NOT NULL default '0',
  `meta_child` int(11) NOT NULL default '0',
  KEY `meta_parent` (`meta_id`,`meta_child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_metapend`
-- 

CREATE TABLE `cosplay_metapend` (
  `meta_id` int(11) NOT NULL default '0',
  `meta_child` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `pend_action` enum('add','remove') NOT NULL default 'add',
  `pend_vote` enum('yes','no') NOT NULL default 'yes',
  KEY `meta_id` (`meta_id`,`meta_child`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_metatype`
-- 

CREATE TABLE `cosplay_metatype` (
  `metatype_id` int(11) NOT NULL auto_increment,
  `metatype_name` tinytext NOT NULL,
  `metatype_desc` text,
  PRIMARY KEY  (`metatype_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_sessions`
-- 

CREATE TABLE `cosplay_sessions` (
  `ses_id` varchar(32) character set ascii collate ascii_bin NOT NULL default '',
  `ses_ip` int(11) NOT NULL default '0',
  `user_id` int(11) NOT NULL default '0',
  `ses_timeout` int(11) NOT NULL default '0',
  `ses_agent` text NOT NULL,
  PRIMARY KEY  (`ses_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table structure for table `cosplay_users`
-- 

CREATE TABLE `cosplay_users` (
  `user_id` int(11) NOT NULL auto_increment,
  `user_name` varchar(32) NOT NULL default '',
  `user_pass` varchar(32) NOT NULL default '',
  `user_email` varchar(64) NOT NULL default '',
  `user_rights` enum('user','mod','admin') NOT NULL default 'user',
  `user_lang` varchar(6) default 'en-us',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;
