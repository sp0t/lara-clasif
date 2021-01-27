-- phpMyAdmin SQL Dump
-- version 4.4.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Mar 13, 2017 at 10:18 AM
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

ALTER TABLE `<<prefix>>pages` CHANGE `type` `type` ENUM('standard','terms','privacy','tips') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `<<prefix>>pages` ADD `excluded_from_footer` TINYINT UNSIGNED DEFAULT '0' AFTER `title_color`;
ALTER TABLE `<<prefix>>ads` ADD `featured` TINYINT(1) UNSIGNED NULL DEFAULT '0' AFTER `reviewed`, ADD INDEX `featured` (`featured`);
ALTER TABLE `<<prefix>>settings` CHANGE `value` `value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

DELETE FROM `<<prefix>>settings` WHERE `key` = 'activation_serp_left_sidebar';
DELETE FROM `<<prefix>>settings` WHERE `key` LIKE 'paypal%';

INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`) 
VALUES
	('upload_image_types', 'Upload Image Types', 'jpg,jpeg,gif,png', 'Upload image types (ex: jpg,jpeg,gif,png,...)', '{"name":"value","label":"Value","type":"text"}', 0, 20, 21, 1, 1, NULL, '2017-02-21 15:02:43'),
	('upload_file_types', 'Upload File Types', 'pdf,doc,docx,word,rtf,rtx,ppt,pptx,odt,odp,wps,jpeg,jpg,bmp,png', 'Upload file types (ex: pdf,doc,docx,odt,...)', '{"name":"value","label":"Value","type":"text"}', 0, 20, 21, 1, 1, NULL, '2017-02-21 15:03:06'),
	('app_favicon', 'Favicon', NULL, 'Favicon (extension: png,jpg)', '{"name":"value","label":"Favicon","type":"image","upload":"true","disk":"uploads","default":"app/default/ico/favicon.png"}', 0, 4, 4, 1, 1, NULL, '2017-02-24 9:15:38'),
	('unactivated_ads_expiration', 'Unactivated Ads Expiration', '30', 'In days (Delete the unactivated ads after this expiration) - You need to add "/usr/bin/php -q /path/to/your/website/artisan ads:clean" in your Cron Job tab', '{"name":"value","label":"Value","type":"text"}', 0, 25, 25, 1, 1, NULL, '2017-03-14 19:31:10'),
	('activated_ads_expiration', 'Activated Ads Expiration', '150', 'In days (Archive the activated ads after this expiration) - You need to add "/usr/bin/php -q /path/to/your/website/artisan ads:clean" in your Cron Job tab', '{"name":"value","label":"Value","type":"text"}', 0, 25, 25, 1, 1, NULL, '2017-03-14 19:31:10'),
	('archived_ads_expiration', 'Archived Ads Expiration', '7', 'In days (Delete the archived ads after this expiration) - You need to add "/usr/bin/php -q /path/to/your/website/artisan ads:clean" in your Cron Job tab', '{"name":"value","label":"Value","type":"text"}', 0, 25, 25, 1, 1, NULL, '2017-03-14 19:31:10'),
	('app_email_sender', 'Transactional Email Sender', NULL, 'Transactional Email Sender. Example: noreply@yoursite.com', '{"name":"value","label":"Value","type":"email"}', 0, 9, 10, 1, 1, NULL, '2017-03-22 09:27:49');


UPDATE `<<prefix>>settings` SET `field`='{"name":"value","label":"Logo","type":"image","upload":"true","disk":"uploads","default":"app/default/logo.png"}' WHERE `key`='app_logo';
UPDATE `<<prefix>>settings` SET `field`='{"name":"value","label":"Value","type":"textarea","hint":"Please <strong>do not</strong> include the &lt;style&gt; tags."}' WHERE `key`='custom_css';
UPDATE `<<prefix>>settings` SET `key`='tracking_code', `name`='Tracking Code', `description`='Tracking Code (ex: Google Analytics Code)', `field`='{"name":"value","label":"Value","type":"textarea","hint":"Paste your Google Analytics (or other) tracking code here. This will be added into the footer. <br>Please <strong>do not</strong> include the &lt;script&gt; tags."}' WHERE `key`='seo_google_analytics';
UPDATE `<<prefix>>settings` s, (SELECT `key`, `value` FROM `<<prefix>>settings` WHERE `key`='app_email') ss SET s.value=ss.value WHERE s.key='app_email_sender';
UPDATE `<<prefix>>settings` SET `parent_id`=0;


DROP TABLE IF EXISTS `<<prefix>>payment_methods`;
CREATE TABLE `<<prefix>>payment_methods` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `has_ccbox` tinyint(1) UNSIGNED DEFAULT '0',
  `lft` int(10) UNSIGNED DEFAULT NULL,
  `rgt` int(10) UNSIGNED DEFAULT NULL,
  `depth` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `<<prefix>>payment_methods`
  ADD PRIMARY KEY (`id`);
  
ALTER TABLE `<<prefix>>payment_methods`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

INSERT INTO `<<prefix>>payment_methods` (`id`, `name`, `display_name`, `description`, `has_ccbox`, `lft`, `rgt`, `depth`, `active`) VALUES
(1, 'paypal', 'Paypal', 'Payment with Paypal', 0, 0, 0, 1, 1);


UPDATE `<<prefix>>ads` as a
INNER JOIN `<<prefix>>payments` as p ON p.ad_id=a.id
SET a.featured=1;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;