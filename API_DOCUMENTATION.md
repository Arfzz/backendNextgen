# Nalarin Mobile — Backend API Documentation (v2)
> **Untuk AI Agent / Flutter Developer:** Copy seluruh isi dokumen ini sebagai konteks sebelum menulis kode integrasi Flutter.

---

## 🌐 Base URL & Konfigurasi Global

| Environment | Base URL |
|---|---|
| Android Emulator | `http://10.0.2.2:8000/api/v1` |
| Device Fisik (WiFi sama) | `http://<IP_LAPTOP>:8000/api/v1` |
| Browser / Postman | `http://localhost:8000/api/v1` |

### Header Wajib (Semua Request)
```
Accept: application/json
Content-Type: application/json        ← kecuali upload file
Authorization: Bearer <token>         ← kecuali Login & Register
```

### Format Error Standard
```json
{ "message": "Deskripsi error", "errors": { "field": ["Pesan validasi"] } }
```

### Enumerasi (Exact String — case-sensitive)
| Field | Nilai yang Valid |
|---|---|
| `user.role` | `student` \| `mentor` \| `admin` |
| `submission.status` | `pending` \| `submitted` \| `graded` |
| `membership.status` | `ongoing` \| `graduated` \| `dropped` |

---

## 🔑 1. Authentication (Publik — No Token)

### `POST /auth/register`
Mendaftarkan akun student baru.
- **Body:**
  ```json
  { "name": "Budi", "email": "budi@mail.com", "password": "password123", "password_confirmation": "password123", "university": "UI" }
  ```
- **Response 201:** `{ "message": "...", "token": "...", "user": { ... } }`

### `POST /auth/login`
Login dan mendapatkan Bearer Token.
- **Body:** `{ "email": "student1@nalarin.id", "password": "password" }`
- **Response 200:**
  ```json
  {
    "message": "Login successful.",
    "token": "1|abcdef...",
    "user": {
      "id": "...",
      "name": "Andi Pratama",
      "email": "student1@nalarin.id",
      "role": "student",
      "university": "Universitas Gadjah Mada",
      "profile_picture": null
    }
  }
  ```
  > **Flutter Action:** Simpan `token` ke `flutter_secure_storage`. Navigate berdasarkan `user.role`.

---

## 👤 2. Profil User (Requires Auth)

### `GET /auth/me`
Mendapatkan data profil user yang sedang login.
- **Response 200:** Object `user` (sama seperti response login).

### `POST /auth/logout`
Mencabut/menghapus token aktif dari database.
- **Response 200:** `{ "message": "Logged out successfully." }`

---

## 📅 3. Kalender (Requires Auth — Student & Mentor)

### `GET /calendar?month=4&year=2026`
Mengembalikan semua event dalam satu bulan tertentu. Response berupa **direct array** (bukan wrapped object).
- **Query Params:** `month` (1-12), `year` (4 digit) — keduanya opsional.
- **Response 200:**
  ```json
  [
    { "type": "task",      "id": "...", "title": "Berkas Administrasi", "date": "2026-11-13" },
    { "type": "mentoring", "id": "...", "title": "Mentoring #2",        "date": "2026-11-20", "link": "https://meet.google.com/..." },
    { "type": "checkpoint","id": "...", "title": "Seleksi Berkas",      "date": "2026-11-15" }
  ]
  ```
  > **type** digunakan untuk warna dot di kalender: `task`=biru, `mentoring`=kuning, `checkpoint`=merah.

---

## 🔔 4. Notifikasi (Requires Auth — Student & Mentor)

### `GET /notifications`
List semua notifikasi milik user yang sedang login, diurutkan terbaru dahulu.
- **Response 200:**
  ```json
  [
    { "id": "...", "title": "Deadline penugasan tinggal 3 hari!", "time": "2026-03-16T12:01:00Z", "is_read": false, "type": "task_deadline" },
    { "id": "...", "title": "Anda mendapat balasan dari Mentor!",  "time": "2026-03-16T13:12:00Z", "is_read": true,  "type": "new_message" }
  ]
  ```

