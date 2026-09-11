# DATABASE SCHEMA: TopSpeak Engine

Dokumen ini adalah **sumber kebenaran** skema database (Laravel Migration) untuk backend **TopSpeak**.
Dibangun di atas MySQL (environment lokal: `C:\laragon\www\topspeak`, database `topspeak`).

---

## 1. Relasi Antar Tabel (Entity Relationship Overview)

```text
[ users ] ─┬───< [ conversation_sessions ] ───< [ conversation_logs ] >─── [ question_banks ]
           ├───< [ conversation_logs ]
           ├───< [ user_level_histories ]
           ├───< [ user_subscriptions ]
           ├───< [ thematic_topics ] (created_by)
           └───< [ user_lesson_progress ] >─── [ lessons ] >─── [ units ]
                                      └─────────>─── [ questions ]

[ grammar_rules ] ───< [ pending_grammar_rules ]
[ vocabulary_bank ]
[ app_configurations ]
[ personal_access_tokens ] (Sanctum)
[ sessions ] (web session)
[ cache / jobs ]
```

Keterangan arah relasi:
- `<` = one-to-many (child FK menunjuk parent).
- `>───` = many-to-one (FK dari child ke parent).
- `conversation_logs.question_id` FK opsional ke `question_banks` (nullable, `onDelete set null`).

---

## 2. Daftar Tabel & Kolom

### 2.1 `users` — Data pengguna (guest/verified/premium/admin)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | auto increment |
| name | string | Nama tampilan (default `Guest Learner`) |
| email | string UNIQUE | `guest-{rand}@topspeak.app` untuk guest |
| email_verified_at | timestamp NULL | |
| password | string | hashed (cast `hashed` di model) |
| device_uuid | string UNIQUE | Unique Device Identifier HP Android |
| phone_number | string NULL UNIQUE | Nomor HP opsional (dipakai payload Duitku `customerPhoneNumber`) |
| current_cefr_level | string(2) default `A1` | `CefrLevel` enum (A1–C2) |
| remaining_trial_sessions | integer default `1` | Kuota Free Tier |
| subscription_status | enum(`FREE`,`PREMIUM_MONTHLY`,`PREMIUM_YEARLY`) default `FREE` | |
| subscription_expires_at | timestamp NULL | Basis `isPremiumActive()` |
| is_admin | boolean (false) | Ditambah migration `add_is_admin` |
| remember_token | string NULL | |
| created_at / updated_at | timestamps | |

### 2.2 `grammar_rules` — Aturan RegEx Sistem Pakar

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| rule_code | string(50) UNIQUE | Contoh: `SVA_01`, `PREP_02` |
| category | string(100) | `Subject-Verb Agreement`, `Past Tense`, dst. |
| cefr_level | string(2) default `A1` | |
| regex_pattern | text | Pola RegEx deteksi error |
| description | text | Penjelasan untuk UI/Tutor |
| is_active | boolean (true) | |
| created_at / updated_at | timestamps | |

Index: `(rule_code, cefr_level)`

### 2.3 `question_banks` — Bank soal

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| test_type | enum(`ADAPTIVE`,`IELTS_SPEAKING`,`TOEFL_IBT`) default `ADAPTIVE` | |
| part_number | integer default `1` | IELTS Part 1-3 / TOEFL Task 1-4 |
| cefr_level | string(2) default `A1` | |
| question_text | text | |
| required_vocab_tags | json | Tag kosa kata untuk skor Fluency |
| is_starter | boolean (false) | Soal pembuka Sesi 1 / Free Tier |
| topic_category | string(100) default `General Conversation` | |
| metadata | json NULL | `audio_url`, reading passage, cue card |
| created_at / updated_at | timestamps | |

Index: `(cefr_level, is_starter, test_type)`

### 2.4 `vocabulary_bank` — Bank kosakata

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| word | string(100) UNIQUE | |
| part_of_speech | string(30) | noun, verb, adjective, adverb |
| cefr_level | string(2) default `A1` | |
| topic_category | string(100) | |
| created_at / updated_at | timestamps | |

Index: `(word, cefr_level, topic_category)`

### 2.5 `thematic_topics` — Skenario roleplay (Thematic Freedom Mode)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| topic_name | string(150) | |
| roleplay_persona | text | Instruksi instruktur AI (HR Manager, Waiter, dst.) |
| selected_level | string(50) default `Beginner` | Beginner→A1, Elementary→A2, dst. |
| context_vocab_tags | json | Tag kata relevan skenario |
| is_active | boolean (true) | |
| created_by | bigint FK `users.id` NULL, `onDelete set null` | |
| created_at / updated_at | timestamps | |

