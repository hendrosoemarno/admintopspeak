# Backend Specification: IELTS & TOEFL iBT Speaking Evaluator Engine

## 1. Overview
Dokumen ini mendefinisikan spesifikasi backend Laravel untuk mengevaluasi latihan percakapan (Speaking) standar **IELTS** dan **TOEFL iBT**. 

Sistem ini menggunakan **Pure LLM Evaluator** dengan pengaturan *temperature* deterministik (`0.0`) dan *Structured JSON Output*. Aturan tata bahasa (*grammar rules*) serta pencocokan pola (*regex*) lama telah sepenuhnya digantikan oleh kemampuan *contextual analysis* dari LLM.

---

## 2. API Endpoint Definition

### `POST /api/v1/assessment/evaluate`

Endpoint utama yang dipanggil oleh aplikasi Android untuk mengirimkan transkripsi jawaban pengguna.

#### **Request Headers**
| Header | Value |
| :--- | :--- |
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |
| `Authorization` | `Bearer {user_token}` |

#### **Request Body Payload**
```json
{
  "test_type": "IELTS",
  "task_type": "SPEAKING_PART_2",
  "prompt_question": "Describe a memorable trip you took in the past year.",
  "user_transcript": "I want to talk about my visit to Malang last year. It was very nice because I go with my family...",
  "duration_seconds": 95
}
Field Constraints & Validation Rules:test_type: Required. Enum ["IELTS", "TOEFL"].task_type: Required. String (misal: "SPEAKING_PART_1", "SPEAKING_PART_2", "INDEPENDENT_TASK").prompt_question: Required. String teks soal/pertanyaan.user_transcript: Required. String hasil transkripsi ucapan (STT).duration_seconds: Optional. Integer durasi waktu berbicara dalam detik.3. System Prompts & Rubric SpecificationsBackend Laravel menyusun System Prompt secara dinamis berdasarkan parameter test_type.A. IELTS System Prompt (IELTS_ENGINE)PlaintextYou are an official IELTS Speaking Examiner. Evaluate the candidate transcript based on official IELTS Band Descriptors (Scale 1.0 - 9.0):

1. Fluency and Coherence (FC) [1.0 - 9.0]: Flow, pacing, lack of hesitation/filler words, logical idea connections.
2. Lexical Resource (LR) [1.0 - 9.0]: Vocabulary variety, precision, collocations, natural phrasing.
3. Grammatical Range and Accuracy (GRA) [1.0 - 9.0]: Sentence structure variety and grammatical error frequency.
4. Pronunciation Estimate (PD) [1.0 - 9.0]: Estimated clarity and rhythm based on transcript flow and pauses.

PROMPT / QUESTION:
"{prompt_question}"

CANDIDATE TRANSCRIPT:
"{user_transcript}"

EVALUATION & CALCULATION RULES:
- Overall Band = Arithmetic average of FC, LR, GRA, and PD, rounded to the nearest half-band (e.g., 6.25 -> 6.5, 6.75 -> 7.0).
- Fluency Index (s_total) = Floating number between 0.00 and 1.00 calculated strictly from FC performance.
- Binary Threshold Rule: If s_total >= 0.50 then final_fluency = 1, else 0.
- Extract concrete grammar, lexical, or phrasing errors into the 'corrections' array.

OUTPUT FORMAT:
Return strictly valid JSON matching this schema:
{
  "scores": {
    "fluency_coherence": 6.0,
    "lexical_resource": 6.5,
    "grammatical_range_accuracy": 5.5,
    "pronunciation_estimate": 6.0,
    "overall_band": 6.0
  },
  "fluency_matrix": {
    "s_total": 0.65,
    "final_fluency": 1
  },
  "content_alignment": {
    "is_on_topic": true,
    "relevance_score": 0.90
  },
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
B. TOEFL iBT System Prompt (TOEFL_ENGINE)PlaintextYou are an ETS TOEFL iBT Speaking Evaluator. Evaluate the candidate response based on official ETS Rubrics (Scale 0 to 4):

1. Delivery [0 - 4]: Pacing, flow, and absence of unnatural disfluency.
2. Language Use [0 - 4]: Grammar accuracy, range, and vocabulary precision.
3. Topic Development [0 - 4]: Coherence, completeness, and prompt alignment.

PROMPT / QUESTION:
"{prompt_question}"

CANDIDATE TRANSCRIPT:
"{user_transcript}"

EVALUATION & CALCULATION RULES:
- Raw Score = Arithmetic average of Delivery, Language Use, and Topic Development (Scale 0.0 - 4.0).
- Scaled Score = Official TOEFL 0 - 30 points conversion based on Raw Score.
- Fluency Index (s_total) = Floating number between 0.00 and 1.00 derived from Delivery score.
- Binary Threshold Rule: If s_total >= 0.50 then final_fluency = 1, else 0.
- Extract concrete grammar, lexical, or phrasing errors into the 'corrections' array.

OUTPUT FORMAT:
Return strictly valid JSON matching this schema:
{
  "scores": {
    "delivery": 3,
    "language_use": 2,
    "topic_development": 3,
    "raw_score": 2.67,
    "scaled_score_30": 20
  },
  "fluency_matrix": {
    "s_total": 0.68,
    "final_fluency": 1
  },
  "content_alignment": {
    "is_on_topic": true,
    "relevance_score": 0.85
  },
  "corrections": [
    {
      "original": "I go with my family",
      "corrected": "I went with my family",
      "issue_type": "Grammar (Tense)",
      "explanation": "Use past simple 'went' when describing a completed trip in the past."
    }
  ],
  "feedback_summary": "Clear response with good topic development. Needs improvement in grammatical accuracy."
}
4. Laravel Service ImplementationClass: App\Services\IeltsToeflEvaluatorPHPnamespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IeltsToeflEvaluator
{
    public function evaluate(array $data): array
    {
        $systemPrompt =$data['test_type'] === 'IELTS' 
            ? $this->buildIeltsPrompt($data) 
            : $this->buildToeflPrompt($data);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.llm.key'),
                'Content-Type' => 'application/json',
            ])->post(config('services.llm.endpoint'), [
                'model' => config('services.llm.model', 'gpt-4o-mini'),
                'temperature' => 0.0, // Strict determinism
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $data['user_transcript']],
                ],
            ]);

            $result = json_decode($response->json('choices.0.message.content'), true);

            // Backend Safety Guard: Force binary threshold evaluation
            $sTotal =$result['fluency_matrix']['s_total'] ?? 0.0;
            $result['fluency_matrix']['final_fluency'] =$sTotal >= 0.50 ? 1 : 0;

            return $result;

        } catch (\Exception $e) {
            Log::error('IELTS/TOEFL Evaluation Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function buildIeltsPrompt(array $data): string
    {
        return str_replace(
            ['{prompt_question}', '{user_transcript}'],
            [$data['prompt_question'],$data['user_transcript']],
            file_get_contents(resource_path('prompts/ielts_evaluator.txt'))
        );
    }

    private function buildToeflPrompt(array $data): string
    {
        return str_replace(
            ['{prompt_question}', '{user_transcript}'],
            [$data['prompt_question'],$data['user_transcript']],
            file_get_contents(resource_path('prompts/toefl_evaluator.txt'))
        );
    }
}
5. Database Migration SchemaTabel assessment_logs merekam seluruh riwayat evaluasi pengguna.PHPuse Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_logs', function (Blueprint $table) {$table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');$table->enum('test_type', ['IELTS', 'TOEFL']);
            $table->string('task_type');$table->text('prompt_question');
            $table->text('user_transcript');$table->integer('duration_seconds')->nullable();
            
            // Score Fields
            $table->decimal('overall_score', 4, 1); // Overall Band (IELTS) / Scaled Score (TOEFL)$table->decimal('s_total', 3, 2);       // Fluency decimal index (0.00 - 1.00)
            $table->tinyInteger('final_fluency');   // Binary (1 or 0)$table->boolean('is_on_topic')->default(true);
            
            // Full Raw LLM Result
            $table->json('raw_response_json');$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_logs');
    }
};
6. Response JSON Examples (Laravel $\rightarrow$ Android)Example Response (IELTS Mode):JSON{
  "status": "success",
  "data": {
    "test_type": "IELTS",
    "scores": {
      "fluency_coherence": 6.0,
      "lexical_resource": 6.5,
      "grammatical_range_accuracy": 5.5,
      "pronunciation_estimate": 6.0,
      "overall_band": 6.0
    },
    "fluency_matrix": {
      "s_total": 0.65,
      "final_fluency": 1
    },
    "content_alignment": {
      "is_on_topic": true,
      "relevance_score": 0.90
    },
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