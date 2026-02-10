-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2025 at 06:23 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `asimos`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(25) NOT NULL,
  `date` varchar(25) NOT NULL,
  `test` varchar(25) NOT NULL,
  `subjectName` varchar(25) NOT NULL,
  `marks` varchar(25) NOT NULL,
  `Result` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `date`, `test`, `subjectName`, `marks`, `Result`) VALUES
(2017115611, '29-06-2024', 'unit-1', 'English', '35/50', 'PASS'),
(2017115611, '29-07-2024', 'unit-1', 'English', '50/50', 'PASS'),
(2017115611, '01-01-2017', 'unit-2', 'Tamil', '0/50', 'FAIL'),
(2017115611, '29-06-2024', 'unit-1', 'English', '12/50', 'FAIL'),
(2017115611, '29-06-2024', 'unit-3', 'Science', '42/50', 'SUCCESS');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `primary_status` tinyint(1) DEFAULT NULL,
  `secondary_status` tinyint(1) DEFAULT NULL,
  `tertiary_status` tinyint(1) DEFAULT NULL,
  `morning` tinyint(1) DEFAULT NULL,
  `afternoon` tinyint(1) DEFAULT NULL,
  `evening` tinyint(1) DEFAULT NULL,
  `teacher_id` varchar(20) DEFAULT NULL,
  `subject_name` varchar(50) DEFAULT NULL,
  `markedBy` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homeworks`
--

CREATE TABLE `homeworks` (
  `id` int(11) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `teacher_id` varchar(10) NOT NULL,
  `standard` int(11) NOT NULL,
  `section` varchar(2) NOT NULL,
  `date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homeworks`
--

INSERT INTO `homeworks` (`id`, `subject_name`, `teacher_id`, `standard`, `section`, `date`, `title`, `description`, `created_at`, `updated_at`) VALUES
(1, 'English', '2017115610', 1, 'A', '2025-08-25', 'Grammar Worksheet', 'Complete exercises on tenses from page 23 to 26.', '2025-08-27 18:51:45', '2025-08-27 18:51:45'),
(2, 'Mathematics', '2017115610', 1, 'A', '2025-08-25', 'Addition Practice', 'Solve problems 1–20 from the class workbook.', '2025-08-27 18:51:45', '2025-08-27 18:51:45'),
(3, 'Science', '2017115610', 1, 'A', '2025-08-26', 'Plants and Animals', 'Draw and label parts of a plant in your notebook.', '2025-08-27 18:51:45', '2025-08-27 18:51:45'),
(4, 'Social', '2017115610', 1, 'A', '2025-08-27', 'Our Neighborhood', 'Write 5 sentences about your neighborhood.', '2025-08-27 18:51:45', '2025-08-27 18:51:45'),
(5, 'Tamil', '2017115610', 1, 'A', '2025-08-27', 'Poem Recitation', 'Memorize the given 4-line Tamil poem and prepare to recite.', '2025-08-27 18:51:45', '2025-08-27 18:51:45');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` varchar(20) NOT NULL,
  `subjectName` varchar(50) NOT NULL,
  `testName` varchar(50) NOT NULL,
  `date` varchar(25) NOT NULL,
  `marksObtained` varchar(10) DEFAULT NULL,
  `totalMarks` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks`
--

INSERT INTO `marks` (`id`, `subjectName`, `testName`, `date`, `marksObtained`, `totalMarks`) VALUES
('2017115611', 'English', 'Term 1', '', '100', '100'),
('2017115611', 'Mathematics', 'Term 1', '', '90', '100'),
('2017115611', 'Science', 'Term 1', '', '50', '100'),
('2017115611', 'Social', 'Term 1', '', '20', '100'),
('2017115611', 'Tamil', 'Term 1', '', '35', '100');

-- --------------------------------------------------------

--
-- Table structure for table `marks_new`
--

CREATE TABLE `marks_new` (
  `id` varchar(20) NOT NULL,
  `subjectName` varchar(50) NOT NULL,
  `testName` varchar(50) NOT NULL,
  `date` varchar(25) NOT NULL,
  `grandTotal` varchar(100) NOT NULL,
  `totalMarks` varchar(10) NOT NULL,
  `english` int(10) NOT NULL,
  `tamil` int(10) NOT NULL,
  `maths` int(10) NOT NULL,
  `science` int(10) NOT NULL,
  `social` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marks_new`
--

