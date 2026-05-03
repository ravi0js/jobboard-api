cat > README.md << 'EOF'
# JobBoard API

A production-grade **Job Board REST API** built with Laravel, demonstrating real-world backend development skills including authentication, RBAC, caching, testing, and containerisation.

## 🚀 Live API Documentation

> Swagger UI: `http://your-server-ip/api/documentation`

---

## 🛠 Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 (PHP 8.3) |
| Authentication | Laravel Sanctum (Token-based) |
| Authorisation | Custom RBAC Middleware |
| Database | MySQL 8.0 |
| Caching | Redis |
| Containerisation | Docker + Docker Compose |
| Testing | PHPUnit (27 tests, 57 assertions) |
| API Docs | Swagger / OpenAPI 3.0 |
| Web Server | Apache |

---

## 📋 Features

- **JWT-style token authentication** via Laravel Sanctum
- **Role-Based Access Control** — Admin, Employer, Candidate
- **Job listings** with filters (location, type, category, search)
- **Redis caching** on high-traffic endpoints
- **Job applications** with status tracking (pending → reviewed → accepted/rejected)
- **27 PHPUnit tests** covering auth, RBAC, jobs, and applications
- **Swagger UI** for interactive API documentation
- **Dockerised** — runs with a single command

---

## 🗂 API Endpoints

### Auth
| Method | Endpoint | Access | Description |
|---|---|---|---|
| POST | `/api/auth/register` | Public | Register as Employer or Candidate |
| POST | `/api/auth/login` | Public | Login and get token |
| GET | `/api/profile` | Auth | Get user profile |
| POST | `/api/auth/logout` | Auth | Logout |

### Jobs
| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/api/jobs` | Public | List jobs (filter by location, type, category) |
| GET | `/api/jobs/{id}` | Public | Get single job |
| POST | `/api/jobs` | Employer | Create job |
| PUT | `/api/jobs/{id}` | Employer | Update own job |
| DELETE | `/api/jobs/{id}` | Employer | Delete own job |
| GET | `/api/my-jobs` | Employer | List own jobs |

### Applications
| Method | Endpoint | Access | Description |
|---|---|---|---|
| POST | `/api/jobs/{id}/apply` | Candidate | Apply for a job |
| GET | `/api/my-applications` | Candidate | View own applications |
| DELETE | `/api/applications/{id}` | Candidate | Withdraw application |
| GET | `/api/jobs/{id}/applications` | Employer | View job applications |
| PUT | `/api/applications/{id}/status` | Employer | Update application status |

### Categories
| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/api/categories` | Public | List categories |
| POST | `/api/categories` | Admin | Create category |
| DELETE | `/api/categories/{id}` | Admin | Delete category |

---

## ⚡ Quick Start

### Prerequisites
- Docker
- Docker Compose

### Setup

```bash
# Clone the repo
git clone https://github.com/ravi0js/jobboard-api.git
cd jobboard-api

# Copy environment file
cp .env.example .env

# Start containers
docker compose up -d --build

# Generate app key
docker compose exec app php artisan key:generate

# Run migrations and seed
docker compose exec app php artisan migrate --seed
```

### Access
- **API Base URL:** `http://localhost:8000/api`
- **Swagger Docs:** `http://localhost:8000/api/documentation`

---

## 🧪 Running Tests

```bash
docker compose exec app php artisan test
```

---

## 🏗 Architecture

├── app/
│   ├── Http/
│   │   ├── Controllers/Api/    # AuthController, JobController, JobApplicationController
│   │   └── Middleware/         # RoleMiddleware (RBAC)
│   └── Models/                 # User, Job, JobApplication, Role, Category
├── database/
│   ├── migrations/             # All table migrations
│   ├── factories/              # Test factories
│   └── seeders/                # Role, Category seeders
├── routes/
│   └── api.php                 # All API routes
├── tests/
│   └── Feature/                # AuthTest, JobTest, JobApplicationTest
├── docker-compose.yml
└── Dockerfile


---

## 👤 Test Credentials (after seeding)

| Role | Email | Password |
|---|---|---|
| Admin | admin@jobboard.com | password |
| Employer | employer@jobboard.com | password |
| Candidate | candidate@jobboard.com | password |

---

## 📄 License

MIT
EOF