### `POST /notifications/{id}/read`
Tandai satu notifikasi sebagai sudah dibaca.
- **Response 200:** `{ "message": "Notification marked as read." }`

### `POST /notifications/read-all`
Tandai semua notifikasi user sebagai sudah dibaca.
- **Response 200:** `{ "message": "All notifications marked as read." }`

---

## 💬 5. Private Chat (Requires Auth — Student & Mentor)

### `GET /chat/conversations`
List semua percakapan (inbox) milik user. Untuk halaman **Chat Inbox**.
- **Response 200:**
  ```json
  {
    "conversations": [
      {
        "id": "...",
        "student": { "id": "...", "name": "Andi", "profile_picture": null },
        "mentor":  { "id": "...", "name": "Dr. Budi", "profile_picture": null },
        "last_message": "Baik kak, terima kasih!",
        "last_message_at": "2026-04-30T00:00:00Z"
      }
    ]
  }
  ```

### `GET /chat/conversations/{target_user_id}`
Buka ruang chat dengan user tertentu. Jika belum ada conversation, akan dibuat otomatis. Pesan dari lawan bicara otomatis ditandai `is_read: true`.
- **Response 200:**
  ```json
  {
    "conversation": { "id": "...", "student": { ... }, "mentor": { ... } },
    "messages": [
      { "id": "...", "sender_id": "...", "content": "Selamat pagi!", "is_read": true, "created_at": "..." }
    ]
  }
  ```

### `POST /chat/conversations/{target_user_id}`
Kirim pesan baru.
- **Body:** `{ "content": "Halo kak, saya ingin bertanya..." }`
- **Response 201:** `{ "message": "Pesan terkirim.", "data": { "id": "...", "content": "...", "is_read": false, "created_at": "..." } }`

---

## 🎓 6. Student Endpoints (Requires `role:student`)

### `GET /student/home`
Dashboard home student. **Aggregate besar** untuk halaman `home_page.dart`.
- **Response 200:**
  ```json
  {
    "user": { "id": "...", "name": "Andi Pratama", "role": "student", "profile_picture": null },
    "global_progress": 75,
    "upcoming_activities": [
      { "id": "...", "type": "task",      "title": "Berkas Administrasi", "date": "2026-11-13" },
      { "id": "...", "type": "mentoring", "title": "Mentoring #2",        "date": "2026-11-10" }
    ],
    "articles": [
      { "id": "...", "title": "Tips Lolos Beasiswa", "image_url": null, "content": "...", "published_at": "2026-10-01T10:00:00Z" }
    ],
    "packages": [
      { "id": "...", "title": "BSI Scholarship", "description": "...", "price": 49000, "old_price": 149000, "deadline_date": "2026-06-03", "features": ["12x Mentoring"], "cover_image": null }
    ],
    "mentors": [
      { "id": "...", "name": "Dr. Budi", "rating_score": 4.8, "students_passed": 142, "profile_picture": null }
    ]
  }
  ```

### `GET /student/my-class-dashboard`
Dashboard kelas aktif student. Untuk `dashboard_penugasan_page.dart`.
- **Response 200:**
  ```json
  {
    "membership": { "progress_percentage": 50, "fase_passed": 2, "status": "ongoing" },
    "package_info": { "title": "BSI Scholarship", "deadline_date": "2026-06-03" },
    "checkpoints": [
      { "id": "...", "title": "Seleksi Berkas", "schedule_date": "2026-11-15", "order_index": 1, "is_completed": true }
    ],
    "tasks_summary": { "total": 10, "completed": 5 },
    "tasks": [
      {
        "id": "...", "title": "Upload CV", "description": "Format PDF", "deadline_date": "2026-11-20",
        "submission": { "status": "graded", "file_url": "https://...", "score": 90, "feedback": "Bagus!", "submitted_at": "2026-11-19T08:00:00Z" }
      }
    ],
    "mentoring_sessions": [
      { "id": "...", "title": "Mentoring Perkenalan", "session_date": "2026-11-10T19:00:00Z", "link": "https://zoom.us/j/123" }
    ],
    "documents": [
      { "id": "...", "title": "Modul Essay", "file_url": "https://...", "uploaded_at": "2026-11-01T10:00:00Z" }
    ]
  }
  ```

