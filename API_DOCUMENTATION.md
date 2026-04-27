# Nalarin Mobile – Backend API Documentation

This document provides complete details of the Nalarin backend REST API constructed using **Laravel 13** and **MongoDB**. This API utilizes **Laravel Sanctum Bearer Tokens** for stateless authentication and features role-based access control (Student, Mentor, Admin).

## 🌍 Base URL
- **Local Emulator:** `http://10.0.2.2:8000/api/v1`
- **Local Browser/Postman:** `http://localhost:8000/api/v1`
- **Global Headers (Required for all requests):**
  - `Accept: application/json`
  - `Content-Type: application/json` *(except for file uploads which require `multipart/form-data`)*
  - `Authorization: Bearer <token>` *(except for Login & Register endpoints)*

---

## 🔒 1. Authentication (No Auth Required)

### `POST /auth/register`
Mendaftarkan student baru.
- **Payload Request:**
  ```json
  {
    "name": "Budi Santoso",
    "email": "budi@student.com",
    "password": "password123",
    "password_confirmation": "password123",
    "university": "Universitas Indonesia"
  }
  ```
- **Response (201 Created):**
  Mengembalikan token Bearer dan data User.

### `POST /auth/login`
Autentikasi user dan mengambil token akses.
- **Payload Request:**
  ```json
  {
    "email": "student1@nalarin.id",
    "password": "password"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "message": "Login successful.",
    "token": "1|abcdef123456789...",
    "user": {
      "id": "60a1b2...",
      "name": "Andi Pratama",
      "email": "student1@nalarin.id",
      "role": "student",
      "university": "Universitas Gadjah Mada",
      "profile_picture": null
    }
  }
  ```

---

## 👤 2. Current User (Requires Auth)

### `GET /auth/me`
Mengambil detail profil user yang sedang login saat ini berdasarkan token.

### `POST /auth/logout`
Menghapus (revoke) token akses saat ini dari database.

---

## 📅 3. Calendar Events (Requires Auth)

### `GET /calendar`
Mengambil seluruh agenda (Tugas, Checkpoint, dan Sesi Mentoring) untuk divisualisasikan dalam kalender bulanan. Endpoint ini berlaku untuk Role: **Student** maupun **Mentor**.
- **Query Params:** `?month=4&year=2026` (opsional, default: bulan saat ini)
- **Response Structure (200 OK):**
  ```json
  {
    "events": [
      {
        "type": "task",
        "id": "60c1d2...",
        "title": "Draft Esai Pribadi",
        "date": "2026-04-15"
      },
      {
        "type": "mentoring",
        "id": "60c3d4...",
        "title": "Review 1-on-1",
        "date": "2026-04-20",
        "link": "https://meet.google.com/xyz"
      }
    ]
  }
  ```

---

## 🎓 4. API Khusus Student (Tujuan: `role:student`)
Semua endpoint di bawah ini melempar error `403 Forbidden` jika diakses oleh mentor.

### `GET /student/home`
Dashboard awal aplikasi student (Overview).
- **Response Body:**
  Menghasilkan *aggregate data* berupa `user`, `global_progress` (integer %), `upcoming_activities` (list task dan mentoring terdekat), `articles` (berita beasiswa), dan list `packages`.

### `GET /student/my-class-dashboard`
Dashboard spesifik untuk kelas yang sedang aktif diikuti student.
- **Response Body:**
  ```json
  {
     "membership": {
        "progress_percentage": 50,
        "fase_passed": 1,
        "status": "ongoing",
        "joined_at": "..."
     },
     "package_info": { "title": "Beasiswa Unggulan" },
     "checkpoints": [ { "title": "Seleksi 1", "is_completed": true } ],
     "tasks_summary": { "total": 5, "completed": 2 },
     "tasks": [ 
       { 
         "id": "...", 
         "title": "Esai Diri", 
         "deadline_date": "2026-05-01", 
         "submission": { "status": "submitted", "file_url": "..." } 
       } 
     ],
     "mentoring_sessions": [...],
     "documents": [...]
  }
  ```

