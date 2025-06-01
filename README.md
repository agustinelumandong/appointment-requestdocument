# Appointment Booking System

A versatile appointment booking system built with Laravel for professionals including doctors, consultants, lawyers, tutors, trainers, and service providers. Features automated email notifications, multi-user roles, calendar-based scheduling, and availability management.

## Features

✅ Multi-role support (Admin, Employee/Professional, Moderator, Subscriber)  
✅ Automated Email Notifications for bookings & reminders  
✅ Interactive Calendar View for easy scheduling  
✅ Multi-Slot Availability (Multiple time slots per day)  
✅ Mark Holidays & Unavailable Dates  
✅ Easy Rescheduling & Cancellation  
✅ Responsive Design (Desktop & Mobile)  

## Installation

1. **Clone and setup:**

   ```bash
   git clone https://github.com/vfixtechnology/appointment-booking-system.git
   cd appointment-booking-system
   composer install
   ```

2. **Configure environment:**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database setup:**

   Create a MySQL database and update your `.env` file:

   ```env
   DB_DATABASE=your_database_name
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

4. **Email configuration:**

   Add your SMTP details to `.env`:

   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=your_smtp_host
   MAIL_PORT=your_smtp_port
   MAIL_USERNAME=your_email_username
   MAIL_PASSWORD=your_email_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your@email.com
   ```

5. **Run migrations and start services:**

   ```bash
   php artisan migrate
   php artisan db:seed
   php artisan queue:listen
   php artisan serve
   ```

   Access the application at `http://localhost:8000`

## Default Admin Credentials

- **URL:** <http://localhost:8000/login>
- **Email:** <admin@example.com>
- **Password:** admin123

## User Roles

### Admin

- Full system control (users, appointments, settings)

### Moderator

- Manage all appointments + employee-level access

### Employee/Professional

- Set availability (multiple slots per day)
- Mark holidays/unavailable dates
- View/manage their own appointments

### Subscriber (Client)

- Book appointments (guest checkout available)
- View bookings after account creation

## Usage

1. Create professional accounts
2. Set availability and working hours
3. Mark holidays/unavailable dates
4. Manage appointments (approve, confirm, cancel)

## Requirements

- PHP 8.1+
- Laravel 10.x
- MySQL 5.7+
- Composer

## License

This project is open-sourced software licensed under the MIT license.
