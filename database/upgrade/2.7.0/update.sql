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

-- home_sections
INSERT INTO `<<prefix>>home_sections` (`name`, `method`, `options`, `view`, `parent_id`, `lft`, `rgt`, `depth`, `active`) VALUES('Search Form (Always in Top)', 'getSearchForm', '{"enable_form_area_customization":"1","background_color":null,"background_image":null,"form_border_color":null,"form_border_size":null,"form_btn_background_color":null,"form_btn_text_color":null,"hide_titles":"0","big_title_color":null,"sub_title_color":null}', 'home.inc.search', 0, 0, 0, 1, 1);


-- settings
INSERT INTO `<<prefix>>settings` (`key`, `name`, `value`, `description`, `field`, `parent_id`, `lft`, `rgt`, `depth`, `active`, `created_at`, `updated_at`) 
VALUES
  ('google_plus_url', 'Google+ URL', '#', 'Website Google+ URL', '{"name":"value","label":"Value","type":"text"}', 0, 51, 52, 1, 1, NULL, '2017-10-10 14:10:16'),
  ('linkedin_url', 'LinkedIn URL', '#', 'Website LinkedIn URL', '{"name":"value","label":"Value","type":"text"}', 0, 52, 53, 1, 1, NULL, '2017-10-10 14:10:16'),
  ('pinterest_url', 'Pinterest URL', '#', 'Website Pinterest URL', '{"name":"value","label":"Value","type":"text"}', 0, 53, 54, 1, 1, NULL, '2017-10-10 14:10:16');


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;