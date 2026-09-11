# LLM ASSESSMENT RULES: Grammar

Dokumen ini menjelaskan penilaian **Grammar** yang kini dilakukan langsung oleh
**LLM** (DeepSeek, OpenAI-compatible) menggantikan pendekatan RegEx
berbasis `grammar_rules` / filler-ratio sebelumnya.

**Scoring fluency (LLM maupun penilaian percakapan) dihapus total.**

---

## 1. Penilaian Grammar (LlmGrammarEvaluator)

**File:** `app/Services/Llm/LlmGrammarEvaluator.php`

Setiap transkrip user dikirim ke LLM untuk dinilai kebenaran grammar-nya.

### Prompt Inti
- LLM menerima kalimat learner dan **konteks pertanyaan**, lalu menentukan
  `is_correct` (true/false).
- Bila salah, LLM juga menghasilkan `corrected_sentence` (versi benar) dan
  `error_description` (penjelasan kesalahan).
- LLM **diinstruksikan MENGABAIKAN masalah format**: kapitalisasi (termasuk
  huruf "i" kecil), tanda baca hilang, atau titik di akhir — karena artefak
  STT/typing, bukan kesalahan grammar.
- `is_correct = false` untuk kesalahan nyata: tense salah, subject-verb
  disagreement, urutan kata, preposisi, pluralisasi, article, maupun
  **kalimat fragment / tidak lengkap / tidak koheren** (mis. "my mind will be")
  meskipun secara gramatikal tidak ada "error klasik".
- Kalimat pendek tapi **utuh dan koheren** (mis. "My hobby is reading.") tetap
  dianggap benar — panjang/kosakata/style bukan kriteria.

### Output JSON
```json
{
  "is_correct": false,
  "corrected_sentence": "Yesterday I went to school.",
  "error_description": "The verb 'go' should be in the past tense 'went'."
}
```

### Pemetaan Skor
- `is_correct = true`  → `grammar_score = 1`, `has_error = false`
- `is_correct = false` → `grammar_score = 0`, `has_error = true`,
  `violations = [{ rule_code: "LLM", description: error_description }]`

### Fallback & Kegagalan
- LLM tidak aktif / gagal / timeout / JSON tidak valid
  → `GrammarEvaluationUnavailableException` → **HTTP 422** (evaluasi diblokir).
- Bila `corrected_sentence` kosong padahal salah → fallback ke
  `standard_answer` soal untuk `correct_way_text`.

---

## 2. Kalimat Fallback / Hedging (Tidak Dinilai)

Kalimat fallback/hedeging **tidak dinilai** — langsung diberi
`grammar_score = 0` **tanpa memanggil LLM** (hemat biaya & latensi).

**Deteksi** (`AdaptiveLevelingEngine::isFallbackResponse`):
- `i don't know`, `i do not know`, `i don't understand`, `i do not understand`
- `i have no idea`, `i don't remember`, `i do not remember`, `i forgot`
- `i am not sure`, `i am not certain`
- `something like that`, `just saying/guessing`
- Transkrip kosong / hanya spasi

**Hasil:** `word_count = 0`, `grammar = 0`, `has_error = true`,
`violations = [{ rule_code: "FALLBACK" }]`, `total_turn_score = 0`.

---

## 3. Mesin Skor (AdaptiveLevelingEngine)

**File:** `app/Services/Engine/AdaptiveLevelingEngine.php`

Skor per-turn = **Word Count (0/1) + Grammar LLM (0/1)** → total **0-2 / turn**.

| Komponen | Sumber | Skor 1 | Skor 0 |
|----------|--------|--------|--------|
| Word Count | `str_word_count` vs threshold per level (A1=12, A2=20, B1=35, B2=55, C1=75, C2=90) | `>= threshold` | `< threshold` |
| Grammar | `LlmGrammarEvaluator` | `is_correct` true (kalimat utuh & benar) | `is_correct` false (error grammar ATAU fragment/tidak koheren) |

- `grammar_rules` (RegEx) **tidak lagi** digunakan untuk penilaian grammar.
- Self-learning capture ke `pending_grammar_rules` dari ketidakcocokan RegEx
  **dihentikan** (penilaian langsung oleh LLM).
- LLM tidak tersedia → evaluasi turn diblokir (HTTP 422).

### Latensi
- Per turn hanya **SATU panggilan LLM** (grammar saja) — tidak ada paralelisme
  multi-panggilan; latensi nyata turn ±1–2 dtk.
- Timeout per panggilan LLM = **15 dtk**, jauh di bawah timeout 30 dtk client
  Android.
- Fallback ketahanan: jika LLM gagal/timeout → `GrammarEvaluationUnavailableException`
  → **HTTP 422** cepat (bukan menggantung hingga timeout client).

---

## 4. Promosi Level

- Kenaikan level **hanya dievaluasi saat sesi selesai** (`completeSession`), bukan
  langsung saat `evaluate-turn`.
- Basis penilaian: **4 turn BERURUTAN mana pun** dalam sesi yang nilainya terbaik.
  Jendela digeser per turn (turn 1-4, 2-5, dst) lalu diambil nilai maksimumnya.
- Nilai terbaik harus `>= 6` (maks 2/turn → 4 turn maksimal = **8**) agar level
  naik satu tingkat ($A1 \rightarrow A2 \rightarrow B1 \rightarrow B2 \rightarrow C1 \rightarrow C2$).
- Contoh: skor turn `[0, 0, 2, 2, 2]` → window 2-5 = 6 → promosi.