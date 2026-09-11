# REST API CONTRACTS: TopSpeak Backend Engine

Dokumen ini adalah **sumber kebenaran** spesifikasi REST API (JSON Request/Response) untuk menghubungkan aplikasi **Android Native (Client)** dengan **Laravel (Backend Server)**. Setiap endpoint baru wajib didokumentasikan di sini dan mengikuti format standar.

---

## 1. Standard Global Specifications

### Base URL
`https://api.topspeak.app/api/v1` (lokal: `http://127.0.0.1:8000/api/v1`)

### Default Headers
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {sanctum_token}   # wajib untuk endpoint terproteksi
```

### Standard Response Format (HTTP 2xx)
```json
{
  "status": "success",
  "message": "Deskripsi singkat hasil operasi",
  "data": {}
}
```

### Standard Error Format (HTTP 4xx / 5xx)
```json
{
  "status": "error",
  "message": "Pesan kesalahan teknis atau validasi",
  "errors": {
    "field_name": ["Detail pesan kesalahan validasi"]
  }
}
```

### Kode Error Bisnis
| HTTP | Kondisi | Keterangan |
|------|---------|------------|
| 401 | Token Sanctum tidak ada/tidak valid | Headers `WWW-Authenticate` |
| 402 | Kuota trial habis (Free Tier) | `errors.is_paywalled=true`, body berisi kuota & status |
| 403 | Bukan admin / akses dilarang | Endpoint admin, callback signature invalid |
| 404 | Sesi/topik/merchant order tidak ditemukan | |
| 422 | Validasi gagal / turn tidak valid | `errors` berisi detail per field |
| 429 | Terlalu banyak percobaan login (maks 5/menit/email+IP) | |

---

## 2. System & Auth Endpoints

### A. Check App Version & Force Update
Digunakan oleh Splash Screen untuk memverifikasi apakah versi aplikasi wajib diperbarui.

**Endpoint:** `GET /app-version`
**Authentication:** None (Public)

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Versi aplikasi",
  "data": {
    "latest_version": "1.2.0",
    "min_required_version": "1.2.0",
    "is_force_update": false,
    "play_store_url": "https://play.google.com/store/apps/details?id=com.topspeak.app",
    "update_message": "Versi baru TopSpeak telah tersedia dengan pembaruan fitur dan perbaikan keamanan."
  }
}
```

### B. Device Registration / Guest Login
Mendaftarkan perangkat baru saat aplikasi pertama kali dibuka.

**Endpoint:** `POST /auth/register-device`
**Authentication:** None

**Request Body**
```json
{ "device_uuid": "9f8a3b2c-1d0e-4f5a-8b9c-7d6e5f4a3b2c" }
```

**Response Success (201 Created)**
```json
{
  "status": "success",
  "message": "Daftar perangkat berhasil. Gunakan token untuk autentikasi.",
  "data": {
    "token": "1|laravel_sanctum_token_hash...",
    "access_token": "1|laravel_sanctum_token_hash...",
    "token_type": "Bearer",
    "user": {
      "id": 102,
      "name": "Guest Learner",
      "email": "guest-9f8a3b2c1d0e@topspeak.app",
      "is_guest": true,
      "device_uuid": "9f8a3b2c-1d0e-4f5a-8b9c-7d6e5f4a3b2c",
      "phone_number": null,
      "current_cefr_level": "A1",
      "remaining_trial_sessions": 1,
      "subscription_status": "FREE",
      "subscription": { "status": "FREE", "expires_at": null, "is_premium": false },
      "level_history": []
    }
  }
}
```

### C. Registrasi Email & Password
Membuat akun ber-email dari akun guest (token guest hasil `/auth/register-device` tetap dipakai).

**Endpoint:** `POST /auth/register`
**Authentication:** Bearer Token (token guest)

**Request Body**
```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "secret123"
}
```
- Email otomatis dilowercase.
- Jika email sudah terdaftar oleh akun lain → akun lama **dipindah ke perangkat ini** dan token perangkat lama **dicabut** (satu perangkat aktif).

**Response Success (201 Created — email baru)**
```json
{
  "status": "success",
  "message": "Pendaftaran berhasil.",
  "data": {
    "token": "94|...",
    "access_token": "94|...",
    "token_type": "Bearer",
    "user": {
      "id": 85,
      "name": "Budi Santoso",
      "email": "budi@example.com",
      "is_guest": false,
      "device_uuid": "9f8a3b2c-1d0e-4f5a-8b9c-7d6e5f4a3b2c",
      "phone_number": null,
      "current_cefr_level": "A1",
      "remaining_trial_sessions": 1,
      "subscription_status": "FREE",
      "subscription": { "status": "FREE", "expires_at": null, "is_premium": false },
      "level_history": null
    }
  }
}
```

**Response 200 (email sudah terdaftar):**
- `"message": "Akun sudah terdaftar di perangkat ini."` (akun sama dengan perangkat sekarang).
- `"message": "Email berhasil dipulihkan ke perangkat baru."` (registrasi ulang saat ganti HP).

