# Sistem Manajemen Laundry (Laundry Asas)

Aplikasi web manajemen operasional dan kasir (Point of Sale / POS) laundry berbasis **Laravel 12**, **Laravel Breeze (Blade)**, **Alpine.js**, dan **Tailwind CSS**.

---

## Fitur Utama

### 1. Autentikasi & Hak Akses (`admin` & `kasir`)
- Seluruh halaman aplikasi dilindungi autentikasi Laravel Breeze (`auth`).
- Terdapat dua role pada tabel `users`:
  - **`kasir`**: Hanya dapat mengakses halaman Kasir POS (`/`). Jika mengakses rute admin akan diarahkan kembali ke `/`.
  - **`admin`**: Dapat mengakses halaman Kasir POS, Dashboard Rekap Pesanan (`/dashboard`), serta manajemen layanan (`/admin/laundry-items`).

### 2. Halaman Kasir / POS (`/`)
- Menampilkan daftar layanan laundry lengkap dengan tab filter kategori (**Semua**, **Cepat**, **Kilat**, **Standar**, **Komplit**).
- Kolom **Nama Pelanggan** wajib diisi (`required`).
- Input jumlah berat/satuan per layanan dan proses pemesanan langsung melalui tombol **Pesan Sekarang** (`POST /checkout`).

### 3. Pembuatan Struk Otomatis di Backend
- Setiap transaksi baru otomatis menghasilkan file struk berformat teks rapi (ASCII) yang disimpan di direktori backend:
  `storage/app/struk/struk_order_{id}.txt`
- Struk otomatis diperbarui apabila admin mengubah status pesanan.
- Halaman kasir hanya menampilkan pesan sukses transaksi tanpa mengekspos path file backend.

### 4. Dashboard Admin (`/dashboard` & `/admin/dashboard`)
- Menampilkan tabel rekap seluruh pesanan beserta nama pelanggan, kasir yang melayani, waktu transaksi, rincian layanan, total tagihan, dan status (`pending`, `approved`, `declined`).
- Admin dapat memperbarui status pesanan secara langsung (`PATCH /admin/orders/{id}/status`).
- Tersedia tautan **Lihat Struk** (`GET /admin/orders/{id}/struk`) bagi admin untuk melihat isi file struk dari backend.

### 5. CRUD Layanan Laundry (`/admin/laundry-items`)
- Admin dapat menambah, mengubah, dan menghapus item layanan laundry.
- Input **Kategori** (`Cepat`, `Kilat`, `Standar`, `Komplit`) dan **Satuan** (`kg`, `pcs`, `pasang`) menggunakan dropdown berbasis enum yang tervalidasi.

---

## Struktur Database

1. **`users`**
   - Kolom bawaan Breeze ditambah `role` (`enum('admin', 'kasir')`, default `'kasir'`).
2. **`laundry_items`**
   - `id`, `name`, `category` (`enum`: `Cepat`, `Kilat`, `Standar`, `Komplit`), `price` (`integer`), `unit` (`enum`: `kg`, `pcs`, `pasang`), `description` (`text`, nullable), timestamps.
3. **`orders`**
   - `id`, `user_id` (FK -> `users.id`), `customer_name` (`string`), `total_price` (`integer`), `status` (`enum`: `pending`, `approved`, `declined`, default `'pending'`), `receipt_path` (`string`, nullable), timestamps.
4. **`order_details`**
   - `id`, `order_id` (FK -> `orders.id`), `laundry_item_id` (FK -> `laundry_items.id`), `quantity` (`integer`), `subtotal` (`integer`), timestamps.

---

## Teknologi yang Digunakan

- **PHP** `^8.2`
- **Laravel Framework** `^12.0`
- **Laravel Breeze** `^2.4` (Blade stack)
- **Tailwind CSS** & **Alpine.js** (dibundle dengan **Vite**)
- **Database**: SQLite (default di `.env.example`, kompatibel dengan MySQL/MariaDB/PostgreSQL)

---

## Cara Instalasi & Menjalankan

1. **Install dependency PHP & JavaScript**
   ```bash
   composer install
   npm install
   npm run build
   ```

2. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *(Pastikan file `database/database.sqlite` sudah tersedia jika menggunakan SQLite, atau sesuaikan pengaturan `DB_*` pada `.env` jika menggunakan MySQL).*

3. **Migrasi & Seeding Data Awal**
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **Jalankan Server Lokal**
   ```bash
   php artisan serve
   ```
   Atau jalankan server beserta Vite dev mode sekaligus:
   ```bash
   composer run dev
   ```

   Akses aplikasi melalui `http://127.0.0.1:8000`.

---

## Akun Default (Seeder)

Seeder (`DatabaseSeeder` dan `LaundryItemSeeder`) menyediakan akun berikut serta 6 data layanan awal:

| Role  | Email              | Password      | Akses Utama |
| ----- | ------------------ | ------------- | ----------- |
| Admin | `admin@gmail.com`  | `password123` | `/dashboard`, `/admin/laundry-items`, `/` |
| Kasir | `kasir@gmail.com`  | `password123` | `/` (Halaman Kasir POS) |

---

## Daftar Route Utama

| Method | URI | Name | Akses |
| ------ | --- | ---- | ----- |
| `GET` | `/` | `customer.index` | Login (`admin`, `kasir`) |
| `POST` | `/checkout` | `customer.checkout` | Login (`admin`, `kasir`) |
| `GET` | `/dashboard` | `dashboard` | Admin |
| `GET` | `/admin/dashboard` | `admin.dashboard` | Admin |
| `PATCH` | `/admin/orders/{id}/status` | `admin.orders.updateStatus` | Admin |
| `GET` | `/admin/orders/{id}/struk` | `admin.orders.viewStruk` | Admin |
| Resource | `/admin/laundry-items` | `laundry-items.*` | Admin |

---

## Pengujian

Jalankan test suite dengan perintah:

```bash
php artisan test
```
