# Documentation & API Specification (Backend)

Dokumen ini berisi spesifikasi arsitektur, skema database, logika bisnis, dan REST API endpoints untuk fitur IELTS Speaking Practice.

---

## 1. Core App Logic & Rules

1. **Akses Open-Ended**: Semua Unit dan Lesson dibuat terbuka secara default (`lessons.is_active = true`). Lesson non-aktif disembunyikan dari katalog & `total_lessons` Progress Predictor.
2. **Session Logic**: 
   - Setiap sesi latihan mengambil **5 soal acak** (`LIMIT 5`) dari bank soal di lesson tersebut.
3. **Passing Grade**: 
   - Minimal **4 dari 5 soal benar** ($\ge 80\%$) untuk dinyatakan **LULUS**.
4. **Grading Parameters (AI Engine)**:
   - **Key Point Checklist (50%)**: Keberadaan dan ketepatan kolokasi/frasa target.
   - **Grammatical Accuracy (25%)**: Ketepatan tata bahasa pada transkrip STT.
   - **Lexical Resource (25%)**: Kesesuaian variasi kata dengan model jawaban.
   - *(Note: Penilaian Fluency & Coherence dihapus total)*.
5. **Status Update**:
   - Jika LULUS ($correct\_count \ge 4$ dari 5) $\rightarrow$ Status lesson di-update menjadi `PASSED` (Centang Hijau).
   - Jika GAGAL $\rightarrow$ tetap `NOT_PASSED`, **kecuali** lesson sudah pernah `PASSED` (prinsip **No-Demotion** — status tidak pernah turun).
   - Pengguna dapat mengulang sesi dengan 5 soal acak baru.
6. **Sesi & Progress Predictor**: `GET /curriculum/lessons/{id}/session` mencatat record `practice_sessions`; `POST /curriculum/lessons/{id}/complete` mengisi metrik sesi dan memantik kalkulasi ulang `/user/progress-predictor` (lihat `PROGRESS_PREDICTOR_SPEC.md`).

---

## 2. Database Schema (PostgreSQL / MySQL)

### Table: `units`
| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | Primary Key, Auto Increment | ID Unit |
| `unit_number` | INT | Not Null | Nomor Unit (6, 7, 8, dst.) |
| `title` | VARCHAR(100) | Not Null | Judul Unit |
| `part` | INT | Not Null | Part IELTS (1, 2, atau 3) |
| `outcome` | TEXT | Nullable | Target pembelajaran unit |

### Table: `lessons`
| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | Primary Key, Auto Increment | ID Lesson |
| `unit_id` | INT | Foreign Key (`units.id`) | Relasi ke Unit |
| `lesson_number` | INT | Not Null | Nomor Lesson |
| `title` | VARCHAR(150) | Not Null | Judul / Fokus Lesson |
| `difficulty` | ENUM | `'Easy'`, `'Medium'`, `'Difficult'` | Tingkat kesulitan |
| `is_active` | BOOLEAN | Not Null, Default true | Draft/arsip dikecualikan dari katalog & predictor |

### Table: `questions`
| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | Primary Key, Auto Increment | ID Soal |
| `lesson_id` | INT | Foreign Key (`lessons.id`) | Relasi ke Lesson |
| `question_text` | TEXT | Not Null | Teks Pertanyaan |
| `model_answer` | TEXT | Not Null | Model Jawaban untuk AI reference |
| `key_point` | VARCHAR(100) | Not Null | Frasa / Kolokasi wajib |

### Table: `user_lesson_progress`
| Field | Type | Attributes | Description |
| :--- | :--- | :--- | :--- |
| `id` | INT | Primary Key, Auto Increment | ID Progress |
| `user_id` | INT | Foreign Key (`users.id`) | ID User |
| `lesson_id` | INT | Foreign Key (`lessons.id`) | ID Lesson |
| `status` | ENUM | `'NOT_PASSED'`, `'PASSED'` | Status kelulusan (Default: `'NOT_PASSED'`) |
| `updated_at` | TIMESTAMP | On Update Current Timestamp | Waktu kelulusan |

---

## 3. REST API Endpoints

### A. Fetch All Units & Lessons Status
Mengambil seluruh daftar kurikulum beserta status kelulusan user.