**Error Responses**
```json
{ "status": "error", "message": "Email sudah terdaftar. Password tidak sesuai.", "errors": {} }  // 422
{ "status": "error", "message": "Silakan masuk terlebih dahulu." }  // 401, token invalid/hilang
```

### D. Login Email & Password
Login ke akun yang **sudah terdaftar** (tidak membuat akun baru). Form login Android memakai endpoint ini, bukan `/auth/register`.

**Endpoint:** `POST /auth/login`
**Authentication:** Bearer Token (token guest hasil `/auth/register-device`)
**Rate Limit:** maksimal 5 percobaan per menit per email + IP

**Request Body**
```json
{
  "email": "budi@example.com",
  "password": "password123"
}
```

**Response Success (200 OK)** — struktur `data` identik dengan `/auth/register`:
```json
{
  "status": "success",
  "message": "Login berhasil.",
  "data": {
    "token": "94|...",
    "access_token": "94|...",
    "token_type": "Bearer",
    "user": {
      "id": 85,
      "name": "BudiLogin",
      "email": "budi@example.com",
      "is_guest": false,
      "device_uuid": "9f8a3b2c-1d0e-4f5a-8b9c-7d6e5f4a3b2c",
      "phone_number": null,
      "current_cefr_level": "A1",
      "remaining_trial_sessions": 1,
      "subscription_status": "FREE",
      "subscription": { "status": "FREE", "expires_at": null, "is_premium": false },
      "level_history": null
    }
  }
}
```

> Jika email terdaftar di perangkat lain (ganti HP), akun dipindah ke perangkat ini, token perangkat lama dicabut, lalu token baru diterbitkan.

**Error Responses**
```json
{ "status": "error", "message": "Email tidak terdaftar." }                                // 404
{ "status": "error", "message": "Email atau password salah." }                            // 401
{ "status": "error", "message": "Terlalu banyak percobaan, coba lagi nanti." }            // 429
{ "message": "The email field is required.", "errors": { "email": ["..."] } }             // 422 (format Laravel)
{ "status": "error", "message": "Silakan masuk terlebih dahulu." }                        // 401, token invalid/hilang
```

### E. Logout
Keluar dari perangkat sekarang (token aktif dicabut). Untuk berganti user, masuk kembali via `/auth/login`.

