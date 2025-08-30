-- USERS TABLE
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `img` TEXT,
  `name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(200) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `contact` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- CONTACT FORM TABLE
CREATE TABLE `contact_form` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(200) NOT NULL,
  `contact_number` VARCHAR(50) NOT NULL,
  `email_id` VARCHAR(200) NOT NULL,
  `subject` VARCHAR(220),
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- SLOTS TABLE
CREATE TABLE `slots` (
  Slot VARCHAR(100) PRIMARY KEY,
  Timings VARCHAR(100) NOT NULL,
  capacity INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `slots` (`Slot`, `Timings`, `capacity`) VALUES
('SLOT A', '08:00 AM - 12:00 PM', 10),
('SLOT B', '01:00 PM - 05:00 PM', 10),
('SLOT C', '06:00 PM - 10:00 PM', 10);

-- SLOT BOOKINGS TABLE
CREATE TABLE `slotbookings` (
  BookingID INT AUTO_INCREMENT PRIMARY KEY,
  UserID INT NOT NULL,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  Slot VARCHAR(100) NOT NULL,
  BookingDateChosen DATE NOT NULL,
  Status VARCHAR(50) DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (UserID) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (Slot) REFERENCES slots(Slot) ON DELETE CASCADE,
  INDEX idx_slot_date (Slot, BookingDateChosen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- PAYMENTS TABLE
CREATE TABLE `payments` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  UserID INT NOT NULL,
  BookingID INT NOT NULL,
  Slot VARCHAR(100) NOT NULL,
  BookingDateChosen DATE NOT NULL,
  name VARCHAR(200) NOT NULL,
  email VARCHAR(200) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_status VARCHAR(50) DEFAULT 'Not Paid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (UserID) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (BookingID) REFERENCES slotbookings(BookingID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- WAITLIST TABLE
CREATE TABLE `waitlist` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  UserID INT NOT NULL,
  BookingID INT NULL,
  Slot VARCHAR(100) NOT NULL,
  BookingDateChosen DATE NOT NULL,
  request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  Status VARCHAR(50) DEFAULT 'Waiting',
  FOREIGN KEY (UserID) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SELECT * FROM `users`;
SELECT * FROM `slots`;
SELECT * FROM `slotbookings`;
SELECT * FROM `payments`;
SELECT * FROM `waitlist`;
SELECT * FROM `contact_form`;

--------------------------------------------------------------------------
/*EDUAXIS ADMIN*/
-------------------------------------------------------------------------
CREATE TABLE `admin_user` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `img` TEXT,
  `name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(200) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `contact` VARCHAR(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

select *from admin_user;


