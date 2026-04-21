# 📚 Dokumentasi Backend — Nalarin (NextGen Backend)

> **Versi Dokumentasi:** 1.0.0 | **Terakhir Diperbarui:** April 2026
> Dibuat untuk developer Flutter, developer backend baru, dan pemangku kepentingan non-teknis.

---

## 📋 Daftar Isi

| # | Seksi | Audiens |
|---|-------|---------|
| [A](#-a-gambaran-umum-sistem-non-teknis) | Gambaran Umum Sistem (Non-Teknis) | Stakeholder, PM |
| [B](#-b-desain-arsitektur) | Desain Arsitektur | Developer Backend |
| [C](#-c-prinsip-desain-api) | Prinsip Desain API | Developer Backend & Flutter |
| [D](#-d-dokumentasi-endpoint-api) | Dokumentasi Endpoint API | Developer Flutter |
| [E](#-e-integrasi-dengan-flutter-penting) | Integrasi dengan Flutter | Developer Flutter |
| [F](#-f-struktur-database) | Struktur Database | Developer Backend |
| [G](#-g-panduan-setup) | Panduan Setup | Developer Baru |
| [H](#-h-panduan-pengembangan) | Panduan Pengembangan | Developer Backend |
| [I](#-i-panduan-kontribusi) | Panduan Kontribusi | Semua Developer |
| [J](#-j-penjelasan-non-teknis) | Penjelasan Non-Teknis | Stakeholder, Investor |
| [+](#-bonus-template-standar-respons-api) | Bonus: Template, Future, Mistakes | Semua |

---

## 🌐 A. Gambaran Umum Sistem (Non-Teknis)

### Apa itu Backend Nalarin?

Backend Nalarin adalah **"otak" dan "jantung" dari aplikasi Nalarin**. Ia adalah sistem yang berjalan di server dan bertanggung jawab untuk:

- ✅ Menyimpan semua data (paket beasiswa, mentor, artikel)
- ✅ Melayani permintaan data dari aplikasi Flutter
- ✅ Memastikan data yang dikirim valid dan aman
- ✅ Menyediakan data statistik untuk dashboard admin

### Peran dalam Ekosistem Nalarin

```
┌─────────────────────────────────────────────────────────────────┐
│                    EKOSISTEM NALARIN                            │
│                                                                 │
│  ┌──────────────┐    HTTP/JSON    ┌──────────────────────────┐  │
│  │              │ ─────────────► │                          │  │
│  │  Aplikasi    │                │  Backend Laravel         │  │
│  │  Flutter     │ ◄───────────── │  (backendNextgen)        │  │
│  │  (Mobile)    │   Response     │                          │  │
│  │              │                │  • API Endpoints         │  │
│  └──────────────┘                │  • Business Logic        │  │
│                                  │  • Validasi Data         │  │
│                                  └─────────────┬────────────┘  │
│                                                │               │
│                                                ▼               │
│                                  ┌──────────────────────────┐  │
│                                  │   MongoDB Database       │  │
│                                  │   (backendPJBL)          │  │
│                                  │                          │  │
│                                  │  • paket_beasiswas       │  │
│                                  │  • mentors               │  │
│                                  │  • artikels              │  │
│                                  └──────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### Alur Data Sederhana

1. **Pengguna membuka aplikasi Flutter** → aplikasi meminta data ke backend
2. **Backend menerima permintaan** → memvalidasi dan mengambil data dari database
3. **Backend mengirim respons JSON** → Flutter menerima dan menampilkan data ke pengguna

Bayangkan backend seperti **pelayan di restoran**: Flutter adalah tamunya, database adalah dapurnya. Pelayan (backend) menerima pesanan dari tamu, mengambil makanan dari dapur, lalu menyajikannya dengan rapi.

---

## 🏗️ B. Desain Arsitektur

### Tech Stack

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | Laravel | ^13.0 |
| Runtime | PHP | ^8.3 |
| Database | MongoDB | ^7.0 |
| ODM | laravel-mongodb | latest |
| Auth | Laravel Sanctum | ^4.0 |
| Asset Bundler | Vite | latest |

### Struktur Laravel yang Digunakan

```
backendNextgen/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                    # Controller untuk Flutter API
│   │   │   │   ├── PaketBeasiswaController.php
│   │   │   │   ├── MentorController.php
│   │   │   │   ├── ArtikelController.php
│   │   │   │   └── DashboardChartController.php
│   │   │   ├── PaketBeasiswaController.php  # Controller untuk Web Admin
│   │   │   ├── MentorController.php
│   │   │   ├── ArtikelController.php
│   │   │   └── DashboardController.php
│   │   └── (Middleware - belum dikonfigurasi custom)
│   ├── Models/
│   │   ├── PaketBeasiswa.php           # Model koleksi paket beasiswa
│   │   ├── Mentor.php                  # Model koleksi mentor
│   │   ├── Artikel.php                 # Model koleksi artikel
│   │   └── User.php                    # Model user (Sanctum ready)
│   └── Providers/
├── database/
│   └── migrations/                     # Definisi koleksi MongoDB
├── routes/
│   ├── api.php                         # Endpoint API (untuk Flutter)
│   └── web.php                         # Route Web (untuk admin dashboard)
├── resources/
│   └── views/                          # Blade views (admin dashboard)
└── .env                                # Konfigurasi environment
```

### Mengapa Arsitektur Ini?

**Dua Lapisan Controller (Web + API):**
- `Controllers/Api/` → khusus melayani Flutter, selalu return JSON
- `Controllers/` (root) → melayani admin dashboard web (Blade views)

Pemisahan ini mengikuti prinsip **Separation of Concerns** — fungsi yang berbeda tidak boleh dicampur dalam satu file. Controller API tidak pernah merender HTML, dan Controller Web tidak pernah diakses Flutter.

**Mengapa MongoDB?**
Data seperti `fase_checkpoint` dan `persyaratan` pada paket beasiswa bersifat dinamis (array dengan jumlah item tidak pasti). MongoDB sangat cocok untuk tipe data seperti ini tanpa perlu membuat tabel relasional terpisah.

### Cara Request Diproses (Step-by-Step)

```
[Flutter / Client]
      │
      │  1. Kirim HTTP Request
      │     GET /api/paket-beasiswa
      ▼
[routes/api.php]
      │
      │  2. Route matching & dispatch ke Controller
      │     Route::apiResource('paket-beasiswa', ...)
      ▼
[App\Http\Controllers\Api\PaketBeasiswaController@index]
      │
      │  3. Controller memanggil Model
      │     PaketBeasiswa::all()
      ▼
[App\Models\PaketBeasiswa]
      │
      │  4. Model query ke MongoDB via laravel-mongodb ODM
      │     db.paket_beasiswas.find({})
      ▼
[MongoDB - Collection: paket_beasiswas]
      │
      │  5. Data dikembalikan ke Model → Controller
      ▼
[Controller membungkus data dalam format standar]
      │
      │  6. Mengembalikan JSON response
      ▼
[Flutter menerima JSON & memparse ke Model Dart]
```

---

## 📐 C. Prinsip Desain API

### Prinsip RESTful yang Digunakan

| Prinsip | Implementasi |
|---------|--------------|
| **Resource-based URL** | `/api/paket-beasiswa`, `/api/mentor` |
| **HTTP Methods Semantik** | GET=baca, POST=buat, PUT=ubah, DELETE=hapus |
| **Stateless** | Setiap request harus berdiri sendiri (tidak bergantung state sebelumnya) |
| **JSON as Data Format** | Semua request dan response menggunakan JSON |
| **Konsisten** | Format response yang sama di semua endpoint |

### Konvensi Penamaan Endpoint

```
# Format umum:
/api/{resource}          → kumpulan resource (index)
/api/{resource}/{id}     → satu resource (show, update, destroy)

# Contoh nyata:
GET    /api/paket-beasiswa       → semua paket beasiswa
GET    /api/paket-beasiswa/{id}  → satu paket beasiswa
POST   /api/paket-beasiswa       → buat paket beasiswa baru
PUT    /api/paket-beasiswa/{id}  → update paket beasiswa
DELETE /api/paket-beasiswa/{id}  → hapus paket beasiswa
```

**Aturan penamaan:**
- Gunakan **kebab-case** untuk resource: `paket-beasiswa` (bukan `paketBeasiswa` atau `paket_beasiswa`)
- Gunakan **jamak** (plural): `mentors`, `artikels`
- Nested resource: `/api/paket-beasiswa/{id}/peserta` (untuk masa depan)

### ✅ Format Respons Standar (WAJIB DIIKUTI)

Semua endpoint **wajib** mengembalikan respons dalam format berikut:

#### Respons Sukses

```json
{
  "success": true,
  "message": "Deskripsi singkat hasil operasi",
  "data": { ... }
}
```

#### Respons Sukses (Kumpulan Data)

```json
{
  "success": true,
  "message": "Daftar data Paket Beasiswa",
  "data": [
    { ... },
    { ... }
  ]
}
```

#### Respons Error

```json
{
  "success": false,
  "message": "Deskripsi error yang jelas",
  "errors": {
    "field_name": ["Pesan error validasi untuk field ini"]
  }
}
```

### HTTP Status Code yang Digunakan

| Status | Makna | Kapan Digunakan |
|--------|-------|-----------------|
| `200 OK` | Sukses | GET, PUT, DELETE berhasil |
| `201 Created` | Data dibuat | POST berhasil membuat data baru |
| `400 Bad Request` | Request salah format | Body JSON tidak valid |
| `401 Unauthorized` | Tidak terautentikasi | Token tidak ada / invalid |
| `404 Not Found` | Data tidak ditemukan | ID tidak ada di database |
| `422 Unprocessable Entity` | Validasi gagal | Field wajib tidak diisi |
| `500 Internal Server Error` | Error server | Bug di kode / MongoDB down |

### Pendekatan Autentikasi

> ⚠️ **Status: Belum Diimplementasi**

Sistem saat ini **belum menggunakan autentikasi** pada endpoint API. Semua endpoint bersifat publik.

**Rencana ke depan:** Implementasi **Laravel Sanctum** (sudah terpasang di `composer.json`):
- Login → server mengembalikan `access_token`
- Setiap request Flutter menyertakan header: `Authorization: Bearer {token}`
- Endpoint dilindungi dengan middleware `auth:sanctum`

---

## 📡 D. Dokumentasi Endpoint API

**Base URL Development:** `http://localhost:8000/api`
**Base URL Production:** `https://api.nalarin.id/api` *(akan dikonfigurasi)*

---

### 📦 1. Paket Beasiswa

#### `GET /api/paket-beasiswa`

**Tujuan:** Mengambil semua daftar paket beasiswa yang tersedia.

**Request:**
```http
GET /api/paket-beasiswa HTTP/1.1
Accept: application/json
```

**Tidak ada request body / parameter.**

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar data Paket Beasiswa",
  "data": [
    {
      "_id": "6621f4b2e3a4c90001234567",
      "nama_beasiswa": "Beasiswa Unggulan Kemendikbud",
      "fase_checkpoint": [
        "Fase 1: Persiapan Dokumen",
        "Fase 2: Penulisan Esai",
        "Fase 3: Wawancara"
      ],
      "persyaratan": [
        "IPK minimal 3.5",
        "Mahasiswa aktif S1",
        "Tidak sedang menerima beasiswa lain"
      ],
      "benefit": [
        "Bebas biaya kuliah",
        "Tunjangan hidup Rp 1.400.000/bulan"
      ],
      "url": "https://beasiswaunggulan.kemdikbud.go.id",
      "gambar": "https://storage.nalarin.id/images/bu-kemendikbud.jpg",
      "deadline": "2026-06-03T00:00:00.000Z",
      "harga": 49000,
      "created_at": "2026-04-16T09:00:00.000Z",
      "updated_at": "2026-04-16T09:00:00.000Z"
    }
  ]
}
```

**Kemungkinan Error:**

| Status | Kondisi |
|--------|---------|
| `500` | MongoDB tidak dapat diakses |

---

#### `POST /api/paket-beasiswa`

**Tujuan:** Membuat paket beasiswa baru.

**Request Body (JSON):**
```json
{
  "nama_beasiswa": "Beasiswa Unggulan Kemendikbud",
  "fase_checkpoint": [
    "Fase 1: Persiapan Dokumen",
    "Fase 2: Penulisan Esai"
  ],
  "persyaratan": [
    "IPK minimal 3.5",
    "Mahasiswa aktif S1"
  ],
  "deadline": "2026-06-03",
  "harga": 49000
}
```

**Aturan Validasi:**

| Field | Wajib | Tipe | Aturan |
|-------|-------|------|--------|
| `nama_beasiswa` | ✅ | string | max:255 |
| `fase_checkpoint` | ✅ | array | min:1 item |
| `fase_checkpoint.*` | ✅ | string | setiap item harus string |
| `persyaratan` | ✅ | array | min:1 item |
| `persyaratan.*` | ✅ | string | setiap item harus string |
| `deadline` | ✅ | date | format: YYYY-MM-DD |
| `harga` | ✅ | integer | min:0 |
| `benefit` | ❌ | array | opsional |
| `url` | ❌ | string | URL paket/website beasiswa |
| `gambar` | ❌ | string | URL gambar thumbnail |

**Contoh Respons (201 Created):**
```json
{
  "success": true,
  "message": "Data Paket Beasiswa berhasil disimpan",
  "data": {
    "_id": "6621f4b2e3a4c90001234567",
    "nama_beasiswa": "Beasiswa Unggulan Kemendikbud",
    "fase_checkpoint": ["Fase 1: Persiapan Dokumen", "Fase 2: Penulisan Esai"],
    "persyaratan": ["IPK minimal 3.5", "Mahasiswa aktif S1"],
    "deadline": "2026-06-03T00:00:00.000Z",
    "harga": 49000,
    "created_at": "2026-04-21T15:00:00.000Z",
    "updated_at": "2026-04-21T15:00:00.000Z"
  }
}
```

**Contoh Respons Error Validasi (422):**
```json
{
  "message": "The nama beasiswa field is required.",
  "errors": {
    "nama_beasiswa": ["The nama beasiswa field is required."],
    "fase_checkpoint": ["The fase checkpoint field is required."]
  }
}
```

---

#### `GET /api/paket-beasiswa/{id}`

**Tujuan:** Mengambil detail satu paket beasiswa berdasarkan ID.

**Path Parameter:**
- `{id}` — MongoDB ObjectId dari paket beasiswa

**Contoh Request:**
```http
GET /api/paket-beasiswa/6621f4b2e3a4c90001234567 HTTP/1.1
Accept: application/json
```

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Detail data Paket Beasiswa",
  "data": {
    "_id": "6621f4b2e3a4c90001234567",
    "nama_beasiswa": "Beasiswa Unggulan Kemendikbud",
    "fase_checkpoint": ["Fase 1: Persiapan Dokumen", "Fase 2: Penulisan Esai"],
    "persyaratan": ["IPK minimal 3.5", "Mahasiswa aktif S1"],
    "benefit": ["Bebas biaya kuliah", "Tunjangan hidup"],
    "url": "https://beasiswaunggulan.kemdikbud.go.id",
    "gambar": "https://storage.nalarin.id/images/bu.jpg",
    "deadline": "2026-06-03T00:00:00.000Z",
    "harga": 49000
  }
}
```

**Contoh Respons Error (404):**
```json
{
  "success": false,
  "message": "Data tidak ditemukan"
}
```

---

#### `PUT /api/paket-beasiswa/{id}`

**Tujuan:** Mengupdate seluruh data paket beasiswa.

> ⚠️ Gunakan `PUT` untuk update penuh. Pastikan semua field wajib disertakan.

**Request Body:** Sama seperti `POST`.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data Paket Beasiswa berhasil diubah",
  "data": { ... }
}
```

---

#### `DELETE /api/paket-beasiswa/{id}`

**Tujuan:** Menghapus paket beasiswa secara permanen.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Data Paket Beasiswa berhasil dihapus"
}
```

---

### 👨‍🏫 2. Mentor

#### `GET /api/mentor`

**Tujuan:** Mengambil semua daftar mentor.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar data Mentor",
  "data": [
    {
      "_id": "6621f4b2e3a4c90001234abc",
      "nama_mentor": "Budi Santoso",
      "pendidikan": "S2 Teknik Informatika, Universitas Indonesia",
      "awardee": ["LPDP 2022", "Beasiswa Unggulan 2020"],
      "created_at": "2026-04-16T09:00:00.000Z",
      "updated_at": "2026-04-16T09:00:00.000Z"
    }
  ]
}
```

---

#### `POST /api/mentor`

**Aturan Validasi:**

| Field | Wajib | Tipe | Aturan |
|-------|-------|------|--------|
| `nama_mentor` | ✅ | string | max:255 |
| `pendidikan` | ✅ | string | max:255 |
| `awardee` | ✅ | string/array | pencapaian beasiswa |
| `rating` | ✅ | numeric | min:0, max:5 |

> ⚠️ **Catatan Inkonsistensi:** Field `rating` ada di validasi Controller API tetapi **tidak ada di Model** `$fillable`. Ini perlu diperbaiki sebelum digunakan di produksi.

**Contoh Request Body:**
```json
{
  "nama_mentor": "Budi Santoso",
  "pendidikan": "S2 Teknik Informatika, Universitas Indonesia",
  "awardee": "LPDP 2022",
  "rating": 4.8
}
```

---

#### `GET /api/mentor/{id}`
#### `PUT /api/mentor/{id}`
#### `DELETE /api/mentor/{id}`

Pola yang sama dengan Paket Beasiswa. Lihat dokumentasi di atas.

---

### 📰 3. Artikel

#### `GET /api/artikel`

**Tujuan:** Mengambil semua artikel yang tersedia.

**Contoh Respons (200 OK):**
```json
{
  "success": true,
  "message": "Daftar data Artikel",
  "data": [
    {
      "_id": "6621f4b2e3a4c90001234def",
      "judul_artikel": "5 Tips Lolos Seleksi Beasiswa Unggulan 2026",
      "url": "https://blog.nalarin.id/tips-beasiswa-unggulan",
      "thumbnail": "https://storage.nalarin.id/images/artikel-1.jpg",
      "created_at": "2026-04-20T10:00:00.000Z",
      "updated_at": "2026-04-20T10:00:00.000Z"
    }
  ]
}
```

---

#### `POST /api/artikel`

**Aturan Validasi:**

| Field | Wajib | Tipe | Aturan |
|-------|-------|------|--------|
| `judul_artikel` | ✅ | string | max:255 |
| `url` | ✅ | string | format URL valid |
| `thumbnail` | ✅ | string | format URL valid |

**Contoh Request Body:**
```json
{
  "judul_artikel": "5 Tips Lolos Seleksi Beasiswa Unggulan 2026",
  "url": "https://blog.nalarin.id/tips-beasiswa-unggulan",
  "thumbnail": "https://storage.nalarin.id/images/artikel-1.jpg"
}
```

---

### 📊 4. Dashboard Chart (Admin)

> ℹ️ Endpoint ini khusus untuk **dashboard admin web**, bukan untuk aplikasi Flutter pengguna.

#### `GET /api/dashboard/charts/mentor-vs-peserta`

**Tujuan:** Mendapatkan perbandingan jumlah mentor vs peserta untuk grafik donut.

**Contoh Respons:**
```json
{
  "labels": ["Mentor", "Peserta"],
  "data": [30, 15],
  "backgroundColor": ["#02BBE5", "#FFD362"]
}
```

---

#### `GET /api/dashboard/charts/top-beasiswa?limit=3`

**Query Parameter:**
- `limit` (opsional, default: 3) — Jumlah item teratas yang ditampilkan

**Contoh Respons:**
```json
{
  "labels": ["Beasiswa Unggulan", "Tanoto Scholarship", "BSI Scholarship Unggulan"],
  "data": [17, 13, 10],
  "backgroundColor": "#02BBE5"
}
```

---

#### `GET /api/dashboard/charts/total-penjualan?filter=2026`

**Query Parameter:**
- `filter` — `2026` (default), `2025`, `q1`

**Contoh Respons:**
```json
{
  "labels": ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
  "data": [32, 45, 60, 48, 70, 65, 80, 55, 62, 85, 90, 75],
  "borderColor": "#8979FF",
  "backgroundColor": "rgba(137, 121, 255, 0.2)"
}
```

---

#### `GET /api/dashboard/charts/total-pendapatan?filter=yearly`

**Query Parameter:**
- `filter` — `yearly` (default), `monthly`

**Contoh Respons (yearly):**
```json
{
  "labels": ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
  "datasets": [
    {
      "label": "Tahun 2026",
      "data": [200000, 350000, 420000, 380000, 500000, 450000, 600000, 490000, 520000, 680000, 720000, 650000],
      "borderColor": "#8979FF",
      "backgroundColor": "rgba(137, 121, 255, 0.4)"
    },
    {
      "label": "Tahun 2025",
      "data": [150000, 280000, 320000, 300000, 400000, 380000, 500000, 420000, 480000, 560000, 600000, 550000],
      "borderColor": "#02BBE5",
      "backgroundColor": "rgba(2, 187, 229, 0.4)"
    }
  ]
}
```

---

### 🔮 Template Endpoint Masa Depan

> Gunakan template ini saat menambahkan endpoint baru.

```
### [METHOD] /api/{resource}

**Tujuan:** [Jelaskan apa yang dilakukan endpoint ini]

**Request Body (JSON):**
```json
{
  "field": "value"
}
```

**Aturan Validasi:**
| Field | Wajib | Tipe | Aturan |
| ...   | ...   | ...  | ...    |

**Contoh Respons (2XX):**
```json
{
  "success": true,
  "message": "...",
  "data": { ... }
}
```

**Kemungkinan Error:**
| Status | Kondisi |
| ...    | ...     |
```

---

## 📱 E. Integrasi dengan Flutter (PENTING)

### Cara Flutter Mengonsumsi API Ini

Berikut pola yang direkomendasikan berdasarkan arsitektur yang sudah didokumentasikan di mobile app:

#### Langkah 1: Konfigurasi Base URL

```dart
// lib/core/constants/api_constants.dart
class ApiConstants {
  // Development (lokal via Laragon)
  static const String baseUrl = 'http://10.0.2.2:8000/api'; // Android Emulator
  // static const String baseUrl = 'http://localhost:8000/api'; // iOS Simulator

  // Production
  // static const String baseUrl = 'https://api.nalarin.id/api';

  // Endpoints
  static const String paketBeasiswa = '$baseUrl/paket-beasiswa';
  static const String mentor        = '$baseUrl/mentor';
  static const String artikel       = '$baseUrl/artikel';
}
```

> ⚠️ **Perhatian untuk Android:** Emulator Android tidak bisa mengakses `localhost`. Gunakan `10.0.2.2` sebagai pengganti `localhost` saat development.

#### Langkah 2: Model Dart untuk Paket Beasiswa

```dart
// lib/features/paket/models/paket_beasiswa_model.dart
class PaketBeasiswa {
  final String id;
  final String namaBeasiswa;
  final List<String> faseCheckpoint;
  final List<String> persyaratan;
  final List<String> benefit;
  final String? url;
  final String? gambar;
  final String deadline;
  final int harga;

  PaketBeasiswa({
    required this.id,
    required this.namaBeasiswa,
    required this.faseCheckpoint,
    required this.persyaratan,
    required this.benefit,
    this.url,
    this.gambar,
    required this.deadline,
    required this.harga,
  });

  factory PaketBeasiswa.fromJson(Map<String, dynamic> json) {
    return PaketBeasiswa(
      // MongoDB menyimpan ID sebagai "_id", BUKAN "id"
      id: json['_id'] as String,
      namaBeasiswa:   json['nama_beasiswa'] as String,
      faseCheckpoint: List<String>.from(json['fase_checkpoint'] ?? []),
      persyaratan:    List<String>.from(json['persyaratan'] ?? []),
      benefit:        List<String>.from(json['benefit'] ?? []),
      url:            json['url'] as String?,
      gambar:         json['gambar'] as String?,
      deadline:       json['deadline'] as String,
      harga:          json['harga'] as int,
    );
  }
}
```

#### Langkah 3: Model Dart untuk Mentor

```dart
// lib/features/mentor/models/mentor_model.dart
class Mentor {
  final String id;
  final String namaMentor;
  final String pendidikan;
  final List<String> awardee;

  Mentor({
    required this.id,
    required this.namaMentor,
    required this.pendidikan,
    required this.awardee,
  });

  factory Mentor.fromJson(Map<String, dynamic> json) {
    return Mentor(
      id:          json['_id'] as String,
      namaMentor:  json['nama_mentor'] as String,
      pendidikan:  json['pendidikan'] as String,
      // awardee bisa berupa String atau List, handle keduanya:
      awardee: json['awardee'] is List
          ? List<String>.from(json['awardee'])
          : [json['awardee'] as String],
    );
  }
}
```

#### Langkah 4: Model Dart untuk Artikel

```dart
// lib/features/artikel/models/artikel_model.dart
class Artikel {
  final String id;
  final String judulArtikel;
  final String url;
  final String thumbnail;

  Artikel({
    required this.id,
    required this.judulArtikel,
    required this.url,
    required this.thumbnail,
  });

  factory Artikel.fromJson(Map<String, dynamic> json) {
    return Artikel(
      id:            json['_id'] as String,
      judulArtikel:  json['judul_artikel'] as String,
      url:           json['url'] as String,
      thumbnail:     json['thumbnail'] as String,
    );
  }
}
```

#### Langkah 5: Service Layer

```dart
// lib/features/paket/services/paket_beasiswa_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import '../../../core/constants/api_constants.dart';
import '../models/paket_beasiswa_model.dart';

class PaketBeasiswaService {
  Future<List<PaketBeasiswa>> getAll() async {
    final response = await http.get(
      Uri.parse(ApiConstants.paketBeasiswa),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      final List data = body['data'] as List;
      return data.map((json) => PaketBeasiswa.fromJson(json)).toList();
    } else {
      throw Exception('Gagal memuat paket beasiswa: ${response.statusCode}');
    }
  }

  Future<PaketBeasiswa> getById(String id) async {
    final response = await http.get(
      Uri.parse('${ApiConstants.paketBeasiswa}/$id'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return PaketBeasiswa.fromJson(body['data']);
    } else if (response.statusCode == 404) {
      throw Exception('Paket beasiswa tidak ditemukan');
    } else {
      throw Exception('Terjadi kesalahan server');
    }
  }
}
```

#### Langkah 6: Konsumsi di Widget dengan FutureBuilder

```dart
// Di dalam Widget Flutter
FutureBuilder<List<PaketBeasiswa>>(
  future: PaketBeasiswaService().getAll(),
  builder: (context, snapshot) {
    // Loading state
    if (snapshot.connectionState == ConnectionState.waiting) {
      return const Center(child: CircularProgressIndicator());
    }

    // Error state
    if (snapshot.hasError) {
      return Center(
        child: Column(
          children: [
            const Icon(Icons.error_outline, color: Colors.red),
            Text('Gagal memuat data:\n${snapshot.error}'),
            ElevatedButton(
              onPressed: () => setState(() {}), // Retry
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      );
    }

    // Success state
    final paketList = snapshot.data!;
    return ListView.builder(
      itemCount: paketList.length,
      itemBuilder: (_, i) => PaketCard(paket: paketList[i]),
    );
  },
)
```

### Format Data yang Diharapkan Flutter

| Field API | Key JSON | Tipe Dart | Catatan |
|-----------|----------|-----------|---------|
| `_id` | `_id` | `String` | ⚠️ Bukan `id`! Ini MongoDB ObjectId |
| `nama_beasiswa` | `nama_beasiswa` | `String` | snake_case |
| `fase_checkpoint` | `fase_checkpoint` | `List<String>` | Selalu array |
| `persyaratan` | `persyaratan` | `List<String>` | Selalu array |
| `harga` | `harga` | `int` | Dalam Rupiah, tanpa desimal |
| `deadline` | `deadline` | `String` | ISO 8601 format |

### ⚠️ Jebakan Umum Saat Integrasi

1. **Menggunakan `id` bukan `_id`**
   - ❌ `json['id']` → akan `null`
   - ✅ `json['_id']` → ID MongoDB yang benar

2. **Tidak menangani `null` pada field opsional**
   - `gambar`, `url`, `benefit` bisa `null`
   - Gunakan `json['gambar'] as String?` dan null-safety di Dart

3. **Menggunakan `localhost` di Android Emulator**
   - ❌ `http://localhost:8000` → tidak bisa terkoneksi
   - ✅ `http://10.0.2.2:8000` → IP host untuk Android Emulator

4. **Tidak menyertakan header `Accept: application/json`**
   - Tanpa header ini, Laravel mungkin mengembalikan HTML error page, bukan JSON

5. **Salah parse tipe data `harga`**
   - Field `harga` adalah `int` (Rupiah bulat), bukan `double`
   - ❌ `json['harga'] as double` → runtime error
   - ✅ `json['harga'] as int`

6. **`fase_checkpoint` dan `persyaratan` adalah Array, bukan String**
   - Gunakan `List<String>.from(json['fase_checkpoint'] ?? [])` selalu

7. **CORS Error saat testing di web/emulator**
   - Pastikan backend berjalan dan URL sudah benar
   - Jika menggunakan device fisik, gunakan IP lokal: `http://192.168.x.x:8000`

### Strategi Versioning API

Saat ini API belum menggunakan versi. Versioning direkomendasikan saat API sudah stabil dan mulai digunakan oleh pengguna nyata.

**Rencana versioning:**
```
/api/v1/paket-beasiswa   ← versi yang sudah stabil
/api/v2/paket-beasiswa   ← versi baru dengan breaking changes
```

**Aturan versioning:**
- Jangan pernah mengubah field yang sudah ada (breaking change)
- Field baru boleh ditambahkan di versi yang sama (backward compatible)
- Gunakan versi baru HANYA jika ada perubahan yang merusak kompatibilitas

---

## 🗄️ F. Struktur Database

### Database: MongoDB — `backendPJBL`

Backend menggunakan **MongoDB** (NoSQL) karena beberapa alasan:
1. Data seperti `fase_checkpoint` dan `persyaratan` bersifat dinamis — jumlah itemnya tidak tetap
2. Tidak perlu mendefinisikan schema ketat — cocok untuk prototyping cepat
3. Embedding dokumen (menyimpan array di dalam dokumen yang sama) lebih efisien untuk data yang selalu dibaca bersama

### Koleksi: `paket_beasiswas`

| Field | Tipe MongoDB | Keterangan |
|-------|-------------|------------|
| `_id` | ObjectId | Identifier unik (auto-generated) |
| `nama_beasiswa` | String | Nama paket beasiswa |
| `fase_checkpoint` | Array of String | Daftar fase/tahapan yang harus dilalui |
| `persyaratan` | Array of String | Daftar syarat pendaftaran |
| `benefit` | Array of String | Keuntungan yang didapatkan |
| `url` | String | Link website resmi beasiswa |
| `gambar` | String | URL thumbnail/gambar paket |
| `deadline` | Date | Batas waktu pendaftaran |
| `harga` | Number (Int) | Harga paket dalam Rupiah |
| `created_at` | Date | Waktu data dibuat (auto) |
| `updated_at` | Date | Waktu data diupdate (auto) |

**Index yang ada:**
- `nama_beasiswa` (untuk pencarian cepat berdasarkan nama)
- `deadline` (untuk sorting berdasarkan tanggal)

**Contoh dokumen:**
```json
{
  "_id": ObjectId("6621f4b2e3a4c90001234567"),
  "nama_beasiswa": "Beasiswa Unggulan Kemendikbud",
  "fase_checkpoint": ["Fase 1: Persiapan Dokumen", "Fase 2: Penulisan Esai"],
  "persyaratan": ["IPK 3.5", "Rekomendasi dosen"],
  "benefit": ["Bebas SPP", "Tunjangan buku"],
  "url": "https://beasiswaunggulan.kemdikbud.go.id",
  "gambar": "https://storage.nalarin.id/bu.jpg",
  "deadline": ISODate("2026-06-03"),
  "harga": 49000,
  "created_at": ISODate("2026-04-16T09:00:00Z"),
  "updated_at": ISODate("2026-04-16T09:00:00Z")
}
```

---

### Koleksi: `mentors`

| Field | Tipe MongoDB | Keterangan |
|-------|-------------|------------|
| `_id` | ObjectId | Identifier unik |
| `nama_mentor` | String | Nama lengkap mentor |
| `pendidikan` | String | Jenjang dan universitas |
| `awardee` | Array of String | Daftar beasiswa yang pernah diperoleh |
| `created_at` | Date | Auto |
| `updated_at` | Date | Auto |

> ⚠️ **Catatan:** Validasi API menyertakan `rating`, namun `rating` **tidak ada** di `$fillable` model. Harus ditambahkan jika ingin digunakan.

---

### Koleksi: `artikels`

| Field | Tipe MongoDB | Keterangan |
|-------|-------------|------------|
| `_id` | ObjectId | Identifier unik |
| `judul_artikel` | String | Judul artikel |
| `url` | String | Link artikel lengkap |
| `thumbnail` | String | URL gambar preview artikel |
| `created_at` | Date | Auto |
| `updated_at` | Date | Auto |

---

### Desain Database — Mengapa Begini?

**Mengapa `fase_checkpoint` disimpan sebagai Array di dalam dokumen yang sama (embedded)?**

Alternatifnya adalah membuat koleksi terpisah `fases` dengan relasi. Namun, karena fase selalu dibaca bersama dengan paket beasiswanya (tidak pernah dibaca sendiri), embedding lebih efisien — satu query cukup untuk mendapatkan semua data yang dibutuhkan.

**Mengapa artikel hanya menyimpan URL, bukan konten penuh?**

Ini pola yang disebut **"headless linking"** — aplikasi hanya menyimpan referensi ke artikel eksternal (website/blog). Konten artikel dikelola di platform lain. Ini mengurangi duplikasi data dan beban storage.

---

## ⚙️ G. Panduan Setup

### Prasyarat

Pastikan perangkat Anda memiliki:

| Software | Versi Minimum | Cek dengan |
|----------|---------------|------------|
| PHP | 8.3 | `php -v` |
| Composer | 2.x | `composer -V` |
| MongoDB | 7.0 | `mongod --version` |
| Git | latest | `git --version` |
| Node.js | 18+ | `node -v` |
| Laragon (Windows) | 6.0+ | (opsional, untuk local dev) |

### Instalasi

#### Langkah 1: Clone Repositori
```bash
git clone https://github.com/Arfzz/backendNextgen.git
cd backendNextgen
```

#### Langkah 2: Install Dependencies PHP
```bash
composer install
```

#### Langkah 3: Buat File Environment
```bash
cp .env.example .env
php artisan key:generate
```

#### Langkah 4: Konfigurasi `.env`

Edit file `.env` dan sesuaikan:

```env
APP_NAME=NalarinBackend
APP_ENV=local
APP_KEY=base64:...  # Sudah di-generate di langkah sebelumnya
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database MongoDB
DB_CONNECTION=pjblNextgen
MONGODB_URI=mongodb://localhost:27017
MONGODB_DATABASE=backendPJBL
```

> ⚠️ **Penting:** Pastikan MongoDB berjalan di port 27017 sebelum melanjutkan.

#### Langkah 5: Jalankan Migrasi
```bash
php artisan migrate
```

Ini akan membuat koleksi-koleksi MongoDB yang dibutuhkan beserta indeksnya.

#### Langkah 6: Install Dependencies Node.js (untuk Admin Dashboard)
```bash
npm install
```

#### Langkah 7: Jalankan Server Development

**Opsi A: Semua sekaligus (recommended)**
```bash
composer run dev
```
Ini menjalankan: Laravel server + Queue worker + Log viewer + Vite dev server

**Opsi B: Server saja**
```bash
php artisan serve
```
API akan tersedia di: `http://localhost:8000/api`

### Verifikasi Instalasi

Buka browser atau gunakan tool API (Postman/Insomnia):
```
GET http://localhost:8000/api/paket-beasiswa
```

Jika berhasil, Anda akan mendapat respons JSON (mungkin dengan `data: []` jika database masih kosong).

---

## 🛠️ H. Panduan Pengembangan

### Cara Menambahkan Endpoint Baru

Ikuti langkah berikut saat menambahkan resource baru (contoh: `Penugasan`):

#### Langkah 1: Buat Model
```bash
php artisan make:model Penugasan
```

Edit `app/Models/Penugasan.php`:
```php
<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Penugasan extends Model
{
    protected $connection = 'pjblNextgen';
    protected $collection = 'penugasans';

    protected $fillable = [
        'judul_tugas',
        'deskripsi',
        'deadline',
        'paket_beasiswa_id',
        'status',
    ];

    protected $casts = [
        'deadline' => 'date',
    ];
}
```

#### Langkah 2: Buat Migrasi (jika perlu index)
```bash
php artisan make:migration create_penugasans_collection
```

#### Langkah 3: Buat API Controller
```bash
php artisan make:controller Api/PenugasanController --api
```

Edit controller di `app/Http/Controllers/Api/PenugasanController.php`:
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penugasan;
use Illuminate\Http\Request;

class PenugasanController extends Controller
{
    public function index()
    {
        $penugasans = Penugasan::all();
        return response()->json([
            'success' => true,
            'message' => 'Daftar data Penugasan',
            'data'    => $penugasans
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_tugas' => 'required|string|max:255',
            'deskripsi'   => 'required|string',
            'deadline'    => 'required|date',
        ]);

        $penugasan = Penugasan::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Penugasan berhasil disimpan',
            'data'    => $penugasan
        ], 201);
    }

    // ... show(), update(), destroy()
}
```

#### Langkah 4: Register Route di `routes/api.php`
```php
Route::apiResource('penugasan', \App\Http\Controllers\Api\PenugasanController::class)
     ->names('api.penugasan');
```

#### Langkah 5: Tambahkan Dokumentasi

Perbarui file ini (`DOKUMENTASI_BACKEND.md`) dengan endpoint baru sesuai template di [Seksi D](#-template-endpoint-masa-depan).

---

### Di Mana Harus Meletakkan Logic?

| Jenis Logic | Letakkan di | Alasan |
|-------------|-------------|--------|
| Validasi input | Controller (`$request->validate()`) | Dekat dengan request handling |
| Query database sederhana | Controller (langsung di method) | Tidak perlu layer tambahan untuk saat ini |
| Query kompleks / reusable | Model (scope) atau Service class | Agar mudah di-reuse |
| Business rules kompleks | Service Class (`app/Services/`) | Pisahkan dari HTTP layer |
| Transformasi response | Controller atau API Resource | Konsisten per resource |

**Kapan buat Service Class?**
Buat `app/Services/PaketBeasiswaService.php` jika:
- Logic yang sama dipakai di lebih dari 1 controller
- Logic melibatkan lebih dari 1 model (multi-collection query)
- Logic butuh unit testing terpisah

### Aturan Validasi

Selalu gunakan `$request->validate()` di Controller. Jangan pernah menyimpan data tanpa validasi.

```php
// ✅ BENAR
$validated = $request->validate([
    'nama_beasiswa' => 'required|string|max:255',
    'harga'         => 'required|integer|min:0',
]);
$paket = PaketBeasiswa::create($validated);

// ❌ SALAH — Berbahaya! Menerima semua input tanpa filter
$paket = PaketBeasiswa::create($request->all());
```

### Best Practices

1. **Selalu gunakan `$validated` untuk `create()`** — Jangan gunakan `$request->all()`
2. **Konsisten format respons** — Selalu ada `success`, `message`, `data`
3. **Periksa null sebelum update/delete** — Return 404 jika data tidak ada
4. **Gunakan type casting di Model** — `'harga' => 'integer'` mencegah bug tipe data
5. **HTTP Status Code semantik** — 201 untuk create, 200 untuk lainnya, 404 untuk not found
6. **Jangan commit `.env`** — File `.env` sudah ada di `.gitignore`

---

## 🤝 I. Panduan Kontribusi

### Git Workflow

```
main
  └── develop
        ├── feature/tambah-endpoint-penugasan
        ├── feature/implementasi-auth-sanctum
        ├── fix/bug-rating-mentor-tidak-tersimpan
        └── docs/update-dokumentasi-api
```

**Aturan branch:**
- `main` — Hanya kode yang sudah production-ready
- `develop` — Integrasi fitur-fitur yang sudah selesai
- `feature/{nama}` — Pengembangan fitur baru
- `fix/{nama}` — Perbaikan bug
- `docs/{nama}` — Update dokumentasi saja

### Commit Convention

Gunakan format: `type(scope): deskripsi singkat`

```bash
# Contoh commit yang baik:
git commit -m "feat(paket-beasiswa): tambah validasi field benefit"
git commit -m "fix(mentor): perbaiki bug rating tidak masuk ke database"
git commit -m "docs: update dokumentasi endpoint artikel"
git commit -m "refactor(api): pisahkan response format ke helper function"
```

| Type | Kapan Digunakan |
|------|-----------------|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `docs` | Perubahan dokumentasi |
| `refactor` | Refactoring tanpa perubahan fungsional |
| `test` | Menambah/mengubah test |
| `chore` | Update dependencies, config |

### Cara Menghindari Breaking API Contract

1. **JANGAN pernah mengubah nama field yang sudah ada di respons**
   - ❌ Ubah `nama_beasiswa` → `name` (merusak semua Flutter yang sudah pakai)
   - ✅ Tambah field baru `name` sambil tetap mempertahankan `nama_beasiswa`

2. **JANGAN mengubah tipe data field yang sudah ada**
   - ❌ Ubah `harga` dari `int` ke `string`

3. **BOLEH menambah field baru** (backward compatible)

4. **Selalu test endpoint sebelum push** menggunakan Postman atau `curl`

5. **Update dokumentasi ini** setiap kali ada perubahan pada API

### Code Standards

- Gunakan **PSR-12** untuk formatting PHP
- Gunakan **Laravel Pint** untuk auto-format: `./vendor/bin/pint`
- Nama method: `camelCase` (sudah standar PHP/Laravel)
- Nama variabel: `camelCase` (`$paketBeasiswa`, bukan `$paket_beasiswa`)
- Komentar dalam **Bahasa Indonesia** untuk readability tim

### Pull Request Checklist

Sebelum membuat PR, pastikan:

- [ ] Semua endpoint baru sudah didokumentasikan di `DOKUMENTASI_BACKEND.md`
- [ ] Validasi sudah ditambahkan di semua endpoint yang menerima input
- [ ] Format respons konsisten (`success`, `message`, `data`)
- [ ] HTTP status code sudah benar
- [ ] Sudah test manual menggunakan Postman
- [ ] Tidak ada `dd()`, `var_dump()`, atau `print_r()` yang tertinggal
- [ ] File `.env` tidak ikut ter-commit

---

## 💬 J. Penjelasan Non-Teknis

### Apa yang Dilakukan Backend Ini?

Bayangkan Nalarin adalah sebuah **toko buku online** untuk beasiswa.

**Backend adalah gudang + kasir toko tersebut:**

- 📦 **Gudang Data:** Backend menyimpan semua informasi — daftar paket beasiswa apa saja yang tersedia, siapa saja mentor yang bergabung, artikel apa yang pernah ditulis.

- 💁 **Pelayan Informasi:** Ketika pengguna membuka aplikasi Nalarin di HP-nya dan melihat daftar beasiswa, HP mereka sebenarnya sedang "bertanya" ke backend: *"Hei, tolong kirimkan daftar paket beasiswa yang ada!"* Lalu backend menjawab dengan mengirimkan datanya.

- ✅ **Penjaga Kualitas Data:** Jika admin memasukkan data paket beasiswa baru, backend memastikan data itu benar — nama tidak boleh kosong, harga harus angka, deadline harus berformat tanggal. Data yang "rusak" akan ditolak sebelum masuk ke database.

### Nilai Bisnis yang Dihasilkan Backend

| Fitur Backend | Dampak Bisnis |
|---------------|---------------|
| API Paket Beasiswa | Pengguna bisa melihat dan memilih paket beasiswa dari HP mereka kapan pun |
| API Mentor | Sistem bisa menampilkan profil mentor yang dapat dipilih pengguna |
| API Artikel | Konten edukatif bisa diperbarui admin tanpa update aplikasi |
| Dashboard Charts | Tim bisnis bisa melihat performa penjualan dan tren mentor vs peserta |

### Status Saat Ini

Backend saat ini sudah berfungsi untuk:
- ✅ Mengelola data paket beasiswa (tambah, lihat, ubah, hapus)
- ✅ Mengelola data mentor
- ✅ Mengelola data artikel
- ✅ Menyediakan data untuk grafik dashboard admin

Yang masih dalam pengembangan:
- 🚧 Fitur login/autentikasi pengguna
- 🚧 Manajemen peserta dan penugasan
- 🚧 Fitur chat antara peserta dan mentor
- 🚧 Notifikasi

---

## 🎁 BONUS: Template Standar Respons API

### ✅ Template Respons Sukses (Kumpulan Data)

```json
{
  "success": true,
  "message": "Daftar data [Nama Resource]",
  "data": [
    {
      "_id": "string (MongoDB ObjectId)",
      "..." : "..."
    }
  ]
}
```

### ✅ Template Respons Sukses (Satu Data)

```json
{
  "success": true,
  "message": "Detail data [Nama Resource]",
  "data": {
    "_id": "string (MongoDB ObjectId)",
    "..." : "..."
  }
}
```

### ✅ Template Respons Sukses (Buat Data)

```json
{
  "success": true,
  "message": "Data [Nama Resource] berhasil disimpan",
  "data": {
    "_id": "string (MongoDB ObjectId, baru dibuat)",
    "..." : "..."
  }
}
```

### ✅ Template Respons Sukses (Hapus Data)

```json
{
  "success": true,
  "message": "Data [Nama Resource] berhasil dihapus"
}
```

### ❌ Template Respons Error — Data Tidak Ditemukan (404)

```json
{
  "success": false,
  "message": "Data tidak ditemukan"
}
```

### ❌ Template Respons Error — Validasi Gagal (422)

```json
{
  "message": "The [field name] field is required.",
  "errors": {
    "nama_beasiswa": [
      "The nama beasiswa field is required."
    ],
    "harga": [
      "The harga field must be an integer."
    ]
  }
}
```

> ⚠️ Perhatikan: Error validasi dari Laravel tidak membungkus dalam `success: false` secara default. Untuk konsistensi, pertimbangkan menambahkan custom exception handler di masa depan.

### ❌ Template Respons Error — Server Error (500)

```json
{
  "success": false,
  "message": "Terjadi kesalahan internal server. Silakan coba lagi."
}
```

---

## 🔮 Future Improvements

### Prioritas Tinggi (Segera)

1. **Implementasi Autentikasi** — Laravel Sanctum sudah terpasang, tinggal dikonfigurasi
   - Endpoint: `POST /api/auth/login`, `POST /api/auth/register`, `POST /api/auth/logout`
   - Proteksi semua endpoint mutation (POST, PUT, DELETE) dengan `auth:sanctum`

2. **Konsistensi Format Error Validasi** — Wrap error validasi Laravel dalam format standar `{success: false, message, errors}`

3. **Tambahkan Field `rating` ke Model Mentor** — Saat ini ada di validasi tapi tidak di `$fillable`

4. **Ganti Data Dummy di DashboardChartController** — Subscribe ke data real dari MongoDB

5. **API Resource / Transformer** — Gunakan `php artisan make:resource` untuk memformat respons secara konsisten dan mencegah field sensitif (seperti `__v` MongoDB) bocor ke client

### Prioritas Menengah

6. **Pagination** — Tambahkan pagination untuk semua endpoint list:
   ```php
   $pakets = PaketBeasiswa::paginate(10); // 10 item per halaman
   ```
   Format respons dengan pagination:
   ```json
   {
     "success": true,
     "data": [...],
     "meta": {
       "current_page": 1,
       "last_page": 5,
       "per_page": 10,
       "total": 48
     }
   }
   ```

7. **Logging & Monitoring** — Implementasi structured logging untuk setiap request API

8. **Rate Limiting** — Batasi jumlah request per menit untuk mencegah abuse

9. **Middleware Kustom** — Logging JWT, request tracking

### Prioritas Rendah (Masa Depan)

10. **Caching dengan Redis** — Cache respons GET dengan TTL untuk mengurangi load ke MongoDB

11. **API Versioning** — Tambahkan `/api/v1/` prefix saat API mulai stabil

12. **Unit & Feature Tests** — Tulis test untuk semua endpoint menggunakan PHPUnit

13. **CI/CD Pipeline** — GitHub Actions untuk auto-test dan deploy

14. **Fitur Baru:** Penugasan, Peserta, Pembayaran, Notifikasi, Chat

---

## ⚠️ Kesalahan Umum Developer

### Kesalahan Developer Flutter

| # | Kesalahan | Akibat | Solusi |
|---|-----------|--------|--------|
| 1 | Menggunakan `json['id']` bukan `json['_id']` | Field selalu `null` | Gunakan `json['_id']` |
| 2 | Tidak menangani `null` untuk field opsional | App crash `Null check operator` | Gunakan `json['field'] as String?` |
| 3 | `localhost` di Android Emulator | `SocketException` | Ganti dengan `10.0.2.2` |
| 4 | Tidak kirim `Accept: application/json` | Menerima HTML error page | Tambahkan di setiap request header |
| 5 | Parse `harga` sebagai `double` | `TypeError` | Gunakan `as int` |
| 6 | Asumsikan field array tidak pernah kosong | `RangeError` / crash | Selalu gunakan `List.from(json['field'] ?? [])` |
| 7 | Tidak handle loading state | UI freeze / tampilan kacau | Selalu implementasi 3 state: loading, error, success |

### Kesalahan Developer Backend

| # | Kesalahan | Akibat | Solusi |
|---|-----------|--------|--------|
| 1 | `PaketBeasiswa::create($request->all())` tanpa validasi | Data rusak masuk database | Selalu `$request->validate()` dulu |
| 2 | Tidak return 404 saat data tidak ada | Client bingung dengan error tidak jelas | Selalu cek `if (!$model) return 404` |
| 3 | Lupa update dokumentasi setelah ubah API | Flutter developer salah mengintegrasikan | Update `DOKUMENTASI_BACKEND.md` di PR yang sama |
| 4 | Commit file `.env` | Credential bocor ke publik | Selalu cek `.gitignore` sebelum commit |
| 5 | Ubah nama field yang sudah dipakai Flutter | Breaking change, app Flutter error | Diskusikan dengan tim Flutter sebelum ubah |
| 6 | Tidak konsisten format respons | Flutter perlu banyak exception handling | Ikuti template respons standar di atas |
| 7 | Menaruh semua logic di Controller | Susah di-test dan di-maintain | Pisahkan ke Service class jika logic kompleks |

---

*Dokumentasi ini adalah dokumen hidup (living document). Selalu perbarui saat ada perubahan pada API.*

**Maintainer:** Tim Backend Nalarin
**Hubungi:** Buka issue di repository GitHub untuk pertanyaan teknis.
