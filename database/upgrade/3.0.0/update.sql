-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Oct 23, 2017 at 10:12 AM
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

-- countries
ALTER TABLE `<<prefix>>countries` ADD `background_image` VARCHAR(255) NULL DEFAULT NULL AFTER `equivalent_fips_code`;


-- languages
ALTER TABLE `<<prefix>>languages` ADD `direction` ENUM('ltr','rtl') NOT NULL DEFAULT 'ltr' AFTER `script`;


-- pictures
ALTER TABLE `<<prefix>>pictures` ADD `position` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `filename`;


-- settings
DELETE FROM `<<prefix>>settings` WHERE `key`='guests_can_contact_seller';
DELETE FROM `<<prefix>>settings` WHERE `key`='guests_can_apply_jobs';
INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`) VALUES('guests_can_apply_jobs', 'Guests can apply Jobs', '1', 'Guests can apply Jobs', '{"name":"value","label":"Activation","type":"checkbox"}', 0, 62, 63, 1, 1, NULL, '2017-11-14 07:07:25');

DELETE FROM `<<prefix>>settings` WHERE `key`='google_plus_url';
DELETE FROM `<<prefix>>settings` WHERE `key`='linkedin_url';
DELETE FROM `<<prefix>>settings` WHERE `key`='pinterest_url';
INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`) 
VALUES
  ('google_plus_url', 'Google+ URL', '#', 'Website Google+ URL', '{"name":"value","label":"Value","type":"text"}', 0, 51, 52, 1, 1, NULL, '2017-11-14 11:35:16'),
  ('linkedin_url', 'LinkedIn URL', '#', 'Website LinkedIn URL', '{"name":"value","label":"Value","type":"text"}', 0, 52, 53, 1, 1, NULL, '2017-11-14 11:35:16'),
  ('pinterest_url', 'Pinterest URL', '#', 'Website Pinterest URL', '{"name":"value","label":"Value","type":"text"}', 0, 53, 54, 1, 1, NULL, '2017-11-14 11:35:16');



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;