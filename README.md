# Meeting Room Booking System - Backend

## 🛠️ Installation & Setup

Follow these steps to get the development environment running:

### 1. Clone the Repository

```bash
git clone <repository-url>
cd mrbs-backend
```

### 2. Environment Configuration

Create your environment file by copying the sample `.env` file:

```bash
cp .env.example .env
```

Open the `.env` file and verify or update the port configurations. The essential configurations for Docker.

### 3. Build & Run Docker Containers

Start all required services (Nginx, PHP-FPM, PostgreSQL, Redis, and Queue Worker) in the background:

```bash
docker-compose up -d --build
```

### 4. Install PHP Dependencies

Since the project files are mounted via volumes, you may need to install the Composer dependencies inside the app container:

```bash
docker-compose exec app composer install
```

### 5. Generate Application Key

Generate the Laravel application key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Run Database Migrations

Run the database migrations to create the required tables:

```bash
docker-compose exec app php artisan migrate
```

---
