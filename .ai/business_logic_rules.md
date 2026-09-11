# BUSINESS LOGIC & ALGORITHM RULES: TopSpeak Engine

Dokumen ini berisi aturan bisnis, logika algoritma penentuan level, mekanisme interupsi pengulangan (*Repetition Loop*), dan manajemen kuota/pembayaran untuk backend TopSpeak.

> **Catatan penilaian**: Penilaian **Grammar** kini dilakukan langsung oleh
> **LLM**. Scoring **Fluency dihapus total**. Detail rubric & formula ada di
> [`LLM_ASSESSMENT_RULES.md`](./LLM_ASSESSMENT_RULES.md). Section 1 di bawah
> adalah ringkasan.

---

## 1. Adaptive Leveling Algorithm (4-Turn Rolling Window)

Sistem penentuan level tidak menggunakan nilai rata-rata keseluruhan, melainkan evaluasi dinamis berbasis 4 jawaban berturut-turut.

### A. Komponen Penilaian Turn (Skor 0–2 Poin)
Setiap *turn* percakapan dievaluasi berdasarkan 2 parameter:
1. **Word Count Score (0/1):**
   - Skor `1` jika jumlah kata pada transkrip user `>= threshold` level CEFR (A1=12, A2=20, B1=35, B2=55, C1=75, C2=90).
   - Skor `0` jika `< threshold`.
2. **Grammar Accuracy Score (0/1):**
   - Dinilai langsung oleh **LLM** (`LlmGrammarEvaluator`).
   - Skor `1` jika kalimat dinilai benar (`is_correct = true`); skor `0` jika ada kesalahan.
   - LLM juga menghasilkan `corrected_sentence` untuk `correct_way`.

$$\text{Total Turn Score} = \text{Word Score} + \text{Grammar Score} \quad (\text{Skor Max} = 2)$$

### B. Algoritma Promosi Level (Kenaikan Level)
- Evaluasi dilakukan pada **4 turn berurutan terbaik** dalam sesi (jendela digeser per turn: 1-4, 2-5, dst):
  $$\text{Accumulated Score} = \text{best sum 4 consecutive turns} \quad (\text{Skor Max} = 8)$$
- **Kondisi Promosi:** Jika $\text{Accumulated Score} \ge 6$:
  1. Set flag `is_promoted = true`.
  2. Perbarui `users.current_cefr_level` ke tingkat berikutnya ($A1 \rightarrow A2 \rightarrow B1 \rightarrow B2 \rightarrow C1 \rightarrow C2$).
  3. Catat riwayat perubahan ke tabel `user_level_histories`.
- **Waktu evaluasi:** Promosi **hanya dievaluasi saat sesi selesai** (`completeSession`), bukan langsung saat `evaluate-turn`. Selama sesi berjalan level tetap.

### C. Prinsip No-Demotion (Tanpa Penurunan Level)
- Jika pada Turn 3 atau akhir sesi $\text{Accumulated Score} < 6$:
  1. Level CEFR pengguna pada `users.current_cefr_level` **TIDAK BOLEH DITURUNKAN**.
  2. Sistem masuk ke mode **Sub-Level Stabilization**: Pertanyaan pada sesi berikutnya tetap di level yang sama dengan menyajikan variasi soal yang lebih bervariasi.

---

## 1b. Simulasi Ujian IELTS Speaking & TOEFL iBT

Mode ini **bukan adaptive leveling** — tidak ada promosi/penurunan level.

- **Mode baru:** `SessionMode::IELTS_SPEAKING`, `SessionMode::TOEFL_IBT`.
- **Pemilihan soal:** dari `question_banks` dengan `test_type` sesuai mode,
  **berurutan per `part_number`** naik. Setiap part menyumbang
  `EXAM_TURNS_PER_PART = 2` turn; `total_turns_planned = jumlah part × 2`.
- **Level:** `start_level`/`current_level` diambil dari level user saat ini
  (hanya informatif; threshold kata tetap memakai `cefr_level` soal).
