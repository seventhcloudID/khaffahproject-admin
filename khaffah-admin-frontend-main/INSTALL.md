# Cara Install Khaffah Admin Frontend

Frontend admin ini pakai **Vue 3**, **Vite**, dan **TypeScript**. Backend API-nya: Laravel di `https://apikaffah.paperostudio.com`.

---

## Persyaratan

- **Node.js** 20.19+ atau 22.12+ ([nodejs.org](https://nodejs.org))
- **npm** (biasanya ikut Node)

Cek versi:
```bash
node -v   # harus 20.x atau 22.x
npm -v
```

---

## 1. Clone / masuk ke folder proyek

```bash
cd path/ke/khaffah-admin-frontend-main
```

---

## 2. Install dependency

```bash
npm install
```

---

## 3. Konfigurasi environment

Copy file contoh env, lalu edit:

```bash
# Windows (PowerShell)
copy .env.example .env

# Linux / Mac
cp .env.example .env
```

Buka `.env` dan atur URL backend API (tanpa trailing slash):

```env
# Untuk production / backend yang sudah deploy
VITE_API_BASE_URL=https://apikaffah.paperostudio.com/api
```

Untuk development lokal (backend jalan di `http://localhost:8000`):

```env
VITE_API_BASE_URL=http://localhost:8000/api
```

**Opsional** (jika URL file storage beda dari backend):

```env
VITE_STORAGE_URL=https://apikaffah.paperostudio.com
VITE_APP_URL=https://admin.domain-anda.com
```

Simpan `.env`. Kalau ubah isi `.env`, **restart** dev server (`npm run dev`).

---

## 4. Jalankan development

```bash
npm run dev
```

Browser buka URL yang muncul (biasanya `http://localhost:5173`). Admin siap dipakai; semua request ke backend mengikuti `VITE_API_BASE_URL` di `.env`.

---

## 5. Build untuk production (deploy)

Build hasilnya ada di folder `dist/`:

```bash
npm run build
```

Lalu:

- **Deploy ke hosting statik:** upload isi folder `dist/` ke document root (mis. subdomain `admin.domain.com`). Pastikan hosting dikonfigurasi untuk SPA (fallback ke `index.html`).
- **Preview hasil build di lokal:**  
  `npm run preview`

---

## Ringkasan perintah

| Perintah           | Kegunaan                    |
|--------------------|-----------------------------|
| `npm install`      | Install dependency          |
| `npm run dev`      | Development (hot reload)   |
| `npm run build`    | Build production ke `dist/`|
| `npm run preview`  | Preview hasil build        |
| `npm run lint`     | Cek & perbaiki ESLint      |

---

## Troubleshooting

- **"Backend tidak terjangkau" / Network Error**  
  Pastikan `VITE_API_BASE_URL` di `.env` benar (termasuk `https://` dan `/api`), backend Laravel bisa diakses dari browser, dan setelah ubah `.env` Anda **restart** `npm run dev`.

- **CORS error**  
  Backend Laravel harus mengizinkan origin frontend (CORS). Di project Laravel, cek konfigurasi CORS (mis. `config/cors.php` atau middleware).

- **Node version**  
  Jika ada error terkait Node, pastikan versi memenuhi `package.json` (^20.19.0 atau >=22.12.0).
