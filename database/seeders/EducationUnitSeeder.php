<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class EducationUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Unit 8: Education (Part 1) ──────────────────────
        $unit = Unit::create([
            'unit_number' => 8,
            'title' => 'Education',
            'part' => 2,
            'outcome' => 'Menggunakan frasa pendidikan (take a course, pass a course, attend a class), deskripsi pengalaman kelas, dan kolokasi edukasi (syllabus, crash course, certificate of completion).',
        ]);

        // ── LESSON 1: Collocations: "Take a Course", "Useful Information" ─────────
        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations: "Take a Course", "Useful Information"',
            'difficulty' => 'Medium',
        ]);

        $questions1 = [
            [
                'question_text' => 'Why did you decide to take a course in computer programming?',
                'model_answer' => 'I decided to take a course in programming to expand my career options in tech.',
                'key_point' => 'Kolokasi take a course.',
            ],
            [
                'question_text' => 'Did you learn a lot of useful information from the seminar?',
                'model_answer' => 'Yes, the speaker provided a lot of useful information about digital marketing trends.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Is it better to take an online course or an offline class?',
                'model_answer' => 'Taking an online course offers flexibility, while offline classes provide better interaction.',
                'key_point' => 'Perbandingan take a course.',
            ],
            [
                'question_text' => 'How do you save useful information when attending lectures?',
                'model_answer' => 'I take structured notes on my tablet to organize useful information for revision.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Have you ever taken a short course to learn a foreign language?',
                'model_answer' => 'Yes, I took an intensive short course in conversational Spanish last year.',
                'key_point' => 'Frasa took a short course.',
            ],
            [
                'question_text' => 'Where do you usually search for useful information for your assignments?',
                'model_answer' => 'I consult academic journals and trusted educational websites for useful information.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Would you take a course in photography during your summer break?',
                'model_answer' => 'I would love to take a course in photography to improve my editing skills.',
                'key_point' => 'Kolokasi take a course.',
            ],
            [
                'question_text' => 'What was the most useful information you gained from your history teacher?',
                'model_answer' => 'The most useful information was learning how past economic crises shape modern finance.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'How much does it cost to take a professional certification course?',
                'model_answer' => 'The fee varies, but taking an accredited certification course is usually a worthwhile investment.',
                'key_point' => 'Kolokasi take a course.',
            ],
            [
                'question_text' => 'Do social media platforms provide useful information for students?',
                'model_answer' => 'They can, provided you follow educational channels that share verified and useful information.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Did you have to take a foundation course before entering university?',
                'model_answer' => 'Yes, I took a one-year foundation course to prepare for advanced academic subjects.',
                'key_point' => 'Frasa took a foundation course.',
            ],
            [
                'question_text' => 'Why is it important to filter out fake news when looking for useful information?',
                'model_answer' => 'Filtering ensures that you base your decisions and research on accurate and useful information.',
                'key_point' => 'Context useful information.',
            ],
            [
                'question_text' => 'What age group is never too old to take a course?',
                'model_answer' => 'Lifelong learning has no age limit; anyone can take a course at any point in life.',
                'key_point' => 'Concept take a course.',
            ],
            [
                'question_text' => 'How does a good textbook present useful information?',
                'model_answer' => 'A great textbook presents useful information using clear diagrams, bullet points, and real examples.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Did your company pay for you to take a management course?',
                'model_answer' => 'Yes, my company funded me to take a leadership course to prepare for my promotion.',
                'key_point' => 'Work context take a course.',
            ],
            [
                'question_text' => 'Is the internet the fastest source to find useful information?',
                'model_answer' => 'Yes, search engines allow us to access vast amounts of useful information instantly.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Why do people take a refresher course after years of working?',
                'model_answer' => 'They take a refresher course to update their knowledge on modern industry standards.',
                'key_point' => 'Frasa take a refresher course.',
            ],
            [
                'question_text' => 'What makes a lecture full of useful information boring?',
                'model_answer' => 'If the instructor delivers useful information in a monotone voice without engagement.',
                'key_point' => 'Delivery of useful information.',
            ],
            [
                'question_text' => 'Do you prefer to take a self-paced course or a scheduled one?',
                'model_answer' => 'I prefer taking a self-paced course because it fits around my unpredictable schedule.',
                'key_point' => 'Frasa take a self-paced course.',
            ],
            [
                'question_text' => 'How do you share useful information with your classmates?',
                'model_answer' => 'We use group chats and shared cloud folders to exchange useful information and study guides.',
                'key_point' => 'Sharing useful information.',
            ],
            [
                'question_text' => 'How long does it take to complete the course you are taking?',
                'model_answer' => 'The online course I am currently taking lasts six weeks with two lessons per week.',
                'key_point' => 'Duration of taking a course.',
            ],
            [
                'question_text' => 'What kind of books contain the most useful information for personal growth?',
                'model_answer' => 'Self-help and psychology books often provide practical, useful information for daily life.',
                'key_point' => 'Source of useful information.',
            ],
            [
                'question_text' => 'Have you ever regretted taking a particular course?',
                'model_answer' => 'I once regretted taking an advanced math course because the pace was too fast for me.',
                'key_point' => 'Experience of taking a course.',
            ],
            [
                'question_text' => 'Does your university library offer access to useful information databases?',
                'model_answer' => 'Yes, students get free access to digital research databases rich in useful information.',
                'key_point' => 'Kolokasi useful information.',
            ],
            [
                'question_text' => 'Is it mandatory for employees to take a safety training course?',
                'model_answer' => 'Yes, all new hires must take a safety course before working in the facility.',
                'key_point' => 'Mandatory take a course.',
            ],
            [
                'question_text' => 'How do infographic posters help convey useful information?',
                'model_answer' => 'Infographics condense complex data into visual formats that deliver useful information quickly.',
                'key_point' => 'Format of useful information.',
            ],
            [
                'question_text' => 'Would you take a course online if it offered no certificate?',
                'model_answer' => 'Yes, as long as the knowledge gained is practical, I would happily take the course.',
                'key_point' => 'Motivation to take a course.',
            ],
            [
                'question_text' => 'Where can beginners find useful information about financial investments?',
                'model_answer' => 'Reputable financial literacy podcasts and government websites offer safe, useful information.',
                'key_point' => 'Guidance on useful information.',
            ],
            [
                'question_text' => 'What prerequisite subjects do you need before taking this advanced course?',
                'model_answer' => 'You need to pass basic algebra and statistics before taking this data science course.',
                'key_point' => 'Prerequisites to take a course.',
            ],
            [
                'question_text' => 'How do you evaluate if an online article contains reliable and useful information?',
                'model_answer' => 'I check author credentials, publication dates, and cross-reference the facts provided.',
                'key_point' => 'Evaluation of useful information.',
            ],
        ];

        foreach ($questions1 as $q) {
            Question::create([
                'lesson_id' => $lesson1->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 2: Describe a Class You've Taken ──────────────
        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'Describe a Class You\'ve Taken',
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'question_text' => 'What was the title or subject of a memorable class you\'ve taken?',
                'model_answer' => 'A memorable class I took was titled \'Public Speaking and Presentation Skills\' at university.',
                'key_point' => 'Identifikasi nama kelas.',
            ],
            [
                'question_text' => 'Who taught the class and what was their teaching style like?',
                'model_answer' => 'The class was taught by an experienced journalist who used an interactive and engaging teaching style.',
                'key_point' => 'Deskripsi instruktur & teaching style.',
            ],
            [
                'question_text' => 'What specific topics were covered during that class?',
                'model_answer' => 'We covered body language, speech structure, voice modulation, and overcoming stage fright.',
                'key_point' => 'Cakupan materi (topics covered).',
            ],
            [
                'question_text' => 'How many students were enrolled in that class with you?',
                'model_answer' => 'It was a small seminar group with only fifteen students, which allowed for personalized feedback.',
                'key_point' => 'Ukuran kelas (class size).',
            ],
            [
                'question_text' => 'Why did you decide to enroll in that particular class?',
                'model_answer' => 'I enrolled because I wanted to improve my confidence when delivering presentations at work.',
                'key_point' => 'Alasan memilih kelas.',
            ],
            [
                'question_text' => 'Was the class conducted online or in a traditional classroom?',
                'model_answer' => 'It was held in a traditional classroom equipped with a small stage for student practice.',
                'key_point' => 'Format/lokasi kelas.',
            ],
            [
                'question_text' => 'What practical activities did you participate in during the class?',
                'model_answer' => 'We gave weekly five-minute speeches, recorded our performances, and reviewed them together.',
                'key_point' => 'Aktivitas praktis kelas.',
            ],
            [
                'question_text' => 'How long did the class last per session?',
                'model_answer' => 'Each session lasted two hours, held twice a week every Tuesday and Thursday.',
                'key_point' => 'Durasi sesi kelas.',
            ],
            [
                'question_text' => 'What teaching materials or textbooks were used in the class?',
                'model_answer' => 'Instead of dense textbooks, the teacher used video case studies and practical worksheets.',
                'key_point' => 'Media/materi pembelajaran.',
            ],
            [
                'question_text' => 'Did you have to complete a final project or exam for that class?',
                'model_answer' => 'Our final exam was delivering a ten-minute keynote speech in front of an open audience.',
                'key_point' => 'Evaluasi akhir kelas.',
            ],
            [
                'question_text' => 'What was the most challenging assignment in that class?',
                'model_answer' => 'Impromptu speaking was the hardest part, where we had to speak on an unknown topic for two minutes.',
                'key_point' => 'Tantangan terbesar di kelas.',
            ],
            [
                'question_text' => 'How did the instructor keep the class engaging and lively?',
                'model_answer' => 'By encouraging group debates, incorporating humorous examples, and giving constructive feedback.',
                'key_point' => 'Metode pengajaran interaktif.',
            ],
            [
                'question_text' => 'Did you make new friends among your classmates?',
                'model_answer' => 'Yes, because we shared nervous experiences, our group became very supportive and close-knit.',
                'key_point' => 'Hubungan antar siswa.',
            ],
            [
                'question_text' => 'How has taking that class benefited your personal or professional life?',
                'model_answer' => 'It drastically reduced my anxiety during meetings and helped me pitch ideas clearly to clients.',
                'key_point' => 'Manfaat nyata setelah kelas.',
            ],
            [
                'question_text' => 'Was the atmosphere in the class formal or relaxed?',
                'model_answer' => 'The atmosphere was casual and supportive, which made everyone feel safe to make mistakes.',
                'key_point' => 'Suasana (atmosphere) kelas.',
            ],
            [
                'question_text' => 'Did the teacher give personalized constructive feedback?',
                'model_answer' => 'Yes, after every presentation, the professor wrote detailed notes on strengths and areas to improve.',
                'key_point' => 'Peran umpan balik (feedback).',
            ],
            [
                'question_text' => 'What was your favorite memory from taking that class?',
                'model_answer' => 'My favorite memory was receiving a round of applause after successfully finishing my final speech.',
                'key_point' => 'Memori terbaik di kelas.',
            ],
            [
                'question_text' => 'Would you recommend that class to a colleague or friend?',
                'model_answer' => 'I highly recommend it to anyone who wants to boost their communication confidence.',
                'key_point' => 'Rekomendasi kelas.',
            ],
            [
                'question_text' => 'Did the class exceed your initial expectations?',
                'model_answer' => 'It completely exceeded my expectations because of how practical and hands-on the curriculum was.',
                'key_point' => 'Kesan umum terhadap kelas.',
            ],
            [
                'question_text' => 'What homework or preparation was required between sessions?',
                'model_answer' => 'We had to write speech outlines and practice rehearsing in front of a mirror daily.',
                'key_point' => 'Tugas rumah (preparation).',
            ],
            [
                'question_text' => 'How did technology enhance your learning experience in that class?',
                'model_answer' => 'The instructor used video recording so we could analyze our posture and facial expressions.',
                'key_point' => 'Peran teknologi di kelas.',
            ],
            [
                'question_text' => 'Was attendance strictly monitored in that class?',
                'model_answer' => 'Yes, since it was a practical workshop, missing more than two classes meant failing.',
                'key_point' => 'Aturan kehadiran (attendance).',
            ],
            [
                'question_text' => 'What was the background or experience level of the other students?',
                'model_answer' => 'The class had a mix of university students, young professionals, and even business owners.',
                'key_point' => 'Profil peserta kelas.',
            ],
            [
                'question_text' => 'How did taking that class change your perspective on learning?',
                'model_answer' => 'It taught me that practical skills are best learned through active trial and error, not passive reading.',
                'key_point' => 'Perubahan sudut pandang.',
            ],
            [
                'question_text' => 'Was there any guest speaker invited to that class?',
                'model_answer' => 'Yes, a professional TEDx speaker joined one session to share insider storytelling tips.',
                'key_point' => 'Pembicara tamu (guest speaker).',
            ],
            [
                'question_text' => 'Did the class offer good value for the tuition fee paid?',
                'model_answer' => 'It was worth every penny considering the practical skills and confidence I gained.',
                'key_point' => 'Nilai investasi biaya kelas.',
            ],
            [
                'question_text' => 'How did you feel on the very first day of that class?',
                'model_answer' => 'I felt extremely anxious initially, but the teacher\'s warm welcome immediately put me at ease.',
                'key_point' => 'Perasaan hari pertama kelas.',
            ],
            [
                'question_text' => 'Were the study materials easy to access outside class hours?',
                'model_answer' => 'Yes, all video lectures and lecture slides were uploaded to an online student portal.',
                'key_point' => 'Akses materi pembelajaran.',
            ],
            [
                'question_text' => 'What key lesson from that class do you still apply today?',
                'model_answer' => 'I still apply the \'pause for emphasis\' technique whenever I lead team meetings.',
                'key_point' => 'Penerapan ilmu jangka panjang.',
            ],
            [
                'question_text' => 'If you could take that class again, what would you do differently?',
                'model_answer' => 'I would volunteer to speak first during practice sessions to maximize my stage time.',
                'key_point' => 'Refleksi perbaikan diri.',
            ],
        ];

        foreach ($questions2 as $q) {
            Question::create([
                'lesson_id' => $lesson2->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 3: Collocations with Class and Course ────────────────────
        $lesson3 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 3,
            'title' => 'Collocations with Class and Course',
            'difficulty' => 'Medium',
        ]);

        $questions3 = [
            [
                'question_text' => 'Have you ever taken an online course to upgrade your skills?',
                'model_answer' => 'Yes, I took an online course in web design to learn modern UI techniques.',
                'key_point' => 'Kolokasi online course.',
            ],
            [
                'question_text' => 'What happens if a student skips a class without permission?',
                'model_answer' => 'If you skip a class, you miss important lecture content and may lose attendance points.',
                'key_point' => 'Kolokasi skip a class.',
            ],
            [
                'question_text' => 'Is it easy to pass this advanced English course?',
                'model_answer' => 'Passing this course requires consistent effort, active participation, and completing all assignments.',
                'key_point' => 'Kolokasi pass a course.',
            ],
            [
                'question_text' => 'How regularly do you attend your university classes?',
                'model_answer' => 'I make it a point to attend every class unless I am unwell or face an emergency.',
                'key_point' => 'Kolokasi attend a class.',
            ],
            [
                'question_text' => 'Have you ever taken a crash course right before an exam?',
                'model_answer' => 'Yes, I joined a weekend crash course to review major concepts before my finals.',
                'key_point' => 'Istilah crash course.',
            ],
            [
                'question_text' => 'What information is included in the course syllabus?',
                'model_answer' => 'The course syllabus details grading criteria, weekly topics, textbook lists, and exam dates.',
                'key_point' => 'Kolokasi course syllabus.',
            ],
            [
                'question_text' => 'Under what circumstances would a student need to retake a course?',
                'model_answer' => 'Students must retake a course if their final grade falls below the passing threshold.',
                'key_point' => 'Kolokasi retake a course.',
            ],
            [
                'question_text' => 'Do you prefer an interactive class or a lecture-style class?',
                'model_answer' => 'I prefer an interactive class where students can discuss ideas and ask questions freely.',
                'key_point' => 'Kolokasi interactive class / lecture class.',
            ],
            [
                'question_text' => 'How do you register for an elective course at your university?',
                'model_answer' => 'We log into the student portal during the enrollment window to choose our elective courses.',
                'key_point' => 'Kolokasi elective course.',
            ],
            [
                'question_text' => 'What is the maximum number of students allowed in a lab class?',
                'model_answer' => 'A science lab class is usually capped at twenty students for safety and equipment access.',
                'key_point' => 'Kolokasi lab class.',
            ],
            [
                'question_text' => 'Do you think taking a vocational course is better than a university degree?',
                'model_answer' => 'A vocational course offers faster practical job training, whereas a degree provides broader theory.',
                'key_point' => 'Kolokasi vocational course.',
            ],
            [
                'question_text' => 'What should you do if you fall behind in a difficult class?',
                'model_answer' => 'You should ask the professor for guidance or hire a tutor to catch up.',
                'key_point' => 'Context difficult class.',
            ],
            [
                'question_text' => 'Is attendance mandatory for all your degree courses?',
                'model_answer' => 'Yes, most core courses require at least 80% attendance to sit for final exams.',
                'key_point' => 'Kolokasi degree course.',
            ],
            [
                'question_text' => 'Have you ever dropped a class halfway through the semester?',
                'model_answer' => 'I once dropped a class because the workload conflicted with my part-time job hours.',
                'key_point' => 'Kolokasi drop a class.',
            ],
            [
                'question_text' => 'What makes an introductory course engaging for freshers?',
                'model_answer' => 'An introductory course should cover fundamental concepts using fun, real-world examples.',
                'key_point' => 'Kolokasi introductory course.',
            ],
            [
                'question_text' => 'Do you participate actively in class discussions?',
                'model_answer' => 'I try to contribute to class discussions whenever I have a valuable point to share.',
                'key_point' => 'Kolokasi class discussion.',
            ],
            [
                'question_text' => 'How do you audit a class if you don\'t need academic credit?',
                'model_answer' => 'You can audit a class by getting permission from the professor to sit in without taking exams.',
                'key_point' => 'Kolokasi audit a class.',
            ],
            [
                'question_text' => 'What are the prerequisites for enrolling in an advanced course?',
                'model_answer' => 'You must successfully complete the introductory level course before enrolling in the advanced one.',
                'key_point' => 'Kolokasi advanced course.',
            ],
            [
                'question_text' => 'Do you prefer morning classes or evening classes?',
                'model_answer' => 'I prefer morning classes because my focus is sharpest early in the day.',
                'key_point' => 'Kolokasi morning class / evening class.',
            ],
            [
                'question_text' => 'How do universities evaluate course feedback from students?',
                'model_answer' => 'Universities collect anonymous end-of-semester course evaluations to improve teaching quality.',
                'key_point' => 'Kolokasi course feedback / evaluation.',
            ],
            [
                'question_text' => 'Is it stressful to take a full-time course load while working?',
                'model_answer' => 'Managing a full-time course load alongside a job requires strict time management to avoid burnout.',
                'key_point' => 'Kolokasi course load.',
            ],
            [
                'question_text' => 'What is the main benefit of a small class size?',
                'model_answer' => 'A small class size allows the instructor to give individual attention to every student.',
                'key_point' => 'Kolokasi class size.',
            ],
            [
                'question_text' => 'Do you need a certificate of completion after finishing an online course?',
                'model_answer' => 'A certificate of completion is great for adding verified skills to your CV or LinkedIn profile.',
                'key_point' => 'Kolokasi certificate of completion.',
            ],
            [
                'question_text' => 'Why do some students cancel a class enrollment before it starts?',
                'model_answer' => 'They might cancel class enrollment due to schedule clashes or tuition budget constraints.',
                'key_point' => 'Kolokasi class enrollment.',
            ],
            [
                'question_text' => 'Is it easy to focus during a two-hour lecture class?',
                'model_answer' => 'It can be tough, so taking short breaks halfway through a long class helps maintain focus.',
                'key_point' => 'Kolokasi lecture class.',
            ],
            [
                'question_text' => 'What practical skills are taught in a culinary course?',
                'model_answer' => 'A culinary course teaches knife skills, food safety, recipe development, and plating techniques.',
                'key_point' => 'Kolokasi culinary course.',
            ],
            [
                'question_text' => 'Do you review your notes immediately after a class ends?',
                'model_answer' => 'Reviewing notes shortly after class helps solidify new information in your long-term memory.',
                'key_point' => 'Habit after class ends.',
            ],
            [
                'question_text' => 'How does a university determine course credits?',
                'model_answer' => 'Course credits are based on the total classroom instruction hours and expected study time per week.',
                'key_point' => 'Kolokasi course credits.',
            ],
            [
                'question_text' => 'What should a teacher do if the class representative is absent?',
                'model_answer' => 'The teacher can assign a temporary class representative to assist with administrative announcements.',
                'key_point' => 'Kolokasi class representative.',
            ],
            [
                'question_text' => 'Will traditional physical classes be completely replaced by online courses?',
                'model_answer' => 'Unlikely; while online courses offer convenience, physical classes provide irreplaceable social interaction.',
                'key_point' => 'Comparison physical class vs online course.',
            ],
        ];

        foreach ($questions3 as $q) {
            Question::create([
                'lesson_id' => $lesson3->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        $this->command->info('Education Unit (Unit 8) seeded successfully!');
        $this->command->info('Lesson 1: ' . count($questions1) . ' questions');
        $this->command->info('Lesson 2: ' . count($questions2) . ' questions');
        $this->command->info('Lesson 3: ' . count($questions3) . ' questions');
    }
}