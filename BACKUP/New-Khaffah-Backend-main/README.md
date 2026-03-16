# 🕋 Khaffah Backend

Backend aplikasi **Khaffah** berbasis **Laravel 10 + PostgreSQL**, berjalan menggunakan **Docker** untuk memudahkan setup di berbagai mesin tanpa harus menginstal PHP, Composer, atau PostgreSQL secara manual.

---

## 🚀 Fitur Utama

- Autentikasi menggunakan **Laravel Sanctum**
- Manajemen **Role & Permission** (Spatie Laravel Permission)
- Role bawaan: `superadmin`, `mitra`, dan `user`
- Database PostgreSQL via Docker
- Environment siap pakai dengan konfigurasi `.env`
- Seeder untuk membuat akun awal otomatis

---

## ⚙️ Persiapan Awal

### 1️⃣ Clone Repository

```bash
git clone https://github.com/username/New-Khaffah-Backend.git
cd New-Khaffah-Backend
```

---

### 2️⃣ Buat File `.env`

Buat file `.env` di root folder (jika belum ada), lalu isi dengan konfigurasi berikut:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=khaffah_db
DB_USERNAME=khaffah_user
DB_PASSWORD=secret

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

FILESYSTEM_DISK=local
APP_TIMEZONE=Asia/Jakarta
BROADCAST_DRIVER=log
```

---

### 3️⃣ Jalankan Docker

```bash
docker compose build --no-cache app
docker compose up -d
```

---

### 4️⃣ Generate Key & Migrasi Database

Masuk ke container app:

```bash
docker compose exec app bash
php artisan key:generate
php artisan migrate --seed
exit
```

Seeder akan otomatis membuat **role dan user bawaan**.

---

## 👤 Akun Default (Seeder)

| Role         | Email              | Password       | Nama Lengkap     |
|---------------|--------------------|----------------|------------------|
| Superadmin    | admin@kaffah.com  | password123    | Super Admin      |
| Mitra         | siti@kaffah.com   | password123    | Siti Aminah      |
| User          | budi@kaffah.com   | password123    | Budi Santoso     |

---

## 🧩 Testing Endpoint

### 🔑 Register
```
POST /api/register
```

### 🔑 Login
```
POST /api/login
```

Response berisi token:
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxx"
}
```

Gunakan token ini untuk endpoint lain dengan menambahkan header:
```
Authorization: Bearer <token>
```

---

### 🔒 Logout
```
POST /api/logout
```

---

### 👤 Info User
```
GET /api/me
```

---

### 👥 Endpoint Role-Based

| Role | Endpoint | Method | Keterangan |
|------|-----------|--------|-------------|
| user | `/api/test-user` | GET | Hanya bisa diakses oleh role `user` |
| mitra | `/api/mitra/dashboard` | GET | Hanya bisa diakses oleh `mitra` |
| superadmin | `/api/admin/dashboard` | GET | Hanya bisa diakses oleh `superadmin` |

---

## 🧰 Troubleshooting

| Masalah | Solusi |
|----------|---------|
| `Unable to set application key` | Pastikan file `.env` sudah dibuat dan jalankan `php artisan key:generate` |
| `RoleDoesNotExist` | Jalankan ulang `php artisan migrate --seed` untuk memastikan RoleSeeder jalan |
| Tidak bisa akses API | Pastikan container berjalan: `docker ps` dan gunakan `http://localhost:8000` |

---

## 🧹 Perintah Tambahan

```bash
# Hentikan container
docker compose down

# Jalankan ulang container
docker compose up -d

# Reset database dan seeding ulang
docker compose exec app php artisan migrate:fresh --seed
```

---

## ✨ Developer Info

**Framework:** Laravel 10  
**Database:** PostgreSQL 15  
**PHP Version:** 8.2  
**Containerized via:** Docker Compose  
