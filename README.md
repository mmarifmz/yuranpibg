# Yuran & Sumbangan PIBG App

A modern Laravel-based web application for collecting **Yuran PIBG (Parent-Teacher Association Fees)** and **Sumbangan Ikhlas** online.

Designed to streamline the fee collection process for Malaysian schools, this app can be used by **any school PTA**. If you're interested in deploying this app for your school, just reach out — I'm happy to help set it up.

> 📧 Contact: **[mm.arif.mz@gmail.com](mailto:mm.arif.mz@gmail.com)**

---

## 🎯 Key Features

* 🔍 Student search by name
* 👨‍👩‍👧‍👦 Family grouping with a single payment per household
* 💳 Fixed PTA fee for parents + optional donation with flexible amounts
* ✅ Integrated with ToyyibPay (current and only supported payment gateway)
* 📜 Auto-generated receipts (online and downloadable PDF)
* 📊 Real-time payment status and logs
* 📱 Mobile-friendly UI
* 🔒 Webhook-secured transaction validation

---

## 🏫 Who Is This For?

* PTA (PIBG) committees
* School administrators
* Class teachers handling fee collection

If you’re a school interested in using this app, drop me a line.

---

## ⚙️ Tech Stack

* Laravel 10 (PHP 8.1+)
* MySQL / MariaDB
* ToyyibPay API
* Bootstrap 5
* DigitalOcean App Platform-ready

---

## 🚀 Getting Started

### Requirements

* PHP >= 8.1
* Composer
* MySQL / MariaDB

### Installation

```bash
git clone https://github.com/mmarifmz/yuranpibg.git
cd yuranpibg
composer install
cp .env.example .env
php artisan key:generate
```

### Configuration

Edit your `.env` file:

```env
APP_NAME="Yuran PIBG"
APP_ENV=production
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

TOYYIBPAY_SECRET_KEY=your_secret_key
TOYYIBPAY_CATEGORY_CODE=your_category_code
```

### Database Setup

```bash
php artisan migrate
php artisan storage:link
```

---

## 🌐 Deployment

Recommended deployment on **DigitalOcean App Platform**:

* GitHub integration with auto-deploy
* PHP buildpack with `public/` as root
* HTTPS by default
* Custom domain support

---

## 📬 Need Help?

This project is built to be reused by any school. If you're a teacher or PTA member and want help deploying this for your school:

📧 **Email**: [mm.arif.mz@gmail.com](mailto:mm.arif.mz@gmail.com)
🔗 **LinkedIn**: [Arif Zamri](https://www.linkedin.com/in/mmarifmz)

---

> Let's digitize PTA payments together, one school at a time. 🇲🇾
