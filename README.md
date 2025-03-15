# Projet Symfony Gestion

A comprehensive management system built with Symfony 5.4, designed to handle orders, products, users, and various business operations.

## 🚀 Features

- User Management (Admin, Reseller, Regular Users)
- Product Management with Variants and Categories
- Order Processing and History
- Stock Management
- Task Management
- Price List Management
- File Upload System
- Notification System
- Transaction Tracking

## 🛠 Technical Stack

- **PHP**: ^8.1
- **Framework**: Symfony 5.4
- **Database**: Doctrine ORM
- **Frontend**: Symfony Webpack Encore
- **Template Engine**: Twig
- **Testing**: PHPUnit
- **API Support**: REST with CORS enabled

## 📋 Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js and npm (for frontend assets)
- MySQL/MariaDB

## 🔧 Installation

1. Clone the repository:
```bash
git clone [repository-url]
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install frontend dependencies:
```bash
npm install
```

4. Configure your environment:
- Copy `.env` to `.env.local`
- Update database and other configuration settings

5. Create database and run migrations:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

6. Build frontend assets:
```bash
npm run build
```

## 🏃‍♂️ Running the Application

1. Start the Symfony development server:
```bash
symfony server:start
```

2. Access the application at `http://localhost:8000`

## 🧪 Testing

Run the test suite:
```bash
php bin/phpunit
```

## 📦 Project Structure

- `src/Controller/` - Application controllers
- `src/Entity/` - Doctrine entities (User, Product, Order, etc.)
- `src/Form/` - Form types
- `src/Repository/` - Database repositories
- `templates/` - Twig templates
- `public/` - Public assets
- `assets/` - Frontend assets (JS, CSS)
- `config/` - Application configuration
- `translations/` - Translation files

## 🔐 Security

The application implements a robust security system with:
- User authentication
- Role-based access control
- Secure password management
- CSRF protection

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## 📄 License

This project is proprietary software. All rights reserved.

## 🛟 Support

For support and questions, please contact the development team.
