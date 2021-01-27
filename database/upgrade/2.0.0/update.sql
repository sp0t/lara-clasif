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

-- advertising
ALTER TABLE `<<prefix>>advertising` ADD UNIQUE(`slug`);
ALTER TABLE `<<prefix>>advertising` ADD INDEX(`active`);


-- categories
ALTER TABLE `<<prefix>>categories` ADD INDEX(`slug`);


-- cities
ALTER TABLE `<<prefix>>cities` ADD INDEX(`subadmin1_code`);
ALTER TABLE `<<prefix>>cities` ADD INDEX(`subadmin2_code`);
ALTER TABLE `<<prefix>>cities` ADD INDEX(`active`);

UPDATE <<prefix>>cities SET subadmin1_code = IF(LENGTH(subadmin1_code) > 0, CONCAT(country_code, '.', subadmin1_code), NULL), 
subadmin2_code = IF(LENGTH(subadmin2_code) > 0, CONCAT(IF(LENGTH(subadmin1_code) > 0, subadmin1_code, country_code), '.', subadmin2_code), NULL);


-- continents
ALTER TABLE `<<prefix>>continents` ADD INDEX(`active`);


-- countries
ALTER TABLE `<<prefix>>countries` ADD `admin_type` ENUM('0','1','2') NOT NULL DEFAULT '0' AFTER `equivalent_fips_code`;
ALTER TABLE `<<prefix>>countries` ADD `admin_field_active` TINYINT(1) UNSIGNED NULL DEFAULT '0' AFTER `admin_type`;
ALTER TABLE `<<prefix>>countries` ADD INDEX(`active`);


-- currencies
ALTER TABLE `<<prefix>>currencies` CHANGE `in_left` `in_left` TINYINT(1) UNSIGNED NULL DEFAULT '0';
INSERT INTO `<<prefix>>currencies` (`code`, `name`, `html_entity`, `font_arial`, `font_code2000`, `unicode_decimal`, `unicode_hex`, `in_left`, `decimal_places`, `decimal_separator`, `thousand_separator`, `created_at`, `updated_at`) VALUES ('XBT', 'Bitcoin', '฿', '฿', '฿', NULL, NULL, 1, 2, '.', ',', NULL, '2017-04-08 04:49:08');


-- languages
ALTER TABLE `<<prefix>>languages` ADD INDEX(`active`);
ALTER TABLE `<<prefix>>languages` ADD INDEX(`default`);


-- messages
ALTER TABLE `<<prefix>>messages` ADD `reply_sent` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `filename`;
ALTER TABLE `<<prefix>>messages` CHANGE `ad_id` `post_id` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>messages` ADD INDEX(`post_id`);


-- packages
ALTER TABLE `<<prefix>>packages` CHANGE `has_badge` `has_badge` TINYINT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `<<prefix>>packages` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `<<prefix>>packages` ADD INDEX(`active`);


-- pages
ALTER TABLE `<<prefix>>pages` CHANGE `excluded_from_footer` `excluded_from_footer` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `<<prefix>>pages` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `<<prefix>>pages` ADD INDEX(`active`);


-- payments
ALTER TABLE `<<prefix>>payments` ADD `active` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `payment_method_id`;
ALTER TABLE `<<prefix>>payments` CHANGE `ad_id` `post_id` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>payments` ADD `transaction_id` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT 'Transaction\'s ID at the Provider' AFTER `payment_method_id`;
ALTER TABLE `<<prefix>>payments` DROP INDEX `ad_id`;
ALTER TABLE `<<prefix>>payments` ADD INDEX(`post_id`);
ALTER TABLE `<<prefix>>payments` ADD INDEX(`active`);


-- payment_methods
ALTER TABLE `<<prefix>>payment_methods` ADD `countries` TEXT NULL DEFAULT NULL COMMENT 'Countries codes separated by comma.' AFTER `has_ccbox`;
ALTER TABLE `<<prefix>>payment_methods` ADD INDEX(`has_ccbox`);
ALTER TABLE `<<prefix>>payment_methods` ADD INDEX(`active`);