### `GET /student/packages`
Menampilkan katalog semua paket mentoring beasiswa.

### `GET /student/packages/{package_id}`
Menampilkan detail spesifik paket scholarship (harga, promo, fitur-fitur).

### `GET /student/tasks/{task_id}`
Menampilkan detail lengkap instruksi sebuah Tugas.

### `POST /student/tasks/{task_id}/submit`
Upload form jawaban/bukti tugas dari student.
- **PENTING - Header:** `Content-Type: multipart/form-data`
- **Form Data Body:**
  - `file`: (File Upload) PDF, DOCX, JPG, Mime Types. Max 5MB.
- **Response (201 Created):**
  Mengembalikan object `submission` berisi status `submitted` dan `file_url` absoulte untuk dirender front end.

---

## 👔 5. API Khusus Mentor (Tujuan: `role:mentor`)
Semua endpoint di bawah ini digunakan untuk *Class Management* pada aplikasi. Error `403 Forbidden` jika diakses oleh student.

### `GET /mentor/dashboard`
Dashboard landing screen mentor.
- **Response Body:**
  Menghasilkan profile mentor, rating score, jumlah murid lulus, tugas/jadwal mengajar terdekat (`upcoming_activities`), serta daftar kelas aktif (`students`) beserta persentase progress mereka.

### `GET /mentor/students/{student_id}/submissions`
Melihat daftar semua tugas serta submission file milik siswa spesifik.

### `POST /mentor/submissions/{submission_id}/grade`
Menyetujui tugas siswa. Digunakan untuk merubah status file submission dari `submitted` ke `graded`.
- **Payload Request:**
  ```json
  {
    "status": "graded"  // Bisa berupa "pending", "submitted", "graded"
  }
  ```

### `GET /mentor/tasks/{task_id}/submissions`
Melihat siapa saja partisipan student yang sudah meng-upload `file` ke suatu `task_id`.

### `POST /mentor/classes/{class_id}/tasks`
Membuat penugasan baru kepada seluruh siswa di kelas tersebut.
- **Payload Request:**
  ```json
  {
     "title": "Upload Scan Ijazah Legalisir",
     "description": "Scan berwarna ukuran A4 maksimal 2MB.",
     "deadline_date": "2026-05-15"
  }
  ```

### `PUT /mentor/tasks/{task_id}`
Memperbarui/mengedit informasi tugas. *(Payload sama seperti POST tasks).*

### `DELETE /mentor/tasks/{task_id}`
Menghapus tugas (termasuk semua submission yang sudah dikirim student otomatis jadi ter-hapus).

### `POST /mentor/classes/{class_id}/mentoring`
Membuat jadwal sesi pertemuan (live Class / 1-on-1) baru.
- **Payload Request:**
  ```json
  {
     "title": "Sesi Latihan Wawancara",
     "session_date": "2026-05-18 19:30:00",
     "link": "https://meet.google.com/xxx-yyy-zzz"
  }
  ```

### `POST /mentor/classes/{class_id}/documents`
Mengunggah file referensi / template ke dalam Modul Kelas.
- **PENTING - Header:** `Content-Type: multipart/form-data`
- **Form Data Body:**
  - `title`: (String) e.g., "Template Proposal LPDP"
  - `file`: (File/Document) Template file.

### `POST /mentor/classes/{class_id}/checkpoints`
Menambahkan titik milestone baru (Tahapan Seleksi/Target Progress) untuk kelas.
- **Payload Request:**
  ```json
  {
     "title": "Pengumuman Tes Bakat Skolastik",
     "schedule_date": "2026-06-01",
     "order_index": 2
  }
  ```

---

## 📌 6. Struktur Data Enumeration
Backend ini memvalidasi strict string enum berikut pada database (MongoDB). Pastikan Frontend mengirimkan string yang *cases* persis sepeti berikut:

1. **UserRole:** `student`, `mentor`, `admin`
2. **SubmissionStatus:** `pending`, `submitted`, `graded`
3. **ClassMemberStatus:** `ongoing`, `graduated`, `dropped`

---
*Generated by AI Documentation Process - Nalarin System Architecture 2026*