### 2.6 `conversation_sessions` — Header 1 sesi latihan

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid PK | `HasUuids` |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| mode | enum(`ADAPTIVE`,`THEMATIC`,`IELTS_SPEAKING`,`TOEFL_IBT`) default `ADAPTIVE` | IELTS/TOEFL = simulasi ujian |
| topic_id | bigint FK `thematic_topics.id` NULL, `onDelete set null` | Wajib saat THEMATIC |
| start_level | string(2) default `A1` | |
| current_level | string(2) default `A1` | Naik real-time saat promosi |
| total_turns_planned | integer default `5` | |
| status | enum(`ACTIVE`,`COMPLETED`) default `ACTIVE` | |
| completed_at | timestamp NULL | |
| created_at / updated_at | timestamps | |

Index: `(user_id, status, mode)`

### 2.7 `conversation_logs` — Detail turn & evaluasi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| session_id | uuid FK `conversation_sessions.id` | UUID per sesi |
| turn_number | integer | Urutan turn (1..5) |
| question_id | bigint FK `question_banks.id` NULL, `onDelete set null` | |
| user_response_text | text NULL | Hasil transkripsi STT |
| score_word_count | integer default `0` | 1 jika words ≥ threshold level (A1=12, A2=20, B1=35, B2=55, C1=75, C2=90) |
| score_grammar | integer default `0` | 1 jika benar (dinilai LLM) |
| total_turn_score | integer default `0` | Akumulasi 0-2 (word + grammar); fluency dihapus |
| has_error | boolean (false) | |
| user_said_text | text NULL | Bagian kesalahan user |
| correct_way_text | text NULL | Kalimat perbaikan plaintext |
| step_state | enum(`NORMAL`,`WAITING_REPETITION`) default `NORMAL` | `SessionStepState` |
| expected_repetition_text | text NULL | Target pengulangan |
| repetition_attempts | integer default `0` | |
| repetition_success | boolean NULL | |
| created_at / updated_at | timestamps | |

Index: `(user_id, session_id, turn_number)`

### 2.7b `assessment_logs` — Histori evaluasi IELTS/TOEFL speaking

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| test_type | enum(`IELTS`,`TOEFL`) | |
| task_type | string | mis. `SPEAKING_PART_2`, `INDEPENDENT_TASK` |
| prompt_question | text | Soal/pertanyaan |
| user_transcript | text | Hasil STT |
| duration_seconds | integer NULL | Durasi bicara (detik) |
| overall_score | decimal(4,1) | IELTS: Overall Band / TOEFL: Scaled Score 0–30 |
| s_total | decimal(3,2) | Fluency index 0.00–1.00 |
| final_fluency | tinyInteger | Binary 1/0 (backend guard `s_total >= 0.50`) |
| is_on_topic | boolean default `true` | |
| raw_response_json | json | Hasil lengkap LLM |
| created_at / updated_at | timestamps | |

Index: `(user_id, test_type)`

### 2.8 `user_level_histories` — Riwayat kenaikan level

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| session_id | uuid | |
| previous_level | string(2) | |
| new_level | string(2) | |
| trigger_score | integer | Akumulasi 4-turn (≥6, maks 8) |
| promotion_reason | text NULL | Alasan promosi |
| created_at / updated_at | timestamps | |

Index: `(user_id, created_at)`

### 2.9 `pending_grammar_rules` — Self-learning rule (hasil deteksi novel)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| grammar_rule_id | bigint FK `grammar_rules.id` NULL, `onDelete set null` | Terisi saat APPROVED |
| raw_user_input | text | Kalimat asli user (unik untuk idempotensi) |
| detected_error | text | Deskripsi error |
| suggested_regex | text NULL | Regex saran |
| status | enum(`PENDING`,`APPROVED`,`REJECTED`) default `PENDING` | |
| created_at / updated_at | timestamps | |

### 2.10 `user_subscriptions` — Langganan & pembayaran (Duitku)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| status | enum(`FREE`,`PREMIUM_MONTHLY`,`PREMIUM_YEARLY`) default `FREE` | |
| started_at | timestamp NULL | |
| expires_at | timestamp NULL | |
| payment_provider | string(30) NULL | `duitku` |
| payment_ref | string(100) NULL | Reference Duitku |
| is_active | boolean (true) | |
| merchant_order_id | string(64) NULL UNIQUE | Ditambah migration `add_payment_fields` |
| amount | decimal(12,2) default `0` | Harga plan |
| payment_status | enum(`PENDING`,`PAID`,`EXPIRED`,`FAILED`) default `PENDING` | `PaymentStatus` |
| payment_method | string(30) NULL | Kanal Duitku: VA, QR, dst. |
| checkout_url | string(500) NULL | `paymentUrl` dari Duitku |
| created_at / updated_at | timestamps | |