### `GET /student/packages`
List semua paket beasiswa tersedia. Untuk `paket_screen.dart`.
- **Response 200:** Array of Package objects (schema lihat di bawah).

### `GET /student/packages/{package_id}`
Detail satu paket. Untuk `detail_paket_page.dart`.
- **Response 200 (Package Object):**
  ```json
  {
    "id": "...",
    "title": "BSI Scholarship",
    "description": "Beasiswa inspirasi untuk mahasiswa terpilih.",
    "price": 49000,
    "old_price": 149000,
    "deadline_date": "2026-06-03",
    "features": ["12x Mentoring", "Review CV", "Akses E-Book"],
    "cover_image": null
  }
  ```

### `GET /student/tasks/{task_id}`
Detail tugas tertentu.

### `POST /student/tasks/{task_id}/submit`
Upload file jawaban tugas.
- **Header:** `Content-Type: multipart/form-data`
- **Form Field:** `file` (File PDF/JPG/DOCX, max 5MB)
- **Response 201:** `{ "message": "Task submitted successfully.", "submission": { "id": "...", "file_url": "...", "status": "submitted" } }`

---

## 👔 7. Mentor Endpoints (Requires `role:mentor`)

### `GET /mentor/dashboard`
Dashboard landing mentor. Untuk `mentor_dashboard_screen.dart`.
- **Response 200:**
  ```json
  {
    "mentor_profile": {
      "id": "...", "name": "Dr. Budi Santoso", "profile_picture": null,
      "rating_score": 4.8, "students_passed": 142
    },
    "upcoming_activities": [
      { "id": "...", "type": "mentoring", "title": "Mentoring Kelas A", "date": "2026-11-10" }
    ],
    "students": [
      {
        "student_id": "...", "name": "Arief Ramadhan", "profile_picture": null,
        "paket": "Beasiswa Unggulan", "progress": 75, "university": "IPB University"
      }
    ]
  }
  ```
  > ⚠️ **Override note:** Response key adalah `mentor_profile` (bukan `mentor`). Pastikan Flutter model memetakan key ini.

### `GET /mentor/classes`
List semua kelas yang dipegang mentor. Untuk `mentor_kelas_page.dart`.
- **Response 200:**
  ```json
  [
    { "class_id": "...", "package_title": "BSI Scholarship Intensif", "total_students": 25, "active_tasks": 2 }
  ]
  ```

### `GET /mentor/classes/{class_id}/students`
List semua peserta di kelas tertentu. Untuk `mentor_list_peserta_page.dart`.
- **Response 200:**
  ```json
  [
    { "student_id": "...", "name": "Rifaldi", "profile_picture": null, "progress": 80, "university": "IPB University" }
  ]
  ```

### `GET /mentor/students/{student_id}/progress`
Progress detail seorang student. Untuk `peserta_detail_page.dart`.
- **Response 200:**
  ```json
  {
    "student_profile": { "name": "Arief Ramadhan", "university": "IPB University", "profile_picture": null },
    "progress_percentage": 75,
    "tasks": [
      { "task_id": "...", "title": "Essay Motivasi",   "submission_status": "graded",    "score": 85, "file_url": "https://..." },
      { "task_id": "...", "title": "Upload Sertifikat","submission_status": "submitted",  "score": null, "file_url": "https://..." }
    ],
    "mentoring_attendance": [
      { "session_id": "...", "title": "Mentoring #1", "attended": null }
    ]
  }
  ```

### `GET /mentor/students/{student_id}/submissions`
List semua submission file seorang student (semua tugas).

### `POST /mentor/submissions/{submission_id}/grade`
Nilai/setujui submission student.
- **Body:** `{ "score": 85, "feedback": "Bagus, tingkatkan lagi!", "status": "graded" }`
  > Score dan feedback opsional. Status bisa dikirim tanpa score untuk sekedar mengubah status.

### `GET /mentor/tasks/{task_id}/submissions`
List semua student yang submit ke task tertentu.

