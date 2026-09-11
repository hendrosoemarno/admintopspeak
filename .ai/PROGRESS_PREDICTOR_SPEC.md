# Documentation & Specification: Progress Predictor Engine (Backend)

Dokumen ini berisi spesifikasi teknis, rumus matematis, alur kalkulasi, dan skema database untuk fitur **Progress Predictor (IELTS Band Predictor)** pada aplikasi TopSpeak.

---

## 1. Overview & Business Rules

1. **Tujuan**: Memprediksi estimasi IELTS Speaking Band Score pengguna secara dinamis berdasarkan performa latihan.
2. **Triggers**: Perhitungan ulang (*recalculation*) terjadi secara otomatis setiap kali pengguna menyelesaikan 1 sesi latihan (5 soal).
3. **Minimum Data Requirement**: Prediksi IELTS Band baru akan ditampilkan kepada pengguna jika telah menyelesaikan **minimal 3 Lesson unik dengan status `PASSED`**. Jika belum memenuhi, API mengembalikan status `NEED_MORE_DATA`.
4. **Exclusion**: Penilaian aspek *Fluency & Coherence* dihapus total sesuai spesifikasi sistem.

---

## 2. Evaluation Parameters & Scoring Formulas

### A. Per Soal (Single Question Score)
Setiap soal dievaluasi oleh AI Engine menghasilkan skor $S_{question}$ ($0 - 100$):

$$S_{question} = (\text{KeyPointScore} \times 0.50) + (\text{GrammarScore} \times 0.25) + (\text{VocabularyScore} \times 0.25)$$

### B. Per Sesi Latihan (Session Performance)
* Satu sesi terdiri dari **5 soal acak**.
* Status Sesi = **LULUS (`PASSED`)** jika $\ge 4$ soal bernilai benar ($S_{question} \ge 80$).

---

## 3. Progress Predictor Calculation Algorithm

Prediksi dihitung berdasarkan **Overall Index Score (0–100%)** yang menggabungkan 3 komponen berbobot:

$$\text{Overall Index Score} = (\text{CompletionScore} \times 0.40) + (\text{MasteryScore} \times 0.40) + (\text{AccuracyScore} \times 0.20)$$

### Component Breakdown:

#### 1. Unit Completion Rate (Weight: 40%)
Mengukur seberapa jauh cakupan kurikulum yang telah dikuasai pengguna.

$$\text{CompletionScore} = \left( \frac{\text{Total Lesson Berstatus 'PASSED'}}{\text{Total Seluruh Lesson Aktif dalam Aplikasi}} \right) \times 100$$

#### 2. Average Mastery Performance (Weight: 40%)
Mengukur tingkat kemahiran rata-rata pengguna menggunakan *rolling average* dari **10 sesi `PASSED` terakhir** (mencerminkan kemampuan terkini).

$$\text{MasteryScore} = \frac{\sum_{i=1}^{N} \text{SessionScore}_i}{N} \quad \text{dimana } N = \min(\text{Total Sesi Passed}, 10)$$

#### 3. Key Point Accuracy Rate (Weight: 20%)
Mengukur konsistensi ketepatan penggunaan kolokasi/frasa target (*Key Points*) di seluruh attempt latihan.

$$\text{AccuracyScore} = \left( \frac{\text{Total Key Points Benar Terdeteksi}}{\text{Total Key Points Diuji pada Attempt Lulus}} \right) \times 100$$

---

## 4. IELTS Band Mapping Table

Setelah **Overall Index Score** didapatkan, backend melakukan pemetaan (*mapping*) ke estimasi **IELTS Band Score**:

