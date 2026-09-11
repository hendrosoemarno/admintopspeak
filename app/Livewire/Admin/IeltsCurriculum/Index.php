<?php

namespace App\Livewire\Admin\IeltsCurriculum;

use App\Enums\LessonDifficulty;
use App\Enums\LessonProgressStatus;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use App\Models\UserLessonProgress;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin kurikulum IELTS (Units/Lessons/Questions) sesuai spesifikasi IELTS.md.
 *
 * Alur: Filter Part -> Daftar Unit -> (klik) Lessons unit -> (klik) Questions
 * lesson. CRUD tersedia di tiga level lewat satu modal dinamis.
 */
class Index extends Component
{
    use WithPagination;

    /** Level navigasi aktif. */
    public ?int $activeUnitId = null;

    public ?int $activeLessonId = null;

    /** Filter bagian kurikulum (1/2/3). */
    public ?int $activePart = null;

    public string $search = '';

    // ===== State modal form =====
    public bool $showForm = false;

    public string $entityType = 'unit'; // unit | lesson | question

    public ?int $editingId = null;

    // Field Unit
    public string $unit_number = '';

    public string $unit_title = '';

    public string $unit_part = '1';

    public string $unit_outcome = '';

    // Field Lesson
    public string $lesson_number = '';

    public string $lesson_title = '';

    public string $lesson_difficulty = 'Medium';

    // Field Question
    public string $question_text = '';

    public string $model_answer = '';

    public string $key_point = '';

    protected function rules(): array
    {
        return match ($this->entityType) {
            'unit' => [
                'unit_number' => 'required|integer|min:1|unique:units,unit_number,' . ($this->editingId ?? 'NULL'),
                'unit_title' => 'required|string|max:100',
                'unit_part' => 'required|in:1,2,3',
                'unit_outcome' => 'nullable|string',
            ],
            'lesson' => [
                'lesson_number' => 'required|integer|min:1|unique:lessons,lesson_number,' . ($this->editingId ?? 'NULL') . ',id,unit_id,' . ($this->activeUnitId ?? 0),
                'lesson_title' => 'required|string|max:150',
                'lesson_difficulty' => 'required|in:Easy,Medium,Difficult',
            ],
            default => [
                'question_text' => 'required|string|min:5|unique:questions,question_text,' . ($this->editingId ?? 'NULL'),
                'model_answer' => 'required|string|min:20',
                'key_point' => 'required|string|max:100',
            ],
        };
    }

    public function openCreateUnit(): void
    {
        $this->resetForm();
        $this->entityType = 'unit';
        $this->unit_part = (string) ($this->activePart ?? 1);
        $this->showForm = true;
    }

    public function openEditUnit(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $this->resetForm();
        $this->entityType = 'unit';
        $this->editingId = $unit->id;
        $this->unit_number = (string) $unit->unit_number;
        $this->unit_title = $unit->title;
        $this->unit_part = (string) $unit->part;
        $this->unit_outcome = $unit->outcome ?? '';
        $this->showForm = true;
    }

    public function openCreateLesson(): void
    {
        $this->resetForm();
        $this->entityType = 'lesson';
        $this->showForm = true;
    }

    public function openEditLesson(int $id): void
    {
        $lesson = Lesson::findOrFail($id);
        $this->resetForm();
        $this->entityType = 'lesson';
        $this->editingId = $lesson->id;
        $this->lesson_number = (string) $lesson->lesson_number;
        $this->lesson_title = $lesson->title;
        $this->lesson_difficulty = $lesson->difficulty->value;
        $this->showForm = true;
    }

    public function openCreateQuestion(): void
    {
        $this->resetForm();
        $this->entityType = 'question';
        $this->showForm = true;
    }

