-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Feb 09, 2017 at 09:13 AM
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

ALTER TABLE `<<prefix>>languages` ADD `russian_pluralization` TINYINT(1) UNSIGNED NULL DEFAULT '0' AFTER `script`;
ALTER TABLE `<<prefix>>currencies` 
	ADD `decimal_places` INT UNSIGNED NULL DEFAULT '2' COMMENT 'Currency Decimal Places - ISO 4217' AFTER `in_left`,
	ADD `decimal_separator` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '.' AFTER `decimal_places`, 
	ADD `thousand_separator` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT ',' AFTER `decimal_separator`;

UPDATE `<<prefix>>currencies` SET `decimal_places` = '2';
UPDATE `<<prefix>>currencies` SET `decimal_separator` = '.';
UPDATE `<<prefix>>currencies` SET `thousand_separator` = ',';

ALTER TABLE `<<prefix>>ads` CHANGE `salary_min` `salary_min` DECIMAL(10,2) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>ads` CHANGE `salary_max` `salary_max` DECIMAL(10,2) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>packs` CHANGE `price` `price` DECIMAL(10,2) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>countries` CHANGE `phone` `phone` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;


INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`) 
VALUES
	('ads_per_page', 'Ads per page', '12', 'Number of ads per page (> 4 and < 40)', '{"name":"value","label":"Value","type":"text"}', '0', '18', '19', '1', '1', NULL, '2017-02-08 13:51:10'),
	('decimals_superscript', 'Decimals Superscript', '0', 'Decimals Superscript (For Price, Salary, etc.)', '{"name":"value","label":"Activation","type":"checkbox"}', '0', '19', '19', '1', '1', NULL, '2017-02-08 13:51:10'),
	('simditor_wysiwyg', 'Simditor WYSIWYG Editor', '1', 'Simditor WYSIWYG Editor', '{"name":"value","label":"Activation","type":"checkbox"}', '0', '19', '19', '1', '1', NULL, '2017-02-08 13:51:10'),
	('ckeditor_wysiwyg', 'CKEditor WYSIWYG Editor', '0', 'CKEditor WYSIWYG Editor (For commercial use: http://ckeditor.com/pricing) - You need to disable the "Simditor WYSIWYG Editor"', '{"name":"value","label":"Activation","type":"checkbox"}', '0', '19', '19', '1', '1', NULL, '2017-02-08 13:51:10'),
	('admin_theme', 'Admin Theme', 'skin-blue', 'Admin Panel Theme', '{"name":"value","label":"Value","type":"select_from_array","options":{"skin-black":"Black","skin-blue":"Blue","skin-purple":"Purple","skin-red":"Red","skin-yellow":"Yellow","skin-green":"Green","skin-blue-light":"Blue light","skin-black-light":"Black light","skin-purple-light":"Purple light","skin-green-light":"Green light","skin-red-light":"Red light","skin-yellow-light":"Yellow light"}}', '0', '13', '13', '1', '1', NULL, '2017-02-12 03:53:11');

RENAME TABLE `<<prefix>>packs` TO `<<prefix>>packages`;
ALTER TABLE `<<prefix>>payments` CHANGE `pack_id` `package_id` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>ads` CHANGE `pack_id` `package_id` INT(10) UNSIGNED NULL DEFAULT NULL;


DROP TABLE IF EXISTS `<<prefix>>pages`;
CREATE TABLE `<<prefix>>pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `translation_lang` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `translation_of` int(10) UNSIGNED DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `type` enum('standard','terms','privacy') COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slug` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8_unicode_ci,
  `lft` int(10) UNSIGNED DEFAULT NULL,
  `rgt` int(10) UNSIGNED DEFAULT NULL,
  `depth` int(10) UNSIGNED DEFAULT NULL,
  `name_color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title_color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `<<prefix>>pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translation_lang` (`translation_lang`),
  ADD KEY `translation_of` (`translation_of`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `<<prefix>>pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;




/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;