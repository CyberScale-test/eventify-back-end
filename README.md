# Eventify Backend (Laravel) 🚀

This is the backend repository for **Eventify**, an event management platform. Built with Laravel, it provides APIs for user authentication, event management, and notifications.

---

## ✨ Features

- **User Authentication**  
  Secure registration and login using Laravel Sanctum

- **Event Management**

  - Create, update, and delete events.

- **Notifications**

  - Email notifications to creators when users join their events.
  - Real-time updates for participants when event details change.

- **API Endpoints**  
  RESTful APIs for seamless integration with the frontend.

---

## 🛠 Tech Stack

- **Framework**: Laravel (PHP)
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Email Notifications**: Laravel Mail (SMTP)
- **API Testing**: Postman

---

## 🚀 Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/CyberScale-test/eventify-back-end.git
   cd eventify-back-end
   ```
2. **Install the composer**

```bash
composer install
```

3. **Install the composer**

```bash
cp .env.example .env
```

4. **Generate the key**

```bash
php artisan key:generate
```

5. **migrate and seed**

```bash
php artisan migrate --seed
```

6. **Run the server**

```bash
php artisan serve
```

7. **Install Pusher**

```bash
composer require pusher/pusher-php-server
```