**Endpoint:** `POST /auth/logout`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Berhasil keluar dari perangkat ini.",
  "data": []
}
```

---

## 3. Active Conversation & Evaluation Endpoints

### A. Start New Conversation Session
Inisialisasi 1 sesi latihan baru (5 turn untuk ADAPTIVE/THEMATIC).

**Endpoint:** `POST /sessions/start`
**Authentication:** Bearer Token

**Request Body**
```json
{
  "mode": "ADAPTIVE",
  "topic_id": null
}
```
- `mode`: `ADAPTIVE` (default), `THEMATIC`, `IELTS_SPEAKING`, atau `TOEFL_IBT`.
  - `THEMATIC`: `topic_id` wajib diisi.
  - `IELTS_SPEAKING` / `TOEFL_IBT`: simulasi ujian, tanpa `topic_id`. Jumlah turn = jumlah part × 2 (`EXAM_TURNS_PER_PART`).

**Response Success (201 Created) — ADAPTIVE/THEMATIC**
```json
{
  "status": "success",
  "message": "Sesi latihan dimulai",
  "data": {
    "session_id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a",
    "mode": "ADAPTIVE",
    "current_cefr_level": "A1",
    "total_turns_planned": 5,
    "first_question": {
      "question_id": 14,
      "turn_number": 1,
      "question_text": "Could you tell me your full name and where you currently live?",
      "audio_url": "https://api.topspeak.app/storage/audio/q14.mp3"
    }
  }
}
```

**Response Success (201 Created) — IELTS_SPEAKING / TOEFL_IBT**
```json
{
  "status": "success",
  "message": "Sesi latihan dimulai",
  "data": {
    "session_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
    "mode": "IELTS_SPEAKING",
    "current_cefr_level": "C1",
    "total_turns_planned": 4,
    "exam": {
      "test_type": "IELTS_SPEAKING",
      "parts": [1, 2]
    },
    "first_question": {
      "question_id": 501,
      "turn_number": 1,
      "question_text": "Analyze the impact of artificial intelligence on the global job market.",
      "part_number": 1,
      "audio_url": null
    }
  }
}
```

**Error (402 Paywall)** — kuota habis:
```json
{
  "status": "error",
  "message": "Kuota latihan gratis kamu sudah habis. Upgrade ke Premium untuk melanjutkan tanpa batas.",
  "errors": {
    "is_paywalled": true,
    "remaining_trial_sessions": 0,
    "subscription_status": "FREE"
  }
}
```

### B. Evaluate Turn Response (Core Logic & Repetition Trigger)
Mengirim hasil transkrip lisan user pada turn berjalan.

**Endpoint:** `POST /sessions/evaluate-turn`
**Authentication:** Bearer Token

**Request Body**
```json
{
  "session_id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a",
  "turn_number": 1,
  "question_id": 14,
  "user_transcript": "He go to market yesterday"
}
```

**Response Success - Error Detected (Triggers WAITING_REPETITION)**
```json
{
  "status": "success",
  "message": "Turn dievaluasi",
  "data": {
    "step_state": "WAITING_REPETITION",
    "scores": {
      "word_count_score": 0,
      "grammar_score": 0,
      "total_turn_score": 0
    },
    "has_error": true,
    "correction_data": {
      "user_said": "He go to market yesterday",
      "correct_way": "He went to the market yesterday"
    },
    "expected_repetition_text": "He went to the market yesterday",
    "ai_speech_prompt": "Not quite! Listen and repeat: \"He went to the market yesterday\"",
    "ai_audio_url": null,
    "next_question": null
  }
}
```

**Response Success - No Error (State NORMAL)**
```json
{
  "status": "success",
  "message": "Turn dievaluasi",
  "data": {
    "step_state": "NORMAL",
    "scores": {
      "word_count_score": 1,
      "grammar_score": 1,
      "total_turn_score": 2
    },
    "has_error": false,
    "ai_speech_prompt": "Great! Now, What is your favorite daily activity and why do you like it?",
    "next_question": {
      "question_id": 15,
      "turn_number": 2,
      "question_text": "What is your favorite daily activity and why do you like it?",
      "audio_url": "https://api.topspeak.app/storage/audio/q15.mp3"
    },
    "promotion": null
  }
}
```
> Pada turn ke-4 tanpa error dan akumulasi skor ≥ 6 (dari maks 8), `promotion` berisi:
> `{ "is_promoted": true, "previous_level": "A1", "new_level": "A2", "trigger_score": 6 }`.
> Setiap error baru pada turn yang sedang WAITING_REPETITION → 422 `RepetitionNotPending`.

### C. Verify Repetition Turn (Say Again Flow)
Mengirim ucapan pengulangan user saat `step_state = 'WAITING_REPETITION'`.

**Endpoint:** `POST /sessions/verify-repetition`
**Authentication:** Bearer Token

**Request Body**
```json
{
  "session_id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a",
  "turn_number": 1,
  "user_repetition_transcript": "He went to the market yesterday"
}
```

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Repetition diverifikasi",
  "data": {
    "repetition_success": true,
    "repetition_attempts": 1,
    "step_state": "NORMAL",
    "ai_transition_speech": "Alright, got it. Now, what did he buy at the market?",
    "next_question": {
      "question_id": 16,
      "turn_number": 2,
      "question_text": "What did he buy at the market?",
      "audio_url": "https://api.topspeak.app/storage/audio/q16.mp3"
    }
  }
}
```

### D. Complete Session & Diagnostic Report
Menutup sesi dan menghitung status kenaikan level (4-Turn Rolling Window).
Untuk mode ujian (`IELTS_SPEAKING` / `TOEFL_IBT`), promosi **tidak** dievaluasi
dan respons menyertakan `exam_report` per part.

**Endpoint:** `POST /sessions/complete`
**Authentication:** Bearer Token

**Request Body**
```json
{ "session_id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a" }
```

**Response Success (200 OK) — ADAPTIVE/THEMATIC**
```json
{
  "status": "success",
  "message": "Sesi selesai",
  "data": {
    "session_id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a",
    "cefr_level_current": "B1",
    "total_turns_completed": 5,
    "accumulated_score": 6,
    "is_promoted": true,
    "previous_cefr_level": "A2",
    "new_cefr_level": "B1",
    "remaining_trial_sessions": 0,
    "diagnostic_report": {
      "grammar_accuracy": "80%",
      "frequent_errors": ["Subject-Verb Agreement"],
      "tutor_notes": "Selamat! Kamu naik level dari A2 ke B1 berkat skor 6/8 pada 4-turn pertama. Terus berlatih untuk mencapai level berikutnya."
    }
  }
}
```

**Response Success (200 OK) — IELTS_SPEAKING / TOEFL_IBT** (tanpa promosi, `is_promoted=false`)
```json
{
  "status": "success",
  "message": "Sesi selesai",
  "data": {
    "session_id": "a1b2c3d4-e5f6-7a8b-9c0d-1e2f3a4b5c6d",
    "cefr_level_current": "C1",
    "total_turns_completed": 4,
    "accumulated_score": 8,
    "is_promoted": false,
    "previous_cefr_level": null,
    "new_cefr_level": null,
    "remaining_trial_sessions": 0,
    "exam_report": {
      "test_type": "IELTS_SPEAKING",
      "total_score": 8,
      "max_score": 8,
      "score_pct": 100,
      "grammar_accuracy": "100%",
      "parts": [
        { "part_number": 1, "turns": 2, "total_score": 4, "max_score": 4, "score_pct": 100 },
        { "part_number": 2, "turns": 2, "total_score": 4, "max_score": 4, "score_pct": 100 }
      ]
    },
    "diagnostic_report": {
      "grammar_accuracy": "100%",
      "frequent_errors": [],
      "tutor_notes": "Performa sangat baik! Kamu menjawab dengan kalimat panjang dan grammar yang benar. Pertahankan konsistensi ini untuk meraih band tinggi."
    }
  }
}
```