INSERT INTO `marks_new` (`id`, `subjectName`, `testName`, `date`, `grandTotal`, `totalMarks`, `english`, `tamil`, `maths`, `science`, `social`) VALUES
('2017115611', '0', 'Term 1', '2023-01-01', '', '100', 50, 60, 40, 80, 50),
('2017115611', '0', 'Term 2', '2023-01-01', '', '100', 42, 92, 54, 20, 74),
('2017115611', '0', 'Term 3', '2023-01-01', '', '100', 12, 42, 44, 85, 48);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` varchar(20) NOT NULL,
  `notification` varchar(200) NOT NULL,
  `sentBy` varchar(200) NOT NULL,
  `date` varchar(12) NOT NULL,
  `time` varchar(10) NOT NULL,
  `status` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `notification`, `sentBy`, `date`, `time`, `status`) VALUES
('2017115611', 'Please pay the second term fees.', 'Principal', '2023-01-01', '20:30', 0),
('2017115611', 'Complete the homework assignments today', 'Teacher', '2023-01-01', '02:30', 0),
('2017115611', 'Parent-Teachers meeting on saturday afternoon 12:30 PM', 'A.O', '02-03-2024', '05:10 AM', 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_info`
--

CREATE TABLE `student_info` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `standard` int(11) DEFAULT NULL,
  `section` varchar(2) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dateOfBirth` varchar(20) DEFAULT NULL,
  `address` varchar(150) DEFAULT NULL,
  `bloodGroup` varchar(5) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `emailID` varchar(50) DEFAULT NULL,
  `fatherName` varchar(50) DEFAULT NULL,
  `fatherOccupation` varchar(50) DEFAULT NULL,
  `fatherPhone` varchar(15) DEFAULT NULL,
  `fatherAnnualIncome` varchar(15) DEFAULT NULL,
  `motherName` varchar(50) DEFAULT NULL,
  `motherOccupation` varchar(50) DEFAULT NULL,
  `motherPhone` varchar(15) DEFAULT NULL,
  `motherAnnualIncome` varchar(15) DEFAULT NULL,
  `community` varchar(20) DEFAULT NULL,
  `identificationMark` varchar(50) DEFAULT NULL,
  `comments` varchar(50) DEFAULT NULL,
  `previousEducation` varchar(100) DEFAULT NULL,
  `coCurricularActivity` varchar(50) DEFAULT NULL,
  `locOfProfilePic` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_info`
--

INSERT INTO `student_info` (`id`, `name`, `age`, `standard`, `section`, `gender`, `dateOfBirth`, `address`, `bloodGroup`, `phone`, `emailID`, `fatherName`, `fatherOccupation`, `fatherPhone`, `fatherAnnualIncome`, `motherName`, `motherOccupation`, `motherPhone`, `motherAnnualIncome`, `community`, `identificationMark`, `comments`, `previousEducation`, `coCurricularActivity`, `locOfProfilePic`) VALUES
('2017115611', 'Viswaprasad', 5, 1, 'A', 'Male', '2000-06-29', 'Pondicherry', 'B+', '9489324639', 'meetprasadviswa@gmail.com', 'Elumalai', 'Advocate', '9489324639', '100000', 'Bhuvaneswari', 'Assistant Agricultur', '9500215554', '600000', 'General', 'Mole on chin', '-', 'St. Antony\'s School', 'None', './assets/img/stdImg/default.png');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_info`
--

CREATE TABLE `teacher_info` (
  `teacher_id` varchar(20) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `marital_status` varchar(10) DEFAULT NULL,
  `nationality` varchar(30) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alt_phone` varchar(20) DEFAULT NULL,
  `email` varchar(70) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `date_of_leaving` date DEFAULT NULL,
  `employment_status` varchar(20) DEFAULT 'Active',
  `job_title` varchar(50) DEFAULT NULL,
  `employee_type` varchar(20) DEFAULT NULL,
  `workload_hours` int(11) DEFAULT NULL,
  `reporting_to` varchar(20) DEFAULT NULL,
  `highest_qualification` varchar(100) DEFAULT NULL,
  `graduation_year` year(4) DEFAULT NULL,
  `university_name` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `certification` varchar(200) DEFAULT NULL,
  `photo_path` varchar(200) DEFAULT NULL,
  `resume_path` varchar(200) DEFAULT NULL,
  `id_proof_path` varchar(200) DEFAULT NULL,
  `basic_salary` decimal(10,2) DEFAULT NULL,
  `hra` decimal(10,2) DEFAULT NULL,
  `allowance_misc` decimal(10,2) DEFAULT NULL,
  `bank_account_number` varchar(30) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `esi_number` varchar(20) DEFAULT NULL,
  `pf_number` varchar(20) DEFAULT NULL,
  `login_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_info`
