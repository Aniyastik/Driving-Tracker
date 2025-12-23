-- Drop tables if they exist
DROP TABLE IF EXISTS `driving_experience_manoeuvres`;
DROP TABLE IF EXISTS `driving_experience`;
DROP TABLE IF EXISTS `manoeuvres`;
DROP TABLE IF EXISTS `road`;
DROP TABLE IF EXISTS `traffic`;
DROP TABLE IF EXISTS `weather`;

-- Table: weather
CREATE TABLE `weather` (
  `Weather_ID` int NOT NULL AUTO_INCREMENT,
  `Weather_Condition` varchar(100),
  PRIMARY KEY (`Weather_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `weather` (`Weather_ID`, `Weather_Condition`) VALUES
(1, 'Sunny'),
(2, 'Rainy'),
(3, 'Cloudy'),
(4, 'Foggy'),
(5, 'Snowy');

-- Table: road
CREATE TABLE `road` (
  `Road_ID` int NOT NULL AUTO_INCREMENT,
  `Road_Type` varchar(50),
  PRIMARY KEY (`Road_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `road` (`Road_ID`, `Road_Type`) VALUES
(1, 'Highway'),
(2, 'Urban'),
(3, 'Rural'),
(4, 'Residential');

-- Table: traffic
CREATE TABLE `traffic` (
  `Traffic_ID` int NOT NULL AUTO_INCREMENT,
  `Traffic_Level` varchar(20),
  PRIMARY KEY (`Traffic_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `traffic` (`Traffic_ID`, `Traffic_Level`) VALUES
(1, 'Light'),
(2, 'Moderate'),
(3, 'Heavy');

-- Table: manoeuvres
CREATE TABLE `manoeuvres` (
  `Manoeuvre_ID` int NOT NULL AUTO_INCREMENT,
  `Manoeuvre_Type` varchar(50),
  `isSucceed` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`Manoeuvre_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `manoeuvres` (`Manoeuvre_ID`, `Manoeuvre_Type`, `isSucceed`) VALUES
(1, 'Left Turn', 1),
(2, 'Right Turn', 1),
(3, 'Overtaking', 1),
(4, 'Parking', 1),
(5, 'Reversing', 1),
(6, 'Three-Point Turn', 1),
(7, 'Emergency Stop', 1),
(8, 'Hill Start', 1);

-- Table: driving_experience (FIXED with AUTO_INCREMENT and foreign keys)
CREATE TABLE `driving_experience` (
  `Experience_ID` int NOT NULL AUTO_INCREMENT,
  `TimeDeparture` datetime NOT NULL,
  `TimeArrival` datetime NOT NULL,
  `Duration` time DEFAULT NULL,
  `Distance` float NOT NULL,
  `Road_ID` int DEFAULT NULL,
  `Weather_ID` int DEFAULT NULL,
  `Traffic_ID` int DEFAULT NULL,
  PRIMARY KEY (`Experience_ID`),
  KEY `fk_road` (`Road_ID`),
  KEY `fk_weather` (`Weather_ID`),
  KEY `fk_traffic` (`Traffic_ID`),
  CONSTRAINT `fk_road` FOREIGN KEY (`Road_ID`) REFERENCES `road` (`Road_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_weather` FOREIGN KEY (`Weather_ID`) REFERENCES `weather` (`Weather_ID`) ON DELETE SET NULL,
  CONSTRAINT `fk_traffic` FOREIGN KEY (`Traffic_ID`) REFERENCES `traffic` (`Traffic_ID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: driving_experience_manoeuvres (junction table with CASCADE delete)
CREATE TABLE `driving_experience_manoeuvres` (
  `Experience_ID` int NOT NULL,
  `Manoeuvre_ID` int NOT NULL,
  PRIMARY KEY (`Experience_ID`, `Manoeuvre_ID`),
  KEY `fk_manoeuvre` (`Manoeuvre_ID`),
  CONSTRAINT `fk_experience` FOREIGN KEY (`Experience_ID`) REFERENCES `driving_experience` (`Experience_ID`) ON DELETE CASCADE,
  CONSTRAINT `fk_manoeuvre` FOREIGN KEY (`Manoeuvre_ID`) REFERENCES `manoeuvres` (`Manoeuvre_ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;