### E. Session History (Riwayat Sesi)
Daftar sesi user (terbaru dulu), dengan agregat skor.

**Endpoint:** `GET /sessions/history?per_page=10`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Riwayat sesi",
  "data": {
    "items": [
      {
        "id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a",
        "mode": "ADAPTIVE",
        "theme": null,
        "start_level": "A1",
        "current_level": "A2",
        "status": "COMPLETED",
        "turns": 5,
        "total_score": 8,
        "score_pct": 80,
        "avg_grammar_accuracy": 80,
        "started_at": "2026-08-13T06:00:00+00:00",
        "completed_at": "2026-08-13T06:12:00+00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 22
    }
  }
}
```

### F. Session Detail
Detail 1 sesi beserta seluruh turn-nya (hanya milik user pemilik).

**Endpoint:** `GET /sessions/{id}`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Detail sesi",
  "data": {
    "session": {
      "id": "c1f8e2a4-5b6c-7d8e-9f0a-1b2c3d4e5f6a",
      "mode": "ADAPTIVE",
      "theme": null,
      "start_level": "A1",
      "current_level": "A2",
      "status": "COMPLETED",
      "turns": 5,
      "total_score": 8,
      "score_pct": 80,
      "avg_grammar_accuracy": 80,
      "started_at": "2026-08-13T06:00:00+00:00",
      "completed_at": "2026-08-13T06:12:00+00:00"
    },
    "turns": [
      {
        "turn_number": 1,
        "question_text": "Could you tell me your full name and where you currently live?",
        "user_said_text": "I go to market yesterday",
        "correct_way_text": "I went to the market yesterday",
        "score_word_count": 0,
        "score_grammar": 0,
        "total_turn_score": 0,
        "has_error": true,
        "step_state": "NORMAL",
        "repetition_success": true,
        "created_at": "2026-08-13T06:01:00+00:00"
      }
    ]
  }
}
```
**Error (404):** `"Sesi tidak ditemukan."` (termasuk sesi milik user lain).

---

## 4. User Profile, Stats & Master Data

### A. Get User Profile & Level History

**Endpoint:** `GET /user/profile`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Profil pengguna",
  "data": {
    "id": 102,
    "name": "Budi Santoso",
    "device_uuid": "9f8a3b2c-1d0e-4f5a-8b9c-7d6e5f4a3b2c",
    "phone_number": "6281234567890",
    "current_cefr_level": "B1",
    "remaining_trial_sessions": 0,
    "subscription_status": "FREE",
    "subscription": {
      "status": "FREE",
      "expires_at": null,
      "is_premium": false
    },
    "level_history": [
      {
        "previous_level": "A1",
        "new_level": "A2",
        "promoted_at": "2026-07-20T10:00:00+00:00"
      }
    ]
  }
}
```

### B. Get User Learning Stats & Streak
Statistik untuk home screen (streak, sesi, akurasi, frasa dipelajari).

**Endpoint:** `GET /user/stats`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Statistik belajar",
  "data": {
    "current_cefr_level": "A2",
    "is_premium": false,
    "remaining_trial_sessions": 3,
    "total_sessions_completed": 12,
    "total_turns": 48,
    "avg_turn_score": 2.4,
    "grammar_accuracy_pct": 78,
    "phrases_learned": 9,
    "current_streak_days": 3,
    "longest_streak_days": 6
  }
}
```
> Aturan streak: latihan hari ini ATAU kemarin masih menghitung streak; selisih > 1 hari memutus streak.

### B.1. Get User Daily Progress
Progres harian untuk badge latihan & notifikasi. Seluruh "hari" dihitung dalam zona waktu **Asia/Jakarta** (UTC+7), bukan zona server.