    public function openEditQuestion(int $id): void
    {
        $question = Question::findOrFail($id);
        $this->resetForm();
        $this->entityType = 'question';
        $this->editingId = $question->id;
        $this->question_text = $question->question_text;
        $this->model_answer = $question->model_answer;
        $this->key_point = $question->key_point;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        switch ($this->entityType) {
            case 'unit':
                $unit = $this->editingId
                    ? Unit::findOrFail($this->editingId)
                    : new Unit();

                $unit->fill([
                    'unit_number' => $this->unit_number,
                    'title' => $this->unit_title,
                    'part' => $this->unit_part,
                    'outcome' => $this->unit_outcome ?: null,
                ])->save();

                $this->dispatch('flash', message: $this->editingId ? 'Unit diperbarui.' : 'Unit ditambahkan.');
                break;

            case 'lesson':
                $lesson = $this->editingId
                    ? Lesson::findOrFail($this->editingId)
                    : new Lesson(['unit_id' => $this->activeUnitId]);

                $lesson->fill([
                    'lesson_number' => $this->lesson_number,
                    'title' => $this->lesson_title,
                    'difficulty' => $this->lesson_difficulty,
                ])->save();

                $this->dispatch('flash', message: $this->editingId ? 'Lesson diperbarui.' : 'Lesson ditambahkan.');
                break;

            default:
                $question = $this->editingId
                    ? Question::findOrFail($this->editingId)
                    : new Question(['lesson_id' => $this->activeLessonId]);

                $question->fill([
                    'question_text' => $this->question_text,
                    'model_answer' => $this->model_answer,
                    'key_point' => $this->key_point,
                ])->save();

                $this->dispatch('flash', message: $this->editingId ? 'Soal diperbarui.' : 'Soal ditambahkan.');
                break;
        }

        $this->closeForm();
    }

    public function deleteUnit(int $id): void
    {
        Unit::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Unit dihapus.');
    }

    public function deleteLesson(int $id): void
    {
        Lesson::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Lesson dihapus.');
    }

    public function deleteQuestion(int $id): void
    {
        Question::findOrFail($id)->delete();
        $this->dispatch('flash', message: 'Soal dihapus.');
    }

    public function setPart(?int $part): void
    {
        $this->activePart = $part;
        $this->activeUnitId = null;
        $this->activeLessonId = null;
        $this->search = '';
        $this->resetPage();
    }

    public function openUnit(int $unitId): void
    {
        $this->activeUnitId = $unitId;
        $this->activeLessonId = null;
    }

    public function openLesson(int $lessonId): void
    {
        $this->activeLessonId = $lessonId;
    }

    public function backToUnits(): void
    {
        $this->activeUnitId = null;
        $this->activeLessonId = null;
    }

    public function backToLessons(): void
    {
        $this->activeLessonId = null;
    }

    #[Computed]
    public function difficulties(): array
    {
        return LessonDifficulty::cases();
    }

    #[Computed]
    public function partCounts(): array
    {
        return Unit::query()
            ->select('part')
            ->get()
            ->groupBy('part')
            ->map->count()
            ->all();
    }

    public function render()
    {
        $data = [
            'units' => collect(),
            'unit' => null,
            'lessons' => collect(),
            'passCounts' => collect(),
            'lesson' => null,
            'questions' => collect(),
            'passCount' => 0,
        ];

        if ($this->activeLessonId !== null) {
            $lesson = Lesson::with('unit')->findOrFail($this->activeLessonId);
            $questions = $lesson->questions()
                ->when($this->search, fn ($q) => $q->where('question_text', 'like', "%{$this->search}%"))
                ->get();

            $data['lesson'] = $lesson;
            $data['questions'] = $questions->load('lesson.unit');
            $data['passCount'] = UserLessonProgress::query()
                ->where('lesson_id', $lesson->id)
                ->where('status', LessonProgressStatus::PASSED->value)
                ->count();
        } elseif ($this->activeUnitId !== null) {
            $unit = Unit::findOrFail($this->activeUnitId);
            $lessons = $unit->lessons()
                ->withCount('questions')
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->get();

            $data['unit'] = $unit;
            $data['lessons'] = $lessons;
            $data['passCounts'] = UserLessonProgress::query()
                ->whereIn('lesson_id', $lessons->pluck('id'))
                ->where('status', LessonProgressStatus::PASSED->value)
                ->get()
                ->groupBy('lesson_id')
                ->map->count();
        } else {
            $units = Unit::query()
                ->withCount('lessons')
                ->when($this->activePart !== null, fn ($q) => $q->where('part', $this->activePart))
                ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->orderBy('part')
                ->orderBy('unit_number')
                ->get();

            $data['units'] = $units;
        }

        return view('livewire.admin.ielts-curriculum.index', $data)
            ->layout('layouts.app', ['title' => 'IELTS Curriculum']);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->unit_number = '';
        $this->unit_title = '';
        $this->unit_part = '1';
        $this->unit_outcome = '';
        $this->lesson_number = '';
        $this->lesson_title = '';
        $this->lesson_difficulty = 'Medium';
        $this->question_text = '';
        $this->model_answer = '';
        $this->key_point = '';
        $this->resetValidation();
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }
}