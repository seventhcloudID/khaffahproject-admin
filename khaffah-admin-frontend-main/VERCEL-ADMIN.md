# Deploy Admin ke Vercel – Login Gagal?

## Penyebab

- **Lokal (http://127.0.0.1:5173):** Request ke `/api/*` di-**proxy** oleh Vite ke backend (mis. `http://127.0.0.1:8000`), jadi login jalan.
- **Vercel (https://khaffah-admin.vercel.app):** Tidak ada proxy. Kalau env **tidak** di-set, request mengarah ke domain Vercel sendiri (bukan ke backend), sehingga login gagal.

## Solusi

### 1. Set Environment Variable di Vercel

1. Buka [vercel.com](https://vercel.com) → pilih project **khaffah-admin**.
2. **Settings** → **Environment Variables**.
3. Tambah:
   - **Name:** `VITE_API_BASE_URL`
   - **Value:** URL backend API (tanpa slash di akhir), contoh: `https://api.kaffahtrip.com`
   - Environment: pilih **Production** (dan Preview/Development jika perlu).
4. Simpan.

### 2. Redeploy

Agar env terbaca di build, wajib **redeploy**:

- **Deployments** → deployment terakhir → **⋯** → **Redeploy**.

Atau push commit baru; Vercel akan build ulang dengan env tersebut.

### 3. CORS di Backend

Backend (Laravel) harus mengizinkan origin admin di Vercel, misalnya:

- `https://khaffah-admin.vercel.app`

Pastikan config CORS backend mencantumkan origin tersebut (dan method/header yang dipakai untuk login).

---

Setelah `VITE_API_BASE_URL` di-set dan redeploy, login di https://khaffah-admin.vercel.app/auth/login akan memanggil backend yang benar dan seharusnya bisa login.