**Endpoint:** `GET /user/daily-progress`
**Authentication:** Bearer Token (guest/device `X-Device-UUID` tetap mengembalikan 200 dengan nilai nol)

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Progres harian",
  "data": {
    "date": "2026-09-09",
    "adaptive_sessions": 1,
    "thematic_sessions": 2,
    "exam_lessons": 1,
    "streak_days": 3,
    "last_7_days": [
      { "date": "2026-09-03", "completed": true },
      { "date": "2026-09-04", "completed": false },
      { "date": "2026-09-05", "completed": true },
      { "date": "2026-09-06", "completed": true },
      { "date": "2026-09-07", "completed": true },
      { "date": "2026-09-08", "completed": true },
      { "date": "2026-09-09", "completed": true }
    ]
  }
}
```

**Keterangan field:**
- `adaptive_sessions` / `thematic_sessions`: jumlah sesi percakapan dengan mode ADAPTIVE / THEMATIC yang **dikomplit pada hari ini** (`POST /sessions/complete` → `status=COMPLETED`).
- `exam_lessons`: jumlah lesson yang **lulus hari ini** (`POST /curriculum/lessons/{id}/complete` → `status=PASSED`).
- `streak_days`: hari beruntun, masing-masing dengan minimal 1 sesi penuh (sesi percakapan selesai ATAU lesson lulus). Mulai dihitung dari hari ini; bila hari ini belum ada aktivitas, dari kemarin. Satu poin per hari.
- `last_7_days`: 7 hari terakhir termasuk hari ini (hari terlama di indeks 0). `completed=true` bila hari itu ada minimal 1 sesi penuh.
- `date`: tanggal hari ini (Asia/Jakarta, format `Y-m-d`).
- Opsional `?tz=` (mis. `?tz=Asia/Makassar`) belum digunakan; kontrak saat ini selalu Asia/Jakarta.

**Error:**
- `401 Unauthorized` → `{"status":"error","message":"Unauthenticated."}`
- `5xx` → app fallback ke nilai default (semua 0, streak 0, `last_7_days` kosong).
- Endpoint **read-only**, tidak mengubah status sesi/lesson maupun flow `complete`.

### B.2. Get IELTS Progress Predictor
Prediksi estimasi IELTS Speaking Band Score berdasarkan performa latihan kurikulum (Units/Lessons/Questions).

**Endpoint:** `GET /user/progress-predictor`
**Authentication:** Bearer Token (guest/device: 200 dengan `is_ready=false`)

**Trigger kalkulasi ulang:** setiap sesi latihan selesai (`POST /curriculum/lessons/{id}/complete`), cache `user_progress_predictors` diperbarui. Saat cache kosong, endpoint menghitung on-the-fly lalu menyimpan.

**Response (200 OK) jika `passed_lessons_count >= 3`:**
```json
{
  "status": "success",
  "message": "Prediksi progres IELTS",
  "data": {
    "is_ready": true,
    "predicted_band": "Band 6.5 - 7.0",
    "overall_index": 82.40,
    "status_label": "Competent / Target Achieved",
    "color_code": "#22C55E",
    "metrics_breakdown": {
      "completion_rate": {
        "score": 75.00,
        "weight": "40%",
        "passed_lessons": 15,
        "total_lessons": 20
      },
      "mastery_performance": {
        "score": 88.50,
        "weight": "40%",
        "based_on_last_sessions": 10
      },
      "key_point_accuracy": {
        "score": 85.00,
        "weight": "20%"
      }
    },
    "last_updated": "2026-09-10T10:30:00+00:00"
  }
}
```

**Response (200 OK) jika `passed_lessons_count < 3`:**
```json
{
  "status": "success",
  "message": "Prediksi progres IELTS",
  "data": {
    "is_ready": false,
    "predicted_band": "NEED_MORE_DATA",
    "overall_index": 0.00,
    "status_label": "Need More Data",
    "color_code": "#9CA3AF",
    "message": "Selesaikan minimal 3 Lesson untuk melihat prediksi IELTS Band Anda.",
    "passed_lessons_count": 1,
    "required_lessons_count": 3
  }
}
```

**Rumus:**
- `overall_index = (completion_rate × 0.40) + (mastery_performance × 0.40) + (key_point_accuracy × 0.20)`.
- `completion_rate = passed_lessons / total_lessons_aktif × 100`.
- `mastery_performance` = rata-rata skor **10 sesi lulus terakhir** (`practice_sessions.score` = rata-rata skor 5 soal sesi tsb, skala 0–100).
- `key_point_accuracy = correct_keypoints / total_keypoints × 100` pada sesi-sesi **lulus** saja.
- Sesi dinyatakan lulus bila `correct_count >= 4` (skor per soal `>= 80` & `key_point_detected`).

**Pemetaan IELTS Band:**
| Overall Index (%) | Band | Label | Warna |
| :--- | :--- | :--- | :--- |
| 90+ | 7.5–8.5 | Expert / Exam Ready | `#10B981` |
| 78–89.99 | 6.5–7.0 | Competent / Target Achieved | `#22C55E` |
| 65–77.99 | 5.5–6.0 | Modest / Need More Practice | `#EAB308` |
| 50–64.99 | 4.5–5.0 | Limited / Focus on Key Points | `#F97316` |
| < 50 | < 4.5 | Beginner / Foundation Level | `#EF4444` |

### C. Get Thematic Topics Catalog
Katalog topik untuk Thematic Freedom Mode.

