CREATE TABLE IF NOT EXISTS `downloadLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipAddress` varchar(16) NOT NULL,
  `downloadDate` datetime NOT NULL,
  `fileCode` enum('browscapini','full_browscapini','lite_browscapini','php_browscapini','full_php_browscapini','lite_php_browscapini') NOT NULL,
  `userAgent` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `banLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipAddress` varchar(16) NOT NULL,
  `banDate` datetime NOT NULL,
  `isPermanent` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `downloadLog`
  ADD INDEX `idx_ip_date` (`ipAddress`, `downloadDate`) USING BTREE;

CREATE TABLE `downloadsPerMonth` (
  `monthPeriod` DATE,
  `downloadCount` INT(11) NOT NULL,
  PRIMARY KEY (`monthPeriod`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `downloadsLastMonth` (
  `dayPeriod` DATE,
  `downloadCount` INT(11) NOT NULL,
  PRIMARY KEY (`dayPeriod`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
