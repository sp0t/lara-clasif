-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Fev 26, 2018 at 09:45 AM
-- Server version: 5.5.42
-- PHP Version: 5.6.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `jobclass`
--


-- categories
ALTER TABLE `<<prefix>>categories` CHANGE `css_class` `icon_class` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;


-- payment_methods
ALTER TABLE `<<prefix>>payment_methods` ADD `is_compatible_api` TINYINT(1) NULL DEFAULT '0' AFTER `has_ccbox`;


-- settings
ALTER TABLE `<<prefix>>settings` CHANGE `field` `field` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

UPDATE `<<prefix>>settings` SET `field` = NULL WHERE `key` = 'app';
UPDATE `<<prefix>>settings` SET `field` = NULL WHERE `key` = 'style';
UPDATE `<<prefix>>settings` SET `field` = NULL WHERE `key` = 'listing';
UPDATE `<<prefix>>settings` SET `field` = NULL WHERE `key` = 'seo';
UPDATE `<<prefix>>settings` SET `field` = NULL WHERE `key` = 'other';
UPDATE `<<prefix>>settings` SET `field` = NULL WHERE `key` = 'cron';


-- users
ALTER TABLE `<<prefix>>users` ADD `language_code` VARCHAR(10) NULL DEFAULT NULL AFTER `country_code`;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;