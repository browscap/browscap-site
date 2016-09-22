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