- **Scoring:** tetap word count (0/1) + grammar LLM (0/1) per turn, maks 2/turn.
- **Complete:** `is_promoted` selalu `false`, `previous_cefr_level` dan
  `new_cefr_level` = `null`. Respons menyertakan **`exam_report`**:
  skor total, maks, persen, akurasi grammar, dan rincian per part
  (`part_number`, `turns`, `total_score`, `max_score`, `score_pct`).
- **History:** setiap item menyertakan `test_type` (`IELTS_SPEAKING` /
  `TOEFL_IBT`; `null` untuk ADAPTIVE/THEMATIC).

---

## 1c. Kurikulum IELTS Tertata (Units / Lessons / Questions)

Fitur kurikulum (spesifikasi `IELTS.md`) menyediakan latihan berstruktur
**Unit → Lesson → Question** per part IELTS Speaking (Part 1, 2, 3).

### A. Struktur & Alur Sesi
1. **Unit** (1..12, 4 per part) berisi **Lesson**; tiap Lesson ≥ 5 soal.
2. `GET /curriculum` menampilkan seluruh unit + lesson + status per user.
3. `GET /curriculum/lessons/{id}/session` mengambil **5 soal acak** milik lesson
   (`QuestionRepository::randomForLesson`, limit `SESSION_QUESTION_LIMIT = 5`)
   dan **membuat record `practice_sessions`** (`session_id` acak `SESS-XXXXXXXX`).
4. `POST /curriculum/lessons/{id}/evaluate-question` menilai **satu** jawaban
   (`session_id` + `question_id` + `user_transcript`) dan menyimpannya ke
   `curriculum_evaluation_logs`. `question_id` **wajib** milik lesson tersebut dan
   hanya boleh dievaluasi **sekali per sesi** (selain itu **422**).
5. `POST /curriculum/lessons/{id}/complete` (body `session_id`) menggabungkan hasil
   evaluasi sesi, menentukan kelulusan (`correct_count >= 4`), memperbarui
   `user_lesson_progress`, mengisi metrik `practice_sessions` (score rata-rata,
   `is_passed`, `total/correct_keypoints`), lalu memantik
   `ProgressPredictorService::recalculate`.

### B. Grading per Soal (LLM, rubric `ielts_lesson_evaluator.txt`)
| Dimensi | Bobot |
|---------|-------|
| Key Point Checklist | 50% |
| Grammatical Accuracy | 25% |
| Lexical Resource | 25% |

- Evaluasi **paralel** per jawaban via `LlmClient::chatJsonMany` (prompt `{question_text}`, `{model_answer}`, `{key_point}` di-substitusi).
- `score` = sum(skor dimensi × bobot) × 100, clamp 0–1 tiap dimensi.
- `is_correct = true` bila **`key_point_detected`** DAN `score >= 80`.

### C. Kelulusan Lesson
- **Passing grade 4 dari 5 soal** (`REQUIRED_CORRECT = 4`) → `PASSED`.
- Gagal (< 4) → `NOT_PASSED`.
- **No-Demotion:** status yang sudah `PASSED` **tidak pernah diturunkan**
  (re-take yang gagal tetap mempertahankan `PASSED`).
- Status disimpan via `UserLessonProgressRepository::updateOrCreateStatus`
  per `(user_id, lesson_id)` unik.

---

## 2. Fitur Interupsi Pengulangan (*Repetition Loop / Say Again*)

Digunakan untuk melatih ingatan otot (*muscle memory*) pengucapan pengguna tanpa merusak alur dialog percakapan.

### A. Pemicu Kesalahan (Turn Pertama)
1. Ketika Sistem Pakar mendeteksi kesalahan *grammar* atau kosa kata (`has_error = true`):
   - Sistem tidak langsung menyajikan pertanyaan baru.
   - Sistem menyusun teks koreksi *plaintext*: `user_said` dan `correct_way`.
   - Backend mengubah status percakapan menjadi `step_state = 'WAITING_REPETITION'`.
   - Audio AI merespons dengan prompt instruksi:
     > *"I see, that's not correct! Please say again: '[correct_way_text]'"*

