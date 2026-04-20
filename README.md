# Cloudy Learning

An e-learning platform built with **Laravel 12** and **Vue 3**, featuring a public-facing SPA and a separate admin panel. Supports course management, video lessons, quizzes, student enrollment workflows, and S3-compatible media storage via MinIO — all running in Docker.

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 12, Repository pattern (`prettus/l5-repository`) |
| Frontend | Vue 3, Vue Router 5, TypeScript, Tailwind CSS 4, Vite 7 |
| Database | PostgreSQL 16 |
| Storage | MinIO (S3-compatible, for videos & thumbnails) |
| Web server | Nginx |
| i18n | `vue-i18n` |

## Features

- **Course catalog** — browse by category, filter, popular/newest listings
- **Enrollment workflow** — students request enrollment; instructors approve or reject
- **Video lessons** — presigned S3 uploads via MinIO; first lesson free before approval
- **Quizzes** — per-lesson quizzes with multiple-choice questions
- **Roles** — Student, Instructor, Admin with separate access controls
- **Admin panel** — full CRUD for courses, lessons, categories, users, quizzes, and reviews
- **Notifications** — in-app notification system for users and admins

## Prerequisites

- [Docker](https://www.docker.com/) & [Docker Compose](https://docs.docker.com/compose/)
- (Optional) [Colima](https://github.com/abiosoft/colima) for improved Docker performance on macOS

## Server Setup (Ubuntu 24.04 — e.g. AWS EC2 t3.small)

Only three things are required on the host:

| Dependency | Purpose |
|---|---|
| **Git** | `git pull` to fetch updates |
| **Docker Engine** | runs containers |
| **Docker Compose Plugin** | `docker compose` (v2) |

Run the bootstrap script once after provisioning the instance:

```sh
# Recommended: download, review, then execute
curl -fsSL https://raw.githubusercontent.com/MAIDUONGTHAO2010/cloudy-learning/main/scripts/setup-server.sh \
    -o setup-server.sh
# Review the script before running it
cat setup-server.sh
bash setup-server.sh
```

Or clone the repo first and run it locally:

```sh
git clone https://github.com/MAIDUONGTHAO2010/cloudy-learning.git
cd cloudy-learning
# Review the script before running it
cat scripts/setup-server.sh
bash scripts/setup-server.sh
```

After the script finishes, **log out and back in** (or run `newgrp docker`) so the `docker` group membership takes effect, then follow the [Getting Started](#getting-started) steps below.

## Getting Started

1. **Clone the repository:**
    ```sh
    git clone https://github.com/your-username/cloudy-learning.git
    cd cloudy-learning
    ```

2. **Copy and configure the environment file:**
    ```sh
    cp .env.example .env
    # Edit .env if needed (DB credentials, MinIO keys, etc.)
    ```

3. **Start all services:**
    ```sh
    docker-compose up -d
    ```

4. **Install dependencies:**
    ```sh
    docker-compose exec workspace composer install
    docker-compose exec workspace npm install
    ```

5. **Generate the application key:**
    ```sh
    docker-compose exec workspace php artisan key:generate
    ```

6. **Run database migrations and seeders:**
    ```sh
    docker-compose exec workspace php artisan migrate --seed
    ```

7. **Start the Vite dev server:**
    ```sh
    docker-compose exec workspace npm run dev
    ```

8. **Access the application:**
    - Public app: [http://localhost:8080](http://localhost:8080)
    - Admin panel: [http://localhost:8080/admin](http://localhost:8080/admin)
    - MinIO console: [http://localhost:9001](http://localhost:9001)
    - Adminer (DB UI): [http://localhost:8081](http://localhost:8081)

## Docker Services

| Container | Port(s) | Purpose |
|---|---|---|
| `cloudy_workspace` | `5174` | PHP app + Vite dev server |
| `cloudy_nginx` | `8080` | Web server / reverse proxy |
| `cloudy_postgres` | `5432` | PostgreSQL 16 database |
| `cloudy_minio` | `9000` (S3 API), `9001` (console) | Object storage for videos & thumbnails |
| `cloudy_adminer` | `8081` | Database admin UI |

## Useful Commands

```sh
# Stop the environment
docker-compose down

# Run tests
docker-compose exec workspace php artisan test

# Lint and build frontend
docker-compose exec workspace npm run lint
docker-compose exec workspace npm run build

# Run migrations
docker-compose exec workspace php artisan migrate

# Artisan tinker
docker-compose exec workspace php artisan tinker
```

## Architecture

The backend follows a 4-layer architecture — see [docs/feature-guide.md](docs/feature-guide.md) for the full guide.

```
HTTP Request → FormRequest (validate) → Controller → Service → Repository → DB
```

Key directories:

```
app/
  Http/Controllers/Admin/   # Admin API controllers
  Http/Requests/Admin/      # Validated form requests
  Services/                 # Business logic
  Repositories/             # Eloquent repository implementations
  Repositories/Contracts/   # Repository interfaces
  Models/                   # Eloquent models
routes/
  web.php                   # Public + authenticated SPA routes (/api/*)
  admin.php                 # Admin panel routes (/admin/api/*)
```

## License

This project is licensed under the MIT License.
