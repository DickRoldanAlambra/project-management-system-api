# Project Management System API

A RESTful API built with Laravel 12 for managing projects, tasks, and comments with role-based access control.

## Features

- Authentication via Laravel Sanctum (token-based)
- Role-based access control (admin, manager, user)
- CRUD operations for Projects, Tasks, and Comments
- Task assignment and reassignment
- Filtering and search on projects and tasks

## Requirements

- PHP 8.4+
- Composer
- MySQL
- Node.js & NPM (for frontend assets)

## Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd project-management-system-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure the database** in `.env`
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=project_management_system_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seed the database**
   ```bash
   php artisan migrate --seed
   ```
   This creates: 3 admins, 3 managers, 5 users, 5 projects, 10 tasks, and 10 comments.

6. **Start the server**
   ```bash
   php artisan serve
   ```
   The API will be available at `http://localhost:8000`.

## Roles & Permissions

| Action              | Admin | Manager | User |
|---------------------|-------|---------|------|
| Create project      | Yes   | No      | No   |
| Update project      | Yes   | No      | No   |
| Delete project      | Yes   | No      | No   |
| View projects       | Yes   | Yes     | Yes  |
| Create task         | No    | Yes     | No   |
| Update task         | No    | Yes     | Assigned only |
| Delete task         | No    | Yes     | No   |
| View tasks          | Yes   | Yes     | Yes  |
| Create comment      | Yes   | Yes     | Yes  |
| View comments       | Yes   | Yes     | Yes  |

## API Endpoints

Base URL: `http://localhost:8000/api/v1`

### Authentication
| Method | Endpoint         | Description       | Auth |
|--------|------------------|-------------------|------|
| POST   | /auth/register   | Register user     | No   |
| POST   | /auth/login      | Login             | No   |
| POST   | /auth/logout     | Logout            | Yes  |
| GET    | /me              | Current user info | Yes  |

### Projects
| Method | Endpoint            | Description      | Role  |
|--------|---------------------|------------------|-------|
| GET    | /projects           | List projects    | Any   |
| GET    | /projects/{id}      | Show project     | Any   |
| POST   | /projects           | Create project   | Admin |
| PUT    | /projects/{id}      | Update project   | Admin |
| DELETE | /projects/{id}      | Delete project   | Admin |

### Tasks
| Method | Endpoint                    | Description    | Role              |
|--------|-----------------------------|----------------|-------------------|
| GET    | /projects/{id}/tasks        | List tasks     | Any               |
| GET    | /tasks/{id}                 | Show task      | Any               |
| POST   | /projects/{id}/tasks        | Create task    | Manager           |
| PUT    | /tasks/{id}                 | Update task    | Manager/Assigned  |
| DELETE | /tasks/{id}                 | Delete task    | Manager           |

### Comments
| Method | Endpoint                 | Description      | Role |
|--------|--------------------------|------------------|------|
| GET    | /tasks/{id}/comments     | List comments    | Any  |
| POST   | /tasks/{id}/comments     | Create comment   | Any  |

## Postman Collection

A pre-built Postman collection is included at `postman_collection.json` with all API endpoints ready to use.

### How to Import

1. Open Postman
2. Click the **Import** button (top-left corner)
3. Drag and drop the `postman_collection.json` file, or click **Upload Files** and select it
4. The collection **"Project Management System API"** will appear in your sidebar

### Usage

1. Start with the **Auth > Register** request to create a user
2. Use **Auth > Login** to authenticate — the token is automatically saved to the collection variables
3. All other requests will use the saved token automatically
4. To switch roles, register/login with a different user (set `role` to `admin`, `manager`, or `user`)

> **Note:** Seeded users all have the password `password`. You can login with any seeded user's email.

## Testing

```bash
php artisan test
```

## Code Formatting

```bash
vendor/bin/pint
```