-- pictures
ALTER TABLE `<<prefix>>pictures` CHANGE `ad_id` `post_id` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>pictures` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `<<prefix>>pictures` DROP INDEX `ad_id`;
ALTER TABLE `<<prefix>>pictures` ADD INDEX(`post_id`);
ALTER TABLE `<<prefix>>pictures` ADD INDEX(`active`);


-- posts
RENAME TABLE `<<prefix>>ads` TO `<<prefix>>posts`;
ALTER TABLE `<<prefix>>posts` DROP `package_id`;
ALTER TABLE `<<prefix>>posts` ADD `tmp_token` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `activation_token`;
ALTER TABLE `<<prefix>>posts` CHANGE `ad_type_id` `post_type_id` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>posts` CHANGE `activation_token` `email_token` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>posts` ADD `phone_token` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `email_token`;
ALTER TABLE `<<prefix>>posts` CHANGE `active` `verified_email` TINYINT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `<<prefix>>posts` ADD `verified_phone` TINYINT(1) UNSIGNED NULL DEFAULT '1' AFTER `verified_email`;
ALTER TABLE `<<prefix>>posts` 
	CHANGE `contact_name` `contact_name` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, 
	CHANGE `contact_email` `email` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, 
	CHANGE `contact_phone` `phone` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, 
	CHANGE `contact_phone_hidden` `phone_hidden` TINYINT(1) NULL DEFAULT '0';
ALTER TABLE `<<prefix>>posts` 
	CHANGE `country_code` `country_code` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL, 
	CHANGE `category_id` `category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0', 
	CHANGE `city_id` `city_id` INT(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `<<prefix>>posts` 
  CHANGE `negotiable` `negotiable` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
  CHANGE `phone_hidden` `phone_hidden` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
  CHANGE `verified_email` `verified_email` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
  CHANGE `reviewed` `reviewed` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
  CHANGE `archived` `archived` TINYINT(1) UNSIGNED NULL DEFAULT '0';
ALTER TABLE `<<prefix>>posts` DROP INDEX `ad_type_id`;
ALTER TABLE `<<prefix>>posts` ADD INDEX(`post_type_id`);
ALTER TABLE `<<prefix>>posts` DROP INDEX `active`;
ALTER TABLE `<<prefix>>posts` ADD INDEX(`verified_email`);
ALTER TABLE `<<prefix>>posts` ADD INDEX(`verified_phone`);
UPDATE `<<prefix>>posts` SET `verified_phone` = 1;


-- post_types
RENAME TABLE `<<prefix>>ad_type` TO `<<prefix>>post_types`;
ALTER TABLE `<<prefix>>post_types` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `<<prefix>>post_types` ADD INDEX(`active`);


-- report_type
RENAME TABLE `<<prefix>>report_type` TO `<<prefix>>report_types`;


-- resumes
ALTER TABLE `<<prefix>>resumes` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '0';


-- roles
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `<<prefix>>permissions`;
DROP TABLE IF EXISTS `<<prefix>>permission_role`;
DROP TABLE IF EXISTS `<<prefix>>roles`;
DROP TABLE IF EXISTS `<<prefix>>role_users`;
SET FOREIGN_KEY_CHECKS=1;


-- saved_posts
RENAME TABLE `<<prefix>>saved_ads` TO `<<prefix>>saved_posts`;
ALTER TABLE `<<prefix>>saved_posts` CHANGE `ad_id` `post_id` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>saved_posts` DROP INDEX `ad_id`;
ALTER TABLE `<<prefix>>saved_posts` ADD INDEX(`post_id`);


-- salary_types
RENAME TABLE `<<prefix>>salary_type` TO `<<prefix>>salary_types`;
ALTER TABLE `<<prefix>>salary_types` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `<<prefix>>salary_types` ADD INDEX(`active`);


-- settings
ALTER TABLE `<<prefix>>settings` ADD INDEX(`active`);

UPDATE `<<prefix>>settings` 
	SET `key` = 'email_verification', 
		`name` = 'Email verification required', 
		`description` = 'Email verification required' 
	WHERE `key` = 'require_users_activation';
	
UPDATE `<<prefix>>settings` 
	SET `key` = 'phone_verification', 
		`name` = 'Phone verification required', 
		`description` = 'Phone verification required' 
	WHERE `key` = 'require_ads_activation';
	
UPDATE `<<prefix>>settings` 
	SET `key` = 'app_cache_expiration', 
		`name` = 'Cache Expiration Time', 
		`description` = 'Cache Expiration Time (in minutes)' 
	WHERE `key` = 'app_cache_expire';
	
UPDATE `<<prefix>>settings` 
	SET `key` = 'app_cookie_expiration', 
		`name` = 'Cookie Expiration Time', 
		`description` = 'Cookie Expiration Time (in secondes)' 
	WHERE `key` = 'app_cookie_expire';

UPDATE `<<prefix>>settings` 
	SET `key` = 'app_skin', 
		`name` = 'Front Skin',
		`value` = IF(LENGTH(`value`) > 0, CONCAT('skin-', `value`), NULL),
		`field` = '{"name":"value","label":"Value","type":"select_from_array","options":{"skin-default":"Default","skin-blue":"Blue","skin-yellow":"Yellow","skin-green":"Green","skin-red":"Red"}}' 
	WHERE `key` = 'app_theme';

UPDATE `<<prefix>>settings` 
	SET `key` = 'admin_skin', 
		`name` = 'Admin Skin', 
		`description` = 'Admin Panel Skin' 
	WHERE `key` = 'admin_theme';

UPDATE `<<prefix>>settings` SET `active`=1 WHERE `key`='sparkpost_secret';

UPDATE `<<prefix>>settings` 
	SET `description`='e.g. smtp, mailgun, mandrill, ses, sparkpost, mail, sendmail', 
		`field`='{"name":"value","label":"Value","type":"select_from_array","options":{"smtp":"SMTP","mailgun":"Mailgun","mandrill":"Mandrill","ses":"Amazon SES","sparkpost":"Sparkpost","mail":"PHP Mail","sendmail":"Sendmail"}}' 
	WHERE `key`='mail_driver';
	
UPDATE `<<prefix>>settings` 
	SET `description`='Before enabling this option you need to download the Maxmind database by following the documentation: http://www.bedigit.com/doc/geo-location/' 
	WHERE `key`='activation_geolocation';

UPDATE `<<prefix>>settings` SET `key` = 'show_post_on_googlemap' WHERE `key` = 'show_ad_on_googlemap';
UPDATE `<<prefix>>settings` SET `key` = 'unactivated_posts_expiration' WHERE `key` = 'unactivated_ads_expiration';
UPDATE `<<prefix>>settings` SET `key` = 'activated_posts_expiration' WHERE `key` = 'activated_ads_expiration';
UPDATE `<<prefix>>settings` SET `key` = 'archived_posts_expiration' WHERE `key` = 'archived_ads_expiration';
UPDATE `<<prefix>>settings` SET `key` = 'posts_per_page' WHERE `key` = 'ads_per_page';
UPDATE `<<prefix>>settings` SET `key` = 'posts_pictures_number' WHERE `key` = 'ads_pictures_number';
UPDATE `<<prefix>>settings` SET `key` = 'posts_review_activation' WHERE `key` = 'ads_review_activation';

DELETE FROM `<<prefix>>settings` WHERE `key`='meta_description';
DELETE FROM `<<prefix>>settings` WHERE `key`='activation_home_stats';
DELETE FROM `<<prefix>>settings` WHERE `key`='facebook_page_fans';
DELETE FROM `<<prefix>>settings` WHERE `key`='show_country_svgmap';

INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`)
VALUES
  ('sms_driver', 'SMS driver', 'nexmo', 'e.g. nexmo, twilio', '{"name":"value","label":"Value","type":"select_from_array","options":{"nexmo":"Nexmo","twilio":"Twilio"}}', 0, 86, 86, 1, 1, NULL, '2017-04-12 13:06:19'),
  ('sms_message_activation', 'SMS Message Activation', '0', 'Users can contact the author by SMS. Note: You need to set the "SMS driver" setting.', '{"name":"value","label":"Activation","type":"checkbox"}', 0, 87, 87, 1, 1, NULL, '2017-06-17 13:06:19');