### B. Evaluasi Pengulangan (Say Again Step)
Saat `step_state = 'WAITING_REPETITION'`, transkrip pengguna berikutnya dievaluasi terhadap `expected_repetition_text`:
1. **Skenario A (Pengulangan Berhasil):**
   - Transkrip pengguna cocok dengan `expected_repetition_text` (kemiripan $\ge 80\%$).
   - AI merespons dengan kalimat transisi netral: *"Alright, got it. Now, [next_question]?"*
   - Ubah `step_state` kembali ke `'NORMAL'` dan sajikan pertanyaan berikutnya.
2. **Skenario B (Pengulangan Masih Salah / Gagal):**
   - Transkrip pengguna tidak cocok.
   - **TIDAK BOLEH memuji (*"Great job!"*) dan TIDAK BOLEH mengunci pengguna dalam loop abadi.**
   - AI merespons dengan kalimat toleransi netral: *"Let's move on. Now, [next_question]?"*
   - Catat kegagalan pengulangan pada log, ubah `step_state` kembali ke `'NORMAL'`, lalu paksakan lanjut ke pertanyaan berikutnya.

---

## 3. Sistem Kuota & Akses (Free Tier vs Paid Subscriber)

### A. Alur Kuota Pengguna
1. **Status Guest (Unverified Device):**
   - Menerima kuota awal dari `AppConfiguration::initialFreeSessions()`
     (default **1** Sesi, `remaining_trial_sessions = 1`); admin dapat mengubah
     di App Config (Free Tier Settings).
2. **Pengurangan Kuota:**
   - Kuota `remaining_trial_sessions` berkurang 1 setiap kali pengguna
     menyelesaikan *Turn 1* pada sebuah sesi baru.
   - Premium aktif (`users.isPremiumActive()`) → kuota tidak dikurangi.
3. **Dihapus:** Fitur verifikasi WhatsApp/OTP (klaim bonus +4 sesi) **tidak ada lagi**
   sejak migrasi `2026_09_07_000003_drop_wa_verification_fields.php`.
   Tidak ada mekanisme penambahan kuota berbasis OTP/WhatsApp.

### B. Pemicu Paywall (Quota Exhausted)
- Jika `remaining_trial_sessions = 0` dan `subscription_status = 'FREE'`:
  - Request API `POST /sessions/start` akan menolak inisialisasi sesi baru dan mengembalikan **HTTP 402 Payment Required** (kode error `402`, lihat `api_contracts.md`) dengan payload penawaran *Paywall/Subscription*:
    ```json
    {
      "status": "error",
      "errors": { "is_paywalled": true, "remaining_trial_sessions": 0, "subscription_status": "FREE" }
    }
    ```
- **Free Tier Promotion Hook:** Pengguna *Free Tier* dapat merasakan naik level di tengah sesi gratis, namun akses untuk memulai sesi baru di level B1/B2 berikutnya akan terkunci oleh Paywall.

---

## 4. Keamanan & Control Version (Force Update)

1. Pada *Splash Screen*, aplikasi mengirimkan versi lokalnya ke `GET /app-version`.
2. Backend membandingkan `current_app_version` dengan `min_required_version` di tabel `app_configurations`:
   - Jika `current_app_version < min_required_version` AND `is_force_update = true`:
     Backend mengembalikan instruksi penguncian UI aplikasi secara penuh.
3. **DeviceUUID Tracking:** Setiap `device_uuid` hanya berhak mendapatkan kuota *Guest* (1 Sesi) sebanyak 1 kali untuk mencegah *abuse* registrasi ganda.
   - *Guest (1 sesi)* dijamin oleh `firstOrCreateByDeviceUuid` (per `device_uuid` unik); jumlah sesi awal dapat diubah admin di halaman App Config (Free Tier Settings).
EOF