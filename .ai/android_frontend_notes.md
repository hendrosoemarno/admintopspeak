# CATATAN FRONTEND ANDROID — Perubahan Scoring & Promosi

Dokumen ini untuk tim Android. Ringkasan perubahan kontrak API yang berdampak
pada aplikasi, hasil dari penghapusan scoring fluency & perubahan window promosi.

---

## 1. Skor per turn: fluency DIHAPUS, maks turn = 2

`POST /sessions/evaluate-turn` → `data.scores` sekarang hanya:

```json
{
  "word_count_score": 0|1,
  "grammar_score": 0|1,
  "total_turn_score": 0..2
}
```

- **`fluency_score` TIDAK ADA lagi.** Hapus dari model/UI jika ditampilkan.
- **Maks per turn = 2** (sebelumnya 3). Semua denominator/progress yang memakai
  `total_turn_score` gunakan skala maks **2** per turn.

Respons error turn (`step_state = WAITING_REPETITION`) sama: `scores` hanya
berisi `word_count_score`, `grammar_score`, `total_turn_score`.

---

## 2. Detail turn: `score_fluency` hilang

`GET /sessions/{id}` → `data.turns[]`:

```json
{
  "turn_number": 1,
  "question_text": "...",
  "user_said_text": "...",
  "correct_way_text": "...",
  "score_word_count": 0,
  "score_grammar": 0,
  "total_turn_score": 0,
  "has_error": true,
  "step_state": "WAITING_REPETITION",
  "repetition_success": null,
  "created_at": "..."
}
```

- Field `score_fluency` **tidak dikirim lagi.**

---

## 3. Riwayat sesi: `avg_fluency` hilang

`GET /sessions/history?per_page=10` → `data.items[]`:

```json
{
  "id": "...",
  "mode": "ADAPTIVE",
  "theme": null,
  "start_level": "A1",
  "current_level": "A2",
  "status": "COMPLETED",
  "turns": 5,
  "total_score": 8,
  "score_pct": 80,
  "avg_grammar_accuracy": 80,
  "started_at": "...",
  "completed_at": "..."
}
```

- Field `avg_fluency` **tidak dikirim lagi.** Hapus kartu/baris UI yang
  menampilkan "Fluency".

---

## 4. Laporan akhir: `fluency_accuracy` hilang

`POST /sessions/complete` → `data.diagnostic_report`:

```json
{
  "grammar_accuracy": "80%",
  "frequent_errors": ["Subject-Verb Agreement"],
  "tutor_notes": "Selamat! Kamu naik level dari A2 ke B1 berkat skor 6/8 pada 4-turn pertama. ..."
}
```

- Field `fluency_accuracy` **tidak dikirim lagi.**

---

## 5. BREAKING: key skor promosi diganti + window 4-turn

`POST /sessions/complete` → `data`:

| Sebelum | Sesudah |
|---|---|
| `accumulated_3turn_score` (maks 6) | **`accumulated_score`** (maks **8**) |
| Promosi bila ≥ 6 dari 3 turn berurutan | Promosi bila **≥ 6 dari 4 turn berurutan terbaik** dalam sesi |

Contoh respons:

```json
{
  "session_id": "...",
  "start_level": "A1",
  "cefr_level_current": "A2",
  "total_turns_completed": 5,
  "accumulated_score": 6,
  "is_promoted": true,
  "previous_cefr_level": "A1",
  "new_cefr_level": "A2",
  "remaining_trial_sessions": 0,
  "diagnostic_report": { "...": "..." }
}
```

**Aksi di Android:**
- Ganti parsing key `accumulated_3turn_score` → `accumulated_score`.
- Label/UI "Skor 3-Turn" → **"Skor 4-Turn"**, denominator `x/6` → **`x/8`**.

---

## 6. Latensi & performa (bonus)

- Per turn kini **hanya 1 panggilan LLM** (grammar saja; fluency tidak
  dievaluasi lagi) → respons `evaluate-turn` jauh lebih cepat (±1–2 dtk).
- Estimasi waktu tunggu di UI bisa diperpendek; timeout 30s tetap aman.

---

## 7. BARU: Simulasi IELTS Speaking & TOEFL iBT

