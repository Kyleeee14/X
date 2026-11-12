# Vibe - Twitter-like Social Media Platform

## 📋 Project Overview
Vibe is a Twitter-like social media platform built with Laravel and Tailwind CSS. It features real-time interactions, image uploads, and a modern UI design.

## 🚀 Core Features
- **Tweet System**: Post, edit, and delete tweets with image support
- **Real-time Interactions**: Like, comment, and reply without page refresh
- **Nested Comments**: Instagram/TikTok-style comment threading
- **Image Uploads**: Support for tweet and comment images with preview
- **Search Functionality**: Search tweets and tags with autocomplete
- **User Profiles**: Customizable profiles with avatars and headers
- **Tag System**: Hashtag support with tag pages
- **Edit Functionality**: Edit tweets, comments, and replies
- **Responsive Design**: Mobile-friendly with Tailwind CSS

## 📁 Important Files & Their Purpose

### 🎯 Application Core
- **`app/Http/Controllers/TweetController.php`** - Handles all tweet operations (create, edit, delete, reply)
- **`app/Models/Tweet.php`** - Tweet model with relationships and tag parsing
- **`app/Models/User.php`** - User model with profile functionality
- **`app/Models/Reply.php`** - Reply/comment model for nested comments
- **`app/Models/Tag.php`** - Tag model for hashtag functionality

### 🎨 Frontend & Views
- **`resources/views/layouts/app.blade.php`** - Main layout template
- **`resources/views/tweets/tweet.blade.php`** - Individual tweet display with interactions
- **`resources/views/home.blade.php`** - Homepage with tweet feed
- **`resources/views/profile/show.blade.php`** - User profile page
- **`resources/views/search.blade.php`** - Search results page
- **`resources/views/auth/`** - Authentication pages (login, register)

### 🛤️ Routes & Configuration
- **`routes/web.php`** - All web routes and API endpoints
- **`config/app.php`** - Application configuration
- **`.env`** - Environment variables (database, app settings)

### 🗄️ Database
- **`database/migrations/`** - Database schema migrations
- **`database/seeders/`** - Database seeders for initial data

### 📦 Assets & Dependencies
- **`public/storage/`** - User uploaded images (avatars, tweet images)
- **`storage/app/public/`** - Internal storage for uploads
- **`tailwind.config.js`** - Tailwind CSS configuration
- **`package.json`** - Node.js dependencies

### ⚙️ Laravel Core Files
- **`artisan`** - Laravel command line interface
- **`composer.json`** - PHP dependencies and packages
- **`bootstrap/app.php`** - Laravel application bootstrap
- **`config/`** - Various configuration files

## 🔧 Technical Stack
- **Backend**: Laravel 10.x with PHP 8.x
- **Frontend**: Blade templates with Tailwind CSS
- **Database**: MySQL (for Laragon deployment)
- **File Storage**: Local storage with symbolic links
- **JavaScript**: Vanilla JS for real-time interactions

## 🚀 Getting Started
1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Run `php artisan storage:link`
7. Start development server

## 📝 Development Notes
- Uses AJAX for real-time interactions
- Implements proper authentication and authorization
- Features comprehensive error handling and validation
- Optimized for mobile and desktop viewing
- Includes debug logging for troubleshooting
