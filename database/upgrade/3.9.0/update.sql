-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: May 22, 2018 at 11:08 AM
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


-- home_sections
ALTER TABLE `<<prefix>>home_sections` CHANGE `method` `method` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' AFTER `id`;
ALTER TABLE `<<prefix>>home_sections` CHANGE `options` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>home_sections` ADD `field` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `view`;


-- languages
ALTER TABLE `<<prefix>>languages`
	ADD `parent_id` INT UNSIGNED NULL DEFAULT NULL AFTER `default`,
	ADD `lft` INT UNSIGNED NULL DEFAULT NULL AFTER `parent_id`,
	ADD `rgt` INT UNSIGNED NULL DEFAULT NULL AFTER `lft`,
	ADD `depth` INT UNSIGNED NULL DEFAULT NULL AFTER `rgt`;


-- settings
UPDATE `<<prefix>>settings` SET `value` = REPLACE(`value`, '"name":', '"app_name":') WHERE `key` = 'app';


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;