`POST /sessions/start` kini menerima `mode: "IELTS_SPEAKING"` atau `"TOEFL_IBT"`.

**Flow:** sama seperti sesi biasa (start → evaluate-turn → verify-repetition → complete),
tapi:

- Soal dipilih **per part berurutan** (part naik), 2 turn per part
  (`total_turns_planned` = jumlah part × 2).
- **Tidak ada promosi level** — `is_promoted` selalu `false`,
  `previous_cefr_level`/`new_cefr_level` = `null`. Level user tidak berubah.
- Setiap objek pertanyaan (`first_question`, `next_question`) menyertakan
  **`part_number`** (tambahan, tidak mengganggu field lama).

**Start (`mode=IELTS_SPEAKING`):**
```json
{
  "session_id": "...",
  "mode": "IELTS_SPEAKING",
  "total_turns_planned": 4,
  "exam": { "test_type": "IELTS_SPEAKING", "parts": [1, 2] },
  "first_question": { "question_id": 501, "turn_number": 1, "part_number": 1, "question_text": "..." }
}
```

**Complete — tambahan `exam_report`:**
```json
{
  "is_promoted": false,
  "previous_cefr_level": null,
  "new_cefr_level": null,
  "exam_report": {
    "test_type": "IELTS_SPEAKING",
    "total_score": 8,
    "max_score": 8,
    "score_pct": 100,
    "grammar_accuracy": "100%",
    "parts": [
      { "part_number": 1, "turns": 2, "total_score": 4, "max_score": 4, "score_pct": 100 }
    ]
  }
}
```

**History** — `GET /sessions/history` menyertakan `test_type` per item:
`"test_type": "TOEFL_IBT"` (null untuk ADAPTIVE/THEMATIC).

**Aksi di Android:**
- Tambahkan mode `IELTS_SPEAKING` dan `TOEFL_IBT` di picker.
- Tampilkan label part dari `part_number` (mis. "Part 2").
- Pada layar hasil, bila `exam_report` ada → render rincian per part;
  bila tidak, render `diagnostic_report` seperti biasa.

---

## 8. BARU: Assessment Evaluator (band & skala ujian)

Endpoint independen untuk menilai SATU jawaban speaking dengan skor band/skala:

`POST /assessment/evaluate` (auth Bearer)

```json
{
  "test_type": "IELTS",            // atau "TOEFL"
  "task_type": "SPEAKING_PART_2",
  "prompt_question": "Describe a memorable trip...",
  "user_transcript": "I went to Malang...",
  "duration_seconds": 95
}
```

**Respons IELTS** — `data.scores` berisi band:
`fluency_coherence`, `lexical_resource`, `grammatical_range_accuracy`,
`pronunciation_estimate`, `overall_band`, `overall_score` (salinan band).

**Respons TOEFL** — `data.scores` berisi skala:
`delivery`, `language_use`, `topic_development`, `raw_score`,
`scaled_score_30`, `overall_score` (salinan skala 0–30).

Keduanya menyertakan:
- `fluency_matrix.s_total` (0.00–1.00) & `final_fluency` (0/1, dijamin oleh backend)
- `content_alignment.is_on_topic` & `relevance_score`
- `corrections[]` (daftar kesalahan konkret: original/corrected/issue_type/explanation)
- `feedback_summary`

**Aksi di Android:**
- Tambahkan layar/flow "Assessment" untuk mode IELTS/TOEFL terpisah dari sesi latihan.
- Render `overall_score` sebagai skor utama; tampilkan `corrections` sebagai daftar perbaikan.
- Error `422` bila LLM tidak tersedia — tampilkan pesan dari `message`.

---

## 9. BARU: Progress Predictor (IELTS Band Predictor)

Prediksi estimasi IELTS Band Score dari performa latihan kurikulum
(Units/Lessons/Questions). Data sudah dihitung & di-cache backend per user;
perubahan apa pun hanya memperbarui nilai yang dikirim.

### 9.1 Endpoint baru

`GET /user/progress-predictor` (auth Bearer)

### 9.2 Respons (siap — sudah lulus ≥ 3 lesson)

