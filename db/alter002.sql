
CREATE TABLE IF NOT EXISTS `banLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipAddress` varchar(16) NOT NULL,
  `banDate` datetime NOT NULL,
  `isPermanent` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
