<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class PlacesUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Unit 9: Places (Part 2) ──────────────────────
        $unit = Unit::create([
            'unit_number' => 9,
            'title' => 'Places',
            'part' => 2,
            'outcome' => 'Menggunakan frasa lokasi (in my hometown, far away), deskripsi tempat umum, dan kolokasi ruangan (meeting place, place of interest, birthplace).',
        ]);

        // ── LESSON 1: Collocations: "In My Hometown", "Far Away" ─────────
        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations: "In My Hometown", "Far Away"',
            'difficulty' => 'Medium',
        ]);

        $questions1 = [
            [
                'question_text' => 'Are there any beautiful public parks in your hometown?',
                'model_answer' => 'Yes, in my hometown there is a spacious central park with ancient trees and a lake.',
                'key_point' => 'Kolokasi in my hometown.',
            ],
            [
                'question_text' => 'Do you currently live far away from your family?',
                'model_answer' => 'Yes, I moved to the capital for work, so I live quite far away from my parents now.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'What is the most famous landmark in your hometown?',
                'model_answer' => 'The most famous landmark in my hometown is an ancient stone temple built in the 18th century.',
                'key_point' => 'Kolokasi in my hometown.',
            ],
            [
                'question_text' => 'Is your workplace far away from your residential area?',
                'model_answer' => 'No, it\'s not far away; it only takes about fifteen minutes by motorcycle.',
                'key_point' => 'Negasi & kolokasi far away.',
            ],
            [
                'question_text' => 'How has the air quality changed in your hometown over the years?',
                'model_answer' => 'Unfortunately, industrial growth in my hometown has led to increased air pollution.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'How do you keep in touch with friends who live far away?',
                'model_answer' => 'We use instant messaging apps and host group video calls to stay connected despite being far away.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'Is it easy to find traditional local dishes in your hometown?',
                'model_answer' => 'Yes, street vendors in my hometown serve authentic regional dishes at every corner.',
                'key_point' => 'Kolokasi in my hometown.',
            ],
            [
                'question_text' => 'Would you like to move to a country that is far away from here?',
                'model_answer' => 'I\'d love to experience living in Europe for a few years, even though it is very far away.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'What public transport options are available in your hometown?',
                'model_answer' => 'Public transport in my hometown relies mainly on local commuter buses and angkot.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'Do you miss anything when you travel far away from home?',
                'model_answer' => 'Whenever I travel far away, I miss my mother\'s home-cooked meals and my comfortable bed.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'Is housing affordable for young couples in your hometown?',
                'model_answer' => 'Housing prices in my hometown are still relatively reasonable compared to major metropolitan cities.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'Why do some people prefer to study at a university that is far away?',
                'model_answer' => 'Studying far away forces young adults to become independent and adapt to new environments.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'Are there any historical museums located in your hometown?',
                'model_answer' => 'Yes, there is a cultural museum in my hometown displaying regional artifacts and textiles.',
                'key_point' => 'Kolokasi in my hometown.',
            ],
            [
                'question_text' => 'Is the nearest hospital located far away from your neighborhood?',
                'model_answer' => 'No, a general hospital is situated just two kilometers down the road, not far away at all.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'What do tourists usually do when they visit places in your hometown?',
                'model_answer' => 'Tourists in my hometown usually explore local night markets and visit surrounding waterfalls.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'How do you prepare for a long-distance road trip to a place far away?',
                'model_answer' => 'I inspect my car\'s tires and engine, pack emergency supplies, and download offline maps.',
                'key_point' => 'Preposisi far away.',
            ],
            [
                'question_text' => 'Is there a strong sense of community among people in your hometown?',
                'model_answer' => 'Yes, neighbors in my hometown know each other well and frequently hold social gatherings.',
                'key_point' => 'Frasa in my hometown.',
            ],
            [
                'question_text' => 'Would you feel lonely if you moved to an island far away?',
                'model_answer' => 'Initially yes, but staying busy with outdoor activities would help me adjust.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'What is the weather usually like during summer in your hometown?',
                'model_answer' => 'Summers in my hometown are warm and humid, often accompanied by afternoon tropical rain.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'Is it expensive to ship goods to remote regions located far away?',
                'model_answer' => 'Yes, shipping costs spike significantly when sending parcels to islands located far away.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'Are there any major universities in your hometown?',
                'model_answer' => 'My hometown hosts two prominent state universities that attract students nationwide.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'Do you prefer working close to home or in a business hub far away?',
                'model_answer' => 'I prefer working close to home because commuting far away wastes precious hours daily.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'What natural scenery surrounds the area in your hometown?',
                'model_answer' => 'My hometown is nestled in a lush valley surrounded by rolling green hills and mountain peaks.',
                'key_point' => 'Deskripsi in my hometown.',
            ],
            [
                'question_text' => 'How long does it take to travel to a beach from where you live?',
                'model_answer' => 'The coastline is quite far away; it takes at least a four-hour drive to reach the beach.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'Is job growth increasing or decreasing in your hometown?',
                'model_answer' => 'Job opportunities in my hometown are expanding rapidly due to new commercial developments.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'Why do elderly people often dislike traveling to places far away?',
                'model_answer' => 'Because long journeys far away cause physical fatigue and discomfort for senior citizens.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'Do you plan to live in your hometown after retirement?',
                'model_answer' => 'Yes, I intend to return to my hometown to enjoy a peaceful, slow-paced lifestyle.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'Is it difficult to get medical care if you live in a village far away?',
                'model_answer' => 'Yes, living far away from urban centers often means limited access to specialized doctors.',
                'key_point' => 'Kolokasi far away.',
            ],
            [
                'question_text' => 'What traditional festival is celebrated enthusiastically in your hometown?',
                'model_answer' => 'The annual harvest festival in my hometown is celebrated with colorful parades and traditional music.',
                'key_point' => 'Context in my hometown.',
            ],
            [
                'question_text' => 'How do you feel when looking at night stars in a place far away from city lights?',
                'model_answer' => 'Star-gazing far away from urban light pollution gives a humbling and peaceful feeling.',
                'key_point' => 'Kolokasi far away.',
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

        // ── LESSON 2: Describe a Public Place You Visit ──────────────
        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'Describe a Public Place You Visit',
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'question_text' => 'What public place do you visit frequently in your town?',
                'model_answer' => 'A public place I visit regularly is the central city library located near the main plaza.',
                'key_point' => 'Identifikasi tempat umum.',
            ],
            [
                'question_text' => 'Where is this public place located and how do you get there?',
                'model_answer' => 'It is situated in the heart of downtown, easily accessible by a short bus ride from my flat.',
                'key_point' => 'Lokasi & transportasi.',
            ],
            [
                'question_text' => 'What activities do people usually do when visiting this public place?',
                'model_answer' => 'Visitors read books, study in quiet zones, attend workshops, or use the free computer lab.',
                'key_point' => 'Aktivitas pengunjung.',
            ],
            [
                'question_text' => 'Who do you usually go with when visiting this public place?',
                'model_answer' => 'I usually go there alone when I need deep focus, but occasionally I meet study partners.',
                'key_point' => 'Pendamping kunjungan.',
            ],
            [
                'question_text' => 'Why do you prefer visiting this public place over other spots?',
                'model_answer' => 'I prefer it because of the peaceful atmosphere, free Wi-Fi, and air-conditioned reading rooms.',
                'key_point' => 'Alasan preferensi.',
            ],
            [
                'question_text' => 'Is this public place crowded during weekends?',
                'model_answer' => 'Yes, students and families fill the main reading halls, especially on Sunday afternoons.',
                'key_point' => 'Kepadatan tempat.',
            ],
            [
                'question_text' => 'What facilities or amenities does this public place offer to visitors?',
                'model_answer' => 'It offers comfortable seating, accessible ramps, a small cafe, and clean restrooms.',
                'key_point' => 'Fasilitas umum.',
            ],
            [
                'question_text' => 'How has this public place changed since you first visited it?',
                'model_answer' => 'It was recently renovated with modern digital catalog screens and expanded study pods.',
                'key_point' => 'Perubahan/renovasi.',
            ],
            [
                'question_text' => 'Is entry to this public place free or do you need to pay a fee?',
                'model_answer' => 'Entry is completely free for the public, though borrowing books requires a annual membership.',
                'key_point' => 'Biaya masuk (entry fee).',
            ],
            [
                'question_text' => 'What is the atmosphere like inside this public place?',
                'model_answer' => 'The atmosphere is quiet, orderly, and highly conducive to productivity and reading.',
                'key_point' => 'Suasana (atmosphere).',
            ],
            [
                'question_text' => 'Do children enjoy visiting this public place?',
                'model_answer' => 'Yes, there is a dedicated children\'s section with colorful storybooks and play corners.',
                'key_point' => 'Fasilitas anak.',
            ],
            [
                'question_text' => 'Is this public place well-maintained by local authorities?',
                'model_answer' => 'Extremely well; janitorial staff keep it spotless and security guards ensure safety.',
                'key_point' => 'Pemeliharaan (maintenance).',
            ],
            [
                'question_text' => 'What time of day is best to visit this public place to avoid crowds?',
                'model_answer' => 'Early morning on weekdays right when it opens at 8 AM is the quietest time.',
                'key_point' => 'Waktu berkunjung ideal.',
            ],
            [
                'question_text' => 'How does this public place benefit the local community?',
                'model_answer' => 'It provides equal access to educational resources and serves as a quiet community hub.',
                'key_point' => 'Manfaat komunitas.',
            ],
            [
                'question_text' => 'What is your favorite corner or spot inside this public place?',
                'model_answer' => 'My favorite spot is a window desk on the third floor overlooking the green garden outside.',
                'key_point' => 'Spot favorit internal.',
            ],
            [
                'question_text' => 'Is this public place accessible for people with disabilities?',
                'model_answer' => 'Yes, it features wheelchair ramps, wide elevators, and accessible restroom facilities.',
                'key_point' => 'Aksesibilitas (disability access).',
            ],
            [
                'question_text' => 'Are there any rules visitors must follow in this public place?',
                'model_answer' => 'Visitors must keep noise levels low, set phones to silent, and refrain from eating near books.',
                'key_point' => 'Aturan tempat (rules).',
            ],
            [
                'question_text' => 'How long do you usually stay when you visit this public place?',
                'model_answer' => 'I typically spend around three to four hours there studying or reading.',
                'key_point' => 'Durasi kunjungan.',
            ],
            [
                'question_text' => 'What natural elements are present in or around this public place?',
                'model_answer' => 'The building is surrounded by landscaped gardens and features large glass windows for sunlight.',
                'key_point' => 'Elemen lanskap alam.',
            ],
            [
                'question_text' => 'Is parking convenient for people visiting this public place?',
                'model_answer' => 'Yes, there is a spacious underground parking lot for cars and motorcycles.',
                'key_point' => 'Akses parkir.',
            ],
            [
                'question_text' => 'Does this public place host any special events or exhibitions?',
                'model_answer' => 'It hosts monthly book launches, art exhibitions, and educational seminars.',
                'key_point' => 'Event khusus.',
            ],
            [
                'question_text' => 'What architectural style does this public building feature?',
                'model_answer' => 'It features modern minimalist architecture with high ceilings and open concrete spaces.',
                'key_point' => 'Gaya arsitektur.',
            ],
            [
                'question_text' => 'How do local residents feel about this public space?',
                'model_answer' => 'Residents take great pride in it and consider it one of the town\'s best public assets.',
                'key_point' => 'Perspekta warga lokal.',
            ],
            [
                'question_text' => 'Is safety a concern when visiting this public place at night?',
                'model_answer' => 'Not at all; the surrounding courtyard is well-lit and monitored by CCTV cameras.',
                'key_point' => 'Keamanan (safety).',
            ],
            [
                'question_text' => 'Would you recommend this public place to tourists visiting your city?',
                'model_answer' => 'Definitely, especially to travelers looking for a quiet spot to work or appreciate modern architecture.',
                'key_point' => 'Rekomendasi turis.',
            ],
            [
                'question_text' => 'How does this public place promote environmental sustainability?',
                'model_answer' => 'It uses solar panels, rainwater harvesting systems, and waste sorting bins throughout.',
                'key_point' => 'Fitur ramah lingkungan.',
            ],
            [
                'question_text' => 'What food or beverage options are available near this public place?',
                'model_answer' => 'There is an in-house coffee shop and several local food stalls right outside the main gate.',
                'key_point' => 'Opsi kuliner sekitar.',
            ],
            [
                'question_text' => 'How does visiting this public place help you mental health?',
                'model_answer' => 'Stepping away from home distractions to sit in a quiet public space reduces my anxiety.',
                'key_point' => 'Dampak psikologis.',
            ],
            [
                'question_text' => 'What improvements would you suggest for this public place?',
                'model_answer' => 'I would suggest adding more electrical outlets for laptops and expanding the coffee shop.',
                'key_point' => 'Ide perbaikan fasilitas.',
            ],
            [
                'question_text' => 'Why are public places like this crucial for urban development?',
                'model_answer' => 'They foster social cohesion, offer green spaces, and elevate the overall livability of a city.',
                'key_point' => 'Nilai tata kota (urban planning).',
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

        // ── LESSON 3: Place Collocations ────────────────────
        $lesson3 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 3,
            'title' => 'Place Collocations',
            'difficulty' => 'Medium',
        ]);

        $questions3 = [
            [
                'question_text' => 'What is the most popular meeting place for young people in your town?',
                'model_answer' => 'The central shopping mall atrium is the most popular meeting place for teenagers.',
                'key_point' => 'Kolokasi meeting place.',
            ],
            [
                'question_text' => 'Are there many places of interest for tourists in your city?',
                'model_answer' => 'Yes, our city boasts several places of interest, including ancient temples and art galleries.',
                'key_point' => 'Kolokasi place of interest.',
            ],
            [
                'question_text' => 'Where do you go when you need a quiet place to think or work?',
                'model_answer' => 'I head to a quiet place like a local botanical garden or a quiet corner in the library.',
                'key_point' => 'Kolokasi quiet place.',
            ],
            [
                'question_text' => 'Is your birthplace different from where you grew up?',
                'model_answer' => 'No, my birthplace is the same coastal town where I spent my entire childhood.',
                'key_point' => 'Kolokasi birthplace.',
            ],
            [
                'question_text' => 'How easy is it to place an order at local restaurants using phone apps?',
                'model_answer' => 'It is seamless; you just scan a QR code on the table to place an order instantly.',
                'key_point' => 'Kolokasi place an order.',
            ],
            [
                'question_text' => 'Why is maintaining cleanliness in a public place important?',
                'model_answer' => 'Keeping a public place clean prevents disease spread and ensures an enjoyable environment.',
                'key_point' => 'Kolokasi public place.',
            ],
            [
                'question_text' => 'What is your ideal place of residence?',
                'model_answer' => 'My ideal place of residence is a peaceful suburban house with a garden near the mountains.',
                'key_point' => 'Kolokasi place of residence.',
            ],
            [
                'question_text' => 'Do you have a favorite place to eat seafood in your city?',
                'model_answer' => 'Yes, my favorite place to eat seafood is a harbor-side restaurant known for grilled fish.',
                'key_point' => 'Kolokasi place to eat.',
            ],
            [
                'question_text' => 'How do you find a safe place to park your vehicle in crowded areas?',
                'model_answer' => 'I look for official multi-story parking structures to ensure my vehicle is in a safe place.',
                'key_point' => 'Kolokasi safe place.',
            ],
            [
                'question_text' => 'What makes a landmark an iconic place of interest?',
                'model_answer' => 'Historical significance, unique architecture, and cultural value make a site an iconic place of interest.',
                'key_point' => 'Kolokasi place of interest.',
            ],
            [
                'question_text' => 'Do you prefer working in a bustling office or a quiet place at home?',
                'model_answer' => 'I perform best in a quiet place at home where I can concentrate without interruptions.',
                'key_point' => 'Kolokasi quiet place.',
            ],
            [
                'question_text' => 'What document proves your official place of birth?',
                'model_answer' => 'A formal birth certificate issued by the government verifies your place of birth.',
                'key_point' => 'Kolokasi place of birth.',
            ],
            [
                'question_text' => 'Is it customary to tip when you place an order at a cafe?',
                'model_answer' => 'In my country, tipping isn\'t mandatory when you place an order, but small change is appreciated.',
                'key_point' => 'Kolokasi place an order.',
            ],
            [
                'question_text' => 'Why is the central square a traditional meeting place?',
                'model_answer' => 'Because of its central location, open space, and accessibility by all public transport lines.',
                'key_point' => 'Kolokasi meeting place.',
            ],
            [
                'question_text' => 'What is the most sacred place of worship in your region?',
                'model_answer' => 'The historic grand mosque in the city center is the primary place of worship for locals.',
                'key_point' => 'Kolokasi place of worship.',
            ],
            [
                'question_text' => 'How do local authorities protect a designated historical place?',
                'model_answer' => 'By enforcing heritage preservation laws that restrict commercial modifications to the site.',
                'key_point' => 'Context historical place.',
            ],
            [
                'question_text' => 'Where do you usually go to find a peaceful place in nature?',
                'model_answer' => 'I hike up a nearby pine forest trail to find a peaceful place away from urban noise.',
                'key_point' => 'Kolokasi peaceful place.',
            ],
            [
                'question_text' => 'Is it safe to leave personal belongings unattended in a public place?',
                'model_answer' => 'No, leaving valuables unattended in a public place risks theft in crowded zones.',
                'key_point' => 'Kolokasi public place.',
            ],
            [
                'question_text' => 'What information is required when registering your place of employment?',
                'model_answer' => 'You need to provide the company\'s official address, tax ID, and business registration.',
                'key_point' => 'Kolokasi place of employment.',
            ],
            [
                'question_text' => 'How long does it take for online stores to deliver after you place an order?',
                'model_answer' => 'Standard delivery usually takes two to three business days after you place an order.',
                'key_point' => 'Kolokasi place an order.',
            ],
            [
                'question_text' => 'What is your favorite place to visit during weekend road trips?',
                'model_answer' => 'A scenic mountain viewpoint overlooking tea plantations is my favorite place to visit.',
                'key_point' => 'Frasa favorite place to visit.',
            ],
            [
                'question_text' => 'Why is finding a suitable meeting place important for business clients?',
                'model_answer' => 'A professional meeting place projects credibility and ensures quiet surroundings for discussion.',
                'key_point' => 'Kolokasi meeting place.',
            ],
            [
                'question_text' => 'What is the most sacred place of worship in your region?',
                'model_answer' => 'The historic grand mosque in the city center is the primary place of worship for locals.',
                'key_point' => 'Kolokasi place of worship.',
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

        // ── LESSON 4: Collocations: "Free Time", "Living Room" ────────────────────
        $lesson4 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 4,
            'title' => 'Collocations: "Free Time", "Living Room"',
            'difficulty' => 'Medium',
        ]);

        $questions4 = [
            [
                'question_text' => 'How much free time do you usually get on weekdays?',
                'model_answer' => 'I get about two hours of free time in the evening after completing my daily work.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'What furniture do you have in your living room?',
                'model_answer' => 'My living room features a comfortable sofa, a wooden coffee table, and a television unit.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'What is your favorite way to spend your free time at home?',
                'model_answer' => 'My favorite way to spend free time is reading books or listening to music in my armchair.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Is your living room the largest space in your house?',
                'model_answer' => 'Yes, the living room is designed with an open layout, making it the biggest room.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Do you prefer spending your free time outdoors or indoors?',
                'model_answer' => 'I prefer outdoor activities like cycling when I have extended free time on weekends.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'How do you decorate your living room during festive seasons?',
                'model_answer' => 'We add decorative lights, fresh flowers, and new cushion covers in the living room.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Do you feel you have enough free time during busy work weeks?',
                'model_answer' => 'Rarely; high workloads often encroach on my free time during peak business seasons.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'How does your living room get plenty of natural sunlight?',
                'model_answer' => 'Yes, large floor-to-ceiling glass windows illuminate the living room all day.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'How did you spend your free time when you were a student?',
                'model_answer' => 'As a student, I spent my free time playing video games and hanging out with flatmates.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Do family members gather in the living room every evening?',
                'model_answer' => 'Yes, after dinner we gather in the living room to watch news and chat about our day.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'What productive skills would you like to learn in your free time?',
                'model_answer' => 'I would love to use my free time to learn video editing and conversational French.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Is your living room connected to an open kitchen?',
                'model_answer' => 'Yes, it features an open-plan concept connecting the living room directly to the dining area.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Why is it important to spend free time away from digital screens?',
                'model_answer' => 'Unplugging during free time reduces eye strain, prevents mental fatigue, and improves sleep.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'What color are the walls painted in your living room?',
                'model_answer' => 'The walls in my living room are painted a warm off-white shade to make it feel bright.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'How do you manage your free time to avoid wasting hours scrolling online?',
                'model_answer' => 'I set strict app timers and schedule physical activities like swimming during my free time.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Do you keep indoor plants in your living room?',
                'model_answer' => 'Yes, I have potted monstera plants and ferns in the living room to purify the air.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'What is the most time-consuming activity during your free time?',
                'model_answer' => 'Binge-watching drama series often consumes most of my free time on weekends.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Do you host guests in your living room or dining area?',
                'model_answer' => 'We entertain guests in the living room because it has comfortable seating options.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Do you think modern workers get more free time than previous generations?',
                'model_answer' => 'No, digital connectivity means work emails often invade personal free time nowadays.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Is there a large carpet or rug covering your living room floor?',
                'model_answer' => 'Yes, a soft plush rug sits under the coffee table in the center of the living room.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'How do you balance resting and working out in your free time?',
                'model_answer' => 'I alternate days: doing gym workouts one day and relaxing with books the next in my free time.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Do you keep a television set in your living room?',
                'model_answer' => 'Yes, a smart TV is mounted on the main wall of our living room.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Why do people feel guilty when enjoying free time without working?',
                'model_answer' => 'Productivity culture makes people feel that spending free time idly is wasteful.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'How do you maintain cleanliness in a heavily used living room?',
                'model_answer' => 'We vacuum the living room rug twice a week and organize clutter every evening.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Is free time essential for creative thinking and problem solving?',
                'model_answer' => 'Absolutely, a relaxed mind during free time allows subconscious creative ideas to surface.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'What artwork or photos do you hang on your living room walls?',
                'model_answer' => 'We hang framed landscape paintings and family portrait photos in the living room.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'Do you prefer spending your free time alone or with family?',
                'model_answer' => 'I like a balance: quiet solo reading time and fun weekend outings with family.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'Is your living room air-conditioned during hot summer days?',
                'model_answer' => 'Yes, we turn on the living room air conditioner to keep the area cool during noon.',
                'key_point' => 'Kolokasi living room.',
            ],
            [
                'question_text' => 'What is the best hobby to pursue in your free time on a budget?',
                'model_answer' => 'Reading library books or outdoor running are fantastic low-cost free time hobbies.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'How would you redesign your living room if you had a large budget?',
                'model_answer' => 'I would install floor-to-ceiling windows, custom wooden shelving, and a modern fireplace in the living room.',
                'key_point' => 'Kolokasi living room.',
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

        // ── LESSON 5: Describe Your Favorite Room in Your Home ────────────────────
        $lesson5 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 5,
            'title' => 'Describe Your Favorite Room in Your Home',
            'difficulty' => 'Medium',
        ]);

        $questions5 = [
            [
                'question_text' => 'Which room in your home is your absolute favorite?',
                'model_answer' => 'My absolute favorite room in my home is my bedroom, which serves as my personal sanctuary.',
                'key_point' => 'Identifikasi kamar favorit.',
            ],
            [
                'question_text' => 'Where is this room located in your house or apartment?',
                'model_answer' => 'It is located on the second floor at the back of the house, away from street noise.',
                'key_point' => 'Lokasi spesifik dalam rumah.',
            ],
            [
                'question_text' => 'What main furniture items are placed inside this room?',
                'model_answer' => 'It contains a queen-sized bed, a wooden study desk, a wardrobe, and a cozy reading chair.',
                'key_point' => 'Perabotan utama.',
            ],
            [
                'question_text' => 'Why do you consider this room to be your favorite place?',
                'model_answer' => 'Because it offers complete privacy, peaceful quietness, and is decorated to my personal taste.',
                'key_point' => 'Alasan utama favorit.',
            ],
            [
                'question_text' => 'How much time do you spend in this room every day?',
                'model_answer' => 'I spend around eight to nine hours there daily, mostly for sleeping, reading, and working.',
                'key_point' => 'Alokasi waktu harian.',
            ],
            [
                'question_text' => 'What color scheme did you choose for the walls and decor of this room?',
                'model_answer' => 'I chose soft pastel blue and cream shades to create a tranquil and relaxing mood.',
                'key_point' => 'Skema warna interior.',
            ],
            [
                'question_text' => 'Does this room get good natural lighting and ventilation?',
                'model_answer' => 'Yes, a large east-facing window allows abundant morning sunlight and fresh breeze inside.',
                'key_point' => 'Pencahayaan & ventilasi.',
            ],
            [
                'question_text' => 'What activities do you enjoy doing inside this room besides sleeping?',
                'model_answer' => 'I enjoy reading novels, practicing guitar, journaling, and studying at my desk.',
                'key_point' => 'Aktivitas selain tidur.',
            ],
            [
                'question_text' => 'How have you personalized the decoration inside this favorite room?',
                'model_answer' => 'I hung string lights, framed travel photos, and placed small potted succulents on my desk.',
                'key_point' => 'Personalisasi dekorasi.',
            ],
            [
                'question_text' => 'Is this room shared with anyone else or do you have it to yourself?',
                'model_answer' => 'I have this room entirely to myself, which gives me full control over privacy and layout.',
                'key_point' => 'Status privasi (private/shared).',
            ],
            [
                'question_text' => 'What is the most comfortable piece of furniture inside this room?',
                'model_answer' => 'My ergonomic reading armchair by the window is undoubtedly the most comfortable spot.',
                'key_point' => 'Perabot paling nyaman.',
            ],
            [
                'question_text' => 'How do you keep this favorite room organized and clean?',
                'model_answer' => 'I make my bed every morning, dust surfaces weekly, and declutter my desk every evening.',
                'key_point' => 'Rutinitas kebersihan.',
            ],
            [
                'question_text' => 'Is there any electronic equipment or gadget kept in this room?',
                'model_answer' => 'I keep my laptop, a bluetooth speaker, and a desk reading lamp in the room.',
                'key_point' => 'Peralatan elektronik.',
            ],
            [
                'question_text' => 'How does being inside this room affect your mood after a stressful day?',
                'model_answer' => 'Stepping into this quiet, warm space instantly lowers my stress and relaxes my mind.',
                'key_point' => 'Efek emosional/psikologis.',
            ],
            [
                'question_text' => 'What changes or upgrades have you made to this room recently?',
                'model_answer' => 'I recently installed blackout curtains and added a soft plush rug beside my bed.',
                'key_point' => 'Renovasi/penambahan terbaru.',
            ],
            [
                'question_text' => 'Is this room warm during winter and cool during summer?',
                'model_answer' => 'Yes, proper wall insulation and an efficient air conditioner keep the temperature pleasant year-round.',
                'key_point' => 'Kontrol suhu ruangan.',
            ],
            [
                'question_text' => 'What view do you see when looking out the window of this room?',
                'model_answer' => 'The window looks out over our peaceful backyard garden and several tall frangipani trees.',
                'key_point' => 'Pemandangan jendela.',
            ],
            [
                'question_text' => 'Do you allow guests to enter your favorite room?',
                'model_answer' => 'Only close friends and family members are invited into my bedroom to respect my privacy.',
                'key_point' => 'Batasan tamu (privacy boundaries).',
            ],
            [
                'question_text' => 'What aroma or scent do you use to keep this room smelling nice?',
                'model_answer' => 'I use a lavender essential oil diffuser in the evenings to promote restful sleep.',
                'key_point' => 'Aromaterapi/wewangian.',
            ],
            [
                'question_text' => 'Why is having a personal favorite room important for mental wellbeing?',
                'model_answer' => 'Having a dedicated private space allows you to recharge without external noise or pressure.',
                'key_point' => 'Nilai mental wellbeing.',
            ],
            [
                'question_text' => 'How is the lighting arranged inside this favorite room?',
                'model_answer' => 'I have a warm ceiling light for general illumination and dimmable bedside lamps for reading.',
                'key_point' => 'Skema pencahayaan buatan.',
            ],
            [
                'question_text' => 'What flooring material is installed in this room?',
                'model_answer' => 'The floor is made of warm teak laminate wood, which feels smooth underfoot.',
                'key_point' => 'Material lantai (flooring).',
            ],
            [
                'question_text' => 'Do you work or study inside your favorite room or in a separate space?',
                'model_answer' => 'I have a dedicated desk inside my room for focused study, away from household noise.',
                'key_point' => 'Integrasi area kerja.',
            ],
            [
                'question_text' => 'Is there enough storage space inside this room?',
                'model_answer' => 'Yes, a built-in wardrobe and under-bed storage drawers keep my belongings neatly organized.',
                'key_point' => 'Solusi penyimpanan (storage).',
            ],
            [
                'question_text' => 'How does the size of this room suit your daily needs?',
                'model_answer' => 'It is moderately sized—not too large to clean, but spacious enough for all my perabotan.',
                'key_point' => 'Evaluasi ukuran kamar.',
            ],
            [
                'question_text' => 'What is the quietest time of day inside this favorite room?',
                'model_answer' => 'Late evening around 10 PM is extraordinarily quiet and perfect for relaxing.',
                'key_point' => 'Waktu paling tenang.',
            ],
            [
                'question_text' => 'Did you design the interior layout of this room yourself?',
                'model_answer' => 'Yes, I arranged the furniture layout myself to maximize floor space and natural light.',
                'key_point' => 'Desain tata letak mandiri.',
            ],
            [
                'question_text' => 'What item inside this room holds the highest sentimental value?',
                'model_answer' => 'A vintage wooden bookshelf inherited from my grandfather holding my favorite novels.',
                'key_point' => 'Nilai sentimental barang.',
            ],
            [
                'question_text' => 'Would you change anything about this room if you had a flexible budget?',
                'model_answer' => 'I would add an ensuite bathroom and install larger floor-to-ceiling balcony glass doors.',
                'key_point' => 'Rencana perbaikan masa depan.',
            ],
            [
                'question_text' => 'How would you feel if you had to move away and leave this favorite room?',
                'model_answer' => 'I would feel deeply nostalgic, as this room holds years of peaceful memories and comfort.',
                'key_point' => 'Ikatan emosional (nostalgia).',
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

        // ── LESSON 6: Room Collocations ────────────────────
        $lesson6 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 6,
            'title' => 'Room Collocations',
            'difficulty' => 'Medium',
        ]);

        $questions6 = [
            [
                'question_text' => 'How often do you clean your room every week?',
                'model_answer' => 'I dust and sweep my room twice a week, and do deep cleaning every Saturday.',
                'key_point' => 'Kolokasi clean your room.',
            ],
            [
                'question_text' => 'What furniture is essential in a formal dining room?',
                'model_answer' => 'A large dining table, comfortable chairs, and a sideboard cabinet are essential in a dining room.',
                'key_point' => 'Kolokasi dining room.',
            ],
            [
                'question_text' => 'Do you have a spare room in your house for visitors?',
                'model_answer' => 'Yes, we converted a small spare room into a comfortable guest room for visiting relatives.',
                'key_point' => 'Kolokasi spare room / guest room.',
            ],
            [
                'question_text' => 'What is the ideal room temperature for a comfortable sleep?',
                'model_answer' => 'Most experts agree that a cool room temperature around 20 degrees Celsius is ideal for sleep.',
                'key_point' => 'Kolokasi room temperature.',
            ],
            [
                'question_text' => 'Would you prefer a spacious room with simple decor or a small cozy one?',
                'model_answer' => 'I prefer a spacious room because it allows better air circulation and movement.',
                'key_point' => 'Kolokasi spacious room.',
            ],
            [
                'question_text' => 'How do family members use the dining room in your house?',
                'model_answer' => 'We gather in the dining room every evening to share home-cooked dinners and converse.',
                'key_point' => 'Kolokasi dining room.',
            ],
            [
                'question_text' => 'Is your guest room fully furnished with a bed and wardrobe?',
                'model_answer' => 'Yes, our guest room includes a queen bed, clean linens, and a small wardrobe.',
                'key_point' => 'Kolokasi guest room.',
            ],
            [
                'question_text' => 'What do you use your spare room for when no guests are staying?',
                'model_answer' => 'When empty, we use the spare room as a quiet home office and ironing area.',
                'key_point' => 'Kolokasi spare room.',
            ],
            [
                'question_text' => 'How do you adjust the room temperature during hot summer days?',
                'model_answer' => 'We set the air conditioner remote to lower the room temperature efficiently.',
                'key_point' => 'Kolokasi room temperature.',
            ],
            [
                'question_text' => 'Why is living in a spacious room beneficial for mental clarity?',
                'model_answer' => 'A spacious room with minimal clutter reduces visual stress and promotes focus.',
                'key_point' => 'Kolokasi spacious room.',
            ],
            [
                'question_text' => 'How long does it take you to clean a messy room thoroughly?',
                'model_answer' => 'Tidying up clothes, vacuuming, and wiping surfaces usually takes about forty-five minutes.',
                'key_point' => 'Frasa clean a room.',
            ],
            [
                'question_text' => 'Do you eat daily meals in the dining room or in front of the TV?',
                'model_answer' => 'We make it a point to eat dinner together in the dining room without electronic distractions.',
                'key_point' => 'Kolokasi dining room.',
            ],
            [
                'question_text' => 'How often do you prepare the guest room for incoming relatives?',
                'model_answer' => 'I change bedsheets and air out the guest room a day before relatives arrive.',
                'key_point' => 'Kolokasi guest room.',
            ],
            [
                'question_text' => 'Would you turn a spare room into a home gym?',
                'model_answer' => 'Yes, turning a spare room into a workout space with yoga mats and weights is a great idea.',
                'key_point' => 'Kolokasi spare room.',
            ],
            [
                'question_text' => 'What heating method keeps the room temperature stable during winter?',
                'model_answer' => 'Central heating radiators keep the room temperature steady throughout chilly nights.',
                'key_point' => 'Kolokasi room temperature.',
            ],
            [
                'question_text' => 'Is your bedroom a spacious room or a compact studio space?',
                'model_answer' => 'It is a compact room, but smart vertical storage makes it feel surprisingly spacious.',
                'key_point' => 'Kolokasi spacious room.',
            ],
            [
                'question_text' => 'What is the most effective way to encourage children to clean their room?',
                'model_answer' => 'Making tidying a game and offering small rewards encourages kids to clean their room.',
                'key_point' => 'Frasa clean their room.',
            ],
            [
                'question_text' => 'Do you keep a decorative chandelier in your dining room?',
                'model_answer' => 'Yes, a warm pendant light hangs directly above the table in our dining room.',
                'key_point' => 'Kolokasi dining room.',
            ],
            [
                'question_text' => 'How do you make a guest room feel welcoming for visitors?',
                'model_answer' => 'By providing fresh towels, extra pillows, bottled water, and Wi-Fi password notes.',
                'key_point' => 'Kolokasi guest room.',
            ],
            [
                'question_text' => 'Can a spare room be used to generate rental income?',
                'model_answer' => 'Yes, home owners often list a spare room on homestay platforms for extra income.',
                'key_point' => 'Kolokasi spare room.',
            ],
            [
                'question_text' => 'Does high room temperature make it hard to focus on work?',
                'model_answer' => 'Yes, an excessively warm room temperature causes drowsiness and reduces cognitive focus.',
                'key_point' => 'Kolokasi room temperature.',
            ],
            [
                'question_text' => 'What perabotan makes a living room feel like a spacious room?',
                'model_answer' => 'Using low-profile furniture and large wall mirrors makes any space look like a spacious room.',
                'key_point' => 'Kolokasi spacious room.',
            ],
            [
                'question_text' => 'Do you prefer to clean your room in the morning or evening?',
                'model_answer' => 'I prefer cleaning my room every morning so I return to a tidy space after work.',
                'key_point' => 'Frasa clean my room.',
            ],
            [
                'question_text' => 'Is the dining room connected directly to your kitchen?',
                'model_answer' => 'Yes, an open archway connects the kitchen to the dining room for easy serving.',
                'key_point' => 'Kolokasi dining room.',
            ],
            [
                'question_text' => 'Is it necessary to keep a TV inside a guest room?',
                'model_answer' => 'It\'s not mandatory, but placing a small TV in the guest room gives visitors entertainment privacy.',
                'key_point' => 'Kolokasi guest room.',
            ],
            [
                'question_text' => 'How would you convert a spare room into a walk-in closet?',
                'model_answer' => 'By installing open clothing racks, full-length mirrors, and custom shoe shelves along walls.',
                'key_point' => 'Kolokasi spare room.',
            ],
            [
                'question_text' => 'What device monitors room temperature accurately?',
                'model_answer' => 'A digital wall thermometer or smart thermostat reads room temperature precisely.',
                'key_point' => 'Kolokasi room temperature.',
            ],
            [
                'question_text' => 'Why do high ceilings make a room feel like a spacious room?',
                'model_answer' => 'High ceilings expand vertical visual space and improve air circulation dramatically.',
                'key_point' => 'Kolokasi spacious room.',
            ],
            [
                'question_text' => 'What tools do you use when you clean your room on weekends?',
                'model_answer' => 'I use a cordless vacuum cleaner, microfiber cloths, and disinfectant spray to clean my room.',
                'key_point' => 'Frasa clean my room.',
            ],
            [
                'question_text' => 'Is the dining room used for studying when no meals are served?',
                'model_answer' => 'Yes, the large dining room table is great for spreading out textbooks and working on projects.',
                'key_point' => 'Kolokasi dining room.',
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

        $this->command->info('Places Unit (Unit 9) seeded successfully!');
        $this->command->info('Lesson 1: ' . count($questions1) . ' questions');
        $this->command->info('Lesson 2: ' . count($questions2) . ' questions');
        $this->command->info('Lesson 3: ' . count($questions3) . ' questions');
        $this->command->info('Lesson 4: ' . count($questions4) . ' questions');
        $this->command->info('Lesson 5: ' . count($questions5) . ' questions');
        $this->command->info('Lesson 6: ' . count($questions6) . ' questions');
    }
}