Index: `(user_id, status, is_active)` + unique `(merchant_order_id)`

### 2.11 `app_configurations` — Force update / versi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK (baris tunggal id=1) | |
| latest_app_version | string(20) default `1.0.0` | |
| min_required_version | string(20) default `1.0.0` | |
| is_force_update | boolean (true) | |
| play_store_url | text | |
| update_message | text | |
| created_at / updated_at | timestamps | |

### 2.12 `units` — Level/part kurikulum IELTS (IELTS.md)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| unit_number | integer UNIQUE | Nomor unit (1..12) |
| part | enum(`1`,`2`,`3`) | Bagian IELTS Speaking |
| unit_title | string(150) | Judul tema (mis. `Studies`) |
| outcome | text | Capaian unit |
| created_at / updated_at | timestamps | |

Relasi: one-to-many ke `lessons`.

### 2.13 `lessons` — Pelajaran per unit

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| unit_id | bigint FK `units.id`, `onDelete cascade` | |
| lesson_number | integer | Nomor urut dalam unit |
| lesson_title | string(150) | mis. `Collocations: foreign language, focus on` |
| difficulty | enum(`Easy`,`Medium`,`Difficult`) | `LessonDifficulty` |
| is_active | boolean (true) | lesson draft/archived dikecualikan dari `total_lessons` predictor |
| created_at / updated_at | timestamps | |

Index: `(unit_id, lesson_number)` unique.

Relasi: one-to-many ke `questions`; many-to-many via `user_lesson_progress` ke `users`.

### 2.14 `questions` — Soal latihan per lesson

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| lesson_id | bigint FK `lessons.id`, `onDelete cascade` | |
| question_text | text | Pertanyaan |
| model_answer | text | Jawaban contoh untuk rubrik LLM |
| key_point | string(150) NULL | Target kolokasi/poin kunci (dicek LLM) |
| created_at / updated_at | timestamps | |

Index: `(lesson_id)`.

### 2.15 `user_lesson_progress` — Status kelulusan lesson per user

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| lesson_id | bigint FK `lessons.id`, `onDelete cascade` | |
| status | enum(`NOT_PASSED`,`PASSED`) default `NOT_PASSED` | `LessonProgressStatus` |
| passed_at | timestamp NULL | Saat pertama PASSED |
| created_at / updated_at | timestamps | |

Index: unique `(user_id, lesson_id)`.

### 2.16 `curriculum_evaluation_logs` — Evaluasi per soal kurikulum

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| session_id | string | grup sesi (generated `SESS-XXXXXXXX`, klien kirim ulang saat complete) |
| lesson_id | bigint FK `lessons.id`, `onDelete cascade` | |
| question_id | bigint FK `questions.id`, `onDelete cascade` | |
| user_transcript | text | Transkrip STT jawaban user |
| score | integer | Skor 0–100 (bobot: key_point 50%, grammar 25%, lexical 25%) |
| is_correct | boolean | `key_point_detected && score >= 80` |
| key_point_detected | boolean | Kolokasi target terdeteksi |
| key_point_target | string nullable | Teks key point soal |
| grammar_feedback / vocabulary_feedback | text nullable | Umpan balik AI |
| suggested_answer | text nullable | Jawaban contoh saat salah |

Index: `(user_id)`, `(session_id)`, `(lesson_id)`.

### 2.17 `practice_sessions` — Metrik sesi latihan kurikulum (Progress Predictor)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, `onDelete cascade` | |
| lesson_id | bigint FK `lessons.id`, `onDelete cascade` | |
| session_id | string | sama dengan `curriculum_evaluation_logs.session_id` |
| total_questions | tinyint (0) | jumlah soal (biasanya 5) |
| correct_count | tinyint (0) | soal benar |
| score | decimal(6,2) | rata-rata skor seluruh soal sesi (0–100) |
| is_passed | boolean (false) | `correct_count >= 4` |
| total_keypoints | int (0) | total key point diuji pada sesi |
| correct_keypoints | int (0) | key point terdeteksi benar |
| completed_at | timestamp nullable | saat sesi selesai |
| created_at / updated_at | timestamps | |

Index: unique `(user_id, session_id)`, `(user_id, is_passed)`.