--

INSERT INTO `teacher_info` (`teacher_id`, `first_name`, `last_name`, `gender`, `date_of_birth`, `blood_group`, `marital_status`, `nationality`, `phone`, `alt_phone`, `email`, `address`, `city`, `state`, `country`, `postal_code`, `date_of_joining`, `date_of_leaving`, `employment_status`, `job_title`, `employee_type`, `workload_hours`, `reporting_to`, `highest_qualification`, `graduation_year`, `university_name`, `specialization`, `certification`, `photo_path`, `resume_path`, `id_proof_path`, `basic_salary`, `hra`, `allowance_misc`, `bank_account_number`, `ifsc_code`, `pan_number`, `esi_number`, `pf_number`, `login_id`, `created_at`, `updated_at`) VALUES
('2017115610', 'Zamp', 'Johnson', 'Male', '1985-06-20', NULL, NULL, NULL, '9876500010', NULL, 'zamp@school.com', 'Pondicherry', NULL, NULL, NULL, NULL, '2015-06-10', NULL, 'Active', 'Senior Teacher', 'Full-Time', NULL, NULL, 'M.Sc, B.Ed', NULL, NULL, 'English', NULL, NULL, NULL, NULL, 52000.00, 12000.00, 5000.00, NULL, NULL, NULL, NULL, NULL, '2017115610', '2025-12-27 17:15:16', '2025-12-27 17:15:16'),
('T001', 'Teacher', 'One', 'Female', '1990-03-11', NULL, NULL, NULL, '9876500001', NULL, 'teacher1@school.com', 'Pondicherry', NULL, NULL, NULL, NULL, '2019-06-10', NULL, 'Active', 'Math Teacher', 'Full-Time', NULL, NULL, 'B.Sc, B.Ed', NULL, NULL, 'Maths', NULL, NULL, NULL, NULL, 38000.00, 8000.00, 3000.00, NULL, NULL, NULL, NULL, NULL, 'T001', '2025-12-27 17:15:16', '2025-12-27 17:15:16');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_subject_allocation`
--

CREATE TABLE `teacher_subject_allocation` (
  `id` int(11) NOT NULL,
  `teacher_id` varchar(20) DEFAULT NULL,
  `subject_name` varchar(50) DEFAULT NULL,
  `standard` int(11) DEFAULT NULL,
  `section` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_subject_allocation`
--

INSERT INTO `teacher_subject_allocation` (`id`, `teacher_id`, `subject_name`, `standard`, `section`) VALUES
(1, 'T001', 'English', 1, 'A'),
(2, 'T002', 'Maths', 1, 'A'),
(3, 'T003', 'Science', 1, 'A'),
(4, 'T004', 'Social', 1, 'A'),
(5, 'T005', 'Tamil', 1, 'A');

-- --------------------------------------------------------

--
-- Table structure for table `user_login`
--

CREATE TABLE `user_login` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `access` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`id`, `name`, `password`, `access`) VALUES
('2017115610', 'Zamp', 'Zamp', 1),
('2017115611', 'Viswaprasad', 'Viswa#2000', 0),
('T001', 'Teacher 1', 'pass', 1),
('T002', 'Teacher 2', 'pass', 1),
('T003', 'Teacher 3', 'pass', 1),
('T004', 'Teacher 4', 'pass', 1),
('T005', 'Teacher 5', 'pass', 1),
('T006', 'Teacher 6', 'pass', 1),
('T007', 'Teacher 7', 'pass', 1),
('T008', 'Teacher 8', 'pass', 1),
('T009', 'Teacher 9', 'pass', 1),
('T010', 'Teacher 10', 'pass', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`,`date`),
  ADD KEY `idx_attendance_student_date` (`id`,`date`);

--
-- Indexes for table `homeworks`
--
ALTER TABLE `homeworks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_homework_teacher` (`teacher_id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`,`subjectName`,`testName`);

--
-- Indexes for table `student_info`
--
ALTER TABLE `student_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_info`
--
ALTER TABLE `teacher_info`
  ADD PRIMARY KEY (`teacher_id`);

--
-- Indexes for table `teacher_subject_allocation`
--
ALTER TABLE `teacher_subject_allocation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_teacher_subject` (`teacher_id`,`subject_name`);

--
-- Indexes for table `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `homeworks`
--
ALTER TABLE `homeworks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teacher_subject_allocation`
--
ALTER TABLE `teacher_subject_allocation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `homeworks`
--
ALTER TABLE `homeworks`
  ADD CONSTRAINT `fk_homework_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `user_login` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
