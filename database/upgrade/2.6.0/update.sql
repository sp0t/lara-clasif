-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Sept 14, 2017 at 15:07 AM
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

-- companies
DROP TABLE IF EXISTS `<<prefix>>companies`;
CREATE TABLE `<<prefix>>companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `country_code` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `city_id` int(10) UNSIGNED DEFAULT '0',
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fax` varchar(60) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `facebook` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `twitter` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `linkedin` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `googleplus` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pinterest` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `<<prefix>>companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `country_code` (`country_code`),
  ADD KEY `city_id` (`city_id`);

ALTER TABLE `<<prefix>>companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;



-- payment_methods
ALTER TABLE `<<prefix>>payment_methods` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;


-- posts
ALTER TABLE `<<prefix>>posts` ADD `company_id` INT UNSIGNED NULL DEFAULT '0' AFTER `user_id`;
ALTER TABLE `<<prefix>>posts` ADD INDEX(`company_id`);
ALTER TABLE `<<prefix>>posts` DROP `company_website`;
ALTER TABLE `<<prefix>>posts` CHANGE `company_description` `company_description` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>posts` ADD `application_url` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `start_date`;
ALTER TABLE `<<prefix>>posts` ADD `tags` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `description`;
ALTER TABLE `<<prefix>>posts` ADD INDEX(`tags`);


-- resumes
ALTER TABLE `<<prefix>>resumes` ADD `name` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `user_id`;
ALTER TABLE `<<prefix>>resumes` CHANGE `filename` `filename` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;


-- settings
UPDATE `<<prefix>>settings` SET `key` = 'guests_can_post_ads' WHERE `key` = 'activation_guests_can_post';

UPDATE `<<prefix>>settings` SET `field`='{"name":"value","label":"Value","type":"textarea","hint":"Paste your Google Analytics (or other) tracking code here. This will be added into the footer."}' WHERE `key`='tracking_code';

INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`) VALUES('guests_can_apply_jobs', 'Guests can apply Jobs', '1', 'Guests can apply Jobs', '{"name":"value","label":"Activation","type":"checkbox"}', 0, 62, 63, 1, 1, NULL, '2017-09-15 08:14:08');



/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;