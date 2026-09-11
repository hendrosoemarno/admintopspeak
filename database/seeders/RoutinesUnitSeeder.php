<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class RoutinesUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Unit 7: Routines (Part 1) ──────────────────────
        $unit = Unit::create([
            'unit_number' => 7,
            'title' => 'Routines',
            'part' => 1,
            'outcome' => 'Menggunakan frasa rutinitas harian (take care of, look after), pola tidur (sleep in, wake up early), serta gabungan kata waktu (make time, save time, waste time).',
        ]);

        // ── LESSON 1: Collocations: "Take Care Of", "Sleep In" ─────────
        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations: "Take Care Of", "Sleep In"',
            'difficulty' => 'Medium',
        ]);

        $questions1 = [
            [
                'question_text' => 'Do you usually sleep in on Sunday mornings?',
                'model_answer' => 'Yes, I love to sleep in on Sundays until 9 AM to recover from a tiring work week.',
                'key_point' => 'Kolokasi sleep in.',
            ],
            [
                'question_text' => 'Who takes care of the house chores in your home?',
                'model_answer' => 'My family members split the tasks, so everyone takes care of specific house chores.',
                'key_point' => 'Kolokasi take care of.',
            ],
            [
                'question_text' => 'Do you take care of any pets at home?',
                'model_answer' => 'Yes, I take care of a cat, which includes feeding him daily and taking him to the vet.',
                'key_point' => 'Kolokasi take care of pets.',
            ],
            [
                'question_text' => 'Is it hard to sleep in when you have noisy neighbors?',
                'model_answer' => 'It is almost impossible to sleep in when there is loud construction noise outside early morning.',
                'key_point' => 'Kolokasi sleep in.',
            ],
            [
                'question_text' => 'How do you take care of your physical health during busy weekdays?',
                'model_answer' => 'I take care of my health by drinking plenty of water, eating balanced meals, and taking short walks.',
                'key_point' => 'Kolokasi take care of health.',
            ],
            [
                'question_text' => 'Do you prefer to sleep in or wake up early on weekends?',
                'model_answer' => 'I prefer to wake up early even on weekends so I have more time for my personal hobbies.',
                'key_point' => 'Perbandingan sleep in vs wake up early.',
            ],
            [
                'question_text' => 'Who takes care of elderly family members in your community?',
                'model_answer' => 'Usually, family members take care of seniors at home with assistance from visiting nurses.',
                'key_point' => 'Kolokasi take care of elderly.',
            ],
            [
                'question_text' => 'Do you ever sleep in on workdays by accident?',
                'model_answer' => 'Rarely, but if my alarm does not go off, sleeping in causes a panicked morning rush.',
                'key_point' => 'Context sleep in by accident.',
            ],
            [
                'question_text' => 'How do you take care of stress after a long working day?',
                'model_answer' => 'I take care of stress by taking a warm shower, listening to calming music, and reading.',
                'key_point' => 'Kolokasi take care of stress.',
            ],
            [
                'question_text' => 'Why is it satisfying to sleep in after finishing a major project?',
                'model_answer' => 'Because your mind and body feel fully relieved of pressure, allowing for deep rest.',
                'key_point' => 'Reason for sleep in.',
            ],
            [
                'question_text' => 'Do you take care of indoor plants in your apartment?',
                'model_answer' => 'Yes, taking care of my houseplants by watering them regularly brings a soothing feeling.',
                'key_point' => 'Kolokasi take care of plants.',
            ],
            [
                'question_text' => 'Until what time do teenagers usually sleep in during holidays?',
                'model_answer' => 'Teenagers often sleep in until mid-morning or noon after staying up late playing games.',
                'key_point' => 'Pattern of sleep in.',
            ],
            [
                'question_text' => 'Who takes care of financial budgeting in your household?',
                'model_answer' => 'My spouse and I take care of finances together by tracking monthly income and expenses.',
                'key_point' => 'Kolokasi take care of finances.',
            ],
            [
                'question_text' => 'Do you feel guilty if you sleep in past 10 AM?',
                'model_answer' => 'Occasionally I feel like I wasted the morning, but resting is important occasionally.',
                'key_point' => 'Emotion associated with sleep in.',
            ],
            [
                'question_text' => 'How do you take care of customer complaints at work?',
                'model_answer' => 'I take care of complaints by listening patiently and offering immediate practical solutions.',
                'key_point' => 'Work context take care of.',
            ],
            [
                'question_text' => 'Is it difficult to sleep in when you have young children?',
                'model_answer' => 'Extremely difficult, as young kids wake up early wanting breakfast and playtime.',
                'key_point' => 'Obstacle to sleep in.',
            ],
            [
                'question_text' => 'How do you take care of your skin during cold winter months?',
                'model_answer' => 'I take care of my skin by applying moisturizing cream daily to prevent dryness.',
                'key_point' => 'Kolokasi take care of skin.',
            ],
            [
                'question_text' => 'Do you allow yourself to sleep in on Saturdays?',
                'model_answer' => 'Yes, Saturday is my official catch-up day for rest, so I sleep in without setting an alarm.',
                'key_point' => 'Habit of sleep in.',
            ],
            [
                'question_text' => 'Who takes care of administrative paperwork in your company?',
                'model_answer' => 'Our administrative department takes care of all official paperwork and client contracts.',
                'key_point' => 'Workplace take care of.',
            ],
            [
                'question_text' => 'Why do active people find it hard to sleep in?',
                'model_answer' => 'Because their natural circadian rhythm wakes them up automatically early in the morning.',
                'key_point' => 'Biological clock & sleep in.',
            ],
            [
                'question_text' => 'How do you take care of your eyes if you stare at screens all day?',
                'model_answer' => 'I take care of my eyes by following the 20-20-20 rule and using blue light filter glasses.',
                'key_point' => 'Kolokasi take care of eyes.',
            ],
            [
                'question_text' => 'Do weather conditions affect your desire to sleep in?',
                'model_answer' => 'Rainy, chilly mornings definitely make me want to stay under blankets and sleep in longer.',
                'key_point' => 'Environmental factor on sleep in.',
            ],
            [
                'question_text' => 'Who takes care of dinner preparation on busy weekdays?',
                'model_answer' => 'We take care of dinner by meal prepping basic ingredients over the weekend.',
                'key_point' => 'Routine take care of meal.',
            ],
            [
                'question_text' => 'Does sleeping in late ruin your evening sleep schedule?',
                'model_answer' => 'Yes, if I sleep in past noon, I find it much harder to fall asleep at night.',
                'key_point' => 'Consequence of sleep in.',
            ],
            [
                'question_text' => 'How do you take care of urgent emails during holidays?',
                'model_answer' => 'I set an automated out-of-office reply and only take care of emergency inquiries.',
                'key_point' => 'Work-life boundary take care of.',
            ],
            [
                'question_text' => 'Why is it important to take care of mental wellbeing?',
                'model_answer' => 'Taking care of mental health prevents burnout and maintains long-term life satisfaction.',
                'key_point' => 'Importance of take care of.',
            ],
            [
                'question_text' => 'Do you sleep in more during winter than in summer?',
                'model_answer' => 'Yes, dark winter mornings make it far easier to sleep in compared to bright summer sun.',
                'key_point' => 'Seasonal variation of sleep in.',
            ],
            [
                'question_text' => 'Who takes care of maintenance issues in a rented apartment?',
                'model_answer' => 'The building landlord is responsible for taking care of structural repairs and plumbing.',
                'key_point' => 'Rental context take care of.',
            ],
            [
                'question_text' => 'How do you take care of your personal growth outside work?',
                'model_answer' => 'I take care of my personal growth by reading books and practicing foreign languages.',
                'key_point' => 'Personal development take care of.',
            ],
            [
                'question_text' => 'What is the first thing you do after you sleep in and wake up?',
                'model_answer' => 'After sleeping in, I stretch in bed and make a fresh cup of coffee.',
                'key_point' => 'Sequence after sleep in.',
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

        // ── LESSON 2: How Do You Usually Spend Your Weekends? ──────────────
        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'How Do You Usually Spend Your Weekends?',
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'question_text' => 'How do you usually spend your weekends?',
                'model_answer' => 'I usually spend my weekends relaxing at home, running errands, and catching up with friends.',
                'key_point' => 'Deskripsi umum rutinitas akhir pekan.',
            ],
            [
                'question_text' => 'Do you prefer spending your weekends indoors or outdoors?',
                'model_answer' => 'I prefer spending my weekends outdoors, especially going for hikes or visiting public parks.',
                'key_point' => 'Perbandingan indoors vs outdoors.',
            ],
            [
                'question_text' => 'What is your absolute favorite way to spend a Saturday afternoon?',
                'model_answer' => 'My favorite way to spend Saturday afternoon is sitting in a cozy coffee shop reading a novel.',
                'key_point' => 'Aktivitas Sabtu sore favorit.',
            ],
            [
                'question_text' => 'How do you spend your Sunday evenings to prepare for the week?',
                'model_answer' => 'On Sunday evenings, I organize my work calendar, prepare outfits, and get an early nights sleep.',
                'key_point' => 'Persiapan Minggu malam (prep routine).',
            ],
            [
                'question_text' => 'Do you spend your weekends differently in summer compared to winter?',
                'model_answer' => 'In summer I spend weekends doing outdoor sports, while in winter I stay indoors watching movies.',
                'key_point' => 'Perbedaan musim pada weekend.',
            ],
            [
                'question_text' => 'How much time do you spend doing household chores on weekends?',
                'model_answer' => 'I dedicate about two hours on Saturday morning to cleaning, laundry, and grocery shopping.',
                'key_point' => 'Alokasi waktu tugas rumah.',
            ],
            [
                'question_text' => 'Do you spend your weekends studying or working continuously?',
                'model_answer' => 'I try to keep my weekends strictly free from work to maintain a healthy work-life balance.',
                'key_point' => 'Batasan work-life balance.',
            ],
            [
                'question_text' => 'Who do you usually spend your weekends with?',
                'model_answer' => 'I spend Saturdays with my close friends and keep Sundays reserved for family time.',
                'key_point' => 'Sosialisasi akhir pekan.',
            ],
            [
                'question_text' => 'How do you spend your weekends when the weather is rainy?',
                'model_answer' => 'On rainy weekends, I bake pastries, listen to vinyl records, and watch TV series.',
                'key_point' => 'Rutinitas saat hujan.',
            ],
            [
                'question_text' => 'Do you spend weekends pursuing creative hobbies?',
                'model_answer' => 'Yes, I spend a few hours on weekends practicing digital illustration and writing.',
                'key_point' => 'Hobi kreatif akhir pekan.',
            ],
            [
                'question_text' => 'What is the most relaxing way to spend a long three-day weekend?',
                'model_answer' => 'Taking a short road trip to a nearby coastal town is the best way to spend a long weekend.',
                'key_point' => 'Konsep long weekend getaway.',
            ],
            [
                'question_text' => 'Do you spend a lot of money during your weekends?',
                'model_answer' => 'I try to budget carefully, focusing on low-cost activities like picnics or home cooking.',
                'key_point' => 'Pengelolaan pengeluaran akhir pekan.',
            ],
            [
                'question_text' => 'How do you spend your weekends when you feel completely exhausted?',
                'model_answer' => 'I spend the weekend doing a digital detox, taking long baths, and sleeping extra hours.',
                'key_point' => 'Pemulihan energi recovery weekend.',
            ],
            [
                'question_text' => 'Do you spend time exercising during the weekend?',
                'model_answer' => 'Yes, I go for a long bicycle ride on Saturday morning when I have plenty of free time.',
                'key_point' => 'Aktivitas olahraga akhir pekan.',
            ],
            [
                'question_text' => 'How did you spend your weekends when you were a student?',
                'model_answer' => 'As a student, I spent weekends studying at the library and hanging out at cheap eateries.',
                'key_point' => 'Retrospeksi past weekend routine.',
            ],
            [
                'question_text' => 'Do you spend time planning social gatherings at home?',
                'model_answer' => 'Occasionally I host board game nights or dinner parties for my close friends.',
                'key_point' => 'Mengadakan acara rumah hosting.',
            ],
            [
                'question_text' => 'How do you spend your weekends when you have no social plans?',
                'model_answer' => 'I relish solitude by watching documentaries, gardening, and ordering comfort food.',
                'key_point' => 'Menikmati waktu sendirian solitude.',
            ],
            [
                'question_text' => 'Do you spend your weekends exploring new places in your city?',
                'model_answer' => 'Yes, I love discovering new local art galleries, cafes, and hidden street spots.',
                'key_point' => 'Eksplorasi kota lokal.',
            ],
            [
                'question_text' => 'How do you balance rest and productivity during your weekend?',
                'model_answer' => 'I finish necessary errands on Saturday morning so the rest of the weekend is pure leisure.',
                'key_point' => 'Strategi balance rest tasks.',
            ],
            [
                'question_text' => 'Do you spend weekends volunteering for local charities?',
                'model_answer' => 'I volunteer at an animal shelter once a month on Sunday mornings.',
                'key_point' => 'Kegiatan sosial akhir pekan.',
            ],
            [
                'question_text' => 'Why do some people feel anxious about how they spend their weekends?',
                'model_answer' => 'Because social media creates pressure to always have exciting photogenic weekend plans.',
                'key_point' => 'Fenomena psikologis FOMO.',
            ],
            [
                'question_text' => 'Do you spend weekends cooking elaborate meals from scratch?',
                'model_answer' => 'Yes, having extra time allows me to experiment with complex recipes I cannot cook on weekdays.',
                'key_point' => 'Hobi memasak akhir pekan.',
            ],
            [
                'question_text' => 'How do parents usually spend their weekends with young children?',
                'model_answer' => 'Parents spend weekends taking kids to playgrounds, sports matches, and family events.',
                'key_point' => 'Peran orang tua akhir pekan.',
            ],
            [
                'question_text' => 'Do you spend your weekends catching up on missed television shows?',
                'model_answer' => 'I occasionally binge-watch a new season of a series on Saturday night.',
                'key_point' => 'Hibur binge-watching.',
            ],
            [
                'question_text' => 'How do you spend your weekends if you have to work on Saturday?',
                'model_answer' => 'I make sure to maximize my Sunday by doing something deeply fulfilling and restful.',
                'key_point' => 'Menghadapi weekend work.',
            ],
            [
                'question_text' => 'Do you spend weekends doing outdoor gardening?',
                'model_answer' => 'Yes, tending to my vegetable patch on weekend mornings is very therapeutic.',
                'key_point' => 'Hobi berkebun.',
            ],
            [
                'question_text' => 'What is the worst way to spend a weekend in your opinion?',
                'model_answer' => 'Stuck in heavy traffic or worrying constantly about upcoming Monday deadlines.',
                'key_point' => 'Opini weekend terburuk.',
            ],
            [
                'question_text' => 'How do you spend your weekends during major festival seasons?',
                'model_answer' => 'I spend them visiting relatives, exchanging gifts, and enjoying traditional feasts.',
                'key_point' => 'Akhir pekan musim festival.',
            ],
            [
                'question_text' => 'Do you spend weekends practicing musical instruments?',
                'model_answer' => 'Yes, I dedicate an hour each weekend day to practicing acoustic guitar chords.',
                'key_point' => 'Latih kemampuan seni.',
            ],
            [
                'question_text' => 'How do you hope to spend your weekends in the future?',
                'model_answer' => 'In the future, I hope to spend weekends living in a countryside home surrounded by nature.',
                'key_point' => 'Visi akhir pekan masa depan.',
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

        // ── LESSON 3: Collocations with Weekend and Time ────────────────────
        $lesson3 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 3,
            'title' => 'Collocations with Weekend and Time',
            'difficulty' => 'Medium',
        ]);

        $questions3 = [
            [
                'question_text' => 'How do you usually make time for your hobbies during busy weeks?',
                'model_answer' => 'I make time by waking up thirty minutes earlier every morning to read.',
                'key_point' => 'Kolokasi make time.',
            ],
            [
                'question_text' => 'Do you have any exciting weekend plans for this week?',
                'model_answer' => 'Yes, my weekend plans include attending a live concert with my friends on Saturday.',
                'key_point' => 'Kolokasi weekend plans.',
            ],
            [
                'question_text' => 'What is the best way to save time when doing daily errands?',
                'model_answer' => 'Ordering groceries online and organizing a daily checklist saves a lot of time.',
                'key_point' => 'Kolokasi save time.',
            ],
            [
                'question_text' => 'Do you prefer spending time alone or with groups on the weekend?',
                'model_answer' => 'I prefer spending time alone on Saturday to recharge, and with groups on Sunday.',
                'key_point' => 'Kolokasi spend time on the weekend.',
            ],
            [
                'question_text' => 'How do you avoid wasting time on your smartphone?',
                'model_answer' => 'I set app time limits to stop myself from wasting time scrolling endlessly.',
                'key_point' => 'Kolokasi waste time.',
            ],
            [
                'question_text' => 'Have you ever planned a spontaneous weekend getaway?',
                'model_answer' => 'Yes, last month we booked a hotel on Friday night for a quick weekend getaway.',
                'key_point' => 'Kolokasi weekend getaway.',
            ],
            [
                'question_text' => 'Is it hard to find free time during peak work seasons?',
                'model_answer' => 'Yes, during high-workload periods, finding free time for relaxation becomes very difficult.',
                'key_point' => 'Kolokasi free time.',
            ],
            [
                'question_text' => 'What do you like to do on the weekend when the weather is nice?',
                'model_answer' => 'On the weekend, I love having outdoor picnics in the park when it is sunny.',
                'key_point' => 'Preposisi on the weekend.',
            ],
            [
                'question_text' => 'How do you manage your time effectively between work and family?',
                'model_answer' => 'I manage my time by setting clear boundaries and turning off work notifications at home.',
                'key_point' => 'Kolokasi manage your time.',
            ],
            [
                'question_text' => 'Do you spend time learning new skills online?',
                'model_answer' => 'Yes, I spend time every week taking short courses on digital marketing.',
                'key_point' => 'Kolokasi spend time.',
            ],
            [
                'question_text' => 'What is your favorite destination for a peaceful weekend getaway?',
                'model_answer' => 'A quiet mountain cabin two hours away is my ideal spot for a weekend getaway.',
                'key_point' => 'Kolokasi weekend getaway.',
            ],
            [
                'question_text' => 'Do you ever feel like you are running out of time during exams?',
                'model_answer' => 'Yes, if I spend too long on difficult questions, I worry about running out of time.',
                'key_point' => 'Kolokasi run out of time.',
            ],
            [
                'question_text' => 'What are your typical weekend activities with family?',
                'model_answer' => 'Our typical weekend activities include cooking lunch together and going for walks.',
                'key_point' => 'Kolokasi weekend activities.',
            ],
            [
                'question_text' => 'How do you pass the time when waiting for a delayed flight?',
                'model_answer' => 'I pass the time by reading e-books or listening to downloaded podcasts.',
                'key_point' => 'Kolokasi pass the time.',
            ],
            [
                'question_text' => 'Do you think modern technology saves time or wastes time?',
                'model_answer' => 'It is a double-edged sword; it saves time on tasks but wastes time through distractions.',
                'key_point' => 'Perbandingan save time vs waste time.',
            ],
            [
                'question_text' => 'Why is it important to have quality time with loved ones?',
                'model_answer' => 'Spending quality time builds strong emotional bonds and creates lasting memories.',
                'key_point' => 'Kolokasi quality time.',
            ],
            [
                'question_text' => 'Do you usually work overtime on weekdays?',
                'model_answer' => 'I try to avoid working overtime so I have evening time to unwind.',
                'key_point' => 'Istilah work overtime.',
            ],
            [
                'question_text' => 'What is the most popular weekend destination in your country?',
                'model_answer' => 'Beach resorts and mountain towns are the most popular weekend destinations.',
                'key_point' => 'Kolokasi weekend destination.',
            ],
            [
                'question_text' => 'How do you kill time when you arrive too early for an appointment?',
                'model_answer' => 'I kill time by browsing news on my phone or enjoying a cup of tea.',
                'key_point' => 'Kolokasi kill time.',
            ],
            [
                'question_text' => 'Do you make a strict weekend schedule or go with the flow?',
                'model_answer' => 'I prefer to go with the flow on weekends rather than following a rigid schedule.',
                'key_point' => 'Konsep weekend schedule.',
            ],
            [
                'question_text' => 'How much leisure time do you get every day?',
                'model_answer' => 'I get about two hours of leisure time in the evening after finishing work.',
                'key_point' => 'Kolokasi leisure time.',
            ],
            [
                'question_text' => 'Do you spend time doing outdoor sports on the weekend?',
                'model_answer' => 'Yes, playing soccer on the weekend keeps me active and refreshed.',
                'key_point' => 'Kolokasi spend time on the weekend.',
            ],
            [
                'question_text' => 'What is a major time-consuming task in your routine?',
                'model_answer' => 'Commuting in heavy traffic is definitely the most time-consuming part of my day.',
                'key_point' => 'Istilah time-consuming.',
            ],
            [
                'question_text' => 'How do you feel when your weekend plans get canceled unexpectedly?',
                'model_answer' => 'Disappointed initially, but I quickly adapt by enjoying a quiet rest day at home.',
                'key_point' => 'Context weekend plans.',
            ],
            [
                'question_text' => 'Is it essential to take time off work regularly?',
                'model_answer' => 'Taking time off is crucial to prevent chronic stress and physical exhaustion.',
                'key_point' => 'Kolokasi take time off.',
            ],
            [
                'question_text' => 'What do you do to value your time better?',
                'model_answer' => 'I prioritize high-value tasks and learn to say no to unnecessary commitments.',
                'key_point' => 'Context value your time.',
            ],
            [
                'question_text' => 'Do you enjoy short weekend breaks more than long annual vacations?',
                'model_answer' => 'Long vacations allow deeper rest, but frequent weekend breaks keep motivation steady.',
                'key_point' => 'Perbandingan weekend breaks vs long vacations.',
            ],
            [
                'question_text' => 'How do you spend time with friends who live far away?',
                'model_answer' => 'We schedule regular video calls and plan annual weekend trips together.',
                'key_point' => 'Kolokasi spend time.',
            ],
            [
                'question_text' => 'What is your idea of a perfect weekend from start to finish?',
                'model_answer' => 'Sleeping in, enjoying a slow brunch, hiking in nature, and sharing a meal with friends.',
                'key_point' => 'Konsep perfect weekend.',
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

        $this->command->info('Routines Unit (Unit 7) seeded successfully!');
        $this->command->info('Lesson 1: ' . count($questions1) . ' questions');
        $this->command->info('Lesson 2: ' . count($questions2) . ' questions');
        $this->command->info('Lesson 3: ' . count($questions3) . ' questions');
    }
}