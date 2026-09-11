# Documentation & API Specification (Backend)

Dokumen ini berisi spesifikasi arsitektur, skema database, logika bisnis, dan REST API endpoints untuk fitur IELTS Speaking Practice.

---

## 1. Core App Logic & Rules

1. **Akses Open-Ended**: Semua Unit dan Lesson terbuka secara default (`is_locked = false`).
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
   - Jika LULUS $\rightarrow$ Status lesson di-update menjadi `PASSED` (Centang Hijau).
   - Jika GAGAL $\rightarrow$ Status lesson tetap `NOT_PASSED`. Pengguna dapat mengulang sesi dengan 5 soal acak baru.

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

Endpoint: /api/v1/lessons/:lesson_id/session

Headers: Authorization: Bearer <token>

Response Body:

JSON
{
  "status": "success",
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
C. Evaluate Answer & Finalize Session
Mengirim transkrip audio jawaban siswa untuk dievaluasi oleh AI dan memperbarui status lesson.

HTTP Method: POST

Endpoint: /api/v1/lessons/:lesson_id/evaluate

Headers: Authorization: Bearer <token>

Request Body:

JSON
{
  "session_id": "SESS-98234712",
  "answers": [
    {
      "question_id": 501,
      "user_transcript": "In my opinion schools focus on foreign language early because children learn faster."
    },
    {
      "question_id": 504,
      "user_transcript": "Yes adults face obstacles but they can learn foreign language if they practice."
    }
    // ... total 5 answers
  ]
}
Response Body:

JSON
{
  "status": "success",
  "session_result": {
    "correct_count": 4,
    "total_questions": 5,
    "is_passed": true, // true jika correct_count >= 4
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