-- subadmin1
ALTER TABLE `<<prefix>>subadmin1` ADD INDEX(`active`);
ALTER TABLE `<<prefix>>subadmin1` ADD `country_code` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `code`;
ALTER TABLE `<<prefix>>subadmin1` ADD INDEX(`country_code`);
UPDATE <<prefix>>subadmin1 SET country_code = SUBSTRING(code, 1, 2);


-- subadmin2
ALTER TABLE `<<prefix>>subadmin2` ADD INDEX(`active`);
ALTER TABLE `<<prefix>>subadmin2` ADD `country_code` VARCHAR(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `code`;
ALTER TABLE `<<prefix>>subadmin2` ADD `subadmin1_code` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `country_code`;
ALTER TABLE `<<prefix>>subadmin2` ADD INDEX(`country_code`);
ALTER TABLE `<<prefix>>subadmin2` ADD INDEX(`subadmin1_code`);
UPDATE <<prefix>>subadmin2 SET country_code = SUBSTRING(code, 1, 2);
UPDATE <<prefix>>subadmin2 SET subadmin1_code = SUBSTRING_INDEX(code, '.', 2);


-- users
ALTER TABLE `<<prefix>>users` CHANGE `activation_token` `email_token` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `<<prefix>>users` ADD `phone_token` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER `email_token`;

ALTER TABLE `<<prefix>>users` CHANGE `active` `verified_email` TINYINT(1) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `<<prefix>>users` ADD `verified_phone` TINYINT(1) UNSIGNED NULL DEFAULT '1' AFTER `verified_email`;
ALTER TABLE `<<prefix>>users` ADD INDEX(`verified_email`);
ALTER TABLE `<<prefix>>users` ADD INDEX(`verified_phone`);

ALTER TABLE `<<prefix>>users` ADD `username` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `phone_hidden`;

ALTER TABLE `<<prefix>>users` ADD INDEX(`username`);
ALTER TABLE `<<prefix>>users` ADD INDEX(`phone`);
ALTER TABLE `<<prefix>>users` ADD INDEX(`email`);

ALTER TABLE `<<prefix>>users` 
	CHANGE `phone_hidden` `phone_hidden` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
	CHANGE `is_admin` `is_admin` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
	CHANGE `disable_comments` `disable_comments` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
	CHANGE `receive_newsletter` `receive_newsletter` TINYINT(1) UNSIGNED NULL DEFAULT '1', 
	CHANGE `receive_advice` `receive_advice` TINYINT(1) UNSIGNED NULL DEFAULT '1', 
	CHANGE `verified_email` `verified_email` TINYINT(1) UNSIGNED NULL DEFAULT '1', 
	CHANGE `blocked` `blocked` TINYINT(1) UNSIGNED NULL DEFAULT '0', 
	CHANGE `closed` `closed` TINYINT(1) UNSIGNED NULL DEFAULT '0';

UPDATE `<<prefix>>users` SET `verified_phone` = 1;

-- user_types
RENAME TABLE `<<prefix>>user_type` TO `<<prefix>>user_types`;
ALTER TABLE `<<prefix>>user_types` CHANGE `id` `id` TINYINT(1) UNSIGNED NOT NULL;
ALTER TABLE `<<prefix>>user_types` CHANGE `active` `active` TINYINT(1) UNSIGNED NULL DEFAULT '1';
ALTER TABLE `<<prefix>>user_types` ADD INDEX(`active`);





-- home_sections
DROP TABLE IF EXISTS `<<prefix>>home_sections`;
CREATE TABLE `<<prefix>>home_sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `method` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `options` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `view` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `lft` int(10) UNSIGNED DEFAULT NULL,
  `rgt` int(10) UNSIGNED DEFAULT NULL,
  `depth` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(1, 'Locations & SVG Map', 'getLocations', '{\"max_items\":null,\"show_map\":\"1\",\"map_background_color\":null,\"map_border\":null,\"map_hover_border\":null,\"map_border_width\":null,\"map_color\":null,\"map_hover\":null,\"map_width\":null,\"map_height\":null,\"cache_expiration\":null}', 'home.inc.locations', 0, 2, 3, 1, 1);
INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(2, 'Sponsored ads', 'getSponsoredPosts', '{\"max_items\":null,\"autoplay\":\"1\",\"autoplay_timeout\":null,\"cache_expiration\":null}', 'home.inc.featured', 0, 4, 5, 1, 1);
INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(3, 'Latest ads', 'getLatestPosts', '{\"max_items\":\"10\",\"cache_expiration\":null}', 'home.inc.latest', 0, 8, 9, 1, 1);
INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(4, 'Categories', 'getCategories', '{\"cache_expiration\":null}', 'home.inc.categories', 0, 10, 11, 1, 1);
INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(5, 'Mini stats', 'getStats', NULL, 'home.inc.stats', 0, 12, 13, 1, 1);
INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(6, 'Bottom advertising', 'getBottomAdvertising', NULL, 'layouts.inc.advertising.bottom', 0, 14, 15, 1, 0);
INSERT INTO `<<prefix>>home_sections` (`id`, `name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES(7, 'Companies', 'getFeaturedPostsCompanies', '{\"max_items\":null,\"cache_expiration\":null}', 'home.inc.companies', 0, 6, 7, 1, 1);

ALTER TABLE `<<prefix>>home_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`);

