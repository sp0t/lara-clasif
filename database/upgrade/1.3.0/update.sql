-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2016 at 03:37 PM
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

ALTER TABLE `<<prefix>>time_zones` DROP INDEX country_code;
ALTER TABLE `<<prefix>>time_zones` ADD INDEX(`country_code`);
ALTER TABLE `<<prefix>>time_zones` ADD UNIQUE(`time_zone_id`);

ALTER TABLE `<<prefix>>cities` CHANGE `longitude` `longitude_tmp` FLOAT NULL DEFAULT NULL COMMENT 'longitude in decimal degrees (wgs84)';
ALTER TABLE `<<prefix>>cities` CHANGE `latitude` `longitude` FLOAT NULL DEFAULT NULL COMMENT 'longitude in decimal degrees (wgs84)';
ALTER TABLE `<<prefix>>cities` CHANGE `longitude_tmp` `latitude` FLOAT NULL DEFAULT NULL COMMENT 'latitude in decimal degrees (wgs84)';

ALTER TABLE `<<prefix>>ads` CHANGE `lon` `lon_tmp` FLOAT NULL DEFAULT NULL COMMENT 'longitude in decimal degrees (wgs84)';
ALTER TABLE `<<prefix>>ads` CHANGE `lat` `lon` FLOAT NULL DEFAULT NULL COMMENT 'longitude in decimal degrees (wgs84)';
ALTER TABLE `<<prefix>>ads` CHANGE `lon_tmp` `lat` FLOAT NULL DEFAULT NULL COMMENT 'latitude in decimal degrees (wgs84)';



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;