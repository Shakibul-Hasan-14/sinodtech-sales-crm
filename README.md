# SinodTech — Sales, Inventory & CRM System

A Laravel-based application for managing product inventory, sales, and customer relationship management, built as a technical assessment.

## Features Implemented

### Sales & Inventory Management

- Product catalog (CRUD) — name, SKU, price, stock quantity
- Sale creation with automatic stock deduction
- Sales are rejected if requested quantity exceeds available stock
- Stock updates are safe under concurrent sales i.e. no overselling, even with simultaneous requests

### Customer Relationship Management (CRM)

- Customer purchase history (full sale + item history per customer)
- Purchase frequency and last purchase date (computed live from sales data, not stored/cached)
- Lost customer detection — configurable inactivity period (default 90 days) via query parameter
- Customer re-engagement — simulated promotional email (Laravel Mailable + Blade template)
- Employee assignment — admins can assign inactive customers to employees for follow-up
- KPI tracking — an employee's KPI score automatically increments when their assigned (previously inactive) customer makes a new purchase

## Architecture Decisions

- **Service layer** (`app/Services/SaleService.php`) — sale creation logic is isolated from the controller, keeping business rules testable and reusable.
- **Database transactions + row locking** — `SaleService::createSale()` wraps all writes in `DB::transaction()` and uses `lockForUpdate()` on product rows to prevent race conditions when multiple sales happen concurrently.
- **Snapshotted pricing** — `sale_items.unit_price` stores the price at time of sale, independent of the product's current price, preserving historical accuracy.
- **Computed CRM fields** — `last_purchase_date` and `purchase_frequency` are derived via query rather than stored columns, avoiding data drift.
- **Query scope for lost-customer detection** — `Customer::inactive($days)` is a reusable Eloquent scope, not a scheduled job or stored status flag, so it's always accurate on demand.
- **API routes over web routes** — all endpoints live in `routes/api.php` (stateless, no CSRF), reflecting a REST-API-first design suitable for a React/Vue frontend or third-party integration.

## Tech Stack

- Laravel 11 (PHP 8.2)
- MySQL
- Mailtrap / Laravel log for email

## Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- MySQL

### Installation

1. Clone the repository:

```bash
   git clone https://github.com/Shakibul-Hasan-14/sinodtech-sales-crm.git
   cd sinodtech-sales-crm
```

2. Install PHP dependencies:

```bash
   composer install
```

3. Copy the environment file and generate an app key:

```bash
   cp .env.example .env
   php artisan key:generate
```

4. Configure your database in `.env`:

```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=sinodtech
   DB_USERNAME=root
   DB_PASSWORD=
```

5. Create the database (via phpMyAdmin, MySQL CLI, or your preferred client):

```sql
   CREATE DATABASE sinodtech;
```

6. Configure email — for real delivery via Mailtrap, add your credentials:

```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=tls
```

If left as `MAIL_MAILER=log` (default), emails are written to `storage/logs/laravel.log` instead of sent.

7. Run migrations and seed the database with realistic sample data:

```bash
   php artisan migrate:fresh --seed
```

This creates 5 employees, 20 products, 30 customers, and a realistic history of sales including backdated sales so the "lost customer" detection has real data to surface.

8. Start the development server:

```bash
   php artisan serve
```

The API is now available at `http://127.0.0.1:8000/api`.

## API Documentation

Full Postman collection with example requests for every endpoint:
**[SinodTech API — Postman Collection](https://api.postman.com/collections/32622238-910c3bf8-f2ec-420e-a3df-f550359f13a9?access_key=PMAT-01KYJRGVB805TK3N8GPF5FNSP5)**

To use:

1. Import the link into Postman (Import → Link → paste URL)
2. Set the `base_url` variable to match your local server (default: `http://127.0.0.1:8000/api`)
3. Test using sample IDs from the seeded data

## Key API Endpoints

| Method | Endpoint                          | Description                          |
| ------ | --------------------------------- | ------------------------------------ |
| GET    | `/api/products`                   | List products                        |
| POST   | `/api/products`                   | Create product                       |
| PUT    | `/api/products/{id}`              | Update product                       |
| DELETE | `/api/products/{id}`              | Delete product                       |
| GET    | `/api/customers`                  | List customers                       |
| GET    | `/api/customers/{id}`             | Customer detail + purchase history   |
| GET    | `/api/customers-inactive?days=90` | List customers inactive for N days   |
| PATCH  | `/api/customers/{id}/assign`      | Assign customer to an employee       |
| POST   | `/api/customers/{id}/reengage`    | Send (simulated) re-engagement email |
| GET    | `/api/employees`                  | List employees                       |
| POST   | `/api/employees`                  | Create employee                      |
| GET    | `/api/sales`                      | List sales (with items + customer)   |
| POST   | `/api/sales`                      | Record a new sale (deducts stock)    |
| GET    | `/api/sales/{id}`                 | Sale detail                          |
