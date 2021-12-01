-- Adminer 4.7.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `jd_ck`;
CREATE TABLE `jd_ck` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `username` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nickname` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `jd_wskey`;
CREATE TABLE `jd_wskey` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `username` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wskey` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enable` tinyint(3) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `push_user`;
CREATE TABLE `push_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `node_id` tinyint(3) unsigned NOT NULL,
  `wecom_id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `wskey2cookie` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wecom_id` (`wecom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