### `POST /mentor/classes/{class_id}/tasks`
Buat penugasan baru.
- **Body:** `{ "title": "Upload Ijazah", "description": "Format PDF max 2MB", "deadline_date": "2026-05-15" }`

### `PUT /mentor/tasks/{task_id}`
Edit penugasan (body sama seperti POST tasks).

### `DELETE /mentor/tasks/{task_id}`
Hapus penugasan.

### `POST /mentor/classes/{class_id}/mentoring`
Buat jadwal sesi mentoring baru.
- **Body:** `{ "title": "Sesi Wawancara", "session_date": "2026-05-18 19:30:00", "link": "https://meet.google.com/xxx" }`

### `POST /mentor/classes/{class_id}/documents`
Upload modul/template ke kelas.
- **Header:** `Content-Type: multipart/form-data`
- **Form Fields:** `title` (String), `file` (File PDF/DOCX)

### `POST /mentor/classes/{class_id}/checkpoints`
Tambah milestone/tahapan seleksi baru.
- **Body:** `{ "title": "Pengumuman Tes", "schedule_date": "2026-06-01", "order_index": 2 }`

---

## 📋 8. Daftar Lengkap Semua Route (30 Endpoints)

| Method | Endpoint | Akses | Digunakan Oleh |
|---|---|---|---|
| POST | `/auth/register` | Public | Register screen |
| POST | `/auth/login` | Public | Login screen |
| GET | `/auth/me` | Auth | Profile screen |
| POST | `/auth/logout` | Auth | Logout action |
| GET | `/calendar` | Auth | Kalender page |
| GET | `/notifications` | Auth | Notifikasi screen |
| POST | `/notifications/{id}/read` | Auth | Mark notif read |
| POST | `/notifications/read-all` | Auth | Mark all read |
| GET | `/chat/conversations` | Auth | Chat inbox |
| GET | `/chat/conversations/{id}` | Auth | Chat room |
| POST | `/chat/conversations/{id}` | Auth | Kirim pesan |
| GET | `/student/home` | Student | home_page.dart |
| GET | `/student/my-class-dashboard` | Student | dashboard_penugasan_page.dart |
| GET | `/student/packages` | Student | paket_screen.dart |
| GET | `/student/packages/{id}` | Student | detail_paket_page.dart |
| GET | `/student/tasks/{id}` | Student | Task detail |
| POST | `/student/tasks/{id}/submit` | Student | Upload tugas |
| GET | `/mentor/dashboard` | Mentor | mentor_dashboard_screen.dart |
| GET | `/mentor/classes` | Mentor | mentor_kelas_page.dart |
| GET | `/mentor/classes/{id}/students` | Mentor | mentor_list_peserta_page.dart |
| GET | `/mentor/students/{id}/progress` | Mentor | peserta_detail_page.dart |
| GET | `/mentor/students/{id}/submissions` | Mentor | Lihat semua submission |
| POST | `/mentor/submissions/{id}/grade` | Mentor | Nilai tugas |
| GET | `/mentor/tasks/{id}/submissions` | Mentor | Siapa yang submit? |
| POST | `/mentor/classes/{id}/tasks` | Mentor | Buat tugas |
| PUT | `/mentor/tasks/{id}` | Mentor | Edit tugas |
| DELETE | `/mentor/tasks/{id}` | Mentor | Hapus tugas |
| POST | `/mentor/classes/{id}/mentoring` | Mentor | Jadwal mentoring |
| POST | `/mentor/classes/{id}/documents` | Mentor | Upload modul |
| POST | `/mentor/classes/{id}/checkpoints` | Mentor | Tambah checkpoint |

---

## 🧪 9. Test Accounts (Setelah `php artisan db:seed`)

| Role | Email | Password |
|---|---|---|
| Admin | admin@nalarin.id | password |
| Mentor | mentor1@nalarin.id | password |
| Mentor | mentor2@nalarin.id | password |
| Student | student1@nalarin.id | password |
| Student | student2@nalarin.id | password |

---

*API Documentation v2 — Nalarin Mobile Backend — Laravel 13 + MongoDB*
*Generated: 2026-04-30*
