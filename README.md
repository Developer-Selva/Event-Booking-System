# Event Booking System API

## 📋 Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [Performing Test case](#test-case)


## 🔧 Requirements

- PHP >= 8.1
- Composer
- MySQL >= 5.7
- Laravel >= 11.0

## 📦 Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd event-booking-system
```
2. **Install PHP dependencies**
```bash
composer install
```
3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

## ⚙️ Configuration
1. **Confire .env file**
```bash
APP_NAME="Event Booking System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=event_booking_system
DB_USERNAME=root
DB_PASSWORD=

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=database
SESSION_DRIVER=array

# Mail Configuration (for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@eventbooking.com
MAIL_FROM_NAME="${APP_NAME}"
```
## 🗄️ Database Setup
1. **Create database**
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS event_booking_system"
```
2. **Run migrations**
```bash
php artisan migrate
```
3. **Run Seeders**
```bash
php artisan db:seed
```
4. **Start queue worker (for notifications)**
```bash
php artisan queue:work
```
## 🚀 Running the Application

# Start development server
```bash
php artisan serve
```
# Start queue worker (in separate terminal)
```bash
php artisan queue:work
```
## 📊 Seeded Data

The system comes pre-seeded with the following test data:

Users (15 total)

Role	Count	Email	Password

Admin	2	admin1@example.com, admin2@example.com	password

Organizer	3	organizer1@example.com, organizer2@example.com, organizer3@example.com	password

Customer	10	customer1@example.com through customer10@example.com	password

## Performing Test case
**Run this artisan commad to perform the tests**
```bash
php artisan test
```