| Overall Index Score (%) | Predicted IELTS Band | Status Label | UI Color Code |
| :--- | :--- | :--- | :--- |
| **90.00 – 100.00** | **Band 7.5 – 8.5** | Expert / Exam Ready | `#10B981` (Green) |
| **78.00 – 89.99** | **Band 6.5 – 7.0** | Competent / Target Achieved | `#22C55E` (Light Green) |
| **65.00 – 77.99** | **Band 5.5 – 6.0** | Modest / Need More Practice | `#EAB308` (Yellow) |
| **50.00 – 64.99** | **Band 4.5 – 5.0** | Limited / Focus on Key Points | `#F97316` (Orange) |
| **< 50.00** | **Band < 4.5** | Beginner / Foundation Level | `#EF4444` (Red) |

---

## 5. Database Schema (PostgreSQL / MySQL)

### Table: `user_progress_predictors`
Menyimpan cache hasil kalkulasi prediksi agar tidak perlu dihitung ulang saat hit API dashboard.

```sql
CREATE TABLE user_progress_predictors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    completion_score DECIMAL(5,2) DEFAULT 0.00,
    mastery_score DECIMAL(5,2) DEFAULT 0.00,
    accuracy_score DECIMAL(5,2) DEFAULT 0.00,
    overall_index DECIMAL(5,2) DEFAULT 0.00,
    predicted_band VARCHAR(20) DEFAULT 'NEED_MORE_DATA',
    status_label VARCHAR(50) DEFAULT 'Need More Data',
    passed_lessons_count INT DEFAULT 0,
    is_ready_for_prediction BOOLEAN DEFAULT FALSE, -- True jika passed_lessons_count >= 3
    last_calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
6. REST API Endpoint
Fetch User Progress Predictor
Mengambil data prediksi skor untuk ditampilkan pada UI Dashboard/Profile.

HTTP Method: GET

Endpoint: /api/v1/user/progress-predictor

Headers: Authorization: Bearer <token>

Response Body (Jika passed_lessons_count >= 3):
JSON
{
  "status": "success",
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
    "last_updated": "2026-09-10T10:30:00Z"
  }
}
Response Body (Jika passed_lessons_count < 3):
JSON
{
  "status": "success",
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
7. Implementation Pseudo-code (Service Layer)
Python
def recalculate_progress_predictor(user_id):
    # 1. Count Passed Lessons
    passed_lessons = db.query(UserLessonProgress).filter_by(user_id=user_id, status='PASSED').count()
    total_lessons = db.query(Lesson).filter_by(is_active=True).count()
    
    if passed_lessons < 3:
        update_predictor_cache(user_id, is_ready=False, passed_count=passed_lessons)
        return

    # 2. Calculate Completion Score (40%)
    completion_score = (passed_lessons / total_lessons) * 100

    # 3. Calculate Mastery Score (40%) - Last 10 Passed Sessions
    last_10_sessions = db.query(PracticeSession)\
                         .filter_by(user_id=user_id, is_passed=True)\
                         .order_by(PracticeSession.created_at.desc())\
                         .limit(10).all()
    
    mastery_score = sum(s.score for s in last_10_sessions) / len(last_10_sessions)

    # 4. Calculate Key Point Accuracy Score (20%)
    total_kp_tested = db.query(func.sum(PracticeSession.total_keypoints)).filter_by(user_id=user_id, is_passed=True).scalar()
    total_kp_correct = db.query(func.sum(PracticeSession.correct_keypoints)).filter_by(user_id=user_id, is_passed=True).scalar()
    
    accuracy_score = (total_kp_correct / total_kp_tested) * 100 if total_kp_tested > 0 else 0

    # 5. Overall Index
    overall_index = (completion_score * 0.40) + (mastery_score * 0.40) + (accuracy_score * 0.20)

    # 6. Map to IELTS Band
    predicted_band, status_label = map_to_ielts_band(overall_index)

    # 7. Save / Update Cache
    save_to_predictor_table(
        user_id=user_id,
        completion=completion_score,
        mastery=mastery_score,
        accuracy=accuracy_score,
        overall=overall_index,
        band=predicted_band,
        label=status_label,
        is_ready=True
    )