# noinspection SqlNoDataSourceInspectionForFile

CREATE DATABASE toto
  CHARACTER SET utf8
  COLLATE utf8_unicode_ci;

USE toto;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `geoname_id` INTEGER NOT NULL,
  `continent` VARCHAR(50) NOT NULL DEFAULT '',
  `country` VARCHAR(50) NOT NULL DEFAULT '',
  `region` VARCHAR(50) NOT NULL DEFAULT '',
  `city` VARCHAR(50) NOT NULL DEFAULT '',
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `location_geoname_id` (`geoname_id`)
);

DROP TABLE IF EXISTS `ipaddress`;
CREATE TABLE `ipaddress` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip_start` INTEGER NOT NULL,
  `ip_end` INTEGER NOT NULL,
  `geoname_id` INTEGER NOT NULL,
  `postcode` VARCHAR(10) NOT NULL DEFAULT '',
  `latitude` FLOAT NOT NULL,
  `longitude` FLOAT NOT NULL,
  `accuracy` INTEGER NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ipaddress_ip_start_ip_end` (`ip_start`, `ip_end`),
  CONSTRAINT `ipaddress_geoname_id_foreign` FOREIGN KEY (`geoname_id`) REFERENCES `location` (`geoname_id`) ON DELETE CASCADE ON UPDATE CASCADE
);


SET FOREIGN_KEY_CHECKS=1;


DROP PROCEDURE IF EXISTS `ipaddressFind`;
DELIMITER @@
CREATE PROCEDURE `ipaddressFind` (pIpAddress VARCHAR(100))
  BEGIN
    DECLARE vSearch6 BINARY(16);
    DECLARE vSearch4 BIGINT;

    IF IS_IPV6(pIpAddress) = 1 THEN
      SET vSearch6 = INET6_ATON(pIpAddress);
      SELECT * FROM ipaddress WHERE ip_start <= vSearch6 AND ip_end >= vSearch6;
    ELSEIF IS_IPV4(pIpAddress) = 1 THEN
      SET vSearch4 = INET_ATON(pIpAddress);
      SELECT * FROM ipaddress WHERE ipaddress.ip_start <= vSearch4 AND ipaddress.ip_end >= vSearch4;
    END IF;


  END
@@
DELIMITER ;