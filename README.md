# 🧠 Knowledge Management System – Slim Framework

Sebuah aplikasi sederhana berbasis web untuk pengelolaan pengetahuan (Knowledge Management System), dibangun menggunakan [Slim Framework](https://www.slimframework.com/) sebagai backend ringan dan efisien.

## 🚀 Fitur Utama
- Manajemen kategori & artikel pengetahuan
- CRUD data berbasis REST API
- Responsif & mudah diintegrasikan ke frontend lain
- Routing dan middleware Slim yang ringan & modular

## 🛠️ Tech Stack
- **Backend:** PHP 8+, Slim Framework 4
- **Database:** MySQL / MariaDB
- **Tooling:** Composer, Dotenv, Eloquent (opsional)

## 📦 Cara Instalasi

1. **Clone repositori**
   ```bash
   git clone https://github.com/RizalRio/knowledge-management-system-slim.git
   cd knowledge-management-system-slim
Install dependensi

bash
Salin
Edit
composer install
Copy & konfigurasi .env

bash
Salin
Edit
cp .env.example .env
# Edit sesuai konfigurasi database kamu
Jalankan server

bash
Salin
Edit
php -S localhost:8000 -t public
🧪 Struktur Folder
pgsql
Salin
Edit
├── app/
│   ├── Controllers/
│   ├── Models/
│   └── Routes/
├── public/
├── .env
└── composer.json
🎯 Tujuan Proyek
Project ini ditujukan sebagai sistem manajemen pengetahuan ringan untuk digunakan secara internal di perusahaan atau tim, serta latihan modularisasi kode PHP modern.

📸 Screenshot / Demo
(Tambahkan di sini jika tersedia)