### 2.18 `user_progress_predictors` — Cache Progress Predictor per user

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint PK | |
| user_id | bigint FK `users.id`, unique, `onDelete cascade` | |
| completion_score | decimal(5,2) | `(passed_lessons / total_lessons_aktif) × 100` |
| mastery_score | decimal(5,2) | avg score 10 sesi lulus terakhir |
| accuracy_score | decimal(5,2) | `(correct/total keypoints pada sesi lulus) × 100` |
| overall_index | decimal(5,2) | `completion*0.40 + mastery*0.40 + accuracy*0.20` |
| predicted_band | string(20) | `NEED_MORE_DATA` atau `Band x – y` |
| status_label | string(50) | label band |
| color_code | string(9) | kode warna UI |
| passed_lessons_count | int (0) | jumlah lesson PASSED |
| is_ready | boolean (false) | `passed_lessons_count >= 3` |
| last_calculated_at | timestamp nullable | kalkulasi terakhir |
| created_at / updated_at | timestamps | |

### 2.19 Tabel bawaan Laravel/Sanctum

- **`personal_access_tokens`** (Sanctum): token `id, tokenable_type, tokenable_id, name, token(unique), abilities, last_used_at, expires_at, timestamps` — dibuat migration `create_personal_access_tokens`.
- **`sessions`** (web session auth admin): `id PK, user_id, ip_address, user_agent, payload, last_activity`.
- **`password_reset_tokens`**: `email PK, token, created_at`.
- **`cache` / `jobs`**: bawaan Laravel (`cache` memakai key/value/expiration; `jobs` untuk queue).

---

## 3. Enums (`app/Enums/`)

| Enum | Nilai | Dipakai di |
|------|-------|------------|
| `CefrLevel` | `A1, A2, B1, B2, C1, C2` | `users.current_cefr_level`, `question_banks.cefr_level`, dst. |
| `SessionMode` | `ADAPTIVE, THEMATIC, IELTS_SPEAKING, TOEFL_IBT` | `conversation_sessions.mode` |
| `SessionStepState` | `NORMAL, WAITING_REPETITION` | `conversation_logs.step_state` |
| `SubscriptionStatus` | `FREE, PREMIUM_MONTHLY, PREMIUM_YEARLY` | `users.subscription_status`, `user_subscriptions.status` |
| `PaymentStatus` | `PENDING, PAID, EXPIRED, FAILED` | `user_subscriptions.payment_status` |
| `LessonDifficulty` | `Easy, Medium, Difficult` | `lessons.difficulty` |
| `LessonProgressStatus` | `NOT_PASSED, PASSED` | `user_lesson_progress.status` |

---

## 4. Migrasi (urutan)

1. `0001_01_01_000000_create_users_table` — users + TopSpeak fields + password_reset_tokens + sessions
2. `0001_01_01_000001_create_cache_table`
3. `0001_01_01_000002_create_jobs_table`
4. `2026_01_01_000000_create_topspeak_core_tables` — grammar_rules, question_banks, vocabulary_bank, thematic_topics, conversation_logs, user_level_histories, pending_grammar_rules, user_subscriptions, app_configurations
5. `2026_08_13_053807_create_personal_access_tokens_table` (Sanctum)
6. `2026_08_13_060000_add_is_admin_to_users_table`
7. `2026_08_13_063000_create_conversation_sessions_table`
8. `2026_08_13_070000_add_payment_fields_to_user_subscriptions_table` — merchant_order_id, amount, payment_status, payment_method, checkout_url
9. `2026_08_20_010000_drop_score_fluency_from_conversation_logs` — hapus kolom `score_fluency`
10. `2026_08_21_010000_extend_conversation_sessions_mode` — mode tambah `IELTS_SPEAKING`, `TOEFL_IBT`
11. `2026_08_21_020000_create_assessment_logs_table` — tabel `assessment_logs` (histori evaluasi IELTS/TOEFL)
12. `2026_08_22_010000_create_ielts_curriculum_tables` — `units`, `lessons`, `questions`, `user_lesson_progress` (kurikulum IELTS)
13. `2026_09_02_050000_create_curriculum_evaluation_logs` — `curriculum_evaluation_logs`
14. `2026_09_10_000001_create_practice_sessions_table` — `practice_sessions` (Progress Predictor)
15. `2026_09_10_000002_add_is_active_to_lessons_table` — `lessons.is_active`
16. `2026_09_10_000003_create_user_progress_predictors_table` — `user_progress_predictors` (cache predictor)

> PostgreSQL disebut di dokumen awal, namun environment aktual memakai **MySQL 8.4** (Laragon). Semua tipe di atas sudah diverifikasi terhadap migrasi yang berjalan (`php artisan migrate`).