```json
{
  "status": "success",
  "data": {
    "is_ready": true,
    "predicted_band": "Band 6.5 - 7.0",
    "overall_index": 82.40,
    "status_label": "Competent / Target Achieved",
    "color_code": "#22C55E",
    "metrics_breakdown": {
      "completion_rate": { "score": 75.00, "weight": "40%", "passed_lessons": 15, "total_lessons": 20 },
      "mastery_performance": { "score": 88.50, "weight": "40%", "based_on_last_sessions": 10 },
      "key_point_accuracy": { "score": 85.00, "weight": "20%" }
    },
    "last_updated": "2026-09-10T10:30:00+00:00"
  }
}
```

### 9.3 Respons (belum siap — kurang dari 3 lesson lulus)

```json
{
  "status": "success",
  "data": {
    "is_ready": false,
    "predicted_band": "NEED_MORE_DATA",
    "overall_index": 0.0,
    "status_label": "Need More Data",
    "color_code": "#9CA3AF",
    "message": "Selesaikan minimal 3 Lesson untuk melihat prediksi IELTS Band Anda.",
    "passed_lessons_count": 1,
    "required_lessons_count": 3
  }
}
```

### 9.4 Pemetaan band (untuk referensi UI)

| `overall_index` | `predicted_band` | `status_label` | `color_code` |
|---|---|---|---|
| 90+ | Band 7.5 - 8.5 | Expert / Exam Ready | `#10B981` |
| 78–89.99 | Band 6.5 - 7.0 | Competent / Target Achieved | `#22C55E` |
| 65–77.99 | Band 5.5 - 6.0 | Modest / Need More Practice | `#EAB308` |
| 50–64.99 | Band 4.5 - 5.0 | Limited / Focus on Key Points | `#F97316` |
| < 50 | Band < 4.5 | Beginner / Foundation Level | `#EF4444` |

### 9.5 ⚠️ BREAKING: ambang benar per soal kurikulum 60 → 80

- `POST /curriculum/lessons/{id}/evaluate-question` → `data.score` per soal kini
  dianggap **benar (`is_correct = true`) bila `score >= 80`** (sebelumnya `>= 60`),
  dan tetap mensyaratkan `key_point_detected = true`.
- Akibat: jangan memakai `score` sebagai patokan kelulusan di sisi klien; selalu
  baca `is_correct` dari respons. Lesson dinyatakan lulus bila `correct_count >= 4`
  (tidak berubah). Status `PASSED` yang sudah dicapai **tidak pernah turun**.

### 9.6 Aksi di Android

- Tambahkan fetch `/user/progress-predictor` di halaman **Dashboard/Profile**
  (dan/atau setelah `complete` lesson) sebagai penyedia data untuk kartu prediksi.
- Bila `is_ready = false` → tampilkan state "Need More Data" + `message`
  (progress `passed_lessons_count / required_lessons_count`). Jangan tampilkan band.
- Bila `is_ready = true`:
  - Tampilkan `predicted_band`, `overall_index` (2 desimal), `status_label`,
    dan `color_code` sebagai warna aksen/kartu.
  - Opsional: render `metrics_breakdown` (Completion / Mastery / Key Point).
- Simpans/sinkronkan `last_updated` untuk label "diperbarui".
- Tidak ada field baru yang merusak payload kurikulum/sesi lain.

## Ringkas (semua breaking untuk Android)

| Endpoint | Field | Perubahan |
|---|---|---|
| evaluate-turn | `scores.fluency_score` | **dihapus** |
| evaluate-turn | `scores.total_turn_score` | maks **2** (bukan 3) |
| sessions/{id} | `turns[].score_fluency` | **dihapus** |
| sessions/history | `items[].avg_fluency` | **dihapus** |
| sessions/complete | `diagnostic_report.fluency_accuracy` | **dihapus** |
| sessions/complete | `accumulated_3turn_score` | **ganti → `accumulated_score`** |
| sessions/complete | `accumulated_score` | maks **8**, window **4-turn**, promosi ≥ 6 |
| curriculum evaluate-question | `data.is_correct` | kini `true` bila `score >= 80` (sebelumnya 60) — jangan pakai `score` sebagai patokan |