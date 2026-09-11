# PROJECT OVERVIEW: TopSpeak Backend API & System Engine

## 1. Executive Summary & Vision
**TopSpeak** adalah platform pembelajaran bahasa Inggris lisan (Speaking) berbasis AI dan Sistem Pakar Adaptif (Adaptive Expert System). Backend ini dibangun menggunakan **Laravel (PHP)** dan **MySQL 8.4** (Laragon) untuk menyediakan REST API bagi aplikasi **Android Native (Kotlin)** dan **Web Admin Dashboard**.

Platform ini mengadopsi standar internasional **CEFR (A1–C2)**, menggunakan pendekatan **Multi-Component Scoring (4-Turn Rolling Window)**, serta menerapkan prinsip psikologis **No-Demotion** (tanpa penurunan level permanen).

---

## 2. Tech Stack & Standards
- **Framework & Language:** Laravel 11 / PHP 8.3+
- **Database:** MySQL 8.4 (Laragon)
- **Architecture Pattern:** Controller -> Service Layer -> Repository Pattern -> API Resource
- **API Standard:** RESTful API (JSON Payload & Responses)
- **Validation:** Strict Form Request Classes (`app/Http/Requests/`)
- **State Management & Types:** PHP Enums (`app/Enums/`)
- **Authentication:** Laravel Sanctum (Token-based)
- **Payment Gateway:** Duitku Integration

---

## 3. Core Architecture & System Features

### A. Adaptive Leveling Engine (4-Turn Rolling Window)
1. **Evaluasi Per Turn (Skor 0–2 Poin):**
   - **Word Count ($\ge$ threshold level CEFR):** +1 Poin
   - **Grammar Accuracy (dinilai LLM):** +1 Poin
   (Scoring Fluency dihapus.)
2. **Kenaikan Level:**
   - Dihitung dari skor 4 jawaban berurutan terbaik dalam sesi (jendela digeser per turn, maks **8**).
   - Jika $\text{Total Skor} \ge 6$: Pemicu promosi level pada akhir sesi (*promote one level*).
   - Jika $\text{Total Skor} < 6$: Penahanan sub-level (Stabilization mode).
3. **Prinsip No-Demotion:** Level CEFR permanen di `users.current_cefr_level` **tidak boleh diturunkan** meskipun skor Sesi/Turn $< 6$.

### B. Interupsi Pengulangan (*Say Again / Repetition Loop*)
1. Jika Sistem Pakar mendeteksi kesalahan grammar/kata pada ucapan user:
   - Backend membalas payload dengan `has_error = true` beserta `correct_way_text`.
   - Menyetel status percakapan `step_state = 'WAITING_REPETITION'`.
2. AI Tutor menginstruksikan user untuk mengulang (*"I see, that's not correct! Please say again..."*).
3. Baik pengulangan user berhasil (*match*) maupun gagal, AI akan merespons dengan kalimat konfirmasi netral (*"Alright, got it"* / *"Let's move on"*) dan **TIDAK Boleh menggunakan kata "Great job!" saat respons salah**. Setelah itu, `step_state` kembali ke `'NORMAL'`.

### C. Sistem Kuota & Paywall (Free Tier vs Premium)
- **Mode Guest:** Mendapatkan kuota awal dari `AppConfiguration::initialFreeSessions()`
  (default 1 Sesi, `remaining_trial_sessions = 1`; dapat diubah admin).
- **Verifikasi WhatsApp dihapus:** Klaim bonus sesi via OTP WhatsApp tidak ada lagi
  (migrasi `2026_09_07_000003_drop_wa_verification_fields.php`).
- **Free Tier Session Behavior:** User *Free Tier* dapat merasakan naik level secara *real-time* di tengah sesi (Turn 1–5), namun akses ke Sesi berikutnya di Level B1 terkunci oleh *Paywall*.
- **Subscriber (Premium):** Bebas memilih seluruh level CEFR dan skenario *Thematic Freedom Mode*.

### D. Security & Force Update Control
- Pengecekan versi aplikasi di *Splash Screen* via endpoint `GET /api/v1/app-version`.
- Jika `current_app_version < min_required_version` dan `is_force_update = true`, aplikasi mobile mengunci UI secara total.

---

## 4. Database Core Entities & Enums

### Key Database Tables
- `users`: Data pengguna, `current_cefr_level`, `remaining_trial_sessions`.
- `grammar_rules`: Aturan RegEx untuk Sistem Pakar Lokal & Server (`rule_code`, `regex_pattern`, `category`, `cefr_level`).
- `question_banks`: Bank soal utama (`cefr_level`, `question_text`, `required_vocab_tags`, `is_starter`).
- `thematic_topics`: Skenario roleplay untuk *Thematic Freedom Mode*.
- `conversation_logs`: Log detail interaksi *turn* percakapan, transkripsi STT, dan evaluasi.
- `app_configurations`: Pengaturan `latest_app_version`, `min_required_version`, dan `play_store_url`.

### Standard Enums (`app/Enums/`)
```php
// CefrLevel.php
enum CefrLevel: string {
    case A1 = 'A1';
    case A2 = 'A2';
    case B1 = 'B1';
    case B2 = 'B2';
    case C1 = 'C1';
    case C2 = 'C2';
}

// SessionStepState.php
enum SessionStepState: string {
    case NORMAL = 'NORMAL';
    case WAITING_REPETITION = 'WAITING_REPETITION';
}