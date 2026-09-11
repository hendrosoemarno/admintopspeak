<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class TransportationUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Unit 5: Transportation (Part 1) ──────────────────────
        $unit = Unit::create([
            'unit_number' => 5,
            'title' => 'Transportation',
            'part' => 1,
            'outcome' => 'Menggunakan kolokasi sarana transportasi (take the train, catch a bus, ride a bike), jarak (within walking distance, short walk), dan preposisi kendaraan yang tepat.',
        ]);

        // ── LESSON 1: Collocations: "Take the Train", "Walking Distance" ─────────
        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations: "Take the Train", "Walking Distance"',
            'difficulty' => 'Medium',
        ]);

        $questions1 = [
            [
                'question_text' => 'Do you usually take the train or drive to work?',
                'model_answer' => 'I usually take the train because it helps me avoid morning traffic jams.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Is your house within walking distance of the nearest station?',
                'model_answer' => 'Yes, the metro station is within walking distance, just a five-minute stroll from my house.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'How often do you take the train to visit nearby cities?',
                'model_answer' => 'I take the train about twice a month whenever I visit my relatives in the neighboring city.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Are essential shops within walking distance of your apartment?',
                'model_answer' => 'Convenience stores and local cafes are all within walking distance of my flat.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Why do many commuters prefer to take the train during peak hours?',
                'model_answer' => 'Commuters take the train during peak hours because it offers predictable travel times.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Is your school within walking distance or do you need a bus?',
                'model_answer' => 'It is within walking distance, so I rarely need to take public transport.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Is it expensive to take the train in your country?',
                'model_answer' => 'No, taking the train is very affordable thanks to government transport subsidies.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Would you consider buying a house that isn\'t within walking distance of transit?',
                'model_answer' => 'I prefer properties within walking distance of transit to keep my daily travel easy.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Do you prefer to take the express train or local train?',
                'model_answer' => 'I prefer to take the express train because it skips minor stops and saves time.',
                'key_point' => 'Kolokasi take the express train.',
            ],
            [
                'question_text' => 'Is the city park within walking distance of your office?',
                'model_answer' => 'Yes, the central park is within walking distance, so I often eat lunch there.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Have you ever missed your chance to take the last train home?',
                'model_answer' => 'Yes, once I stayed out late with friends and missed the last train, so I had to order a taxi.',
                'key_point' => 'Kolokasi take the last train.',
            ],
            [
                'question_text' => 'Why is living within walking distance of workplace ideal?',
                'model_answer' => 'Living within walking distance eliminates commute stress and saves money on fuel.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Do you read books when you take the train?',
                'model_answer' => 'Yes, I always listen to podcasts or read e-books whenever I take the train.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Are primary schools in your city within walking distance for children?',
                'model_answer' => 'In most neighborhoods, elementary schools are within walking distance for safety.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Is it comfortable to take the train during summer?',
                'model_answer' => 'Yes, modern carriages are air-conditioned, making taking the train very pleasant.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Is a grocery store within walking distance of your place?',
                'model_answer' => 'Fortunately, a large supermarket is within walking distance right across the street.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Do tourists prefer to take the train to travel around your country?',
                'model_answer' => 'Tourists love to take the train because it offers scenic views of the countryside.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'What do you do if your destination isn\'t within walking distance?',
                'model_answer' => 'If it\'s not within walking distance, I usually rent an electric scooter or take a bus.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'How early do you leave home to take the morning train?',
                'model_answer' => 'I leave my home at 7 AM to take the morning train without rushing.',
                'key_point' => 'Kolokasi take the morning train.',
            ],
            [
                'question_text' => 'Is your favorite restaurant within walking distance?',
                'model_answer' => 'Yes, it\'s a short walk away, completely within walking distance of my house.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Why do people choose to take the high-speed train instead of flying?',
                'model_answer' => 'They take the high-speed train to avoid lengthy airport security check-ins.',
                'key_point' => 'Kolokasi take the high-speed train.',
            ],
            [
                'question_text' => 'Do you feel safe walking to locations within walking distance at night?',
                'model_answer' => 'Yes, our street is well-lit, so walking to nearby spots at night feels very safe.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Do you need a monthly pass to take the train daily?',
                'model_answer' => 'Yes, buying a monthly travel pass makes taking the train much cheaper.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'How far is an acceptable distance to be considered within walking distance?',
                'model_answer' => 'Anything under a 15-minute walk, or roughly one kilometer, is within walking distance.',
                'key_point' => 'Definisi within walking distance.',
            ],
            [
                'question_text' => 'Is it easy to take the train with heavy luggage?',
                'model_answer' => 'It can be tricky during rush hours, but stations have elevators to assist passengers.',
                'key_point' => 'Context take the train.',
            ],
            [
                'question_text' => 'Is your local bus stop within walking distance?',
                'model_answer' => 'Yes, the nearest bus stop is easily within walking distance, just two minutes away.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'What is the best scenic route to take the train in your region?',
                'model_answer' => 'Taking the mountain coastal line offers incredible views of cliffs and the sea.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Would you move to a house if no amenities were within walking distance?',
                'model_answer' => 'No, I prefer neighborhoods where daily necessities are within walking distance.',
                'key_point' => 'Kolokasi within walking distance.',
            ],
            [
                'question_text' => 'Do high school students take the train to school in your city?',
                'model_answer' => 'Many high school students take the train daily as it\'s safe and punctual.',
                'key_point' => 'Kolokasi take the train.',
            ],
            [
                'question_text' => 'Is your workplace within walking distance of a gym?',
                'model_answer' => 'Yes, a modern fitness center is within walking distance right behind my office.',
                'key_point' => 'Kolokasi within walking distance.',
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

        // ── LESSON 2: How Do You Usually Commute to Work or School? ──────────────
        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'How Do You Usually Commute to Work or School?',
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'question_text' => 'How do you usually commute to work or school?',
                'model_answer' => 'I usually commute by motorcycle because it allows me to weave through city traffic.',
                'key_point' => 'Moda & alasan komutasi.',
            ],
            [
                'question_text' => 'How long does your daily commute take on average?',
                'model_answer' => 'My daily commute takes around thirty minutes each way under normal traffic conditions.',
                'key_point' => 'Frasa daily commute / duration.',
            ],
            [
                'question_text' => 'Do you face heavy traffic jams during your morning commute?',
                'model_answer' => 'Yes, heavy traffic jams are common during peak hours, which delays my commute.',
                'key_point' => 'Istilah traffic jams / morning commute.',
            ],
            [
                'question_text' => 'What is the most relaxing way to commute in your opinion?',
                'model_answer' => 'Commuting by train is the most relaxing because I can read or listen to music.',
                'key_point' => 'Deskripsi cara komutasi ideal.',
            ],
            [
                'question_text' => 'Have you ever changed your commute route to avoid traffic?',
                'model_answer' => 'I frequently use navigation apps to find alternative backroads and avoid congestion.',
                'key_point' => 'Strategi rute komutasi.',
            ],
            [
                'question_text' => 'Do you prefer to commute by car or by public transportation?',
                'model_answer' => 'I prefer public transit because I don\'t have to stress about parking or driving.',
                'key_point' => 'Perbandingan car vs public transit.',
            ],
            [
                'question_text' => 'Is your commute longer in the evening than in the morning?',
                'model_answer' => 'Evening commutes are usually longer because everyone leaves office buildings simultaneously.',
                'key_point' => 'Perbandingan evening vs morning commute.',
            ],
            [
                'question_text' => 'What do you usually do to kill time during your commute?',
                'model_answer' => 'I listen to educational podcasts or catch up on language learning lessons during my commute.',
                'key_point' => 'Aktivitas mengisi waktu komutasi.',
            ],
            [
                'question_text' => 'How much money do you spend on your monthly commute?',
                'model_answer' => 'I spend roughly fifty dollars a month on public transit passes for my commute.',
                'key_point' => 'Biaya komutasi bulanan.',
            ],
            [
                'question_text' => 'Is commuting by bicycle safe in your city?',
                'model_answer' => 'It is becoming safer as the city adds dedicated bike lanes along main roads.',
                'key_point' => 'Keamanan komutasi bersepeda.',
            ],
            [
                'question_text' => 'Does bad weather affect your daily commute significantly?',
                'model_answer' => 'Heavy rain causes severe delays and doubles my commute time on flooded roads.',
                'key_point' => 'Pengaruh cuaca pada komutasi.',
            ],
            [
                'question_text' => 'Would you accept a job offer with a two-hour daily commute?',
                'model_answer' => 'No, a two-hour commute would ruin my work-life balance and cause immense fatigue.',
                'key_point' => 'Analisis dampak komutasi panjang.',
            ],
            [
                'question_text' => 'Do you commute with family members or coworkers?',
                'model_answer' => 'I occasionally carpool with a coworker who lives near my residential area.',
                'key_point' => 'Istilah carpool.',
            ],
            [
                'question_text' => 'What is the peak rush hour for commuters in your city?',
                'model_answer' => 'The busiest rush hour is between 7:30 AM and 8:30 AM when schools and offices open.',
                'key_point' => 'Istilah peak rush hour.',
            ],
            [
                'question_text' => 'Has your commute improved over the past few years?',
                'model_answer' => 'Yes, the opening of the new highway route shortened my commute by fifteen minutes.',
                'key_point' => 'Perubahan kualitas komutasi.',
            ],
            [
                'question_text' => 'Do you find commuting stressful?',
                'model_answer' => 'It can be stressful when trains are overcrowded or delayed unexpectedly.',
                'key_point' => 'Efek psikologis komutasi.',
            ],
            [
                'question_text' => 'Is walking a realistic option for your daily commute?',
                'model_answer' => 'Unfortunately no, my workplace is ten kilometers away, which is too far to walk.',
                'key_point' => 'Realita komutasi jalan kaki.',
            ],
            [
                'question_text' => 'How do you keep yourself safe during late-night commutes?',
                'model_answer' => 'I stick to well-lit main streets and share my live location with family.',
                'key_point' => 'Keamanan komutasi malam.',
            ],
            [
                'question_text' => 'What mode of transport do students mostly use for their commute?',
                'model_answer' => 'Most students commute using public buses, light rail, or motorbikes.',
                'key_point' => 'Moda komutasi pelajar.',
            ],
            [
                'question_text' => 'Do you think remote work will eliminate the need to commute?',
                'model_answer' => 'Remote work reduces daily commuting, but hybrid models still require travel on certain days.',
                'key_point' => 'Dampak remote/hybrid work.',
            ],
            [
                'question_text' => 'What is the most environment-friendly way to commute?',
                'model_answer' => 'Riding a bicycle or walking are the greenest ways to commute with zero emissions.',
                'key_point' => 'Komutasi eco-friendly.',
            ],
            [
                'question_text' => 'Do you ever work or answer emails during your commute?',
                'model_answer' => 'If I\'m sitting on a train, I sometimes check urgent emails on my smartphone.',
                'key_point' => 'Produktivitas saat komutasi.',
            ],
            [
                'question_text' => 'Is parking expensive at your workplace after commuting by car?',
                'model_answer' => 'Yes, parking fees in the financial district are quite high, making car commuting pricey.',
                'key_point' => 'Biaya parkir komutasi.',
            ],
            [
                'question_text' => 'How do public transport delays impact your morning commute?',
                'model_answer' => 'Unexpected delays cause me to be late for morning team meetings.',
                'key_point' => 'Dampak keterlambatan komutasi.',
            ],
            [
                'question_text' => 'Do you prefer commuting early to avoid the rush hour?',
                'model_answer' => 'Yes, leaving home thirty minutes early ensures a peaceful and smooth journey.',
                'key_point' => 'Trik mengindari rush hour.',
            ],
            [
                'question_text' => 'What is the main drawback of commuting by motorcycle?',
                'model_answer' => 'The main drawback is exposure to heavy rain, heat, and higher safety risks.',
                'key_point' => 'Kekurangan komutasi motor.',
            ],
            [
                'question_text' => 'Do you wear comfortable shoes during your daily commute?',
                'model_answer' => 'I wear comfortable sneakers for my commute and change into formal shoes at office.',
                'key_point' => 'Kenyamanan saat komutasi.',
            ],
            [
                'question_text' => 'How does city infrastructure affect your commute quality?',
                'model_answer' => 'Well-maintained roads and efficient traffic lights make commuting far smoother.',
                'key_point' => 'Peran city infrastructure.',
            ],
            [
                'question_text' => 'Have you ever tried carpooling for your daily commute?',
                'model_answer' => 'Yes, carpooling reduces fuel costs and makes the long commute more sociable.',
                'key_point' => 'Manfaat carpooling.',
            ],
            [
                'question_text' => 'What is your dream commute scenario?',
                'model_answer' => 'My dream commute is a short ten-minute walk through a green park to my office.',
                'key_point' => 'Komutasi impian.',
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

        // ── LESSON 3: Prepositions and Vehicles ────────────────────
        $lesson3 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 3,
            'title' => 'Prepositions and Vehicles',
            'difficulty' => 'Medium',
        ]);

        $questions3 = [
            [
                'question_text' => 'Do you prefer traveling on a bus or on a train?',
                'model_answer' => 'I prefer traveling on a train because it is smoother and offers more legroom.',
                'key_point' => 'Preposisi on a bus / on a train.',
            ],
            [
                'question_text' => 'Is it comfortable to work on a laptop while in a car?',
                'model_answer' => 'No, typing while in a car makes me feel motion sick.',
                'key_point' => 'Preposisi in a car.',
            ],
            [
                'question_text' => 'Do you wear a helmet when riding on a bike?',
                'model_answer' => 'Always. Wearing a helmet on a bike is essential for safety.',
                'key_point' => 'Preposisi on a bike.',
            ],
            [
                'question_text' => 'How do you feel when you get on a crowded bus during rush hour?',
                'model_answer' => 'Getting on a packed bus can feel suffocating, so I try to avoid peak times.',
                'key_point' => 'Phrasal verb get on a bus.',
            ],
            [
                'question_text' => 'Did you remember to lock your belongings before getting out of the taxi?',
                'model_answer' => 'Yes, I always double-check my phone and bag before getting out of a taxi.',
                'key_point' => 'Phrasal verb get out of a taxi.',
            ],
            [
                'question_text' => 'Do you like listening to music when you are in a car?',
                'model_answer' => 'Yes, playing my favorite playlist while in a car makes long drives enjoyable.',
                'key_point' => 'Preposisi in a car.',
            ],
            [
                'question_text' => 'Is Wi-Fi usually available when traveling on an airplane?',
                'model_answer' => 'Many modern airlines offer Wi-Fi service when you are on an airplane.',
                'key_point' => 'Preposisi on an airplane.',
            ],
            [
                'question_text' => 'What should you do before getting off the train?',
                'model_answer' => 'Gather all personal items and move toward the exit doors before getting off the train.',
                'key_point' => 'Phrasal verb get off the train.',
            ],
            [
                'question_text' => 'Do you prefer traveling by plane or by train for long distances?',
                'model_answer' => 'I prefer traveling by plane for long distances to save time.',
                'key_point' => 'Preposisi by plane / by train.',
            ],
            [
                'question_text' => 'How easy is it to get in a taxi during heavy rain?',
                'model_answer' => 'It is very difficult to get in a taxi during downpours due to high demand.',
                'key_point' => 'Phrasal verb get in a taxi.',
            ],
            [
                'question_text' => 'Do you feel safe riding on a motorcycle in heavy traffic?',
                'model_answer' => 'Riding on a motorcycle requires high alertness, especially when navigating heavy traffic.',
                'key_point' => 'Preposisi on a motorcycle.',
            ],
            [
                'question_text' => 'What rules must passengers follow while on a ferry?',
                'model_answer' => 'Passengers on a ferry must locate life jackets and remain in designated seating areas.',
                'key_point' => 'Preposisi on a ferry.',
            ],
            [
                'question_text' => 'Do you get motion sickness when sitting in the back seat of a car?',
                'model_answer' => 'Yes, sitting in the back seat of a car often makes me feel dizzy.',
                'key_point' => 'Preposisi in the back seat of a car.',
            ],
            [
                'question_text' => 'Is eating permitted while on a city bus?',
                'model_answer' => 'No, eating and drinking are strictly prohibited while on a city bus.',
                'key_point' => 'Preposisi on a city bus.',
            ],
            [
                'question_text' => 'What do you do when you get off a subway station?',
                'model_answer' => 'After getting off the subway, I follow signposts to reach the correct street exit.',
                'key_point' => 'Phrasal verb get off the subway.',
            ],
            [
                'question_text' => 'Is it difficult to get into a small sports car?',
                'model_answer' => 'Yes, sports cars sit very low to the ground, making getting in a bit awkward.',
                'key_point' => 'Phrasal verb get into a car.',
            ],
            [
                'question_text' => 'Do you enjoy sleeping while on a long-distance flight?',
                'model_answer' => 'I try to sleep while on a flight, especially during overnight journeys.',
                'key_point' => 'Preposisi on a flight.',
            ],
            [
                'question_text' => 'Why should you hold the handrail when standing on a moving bus?',
                'model_answer' => 'To maintain your balance and prevent falling when standing on a moving bus.',
                'key_point' => 'Preposisi on a moving bus.',
            ],
            [
                'question_text' => 'How quickly can passengers get off an airplane after landing?',
                'model_answer' => 'It usually takes about fifteen to twenty minutes for everyone to get off the airplane.',
                'key_point' => 'Phrasal verb get off the airplane.',
            ],
            [
                'question_text' => 'Do you talk to strangers when sitting next to them in a taxi?',
                'model_answer' => 'I usually prefer quiet, but I occasionally exchange polite small talk in a shared taxi.',
                'key_point' => 'Preposisi in a taxi.',
            ],
            [
                'question_text' => 'Is it safe to ride on an electric scooter without a helmet?',
                'model_answer' => 'No, riding on an electric scooter without protection poses severe risk of head injury.',
                'key_point' => 'Preposisi on an electric scooter.',
            ],
            [
                'question_text' => 'What do you do if you leave an item inside a ride-hailing car?',
                'model_answer' => 'I immediately contact driver support via the app to report lost items in the car.',
                'key_point' => 'Preposisi in the car.',
            ],
            [
                'question_text' => 'Do you prefer standing or sitting when on a tram?',
                'model_answer' => 'I prefer sitting when on a tram, but I readily yield my seat to elderly passengers.',
                'key_point' => 'Preposisi on a tram.',
            ],
            [
                'question_text' => 'How do you step safely when getting off a boat onto the dock?',
                'model_answer' => 'Wait until the vessel is securely tied before getting off the boat.',
                'key_point' => 'Phrasal verb get off the boat.',
            ],
            [
                'question_text' => 'Is it mandatory to wear seatbelts when sitting in a car?',
                'model_answer' => 'Yes, wearing seatbelts is mandatory for all passengers sitting in a car.',
                'key_point' => 'Preposisi in a car.',
            ],
            [
                'question_text' => 'Do you check your mobile phone while riding on a bicycle?',
                'model_answer' => 'No, using a phone while on a bicycle is extremely dangerous and distracts your focus.',
                'key_point' => 'Preposisi on a bicycle.',
            ],
            [
                'question_text' => 'What is the protocol when emergency vehicles pass while you are in a car?',
                'model_answer' => 'When in a car, you must pull over to the side to give way to emergency vehicles.',
                'key_point' => 'Preposisi in a car.',
            ],
            [
                'question_text' => 'Is carrying heavy luggage easy when getting on a high-speed train?',
                'model_answer' => 'Yes, high-speed train platforms are level with doors, making getting on very easy.',
                'key_point' => 'Phrasal verb get on a train.',
            ],
            [
                'question_text' => 'Do you prefer driving by yourself or being a passenger in a car?',
                'model_answer' => 'I prefer being a passenger in a car because I can relax and enjoy the scenery.',
                'key_point' => 'Preposisi in a car.',
            ],
            [
                'question_text' => 'What happens if you swipe your travel card incorrectly when getting on a bus?',
                'model_answer' => 'The card reader will beep red, and you must re-scan before getting on the bus.',
                'key_point' => 'Context getting on a bus.',
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

        $this->command->info('Transportation Unit (Unit 5) seeded successfully!');
        $this->command->info('Lesson 1: ' . count($questions1) . ' questions');
        $this->command->info('Lesson 2: ' . count($questions2) . ' questions');
        $this->command->info('Lesson 3: ' . count($questions3) . ' questions');
    }
}
