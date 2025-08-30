EduAxis – Study Center Management System
📌 Overview

The EduAxis Study Center Management System is a PHP-based web application designed to digitalize and simplify the operations of the EduAxis Study Center.
It manages student registration, slot booking, payments, and profile management, making day-to-day operations efficient and transparent.

🚀 Features

1. User Authentication – Secure student registration & login.
2. Profile Management – Update or delete student accounts anytime.
3. Slot Booking System – Reserve available study slots online.
4. Waitlist Management – Automatic waitlist confirmation when slots open.
5. Payment Portal – Secure portal for fee payments.
6. Student Dashboard – Central hub for all student activities.
7. Email Notifications – Sends confirmations via PHPMailer.

🛠️ Technologies Used
- Frontend: HTML, CSS, Bootstrap (within PHP views).
- Backend: PHP (Core PHP).
- Database: MySQL (EduAxis.sql).
- Libraries: PHPMailer
 via Composer.

📂 Project Structure
EduAxis_Study_Center/
│── HOME PAGE.php            # Landing page
│── REGISTER_FORM.php        # Student registration
│── LOGIN_FORM.php           # Student login
│── STUDENT_DASHBOARD.php    # Student portal
│── SLOTS.php                # Slot booking
│── SLOT_BOOKING.php         # Booking logic
│── PAYMENT_PORTAL.php       # Payment gateway UI
│── PAYMENT.php              # Payment processing
│── BOOKING_CONFIRM.php      # Booking confirmation
│── WAITLIST_CONFIRM.php     # Waitlist confirmation
│── UPDATE_PROFILE.php       # Profile updates
│── DELETE_ACCOUNT.php       # Account removal
│── LOGOUT.php               # Session logout
│── EduAxis.sql              # Database schema & sample data
│── vendor/                  # Composer dependencies (PHPMailer, etc.)
│── composer.json            # Dependency manager

Installation & Setup

1. Download or clone the project from your repository.
git clone https://github.com/your-repo/eduaxis-management.git
2. Move the project folder to C:\xampp\htdocs.
3. Install the required dependencies by running the command: composer install
4. Import the EduAxis.sql file into your MySQL workbench.
5. Update the database connection details inside the PHP files with your personal mysql password and make sure the schema name is same as the database name 'eduaxis' .
6. Start your web server Apache.
7. Open your browser and go to: http://localhost/STUDY%20CENTER%20MANAGEMENT%20SYSTEM

📧 Email Configuration
- Update PHPMailer SMTP settings with your mail server credentials.
- This enables sending of booking confirmations and payment receipts.

