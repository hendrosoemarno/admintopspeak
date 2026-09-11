<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class HobbiesUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Unit 10: Hobbies & Entertainment (Part 2) ──────────────────────
        $unit = Unit::create([
            'unit_number' => 10,
            'title' => 'Hobbies & Entertainment',
            'part' => 2,
            'outcome' => 'Menggunakan frasa komitmen (get serious about, be good at), deskripsi hobi (origin, frequency, benefits), dan kolokasi entertainment (binge-watch, box office, in-app purchases).',
        ]);

        // ── LESSON 1: Collocations: "Get Serious", "Be Good At" ─────────
        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations: "Get Serious", "Be Good At"',
            'difficulty' => 'Medium',
        ]);

        $questions1 = [
            [
                'question_text' => 'When did you get serious about learning English?',
                'model_answer' => 'I got serious about learning English when I decided to prepare for the IELTS exam.',
                'key_point' => 'Kolokasi get serious about.',
            ],
            [
                'question_text' => 'What skills or sports are you naturally good at?',
                'model_answer' => 'I\'ve always been good at racket sports, especially badminton and table tennis.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'What made you get serious about taking care of your health?',
                'model_answer' => 'Experiencing constant fatigue made me get serious about regular exercise and healthy eating.',
                'key_point' => 'Kolokasi get serious about.',
            ],
            [
                'question_text' => 'Is it necessary to be good at math to learn computer coding?',
                'model_answer' => 'Being good at basic logic helps, but you don\'t need advanced math to be good at coding.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'How can a beginner get serious about building a financial budget?',
                'model_answer' => 'They can get serious by tracking daily expenses on a spreadsheet and setting savings targets.',
                'key_point' => 'Kolokasi get serious about.',
            ],
            [
                'question_text' => 'Are you good at public speaking in front of large crowds?',
                'model_answer' => 'I used to suffer from stage fright, but practice helped me become good at public speaking.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'Why do people usually get serious about a hobby after retirement?',
                'model_answer' => 'Because they finally have abundant free time to dedicate to their personal passions.',
                'key_point' => 'Context get serious about.',
            ],
            [
                'question_text' => 'What subject were you good at when you were in high school?',
                'model_answer' => 'I was particularly good at history and literature because I loved reading stories.',
                'key_point' => 'Past tense: was good at.',
            ],
            [
                'question_text' => 'When should an entrepreneur get serious about scaling their business?',
                'model_answer' => 'When they have a stable customer base and consistent monthly cash flow.',
                'key_point' => 'Context get serious about.',
            ],
            [
                'question_text' => 'Do you think anyone can be good at drawing with enough practice?',
                'model_answer' => 'Yes, while talent gives a head start, consistent practice enables anyone to be good at art.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'Did you get serious about your career right after university?',
                'model_answer' => 'Yes, landing my first junior role forced me to get serious about professional growth.',
                'key_point' => 'Kolokasi get serious about.',
            ],
            [
                'question_text' => 'Are you good at managing time when multiple deadlines overlap?',
                'model_answer' => 'Yes, I am good at prioritizing tasks using digital calendars and checklists.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'How does an athlete show they are getting serious about winning?',
                'model_answer' => 'By increasing training intensity, strictly controlling diet, and prioritizing rest.',
                'key_point' => 'Kolokasi get serious about.',
            ],
            [
                'question_text' => 'Is it important for a manager to be good at active listening?',
                'model_answer' => 'Crucial, because being good at listening builds trust and resolves team conflicts quickly.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'What age is ideal to get serious about learning a musical instrument?',
                'model_answer' => 'Any age is fine, though getting serious as a child builds strong muscle memory.',
                'key_point' => 'Context get serious about.',
            ],
            [
                'question_text' => 'Were you good at science subjects during your school days?',
                'model_answer' => 'I was good at biology, but I struggled quite a bit with complex physics formulas.',
                'key_point' => 'Kolocasi was good at.',
            ],
            [
                'question_text' => 'What happens when a student gets serious about preparing for exams?',
                'model_answer' => 'Their study focus improves, procrastination decreases, and their test scores rise.',
                'key_point' => 'Impact of get serious.',
            ],
            [
                'question_text' => 'Are you good at remembering people\'s names after a first meeting?',
                'model_answer' => 'Not really; I am terrible at names, but I am good at remembering faces.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'How do you get serious about reducing your daily screen time?',
                'model_answer' => 'By setting app limits, leaving my phone in another room, and picking up physical books.',
                'key_point' => 'Kolokasi get serious about.',
            ],
            [
                'question_text' => 'Is being good at problem-solving essential for software engineers?',
                'model_answer' => 'Yes, software development is fundamentally about being good at breaking down complex logic.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'Why do some people never get serious about their personal goals?',
                'model_answer' => 'Often due to fear of failure, lack of clear planning, or low self-discipline.',
                'key_point' => 'Obstacles to get serious.',
            ],
            [
                'question_text' => 'Do you consider yourself good at cooking traditional meals?',
                'model_answer' => 'I am good at cooking simple traditional dishes, thanks to recipes taught by my mother.',
                'key_point' => 'Kolocasi be good at.',
            ],
            [
                'question_text' => 'What prompted your city to get serious about recycling waste?',
                'model_answer' => 'Overflowing landfills forced the municipal council to get serious about waste segregation.',
                'key_point' => 'Context get serious about.',
            ],
            [
                'question_text' => 'Are you good at adapting to sudden changes in your schedule?',
                'model_answer' => 'Yes, I am quite flexible and good at adjusting my plans without getting stressed.',
                'key_point' => 'Kolokasi be good at.',
            ],
            [
                'question_text' => 'When did you get serious about saving money for a house?',
                'model_answer' => 'I got serious about saving three years ago when I opened a dedicated high-yield account.',
                'key_point' => 'Kolocasi get serious about.',
            ],
            [
                'question_text' => 'Is your best friend good at giving practical life advice?',
                'model_answer' => 'Yes, she is remarkably good at analyzing situations objectively and giving calm advice.',
                'key_point' => 'Kolocasi be good at.',
            ],
            [
                'question_text' => 'How do you get serious about writing a book?',
                'model_answer' => 'By setting a non-negotiable daily word count goal and writing consistently every morning.',
                'key_point' => 'Action of get serious.',
            ],
            [
                'question_text' => 'Were you good at team sports or individual sports in childhood?',
                'model_answer' => 'I was much better at individual sports like swimming than team games like soccer.',
                'key_point' => 'Perbandingan was good at.',
            ],
            [
                'question_text' => 'What signal shows a company is getting serious about sustainability?',
                'model_answer' => 'Transitioning to renewable energy sources and eliminating single-use plastic packaging.',
                'key_point' => 'Indicator of get serious.',
            ],
            [
                'question_text' => 'What soft skill do you wish you were better good at?',
                'model_answer' => 'I wish I were better good at negotiating salaries and pitching business ideas confidently.',
                'key_point' => 'Desire to be good at.',
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

        // ── LESSON 2: Describe a Current Hobby ──────────────
        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'Describe a Current Hobby',
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'question_text' => 'What is your main current hobby or pastime?',
                'model_answer' => 'My main current hobby is digital landscape photography, which I picked up two years ago.',
                'key_point' => 'Identifikasi hobi saat ini.',
            ],
            [
                'question_text' => 'When and how did you first get started with this hobby?',
                'model_answer' => 'I started during a mountain trip when I realized how much I enjoyed capturing natural scenery.',
                'key_point' => 'Asal usul hobi.',
            ],
            [
                'question_text' => 'How often do you dedicate time to this hobby every week?',
                'model_answer' => 'I spend about three to four hours every weekend taking photos and editing them.',
                'key_point' => 'Frekuensi hobi.',
            ],
            [
                'question_text' => 'What special equipment or gear do you need for this hobby?',
                'model_answer' => 'I use a mirrorless camera, a sturdy tripod, and editing software on my laptop.',
                'key_point' => 'Peralatan hobi.',
            ],
            [
                'question_text' => 'Do you pursue this hobby alone or with a group of friends?',
                'model_answer' => 'I usually go on photo walks alone for focus, but I occasionally join local photography clubs.',
                'key_point' => 'Modus hobi (solo/group).',
            ],
            [
                'question_text' => 'Why do you find this current hobby so enjoyable and rewarding?',
                'model_answer' => 'Because it combines creative artistic expression with exploring beautiful outdoor nature.',
                'key_point' => 'Alasan menikmati hobi.',
            ],
            [
                'question_text' => 'Is this current hobby expensive to maintain?',
                'model_answer' => 'Initial camera gear was pricey, but day-to-day ongoing costs are relatively low.',
                'key_point' => 'Evaluasi biaya hobi.',
            ],
            [
                'question_text' => 'What specific skills have you developed through this hobby?',
                'model_answer' => 'I\'ve developed patience, an eye for visual composition, and technical photo-editing skills.',
                'key_point' => 'Skill yang didapat.',
            ],
            [
                'question_text' => 'How has this hobby helped you manage daily work stress?',
                'model_answer' => 'Focusing on framing shots forces me to stay present and forget about office pressures.',
                'key_point' => 'Manfaat kesehatan mental.',
            ],
            [
                'question_text' => 'Where do you usually practice or enjoy this hobby?',
                'model_answer' => 'I practice in local public parks, mountain trails, and historic downtown districts.',
                'key_point' => 'Lokasi hobi.',
            ],
            [
                'question_text' => 'Have you ever earned money or shared work from this hobby?',
                'model_answer' => 'I share my photos on social media and occasionally sell digital prints to friends.',
                'key_point' => 'Aspek komersial/sosial hobi.',
            ],
            [
                'question_text' => 'What is the most challenging aspect of this current hobby?',
                'model_answer' => 'Mastering manual camera settings in tricky low-light conditions is the hardest part.',
                'key_point' => 'Tantangan hobi.',
            ],
            [
                'question_text' => 'Do weather conditions affect your ability to practice this hobby?',
                'model_answer' => 'Yes, heavy rain limits outdoor shooting, though stormy skies can create dramatic photos.',
                'key_point' => 'Pengaruh cuaca.',
            ],
            [
                'question_text' => 'How has technology improved the way you pursue this hobby?',
                'model_answer' => 'Advanced camera sensors and AI editing software make producing high-quality images much faster.',
                'key_point' => 'Peran teknologi.',
            ],
            [
                'question_text' => 'Did anyone inspire or mentor you to take up this hobby?',
                'model_answer' => 'I was inspired by a documentary photographer whose work I followed online.',
                'key_point' => 'Inspirasi hobi.',
            ],
            [
                'question_text' => 'How do you balance time between your career and this hobby?',
                'model_answer' => 'I reserve weekend mornings exclusively for photography so it never conflicts with work.',
                'key_point' => 'Manajemen waktu hobi.',
            ],
            [
                'question_text' => 'What is your favorite achievement or project in this hobby so far?',
                'model_answer' => 'Winning second place in a local community photo contest last year was very gratifying.',
                'key_point' => 'Pencapaian hobi.',
            ],
            [
                'question_text' => 'Do you plan to continue this hobby for a long time?',
                'model_answer' => 'Absolutely, it\'s a lifelong passion that I can continue enjoying as I grow older.',
                'key_point' => 'Rencana masa depan hobi.',
            ],
            [
                'question_text' => 'How does your family feel about your current hobby?',
                'model_answer' => 'They are very supportive and love receiving framed prints as birthday gifts.',
                'key_point' => 'Respon keluarga.',
            ],
            [
                'question_text' => 'Is this hobby popular among people of your age group?',
                'model_answer' => 'Yes, content creation and photography are hugely popular among young professionals.',
                'key_point' => 'Tren usia hobi.',
            ],
            [
                'question_text' => 'What advice would you give to a beginner starting this hobby?',
                'model_answer' => 'Start with your smartphone camera first to master composition before buying expensive gear.',
                'key_point' => 'Nasihat pemula.',
            ],
            [
                'question_text' => 'Has this hobby made you more observant of your surroundings?',
                'model_answer' => 'Definitely, I now notice subtle lighting variations, architectural lines, and shadows everywhere.',
                'key_point' => 'Perubahan persepsi.',
            ],
            [
                'question_text' => 'Do you read books or watch tutorials to improve this hobby?',
                'model_answer' => 'I regularly watch online video tutorials to learn advanced color grading techniques.',
                'key_point' => 'Sumber belajar hobi.',
            ],
            [
                'question_text' => 'How do you store or organize the results of your hobby?',
                'model_answer' => 'I organize digital files in categorized cloud folders backed up on external hard drives.',
                'key_point' => 'Penyimpanan karya.',
            ],
            [
                'question_text' => 'Would you ever consider turning this hobby into a full-time career?',
                'model_answer' => 'I prefer keeping it as a creative outlet so commercial pressure doesn\'t ruin the fun.',
                'key_point' => 'Hobi vs Karir.',
            ],
            [
                'question_text' => 'What is the next goal you want to achieve in this hobby?',
                'model_answer' => 'My next goal is to publish a self-printed coffee table photo book of my hometown.',
                'key_point' => 'Target hobi berikutnya.',
            ],
            [
                'question_text' => 'Do you travel specifically to pursue this hobby in new places?',
                'model_answer' => 'Yes, I plan my holiday destinations around scenic landscapes that I want to photograph.',
                'key_point' => 'Hobi & Travel.',
            ],
            [
                'question_text' => 'How does your hobby complement your personality?',
                'model_answer' => 'As an introverted person, spending quiet hours observing nature suits me perfectly.',
                'key_point' => 'Hobi & Kepribadian.',
            ],
            [
                'question_text' => 'What physical or physical benefits do you get from this hobby?',
                'model_answer' => 'Walking miles during outdoor photo hunts keeps me physically active and fit.',
                'key_point' => 'Manfaat fisik.',
            ],
            [
                'question_text' => 'Why are current hobbies essential for modern working adults?',
                'model_answer' => 'Hobbies provide a healthy boundary between work life and personal emotional fulfillment.',
                'key_point' => 'Nilai hobi modern.',
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

        // ── LESSON 3: Collocations with the Word Fun ────────────────────
        $lesson3 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 3,
            'title' => 'Collocations with the Word Fun',
            'difficulty' => 'Medium',
        ]);

        $questions3 = [
            [
                'question_text' => 'What is your idea of a truly fun activity on a weekend?',
                'model_answer' => 'Going on a road trip to the beach with close friends is my idea of a fun activity.',
                'key_point' => 'Kolokasi fun activity.',
            ],
            [
                'question_text' => 'Did you have fun at the music festival last night?',
                'model_answer' => 'Yes, we had immense fun dancing to live bands and trying street food.',
                'key_point' => 'Kolokasi have fun.',
            ],
            [
                'question_text' => 'Why is it mean to make fun of someone\'s accent?',
                'model_answer' => 'Making fun of someone hurts their confidence and shows a lack of empathy.',
                'key_point' => 'Kolokasi make fun of.',
            ],
            [
                'question_text' => 'Do you play computer games for competition or just for fun?',
                'model_answer' => 'I mostly play casual puzzle games just for fun to unwind after work.',
                'key_point' => 'Kolokasi for fun.',
            ],
            [
                'question_text' => 'Was your childhood summer camp full of fun memories?',
                'model_answer' => 'Yes, it was full of fun activities like campfire singing, kayaking, and hiking.',
                'key_point' => 'Kolokasi full of fun.',
            ],
            [
                'question_text' => 'How do you ensure guests have fun at your house parties?',
                'model_answer' => 'By preparing good food, playing background music, and organizing light icebreaker games.',
                'key_point' => 'Kolokasi have fun.',
            ],
            [
                'question_text' => 'Is learning a new language a fun experience for you?',
                'model_answer' => 'It can be challenging, but discovering new phrases makes it a fun experience.',
                'key_point' => 'Frasa fun experience.',
            ],
            [
                'question_text' => 'Why do siblings often make fun of each other growing up?',
                'model_answer' => 'It is often a lighthearted way of bonding, as long as it does not cross into bullying.',
                'key_point' => 'Kolokasi make fun of.',
            ],
            [
                'question_text' => 'What is a budget-friendly fun activity to do in your city?',
                'model_answer' => 'Having a picnic and playing frisbee in the central public park is a great fun activity.',
                'key_point' => 'Kolokasi fun activity.',
            ],
            [
                'question_text' => 'Do you write fiction professionally or just for fun?',
                'model_answer' => 'I write short stories purely for fun as a creative outlet without commercial pressure.',
                'key_point' => 'Kolokasi for fun.',
            ],
            [
                'question_text' => 'Was the team-building event at your workplace full of fun?',
                'model_answer' => 'Yes, the outdoor obstacle courses made the whole day full of fun and laughter.',
                'key_point' => 'Kolokasi full of fun.',
            ],
            [
                'question_text' => 'How can teachers make learning math more fun for kids?',
                'model_answer' => 'By using interactive math games, visual puzzles, and hands-on counting objects.',
                'key_point' => 'Context make learning fun.',
            ],
            [
                'question_text' => 'Do you agree that age shouldn\'t stop anyone from having fun?',
                'model_answer' => 'Absolutely, staying playful and having fun keeps people young at heart regardless of age.',
                'key_point' => 'Kolokasi have fun.',
            ],
            [
                'question_text' => 'Why shouldn\'t colleagues make fun of mistakes in professional meetings?',
                'model_answer' => 'Because making fun creates a toxic culture where employees fear sharing innovative ideas.',
                'key_point' => 'Kolocasi make fun of.',
            ],
            [
                'question_text' => 'What fun outdoor sports do you enjoy during summer?',
                'model_answer' => 'Beach volleyball and snorkeling are my favorite fun outdoor sports during summer.',
                'key_point' => 'Frasa fun sports.',
            ],
            [
                'question_text' => 'Do you take cooking classes professionally or for fun?',
                'model_answer' => 'I took a Italian pasta-making workshop just for fun with my partner.',
                'key_point' => 'Kolocasi for fun.',
            ],
            [
                'question_text' => 'Was your university graduation trip full of fun moments?',
                'model_answer' => 'It was an unforgettable week full of fun, exploring island beaches with my classmates.',
                'key_point' => 'Kolokasi full of fun.',
            ],
            [
                'question_text' => 'How do you make routine household chores more fun?',
                'model_answer' => 'I listen to energetic music playlists or engaging podcasts while cleaning.',
                'key_point' => 'Context make chores fun.',
            ],
            [
                'question_text' => 'Where do local teenagers go to have fun on weekends?',
                'model_answer' => 'They usually gather at shopping malls, movie theaters, and indoor arcade centers.',
                'key_point' => 'Kolokasi have fun.',
            ],
            [
                'question_text' => 'Is it acceptable to make fun of yourself in self-deprecating humor?',
                'model_answer' => 'Light self-deprecating humor can break the ice, as long as it doesn\'t harm self-esteem.',
                'key_point' => 'Kolokasi make fun of yourself.',
            ],
            [
                'question_text' => 'What is a fun activity to do on a rainy Sunday afternoon?',
                'model_answer' => 'Baking cookies, playing board games, or binge-watching movies are fun rainy-day activities.',
                'key_point' => 'Kolokasi fun activity.',
            ],
            [
                'question_text' => 'Do you enter marathons to win or just for fun?',
                'model_answer' => 'I run marathons just for fun and to challenge my personal physical limits.',
                'key_point' => 'Kolocasi for fun.',
            ],
            [
                'question_text' => 'Why are amusement parks considered full of fun for families?',
                'model_answer' => 'Because they offer diverse rollercoasters, live shows, and games suitable for all ages.',
                'key_point' => 'Kolokasi full of fun.',
            ],
            [
                'question_text' => 'How do you politely tell someone to stop making fun of you?',
                'model_answer' => 'By speaking calmly, setting a clear boundary, and expressing that the joke is unappreciated.',
                'key_point' => 'Kolokasi make fun of.',
            ],
            [
                'question_text' => 'Did you have fun exploring street markets in your last vacation?',
                'model_answer' => 'Yes, bargaining for handicrafts and trying exotic street food was immensely fun.',
                'key_point' => 'Kolokasi have fun.',
            ],
            [
                'question_text' => 'What is the difference between innocent humor and making fun maliciously?',
                'model_answer' => 'Innocent humor includes everyone in laughter, while malicious teasing targets someone at their expense.',
                'key_point' => 'Kolokasi make fun.',
            ],
            [
                'question_text' => 'What fun games do you play during family gatherings?',
                'model_answer' => 'We love playing charades and trivia card games after family holiday dinners.',
                'key_point' => 'Frasa fun games.',
            ],
            [
                'question_text' => 'Do you travel for work or purely for fun?',
                'model_answer' => 'My upcoming trip to Japan is purely for fun and sightseeing during my annual leave.',
                'key_point' => 'Kolocasi for fun.',
            ],
            [
                'question_text' => 'What makes an educational video game both informative and full of fun?',
                'model_answer' => 'Great graphics, rewarding game progression, and seamless integration of learning tasks.',
                'key_point' => 'Kolocasi full of fun.',
            ],
            [
                'question_text' => 'How can workplace environments incorporate fun without losing productivity?',
                'model_answer' => 'By hosting casual Friday lunches, celebrating team wins, and providing comfortable break rooms.',
                'key_point' => 'Concept workplace fun.',
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

        // ── LESSON 4: Collocations: "Check Out", "Make an Impression" ──
        $lesson4 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 4,
            'title' => 'Collocations: "Check Out", "Make an Impression"',
            'difficulty' => 'Easy',
        ]);

        $questions4 = [
            [
                'question_text' => 'What new shows have you checked out recently?',
                'model_answer' => 'I recently checked out a documentary series about ocean life that my friend recommended.',
                'key_point' => 'Kolokasi check out.',
            ],
            [
                'question_text' => 'How do you usually check out reviews before watching a movie?',
                'model_answer' => 'I usually check out the critics\' reviews on a movie app and read a few audience ratings.',
                'key_point' => 'Kolokasi check out reviews.',
            ],
            [
                'question_text' => 'What made a strong first impression on you when you joined your company?',
                'model_answer' => 'The friendly culture and the way senior staff welcomed me made a strong first impression.',
                'key_point' => 'Frasa make a first impression.',
            ],
            [
                'question_text' => 'When has a person made a lasting impression on you?',
                'model_answer' => 'A university professor made a lasting impression on me by showing genuine kindness to struggling students.',
                'key_point' => 'Frasa make a lasting impression.',
            ],
            [
                'question_text' => 'Do you check out a book before buying it?',
                'model_answer' => 'Yes, I usually check out the first few pages and read the summary before spending money.',
                'key_point' => 'Kolokasi check out a book.',
            ],
            [
                'question_text' => 'How can a candidate make a good impression during a job interview?',
                'model_answer' => 'Arriving early, dressing neatly, and listening carefully help a candidate make a good impression.',
                'key_point' => 'Frasa make a good impression.',
            ],
            [
                'question_text' => 'What restaurant would you like to check out this weekend?',
                'model_answer' => 'I would love to check out the new Korean barbecue place that just opened downtown.',
                'key_point' => 'Kolokasi check out a restaurant.',
            ],
            [
                'question_text' => 'Does a strong first impression always reflect a person\'s true character?',
                'model_answer' => 'Not always, because nerves or shyness can hide someone\'s real personality on a first meeting.',
                'key_point' => 'Concept first impression.',
            ],
            [
                'question_text' => 'Have you ever checked out a new hobby before committing to it?',
                'model_answer' => 'Yes, I checked out pottery for a single trial class before deciding to continue with it.',
                'key_point' => 'Kolokasi check out a hobby.',
            ],
            [
                'question_text' => 'What gives a city a great first impression for tourists?',
                'model_answer' => 'Clean streets, clear signage, and welcoming locals give tourists a great first impression.',
                'key_point' => 'Concept city first impression.',
            ],
            [
                'question_text' => 'How do you check out a news story to confirm it is true?',
                'model_answer' => 'I check out the story on two or three different reliable sources before believing it.',
                'key_point' => 'Kolokasi check out a story.',
            ],
            [
                'question_text' => 'Which movie left a deep impression on you as a child?',
                'model_answer' => 'An animated film about a brave little fish left a deep impression on me as a child.',
                'key_point' => 'Frasa leave a deep impression.',
            ],
            [
                'question_text' => 'Do you check out online classes before enrolling in them?',
                'model_answer' => 'I always check out the instructor\'s preview video and past student reviews first.',
                'key_point' => 'Kolokasi check out a class.',
            ],
            [
                'question_text' => 'What impression do new employees usually get in the first week?',
                'model_answer' => 'New employees get the impression that the company values teamwork and open communication.',
                'key_point' => 'Frasa get the impression.',
            ],
            [
                'question_text' => 'Why is it important to check out a product\'s warranty before purchase?',
                'model_answer' => 'Checking out the warranty reveals what is covered and protects you from unexpected repair costs.',
                'key_point' => 'Kolokasi check out a warranty.',
            ],
            [
                'question_text' => 'Can clothes help someone make a good first impression at a formal event?',
                'model_answer' => 'Yes, wearing appropriate formal clothes helps create a positive and respectful impression.',
                'key_point' => 'Concept impression and appearance.',
            ],
            [
                'question_text' => 'What new music would you check out if you had more free time?',
                'model_answer' => 'I would check out some classical pieces and indie bands that friends have praised.',
                'key_point' => 'Kolokasi check out music.',
            ],
            [
                'question_text' => 'How do companies make a strong impression on potential customers?',
                'model_answer' => 'Companies make a strong impression through clear branding, helpful service, and quality products.',
                'key_point' => 'Frasa make a strong impression.',
            ],
            [
                'question_text' => 'Do you check out fitness centers before choosing a membership?',
                'model_answer' => 'Yes, I check out the equipment, class schedule, and crowd level during a free trial visit.',
                'key_point' => 'Kolokasi check out a gym.',
            ],
            [
                'question_text' => 'What impression do social media profiles create at first glance?',
                'model_answer' => 'Profiles create an impression based on photos and comments, which may not match reality.',
                'key_point' => 'Concept digital first impression.',
            ],
            [
                'question_text' => 'Should you check out the weather before planning an outdoor trip?',
                'model_answer' => 'Definitely, checking out the forecast helps you pack properly and avoid ruined plans.',
                'key_point' => 'Kolokasi check out weather.',
            ],
            [
                'question_text' => 'How can a teacher make a memorable impression on students?',
                'model_answer' => 'By using engaging activities and showing genuine care, a teacher leaves a memorable impression.',
                'key_point' => 'Frasa make a memorable impression.',
            ],
            [
                'question_text' => 'What app would you check out to improve your language learning?',
                'model_answer' => 'I would check out a speaking exchange app to practice with native speakers online.',
                'key_point' => 'Kolokasi check out an app.',
            ],
            [
                'question_text' => 'Why do people make an impression on others without realizing it?',
                'model_answer' => 'Because small gestures like tone and posture influence how others perceive us unconsciously.',
                'key_point' => 'Concept unconscious impression.',
            ],
            [
                'question_text' => 'Do you check out a menu online before visiting a new restaurant?',
                'model_answer' => 'Yes, I check out the menu and prices online to make sure it fits my budget and taste.',
                'key_point' => 'Kolokasi check out a menu.',
            ],
            [
                'question_text' => 'What impression should a leader aim to leave on their team?',
                'model_answer' => 'A leader should leave the impression of being fair, dependable, and genuinely supportive.',
                'key_point' => 'Concept leader impression.',
            ],
            [
                'question_text' => 'Have you ever checked out a foreign film with subtitles?',
                'model_answer' => 'Yes, I checked out a Japanese drama and enjoyed it even though I only understood the subtitles.',
                'key_point' => 'Kolokasi check out a film.',
            ],
            [
                'question_text' => 'Why do first impressions matter in a job interview?',
                'model_answer' => 'Because the first few minutes shape how the interviewer perceives your confidence and character.',
                'key_point' => 'Concept first impression importance.',
            ],
            [
                'question_text' => 'What would you check out first when visiting a new city?',
                'model_answer' => 'I would check out the local food market and the historical district to understand the culture.',
                'key_point' => 'Kolokasi check out a city.',
            ],
            [
                'question_text' => 'How can you make sure your message makes the right impression in writing?',
                'model_answer' => 'By proofreading, using a polite tone, and structuring your ideas clearly, your message leaves the right impression.',
                'key_point' => 'Concept written impression.',
            ],
        ];

        foreach ($questions4 as $q) {
            Question::create([
                'lesson_id' => $lesson4->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 5: Describe a TV Show or Movie That Made an Impression ──
        $lesson5 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 5,
            'title' => 'Describe a TV Show or Movie That Made an Impression',
            'difficulty' => 'Difficult',
        ]);

        $questions5 = [
            [
                'question_text' => 'Describe a movie that made a lasting impression on you.',
                'model_answer' => 'Interstellar made a lasting impression on me because of its emotional story about love and time.',
                'key_point' => 'Frasa make a lasting impression.',
            ],
            [
                'question_text' => 'When and where did you first watch this show?',
                'model_answer' => 'I first watched it at a local cinema with my cousin during a summer holiday.',
                'key_point' => 'Context when and where.',
            ],
            [
                'question_text' => 'Who were the main characters and why did they stand out?',
                'model_answer' => 'The main character stood out because he was a devoted parent who made difficult sacrifices for his family.',
                'key_point' => 'Frasa stand out.',
            ],
            [
                'question_text' => 'What was the most memorable scene for you?',
                'model_answer' => 'The most memorable scene was when the protagonist reunited with his grown daughter after many years.',
                'key_point' => 'Frasa memorable scene.',
            ],
            [
                'question_text' => 'Why do you think this story stayed with you?',
                'model_answer' => 'The story stayed with me because it combined science with deep human emotions so beautifully.',
                'key_point' => 'Frasa stay with someone.',
            ],
            [
                'question_text' => 'Would you recommend this movie to your friends? Why?',
                'model_answer' => 'I would strongly recommend it because of its stunning visuals and thought-provoking themes.',
                'key_point' => 'Frasa recommend a movie.',
            ],
            [
                'question_text' => 'How has this show influenced your taste in entertainment?',
                'model_answer' => 'It influenced my taste by making me appreciate slower, more emotional science-fiction films.',
                'key_point' => 'Frasa influence taste.',
            ],
            [
                'question_text' => 'Did you watch it alone or with others?',
                'model_answer' => 'I watched it with my cousin, and we spent hours discussing the ending afterwards.',
                'key_point' => 'Context watching together.',
            ],
            [
                'question_text' => 'What lesson did you take away from this story?',
                'model_answer' => 'I took away the lesson that time is precious and relationships deserve our full attention.',
                'key_point' => 'Frasa take away a lesson.',
            ],
            [
                'question_text' => 'How did the movie make you feel during the first watch?',
                'model_answer' => 'It made me feel deeply moved and slightly overwhelmed by its scale and emotional weight.',
                'key_point' => 'Frasa make someone feel.',
            ],
            [
                'question_text' => 'Would you watch it again in the future?',
                'model_answer' => 'Absolutely, I would rewatch it because I notice new details and meaning every single time.',
                'key_point' => 'Frasa rewatch a movie.',
            ],
            [
                'question_text' => 'What makes a good series worth following for many seasons?',
                'model_answer' => 'Strong character development, a consistent plot, and a sense of mystery make a series worth following.',
                'key_point' => 'Frasa character development.',
            ],
            [
                'question_text' => 'Do you prefer shows with a single season or multiple seasons?',
                'model_answer' => 'I prefer well-crafted mini-series because they tell a complete story without dragging on.',
                'key_point' => 'Concept series length.',
            ],
            [
                'question_text' => 'How important is the soundtrack to a movie\'s impact?',
                'model_answer' => 'The soundtrack is vital because music intensifies emotions and makes certain scenes unforgettable.',
                'key_point' => 'Concept movie soundtrack.',
            ],
            [
                'question_text' => 'Why do some worldwide hits fail to impress local audiences?',
                'model_answer' => 'Cultural differences can make certain jokes or themes feel foreign and less relatable to local viewers.',
                'key_point' => 'Concept cultural differences.',
            ],
            [
                'question_text' => 'Should parents limit what children watch on TV?',
                'model_answer' => 'Yes, parents should limit screen time and choose age-appropriate content that teaches positive values.',
                'key_point' => 'Concept parental control.',
            ],
            [
                'question_text' => 'Does watching in a cinema feel different from watching at home?',
                'model_answer' => 'Yes, the big screen and shared audience make the experience far more immersive and exciting.',
                'key_point' => 'Frasa in the cinema.',
            ],
            [
                'question_text' => 'What genre leaves the strongest impression on most people?',
                'model_answer' => 'Dramas and emotionally powerful films tend to leave the strongest impression on most viewers.',
                'key_point' => 'Concept genre impact.',
            ],
            [
                'question_text' => 'Have you ever been disappointed by a highly praised movie?',
                'model_answer' => 'Yes, once a critically praised film failed to impress me because the pacing was far too slow.',
                'key_point' => 'Frasa fail to impress.',
            ],
            [
                'question_text' => 'How do reviews shape your decision to watch something?',
                'model_answer' => 'Reviews shape my decision strongly, though I still trust my own taste for certain genres.',
                'key_point' => 'Frasa shape a decision.',
            ],
            [
                'question_text' => 'What would you say is the most important element of a good story?',
                'model_answer' => 'I believe a well-developed character that the audience can relate to is the most important element.',
                'key_point' => 'Concept story elements.',
            ],
            [
                'question_text' => 'Do you enjoy discussing movies with friends afterwards?',
                'model_answer' => 'Yes, discussing theories and favorite scenes with friends makes the experience even more enjoyable.',
                'key_point' => 'Concept discussing movies.',
            ],
            [
                'question_text' => 'How has streaming changed the way people watch series?',
                'model_answer' => 'Streaming lets people binge entire seasons at their own pace instead of waiting weekly for episodes.',
                'key_point' => 'Concept binge watching.',
            ],
            [
                'question_text' => 'Why do certain characters stay with audiences for years?',
                'model_answer' => 'Deeply written characters with real struggles stay with audiences because they feel genuinely human.',
                'key_point' => 'Frasa stay with audiences.',
            ],
            [
                'question_text' => 'Would you watch a sequel of your favorite movie?',
                'model_answer' => 'Yes, but I would be cautious because sequels sometimes fail to capture the magic of the original.',
                'key_point' => 'Concept movie sequels.',
            ],
            [
                'question_text' => 'What advice would you give someone making their first film?',
                'model_answer' => 'I would advise them to focus on a clear, emotional story before worrying about fancy special effects.',
                'key_point' => 'Concept filmmaking advice.',
            ],
            [
                'question_text' => 'Do you think adaptations are usually as good as the original books?',
                'model_answer' => 'Rarely, because films must cut details, but a good adaptation can still capture the spirit of the book.',
                'key_point' => 'Concept book adaptations.',
            ],
            [
                'question_text' => 'How do you choose what to watch on a streaming service?',
                'model_answer' => 'I usually browse trailers, check ratings, and rely on recommendations from friends or algorithms.',
                'key_point' => 'Frasa choose what to watch.',
            ],
            [
                'question_text' => 'What makes a movie memorable years after watching it?',
                'model_answer' => 'A unique idea, powerful music, and emotionally truthful acting make a movie memorable for years.',
                'key_point' => 'Frasa memorable movie.',
            ],
            [
                'question_text' => 'Why is the ending of a story so important to its overall impression?',
                'model_answer' => 'Because the ending ties everything together and determines the final feeling the audience takes away.',
                'key_point' => 'Concept story ending.',
            ],
        ];

        foreach ($questions5 as $q) {
            Question::create([
                'lesson_id' => $lesson5->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 6: TV and Movie Collocations ─────────────────────────
        $lesson6 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 6,
            'title' => 'TV and Movie Collocations',
            'difficulty' => 'Easy',
        ]);

        $questions6 = [
            [
                'question_text' => 'How often do you watch TV series these days?',
                'model_answer' => 'I watch a TV series almost every evening after finishing my work and household chores.',
                'key_point' => 'Kolokasi watch a TV series.',
            ],
            [
                'question_text' => 'What is your favorite way to watch movies at home?',
                'model_answer' => 'My favorite way is to watch a movie on a streaming service from the comfort of my sofa.',
                'key_point' => 'Frasa watch a movie.',
            ],
            [
                'question_text' => 'Do you prefer watching TV shows with subtitles or dubbed?',
                'model_answer' => 'I prefer watching with subtitles because the original voices carry more emotion and authenticity.',
                'key_point' => 'Frasa watch with subtitles.',
            ],
            [
                'question_text' => 'What kind of shows do you usually switch on for relaxation?',
                'model_answer' => 'I usually switch on light comedies or nature documentaries to relax after a stressful day.',
                'key_point' => 'Kolokasi switch on a show.',
            ],
            [
                'question_text' => 'Have you ever bought movie tickets online in advance?',
                'model_answer' => 'Yes, I often buy movie tickets online to avoid long queues at the cinema box office.',
                'key_point' => 'Frasa buy movie tickets.',
            ],
            [
                'question_text' => 'What makes a good TV show worth watching every week?',
                'model_answer' => 'A strong plot, likable characters, and a gripping cliffhanger make a show worth watching.',
                'key_point' => 'Frasa a good TV show.',
            ],
            [
                'question_text' => 'Do you prefer watching movies alone or with company?',
                'model_answer' => 'I prefer watching movies with company because sharing reactions makes the experience more fun.',
                'key_point' => 'Frasa watch a film.',
            ],
            [
                'question_text' => 'How do you decide which new show to start watching?',
                'model_answer' => 'I read reviews and watch trailers before deciding which new show to start watching.',
                'key_point' => 'Kolokasi start watching a show.',
            ],
            [
                'question_text' => 'Why do some people binge-watch an entire series in one weekend?',
                'model_answer' => 'Because addictive plots and automatic next episodes make it tempting to binge-watch without stopping.',
                'key_point' => 'Kolokasi binge-watch a series.',
            ],
            [
                'question_text' => 'What is the difference between watching movies in a cinema and at home?',
                'model_answer' => 'In a cinema the sound and screen are immersive, while at home you have comfort and convenience.',
                'key_point' => 'Frasa go to the cinema.',
            ],
            [
                'question_text' => 'Do you follow any TV shows that release episodes weekly?',
                'model_answer' => 'Yes, I follow one drama that releases episodes weekly, which keeps me anticipating each one.',
                'key_point' => 'Kolokasi release episodes.',
            ],
            [
                'question_text' => 'How do you feel about watching a movie with advertisements?',
                'model_answer' => 'I dislike interruptions, so I prefer a streaming service that plays movies without ad breaks.',
                'key_point' => 'Concept movie interruptions.',
            ],
            [
                'question_text' => 'What kind of movies do you usually book tickets for?',
                'model_answer' => 'I usually book tickets for action and adventure films, especially on opening weekends.',
                'key_point' => 'Frasa book tickets.',
            ],
            [
                'question_text' => 'Have you ever watched a TV show from beginning to end in one sitting?',
                'model_answer' => 'Yes, I once watched a short series from beginning to end over a long holiday weekend.',
                'key_point' => 'Kolokasi from beginning to end.',
            ],
            [
                'question_text' => 'Do you think watching TV helps people improve their language skills?',
                'model_answer' => 'Yes, watching TV in a foreign language exposes learners to natural speech and new vocabulary.',
                'key_point' => 'Frasa learning from TV.',
            ],
            [
                'question_text' => 'What genre of film would you never watch?',
                'model_answer' => 'I would never watch extremely violent horror films because they only make me anxious.',
                'key_point' => 'Concept film genre.',
            ],
            [
                'question_text' => 'How do you feel when a favorite series gets cancelled?',
                'model_answer' => 'I feel disappointed and frustrated, especially when the story ends without a proper conclusion.',
                'key_point' => 'Concept cancelled series.',
            ],
            [
                'question_text' => 'What is the most popular type of show in your country right now?',
                'model_answer' => 'Reality competitions and drama series are the most popular types of show in my country now.',
                'key_point' => 'Frasa popular show.',
            ],
            [
                'question_text' => 'Do you rewatch old movies or only new releases?',
                'model_answer' => 'I often rewatch old classics because they bring back fond memories and never feel outdated.',
                'key_point' => 'Kolokasi rewatch a movie.',
            ],
            [
                'question_text' => 'Why do people watch the same movie multiple times?',
                'model_answer' => 'People rewatch movies to catch missed details, relive emotions, or simply enjoy familiar comfort.',
                'key_point' => 'Concept rewatch reasons.',
            ],
            [
                'question_text' => 'How important is the acting in making a movie believable?',
                'model_answer' => 'Strong acting is crucial because believable performances draw the audience into the story.',
                'key_point' => 'Concept quality acting.',
            ],
            [
                'question_text' => 'What would you do if your internet went down during an important episode?',
                'model_answer' => 'I would wait patiently and avoid spoilers until my connection returned to finish the episode.',
                'key_point' => 'Concept streaming interruption.',
            ],
            [
                'question_text' => 'Do you prefer short films or full-length movies?',
                'model_answer' => 'I generally prefer full-length movies because they allow deeper character and plot development.',
                'key_point' => 'Concept film length.',
            ],
            [
                'question_text' => 'How do you recommend a good show to a friend?',
                'model_answer' => 'I usually share the trailer and explain why the storyline would match my friend\'s taste.',
                'key_point' => 'Frasa recommend a show.',
            ],
            [
                'question_text' => 'What role does a movie director play in the final product?',
                'model_answer' => 'The director shapes the vision, guides the actors, and controls how the story unfolds on screen.',
                'key_point' => 'Concept movie director.',
            ],
            [
                'question_text' => 'Do you think live sports on TV are better than attending in person?',
                'model_answer' => 'Watching on TV offers better angles and comfort, but attending in person has unmatched energy.',
                'key_point' => 'Concept live sports on TV.',
            ],
            [
                'question_text' => 'How has your watching habit changed over the last five years?',
                'model_answer' => 'I now watch more on streaming services and far less on traditional broadcast television.',
                'key_point' => 'Frasa watching habit.',
            ],
            [
                'question_text' => 'What is a guilty-pleasure show that you secretly enjoy?',
                'model_answer' => 'I secretly enjoy a light reality show about cooking, even though it has no real plot.',
                'key_point' => 'Frasa guilty pleasure.',
            ],
            [
                'question_text' => 'Should movie theaters close because of streaming platforms?',
                'model_answer' => 'No, cinemas should survive because the shared theatrical experience cannot be fully replaced.',
                'key_point' => 'Concept cinema survival.',
            ],
            [
                'question_text' => 'What would you change about your country\'s television channels?',
                'model_answer' => 'I would reduce excessive advertising and add more educational and cultural programs.',
                'key_point' => 'Concept improving TV channels.',
            ],
        ];

        foreach ($questions6 as $q) {
            Question::create([
                'lesson_id' => $lesson6->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 7: Collocations: "A Convenient Way", "Communicate With" ──
        $lesson7 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 7,
            'title' => 'Collocations: "A Convenient Way", "Communicate With"',
            'difficulty' => 'Easy',
        ]);

        $questions7 = [
            [
                'question_text' => 'Do apps offer a convenient way to do everyday tasks?',
                'model_answer' => 'Yes, apps offer a convenient way to pay bills, order food, and book appointments online.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How do you communicate with your colleagues at work?',
                'model_answer' => 'I communicate with my colleagues through a group chat app and occasional video calls.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'What is a convenient way to reach your workplace?',
                'model_answer' => 'The most convenient way is to take the train because the station is near my office.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How often do you communicate with family members who live far away?',
                'model_answer' => 'I communicate with my relatives abroad every weekend using a video calling app.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'Which apps make your daily life more convenient?',
                'model_answer' => 'E-wallet and navigation apps make my daily life much more convenient and efficient.',
                'key_point' => 'Frasa convenient apps.',
            ],
            [
                'question_text' => 'Do you prefer communicating in person or through messages?',
                'model_answer' => 'I prefer communicating in person for serious matters, but messages are quick for casual chats.',
                'key_point' => 'Kolokasi communicate in person.',
            ],
            [
                'question_text' => 'Is online shopping a convenient way to buy groceries?',
                'model_answer' => 'Yes, online shopping is a convenient way to get groceries delivered without leaving home.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How has communication between friends changed with technology?',
                'model_answer' => 'Technology lets friends communicate with each other instantly through texts and voice notes.',
                'key_point' => 'Frasa communicate with each other.',
            ],
            [
                'question_text' => 'What is the most convenient way to manage your money?',
                'model_answer' => 'The most convenient way is to use a banking app that tracks all transactions automatically.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'Why do people find video calls a convenient way of staying in touch?',
                'model_answer' => 'Video calls are a convenient way to stay in touch because they let people see each other\'s faces.',
                'key_point' => 'Frasa stay in touch.',
            ],
            [
                'question_text' => 'How do you communicate with a customer service team?',
                'model_answer' => 'I usually communicate with customer service through a live chat feature on their website.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'Do you think email is still a convenient way to communicate?',
                'model_answer' => 'Yes, email is still a convenient way to communicate for formal and detailed messages.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'What challenges arise when people communicate with strangers online?',
                'model_answer' => 'People often misread tone, which creates misunderstandings when they communicate with strangers online.',
                'key_point' => 'Kolokasi communicate with strangers.',
            ],
            [
                'question_text' => 'Is it convenient to carry cash in modern cities?',
                'model_answer' => 'Cash is becoming less convenient because digital payments are faster and widely accepted.',
                'key_point' => 'Concept convenience of cash.',
            ],
            [
                'question_text' => 'How do you communicate with someone who speaks a different language?',
                'model_answer' => 'I communicate with them using simple words, gestures, and translation apps.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'What is a convenient way to learn new vocabulary quickly?',
                'model_answer' => 'Using flashcards on a mobile app is a convenient way to review new words during free moments.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'Do you communicate with your neighbors regularly?',
                'model_answer' => 'I communicate with my neighbors occasionally, mostly to exchange greetings and small favors.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'Why is public transport a convenient way to move around a city?',
                'model_answer' => 'Public transport is a convenient way to move around because it avoids parking problems and traffic stress.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How can a company communicate its values to customers?',
                'model_answer' => 'A company can communicate its values through advertising, social media, and responsible practices.',
                'key_point' => 'Kolokasi communicate values.',
            ],
            [
                'question_text' => 'What is the most convenient way to send documents quickly?',
                'model_answer' => 'Sharing files through a cloud app is the most convenient way to send documents instantly.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'Do you communicate with your teachers outside of class?',
                'model_answer' => 'Yes, I communicate with my teachers through email when I have questions about assignments.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'Has remote work made communication easier or harder?',
                'model_answer' => 'Remote work makes communication easier for quick updates but harder for casual team bonding.',
                'key_point' => 'Concept remote communication.',
            ],
            [
                'question_text' => 'What is a convenient way to book a hotel when traveling?',
                'model_answer' => 'Booking through a travel app is the most convenient way to compare prices and read reviews.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How do you communicate with support when a device breaks?',
                'model_answer' => 'I communicate with the support team through a chatbot before a technician contacts me.',
                'key_point' => 'Kolokasi communicate with.',
            ],
            [
                'question_text' => 'Why do some people still find phone calls a convenient way to talk?',
                'model_answer' => 'Phone calls are a convenient way to talk because they are hands-free and immediate.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How do friends communicate with each other during a travel trip?',
                'model_answer' => 'Friends communicate with each other through a shared group chat to coordinate plans.',
                'key_point' => 'Frasa communicate with each other.',
            ],
            [
                'question_text' => 'What is a convenient way to keep track of your schedule?',
                'model_answer' => 'Syncing a digital calendar with reminders is a convenient way to manage appointments.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'Do you think emojis improve the way people communicate?',
                'model_answer' => 'Yes, emojis help people communicate tone and emotion that plain text often loses.',
                'key_point' => 'Kolokasi communicate tone.',
            ],
            [
                'question_text' => 'What is the most convenient way to do your banking?',
                'model_answer' => 'Mobile banking is the most convenient way to check balances and transfer money on the go.',
                'key_point' => 'Kolokasi a convenient way.',
            ],
            [
                'question_text' => 'How can teams communicate with each other effectively on a project?',
                'model_answer' => 'Teams communicate with each other effectively by holding short stand-up meetings and sharing updates.',
                'key_point' => 'Frasa communicate effectively.',
            ],
        ];

        foreach ($questions7 as $q) {
            Question::create([
                'lesson_id' => $lesson7->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 8: Describe an App You Often Use on Your Phone ──────
        $lesson8 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 8,
            'title' => 'Describe an App You Often Use on Your Phone',
            'difficulty' => 'Medium',
        ]);

        $questions8 = [
            [
                'question_text' => 'Describe an app that you often use on your phone.',
                'model_answer' => 'I often use a note-taking app on my phone to capture tasks and ideas instantly.',
                'key_point' => 'Frasa use an app on your phone.',
            ],
            [
                'question_text' => 'What is the main purpose of this app for you?',
                'model_answer' => 'The main purpose for me is to remember reminders and keep my work organized throughout the day.',
                'key_point' => 'Frasa main purpose.',
            ],
            [
                'question_text' => 'How often do you open this app every day?',
                'model_answer' => 'I open this app dozens of times a day, whenever a thought or task crosses my mind.',
                'key_point' => 'Frasa how often.',
            ],
            [
                'question_text' => 'When did you first start using this app?',
                'model_answer' => 'I first started using it about three years ago when I began juggling multiple projects.',
                'key_point' => 'Frasa start using an app.',
            ],
            [
                'question_text' => 'Why do you find this app so useful?',
                'model_answer' => 'I find it useful because it syncs across all my devices and never loses my notes.',
                'key_point' => 'Frasa find an app useful.',
            ],
            [
                'question_text' => 'What features of this app do you rely on most?',
                'model_answer' => 'I rely most on the checklists, tags, and the instant search feature to find old notes.',
                'key_point' => 'Frasa app features.',
            ],
            [
                'question_text' => 'Is this app free or do you pay for it?',
                'model_answer' => 'The app is free to use, though I pay a small subscription for extra storage and tools.',
                'key_point' => 'Frasa pay for an app.',
            ],
            [
                'question_text' => 'Would you recommend this app to other people?',
                'model_answer' => 'Yes, I would definitely recommend it to anyone who struggles to stay organized.',
                'key_point' => 'Frasa recommend an app.',
            ],
            [
                'question_text' => 'How does this app help you save time?',
                'model_answer' => 'It saves time because I can make quick notes by voice instead of typing everything.',
                'key_point' => 'Frasa save time.',
            ],
            [
                'question_text' => 'Have you ever had problems with this app?',
                'model_answer' => 'Yes, it occasionally fails to sync, and I once lost a list of important reminders.',
                'key_point' => 'Frasa problems with an app.',
            ],
            [
                'question_text' => 'Do you prefer apps with a simple or a complex design?',
                'model_answer' => 'I prefer a simple design because an uncluttered interface makes an app much faster to use.',
                'key_point' => 'Concept app design.',
            ],
            [
                'question_text' => 'How does an app\'s design affect whether you keep using it?',
                'model_answer' => 'A clean and intuitive design makes me want to keep using an app, while clutter makes me uninstall it.',
                'key_point' => 'Frasa keep using an app.',
            ],
            [
                'question_text' => 'Why are mobile apps more popular than websites these days?',
                'model_answer' => 'Mobile apps are more popular because they offer notifications, offline access, and smoother performance.',
                'key_point' => 'Frasa mobile apps.',
            ],
            [
                'question_text' => 'What is the most necessary feature of a phone app?',
                'model_answer' => 'The most necessary feature is reliability, because an app that crashes constantly is useless.',
                'key_point' => 'Frasa necessary feature.',
            ],
            [
                'question_text' => 'How do you discover new useful apps?',
                'model_answer' => 'I discover new apps through friends\' recommendations and curated lists in the app store.',
                'key_point' => 'Frasa discover apps.',
            ],
            [
                'question_text' => 'Do you ever pay for apps, or only use free ones?',
                'model_answer' => 'I only pay for apps I use daily, because free versions usually cover all my basic needs.',
                'key_point' => 'Frasa pay for apps.',
            ],
            [
                'question_text' => 'What are the dangers of downloading too many apps?',
                'model_answer' => 'Downloading too many apps fills up storage, drains the battery, and floods you with notifications.',
                'key_point' => 'Concept downloading apps.',
            ],
            [
                'question_text' => 'How do app updates improve your experience?',
                'model_answer' => 'App updates improve my experience by adding features, fixing bugs, and increasing security.',
                'key_point' => 'Frasa app updates.',
            ],
            [
                'question_text' => 'Which app could you not live without?',
                'model_answer' => 'I could not live without my messaging app because it connects me to work, family, and friends.',
                'key_point' => 'Frasa could not live without.',
            ],
            [
                'question_text' => 'How has this app changed the way you organize your life?',
                'model_answer' => 'It changed my organization completely, replacing my paper planner and scattered sticky notes.',
                'key_point' => 'Frasa organize your life.',
            ],
            [
                'question_text' => 'Do you trust apps to keep your personal information safe?',
                'model_answer' => 'I trust well-known apps with strong privacy policies, but I am cautious about granting permissions.',
                'key_point' => 'Concept app privacy.',
            ],
            [
                'question_text' => 'What should developers do to make apps more user-friendly?',
                'model_answer' => 'Developers should test apps with real users and keep instructions simple and clear.',
                'key_point' => 'Concept user-friendly apps.',
            ],
            [
                'question_text' => 'How do you manage the notifications from your apps?',
                'model_answer' => 'I disable unnecessary notifications so my apps only alert me about important events.',
                'key_point' => 'Frasa manage notifications.',
            ],
            [
                'question_text' => 'Would you prefer using an app over asking a person for help?',
                'model_answer' => 'For routine tasks I prefer an app, but for complex problems I still value human help.',
                'key_point' => 'Frasa use an app.',
            ],
            [
                'question_text' => 'What makes an app addictive in a negative way?',
                'model_answer' => 'Endless feeds and reward mechanisms make apps addictive and steal too much of your time.',
                'key_point' => 'Concept addictive apps.',
            ],
            [
                'question_text' => 'How has this app made your daily commute better?',
                'model_answer' => 'During my commute I use it to catch up on reading, which turns wasted time into productive time.',
                'key_point' => 'Frasa daily commute.',
            ],
            [
                'question_text' => 'Do you think apps will replace traditional services?',
                'model_answer' => 'Apps will replace many routine services, but personal services like banking advice will remain.',
                'key_point' => 'Concept apps replacing services.',
            ],
            [
                'question_text' => 'What is the perfect app for language learners?',
                'model_answer' => 'The perfect language app should combine flashcards, speaking practice, and real conversations.',
                'key_point' => 'Frasa language app.',
            ],
            [
                'question_text' => 'How do you decide whether to delete an app you no longer use?',
                'model_answer' => 'I delete an app when I have not opened it for over a month and it uses too much space.',
                'key_point' => 'Frasa delete an app.',
            ],
            [
                'question_text' => 'Would you recommend this app to a beginner user?',
                'model_answer' => 'Yes, I would recommend it to beginners because its interface is simple and easy to learn.',
                'key_point' => 'Frasa recommend an app.',
            ],
        ];

        foreach ($questions8 as $q) {
            Question::create([
                'lesson_id' => $lesson8->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 9: App Collocations ─────────────────────────────────
        $lesson9 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 9,
            'title' => 'App Collocations',
            'difficulty' => 'Medium',
        ]);

        $questions9 = [
            [
                'question_text' => 'What kinds of apps do students use the most?',
                'model_answer' => 'Students mostly use education, messaging, note-taking, and calendar apps for study and social life.',
                'key_point' => 'Frasa education apps.',
            ],
            [
                'question_text' => 'Why do people install an app and then never open it?',
                'model_answer' => 'People install an app out of curiosity and then abandon it when it fails to meet their needs.',
                'key_point' => 'Kolokasi install an app.',
            ],
            [
                'question_text' => 'Do apps make people lazier or more efficient?',
                'model_answer' => 'Apps make people more efficient by automating tasks, though they can also encourage procrastination.',
                'key_point' => 'Concept app effect.',
            ],
            [
                'question_text' => 'How often do you update the apps on your phone?',
                'model_answer' => 'I update the apps automatically at night so I always have the latest features and fixes.',
                'key_point' => 'Kolokasi update an app.',
            ],
            [
                'question_text' => 'Why is it important to check an app\'s permissions before installing it?',
                'model_answer' => 'Checking permissions protects your privacy, because some apps request data they do not truly need.',
                'key_point' => 'Kolokasi an app permission.',
            ],
            [
                'question_text' => 'What is the difference between a free app and a paid app?',
                'model_answer' => 'A free app usually depends on ads or limits, while a paid app often removes ads and unlocks all features.',
                'key_point' => 'Concept free vs paid apps.',
            ],
            [
                'question_text' => 'Do you think mobile games are a waste of time?',
                'model_answer' => 'In moderation a mobile game is fine for relaxation, but it becomes a waste of time when overplayed.',
                'key_point' => 'Frasa a mobile game.',
            ],
            [
                'question_text' => 'How do you protect yourself from fake or harmful apps?',
                'model_answer' => 'I only download apps from official stores, check reviews, and read the developer information.',
                'key_point' => 'Concept harmful apps.',
            ],
            [
                'question_text' => 'What apps are popular with older people in your country?',
                'model_answer' => 'Older people favor simple messaging, calculator, and health apps that are easy to read and use.',
                'key_point' => 'Frasa popular with older people.',
            ],
            [
                'question_text' => 'Why do some apps ask for a subscription fee?',
                'model_answer' => 'Apps charge a subscription fee to fund development and keep the service running without ads.',
                'key_point' => 'Frasa subscription fee.',
            ],
            [
                'question_text' => 'How has this weather app helped you plan your days?',
                'model_answer' => 'This weather app helps me plan by giving hourly forecasts and rain alerts for my area.',
                'key_point' => 'Concept weather app.',
            ],
            [
                'question_text' => 'Do you think apps should work without an internet connection?',
                'model_answer' => 'Yes, important features should work offline so users are not helpless without a connection.',
                'key_point' => 'Concept offline apps.',
            ],
            [
                'question_text' => 'What makes an app trustworthy in your opinion?',
                'model_answer' => 'An app is trustworthy when it has a clear privacy policy, good reviews, and reputable developers.',
                'key_point' => 'Frasa a trustworthy app.',
            ],
            [
                'question_text' => 'How do developers earn money from free apps?',
                'model_answer' => 'Developers earn from free apps through advertising, premium upgrades, and in-app purchases.',
                'key_point' => 'Frasa in-app purchases.',
            ],
            [
                'question_text' => 'Which app feature do you wish every app had?',
                'model_answer' => 'I wish every app had a dark mode and a simple way to back up all of your data.',
                'key_point' => 'Frasa app feature.',
            ],
            [
                'question_text' => 'Why do some people refuse to use banking apps?',
                'model_answer' => 'Some people refuse them out of fear of fraud and a lack of familiarity with technology.',
                'key_point' => 'Concept banking apps.',
            ],
            [
                'question_text' => 'How do apps help people stay healthy and fit?',
                'model_answer' => 'Health apps track steps, remind users to drink water, and guide them through workouts.',
                'key_point' => 'Frasa health apps.',
            ],
            [
                'question_text' => 'Do you read an app\'s terms and conditions before using it?',
                'model_answer' => 'Honestly, I rarely read them, but I should because they explain how my data is used.',
                'key_point' => 'Concept terms and conditions.',
            ],
            [
                'question_text' => 'What should you do if an app stops responding?',
                'model_answer' => 'You should close the app, restart your phone, and reinstall it if the problem continues.',
                'key_point' => 'Concept app troubleshooting.',
            ],
            [
                'question_text' => 'How has this navigation app changed the way you travel?',
                'model_answer' => 'The navigation app lets me avoid traffic jams and find new places I would never have discovered.',
                'key_point' => 'Frasa a navigation app.',
            ],
            [
                'question_text' => 'Why is it important to back up app data regularly?',
                'model_answer' => 'Backing up app data protects your information if your phone is lost, damaged, or replaced.',
                'key_point' => 'Concept back up app data.',
            ],
            [
                'question_text' => 'Do apps encourage you to spend more time than you should?',
                'model_answer' => 'Yes, many apps are designed to hold your attention, so I set limits to control my usage.',
                'key_point' => 'Concept app screen time.',
            ],
            [
                'question_text' => 'What is a good way to discover the best version of an app?',
                'model_answer' => 'A good way is to compare ratings, read recent reviews, and check which version others recommend.',
                'key_point' => 'Frasa the best app.',
            ],
            [
                'question_text' => 'How do you feel when an app changes its interface?',
                'model_answer' => 'I feel frustrated at first, but I usually adjust once I learn where everything moved.',
                'key_point' => 'Concept app interface changes.',
            ],
            [
                'question_text' => 'Should parents control what apps their children use?',
                'model_answer' => 'Yes, parents should monitor app usage and set limits to keep children safe online.',
                'key_point' => 'Concept parental app control.',
            ],
            [
                'question_text' => 'Why do some apps require more storage than others?',
                'model_answer' => 'Complex apps with videos, games, and rich graphics require much more storage than simple text apps.',
                'key_point' => 'Concept app storage.',
            ],
            [
                'question_text' => 'How can an app help you improve your English speaking?',
                'model_answer' => 'A speaking app offers recording, feedback, and conversation partners to practice your fluency.',
                'key_point' => 'Concept speaking apps.',
            ],
            [
                'question_text' => 'Do you keep your apps organized or scattered across your screen?',
                'model_answer' => 'I keep my apps organized in folders so I can find what I need without searching.',
                'key_point' => 'Frasa organize apps.',
            ],
            [
                'question_text' => 'What would life be like without mobile apps?',
                'model_answer' => 'Life without mobile apps would be slower and less convenient, but also calmer and less distracting.',
                'key_point' => 'Concept life without apps.',
            ],
            [
                'question_text' => 'How do you rate an app after using it?',
                'model_answer' => 'I rate an app based on its usefulness, performance, and how often I actually open it.',
                'key_point' => 'Frasa rate an app.',
            ],
        ];

        foreach ($questions9 as $q) {
            Question::create([
                'lesson_id' => $lesson9->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 10: Collocations: "Important Part", "Most People" ──
        $lesson10 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 10,
            'title' => 'Collocations: "Important Part", "Most People"',
            'difficulty' => 'Difficult',
        ]);

        $questions10 = [
            [
                'question_text' => 'Is technology an important part of your daily routine?',
                'model_answer' => 'Yes, technology is an important part of my daily routine because I use it for work and study.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'What is an important part of staying healthy?',
                'model_answer' => 'Regular exercise and a balanced diet are an important part of staying healthy.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'Do most people in your country use digital payments?',
                'model_answer' => 'Yes, most people in my country now use digital payments for their everyday purchases.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'What do most people do to relax after work?',
                'model_answer' => 'Most people relax after work by watching TV, scrolling social media, or exercising.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'Why is sleep an important part of a healthy lifestyle?',
                'model_answer' => 'Sleep is an important part of a healthy lifestyle because it restores both the body and the mind.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'Does music play an important part in most people\'s lives?',
                'model_answer' => 'Yes, music plays an important part in most people\'s lives because it influences their mood.',
                'key_point' => 'Frasa play an important part.',
            ],
            [
                'question_text' => 'How important is breakfast for most people in the morning?',
                'model_answer' => 'Breakfast is important for most people because it provides energy to start the day.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'What is an important part of learning a new language?',
                'model_answer' => 'Speaking practice is an important part of learning a new language because it builds confidence.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'Why do most people prefer streaming to traditional TV now?',
                'model_answer' => 'Most people prefer streaming because it offers more choice, no schedule, and fewer ad breaks.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'Is family an important part of most cultures?',
                'model_answer' => 'Yes, family is an important part of most cultures because it shapes values and traditions.',
                'key_point' => 'Frasa an important part of.',
            ],
            [
                'question_text' => 'What role do teachers play in the important part of education?',
                'model_answer' => 'Teachers are an important part of education because they guide and inspire students directly.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'How do most people feel about commutes to work?',
                'model_answer' => 'Most people feel a commute is a necessary but tiring part of their working days.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'Why is saving money an important part of financial planning?',
                'model_answer' => 'Saving money is an important part of financial planning because it prepares you for emergencies.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'What do most people look for when buying a new phone?',
                'model_answer' => 'Most people look for a good camera, long battery life, and a reasonable price.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'How has social media become an important part of communication?',
                'model_answer' => 'Social media has become an important part of communication for staying connected with distant friends.',
                'key_point' => 'Frasa an important part of.',
            ],
            [
                'question_text' => 'Do most people in your country eat out or cook at home?',
                'model_answer' => 'Most people in my country cook at home, though eating out is popular on weekends.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'Why is teamwork an important part of professional success?',
                'model_answer' => 'Teamwork is an important part of professional success because big projects need cooperation.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'What is the most important part of a healthy relationship, as most people see it?',
                'model_answer' => 'Most people agree that trust and communication form the most important part of a healthy relationship.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'How do most people in your city travel to work?',
                'model_answer' => 'Most people in my city travel to work by motorcycle or public transport.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'Is exercise an important part of your weekly routine?',
                'model_answer' => 'Yes, exercise is an important part of my weekly routine that helps me manage stress.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'Why is reading an important part of personal growth?',
                'model_answer' => 'Reading is an important part of personal growth because it expands knowledge and empathy.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'What do most people think about working from home?',
                'model_answer' => 'Most people think working from home offers freedom, though some miss the office routine.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'How does trust play an important part in teamwork?',
                'model_answer' => 'Trust plays an important part in teamwork because people cooperate better when they rely on each other.',
                'key_point' => 'Frasa play an important part.',
            ],
            [
                'question_text' => 'Do most people consider learning English important nowadays?',
                'model_answer' => 'Yes, most people consider learning English important because it opens global opportunities.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'What is an important part of preparing for an interview?',
                'model_answer' => 'Researching the company is an important part of preparing for an interview successfully.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'Why do most people take photos when they travel?',
                'model_answer' => 'Most people take photos when they travel to capture memories and share their experiences.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'Is good nutrition an important part of an athlete\'s training?',
                'model_answer' => 'Good nutrition is an important part of training because it fuels performance and recovery.',
                'key_point' => 'Kolokasi an important part.',
            ],
            [
                'question_text' => 'What do most people struggle with when learning a new skill?',
                'model_answer' => 'Most people struggle with staying consistent and finding time to practice a new skill.',
                'key_point' => 'Kolokasi most people.',
            ],
            [
                'question_text' => 'How is language an important part of cultural identity?',
                'model_answer' => 'Language is an important part of cultural identity because it preserves history and tradition.',
                'key_point' => 'Frasa an important part of.',
            ],
            [
                'question_text' => 'Are holidays an important part of most people\'s year?',
                'model_answer' => 'Yes, holidays are an important part of most people\'s year because they provide rest and joy.',
                'key_point' => 'Kolokasi an important part.',
            ],
        ];

        foreach ($questions10 as $q) {
            Question::create([
                'lesson_id' => $lesson10->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 11: Describe Something You Own That\'s Very Useful ──
        $lesson11 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 11,
            'title' => 'Describe Something You Own That\'s Very Useful',
            'difficulty' => 'Medium',
        ]);

        $questions11 = [
            [
                'question_text' => 'Describe something you own that is very useful.',
                'model_answer' => 'I own a wireless headphone that is very useful for calls, study, and workouts.',
                'key_point' => 'Frasa very useful object.',
            ],
            [
                'question_text' => 'When and where did you get this item?',
                'model_answer' => 'I bought it two years ago at an electronics store during a clearance sale.',
                'key_point' => 'Context when and where.',
            ],
            [
                'question_text' => 'How do you use this item in your daily life?',
                'model_answer' => 'I use it every day during my commute and while studying to block out noise.',
                'key_point' => 'Frasa in daily life.',
            ],
            [
                'question_text' => 'Why do you consider this object so useful?',
                'model_answer' => 'I consider it extremely useful because it is comfortable, long-lasting, and truly multipurpose.',
                'key_point' => 'Frasa consider something useful.',
            ],
            [
                'question_text' => 'Would you buy this item again if it broke?',
                'model_answer' => 'Yes, I would buy the same model again because it has never let me down.',
                'key_point' => 'Frasa buy something again.',
            ],
            [
                'question_text' => 'What is the most useful thing you have ever received as a gift?',
                'model_answer' => 'The most useful gift I received is a power bank that saves me during long trips.',
                'key_point' => 'Frasa the most useful gift.',
            ],
            [
                'question_text' => 'How do you take care of your valuable gadgets?',
                'model_answer' => 'I keep them in protective cases, avoid dropping them, and charge them properly.',
                'key_point' => 'Frasa take care of gadgets.',
            ],
            [
                'question_text' => 'Why do people buy items they rarely end up using?',
                'model_answer' => 'People buy on impulse or follow trends, then realize the item does not fit their real needs.',
                'key_point' => 'Concept rarely used items.',
            ],
            [
                'question_text' => 'Are everyday objects becoming smarter these days?',
                'model_answer' => 'Yes, everyday objects like watches and appliances now connect to apps and automate tasks.',
                'key_point' => 'Frasa smart objects.',
            ],
            [
                'question_text' => 'How important are portable chargers in your life?',
                'model_answer' => 'A portable charger is very important to me because my phone battery drains quickly.',
                'key_point' => 'Frasa portable chargers.',
            ],
            [
                'question_text' => 'What small item would you never leave home without?',
                'model_answer' => 'I would never leave home without my phone and my wallet, which I check every morning.',
                'key_point' => 'Frasa never leave home without.',
            ],
            [
                'question_text' => 'How do you decide whether to repair or replace a broken device?',
                'model_answer' => 'I replace a device when repairs cost more than half the price of a new one.',
                'key_point' => 'Concept repair or replace.',
            ],
            [
                'question_text' => 'Why is a reliable backpack an important possession for you?',
                'model_answer' => 'A reliable backpack is important because it protects my laptop during daily travel.',
                'key_point' => 'Frasa a reliable possession.',
            ],
            [
                'question_text' => 'What feature makes an electronic device truly useful?',
                'model_answer' => 'Long battery life and a simple interface make an electronic device truly useful.',
                'key_point' => 'Frasa useful feature.',
            ],
            [
                'question_text' => 'How has this item improved your everyday efficiency?',
                'model_answer' => 'It improved my efficiency because I can work and study anywhere without distractions.',
                'key_point' => 'Frasa improve efficiency.',
            ],
            [
                'question_text' => 'Do you prefer buying high-quality items that last longer?',
                'model_answer' => 'Yes, I prefer a small number of high-quality items that last longer instead of cheap replacements.',
                'key_point' => 'Concept buying quality.',
            ],
            [
                'question_text' => 'What is the most useful app on your phone right now?',
                'model_answer' => 'The most useful app on my phone is my note-taking app because I rely on it daily.',
                'key_point' => 'Frasa the most useful app.',
            ],
            [
                'question_text' => 'How do you protect yourself from buying useless items?',
                'model_answer' => 'I wait a few days before buying and ask whether the item would actually solve a need.',
                'key_point' => 'Concept avoiding useless items.',
            ],
            [
                'question_text' => 'Why is a good watch still useful in the age of smartphones?',
                'model_answer' => 'A good watch is useful because checking the time on your wrist is faster than unlocking a phone.',
                'key_point' => 'Concept usefulness of a watch.',
            ],
            [
                'question_text' => 'What useful kitchen tool do you use almost daily?',
                'model_answer' => 'I use a rice cooker almost daily because it makes cooking effortless and consistent.',
                'key_point' => 'Frasa useful kitchen tool.',
            ],
            [
                'question_text' => 'Do you value practicality or style in your possessions?',
                'model_answer' => 'I value practicality first, but I still choose items that look good as well.',
                'key_point' => 'Concept practicality vs style.',
            ],
            [
                'question_text' => 'How do you organize the useful things you own?',
                'model_answer' => 'I organize my things in labeled drawers and shelves so everything is easy to find.',
                'key_point' => 'Frasa organize your things.',
            ],
            [
                'question_text' => 'What is the most useful advice you ever received?',
                'model_answer' => 'The most useful advice I received was to focus on one skill at a time instead of trying everything.',
                'key_point' => 'Frasa the most useful advice.',
            ],
            [
                'question_text' => 'Why do some gadgets lose their usefulness quickly?',
                'model_answer' => 'Gadgets lose usefulness when new models arrive and old ones stop receiving software updates.',
                'key_point' => 'Concept gadgets losing usefulness.',
            ],
            [
                'question_text' => 'What useful habit do you practice every day?',
                'model_answer' => 'I practice the useful habit of making a short to-do list every morning before starting work.',
                'key_point' => 'Frasa useful habit.',
            ],
            [
                'question_text' => 'Would you mind sharing your useful items with others?',
                'model_answer' => 'I do not mind sharing useful items with friends and family whenever they need help.',
                'key_point' => 'Frasa share items.',
            ],
            [
                'question_text' => 'What makes a laptop the most useful device for students?',
                'model_answer' => 'A laptop is useful for students because it combines research, writing, and communication in one place.',
                'key_point' => 'Frasa useful device.',
            ],
            [
                'question_text' => 'How do you feel about owning too many possessions?',
                'model_answer' => 'Owners feel overwhelmed by too many possessions, so I try to keep only what I really use.',
                'key_point' => 'Concept too many possessions.',
            ],
            [
                'question_text' => 'Why is a comfortable chair a useful investment at home?',
                'model_answer' => 'A comfortable chair is a useful investment because it protects your back during long work hours.',
                'key_point' => 'Frasa useful investment.',
            ],
            [
                'question_text' => 'What would you buy next to make your life more convenient?',
                'model_answer' => 'I would buy a smart speaker to make my daily tasks more convenient through voice commands.',
                'key_point' => 'Frasa make life convenient.',
            ],
        ];

        foreach ($questions11 as $q) {
            Question::create([
                'lesson_id' => $lesson11->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        // ── LESSON 12: Collocations with Use and Useful ────────────────
        $lesson12 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 12,
            'title' => 'Collocations with Use and Useful',
            'difficulty' => 'Difficult',
        ]);

        $questions12 = [
            [
                'question_text' => 'How useful is your phone for studying?',
                'model_answer' => 'My phone is really useful for studying because it gives me access to apps and dictionaries.',
                'key_point' => 'Frasa useful for.',
            ],
            [
                'question_text' => 'What do people use public transport for?',
                'model_answer' => 'People use public transport for commuting to work and traveling around the city cheaply.',
                'key_point' => 'Frasa use something for.',
            ],
            [
                'question_text' => 'Do you make good use of your free time?',
                'model_answer' => 'Yes, I try to make good use of my free time by reading and exercising.',
                'key_point' => 'Kolokasi make good use of.',
            ],
            [
                'question_text' => 'Is it useful to learn more than one language?',
                'model_answer' => 'Yes, it is very useful to learn more than one language for travel, work, and new friendships.',
                'key_point' => 'Frasa be useful to.',
            ],
            [
                'question_text' => 'How do you put your skills to good use?',
                'model_answer' => 'I put my writing skills to good use by helping friends edit their resumes.',
                'key_point' => 'Kolokasi put to good use.',
            ],
            [
                'question_text' => 'What is the most useful tool in your kitchen?',
                'model_answer' => 'The most useful tool in my kitchen is a good knife that makes food preparation easy.',
                'key_point' => 'Frasa a useful tool.',
            ],
            [
                'question_text' => 'Should people reuse old items instead of throwing them away?',
                'model_answer' => 'Yes, people should reuse old items to save money and protect the environment.',
                'key_point' => 'Kolokasi reuse old items.',
            ],
            [
                'question_text' => 'How do you use technology to improve your English?',
                'model_answer' => 'I use technology by watching videos, using flashcard apps, and talking with online tutors.',
                'key_point' => 'Frasa use technology.',
            ],
            [
                'question_text' => 'Why is it useful to plan your day in advance?',
                'model_answer' => 'Planning in advance is useful because it prevents panic and helps you use time wisely.',
                'key_point' => 'Frasa it is useful to.',
            ],
            [
                'question_text' => 'How do you make good use of old clothes?',
                'model_answer' => 'I make good use of old clothes by donating them or turning them into cleaning rags.',
                'key_point' => 'Kolokasi make good use of.',
            ],
            [
                'question_text' => 'Is a dictionary still useful in a digital world?',
                'model_answer' => 'Yes, a dictionary is still useful, though online dictionaries are now more convenient.',
                'key_point' => 'Frasa a useful dictionary.',
            ],
            [
                'question_text' => 'What is the most useful feature of your phone?',
                'model_answer' => 'The most useful feature of my phone is the high-quality camera for capturing important moments.',
                'key_point' => 'Frasa useful feature.',
            ],
            [
                'question_text' => 'How do people use social media for learning?',
                'model_answer' => 'People use social media for learning by following educational pages and short tutorial videos.',
                'key_point' => 'Frasa use social media for.',
            ],
            [
                'question_text' => 'Why is it useful to keep a daily journal?',
                'model_answer' => 'Keeping a daily journal is useful because it helps you reflect and manage your emotions.',
                'key_point' => 'Frasa it is useful to.',
            ],
            [
                'question_text' => 'How can you put your free evening hours to good use?',
                'model_answer' => 'I put my free evening hours to good use by studying English or practicing my hobbies.',
                'key_point' => 'Kolokasi put to good use.',
            ],
            [
                'question_text' => 'What useful habit should everyone develop?',
                'model_answer' => 'Everyone should develop the useful habit of reading a little every day.',
                'key_point' => 'Frasa a useful habit.',
            ],
            [
                'question_text' => 'How do you use your memory when learning new words?',
                'model_answer' => 'I use my memory by linking new words to images and reviewing them with spaced repetition.',
                'key_point' => 'Frasa use your memory.',
            ],
            [
                'question_text' => 'Is it useful to learn skills outside your field?',
                'model_answer' => 'Yes, it is useful to learn unrelated skills because they often combine in surprising ways.',
                'key_point' => 'Frasa it is useful to.',
            ],
            [
                'question_text' => 'How do companies make good use of customer feedback?',
                'model_answer' => 'Companies make good use of feedback to improve products and respond to complaints.',
                'key_point' => 'Kolokasi make good use of.',
            ],
            [
                'question_text' => 'What is the most useful piece of clothing you own?',
                'model_answer' => 'The most useful piece of clothing I own is a waterproof jacket that I wear in all seasons.',
                'key_point' => 'Frasa a useful piece of.',
            ],
            [
                'question_text' => 'Why is it useful to learn basic cooking skills?',
                'model_answer' => 'Basic cooking skills are useful because they save money and help you eat healthily.',
                'key_point' => 'Frasa it is useful to.',
            ],
            [
                'question_text' => 'How can students make good use of their study notes?',
                'model_answer' => 'Students make good use of their notes by reviewing them regularly before exams.',
                'key_point' => 'Kolokasi make good use of.',
            ],
            [
                'question_text' => 'Do you think everyone should use a budget to manage money?',
                'model_answer' => 'Yes, using a budget is useful because it helps people track spending and reach savings goals.',
                'key_point' => 'Frasa use a budget.',
            ],
            [
                'question_text' => 'What is the best way to use your time at work?',
                'model_answer' => 'The best way to use your time at work is to focus on the most important tasks first.',
                'key_point' => 'Frasa use your time.',
            ],
            [
                'question_text' => 'Why is it useful to exercise early in the morning?',
                'model_answer' => 'Morning exercise is useful because it boosts your energy and mood for the whole day.',
                'key_point' => 'Frasa it is useful to.',
            ],
            [
                'question_text' => 'How do you make good use of a rainy weekend?',
                'model_answer' => 'I make good use of a rainy weekend by cleaning, reading, and finishing pending tasks.',
                'key_point' => 'Kolokasi make good use of.',
            ],
            [
                'question_text' => 'What useful book would you recommend to a learner?',
                'model_answer' => 'I would recommend a book with practical exercises because it is useful for building real skills.',
                'key_point' => 'Frasa a useful book.',
            ],
            [
                'question_text' => 'How do you use feedback to improve your speaking?',
                'model_answer' => 'I use feedback by noting my repeated mistakes and practicing those areas with a tutor.',
                'key_point' => 'Frasa use feedback.',
            ],
            [
                'question_text' => 'Is it useful to set goals before starting a project?',
                'model_answer' => 'Yes, setting goals is useful because it gives the project direction and a way to measure success.',
                'key_point' => 'Frasa it is useful to.',
            ],
            [
                'question_text' => 'How can people make good use of their holiday time?',
                'model_answer' => 'People make good use of holiday time by resting, traveling, and spending time with family.',
                'key_point' => 'Kolokasi make good use of.',
            ],
        ];

        foreach ($questions12 as $q) {
            Question::create([
                'lesson_id' => $lesson12->id,
                'question_text' => $q['question_text'],
                'model_answer' => $q['model_answer'],
                'key_point' => $q['key_point'],
            ]);
        }

        $this->command->info('Hobbies & Entertainment Unit (Unit 10) seeded successfully!');
        $this->command->info('Lesson 1: ' . count($questions1) . ' questions');
        $this->command->info('Lesson 2: ' . count($questions2) . ' questions');
        $this->command->info('Lesson 3: ' . count($questions3) . ' questions');
        $this->command->info('Lesson 4: ' . count($questions4) . ' questions');
        $this->command->info('Lesson 5: ' . count($questions5) . ' questions');
        $this->command->info('Lesson 6: ' . count($questions6) . ' questions');
        $this->command->info('Lesson 7: ' . count($questions7) . ' questions');
        $this->command->info('Lesson 8: ' . count($questions8) . ' questions');
        $this->command->info('Lesson 9: ' . count($questions9) . ' questions');
        $this->command->info('Lesson 10: ' . count($questions10) . ' questions');
        $this->command->info('Lesson 11: ' . count($questions11) . ' questions');
        $this->command->info('Lesson 12: ' . count($questions12) . ' questions');
    }
}