* **HTTP Method**: `GET`
* **Endpoint**: `/api/v1/curriculum`
* **Headers**: `Authorization: Bearer <token>`
* **Response Body**:
```json
{
  "status": "success",
  "data": [
    {
      "unit_id": 12,
      "unit_number": 12,
      "unit_title": "Studies",
      "part": 3,
      "lessons": [
        {
          "lesson_id": 101,
          "lesson_number": 1,
          "lesson_title": "Collocations: foreign language, focus on",
          "difficulty": "Easy",
          "status": "PASSED" // PASSED = Hijau & Centang, NOT_PASSED = Netral
        },
        {
          "lesson_id": 102,
          "lesson_number": 2,
          "lesson_title": "Do you think it's important for people to study languages?",
          "difficulty": "Medium",
          "status": "NOT_PASSED"
        }
      ]
    }
  ]
}
B. Start Practice Session (Get 5 Random Questions)
Mengambil 5 soal acak dari lesson yang dipilih.

HTTP Method: GET

Endpoint: /api/v1/curriculum/lessons/:lesson_id/session

Headers: Authorization: Bearer <token>

Response Body:

JSON
{
  "status": "success",
  "message": "Sesi latihan dimulai",
  "session_id": "SESS-98234712",
  "lesson_id": 101,
  "questions": [
    {
      "question_id": 501,
      "question_text": "Why do many educational systems choose to focus on foreign language learning from a young age?",
      "key_point": "focus on / foreign language"
    },
    {
      "question_id": 504,
      "question_text": "Is it difficult for adults to learn a foreign language compared to children?",
      "key_point": "foreign language"
    }
    // ... total 5 questions
  ]
}

> `GET` ini **membuat record `practice_sessions`** (session stateless tidak lagi — metrik sesi
> diisi saat `complete`). Error: **404** `"Lesson tidak ditemukan."`

### C.1. Evaluate One Answer (Per-Question)
Menilai **satu** jawaban dan menyimpannya ke `curriculum_evaluation_logs` (kelulusan ditentukan di Complete).

* **HTTP Method**: `POST`
* **Endpoint**: `/api/v1/curriculum/lessons/:lesson_id/evaluate-question`
* **Headers**: `Authorization: Bearer <token>`
* **Request Body**:
```json
{
  "session_id": "SESS-98234712",
  "question_id": 501,
  "user_transcript": "In my opinion schools focus on foreign language early because children learn faster."
}
```
* **Response (200 OK)**:
```json
{
  "status": "success",
  "message": "Evaluasi soal tersimpan",
  "data": {
    "question_id": 501,
    "is_correct": true,
    "score": 85,
    "key_point_detected": true,
    "key_point_target": "focus on / foreign language",
    "grammar_feedback": "Sentence structure is correct.",
    "vocabulary_feedback": "Good use of targeted collocations.",
    "suggested_answer": ""
  }
}
```
- `score` per soal = 0–100; `is_correct = true` bila `key_point_detected && score >= 80`.
- Satu `question_id` hanya boleh dievaluasi sekali per `session_id` (ulang → 422).
- Error 404 lesson tidak ditemukan; 422 soal bukan milik lesson / sudah dievaluasi / LLM tidak tersedia.

### C.2. Complete Lesson Session (Finalize Passing Grade)
Menghitung kelulusan dari seluruh jawaban sesi & memperbarui status lesson.

* **HTTP Method**: `POST`
* **Endpoint**: `/api/v1/curriculum/lessons/:lesson_id/complete`
* **Headers**: `Authorization: Bearer <token>`
* **Request Body**: `{ "session_id": "SESS-98234712" }`
* **Response (200 OK)**:
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
        "vocabulary_feedback": "Good use of targeted collocations.",
        "suggested_answer": ""
      }
    ]
  }
}
```
- `is_passed = true` bila `correct_count >= 4`; status hanya naik ke `PASSED` (no-demotion).
- Wajib minimal 1 `evaluate-question` pada sesi (belum ada → 422).
- Efek samping: metrik `practice_sessions` diisi (score avg, `is_passed`, `total/correct_keypoints`) dan `user_progress_predictors` di-*recalculate*.