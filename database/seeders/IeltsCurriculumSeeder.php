<?php

namespace Database\Seeders;

use App\Enums\LessonDifficulty;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use App\Models\UserLessonProgress;
use Illuminate\Database\Seeder;

/**
 * Seeder kurikulum IELTS Speaking (Units/Lessons/Questions).
 *
 * Struktur mengikuti IELTS.md: Part 1 (Unit 1-7), Part 2 (Unit 8-11),
 * Part 3 (Unit 12-17). Setiap lesson memiliki 5 soal (latihan sesi 5 acak),
 * passing grade 4 dari 5, dan grading berbasis key point (kolokasi).
 *
 * Seeder sengaja mengosongkan tabel kurikulum terlebih dahulu agar tidak ada
 * leftover unit/lesson/soal lama (konten diganti total).
 */
class IeltsCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        UserLessonProgress::query()->delete();
        Question::query()->delete();
        Lesson::query()->delete();
        Unit::query()->delete();

        $this->seedUnits();
    }

    private function seedUnits(): void
    {
        foreach ($this->data() as $unitData) {
            $unit = Unit::updateOrCreate(
                ['unit_number' => $unitData['unit_number']],
                [
                    'title' => $unitData['title'],
                    'part' => $unitData['part'],
                    'outcome' => $unitData['outcome'] ?? null,
                ]
            );

            foreach ($unitData['lessons'] as $lessonData) {
                $lesson = Lesson::updateOrCreate(
                    ['unit_id' => $unit->id, 'lesson_number' => $lessonData['lesson_number']],
                    [
                        'title' => $lessonData['title'],
                        'difficulty' => $lessonData['difficulty'],
                    ]
                );

                foreach ($lessonData['questions'] as $questionData) {
                    Question::updateOrCreate(
                        ['lesson_id' => $lesson->id, 'question_text' => $questionData['question_text']],
                        $questionData,
                    );
                }
            }
        }
    }

    private function data(): array
    {
        return [
            // ===================== PART 1 (Unit 1-7) =====================
            [
                'unit_number' => 1,
                'title' => 'Work & Studies',
                'part' => 1,
                'outcome' => 'Use collocations to describe what you do',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'What do you do?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What do you do for a living?', 'Do you enjoy your work? Why?', 'What does a typical day at work look like for you?', 'Is your job more challenging or more routine?', 'Would you say your job is a good fit for your personality?'],
                            'I work as a graphic designer, and I get a lot of job satisfaction from seeing my ideas come to life. My typical day is quite varied; some days are devoted to routine tasks, but other days I face challenging projects that push my creativity.',
                            ['work as', 'for a living', 'a typical day', 'job satisfaction', 'career path'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Collocations with the word job',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What was your first job, and what did you learn from it?', 'Do you prefer a well-paid job or a meaningful job?', 'What are the advantages of a part-time job for students?', 'How can people find a good job in your country?', 'Has the job market changed a lot in the last decade?'],
                            'My first job was a part-time job in a café, which taught me to work under pressure and talk to strangers. Nowadays the job market is very competitive, so many graduates look for on-the-job training before taking a full-time job.',
                            ['a well-paid job', 'part-time job', 'job market', 'full-time job', 'on-the-job training'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'What do you study?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What are you studying at the moment?', 'Why did you choose that subject?', 'Do you prefer studying alone or in a group?', 'Which part of your studies do you find the hardest?', 'What do you plan to do after you finish studying?'],
                            'I am currently majoring in economics because I enjoy understanding how markets work. I mostly prefer studying alone, but the hardest part for me is statistics, so I join a study group for that subject. After I graduate, I hope to take a professional course in finance.',
                            ['major in', 'take a course', 'studying alone', 'hardest part', 'graduate from'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations with the word major',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Why do some students change their major at university?', 'Is it better to choose a major you love or one with good job prospects?', 'Do many people in your country study a science major?', 'Should governments allow students to choose any major freely?', 'What advice would you give someone who is choosing a major?'],
                            'Choosing a major is a major decision in every student\'s life. In my country, many students pick a science major for its job prospects, but some later decide to change their major once they discover their real interests. My advice is to combine passion with practical career opportunities.',
                            ['choose a major', 'change their major', 'a science major', 'a major decision', 'job prospects'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'Collocations: interesting to me, the hardest part',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Is school generally interesting to you, or do you find it boring?', 'What subjects are really interesting to you and why?', 'What is the hardest part of learning English?', 'What is the hardest part about staying organized as a student?', 'How can teachers make their lessons interesting to students?'],
                            'School was always interesting to me because I loved science and experiments. The hardest part for me was grammar rules, and it took a lot of hard work to keep up with the class. I think lessons become interesting to students when teachers connect the topic to daily life.',
                            ['interesting to me', 'the hardest part', 'hard work', 'keep up with', 'find something interesting'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Collocations: hard work, interested in',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Would you describe yourself as a hard-working student or employee?', 'What are you really interested in outside of work or study?', 'When did you first become interested in your current hobby?', 'Is success mostly about hard work or talent?', 'How do you stay interested in a project that lasts for months?'],
                            'I am definitely a hard-working person when a task catches my attention. I first got interested in photography during high school, and that hobby still excites me because there is always something new to learn. I believe talent gives you a starting line, but hard work is what makes you improve.',
                            ['hard work', 'be interested in', 'get interested in', 'a hard-working person', 'a reward for hard work'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 2,
                'title' => 'Hometown',
                'part' => 1,
                'outcome' => 'Learn to describe the place where you\'re from',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: in the mountains, once a year',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What is there to do in your hometown?', 'How often do you go back to your hometown?', 'Describe a popular attraction near your hometown.', 'Is your hometown a good place to raise a family?', 'Would you like to live there again in the future?'],
                            'My hometown is a small town in the mountains, so the air is fresh and the views are beautiful. I only go back there once a year, usually during the holidays, but it will always be the place where I grew up and where my grandparents still live.',
                            ['in the mountains', 'once a year', 'a small town', 'a tourist attraction', 'grow up'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Where is your hometown?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Where exactly is your hometown located?', 'Can you describe the region where your hometown is found?', 'What is your hometown most famous for?', 'Has your hometown changed since you left? How?', 'What do you miss most about your hometown?'],
                            'My hometown is located in the eastern part of Java, not far from the coast. It is a quiet fishing village famous for its fresh seafood and beautiful sunsets. People often speak of it with a kind of hometown pride, and although it has grown busier, the old market still feels the same.',
                            ['located in', 'fishing village', 'famous for', 'hometown pride', 'the eastern part'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with city, town, and village',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do you prefer living in a city, a town, or a village?', 'What is the main difference between a city and a village in your country?', 'Why are young people moving from villages to cities?', 'Is there too much traffic in your city?', 'Would you recommend a visitor to stay in the city center?'],
                            'I grew up in a small village, but now I live in a crowded city. I enjoy city life because of the opportunities, even though I sometimes miss the quiet evenings and the annual village fair. For visitors, staying in the city center is practical because everything is within walking distance.',
                            ['city life', 'a crowded city', 'in the city center', 'village fair', 'a hometown town'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 3,
                'title' => 'Entertainment',
                'part' => 1,
                'outcome' => 'Talk about sports, music, and the internet',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: kinds of, for a long time',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What kinds of movies do you enjoy?', 'What kinds of music did you listen to as a child?', 'Have you had the same hobby for a long time?', 'How long have you been playing your favorite sport?', 'What kinds of things do you do to relax?'],
                            'I enjoy many different kinds of movies, from comedies to documentaries. I have loved football for a long time, so I have been playing it for over ten years now. For me, sports and music are the best kinds of entertainment because they never feel like wasted time.',
                            ['kinds of', 'for a long time', 'different kinds', 'an interesting kind of', 'the same hobby'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'What kinds of sports do you like?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What kinds of sports do you like to watch or play?', 'How often do you exercise?', 'Did you play sports when you were a child?', 'What sports are most popular in your country?', 'Do you think it is important for children to play sports?'],
                            'I like all kinds of sports, but my favorite is badminton because it works both as a friendly game and a competitive sport. I try to play twice a week to stay fit, and I believe children absolutely should play sports because they learn teamwork and discipline early.',
                            ['kinds of sports', 'play sports', 'a competitive sport', 'stay fit', 'sports equipment'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with the word sports',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Do you like watching sports on television?', 'Why do some people never take part in sports?', 'Are good sports facilities available everywhere in your country?', 'Should the government spend money on sports stadiums?', 'What is your favorite sports team?'],
                            'I usually watch big sports events on television, especially international tournaments. Public sports facilities in my city are still limited, so I believe the government should build more courts, because regular exercise prevents a lot of health problems and unites the community.',
                            ['sports facilities', 'sports events', 'a sports team', 'take part in sports', 'a professional sports event'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: on my phone, keep in touch',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How much time do you spend on your phone every day?', 'What do you usually do on your phone?', 'How do you keep in touch with your relatives?', 'Do you prefer calls, messages, or video chats?', 'Has using a phone changed your daily habits?'],
                            'I spend quite a lot of time on my phone, mainly watching videos and chatting. I use it to keep in touch with relatives who live abroad, and video calls have made it so much easier to stay connected. Sometimes I have to remind myself to put the phone down.',
                            ['on my phone', 'keep in touch', 'put the phone down', 'stay connected', 'a phone call'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'How important is the internet to you?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['How important is the internet in your daily life?', 'What would you do if you lost your internet connection for a week?', 'How has the internet changed the way people work?', 'Are there any dangers of using the internet too much?', 'Should children have unlimited internet access?'],
                            'I completely depend on the internet for both work and entertainment, so it is very important to me. When my internet connection is slow, my whole routine suffers. I think children need parental control because unlimited use can easily become an unhealthy habit.',
                            ['depend on the internet', 'internet access', 'high-speed internet', 'get online', 'browse the web'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Internet collocations',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do you often shop online?', 'What do you use the internet for most?', 'Have you ever had problems with your internet connection?', 'How do people in your country usually get online?', 'Will the internet make printed books disappear?'],
                            'I shop online almost every week and browse the web for news and study materials. When my internet connection failed last month, I realized how much I rely on it for everything. In the future I think faster connections will make downloads instant, but printed books will survive as a habit.',
                            ['shop online', 'browse the web', 'internet connection', 'get online', 'download and upload'],
                        ),
                    ],
                    [
                        'lesson_number' => 7,
                        'title' => 'Collocations: musical instrument, in the future',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Can you play a musical instrument?', 'What is the most popular musical instrument in your country?', 'Do children in your country learn instruments at school?', 'When did you last listen to live music?', 'Would you like to learn a musical instrument in the future?'],
                            'I can play the guitar, which I started learning at school. It is one of the most popular musical instruments in my country, so finding a teacher was easy. In the future I would love to learn the piano, but first I need to practice the guitar every day to really master it.',
                            ['a musical instrument', 'play an instrument', 'in the future', 'a live concert', 'practice every day'],
                        ),
                    ],
                    [
                        'lesson_number' => 8,
                        'title' => 'What\'s your favorite musical instrument?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What is your favorite musical instrument and why?', 'How does listening to music make you feel?', 'Is traditional music still popular where you live?', 'Do you think everyone should learn a musical instrument?', 'Has technology changed the way people make music?'],
                            'My favorite musical instrument is the violin because its sound is so expressive and emotional. When I listen to music, I feel calm and focused, which is why I play it during study breaks. I strongly believe children should try an instrument, because it trains patience and concentration even if they do not become musicians.',
                            ['favorite musical instrument', 'listen to music', 'play the piano', 'a good ear for music', 'learn an instrument'],
                        ),
                    ],
                    [
                        'lesson_number' => 9,
                        'title' => 'Collocations with the word music',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What kind of music do you like?', 'When do you usually listen to music?', 'How has music streaming changed how people discover music?', 'Do you prefer live music or recorded music?', 'Can music help people study or work better?'],
                            'I like acoustic music, and I usually listen to music while commuting. Music streaming has made it incredibly easy to find new artists, although nothing beats live music. Sometimes I put on soft background music to concentrate better when I study.',
                            ['listen to music', 'music streaming', 'background music', 'live music', 'a music festival'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 4,
                'title' => 'Accommodation',
                'part' => 1,
                'outcome' => 'Discuss buying, renting, and the building you live in',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: fourth floor, instead of',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What floor do you live on?', 'Do you prefer a high-rise building or a house?', 'Why do people choose apartments instead of houses in cities?', 'Is there a lift in your building?', 'Would you rather take the stairs or the lift every day?'],
                            'I live on the fourth floor of a high-rise building, and I usually take the lift instead of the stairs. Many young people choose apartments instead of houses because they are cheaper and easier to maintain. The only downside is waiting for the lift during rush hours.',
                            ['fourth floor', 'instead of', 'a high-rise building', 'take the stairs', 'on the ground floor'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'What kind of building do you live in?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What kind of building do you live in?', 'How long have you lived there?', 'What do you like most about your building?', 'What would you improve about your apartment?', 'Is your neighborhood a convenient place to live?'],
                            'I live in an apartment block in a quiet residential area. It is a fairly modern building with a shared rooftop garden, which is my favorite spot. The best part is the convenient location, because shops and transport stops are all within walking distance.',
                            ['an apartment block', 'a residential area', 'a convenient location', 'a shared garden', 'a quiet neighborhood'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with buy and rent',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do people in your country usually buy or rent their homes?', 'What are the advantages of renting an apartment?', 'What are the advantages of buying a house?', 'Is housing expensive in your city?', 'Would you prefer to buy a flat or rent one if you moved?'],
                            'In my country most young people rent an apartment first, because buying a house requires a large down payment. Renting gives you flexibility to move, but in the long run owning property is a better investment. The housing market in big cities has become quite expensive lately.',
                            ['rent an apartment', 'buy a house', 'pay rent', 'the housing market', 'a down payment'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 5,
                'title' => 'Transportation',
                'part' => 1,
                'outcome' => 'Talk about means of transportation and your commute',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: take the train, walking distance',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How do you usually get to work or school?', 'Have you ever taken the train to travel?', 'Is your home within walking distance of the station?', 'How long is your daily commute?', 'Do you prefer driving or taking public transport?'],
                            'I usually take the train to work because it is fast and cheap. My house is within walking distance of the station, so I rarely need to take a bus. My daily commute takes about thirty minutes, and I actually enjoy it because I use the time to read.',
                            ['take the train', 'walking distance', 'take a bus', 'public transport', 'a daily commute'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'How do you usually commute to work or school?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['How do you usually commute to work or school?', 'Has your commute changed over the years?', 'What is the busiest time of day for traffic?', 'Do you enjoy the journey, or does it waste your time?', 'How could public transport improve in your city?'],
                            'I usually commute to work by motorcycle because it helps me avoid the rush hour. Even so, I sometimes get stuck in traffic when it rains. My friends prefer public transport because it is a convenient way to travel and much kinder to the environment.',
                            ['commute to work', 'rush hour', 'get stuck in traffic', 'a convenient way', 'a daily commute'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Prepositions and vehicles',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Do you travel by car or by train more often?', 'Is it common to commute by bike in your country?', 'When you travel by bus, where do you like to sit?', 'What can cities do to reduce traffic jams?', 'Do you prefer travelling on foot when the distance is short?'],
                            'I usually travel by train when I go to the city, but I go on foot for nearby places because it keeps me active. When it rains, I get in the car or get on the bus. I believe travelling by bike is a great option for short trips and helps reduce traffic jams.',
                            ['by car', 'by train', 'on foot', 'get on the bus', 'get in the car'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 6,
                'title' => 'Travel',
                'part' => 1,
                'outcome' => 'Discuss holiday plans and favorite destinations',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: all over the country, summer break',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Where would you like to travel next?', 'Do people in your country travel all over the country for holidays?', 'When is your summer break?', 'What do you usually do during a long holiday?', 'Do you prefer travelling alone or with others?'],
                            'Many people in my country travel all over the country during the holiday season, especially to beaches and mountains. I usually use my summer break to visit my grandparents, and once a year we go on holiday to a coastal town. I hope to travel abroad in the next few years.',
                            ['all over the country', 'summer break', 'go on holiday', 'travel abroad', 'the holiday season'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'What\'s your favorite place to visit?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What\'s your favorite place to visit in your country?', 'What can visitors see and do there?', 'Do you prefer the mountains or the beach for a holiday?', 'Who do you usually travel with?', 'Is it better to plan a trip carefully or travel spontaneously?'],
                            'My favorite place to visit is Bali, because it is a world-famous tourist destination and the best time to visit is during the dry season. I often take a day trip there with friends, and we usually make a family trip there together once a year.',
                            ['a tourist destination', 'a day trip', 'the best time to visit', 'travel with friends', 'a family trip'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Travel collocations',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do you often go on trips?', 'What do you usually pack when you travel?', 'Has online booking made travel easier?', 'What is the most useful thing to bring on a holiday?', 'What was your most memorable trip?'],
                            'I go on a trip at least twice a year, and I like to book a hotel online in advance. I always pack a small bag to keep things simple, and for long distances I travel by plane. My most memorable journey was backpacking across neighboring islands with my friends.',
                            ['go on a trip', 'book a hotel', 'pack a bag', 'travel by plane', 'a memorable journey'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 7,
                'title' => 'Routines',
                'part' => 1,
                'outcome' => 'Describe your daily routine and weekend activities',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: take care of, sleep in',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What is your morning routine?', 'What responsibilities do you have at home?', 'How do you take care of your health?', 'What do you normally eat for breakfast?', 'Do you sleep in on the weekends?'],
                            'On a normal day I wake up early, have a shower, and make a simple breakfast. I take care of my younger sister before school, and I try to take care of my health with a short jog. On weekends, though, I love to sleep in, and that alone makes the days feel completely different.',
                            ['take care of', 'sleep in', 'wake up early', 'have a shower', 'day to day'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'How do you usually spend your weekends?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['How do you usually spend your weekends?', 'Did your weekend routine change after you grew up?', 'What makes a perfect weekend for you?', 'Do you plan your free time or keep it flexible?', 'How do you relax on Sunday before work?'],
                            'I usually spend my weekend divided between exercising on Saturday and resting on Sunday. It is a relaxing time compared to weekdays, and I enjoy going out with friends in the evening. For me a perfect weekend is one where my free time feels completely guilt-free.',
                            ['spend the weekend', 'going out with friends', 'a relaxing time', 'free time', 'weekend routine'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with weekend and time',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What do you do on a typical weekday?', 'How much free time do you have after work?', 'Is it hard for you to manage your time?', 'Do you prefer mornings or evenings for studying?', 'How has your daily routine changed over the past five years?'],
                            'After work I have only a couple of hours of free time, so I try not to waste time. I usually spend time reading, which is a good way to unwind. In the past I was bad at planning, but now I have learned to manage my time better, and it has changed my weekdays completely.',
                            ['free time', 'spend time', 'waste time', 'save time', 'manage one\'s time'],
                        ),
                    ],
                ],
            ],

            // ===================== PART 2 (Unit 8-11) =====================
            [
                'unit_number' => 8,
                'title' => 'Education',
                'part' => 2,
                'outcome' => 'Describe a class you\'ve taken',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: take a course, useful information',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Have you taken any courses outside school?', 'What kind of course would you like to take in the future?', 'When did you last learn something completely new?', 'Which teacher gave you the most useful information as a student?', 'Why do adults keep taking courses?'],
                            'I once took a short course in digital marketing, and it gave me a lot of useful information about online business. I believe taking a language course is the smartest way for adults to learn something new, and I plan to enrol in one soon so I can improve my presentations.',
                            ['take a course', 'useful information', 'a short course', 'a language course', 'learn something new'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Describe a class you\'ve taken',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe a class you\'ve taken that you remember well. You should say what it was, who taught it, what you did, and why you remember it.', 'What makes a class interesting for you?', 'Who has been your most inspiring teacher?', 'Should schools teach practical skills like cooking and finance?', 'What is the real value of a university education?'],
                            'I would like to describe a public speaking class I took in my final year. The teacher gave us useful information about structuring arguments, and we spent most of the time presenting in front of each other. It was a difficult subject at first, but we learnt a lot, and I still use those skills at work today.',
                            ['a difficult subject', 'learn a lot', 'put into practice', 'take a class', 'a training course'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with class and course',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do you prefer morning or afternoon classes?', 'Why have online courses become so popular?', 'Which courses do young people in your country usually take?', 'Should companies pay for their staff to take courses?', 'What is the difference between a class and a course?'],
                            'I prefer morning classes because I concentrate better early in the day. Many of my friends take an online course to improve their skills without leaving home. I strongly hope my company will offer a training course in project management next year, because practical skills are extremely useful.',
                            ['take a class', 'an online course', 'a training course', 'in class', 'a private course'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 9,
                'title' => 'Places',
                'part' => 2,
                'outcome' => 'Describe public and personal spaces',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: in my hometown, far away',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Is there a place in your hometown that you miss?', 'Do you prefer places near home or far away?', 'Which places in your city do you usually avoid?', 'Where would tourists go in your hometown?', 'What do you like most about living where you are now?'],
                            'There is a park in my hometown that I really miss, because it was close to my school. I do not mind travelling far away for work, but for everyday life I prefer places near my house, like a coffee shop or a shopping mall, because they save so much time.',
                            ['in my hometown', 'far away', 'near my house', 'a coffee shop', 'a shopping mall'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Describe a public place you visit',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe a public place you often visit. You should say where it is, who goes there, and why you visit it.', 'Why do people like to visit parks?', 'Do cities need more public places?', 'Is there a place in your city that you would improve?', 'How do people use public transport stations?'],
                            'I often visit the central public park because it offers a peaceful atmosphere, especially in the morning. On weekends it becomes crowded with people exercising and having picnics. I think good shopping centers are also important public places, because they bring people together even when the weather is bad.',
                            ['a public place', 'a public park', 'a peaceful atmosphere', 'crowded with people', 'a shopping center'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Place collocations',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What is your favorite place to read?', 'Which places have you visited recently?', 'How do you find your way around a new city?', 'Do you keep your workspace tidy?', 'Why do some people enjoy visiting old buildings?'],
                            'My favorite place to read is the university library, because it is a quiet place that helps me concentrate. When I visit a new city, I look for a landmark first to get my bearings, then a place where locals gather. Old buildings are a place of interest for me because they tell the city\'s stories.',
                            ['a quiet place', 'a place of interest', 'a landmark', 'a place to unwind', 'in the city center'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: free time, living room',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Where do you spend most of your free time at home?', 'Do you have a favorite corner in your home?', 'What furniture is there in your living room?', 'Do you often receive guests at home?', 'How do you decorate your personal space?'],
                            'I spend most of my free time in the living room, sitting on the big sofa with a cup of tea. My favorite decoration is a wall of family photos, and I keep a comfortable chair by the window for reading. Small touches like that make my home feel warm and welcoming.',
                            ['free time', 'the living room', 'a comfortable chair', 'family photos', 'my own space'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'Describe your favorite room in your home',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Describe your favorite room in your home. You should say what it looks like, what you do there, and why it is your favorite.', 'Do you keep your bedroom tidy or messy?', 'Would you like to have a bigger room? Why?', 'How do you make a room feel comfortable?', 'What does a good study room need?'],
                            'My favorite room is my bedroom, because it is my personal space and my escape from the rest of the world. I love the natural light that comes through the window, and I decorated it with plants and warm lamps to make it cozy. I always keep it tidy because it is where I recharge after a long day.',
                            ['a cozy room', 'personal space', 'natural light', 'a tidy room', 'a quiet place'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Room collocations',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How many rooms does your home have?', 'Which room does your family share the most?', 'Do you like having a private room?', 'What furniture is essential in a bedroom?', 'Could you live in one small studio room?'],
                            'Our home has three bedrooms and an open living room where my family spends most evenings together. I have my own private room, decorated with posters and fairy lights. I really value having a quiet space to study, because sharing a bedroom would make it impossible for me to concentrate.',
                            ['a private room', 'a living room', 'a bedroom', 'a shared space', 'a quiet corner'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 10,
                'title' => 'Hobbies & Entertainment',
                'part' => 2,
                'outcome' => 'Discuss your favorite TV show and talk about useful apps',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: get serious, be good at',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What hobby did you take up recently?', 'What are you naturally good at?', 'When did you get serious about your favorite hobby?', 'Is it easy to get serious about a new skill as an adult?', 'What would you like to get better at in the next year?'],
                            'A couple of years ago I started taking photography seriously after years of casual snapping. I would say I am naturally good at noticing small details, which is why I enjoy it. Once you get serious about a hobby, your progress becomes much faster and more satisfying.',
                            ['get serious', 'be good at', 'take up a hobby', 'practice regularly', 'a natural talent'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Describe a current hobby',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe a hobby you currently enjoy. You should say what it is, how you started it, and what you get from it.', 'How much time do you spend on hobbies each week?', 'Do people in your country have enough time for hobbies?', 'Is it better to have one hobby or several?', 'What hobbies will be popular in the future?'],
                            'My current hobby is jogging in the park every morning. I started it to stay healthy, and it has become a habit I genuinely look forward to. It gives me energy for the whole day, and I listen to podcasts while I run, so it never feels like wasted time.',
                            ['a current hobby', 'in my free time', 'stay active', 'a refreshing break', 'an enjoyable way'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with the word fun',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What do you do for fun at the weekend?', 'Was school a fun experience for you?', 'Do adults have as much fun as children?', 'What is the most fun party you have attended?', 'Can studying be fun?'],
                            'I play badminton for fun every weekend with my colleagues. Although learning is difficult, I believe studying can be a fun experience if you turn it into a game or a challenge. For most people, weekend trips with friends are simply a lot of fun and a welcome break from routine.',
                            ['for fun', 'a lot of fun', 'a fun experience', 'have fun', 'funny moments'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: check out, make an impression',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What new shows have you checked out recently?', 'Describe a film or show that made an impression on you.', 'Do you check out reviews before watching a movie?', 'When has a person made a strong first impression on you?', 'What makes a movie leave a lasting impression?'],
                            'I recently checked out a documentary about the ocean, and it really made an impression on me because of its beautiful footage. Before watching anything, I usually check out the critics\' reviews, but sometimes a film without great reviews can still leave a lasting impression because of its story.',
                            ['check out', 'make an impression', 'a first impression', 'a lasting impression', 'worth watching'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'Describe a TV show or movie that made an impression',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Describe a TV show or movie that made an impression on you. You should say what it was, when you watched it, and why it stayed with you.', 'Do you prefer watching at home or in a cinema?', 'Which character stayed with you the longest?', 'Why do some series become worldwide hits?', 'Should parents limit what children watch?'],
                            'A movie that made a deep impression on me was Interstellar, because of its emotional story and stunning visuals. I saw it in the cinema years ago, yet I still think about its message about love and time. Since then I have rewatched it a few times, and it never loses its power for me.',
                            ['make an impression', 'watch a movie', 'in the cinema', 'character development', 'a memorable scene'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'TV and movie collocations',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How often do you watch TV series?', 'Do you watch sports matches on TV?', 'What movies are popular in your country right now?', 'Would you pay for a streaming service?', 'Do you prefer subtitles or dubbing?'],
                            'I rarely watch traditional TV, but I watch a good TV series on a streaming service almost every evening. I mostly enjoy documentaries and light comedies. I prefer watching with subtitles, because dubbing often loses the emotion of the original acting.',
                            ['watch TV', 'a TV series', 'a streaming service', 'movie tickets', 'watch a film'],
                        ),
                    ],
                    [
                        'lesson_number' => 7,
                        'title' => 'Collocations: a convenient way, communicate with',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do apps offer a convenient way to do everyday things?', 'How do you communicate with your classmates or colleagues?', 'Which apps make your life easier?', 'Do you prefer meeting in person or chatting online?', 'Has technology improved communication in your family?'],
                            'Messaging apps are a convenient way to manage everything, from paying bills to booking doctor visits. I communicate with my team mostly through a group chat, which is great for quick questions. Although I see my family in person often, chats help us stay in touch every day.',
                            ['a convenient way', 'communicate with', 'a useful app', 'stay in touch', 'keep in contact'],
                        ),
                    ],
                    [
                        'lesson_number' => 8,
                        'title' => 'Describe an app that you often use on your phone',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe an app that you often use on your phone. You should say what it does, how often you use it, and why it is useful.', 'Do people download too many apps?', 'What is the most necessary feature of a phone app?', 'Do you ever pay for apps?', 'Which app could you not live without?'],
                            'The app I use most on my phone is a note-taking app; I open it dozens of times a day for tasks and ideas. It is free to use and syncs instantly across my devices, so it feels like a reliable assistant. Most apps are convenient, but this one is essential for how I organize my whole week.',
                            ['an app', 'on my phone', 'download an app', 'a useful feature', 'a convenient way'],
                        ),
                    ],
                    [
                        'lesson_number' => 9,
                        'title' => 'App collocations',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What kinds of apps do students use most?', 'Do apps make people lazier or more efficient?', 'Which apps are popular with older people?', 'Are free apps really free?', 'Should schools teach app development?'],
                            'Students in my country mostly install education apps for flashcards and schedules, plus a mobile game to relax. Seniors prefer simple apps like large-button calculators and chat apps to talk with their children. Although many useful apps claim to be free, they usually rely on ads or ask you to update the app constantly.',
                            ['install an app', 'a mobile game', 'an education app', 'a useful app', 'update the app'],
                        ),
                    ],
                    [
                        'lesson_number' => 10,
                        'title' => 'Collocations: important part, most people',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Is technology an important part of your daily routine?', 'What is an important part of staying healthy?', 'Do most people in your country use e-money?', 'What do most people do to relax after work?', 'Has social media become an important part of social life?'],
                            'Technology is an important part of my daily routine, and I believe most people in my city would agree. Most people now pay with e-money, and it has become part of everyday errands. Even so, I try to keep exercise an important part of my week so my life is not purely digital.',
                            ['an important part', 'most people', 'part of daily life', 'a significant role', 'the majority of'],
                        ),
                    ],
                    [
                        'lesson_number' => 11,
                        'title' => 'Describe something you own that\'s very useful',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe something you own that is very useful. You should say what it is, how you got it, and how often you use it.', 'Why do people buy things they rarely use?', 'Are everyday objects becoming smarter?', 'How important are portable chargers in your life?', 'What is the most useful gift you have received?'],
                            'The most useful object I own is my wireless headphone; I use it on a daily basis for calls, study, and workouts. It is extremely useful because it is comfortable to wear all day, and I have got so used to it that I cannot imagine commuting without it. A good gadget should disappear into your routine.',
                            ['extremely useful', 'on a daily basis', 'a useful gadget', 'a reliable device', 'get used to'],
                        ),
                    ],
                    [
                        'lesson_number' => 12,
                        'title' => 'Collocations with use and useful',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['How useful is your phone for studying?', 'What do people use public transport for?', 'Do you make good use of your free time?', 'Is it useful to learn more than one language?', 'Do you reuse old items or throw them away?'],
                            'I try to make good use of my evenings by studying English for half an hour. Learning a language is really useful for both work and travel, and the phone helps with flashcards. On weekends I put old clothes to good use by donating them, which is a small but meaningful habit.',
                            ['make good use of', 'be useful for', 'a useful tool', 'put to good use', 'use technology'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 11,
                'title' => 'People & Events',
                'part' => 2,
                'outcome' => 'Describe people and memorable events',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: get into, find out',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How did you get into your current hobby?', 'What new hobby would you like to get into?', 'How do you usually find out about the news?', 'When did you get into watching documentaries?', 'Who first took you to something you still love doing today?'],
                            'I got into photography because my uncle lent me his old camera. These days I find out about photography news on YouTube and forums. I once got into trouble for uploading a photo without permission, and that taught me to check the rules before sharing anything online.',
                            ['get into', 'find out', 'get into trouble', 'find out about', 'give it a try'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Describe a memorable event in your life',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe a memorable event in your life. You should say when it happened, who was there, and why you still remember it.', 'Do adults enjoy celebrating their birthdays?', 'Which national event is celebrated in your country?', 'Do you remember events from your childhood clearly?', 'Is it important to mark special dates?'],
                            'One unforgettable event was my high-school graduation; it was a special occasion for which my whole family came together. I remember the feeling clearly, as if it closed one chapter of my life and opened another. In my country, graduation is a big deal, and families usually celebrate with a large dinner.',
                            ['a memorable event', 'a special occasion', 'an unforgettable experience', 'remember clearly', 'a celebration'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with the words event and memory',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What events does your city hold every year?', 'Are you good at keeping memories of places?', 'Why do some memories fade faster than others?', 'Which local event would you recommend to tourists?', 'Should museums preserve collective memories?'],
                            'My city holds a food festival every year, and it is a special event that people travel for. I have a good memory for faces but only a vague memory for names. In memory of my grandfather, my family still cooks his favorite dish at festivals, so for me events and memories are deeply connected.',
                            ['a special event', 'an unforgettable memory', 'a good memory', 'a vague memory', 'in memory of'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: positive qualities, spend time',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What positive qualities do you admire in a friend?', 'Who has influenced the way you treat people?', 'How do you usually spend time with your loved ones?', 'Which quality matters most: honesty, patience, or humor?', 'Should parents let children choose their own friends?'],
                            'I value positive qualities like honesty and a good sense of humor in a friend. I usually spend time with my closest friends playing board games or cooking together. A loyal and caring person who is honest with you is rare, and in my experience that kind of friendship is worth more than many acquaintances.',
                            ['positive qualities', 'spend time', 'an honest person', 'a good sense of humor', 'a loyal friend'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'Describe someone who has had a significant influence',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Describe someone who has had a significant influence on you. You should say who they are, how you know them, and how they changed the way you think.', 'How can a person shape your daily habits?', 'Can famous people influence ordinary life?', 'Do role models actually matter?', 'Does influence only come from people close to you?'],
                            'My high-school physics teacher has had a significant influence on me. He was an inspiring role model who showed me that curiosity matters more than grades. Because I looked up to him, I started solving problems instead of memorizing answers, and that habit still shapes my career today.',
                            ['significant influence', 'an inspiring role model', 'look up to', 'follow their example', 'shape your habits'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Influence collocations',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Who influences your shopping choices?', 'Does social media influence the way you think?', 'Are children easily influenced by advertising?', 'How do peers influence teenagers?', 'What has the strongest influence on how you spend your free time?'],
                            'Social media certainly has an influence on how I discover new music and recipes. Advertisers often try to influence people by hiring influencers, and children are especially easily influenced by that. I try to let trusted experts, rather than ads, have the strongest influence on my serious decisions.',
                            ['have an influence on', 'an influencer', 'be easily influenced', 'influence people', 'a strong influence'],
                        ),
                    ],
                    [
                        'lesson_number' => 7,
                        'title' => 'Collocations: family members, hard to explain',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How many family members do you live with?', 'Are you closer to your relatives now than as a child?', 'Why is your dream job sometimes hard to explain to older relatives?', 'Which family member do you ask for advice?', 'Do you prefer big family gatherings or small ones?'],
                            'I live with four family members in a close-knit household. Sometimes my remote-work job is hard to explain to older relatives, because they grew up in a world where that did not exist. But our extended family still gathers every month, and that steady contact is a huge source of comfort for all of us.',
                            ['family members', 'hard to explain', 'a close-knit family', 'extended family', 'gather together'],
                        ),
                    ],
                    [
                        'lesson_number' => 8,
                        'title' => 'Describe the most successful member of your family',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Describe the most successful member of your family. You should say who they are, what they achieved, and why you respect them.', 'Is success about money, recognition, or happiness?', 'Do young people in your family copy that example?', 'Have standards of success changed between generations?', 'What does success at work look like for you?'],
                            'The most successful member of my family is my aunt, who built a career as a surgeon. Her success story began in a small town, and she often jokes that it took years of hard work and sacrifice. I am extremely proud of her, but she insists that real success means being happy with your contribution, not just your salary.',
                            ['the most successful', 'a success story', 'a successful career', 'be proud of', 'achievement'],
                        ),
                    ],
                    [
                        'lesson_number' => 9,
                        'title' => 'Collocations with success and successful',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['How would you personally define success?', 'What makes a business successful?', 'Do exams measure success fairly?', 'Who is a successful leader in your view?', 'Does success automatically bring happiness?'],
                            'For many, success in life means a successful career and a comfortable home, but I would define it more broadly as inner peace and growth. A business becomes highly successful when it grows without damaging its employees\' trust. Above all, true success for me is being able to respect yourself at the end of the day.',
                            ['success in life', 'a successful career', 'highly successful', 'a sense of achievement', 'a success story'],
                        ),
                    ],
                ],
            ],

            // ===================== PART 3 (Unit 12-17) =====================
            [
                'unit_number' => 12,
                'title' => 'Studies',
                'part' => 3,
                'outcome' => 'Discuss the importance of studying foreign languages',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: foreign language, focus on',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Why do many schools focus on foreign languages from an early age?', 'Is it easier for children than adults to learn a foreign language?', 'Should everyone be required to study a foreign language?', 'What is the hardest part of mastering a foreign language?', 'How can technology help people focus on language learning?'],
                            'Schools focus on foreign languages early because children absorb sounds and grammar naturally, and those skills stay useful for life. I believe adults can still succeed, but they must focus on consistent daily practice instead of hoping for quick results. Technology, with its chat apps and flashcards, makes that kind of focus much easier.',
                            ['a foreign language', 'focus on', 'language skills', 'daily practice', 'fluency'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Do you think it\'s important for people to study languages?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Do you think it is important for people to study languages? Why?', 'Which foreign language deserves more attention in your country?', 'Are bilingual people at an advantage at work?', 'Should governments make foreign languages mandatory in schools?', 'Could machine translation make language study unnecessary?'],
                            'Absolutely, I believe studying a language opens both career opportunities and personal growth. In our region, English is the language that helps people cross the language barrier in most industries. Machine translation is a useful tool, but it can never replace the trust that comes from speaking with native speakers directly.',
                            ['study a language', 'the importance of', 'career opportunities', 'a language barrier', 'native speakers'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Language collocations',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How have you improved your English recently?', 'What do learners find hardest about English?', 'When did you start studying a foreign language?', 'Are there better ways to teach languages in school?', 'Should companies offer language training to employees?'],
                            'I improve my English by practicing speaking with tutors online and shadowing podcast phrases. Picking up a language is easier when you connect it to daily habits, like keeping notes in English. I believe language teaching should be more conversational, because learners need confidence before perfect grammar.',
                            ['improve your English', 'practice speaking', 'pick up a language', 'language teaching', 'a native speaker'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 13,
                'title' => 'Hobbies & Entertainment',
                'part' => 3,
                'outcome' => 'Describe popular activities in your country',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: a big part of, a movie theater',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['What is a big part of young people\'s entertainment?', 'Do people still go to a movie theater these days?', 'How do people in your country usually spend the weekend?', 'Are sports a big part of the national culture?', 'Has cooking become a bigger part of home life?'],
                            'Streaming has become a big part of young people\'s entertainment, yet the movie theater is still where friends gather for big releases. Sports, especially football, are also a big part of our national culture. For many families, gathering to watch a match together is as precious as going to the movies used to be.',
                            ['a big part of', 'a movie theater', 'go to the movies', 'part of the culture', 'entertainment industry'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'What kinds of TV shows are popular in your country?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What kinds of TV shows are popular in your country?', 'Do reality shows deserve their popularity?', 'What makes a local show succeed abroad?', 'Can TV shows help people learn languages?', 'Should channels invest more in local series?'],
                            'In my country, drama series and cooking competitions are popular among different age groups. Reality shows are also huge, though I think their appeal comes from relatability rather than depth. I would personally love more travel shows about our own regions, because they combine entertainment with real information.',
                            ['TV shows', 'a drama series', 'reality shows', 'popular in your country', 'a travel show'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Online entertainment collocations',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['How much time do people spend on online entertainment?', 'Is online entertainment safe for children?', 'Will live shows survive competition from streaming?', 'How do you choose what to watch?', 'Should online platforms be more strictly regulated?'],
                            'Online entertainment now competes with sleep for some people\'s evenings, which makes it risky for children. Streaming services depend on a subscription fee, but they give us the freedom to watch at our own pace. I believe live shows will survive, because the energy of a shared audience cannot be perfectly streamed.',
                            ['online entertainment', 'a streaming service', 'a subscription fee', 'watch at your own pace', 'screen time'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: go out, negative effect',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['How often do people in your culture go out together?', 'Can too much time online have a negative effect?', 'What has a negative effect on students\' focus?', 'Do you prefer going out or hosting friends at home?', 'How should students balance going out and studying?'],
                            'In my country, going out with friends is very common on weekends. Yet spending too much time on screens can have a negative effect on both sleep and sociability. I try to balance going out and studying with a simple rule: mornings for work, and evenings for people I care about.',
                            ['go out', 'a negative effect', 'go out with friends', 'spend time together', 'a point of balance'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'What hobbies are common in your culture?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What hobbies are common in your culture?', 'Are traditional hobbies disappearing?', 'Do friends in your country usually share hobbies?', 'What hobbies are gaining popularity with older people?', 'Can a hobby become a full-time job?'],
                            'Gardening and badminton are common in our culture, and both are often passed down from parents to children. Traditional hobbies such as drawing batik are less common among young people, though classes are making a comeback. I think a shared hobby, like a weekly football group, creates strong friendships and sometimes even grows into paid work.',
                            ['common in your culture', 'traditional hobbies', 'a shared hobby', 'hobbies and interests', 'pass down'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Collocations about change',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['How have daily habits changed over the past decade?', 'Do people generally welcome or fear change?', 'Which change has improved your life the most?', 'How is your country\'s culture changing?', 'Should schools prepare students for a fast-changing world?'],
                            'Society is quick to adapt to change, but some people still fear rapid technological change. The change that improved my life most was hybrid work, which gave me time to exercise. To keep up with changes in my sector, I set aside weekly learning time, and I believe education must teach adaptability, not just facts.',
                            ['adapt to change', 'keep up with changes', 'a rapid change', 'embrace change', 'a change of pace'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 14,
                'title' => 'Public & Personal Spaces',
                'part' => 3,
                'outcome' => 'Talk about cities and public spaces',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: public spaces, spend time',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Why do cities need public spaces?', 'Where do residents prefer to spend time in your city?', 'How do public spaces build community?', 'How have parks helped people during hot weather?', 'What makes a public space feel welcoming?'],
                            'Public spaces such as parks and city squares give residents a place to spend time without having to buy anything. They create a sense of community because strangers meet and share benches and stories. In my city, the new green public spaces have become the heart of weekend life.',
                            ['public spaces', 'spend time', 'green public spaces', 'a sense of community', 'the city square'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Do you think it\'s important for cities to provide public spaces?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Do you think it is important for cities to provide public spaces?', 'Who should fund and maintain public parks?', 'Are public spaces more important than parking lots?', 'What happens when a square is replaced by a building?', 'How can a small city create better public spaces?'],
                            'Yes, cities should provide public spaces, because they directly improve the quality of life for every resident. The local government should fund parks, but citizens must help protect them. When a city removes a square for a tower, the effect is social: fewer places to meet and relax, which slowly changes a neighborhood for the worse.',
                            ['provide public spaces', 'the local government', 'quality of life', 'the city center', 'public facilities'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations with the word public',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Which public services matter most in your country?', 'Is public transport reliable in your city?', 'Should public libraries be expanded?', 'Do people behave differently in public than in private?', 'Which public event is unforgettable for you?'],
                            'Public transport and health services matter most for daily life, and reliable public transport keeps a city alive. I have noticed that people behave politely in public, even when they complain freely on private chats. I would defend public libraries strongly, because they remain free learning spaces for everyone.',
                            ['public transport', 'public services', 'in public', 'public libraries', 'a public holiday'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: affordable housing, long-term plan',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Is affordable housing available in your city?', 'What long-term plan does your local government need?', 'Why does rent keep rising so fast?', 'Should cities build more low-cost apartments?', 'How do young people manage to buy a home today?'],
                            'Affordable housing is the biggest worry for young adults in my city, and rents keep climbing along with the cost of living. The local government needs a long-term plan that combines low-cost apartments with good transport links. Without such a plan, finding a place to live near work becomes impossible for graduates.',
                            ['affordable housing', 'a long-term plan', 'a place to live', 'the cost of living', 'a housing plan'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'How hard is it to find a place to live in your city?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['How hard is it to find a place to live in your city?', 'Do people in your country prefer renting or buying?', 'What makes a neighborhood desirable?', 'Should housing near universities be protected?', 'Is remote work changing where people live?'],
                            'In my city it is quite hard to find a place to live, because demand now outstrips supply and the city keeps growing. Most people prefer renting in a quiet residential area that still has good transport. Remote work has eased demand near offices but pushed more people toward quieter suburbs.',
                            ['find a place to live', 'a residential area', 'a growing city', 'moving to the city', 'a desirable neighborhood'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Collocations with the verb live',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Where do you live and with whom?', 'Would you be willing to live abroad for work?', 'What does it mean to live within your means?', 'What kind of neighborhood would you love to live in?', 'Has living alone become more common in your country?'],
                            'I live in a small apartment near the university, and for now I live on my own. I would happily live abroad for a couple of years to gain experience and a new perspective. Living alone taught me budgeting, and I now try to live within my means while saving for a home of my own.',
                            ['live in', 'live abroad', 'live on your own', 'live near', 'a place to live'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 15,
                'title' => 'Personal Belongings',
                'part' => 3,
                'outcome' => 'Describe personal objects and possessions',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations: rely on, electronic device',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Which electronic device do you rely on most?', 'Do people rely on apps to remember things?', 'What gadgets do students rely on today?', 'How reliable are modern electronic devices?', 'Could you function without electricity for a week?'],
                            'I rely on my laptop more than any other electronic device, because work and study both flow through it. Students today rely on their phones for calendars and notes, and portable chargers have become essential. Anything offline suddenly feels broken, which shows how deep the dependence has grown.',
                            ['rely on', 'an electronic device', 'a reliable device', 'a laptop', 'a portable charger'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'What kinds of electronic devices are most popular?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What kinds of electronic devices are most popular in your country?', 'Are older people adopting new devices now?', 'Which device will become popular next?', 'Should children own personal devices?', 'Do electronic devices make life easier or more stressful?'],
                            'In my country, smartphones are the most popular electronic devices, followed closely by wireless earbuds and smartwatches. Seniors are now embracing smartwatches for health, which is a wonderful trend. I believe the next popular device will be something wearable, but children definitely need limits, because too much screen time harms sleep and attention.',
                            ['electronic devices', 'a smartwatch', 'a personal computer', 'screen time', 'popular with'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Technology collocations',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['How has technology changed communication in your family?', 'Which technology has improved your education?', 'Is technology isolating people from each other?', 'How do people manage technology overload?', 'Will technology make us happier in the future?'],
                            'Modern technology has changed family communication completely; we now have daily group chats but fewer long conversations. In education, cutting-edge technology such as online labs allowed practice from home during tough times. To avoid overload, I embrace technology selectively, because a person who curates what they install tends to live a calmer life.',
                            ['modern technology', 'cutting-edge technology', 'embrace technology', 'advances in technology', 'digital habits'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'Collocations: old photos, sentimental value',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do you keep old photos in your home?', 'Why do objects gain sentimental value over time?', 'Are printed photos making a comeback?', 'Which object has sentimental value for you?', 'Why do people keep things they no longer need?'],
                            'I keep old photos and my grandfather\'s watch, both of which are full of sentimental value. A family album is a treasure, even when the cover is dusty. I think people keep things because the memory attached to an object outweighs its usefulness, and that is true for almost everyone.',
                            ['old photos', 'sentimental value', 'a keepsake', 'a family album', 'a personal treasure'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'What types of possessions do people generally keep?',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['What types of possessions do people generally keep?', 'Do people become attached to gifts?', 'Should people own fewer things?', 'What do people collect in your country?', 'What do our possessions say about us?'],
                            'People generally keep useful possessions such as furniture, appliances, and phones, plus a few personal belongings that carry memories. Many also keep small collections, like pins or keychains from trips. I try to throw away what I no longer use, because fewer objects usually means a clearer mind.',
                            ['types of possessions', 'personal belongings', 'a collection of', 'keep things', 'throw away'],
                        ),
                    ],
                    [
                        'lesson_number' => 6,
                        'title' => 'Collocations with personal belongings',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['How do you organize your personal belongings?', 'Do family members share belongings or keep their own?', 'What happens when luggage is lost on a trip?', 'Which personal item is the hardest to replace?', 'What does losing a belonging teach people?'],
                            'I organize my personal belongings in labeled boxes, which keeps the house calm. At home we share some things, like the coffee machine, but each of us has personal items that are off-limits. Taking care of what you own teaches discipline, and losing even a cheap item once taught me to value things more.',
                            ['personal belongings', 'take care of', 'a personal item', 'a valuable possession', 'keep safe'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 16,
                'title' => 'Holidays',
                'part' => 3,
                'outcome' => 'Talk about festivals and celebrations',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'Collocations with the words holiday and celebration',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['Which holiday is celebrated most in your country?', 'How do people prepare for a national holiday?', 'Do celebrations bring families closer?', 'How do holidays differ across regions of your country?', 'Should the state invest in cultural festivals?'],
                            'The biggest celebration in my country is the end-of-fasting festival, a national holiday when families travel home to see relatives. Preparation starts weeks in advance, and the whole holiday season seems to soften the rhythm of the country. Money spent on cultural festivals is a real investment in tourism and identity.',
                            ['a national holiday', 'a celebration', 'the holiday season', 'celebrate with your family', 'a cultural festival'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Collocations: take off, whole week',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do workers take time off during the holidays?', 'Would you take off a whole week for a long trip?', 'Do long holidays improve productivity afterwards?', 'How do companies decide who takes time off first?', 'Have you ever taken time off school for a family event?'],
                            'In my country, many workers take off a whole week during the biggest holidays. Taking time off before a big project actually makes the work that follows stronger. I usually take a break mid-year, but saving enough days for a whole week with the family is always my plan.',
                            ['take off', 'a whole week', 'time off', 'take a break', 'the whole holiday'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'What are some of the main holidays or special events?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What are some of the main holidays or special events in your country?', 'How do cities decorate during festivals?', 'Are there any new holidays being invented?', 'Do local festivals help small businesses?', 'Should every region keep its own special events?'],
                            'The main holidays in my country include the new-year festival, independence month, and the Ramadan month, followed by the big celebration. Cities decorate with lights and hold a festival downtown, which gives a real boost to small food businesses. I believe every region should keep its own special events, because they give communities their identity.',
                            ['main holidays', 'special events', 'a festival', 'hold an event', 'cultural celebrations'],
                        ),
                    ],
                ],
            ],
            [
                'unit_number' => 17,
                'title' => 'Perspectives',
                'part' => 3,
                'outcome' => 'Discuss achievement and work-life balance',
                'lessons' => [
                    [
                        'lesson_number' => 1,
                        'title' => 'What kinds of people tend to be famous in your country?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What kinds of people tend to be famous in your country?', 'Is fame a healthy goal for young people?', 'Do celebrities influence spending habits?', 'Does online fame last very long?', 'Should famous people act as moral examples?'],
                            'In my country, entertainment figures and successful athletes tend to be famous, though online influencers now join them very quickly. I worry that fame is a fragile goal, because the public eye changes so fast. Celebrities influence what people buy, so I believe those in the spotlight should be mindful of that power.',
                            ['famous people', 'become famous', 'a celebrity', 'in the public eye', 'influence people'],
                        ),
                    ],
                    [
                        'lesson_number' => 2,
                        'title' => 'Celebrity collocations',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Why do people follow celebrities so closely?', 'Is celebrity culture harmful to teenagers?', 'Which celebrity behavior do you find hard to accept?', 'Do celebrities deserve their high earnings?', 'How can brands and celebrities work together responsibly?'],
                            'People follow celebrities because they offer a mix of drama and aspiration, which is exactly why celebrity culture can be harmful to teenagers. A star builds a public image, and fans expect it to stay consistent. I think celebrities deserve high earnings when genuine talent backs them up, but brands must choose their partners carefully.',
                            ['a celebrity', 'a public image', 'be famous for', 'fans expect', 'celebrity culture'],
                        ),
                    ],
                    [
                        'lesson_number' => 3,
                        'title' => 'Collocations: make money, work-life balance',
                        'difficulty' => LessonDifficulty::MEDIUM->value,
                        'questions' => $this->questions(
                            ['Do people in your country make money through side hustles?', 'Is making money your main career goal?', 'How hard is it to keep a healthy work-life balance?', 'Would you change jobs for less money and better balance?', 'What most harms work-life balance in modern offices?'],
                            'Many young people now try to make money through online side hustles on top of their main jobs. For me, the priority is a healthy work-life balance, so I would gladly accept a slightly lower salary for fewer late-night calls. Protect the balance, because burnout is far harder to cure than a missed bonus.',
                            ['make money', 'work-life balance', 'earn a living', 'a side hustle', 'burn out'],
                        ),
                    ],
                    [
                        'lesson_number' => 4,
                        'title' => 'What does it mean to be successful in your society?',
                        'difficulty' => LessonDifficulty::DIFFICULT->value,
                        'questions' => $this->questions(
                            ['What does it mean to be successful in your society?', 'Do parents and children define success differently?', 'Can success be measured in happiness?', 'Why do people compare their success so openly?', 'How has social media changed the meaning of success?'],
                            'In my society, success is still often measured by a stable job, a home, and a family, though young people are redefining it as freedom and purpose. Parents and children genuinely differ: parents value security, while children value flexibility. Fewer comparisons would help everyone, because comparing ourselves to curated online lives distorts any healthy idea of success.',
                            ['success in life', 'a successful person', 'a definition of success', 'a sense of achievement', 'compare ourselves with'],
                        ),
                    ],
                    [
                        'lesson_number' => 5,
                        'title' => 'Collocations: become famous, role model',
                        'difficulty' => LessonDifficulty::EASY->value,
                        'questions' => $this->questions(
                            ['Do you personally want to become famous?', 'Who is a real role model for you today?', 'Do children need role models in their lives?', 'Can successful people also be bad role models?', 'Is it easier to become famous today than before?'],
                            'I have never wanted to become famous; I would rather do meaningful work quietly. My role model is a teacher who left a high-paying job to teach in small towns. Children do need people to look up to, but fame comes with pressure, and I believe we should admire people for character, not just visibility.',
                            ['become famous', 'a role model', 'look up to', 'be in the spotlight', 'a good example'],
                        ),
                    ],
                ],
            ],
        ];
    }

    /**
     * Bangun daftar soal dari templat pertanyaan & jawaban.
     *
     * @param  array<string>  $questionTexts
     * @return array<int, array{question_text: string, model_answer: string, key_point: string}>
     */
    private function questions(array $questionTexts, string $modelAnswer, array $keyPoints): array
    {
        $result = [];

        foreach ($questionTexts as $i => $text) {
            $result[] = [
                'question_text' => $text,
                'model_answer' => $modelAnswer,
                'key_point' => $keyPoints[$i % count($keyPoints)],
            ];
        }

        return $result;
    }
}