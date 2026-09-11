<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Question;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class AccommodationUnitSeeder extends Seeder
{
    public function run(): void
    {
        // ── Unit 4: Accommodation (Part 1) ──────────────────────
        $unit = Unit::create([
            'unit_number' => 4,
            'title' => 'Accommodation',
            'part' => 1,
            'outcome' => 'Menggunakan kolokasi posisi/alternatif, mendeskripsikan jenis bangunan, dan frasa transaksi properti.',
        ]);

        // ── LESSON 1: Collocations: "Fourth Floor", "Instead Of" ─────────
        $lesson1 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 1,
            'title' => 'Collocations: "Fourth Floor", "Instead Of"',
            'difficulty' => 'Medium',
        ]);

        $questions1 = [
            [
                'question_text' => 'Do you live on the fourth floor or higher up?',
                'model_answer' => 'I live on the fourth floor of a modern apartment building, which gives me a nice view of the city.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Would you prefer to rent an apartment instead of buying a house?',
                'model_answer' => 'Currently, I prefer renting an apartment instead of buying a house because it offers more flexibility.',
                'key_point' => 'Penggunaan frasa instead of.',
            ],
            [
                'question_text' => 'Is there an elevator to get to the fourth floor?',
                'model_answer' => 'Yes, thankfully there is a high-speed elevator, so going up to the fourth floor is very convenient.',
                'key_point' => 'Frasa fourth floor / elevator.',
            ],
            [
                'question_text' => 'Why did you choose to live in a flat instead of a traditional house?',
                'model_answer' => 'I chose a flat instead of a house because it requires less maintenance and offers better security.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'How long does it take to walk up the stairs to the fourth floor?',
                'model_answer' => 'Walking up to the fourth floor takes about two minutes, which is a great daily workout.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Do you prefer taking the stairs instead of using the lift?',
                'model_answer' => 'I usually take the stairs instead of using the lift when I\'m not carrying heavy groceries.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Is your apartment unit on the fourth floor noisy?',
                'model_answer' => 'No, the fourth floor is high enough to block out most of the street-level traffic noise.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Would you consider buying secondhand furniture instead of new items?',
                'model_answer' => 'Yes, I often buy vintage furniture instead of new items to save money and add character to my home.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Does the fourth floor of your building have a balcony?',
                'model_answer' => 'Yes, every apartment on the fourth floor comes with a spacious balcony overlooking the courtyard.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Why do some people prefer living in the suburbs instead of the city center?',
                'model_answer' => 'People choose the suburbs instead of the city center to enjoy fresh air, larger gardens, and peace.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Is the rent higher on the fourth floor compared to the ground floor?',
                'model_answer' => 'In my building, units on the fourth floor are slightly more expensive due to better natural light.',
                'key_point' => 'Perbandingan fourth floor vs ground floor.',
            ],
            [
                'question_text' => 'Do you cook at home instead of eating out at restaurants?',
                'model_answer' => 'I try to cook at home instead of eating out because it\'s healthier and more economical.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'What is located on the fourth floor of your apartment complex?',
                'model_answer' => 'The fourth floor houses residential units, while the rooftop gym is right above us.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Would you prefer living on the ground floor instead of an upper floor?',
                'model_answer' => 'I prefer an upper floor like the fourth floor instead of the ground floor for better privacy.',
                'key_point' => 'Penggunaan fourth floor / instead of.',
            ],
            [
                'question_text' => 'Is it difficult to move heavy furniture up to the fourth floor?',
                'model_answer' => 'It can be challenging if the items don\'t fit into the elevator to the fourth floor.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Do you use natural light during the day instead of turning on lamps?',
                'model_answer' => 'My apartment gets plenty of sunlight, so I open the blinds instead of turning on electric lights.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Are emergency exits clearly marked on the fourth floor?',
                'model_answer' => 'Yes, emergency stairwells are clearly illuminated on the fourth floor and every other level.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Why did you decide to paint the walls white instead of colorful shades?',
                'model_answer' => 'I chose white instead of vibrant colors to make my small living room feel brighter and larger.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'How is the water pressure on the fourth floor?',
                'model_answer' => 'The water pressure on the fourth floor is excellent thanks to our building\'s rooftop water pumps.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Would you like to have a terrace instead of a small balcony?',
                'model_answer' => 'I would definitely love a spacious terrace instead of a small balcony so I could host outdoor dinners.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Do many elderly people live on the fourth floor of your building?',
                'model_answer' => 'Not many; most seniors prefer ground floor units instead of living on upper levels.',
                'key_point' => 'Penggunaan fourth floor / instead of.',
            ],
            [
                'question_text' => 'Do you prefer wooden flooring instead of carpets?',
                'model_answer' => 'I prefer wooden flooring instead of carpets because wood is much easier to clean and sweep.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Is there good cell phone reception on the fourth floor?',
                'model_answer' => 'Yes, mobile signal reception on the fourth floor is very strong and consistent.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Why choose a long-term lease instead of a short-term contract?',
                'model_answer' => 'Long-term leases offer rental price stability instead of dealing with annual price increases.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Is there a nice breeze when you open windows on the fourth floor?',
                'model_answer' => 'Yes, being on the fourth floor allows for great cross-ventilation when windows are open.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Do you use a ceiling fan instead of air conditioning?',
                'model_answer' => 'During mild weather, I use a ceiling fan instead of air conditioning to save electricity.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'Do you have good neighbors on the fourth floor?',
                'model_answer' => 'Yes, everyone living on the fourth floor is friendly, quiet, and respectful.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Would you buy a studio apartment instead of a two-bedroom unit?',
                'model_answer' => 'As a single professional, I\'d buy a studio instead of a two-bedroom unit to fit my budget.',
                'key_point' => 'Penggunaan instead of.',
            ],
            [
                'question_text' => 'What is the view like from the fourth floor window?',
                'model_answer' => 'The window on the fourth floor looks out over a green city park and distant hills.',
                'key_point' => 'Kolokasi fourth floor.',
            ],
            [
                'question_text' => 'Why rent a furnished place instead of an unfurnished one?',
                'model_answer' => 'Renting a furnished flat is convenient because you can move in right away instead of buying furniture.',
                'key_point' => 'Penggunaan instead of.',
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

        // ── LESSON 2: What Kind of Building Do You Live In? ──────────────
        $lesson2 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 2,
            'title' => 'What Kind of Building Do You Live In?',
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'question_text' => 'What kind of building do you live in?',
                'model_answer' => 'I live in a modern high-rise apartment building located in the city center.',
                'key_point' => 'Deskripsi jenis bangunan (high-rise apartment).',
            ],
            [
                'question_text' => 'Do you live in a single-family detached house or an apartment?',
                'model_answer' => 'I live in a two-story detached house with a private garden in the suburbs.',
                'key_point' => 'Istilah detached house.',
            ],
            [
                'question_text' => 'What is the exterior design of your building like?',
                'model_answer' => 'The building features a sleek glass facade with modern concrete architecture.',
                'key_point' => 'Deskripsi exterior design.',
            ],
            [
                'question_text' => 'Is your residence part of a gated residential complex?',
                'model_answer' => 'Yes, I live in a gated residential community with 24-hour security guards.',
                'key_point' => 'Istilah gated residential complex.',
            ],
            [
                'question_text' => 'How old is the building you currently live in?',
                'model_answer' => 'It\'s a relatively new building, constructed about five years ago.',
                'key_point' => 'Usia bangunan.',
            ],
            [
                'question_text' => 'Do you live in a townhouse or a high-rise condominium?',
                'model_answer' => 'I live in a three-story townhouse that shares side walls with neighboring houses.',
                'key_point' => 'Istilah townhouse.',
            ],
            [
                'question_text' => 'What facilities are available inside your apartment building?',
                'model_answer' => 'Our building provides great facilities, including an underground parking garage, gym, and swimming pool.',
                'key_point' => 'Fasilitas internal gedung.',
            ],
            [
                'question_text' => 'Is your house built from brick, concrete, or wood?',
                'model_answer' => 'The house is constructed primarily from reinforced concrete and red bricks.',
                'key_point' => 'Material bangunan.',
            ],
            [
                'question_text' => 'Do you live in a low-rise or high-rise building?',
                'model_answer' => 'I reside in a low-rise residential building that has only four floors.',
                'key_point' => 'Membedakan low-rise vs high-rise.',
            ],
            [
                'question_text' => 'What do you like most about the building you live in?',
                'model_answer' => 'What I like most is the large rooftop garden that offers amazing views of the skyline.',
                'key_point' => 'Fitur favorit bangunan.',
            ],
            [
                'question_text' => 'Is your building located on a busy main street or a quiet lane?',
                'model_answer' => 'It is tucked away on a quiet residential lane, far from main road traffic noise.',
                'key_point' => 'Lokasi bangunan.',
            ],
            [
                'question_text' => 'Are there commercial shops on the ground floor of your building?',
                'model_answer' => 'Yes, the ground floor houses a convenience store, a coffee shop, and a laundromat.',
                'key_point' => 'Bangunan mixed-use.',
            ],
            [
                'question_text' => 'Is the building you live in energy-efficient?',
                'model_answer' => 'Yes, it\'s an eco-friendly building equipped with solar panels and double-glazed windows.',
                'key_point' => 'Frasa energy-efficient / eco-friendly building.',
            ],
            [
                'question_text' => 'How many units or apartments are there in your building?',
                'model_answer' => 'It\'s a small boutique building with only twenty individual residential units.',
                'key_point' => 'Jumlah unit tempat tinggal.',
            ],
            [
                'question_text' => 'Do you live in a traditional style building or a modern one?',
                'model_answer' => 'I live in a modern building designed with minimalist interiors and open layouts.',
                'key_point' => 'Gaya arsitektur.',
            ],
            [
                'question_text' => 'Is there a basement or underground garage in your building?',
                'model_answer' => 'Yes, we have a two-level underground parking garage for residents\' vehicles.',
                'key_point' => 'Istilah underground garage.',
            ],
            [
                'question_text' => 'What color is the exterior wall of your house or building?',
                'model_answer' => 'The exterior is painted in a neutral beige shade with dark gray window frames.',
                'key_point' => 'Warna eksterior.',
            ],
            [
                'question_text' => 'Does your building have good fire safety systems?',
                'model_answer' => 'Yes, every floor has smoke detectors, fire extinguishers, and clear emergency stairs.',
                'key_point' => 'Fitur fire safety systems.',
            ],
            [
                'question_text' => 'Do you live in a semi-detached house?',
                'model_answer' => 'Yes, my home is a semi-detached house, so we share one common wall with our neighbor.',
                'key_point' => 'Istilah semi-detached house.',
            ],
            [
                'question_text' => 'Is your building managed by a professional property management firm?',
                'model_answer' => 'Yes, a property management team handles daily maintenance and cleaning.',
                'key_point' => 'Frasa property management team.',
            ],
            [
                'question_text' => 'Does the building you live in have a balcony in every unit?',
                'model_answer' => 'Yes, each unit features a private outdoor balcony.',
                'key_point' => 'Deskripsi fitur balkon.',
            ],
            [
                'question_text' => 'What kind of roof does your house or building have?',
                'model_answer' => 'It has a flat concrete roof terrace where residents can dry laundry or relax.',
                'key_point' => 'Jenis atap (flat concrete roof).',
            ],
            [
                'question_text' => 'Is soundproofing good in the building you live in?',
                'model_answer' => 'The thick concrete walls provide excellent soundproofing between adjacent apartments.',
                'key_point' => 'Istilah soundproofing.',
            ],
            [
                'question_text' => 'Have there been any major renovations to your building recently?',
                'model_answer' => 'The building management recently repainted the exterior facade and upgraded the lobby.',
                'key_point' => 'Aktivitas renovations.',
            ],
            [
                'question_text' => 'Do you live in a studio apartment or a multi-room flat?',
                'model_answer' => 'I live in a two-bedroom flat that has a separate living room and kitchen area.',
                'key_point' => 'Membedakan studio vs multi-room.',
            ],
            [
                'question_text' => 'Is your residential building close to public transit stations?',
                'model_answer' => 'It\'s exceptionally convenient, located just a five-minute walk from the metro station.',
                'key_point' => 'Akses transportasi.',
            ],
            [
                'question_text' => 'Do you live in a historic heritage building?',
                'model_answer' => 'No, but I admire heritage buildings with classic colonial architecture.',
                'key_point' => 'Istilah historic heritage building.',
            ],
            [
                'question_text' => 'Is security tight at the entrance of your building?',
                'model_answer' => 'Yes, residents must scan keycards to access the main entrance gate and elevators.',
                'key_point' => 'Sistem keamanan keycard access.',
            ],
            [
                'question_text' => 'What kind of view do you get from your living room window?',
                'model_answer' => 'My living room window offers a pleasant view of a tree-lined street.',
                'key_point' => 'Deskripsi pemandangan jendela.',
            ],
            [
                'question_text' => 'Would you like to move to a different type of building in the future?',
                'model_answer' => 'In the future, I hope to move into a single-family house with a private backyard.',
                'key_point' => 'Harapan rumah masa depan.',
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

        // ── LESSON 3: Collocations with Buy and Rent ────────────────────
        $lesson3 = Lesson::create([
            'unit_id' => $unit->id,
            'lesson_number' => 3,
            'title' => 'Collocations with Buy and Rent',
            'difficulty' => 'Medium',
        ]);

        $questions3 = [
            [
                'question_text' => 'Do you currently rent your apartment or own your home?',
                'model_answer' => 'I currently rent an apartment, but I am saving money to buy my own house eventually.',
                'key_point' => 'Kolokasi rent an apartment / buy a house.',
            ],
            [
                'question_text' => 'Is it very expensive to buy a property in your city?',
                'model_answer' => 'Yes, real estate prices have skyrocketed, making it difficult for young people to buy property.',
                'key_point' => 'Kolokasi buy property / real estate prices.',
            ],
            [
                'question_text' => 'How often do you have to pay rent to your landlord?',
                'model_answer' => 'I pay rent on a monthly basis, usually on the first day of every month.',
                'key_point' => 'Kolokasi pay rent.',
            ],
            [
                'question_text' => 'Is the rental price affordable in your neighborhood?',
                'model_answer' => 'The rental price is quite reasonable compared to the city center.',
                'key_point' => 'Kolokasi rental price.',
            ],
            [
                'question_text' => 'Would you prefer to rent a house long-term or short-term?',
                'model_answer' => 'I prefer to sign a long-term rental agreement because it guarantees price stability.',
                'key_point' => 'Kolokasi rental agreement / long-term.',
            ],
            [
                'question_text' => 'What should a first-time home buyer consider before purchasing?',
                'model_answer' => 'A first-time home buyer should check the location, mortgage rates, and property condition.',
                'key_point' => 'Kolokasi home buyer.',
            ],
            [
                'question_text' => 'Is it better to rent a property or take out a mortgage to buy one?',
                'model_answer' => 'Taking out a mortgage to buy property builds long-term equity, whereas rent is an expense.',
                'key_point' => 'Kolokasi rent a property / buy property.',
            ],
            [
                'question_text' => 'Have you ever negotiated the monthly rent with a landlord?',
                'model_answer' => 'Yes, I managed to negotiate a lower monthly rent by agreeing to sign a two-year lease.',
                'key_point' => 'Kolokasi monthly rent.',
            ],
            [
                'question_text' => 'Why do many people choose to rent an apartment near their workplace?',
                'model_answer' => 'People rent near work to minimize daily commute time and avoid traffic stress.',
                'key_point' => 'Kolokasi rent an apartment.',
            ],
            [
                'question_text' => 'What is included in your monthly rent payment?',
                'model_answer' => 'My monthly rent includes building maintenance fees and access to the gym, but utilities are separate.',
                'key_point' => 'Kolokasi monthly rent payment.',
            ],
            [
                'question_text' => 'Are you planning to buy household furniture soon?',
                'model_answer' => 'I plan to buy a comfortable sofa and a dining table once I move into my new flat.',
                'key_point' => 'Frasa buy household furniture.',
            ],
            [
                'question_text' => 'What happens if a tenant fails to pay rent on time?',
                'model_answer' => 'Failing to pay rent on time may result in late fees or eventual eviction by the landlord.',
                'key_point' => 'Kolokasi pay rent on time.',
            ],
            [
                'question_text' => 'Is it easy to find an apartment for rent in your town?',
                'model_answer' => 'Yes, there are numerous apartments for rent listed on real estate websites.',
                'key_point' => 'Kolokasi apartments for rent.',
            ],
            [
                'question_text' => 'Why do some people decide to buy land instead of a built house?',
                'model_answer' => 'Some people buy land so they can custom-design their dream home from scratch.',
                'key_point' => 'Kolokasi buy land.',
            ],
            [
                'question_text' => 'Does your landlord increase the rent every year?',
                'model_answer' => 'Fortunately, my landlord only increases the rent slightly every two years.',
                'key_point' => 'Kolokasi increase the rent.',
            ],
            [
                'question_text' => 'Is it cheaper to rent a room in a shared house?',
                'model_answer' => 'Yes, renting a room in a shared house reduces costs because you split utilities with flatmates.',
                'key_point' => 'Kolokasi rent a room.',
            ],
            [
                'question_text' => 'What documents do you need to buy a house with a bank loan?',
                'model_answer' => 'You need proof of income, tax returns, credit history, and personal identification to buy a house.',
                'key_point' => 'Kolokasi buy a house.',
            ],
            [
                'question_text' => 'Is it common to pay a security deposit when you rent a place?',
                'model_answer' => 'Yes, landlords usually require a security deposit equivalent to one month\'s rent upfront.',
                'key_point' => 'Kolokasi security deposit / month\'s rent.',
            ],
            [
                'question_text' => 'Would you ever buy a fixer-upper house that needs renovation?',
                'model_answer' => 'I wouldn\'t mind buying a fixer-upper if the price is low and the structure is solid.',
                'key_point' => 'Frasa buy a fixer-upper.',
            ],
            [
                'question_text' => 'Why is the rent demand so high in university cities?',
                'model_answer' => 'Rent demand spikes because thousands of incoming students need temporary housing every year.',
                'key_point' => 'Istilah rent demand.',
            ],
            [
                'question_text' => 'Is it wise to buy investment property for rental income?',
                'model_answer' => 'Yes, buying investment property provides steady monthly rental income and long-term appreciation.',
                'key_point' => 'Kolokasi buy investment property / rental income.',
            ],
            [
                'question_text' => 'What are the hidden costs when you buy a house?',
                'model_answer' => 'Hidden costs include property taxes, legal fees, home insurance, and closing costs.',
                'key_point' => 'Kolokasi buy a house.',
            ],
            [
                'question_text' => 'Do you prefer to rent from a private landlord or a leasing company?',
                'model_answer' => 'I prefer renting from a private landlord because they tend to be more flexible.',
                'key_point' => 'Kolokasi rent from a landlord.',
            ],
            [
                'question_text' => 'How long does it take to save enough money to buy a home?',
                'model_answer' => 'It typically takes five to ten years of disciplined saving to afford a down payment to buy a home.',
                'key_point' => 'Kolokasi buy a home.',
            ],
            [
                'question_text' => 'Can you sublet your apartment if you rent it?',
                'model_answer' => 'No, my tenancy contract strictly prohibits subletting the rented apartment to third parties.',
                'key_point' => 'Istilah rented apartment.',
            ],
            [
                'question_text' => 'What is the average rent for a one-bedroom apartment in your city?',
                'model_answer' => 'The average rent for a one-bedroom flat is around five hundred dollars a month.',
                'key_point' => 'Kolokasi average rent.',
            ],
            [
                'question_text' => 'Should young adults buy a car or save to buy a house first?',
                'model_answer' => 'They should prioritize saving to buy a house because real estate appreciates, while cars depreciate.',
                'key_point' => 'Perbandingan buy a car vs buy a house.',
            ],
            [
                'question_text' => 'What are your rights as a consumer when you rent accommodation?',
                'model_answer' => 'Tenants have the right to a safe, habitable living space and timely maintenance repairs.',
                'key_point' => 'Context rent accommodation.',
            ],
            [
                'question_text' => 'Do you use real estate agents when looking to buy property?',
                'model_answer' => 'Yes, hiring a reputable agent makes finding and negotiating to buy property much smoother.',
                'key_point' => 'Kolokasi buy property.',
            ],
            [
                'question_text' => 'What is your ultimate dream home if money wasn\'t an issue to buy it?',
                'model_answer' => 'I would buy a modern beachfront villa with floor-to-ceiling windows and a private pool.',
                'key_point' => 'Frasa buy a dream home.',
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

        $this->command->info('Accommodation Unit (Unit 4) seeded successfully!');
        $this->command->info('Lesson 1: ' . count($questions1) . ' questions');
        $this->command->info('Lesson 2: ' . count($questions2) . ' questions');
        $this->command->info('Lesson 3: ' . count($questions3) . ' questions');
    }
}