**Endpoint:** `GET /thematic-topics`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Topik tematik",
  "data": [
    {
      "id": 1,
      "topic_name": "Job Interview Simulation",
      "selected_level": "Intermediate",
      "roleplay_persona": "HR Manager at a Tech Startup",
      "context_vocab_tags": ["skills", "experience", "strengths", "career"],
      "is_active": true
    }
  ]
}
```

### D. Vocabulary Bank (Bank Kosakata)
Kosakata terpaginasi dengan filter level CEFR, kategori topik, dan pencarian.

**Endpoint:** `GET /vocabulary?per_page=20&cefr_level=A2&topic_category=Shopping&q=price`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Bank kosakata",
  "data": {
    "items": [
      {
        "id": 18,
        "word": "cheap",
        "part_of_speech": "adjective",
        "cefr_level": "A2",
        "topic_category": "Shopping"
      }
    ],
    "pagination": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 20,
      "total": 16
    }
  }
}
```

### E. Learned Vocabulary (Frasa Dipelajari)
Frasa yang pernah dikoreksi ke user, dikelompokkan, dengan status penguasaan.

**Endpoint:** `GET /vocabulary/learned`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Frasa yang telah dipelajari",
  "data": [
    {
      "phrase": "Yesterday I went to the market.",
      "corrected_from": "Yesterday I go to the market.",
      "mastered": true,
      "learned_at": "2026-08-13T06:01:00+00:00"
    }
  ]
}
```
> `mastered=true` bila repetition sukses ATAU user pernah mengucapkan versi benar yang sama.

---

## 5. Subscription & Payment (Duitku)

> Sumber paket = **Master Paket Langganan** (`subscription_plans`, status ACTIVE) — bukan hardcoded.
> App: ambil katalog `/subscriptions/plans` → pilih paket → `/subscriptions/payment-methods` → pilih kanal →
> `purchase`. Signature semua request/callback = **HMAC-SHA256** (`hash_hmac`), bukan sha256 plain.

### A. List Plans (Master Paket Langganan)
Katalog paket yang dijual.

**Endpoint:** `GET /subscriptions/plans`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Daftar paket langganan.",
  "data": {
    "plans": [
      {
        "id": 1,
        "name": "Paket Harian",
        "description": "...",
        "features": [],
        "price": 5000,
        "original_price": 5000,
        "duration_value": 1,
        "duration_unit": "DAY",
        "duration_label": "1 hari",
        "badge_promo": null
      }
    ]
  }
}
```
- Paket selalu **TIME** (premium aktif sesuai durasi).
- `price` = harga efektif (diskon bila ada). Tampilkan label dari `duration_label`.

### B. List Payment Methods
Menampilkan kanal pembayaran aktif (logo, nama, fee) untuk nominal paket terpilih.

