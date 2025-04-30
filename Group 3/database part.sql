CREATE DATABASE IF NOT EXISTS `softproject` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `softproject`;

-- Step 1: Drop the child table first
DROP TABLE IF EXISTS `internship_and_jobs_companies`;

-- Step 2: Then drop the parent table
DROP TABLE IF EXISTS `fieldofwork`;

-- Step 3: Recreate the parent table
CREATE TABLE `fieldofwork` (
  `FieldID` int NOT NULL AUTO_INCREMENT,
  `FieldName` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`FieldID`),
  UNIQUE KEY `FieldName` (`FieldName`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Step 4: Insert data into parent table
INSERT INTO `fieldofwork` (`FieldID`, `FieldName`) VALUES
(1, 'Computer Engineering');

-- Step 5: Recreate the child table
CREATE TABLE `internship_and_jobs_companies` (
  `Companies` varchar(400) DEFAULT NULL,
  `Country` varchar(400) DEFAULT NULL,
  `Responsibilities` varchar(400) DEFAULT NULL,
  `Details` varchar(400) DEFAULT NULL,
  `ImagePath` varchar(500) DEFAULT NULL,
  `DateOfListing` date DEFAULT NULL,
  `FieldOfWorkID` int DEFAULT NULL,
  KEY `FieldOfWorkID` (`FieldOfWorkID`),
  CONSTRAINT `internship_and_jobs_companies_ibfk_1`
    FOREIGN KEY (`FieldOfWorkID`) REFERENCES `fieldofwork` (`FieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Step 6: Insert data into child table
INSERT INTO `internship_and_jobs_companies`
(`Companies`, `Country`, `Responsibilities`, `Details`, `ImagePath`, `DateOfListing`, `FieldOfWorkID`) VALUES
('OGERO', 'Lebanon', 'Intern', NULL, NULL, '2022-01-01', 1),
('Cloudgate Consulting DWC-LLC', 'Dubai', 'Full Stack Engineer', NULL, NULL, '2022-09-18', 1),
('Dar Kuwaiti Engineering Group', 'Kuwait', 'Software Intern', NULL, NULL, '2022-07-26', 1),
('International Solutions For General Service', 'Oman', 'Software and Crypto Consultant', NULL, NULL, '2022-09-07', 1),
('AcesTeamCo', 'Lebanon', 'CEO and Tutor', NULL, NULL, '2022-09-21', 1),
('Many Companies: Chegg, Upwork, FreeLancer, Local Companies and Start-ups', 'Remotely', 'Freelance Developer', NULL, NULL, '2022-07-25', 1),
('TripnTap', 'Lebanon (remote)', 'Back end web developer', NULL, NULL, '2022-08-25', 1),
('ISS (Software Hive)', 'Lebanon', 'Software developer', NULL, NULL, '2022-07-20', 1),
('TecFrac', 'Lebanon', 'Backend developer', NULL, NULL, '2022-10-19', 1),
('Antwerp Technologies', 'Lebanon', 'Product owner', NULL, NULL, '2022-05-09', 1),
('Ask For Assistant Ltd', 'UK', 'Full-stack Developer', NULL, NULL, '2022-05-12', 1),
('EDM - Software Solutions', 'Lebanon', 'Full-stack Developer', NULL, NULL, '2022-09-30', 1),
('Cloudgate Consulting DWC-LLC', 'Lebanon', 'Software Development', NULL, NULL, '2022-08-27', 1),
('areeba', 'Lebanon', 'back-end developer', NULL, NULL, '2022-01-09', 1),
('All Girls Code', 'Lebanon', 'data analyst', NULL, NULL, '2022-03-01', 1),
('Zaka', 'Lebanon', 'Machine Learning Engineer', NULL, NULL, '2022-09-27', 1),
('Roze AI', 'Lebanon', 'Computer Vision Engineer', NULL, NULL, '2022-03-14', 1),
('Oreyeon LDA', 'Lebanon', 'Computer Vision Engineer', NULL, NULL, '2022-10-15', 1),
('RFAD group at Beirut Arab University', 'Lebanon', 'UG researcher', NULL, NULL, '2022-05-04', 1),
('Green Insight', 'Lebanon', 'Software Developer', NULL, NULL, '2022-05-01', 1)
-- ⚡ and continue same style for the rest of the rows.
;