ALTER TABLE `<<prefix>>home_sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;





-- meta_tags
DROP TABLE IF EXISTS `<<prefix>>meta_tags`;
CREATE TABLE `<<prefix>>meta_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `translation_lang` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `translation_of` int(10) UNSIGNED NOT NULL,
  `page` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `keywords` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT '',
  `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `<<prefix>>meta_tags`
--

INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(1, 'en', 1, 'home', '{app_name} - Geolocalized Job Board Script', 'Welcome to {app_name} : 100% Job Board. Find a job near you. Simple, fast and efficient - {country}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(2, 'en', 2, 'register', 'Sign Up - {app_name}', 'Sign Up on {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(3, 'en', 3, 'login', 'Login - {app_name}', 'Log in to {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(4, 'en', 4, 'create', 'Post a Job', 'Post a Job - {country}.', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(5, 'en', 5, 'countries', 'Jobs in the World', 'Welcome to {app_name} : 100% Job Board. Find a job near you. Simple, fast and efficient.', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(6, 'en', 6, 'contact', 'Contact Us - {app_name}', 'Contact Us - {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(7, 'en', 7, 'sitemap', 'Sitemap {app_name} - {country}', 'Sitemap {app_name} - {country}. Job Bord.', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(8, 'fr', 1, 'home', '{app_name} - Geolocalized Job Board Script', 'Bienvenue sur {app_name} : Site d\'emplois 100% gratuit. Trouvez un travail près de chez vous. Simple, rapide et efficace - {country}', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(9, 'es', 1, 'home', '{app_name} - Geolocalized Job Board Script', 'Welcome to {app_name} : 100% Job Board. Find a job near you. Simple, fast and efficient - {country}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(10, 'fr', 2, 'register', 'S\'inscrire - {app_name}', 'S\'inscrire sur {app_name}', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(11, 'es', 2, 'register', 'Sign Up - {app_name}', 'Sign Up on {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(12, 'fr', 3, 'login', 'S\'identifier - {app_name}', 'S\'identifier sur {app_name}', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(13, 'es', 3, 'login', 'Login - {app_name}', 'Log in to {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(14, 'fr', 4, 'create', 'Publiez une offre d\'emploi', 'Publiez une offre d\'emploi - {country}.', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(15, 'es', 4, 'create', 'Publicar un anuncio gratuito', 'Publicar un anuncio gratuito - {country}.', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(16, 'fr', 5, 'countries', 'Emplois dans le monde', 'Bienvenue sur {app_name} : Site d\'emplois 100% gratuit. Trouvez un travail près de chez vous. Simple, rapide et efficace.', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(17, 'es', 5, 'countries', 'Jobs in the World', 'Welcome to {app_name} : 100% Job Board. Find a job near you. Simple, fast and efficient.', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(18, 'fr', 6, 'contact', 'Nous contacter - {app_name}', 'Nous contacter - {app_name}', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(19, 'es', 6, 'contact', 'Contact Us - {app_name}', 'Contact Us - {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(20, 'fr', 7, 'sitemap', 'Plan du site {app_name} - {country}', 'Plan du site {app_name} - {country}. Site d\'emplois.', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(21, 'es', 7, 'sitemap', 'Sitemap {app_name} - {country}', 'Sitemap {app_name} - {country}. Job Bord.', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(22, 'en', 22, 'password', 'Lost your password? - {app_name}', 'Lost your password? - {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(23, 'fr', 22, 'password', 'Mot de passe oublié? - {app_name}', 'Mot de passe oublié? - {app_name}', '{app_name}, {country}, offres d\'emploi, emplois, annonces, script, app, premium jobs', 1);
INSERT INTO `<<prefix>>meta_tags` (`id`, `translation_lang`, `translation_of`, `page`, `title`, `description`, `keywords`, `active`) VALUES(24, 'es', 22, 'password', '¿Perdiste tu contraseña? - {app_name}', '¿Perdiste tu contraseña? - {app_name}', '{app_name}, {country}, jobs ads, jobs, ads, script, app, premium jobs', 1);

ALTER TABLE `<<prefix>>meta_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `translation_lang` (`translation_lang`),
  ADD KEY `translation_of` (`translation_of`),
  ADD KEY `active` (`active`);

ALTER TABLE `<<prefix>>meta_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;