**Endpoint:** `GET /subscriptions/payment-methods?plan_id=1`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Daftar kanal pembayaran.",
  "data": {
    "plan_id": 1,
    "amount": 5000,
    "methods": [
      { "code": "VA", "name": "MAYBANK VA", "image": "https://images.duitku.com/hotlink-ok/VA.PNG", "fee": "0" },
      { "code": "VC", "name": "CREDIT CARD", "image": "https://images.duitku.com/hotlink-ok/VC.PNG", "fee": "0" }
    ]
  }
}
```

### C. Create Checkout / Purchase
Membuat transaksi Duitku (inquiry) untuk kanal terpilih & menyimpan langganan PENDING.

**Endpoint:** `POST /subscriptions/purchase`
**Authentication:** Bearer Token

**Request Body**
```json
{ "plan_id": 1, "payment_method": "VA" }
```
- `plan_id`: id paket ACTIVE dari `/subscriptions/plans`.
- `payment_method`: kode kanal dari `/subscriptions/payment-methods`. **Opsional** — bila kosong,
  backend memilih kanal pertama yang tersedia.

**Response Success (201 Created)**
```json
{
  "status": "success",
  "message": "Checkout Duitku dibuat. Lanjutkan pembayaran.",
  "data": {
    "plan_id": 1,
    "plan_name": "Paket Harian",
    "merchant_order_id": "TP20260908061012A1B2C3D4",
    "amount": 5000,
    "payment_status": "PENDING",
    "payment_method": "VA",
    "checkout_url": "https://sandbox.duitku.com/topup/topupdirectv2.aspx?ref=...",
    "va_number": "7007014001444348",
    "qr_string": null,
    "app_url": null
  }
}
```
> `va_number` (VA) / `qr_string` (QRIS) / `app_url` (e-wallet) hanya muncul sesuai kanal.
> **Error:** `404` paket tidak ditemukan/tidak aktif; `422` gateway nonaktif.

### D. Duitku Payment Callback (Webhook)
Dihubungi server Duitku saat status pembayaran berubah. Signature diverifikasi server (HMAC), bukan oleh app.

**Endpoint:** `POST /subscriptions/duitku-callback`
**Authentication:** None (Public Webhook)

**Request Body (dikirim Duitku)**
```json
{
  "merchantCode": "DS23463",
  "amount": "5000",
  "merchantOrderId": "TP20260908061012A1B2C3D4",
  "resultCode": "00",
  "reference": "DUITKUREF123",
  "signature": "<hmac-sha256(merchantCode + amount + merchantOrderId, apiKey)>"
}
```

**Response Success (200 OK)** — `resultCode "00"` mengaktifkan premium sesuai durasi paket (mis. +1 hari):
```json
{
  "status": "success",
  "message": "Callback diproses",
  "data": { "activated": true, "merchant_order_id": "TP20260908061012A1B2C3D4", "payment_status": "PAID" }
}
```
**Error (403):** signature tidak valid → `"Invalid Duitku callback signature."`
**Error (404):** merchant order tidak ditemukan.
> `resultCode` selain `"00"` → langganan ditandai `EXPIRED`; callback berulang `PAID` bersifat idempoten (tidak menggandakan masa aktif).

---

## 6. Admin Server Endpoints (Web Admin Dashboard / API)

Semua endpoint admin API memakai `Authorization: Bearer {admin_token}` (user dengan `is_admin=true`). Halaman web admin: `GET /admin/login` (session auth) lalu `/admin/*`.

### A. Admin Add Grammar Rule

**Endpoint:** `POST /admin/grammar-rules`

**Request Body**
```json
{
  "rule_code": "PREP_02",
  "category": "Preposition Error",
  "cefr_level": "B1",
  "regex_pattern": "\\bdepend\\s+to\\b",
  "description": "Kata 'depend' harus diikuti preposisi 'on', bukan 'to'."
}
```

**Response Success (201 Created)**
```json
{
  "status": "success",
  "message": "New grammar rule added successfully",
  "data": { "id": 12, "rule_code": "PREP_02", "is_active": true }
}
```

### B. Admin Update Force Update Config

**Endpoint:** `POST /admin/app-config`

**Request Body**
```json
{
  "latest_app_version": "1.3.0",
  "min_required_version": "1.2.0",
  "is_force_update": true,
  "play_store_url": "https://play.google.com/store/apps/details?id=com.topspeak.app",
  "update_message": "Mohon perbarui aplikasi TopSpeak Anda ke versi terbaru."
}
```

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "App configuration updated successfully",
  "data": { "latest_app_version": "1.3.0", "min_required_version": "1.2.0" }
}
```

---

## 7. Assessment Evaluator (IELTS & TOEFL iBT Speaking)

Menilai satu jawaban speaking dengan **pure LLM evaluator** (temperature `0.0`,
Structured JSON Output). Endpoint independen dari sesi latihan biasa.

### A. Evaluate Answer

**Endpoint:** `POST /assessment/evaluate`
**Authentication:** Bearer Token

**Request Body**
```json
{
  "test_type": "IELTS",
  "task_type": "SPEAKING_PART_2",
  "prompt_question": "Describe a memorable trip you took in the past year.",
  "user_transcript": "I want to talk about my visit to Malang last year. It was very nice because I went with my family...",
  "duration_seconds": 95
}
```
- `test_type`: `IELTS` | `TOEFL`
- `task_type`: mis. `SPEAKING_PART_1`, `SPEAKING_PART_2`, `INDEPENDENT_TASK`
- `duration_seconds`: opsional (1–600)

**Response Success (200 OK) — IELTS**
```json
{
  "status": "success",
  "message": "Evaluasi selesai",
  "data": {
    "test_type": "IELTS",
    "scores": {
      "fluency_coherence": 6.0,
      "lexical_resource": 6.5,
      "grammatical_range_accuracy": 5.5,
      "pronunciation_estimate": 6.0,
      "overall_band": 6.0,
      "overall_score": 6.0
    },
    "fluency_matrix": { "s_total": 0.65, "final_fluency": 1 },
    "content_alignment": { "is_on_topic": true, "relevance_score": 0.90 },
    "corrections": [
      {
        "original": "I go with my family",
        "corrected": "I went with my family",
        "issue_type": "Grammar (Tense)",
        "explanation": "Use past simple 'went' when describing a completed trip in the past."
      }
    ],
    "feedback_summary": "Good flow and vocabulary, but pay attention to past tense consistency."
  }
}
```

**Response Success (200 OK) — TOEFL** (skor 0–4 / skala 0–30)
```json
{
  "status": "success",
  "message": "Evaluasi selesai",
  "data": {
    "test_type": "TOEFL",
    "scores": {
      "delivery": 3,
      "language_use": 2,
      "topic_development": 3,
      "raw_score": 2.67,
      "scaled_score_30": 20,
      "overall_score": 20
    },
    "fluency_matrix": { "s_total": 0.68, "final_fluency": 1 },
    "content_alignment": { "is_on_topic": true, "relevance_score": 0.85 },
    "corrections": [],
    "feedback_summary": "Clear response with good topic development. Needs improvement in grammatical accuracy."
  }
}
```

> **Safety Guard:** backend selalu menimpa `fluency_matrix.final_fluency`
> dengan aturan biner `s_total >= 0.50 ? 1 : 0` (LLM tidak bisa meng-klaim 1 bila s_total rendah).

**Error (422)** — LLM tidak dikonfigurasi / gagal merespons:
`"Evaluasi IELTS/TOEFL tidak tersedia: LLM tidak dikonfigurasi atau gagal merespons. Coba lagi nanti."`

> Hasil evaluasi tersimpan di tabel `assessment_logs` (histori per user per test type).

---

## 8. IELTS Curriculum (Units / Lessons / Questions)

Fitur kurikulum tertata per level (spesifikasi `IELTS.md`): **Unit → Lesson → Question**.
Setiap lesson memuat minimal 5 soal (utsai diambil 5 acak), passing grade **4 dari 5** (≥ 80%),
dan status kelulusan **per user** disimpan di `user_lesson_progress`.

### A. Fetch All Units & Lessons Status

**Endpoint:** `GET /curriculum`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Kurikulum IELTS",
  "data": [
    {
      "unit_id": 1,
      "unit_number": 12,
      "unit_title": "Studies",
      "part": 3,
      "outcome": "Berdiskusi tentang pendidikan dan peran teknologi dalam pembelajaran.",
      "lessons": [
        {
          "lesson_id": 101,
          "lesson_number": 1,
          "lesson_title": "Collocations: foreign language, focus on",
          "difficulty": "Easy",
          "status": "PASSED"
        }
      ]
    }
  ]
}
```
> `status`: `PASSED` (hijau & centang) atau `NOT_PASSED` (netral). Default per user `NOT_PASSED`.

### B. Start Practice Session (Get 5 Random Questions)

**Endpoint:** `GET /curriculum/lessons/{lesson_id}/session`
**Authentication:** Bearer Token

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Sesi latihan dimulai",
  "data": {
    "session_id": "SESS-98234712",
    "lesson_id": 101,
    "questions": [
      {
        "question_id": 501,
        "question_text": "Why do many educational systems choose to focus on foreign language learning from a young age?",
        "key_point": "focus on / foreign language"
      }
    ]
  }
}
```
> `questions` berisi hingga 5 soal acak milik lesson tersebut.
> **Error (404):** `"Lesson tidak ditemukan."`

### C. Evaluate Answer & Finalize Session

**Endpoint:** `POST /curriculum/lessons/{lesson_id}/evaluate`
**Authentication:** Bearer Token

**Request Body**
```json
{
  "session_id": "SESS-98234712",
  "answers": [
    {
      "question_id": 501,
      "user_transcript": "In my opinion schools focus on foreign language early because children learn faster."
    }
  ]
}
```
- `answers`: 1–5 item, setiap `question_id` wajib milik lesson ini (selain itu → 422).

**Request Body** — contoh lengkap 5 jawaban: `answers` diisi satu objek per soal (`question_id` + `user_transcript`).

**Response Success (200 OK)**
```json
{
  "status": "success",
  "message": "Evaluasi selesai",
  "data": {
    "session_result": {
      "correct_count": 4,
      "total_questions": 5,
      "is_passed": true,
      "lesson_status": "PASSED"
    },
    "evaluations": [
      {
        "question_id": 501,
        "is_correct": true,
        "score": 85,
        "key_point_detected": true,
        "key_point_target": "focus on / foreign language",
        "grammar_feedback": "Sentence structure is correct.",
        "vocabulary_feedback": "Good use of targeted collocations."
      }
    ]
  }
}
```

**Grading (AI Engine):**
| Dimensi | Bobot | Keterangan |
|---------|-------|------------|
| Key Point Checklist | 50% | Keberadaan & ketepatan kolokasi target (`key_point`) |
| Grammatical Accuracy | 25% | Ketepatan tata bahasa pada transkrip STT |
| Lexical Resource | 25% | Variasi kosakata sesuai `model_answer` |

- `score` per soal = 0–100 (bobot tertimbang). `is_correct = true` bila `key_point_detected && score >= 80`.
- `is_passed = true` bila `correct_count >= 4`.
- Status lesson hanya **naik** ke `PASSED`, tidak pernah turun (re-take gagal tetap `PASSED`).
- **Error (404):** lesson tidak ditemukan. **Error (422):** LLM tidak tersedia / `question_id` asing.

---

## 9. Catatan Implementasi

- **Autentikasi**: Sanctum Bearer token. Token didapat dari `POST /auth/register-device` (`data.token`).
- **Skor per turn**: maksimum 2 poin (`word_count` 0/1, `grammar` 0/1). Promosi level saat akumulasi 4-turn berurutan terbaik ≥ 6 (maks 8, tanpa demosi). Field `accumulated_score` (bukan `accumulated_3turn_score`).
- **Kuota**: Guest 1 sesi; verifikasi WA menambah 4 (total 5); konsumsi di turn pertama tiap sesi; premium unlimited.
- **Self-Learning Grammar**: kalimat dengan error yang belum terpetakan rule-nya otomatis tercatat sebagai pending rule (`/admin/grammar-rules/pending`) untuk direview admin.
- **OTP**: 6 digit, berlaku 5 menit, cooldown kirim ulang 60 detik. Kode development terlihat di `storage/logs/laravel.log` dan `data.debug_code`.
