Translation API - Laravel 10
This project is an API-driven translation service built with Laravel 10, supporting multiple locales, tag-based filtering, and JSON exports for frontend integration.

Features
Manage translations with locales (e.g., en, fr, es)

Tag support (mobile, web, desktop)

CRUD operations

Search by tag, key, content, or locale

JSON export for frontend use (e.g., Vue.js)

Token-based authentication (Laravel Sanctum)

OpenAPI/Swagger documentation

Seed 100k+ records for scalability testing

Fast response: < 200ms (standard), < 500ms (export)

Installation
Clone this repo

Run:

composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
Install Sanctum:

php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
Start dev server:

php artisan serve
API Docs (Swagger)
Visit: http://localhost:8000/docs

Authentication
Use Laravel Sanctum for login and access tokens.

Testing
Run feature tests:
php artisan test

Seeder for 100k Translations
To populate:
php artisan migrate:fresh --seed
This creates 100,000+ translations for benchmarking.

Create Test User via Tinker

php artisan tinker

Then run:

use App\Models\User;
$user = User::create([
'name' => 'Test Admin',
'email' => 'admin@example.com',
'password' => bcrypt('password'),
]);
$token = $user->createToken('api-token')->plainTextToken;
echo "Token: $token";

Use this token in headers:
Authorization: Bearer your_token_here
