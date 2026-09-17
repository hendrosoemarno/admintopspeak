<?php

namespace Database\Seeders;

use App\Enums\CefrLevel;
use App\Enums\SubscriptionStatus;
use App\Models\AppConfiguration;
use App\Models\ConversationLog;
use App\Models\GrammarRule;
use App\Models\QuestionBank;
use App\Models\ThematicTopic;
use App\Models\User;
use App\Models\UserLevelHistory;
use App\Models\UserSubscription;
use App\Models\VocabularyBank;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    public function run(): void
    {
        AppConfiguration::updateOrCreate(
            ['id' => 1],
            [
                'latest_app_version' => '1.2.0',
                'min_required_version' => '1.2.0',
                'is_force_update' => false,
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.topspeak.app',
                'update_message' => 'Versi baru TopSpeak telah tersedia dengan pembaruan fitur dan perbaikan keamanan.',
            ]
        );

        $this->seedGrammarRules();
        $this->seedVocabulary();
        $this->seedQuestionBanks();
        $this->seedThematicTopics();
        $this->seedThematicQuestions();
        $this->seedUsers();
        $this->seedAdminUser();
        $this->seedConversationSessions();

        $this->call(FillerWordSeeder::class);
        $this->call(IeltsCurriculumSeeder::class);
    }

    private function seedGrammarRules(): void
    {
        $rules = [
            ['SVA_01', 'Subject-Verb Agreement', 'A2', '/\\b(he|she|it)\\s+{v1}\\b/i', 'Subject he/she/it harus diikuti verb berakhiran -s/-es (irregular V1 = bentuk dasar, harus goes/has/does).'],
            ['SVA_02', 'Subject-Verb Agreement', 'A2', '/\\b(they|we|you)\\s+(goes|has|does)\\b/i', 'Subject plural tidak diikuti verb tunggal.'],
            ['SVA_03', 'Subject-Verb Agreement', 'B1', '/\\b(everyone|nobody|somebody)\\s+(are|were)\\b/i', 'Kata ganti tak tentu (everyone, nobody) memakai verb tunggal.'],
            ['TENSE_01', 'Past Tense', 'A2', '/\\byesterday\\s+.+\\b{v1}\\b/i', 'Pada konteks lampau irregular verb (V1) harus berubah bentuk (went, bought, saw, ate).'],
            ['TENSE_02', 'Past Tense', 'B1', '/\\b(have|has)\\s+{v2}\\b/i', 'Setelah have/has gunakan past participle (V3), bukan past simple (V2).'],
            ['PREP_01', 'Preposition Error', 'B1', '/\\bdepend\\s+at\\b/i', 'Kata "depend" harus diikuti preposisi "on", bukan "at".'],
            ['PREP_02', 'Preposition Error', 'B1', '/\\bdepend\\s+to\\b/i', 'Kata "depend" harus diikuti preposisi "on", bukan "to".'],
            ['PREP_03', 'Preposition Error', 'A2', '/\\binterested\\s+for\\b/i', '"Interested" selalu diikuti "in", bukan "for".'],
            ['ART_01', 'Article Error', 'A2', '/\\ba\\s+(apple|orange|hour|umbrella)\\b/i', 'Kata benda berawalan bunyi vokal memakai "an" bukan "a".'],
            ['ART_02', 'Article Error', 'A2', '/\\ban\\s+(book|car|dog)\\b/i', 'Kata benda berawalan bunyi konsonan memakai "a" bukan "an".'],
            ['PRON_01', 'Pronoun Error', 'B1', '/\\b{subject_pronoun_no_i}\\s+am\\b/i', 'Kombinasi subjek-pronoun dan to be tidak cocok (am hanya untuk "i").'],
            ['MODAL_01', 'Modal Verb', 'B1', '/\\b(can|must|should)\\s+to\\s+\\w+/i', 'Setelah modal verb gunakan verb dasar tanpa "to".'],
            ['ADJ_01', 'Adjective Form', 'B2', '/\\bmore\\s+(easy|happy|good|bad|far)\\b/i', 'Adjective pendek umumnya memakai bentuk banding -er (easier, happier).'],
            ['COUNT_01', 'Countable/Uncountable', 'B1', '/\\bmuch\\s+{plural_noun}\\b/i', '"Much" untuk uncountable; gunakan "many" untuk countable plural irregular (children, people, dst).'],
            ['GER_01', 'Gerund/Infinitive', 'B2', '/\\benjoy\\s+to\\s+\\w+/i', 'Setelah "enjoy" gunakan gerund (-ing), bukan to-infinitive.'],
        ];

        foreach ($rules as [$code, $category, $level, $pattern, $desc]) {
            GrammarRule::updateOrCreate(
                ['rule_code' => $code],
                [
                    'category' => $category,
                    'cefr_level' => $level,
                    'regex_pattern' => $pattern,
                    'description' => $desc,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedVocabulary(): void
    {
        $words = [
            // Introduction
            ['hello', 'interjection', 'A1', 'Introduction'], ['name', 'noun', 'A1', 'Introduction'],
            ['greet', 'verb', 'A1', 'Introduction'], ['country', 'noun', 'A1', 'Introduction'],
            ['city', 'noun', 'A1', 'Introduction'], ['family', 'noun', 'A1', 'Introduction'],
            // Hobbies & Daily Routine
            ['hobby', 'noun', 'A1', 'Hobbies'], ['music', 'noun', 'A1', 'Hobbies'],
            ['movie', 'noun', 'A1', 'Hobbies'], ['weekend', 'noun', 'A2', 'Daily Routine'],
            ['relax', 'verb', 'A2', 'Daily Routine'], ['wake up', 'phrase', 'A1', 'Daily Routine'],
            ['sleep', 'verb', 'A1', 'Daily Routine'], ['morning', 'noun', 'A1', 'Daily Routine'],
            // Shopping
            ['market', 'noun', 'A1', 'Shopping'], ['buy', 'verb', 'A1', 'Shopping'],
            ['price', 'noun', 'A2', 'Shopping'], ['cheap', 'adjective', 'A2', 'Shopping'],
            ['expensive', 'adjective', 'A2', 'Shopping'], ['yesterday', 'adverb', 'A2', 'Shopping'],
            // Food & Restaurant
            ['menu', 'noun', 'A1', 'Restaurant'], ['order', 'verb', 'A1', 'Restaurant'],
            ['drink', 'noun', 'A1', 'Restaurant'], ['bill', 'noun', 'A1', 'Restaurant'],
            ['delicious', 'adjective', 'A2', 'Restaurant'], ['breakfast', 'noun', 'A1', 'Restaurant'],
            // Travel
            ['travel', 'verb', 'A2', 'Travel'], ['airport', 'noun', 'A2', 'Travel'],
            ['ticket', 'noun', 'A2', 'Travel'], ['flight', 'noun', 'B1', 'Travel'],
            ['journey', 'noun', 'B1', 'Travel'], ['destination', 'noun', 'B1', 'Travel'],
            // Job Interview
            ['experience', 'noun', 'B1', 'Job Interview'], ['skill', 'noun', 'B1', 'Job Interview'],
            ['strength', 'noun', 'B1', 'Job Interview'], ['career', 'noun', 'B1', 'Job Interview'],
            ['achievement', 'noun', 'B2', 'Job Interview'], ['candidate', 'noun', 'B2', 'Job Interview'],
            // Education
            ['study', 'verb', 'A1', 'Education'], ['university', 'noun', 'A2', 'Education'],
            ['exam', 'noun', 'A2', 'Education'], ['graduate', 'verb', 'B1', 'Education'],
            ['research', 'noun', 'B2', 'Education'], ['knowledge', 'noun', 'B1', 'Education'],
            // Health
            ['doctor', 'noun', 'A1', 'Health'], ['hospital', 'noun', 'A1', 'Health'],
            ['exercise', 'noun', 'A2', 'Health'], ['healthy', 'adjective', 'A2', 'Health'],
            // Technology
            ['computer', 'noun', 'A1', 'Technology'], ['internet', 'noun', 'A2', 'Technology'],
            ['technology', 'noun', 'B1', 'Technology'], ['digital', 'adjective', 'B2', 'Technology'],
            // Abstract / Fluency
            ['imagine', 'verb', 'B1', 'Abstract'], ['believe', 'verb', 'A2', 'Abstract'],
            ['opinion', 'noun', 'B1', 'Abstract'], ['suggest', 'verb', 'B2', 'Abstract'],
            ['alternative', 'adjective', 'B2', 'Abstract'], ['perspective', 'noun', 'C1', 'Abstract'],
            // Adverbs of Frequency
            ['always', 'adverb', 'A1', 'Adverbs of Frequency'], ['usually', 'adverb', 'A1', 'Adverbs of Frequency'],
            ['often', 'adverb', 'A1', 'Adverbs of Frequency'], ['sometimes', 'adverb', 'A1', 'Adverbs of Frequency'],
            ['rarely', 'adverb', 'B1', 'Adverbs of Frequency'], ['never', 'adverb', 'A1', 'Adverbs of Frequency'],
            ['seldom', 'adverb', 'B2', 'Adverbs of Frequency'], ['frequently', 'adverb', 'B1', 'Adverbs of Frequency'],
                        ['occasionally', 'adverb', 'B2', 'Adverbs of Frequency'],
            // ===== Modal Verbs =====
            ['can', 'modal', 'A1', 'Grammar Support'], ['could', 'modal', 'A1', 'Grammar Support'],
            ['may', 'modal', 'B1', 'Grammar Support'], ['might', 'modal', 'B1', 'Grammar Support'],
            ['must', 'modal', 'A2', 'Grammar Support'], ['shall', 'modal', 'B1', 'Grammar Support'],
            ['should', 'modal', 'A1', 'Grammar Support'], ['will', 'modal', 'A1', 'Grammar Support'],
            ['would', 'modal', 'A1', 'Grammar Support'],
            // ===== Auxiliary Verbs (bentuk be/do/have; be, do, have sengaja tetap verb) =====
            ['am', 'auxiliary', 'A1', 'Grammar Support'], ['is', 'auxiliary', 'A1', 'Grammar Support'],
            ['are', 'auxiliary', 'A1', 'Grammar Support'], ['was', 'auxiliary', 'A1', 'Grammar Support'],
            ['were', 'auxiliary', 'A1', 'Grammar Support'], ['been', 'auxiliary', 'A2', 'Grammar Support'],
            ['being', 'auxiliary', 'B1', 'Grammar Support'], ['does', 'auxiliary', 'A1', 'Grammar Support'],
            ['did', 'auxiliary', 'A1', 'Grammar Support'], ['has', 'auxiliary', 'A2', 'Grammar Support'],
            ['had', 'auxiliary', 'A2', 'Grammar Support'],
            // ===== Articles =====
            ['a', 'article', 'A1', 'Grammar Support'], ['an', 'article', 'A1', 'Grammar Support'],
            ['the', 'article', 'A1', 'Grammar Support'],
            // ===== Countries =====
            ['Indonesia', 'country', 'A1', 'Countries & Languages'], ['Japan', 'country', 'A1', 'Countries & Languages'],
            ['China', 'country', 'A1', 'Countries & Languages'], ['Korea', 'country', 'A1', 'Countries & Languages'],
            ['Thailand', 'country', 'A1', 'Countries & Languages'], ['Malaysia', 'country', 'A1', 'Countries & Languages'],
            ['Singapore', 'country', 'A1', 'Countries & Languages'], ['Vietnam', 'country', 'A1', 'Countries & Languages'],
            ['India', 'country', 'A1', 'Countries & Languages'], ['Australia', 'country', 'A1', 'Countries & Languages'],
            ['England', 'country', 'A1', 'Countries & Languages'], ['America', 'country', 'A1', 'Countries & Languages'],
            ['France', 'country', 'A1', 'Countries & Languages'], ['Germany', 'country', 'A1', 'Countries & Languages'],
            ['Spain', 'country', 'A1', 'Countries & Languages'], ['Italy', 'country', 'A1', 'Countries & Languages'],
            ['Brazil', 'country', 'A1', 'Countries & Languages'], ['Russia', 'country', 'A2', 'Countries & Languages'],
            ['Turkey', 'country', 'A2', 'Countries & Languages'], ['Egypt', 'country', 'A2', 'Countries & Languages'],
            ['Netherlands', 'country', 'B1', 'Countries & Languages'], ['Switzerland', 'country', 'B1', 'Countries & Languages'],
            ['Canada', 'country', 'B1', 'Countries & Languages'], ['Mexico', 'country', 'B1', 'Countries & Languages'],
            // ===== Languages =====
            ['Indonesian', 'language', 'A1', 'Countries & Languages'], ['English', 'language', 'A1', 'Countries & Languages'],
            ['Japanese', 'language', 'A1', 'Countries & Languages'], ['Chinese', 'language', 'A1', 'Countries & Languages'],
            ['Korean', 'language', 'A1', 'Countries & Languages'], ['Thai', 'language', 'A1', 'Countries & Languages'],
            ['Malay', 'language', 'A1', 'Countries & Languages'], ['Vietnamese', 'language', 'A1', 'Countries & Languages'],
            ['Hindi', 'language', 'A1', 'Countries & Languages'], ['French', 'language', 'A1', 'Countries & Languages'],
            ['German', 'language', 'A1', 'Countries & Languages'], ['Spanish', 'language', 'A1', 'Countries & Languages'],
            ['Italian', 'language', 'A1', 'Countries & Languages'], ['Portuguese', 'language', 'A2', 'Countries & Languages'],
            ['Arabic', 'language', 'A2', 'Countries & Languages'], ['Russian', 'language', 'A2', 'Countries & Languages'],
            ['Dutch', 'language', 'B1', 'Countries & Languages'], ['Turkish', 'language', 'B1', 'Countries & Languages'],
            ['Swedish', 'language', 'B1', 'Countries & Languages'], ['Greek', 'language', 'B1', 'Countries & Languages'],
            // ===== Verbs (kembali ke POS asli - jangan di-override jadi adjective) =====
            ['clean', 'verb', 'A1', 'General Actions'], ['open', 'verb', 'A1', 'General Actions'],
            ['dry', 'verb', 'A1', 'General Actions'], ['empty', 'verb', 'A1', 'General Actions'],
            // ===== Descriptive Adjectives (A1) =====
            ['good', 'adjective', 'A1', 'Descriptive'], ['bad', 'adjective', 'A1', 'Descriptive'],
            ['big', 'adjective', 'A1', 'Descriptive'], ['small', 'adjective', 'A1', 'Descriptive'],
            ['new', 'adjective', 'A1', 'Descriptive'], ['old', 'adjective', 'A1', 'Descriptive'],
            ['happy', 'adjective', 'A1', 'Descriptive'], ['sad', 'adjective', 'A1', 'Descriptive'],
            ['hot', 'adjective', 'A1', 'Descriptive'], ['cold', 'adjective', 'A1', 'Descriptive'],
            ['tall', 'adjective', 'A1', 'Descriptive'], ['short', 'adjective', 'A1', 'Descriptive'],
            ['long', 'adjective', 'A1', 'Descriptive'], ['young', 'adjective', 'A1', 'Descriptive'],
            ['high', 'adjective', 'A1', 'Descriptive'], ['low', 'adjective', 'A1', 'Descriptive'],
            ['fast', 'adjective', 'A1', 'Descriptive'], ['slow', 'adjective', 'A1', 'Descriptive'],
            ['easy', 'adjective', 'A1', 'Descriptive'], ['hard', 'adjective', 'A1', 'Descriptive'],
            ['beautiful', 'adjective', 'A1', 'Descriptive'], ['nice', 'adjective', 'A1', 'Descriptive'],
            ['kind', 'adjective', 'A1', 'Descriptive'], ['friendly', 'adjective', 'A1', 'Descriptive'],
            ['busy', 'adjective', 'A1', 'Descriptive'], ['tired', 'adjective', 'A1', 'Descriptive'],
            ['hungry', 'adjective', 'A1', 'Descriptive'], ['thirsty', 'adjective', 'A1', 'Descriptive'],
            ['full', 'adjective', 'A1', 'Descriptive'],
            ['closed', 'adjective', 'A1', 'Descriptive'],
            ['near', 'adjective', 'A1', 'Descriptive'], ['far', 'adjective', 'A1', 'Descriptive'],
            ['early', 'adjective', 'A1', 'Descriptive'], ['late', 'adjective', 'A1', 'Descriptive'],
            ['ready', 'adjective', 'A1', 'Descriptive'], ['sure', 'adjective', 'A1', 'Descriptive'],
            ['dirty', 'adjective', 'A1', 'Descriptive'],
            ['quiet', 'adjective', 'A1', 'Descriptive'], ['loud', 'adjective', 'A1', 'Descriptive'],
            ['strong', 'adjective', 'A1', 'Descriptive'], ['weak', 'adjective', 'A1', 'Descriptive'],
            ['rich', 'adjective', 'A1', 'Descriptive'], ['poor', 'adjective', 'A1', 'Descriptive'],
            ['funny', 'adjective', 'A1', 'Descriptive'], ['dark', 'adjective', 'A1', 'Descriptive'],
            ['light', 'adjective', 'A1', 'Descriptive'], ['heavy', 'adjective', 'A1', 'Descriptive'],
            ['warm', 'adjective', 'A1', 'Descriptive'], ['cool', 'adjective', 'A1', 'Descriptive'],
            ['wet', 'adjective', 'A1', 'Descriptive'],
            ['sunny', 'adjective', 'A1', 'Descriptive'], ['rainy', 'adjective', 'A1', 'Descriptive'],
            ['cloudy', 'adjective', 'A1', 'Descriptive'], ['windy', 'adjective', 'A1', 'Descriptive'],
            ['safe', 'adjective', 'A1', 'Descriptive'], ['dangerous', 'adjective', 'A1', 'Descriptive'],
            ['right', 'adjective', 'A1', 'Descriptive'], ['wrong', 'adjective', 'A1', 'Descriptive'],
            // ===== Descriptive Adjectives (A2+) =====
            ['smart', 'adjective', 'A2', 'Descriptive'], ['clever', 'adjective', 'A2', 'Descriptive'],
            ['lazy', 'adjective', 'A2', 'Descriptive'], ['brave', 'adjective', 'A2', 'Descriptive'],
            ['shy', 'adjective', 'A2', 'Descriptive'], ['polite', 'adjective', 'A2', 'Descriptive'],
            ['rude', 'adjective', 'A2', 'Descriptive'], ['honest', 'adjective', 'A2', 'Descriptive'],
            ['patient', 'adjective', 'A2', 'Descriptive'], ['generous', 'adjective', 'A2', 'Descriptive'],
            ['helpful', 'adjective', 'A2', 'Descriptive'], ['careful', 'adjective', 'A2', 'Descriptive'],
            ['careless', 'adjective', 'A2', 'Descriptive'], ['cute', 'adjective', 'A2', 'Descriptive'],
            ['handsome', 'adjective', 'A2', 'Descriptive'], ['pretty', 'adjective', 'A2', 'Descriptive'],
            ['lovely', 'adjective', 'A2', 'Descriptive'], ['wonderful', 'adjective', 'A2', 'Descriptive'],
            ['terrible', 'adjective', 'A2', 'Descriptive'], ['great', 'adjective', 'A2', 'Descriptive'],
            ['interesting', 'adjective', 'A2', 'Descriptive'], ['exciting', 'adjective', 'A2', 'Descriptive'],
            ['boring', 'adjective', 'A2', 'Descriptive'], ['famous', 'adjective', 'A2', 'Descriptive'],
            ['popular', 'adjective', 'A2', 'Descriptive'], ['modern', 'adjective', 'A2', 'Descriptive'],
            ['important', 'adjective', 'A2', 'Descriptive'], ['difficult', 'adjective', 'A2', 'Descriptive'],
            ['different', 'adjective', 'A2', 'Descriptive'], ['special', 'adjective', 'A2', 'Descriptive'],
            ['comfortable', 'adjective', 'A2', 'Descriptive'], ['soft', 'adjective', 'A2', 'Descriptive'],
            ['sharp', 'adjective', 'A2', 'Descriptive'], ['fresh', 'adjective', 'A2', 'Descriptive'],
            ['sweet', 'adjective', 'A2', 'Descriptive'], ['sour', 'adjective', 'A2', 'Descriptive'],
            ['spicy', 'adjective', 'A2', 'Descriptive'], ['salty', 'adjective', 'A2', 'Descriptive'],
            ['crowded', 'adjective', 'A2', 'Descriptive'], ['noisy', 'adjective', 'A2', 'Descriptive'],
            ['smooth', 'adjective', 'A2', 'Descriptive'], ['rough', 'adjective', 'A2', 'Descriptive'],
            ['wide', 'adjective', 'A2', 'Descriptive'], ['narrow', 'adjective', 'A2', 'Descriptive'],
            ['deep', 'adjective', 'A2', 'Descriptive'], ['thick', 'adjective', 'A2', 'Descriptive'],
            ['thin', 'adjective', 'A2', 'Descriptive'], ['tiny', 'adjective', 'A2', 'Descriptive'],
            ['huge', 'adjective', 'A2', 'Descriptive'], ['angry', 'adjective', 'A2', 'Descriptive'],
            ['afraid', 'adjective', 'A2', 'Descriptive'], ['alone', 'adjective', 'A2', 'Descriptive'],
            ['asleep', 'adjective', 'A2', 'Descriptive'], ['awake', 'adjective', 'A2', 'Descriptive'],
            ['bored', 'adjective', 'A2', 'Descriptive'], ['calm', 'adjective', 'A2', 'Descriptive'],
            ['confident', 'adjective', 'B1', 'Descriptive'], ['proud', 'adjective', 'B1', 'Descriptive'],
            ['embarrassed', 'adjective', 'B1', 'Descriptive'], ['disappointed', 'adjective', 'B1', 'Descriptive'],
            ['surprised', 'adjective', 'B1', 'Descriptive'], ['anxious', 'adjective', 'B1', 'Descriptive'],
            ['nervous', 'adjective', 'B1', 'Descriptive'], ['stubborn', 'adjective', 'B1', 'Descriptive'],
            ['gentle', 'adjective', 'B1', 'Descriptive'], ['fierce', 'adjective', 'B1', 'Descriptive'],
            ['valuable', 'adjective', 'B1', 'Descriptive'], ['enormous', 'adjective', 'B1', 'Descriptive'],
            ['magnificent', 'adjective', 'B1', 'Descriptive'], ['ancient', 'adjective', 'B1', 'Descriptive'],
            // ===== Prepositions =====
            ['in', 'preposition', 'A1', 'Grammar Support'], ['on', 'preposition', 'A1', 'Grammar Support'],
            ['at', 'preposition', 'A1', 'Grammar Support'], ['to', 'preposition', 'A1', 'Grammar Support'],
            ['from', 'preposition', 'A1', 'Grammar Support'], ['for', 'preposition', 'A1', 'Grammar Support'],
            ['with', 'preposition', 'A1', 'Grammar Support'], ['of', 'preposition', 'A1', 'Grammar Support'],
            ['about', 'preposition', 'A1', 'Grammar Support'], ['by', 'preposition', 'A1', 'Grammar Support'],
            ['under', 'preposition', 'A1', 'Grammar Support'], ['over', 'preposition', 'A1', 'Grammar Support'],
            ['above', 'preposition', 'A1', 'Grammar Support'], ['below', 'preposition', 'A1', 'Grammar Support'],
            ['between', 'preposition', 'A1', 'Grammar Support'], ['behind', 'preposition', 'A1', 'Grammar Support'],
            ['around', 'preposition', 'A1', 'Grammar Support'], ['inside', 'preposition', 'A1', 'Grammar Support'],
            ['outside', 'preposition', 'A1', 'Grammar Support'], ['during', 'preposition', 'A1', 'Grammar Support'],
            ['after', 'preposition', 'A1', 'Grammar Support'], ['before', 'preposition', 'A1', 'Grammar Support'],
            ['without', 'preposition', 'A2', 'Grammar Support'], ['across', 'preposition', 'A2', 'Grammar Support'],
            ['through', 'preposition', 'A2', 'Grammar Support'], ['along', 'preposition', 'A2', 'Grammar Support'],
            ['toward', 'preposition', 'A2', 'Grammar Support'], ['among', 'preposition', 'B1', 'Grammar Support'],
            ['against', 'preposition', 'B1', 'Grammar Support'], ['throughout', 'preposition', 'B1', 'Grammar Support'],
            // ===== Conjunctions =====
            ['and', 'conjunction', 'A1', 'Grammar Support'], ['but', 'conjunction', 'A1', 'Grammar Support'],
            ['or', 'conjunction', 'A1', 'Grammar Support'], ['because', 'conjunction', 'A1', 'Grammar Support'],
            ['so', 'conjunction', 'A1', 'Grammar Support'], ['if', 'conjunction', 'A1', 'Grammar Support'],
            ['when', 'conjunction', 'A1', 'Grammar Support'], ['while', 'conjunction', 'A2', 'Grammar Support'],
            ['until', 'conjunction', 'A2', 'Grammar Support'], ['although', 'conjunction', 'B1', 'Grammar Support'],
            ['though', 'conjunction', 'B1', 'Grammar Support'], ['since', 'conjunction', 'B1', 'Grammar Support'],
            ['unless', 'conjunction', 'B1', 'Grammar Support'], ['whether', 'conjunction', 'B1', 'Grammar Support'],
            ['whenever', 'conjunction', 'B1', 'Grammar Support'], ['even though', 'conjunction', 'B1', 'Grammar Support'],
            // ===== Adverbs (Manner / Time / Degree) =====
            ['quickly', 'adverb', 'A1', 'Grammar Support'], ['slowly', 'adverb', 'A1', 'Grammar Support'],
            ['well', 'adverb', 'A1', 'Grammar Support'], ['again', 'adverb', 'A1', 'Grammar Support'],
            ['soon', 'adverb', 'A1', 'Grammar Support'], ['today', 'adverb', 'A1', 'Grammar Support'],
            ['tomorrow', 'adverb', 'A1', 'Grammar Support'], ['tonight', 'adverb', 'A1', 'Grammar Support'],
            ['now', 'adverb', 'A1', 'Grammar Support'], ['then', 'adverb', 'A1', 'Grammar Support'],
            ['here', 'adverb', 'A1', 'Grammar Support'], ['there', 'adverb', 'A1', 'Grammar Support'],
            ['together', 'adverb', 'A1', 'Grammar Support'], ['home', 'adverb', 'A1', 'Grammar Support'],
            ['really', 'adverb', 'A1', 'Grammar Support'], ['very', 'adverb', 'A1', 'Grammar Support'],
            ['too', 'adverb', 'A1', 'Grammar Support'], ['badly', 'adverb', 'A2', 'Grammar Support'],
            ['easily', 'adverb', 'A2', 'Grammar Support'], ['quietly', 'adverb', 'A2', 'Grammar Support'],
            ['loudly', 'adverb', 'A2', 'Grammar Support'], ['happily', 'adverb', 'A2', 'Grammar Support'],
            ['sadly', 'adverb', 'A2', 'Grammar Support'], ['clearly', 'adverb', 'A2', 'Grammar Support'],
            ['finally', 'adverb', 'A2', 'Grammar Support'], ['almost', 'adverb', 'A2', 'Grammar Support'],
            ['quite', 'adverb', 'A2', 'Grammar Support'], ['carefully', 'adverb', 'A2', 'Grammar Support'],
        ];

        foreach ($words as [$word, $pos, $level, $topic]) {
            VocabularyBank::updateOrCreate(
                ['word' => $word],
                ['part_of_speech' => $pos, 'cefr_level' => $level, 'topic_category' => $topic]
            );
        }
    }

    private function seedQuestionBanks(): void
    {
        $questions = [
            // ===== A1 =====
            ['A1', 'Could you tell me your full name and where you currently live?', ['name', 'greet', 'city'], true, 'Introduction', 'ADAPTIVE', 1],
            ['A1', 'How do you usually greet people when you meet them?', ['greet', 'name'], true, 'Introduction', 'ADAPTIVE', 1],
            ['A1', 'What is your favorite hobby and what do you do in your free time?', ['hobby', 'music', 'movie'], false, 'Hobbies', 'ADAPTIVE', 1],
            ['A1', 'Could you tell me what you would like to order today?', ['menu', 'order', 'drink'], false, 'Restaurant', 'ADAPTIVE', 1],
            ['A1', 'What time do you wake up in the morning?', ['morning', 'wake up', 'sleep'], false, 'Daily Routine', 'ADAPTIVE', 1],
            ['A1', 'Do you like to go to the market? What do you usually buy there?', ['market', 'buy', 'fruit'], false, 'Shopping', 'ADAPTIVE', 1],
            // ===== A2 =====
            ['A2', 'What did you buy at the market yesterday?', ['buy', 'market', 'yesterday', 'price'], true, 'Shopping', 'ADAPTIVE', 1],
            ['A2', 'How do you relax after a long day?', ['relax', 'weekend', 'movie'], false, 'Daily Routine', 'ADAPTIVE', 1],
            ['A2', 'What do you like to order when you eat at a restaurant?', ['order', 'menu', 'delicious'], false, 'Restaurant', 'ADAPTIVE', 1],
            ['A2', 'Tell me about your last visit to the hospital or the doctor.', ['doctor', 'hospital', 'healthy'], false, 'Health', 'ADAPTIVE', 1],
            ['A2', 'How do you usually travel to work or school?', ['travel', 'ticket', 'city'], false, 'Travel', 'ADAPTIVE', 1],
            ['A2', 'Describe your favorite day of the week.', ['weekend', 'relax', 'family'], false, 'Daily Routine', 'ADAPTIVE', 1],
            // ===== B1 =====
            ['B1', 'Tell me about your work experience and your main strengths.', ['experience', 'skill', 'strength', 'career'], true, 'Job Interview', 'ADAPTIVE', 1],
            ['B1', 'Why do you think you are a good candidate for this position?', ['candidate', 'skill', 'experience'], false, 'Job Interview', 'ADAPTIVE', 1],
            ['B1', 'What are the advantages of studying abroad for a university student?', ['university', 'graduate', 'knowledge'], false, 'Education', 'ADAPTIVE', 1],
            ['B1', 'Describe a memorable journey you have taken.', ['journey', 'destination', 'flight'], false, 'Travel', 'ADAPTIVE', 1],
            ['B1', 'What advice would you give to someone who wants to live a healthier life?', ['healthy', 'exercise', 'doctor'], false, 'Health', 'ADAPTIVE', 1],
            ['B1', 'Do you think technology makes our daily lives easier?', ['technology', 'internet', 'computer'], false, 'Technology', 'ADAPTIVE', 1],
            // ===== B2 =====
            ['B2', 'In your opinion, should companies invest more in employee training?', ['opinion', 'achievement', 'career'], true, 'Job Interview', 'ADAPTIVE', 1],
            ['B2', 'What are the long-term effects of studying with artificial intelligence tools?', ['technology', 'research', 'knowledge'], false, 'Education', 'ADAPTIVE', 1],
            ['B2', 'How has digital communication changed the way people make friends?', ['digital', 'internet', 'opinion'], false, 'Technology', 'ADAPTIVE', 1],
            ['B2', 'What makes a destination attractive for international tourists?', ['destination', 'journey', 'flight'], false, 'Travel', 'ADAPTIVE', 1],
            ['B2', 'How do you balance a demanding career with personal well-being?', ['career', 'healthy', 'exercise'], false, 'Health', 'ADAPTIVE', 1],
            ['B2', 'Do you agree that remote work will become the standard?', ['opinion', 'alternative', 'career'], false, 'Job Interview', 'ADAPTIVE', 1],
            // ===== C1 =====
            ['C1', 'Analyze the impact of artificial intelligence on the global job market.', ['technology', 'perspective', 'research'], false, 'Technology', 'IELTS_SPEAKING', 3],
            ['C1', 'Discuss how governments could improve access to higher education.', ['university', 'perspective', 'alternative'], false, 'Education', 'IELTS_SPEAKING', 3],
            ['C1', 'Evaluate the ethical considerations of personalized advertising.', ['digital', 'opinion', 'perspective'], false, 'Technology', 'TOEFL_IBT', 2],
            // ===== C2 =====
            ['C2', 'Critically evaluate the role of lifelong learning in modern economies.', ['knowledge', 'perspective', 'research'], false, 'Education', 'IELTS_SPEAKING', 3],
            ['C2', 'Propose solutions for ensuring equitable access to healthcare in remote areas.', ['alternative', 'perspective', 'healthy'], false, 'Health', 'TOEFL_IBT', 4],
            ['C2', 'Articulate a compelling argument for or against universal basic income.', ['perspective', 'opinion', 'alternative'], false, 'Abstract', 'IELTS_SPEAKING', 3],
        ];

        foreach ($questions as [$level, $text, $tags, $isStarter, $topic, $testType, $part]) {
            QuestionBank::updateOrCreate(
                ['question_text' => $text],
                [
                    'cefr_level' => $level,
                    'required_vocab_tags' => $tags,
                    'is_starter' => $isStarter,
                    'topic_category' => $topic,
                    'test_type' => $testType,
                    'part_number' => $part,
                    'metadata' => ['audio_url' => null],
                ]
            );
        }
    }

    private function seedThematicTopics(): void
    {
        $topics = [
            ['Job Interview Simulation', 'HR Manager at a Tech Startup', 'Intermediate', ['skills', 'experience', 'strengths', 'career']],
            ['Ordering Food at a Restaurant', 'Waiter at an Italian Restaurant', 'Beginner', ['menu', 'order', 'drink', 'bill']],
            ['Talking About Your Weekend', 'Friendly Neighbor', 'Beginner', ['weekend', 'relax', 'family', 'movie']],
            ['Airport Check-in & Travel', 'Airline Check-in Staff', 'Elementary', ['airport', 'ticket', 'flight', 'journey']],
            ['Visiting the Doctor', 'General Practitioner', 'Elementary', ['doctor', 'hospital', 'healthy', 'exercise']],
            ['University Campus Tour', 'Senior Student Guide', 'Intermediate', ['university', 'graduate', 'research', 'knowledge']],
            ['Tech Startup Pitch', 'Business Investor', 'Advanced', ['technology', 'digital', 'alternative', 'achievement']],
            ['Debating Social Media', 'Talk Show Host', 'Upper-Intermediate', ['digital', 'opinion', 'perspective', 'internet']],
        ];

        foreach ($topics as [$name, $persona, $level, $tags]) {
            ThematicTopic::updateOrCreate(
                ['topic_name' => $name],
                [
                    'roleplay_persona' => $persona,
                    'selected_level' => $level,
                    'context_vocab_tags' => $tags,
                    'is_active' => true,
                    'created_by' => null,
                ]
            );
        }
    }

    private function seedThematicQuestions(): void
    {
        $questions = [
            'Job Interview Simulation' => [
                ['Tell me about yourself and your background.', 'My name is Rina. I have five years of experience as a marketing analyst and I specialize in digital campaigns.', 'B1', 'work experience'],
                ['Why do you want to work for this company?', 'I want to work here because your company values innovation and offers real career growth opportunities.', 'B1', 'company motivation'],
                ['What are your greatest strengths?', 'My greatest strengths are communication and problem solving. I stay calm under pressure and can lead a team effectively.', 'B1', 'strengths'],
                ['What is your biggest weakness?', 'I sometimes spend too much time perfecting small details, but I am learning to prioritize better.', 'B1', 'weakness'],
                ['Where do you see yourself in five years?', 'In five years I plan to grow into a senior marketing lead and mentor junior team members while delivering strong results.', 'B1', 'career plan'],
                ['Tell me about a challenge you faced at work.', 'Last year our team missed a deadline, so I reorganized our task list and daily stand-up meetings to get the project back on track.', 'B1', 'challenge'],
                ['Why should we hire you?', 'You should hire me because I bring proven results, a strong work ethic, and the exact skills your team currently needs.', 'B1', 'self promotion'],
                ['How do you handle stress and pressure?', 'I handle pressure by breaking tasks into smaller steps and focusing on one priority at a time, which keeps me productive and calm.', 'B1', 'stress management'],
                ['What do you know about our company?', 'I know your company leads the local tech market and focuses on user-friendly products with strong customer support.', 'B1', 'company research'],
                ['Do you have any questions for us?', 'Yes, I would like to know what the daily responsibilities of this role are and how the team measures success.', 'B1', 'questions to ask'],
            ],
            'Ordering Food at a Restaurant' => [
                ['Good afternoon, welcome to our restaurant. What can I get for you today?', 'I would like a bowl of chicken soup and a glass of orange juice, please.', 'A1', 'ordering food'],
                ['Would you like anything to drink?', 'Yes, I would like some water and one cup of hot tea, please.', 'A1', 'drink order'],
                ['What do you recommend from the menu today?', 'I think the grilled fish with rice is excellent today because it is fresh and delicious.', 'A1', 'recommendation'],
                ['How would you like your steak cooked?', 'I would like my steak medium rare, please, with extra vegetables on the side.', 'A1', 'cooking preference'],
                ['Is everything okay with your meal?', 'Yes, everything is delicious. Could I have some more bread, please?', 'A1', 'meal feedback'],
                ['Would you like dessert after your meal?', 'Yes, I would love a piece of chocolate cake with a cup of coffee, please.', 'A1', 'dessert'],
                ['Do you have any allergies I should know about?', 'I am allergic to peanuts, so please do not add nuts to my food.', 'A1', 'allergy'],
                ['Would you like to order an appetizer first?', 'Yes, please bring us the spring rolls and a small salad to start.', 'A1', 'appetizer'],
                ['How is the food today?', 'The food is very tasty and the service is excellent. I really enjoy my meal.', 'A1', 'food quality'],
                ['Can I get you anything else?', 'No thanks, could I have the bill now, please?', 'A1', 'bill'],
            ],
            'Talking About Your Weekend' => [
                ['Hi! How was your weekend?', 'My weekend was great. I spent Sunday relaxing at home and watched a movie with my family.', 'A1', 'weekend response'],
                ['What did you do last Saturday?', 'Last Saturday I visited my grandparents and had lunch together at their house.', 'A1', 'saturday activity'],
                ['Did you go anywhere this weekend?', 'Yes, I went to the beach on Sunday with my friends and we had a wonderful time.', 'A1', 'going out'],
                ['What do you usually do on weekends?', 'I usually sleep late, do some shopping, and meet my friends for coffee.', 'A1', 'usual weekend'],
                ['Do you prefer spending weekends at home or outside?', 'I prefer spending weekends outside because I love exploring new places and meeting people.', 'A1', 'preference'],
                ['Who did you spend your weekend with?', 'I spent my weekend with my family and some close friends from my neighborhood.', 'A1', 'companions'],
                ['Was your weekend relaxing?', 'Yes, it was very relaxing. I slept well and did not do any work at all.', 'A1', 'relaxing'],
                ['Did you watch any movies this weekend?', 'Yes, I watched two movies at home, one comedy and one action movie.', 'A1', 'movies'],
                ['What was the best part of your weekend?', 'The best part was having a family dinner on Sunday evening with a lot of laughter.', 'A1', 'best moment'],
                ['Do you have any plans for next weekend?', 'I plan to visit a new museum on Saturday and cook something special on Sunday.', 'A1', 'future plans'],
            ],
            'Airport Check-in & Travel' => [
                ['Good morning, may I see your passport and ticket, please?', 'Good morning, here is my passport and this is my boarding ticket.', 'A2', 'documents'],
                ['Do you have any luggage to check in today?', 'Yes, I have one suitcase that I would like to check in and a small bag that I will carry with me.', 'A2', 'luggage'],
                ['Which seat would you prefer, window or aisle?', 'I prefer a window seat, please, so I can enjoy the view during the flight.', 'A2', 'seat preference'],
                ['Would you like any special meal for your flight?', 'Yes, I would like a vegetarian meal, please.', 'A2', 'special meal'],
                ['Where are you traveling to today?', 'I am traveling to Singapore today for a three-day business trip.', 'A2', 'destination'],
                ['Could you place your bag on the scale, please?', 'Of course, here is my carry-on bag. I hope it is not too heavy.', 'A2', 'hand luggage'],
                ['Is this your first time flying with us?', 'No, I have flown with your airline many times and I really like the service.', 'A2', 'first flight'],
                ['What is the purpose of your visit?', 'I am going for a business meeting with our company partners.', 'A2', 'purpose of visit'],
                ['Please proceed to gate number seven for boarding.', 'Thank you, I will go to gate number seven now. Which time does boarding start?', 'A2', 'boarding gate'],
                ['Have a nice flight!', 'Thank you very much, I will. Goodbye!', 'A2', 'farewell'],
            ],
            'Visiting the Doctor' => [
                ['Good morning, what brings you here today?', 'Good morning, I have a sore throat and a headache that started two days ago.', 'A2', 'symptoms'],
                ['How long have you been feeling this way?', 'I have been feeling unwell since Monday, so about three days now.', 'A2', 'duration'],
                ['Do you have a fever or any other symptoms?', 'I have a slight fever and I feel very tired all the time.', 'A2', 'other symptoms'],
                ['Are you taking any medication right now?', 'I am taking some painkillers and vitamins that I bought at the pharmacy.', 'A2', 'medication'],
                ['Do you have any allergies to medicine?', 'Yes, I am allergic to penicillin, so please avoid antibiotics from that group.', 'A2', 'medicine allergy'],
                ['Have you had this problem before?', 'I had a similar problem last year during the rainy season.', 'A2', 'history'],
                ['Did you sleep or eat well recently?', 'I have not been eating well this week and I sleep only about five hours a night.', 'A2', 'lifestyle'],
                ['Let me check your temperature and blood pressure.', 'Okay doctor, I will follow your instructions and stand still for the examination.', 'A2', 'examination'],
                ['You need to rest and drink lots of water.', 'Thank you doctor. How long should I rest before I can return to work?', 'A2', 'rest advice'],
                ['Please take this medicine three times a day after meals.', 'Understood, I will take it three times a day after meals for one week.', 'A2', 'take medicine'],
            ],
            'University Campus Tour' => [
                ['Welcome to our campus! Where would you like to start the tour?', 'I would love to start with the library and then see the main lecture hall.', 'B1', 'tour start'],
                ['What facilities does this university offer?', 'This university has modern laboratories, a large library, sports centers, and comfortable dormitories.', 'B1', 'facilities'],
                ['How many students study here each year?', 'About ten thousand students study here every year, including many international students.', 'B1', 'student body'],
                ['Can you tell me about the study programs?', 'We offer strong programs in engineering, medicine, business, and computer science.', 'B1', 'study programs'],
                ['How do I apply to this university?', 'You can apply online through our website, and you will need your transcripts and an English test score.', 'B1', 'application'],
                ['Are there scholarships for international students?', 'Yes, we provide merit-based scholarships that cover up to fifty percent of tuition fees.', 'B1', 'scholarship'],
                ['What is student life like on campus?', 'Student life is very active, with clubs, sports teams, cultural events, and weekly study groups.', 'B1', 'student life'],
                ['Is the campus near the city center?', 'Yes, the campus is about ten minutes from the city center and easy to reach by bus.', 'B1', 'location'],
                ['Where can international students live?', 'International students usually live in the dormitories on campus or close apartments nearby.', 'B1', 'accommodation'],
                ['Can I visit a lecture to see how classes are taught?', 'Yes, you are welcome to attend our English literature class tomorrow at nine in the morning.', 'B1', 'class visit'],
            ],
            'Tech Startup Pitch' => [
                ['Tell me about your product and what problem it solves.', 'Our product is an AI scheduling app that saves small businesses an average of ten hours a week.', 'C1', 'product value'],
                ['Who is your target market?', 'Our main target is small and medium retail businesses that struggle with manual appointment management.', 'C1', 'target market'],
                ['How is your product different from competitors?', 'Unlike competitors, we offer full automation and a pricing model that starts free for small teams.', 'C1', 'differentiation'],
                ['What is your business model and revenue streams?', 'We generate revenue through monthly subscriptions and a premium tier with advanced analytics.', 'C1', 'business model'],
                ['What is your current traction?', 'We currently have two hundred paying customers and we are growing at twenty percent each month.', 'C1', 'traction'],
                ['How large is your team?', 'Our team has eight members, four engineers, two designers, and two business developers.', 'C1', 'team size'],
                ['What are your key metrics?', 'Our key metrics are monthly recurring revenue, customer retention, and daily active users.', 'C1', 'metrics'],
                ['What is your funding ask and how will you use it?', 'We are raising two hundred thousand dollars to expand engineering and launch our marketing campaigns.', 'C1', 'funding'],
                ['What are the biggest risks to your startup?', 'The biggest risks are competition from larger platforms and difficulty acquiring customers in new regions.', 'C1', 'risks'],
                ['What is your 12-month vision?', 'Within twelve months, we aim to reach ten thousand customers and expand into two new countries.', 'C1', 'vision'],
            ],
            'Debating Social Media' => [
                ['Do you think social media connects or divides people?', 'I believe social media connects people across distances, but it can also divide society when misinformation spreads.', 'B2', 'social media impact'],
                ['What are the positive effects of social media?', 'Social media provides instant communication, access to news, and incredible opportunities for small businesses.', 'B2', 'positive effects'],
                ['What are the dangers of social media?', 'The main dangers are data privacy issues, cyberbullying, and the spread of misleading information.', 'B2', 'dangers'],
                ['Should children have their own social media accounts?', 'I think children under thirteen should not have unrestricted access, because they are not ready to handle online risks.', 'B2', 'children'],
                ['Does social media affect mental health?', 'Yes, heavy usage can increase anxiety and comparison, but moderate use with healthy habits is usually fine.', 'B2', 'mental health'],
                ['Should social media platforms regulate more content?', 'Platforms should regulate harmful content more strictly, but they must also protect freedom of speech.', 'B2', 'regulation'],
                ['Is social media good for democracy?', 'It can strengthen democracy by giving everyone a voice, yet it can also spread propaganda if left unchecked.', 'B2', 'democracy'],
                ['Do you think people spend too much time on social media?', 'Yes, many people check their phones hundreds of times daily, which harms productivity and real relationships.', 'B2', 'screen time'],
                ['Should governments control social media usage?', 'Governments should set clear rules but not control free expression, because balance is essential in a democracy.', 'B2', 'government control'],
                ['How can people use social media more healthily?', 'People can set daily time limits, mute negative accounts, and prioritize face-to-face communication.', 'B2', 'healthy usage'],
            ],
        ];

        $topicsByLevel = [
            'Job Interview Simulation' => 'B1',
            'Ordering Food at a Restaurant' => 'A1',
            'Talking About Your Weekend' => 'A1',
            'Airport Check-in & Travel' => 'A2',
            'Visiting the Doctor' => 'A2',
            'University Campus Tour' => 'B1',
            'Tech Startup Pitch' => 'C1',
            'Debating Social Media' => 'B2',
        ];

        foreach ($questions as $topicName => $rows) {
            $topic = ThematicTopic::where('topic_name', $topicName)->first();
            foreach ($rows as [$questionText, $standardAnswer, $level, $keyPoint]) {
                \App\Models\ThematicQuestion::updateOrCreate(
                    [
                        'topic_id' => $topic->id,
                        'question_text' => $questionText,
                    ],
                    [
                        'standard_answer' => $standardAnswer,
                        'cefr_level' => $topicsByLevel[$topicName],
                        'key_point' => $keyPoint,
                    ]
                );
            }
        }
    }

    private function seedUsers(): void
    {
        $names = [
            'A1' => ['Budi Santoso', 'Siti Aminah', 'Agus Wijaya', 'Dewi Lestari'],
            'A2' => ['Rizky Pratama', 'Ani Rahmawati', 'Bayu Saputra', 'Citra Kirana'],
            'B1' => ['Rina Puspita', 'Fajar Nugroho', 'Maya Anggraini', 'Dimas Arya'],
            'B2' => ['Andi Firmansyah', 'Sari Dewanti', 'Galih Prakoso', 'Nia Ramadhani'],
            'C1' => ['Lukman Hakim', 'Intan Permata', 'Rendra Wijaya', 'Putri Maharani'],
            'C2' => ['Hendra Gunawan', 'Salsaabila', 'Yoga Prasetyo', 'Larasati'],
        ];

        foreach ($this->levels as $idx => $level) {
            foreach ($names[$level] as $j => $name) {
                $email = 'user'.strtolower(str_replace(' ', '', $name)).'@topspeak.test';
                $premium = ($idx >= 3) || ($idx === 2 && $j === 0);

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => Str::password(20),
                        'device_uuid' => (string) Str::uuid(),
                        'phone_number' => '+628'.random_int(100000000, 999999999),
                        'current_cefr_level' => $level,
                        'remaining_trial_sessions' => random_int(0, 5),
                        'subscription_status' => $premium ? SubscriptionStatus::PREMIUM_MONTHLY : SubscriptionStatus::FREE,
                        'subscription_expires_at' => $premium ? now()->addMonth() : null,
                    ]
                );

                if ($premium) {
                    UserSubscription::updateOrCreate(
                        ['user_id' => $user->id, 'status' => SubscriptionStatus::PREMIUM_MONTHLY->value],
                        [
                            'started_at' => now()->subWeeks(random_int(1, 8)),
                            'expires_at' => now()->addMonth(),
                            'payment_provider' => 'duitku',
                            'payment_ref' => 'DUITKU-'.strtoupper(Str::random(8)),
                            'is_active' => true,
                        ]
                    );
                }

                if ($level !== 'A1') {
                    $prevIdx = array_search($level, $this->levels, true) - 1;
                    $previous = $this->levels[$prevIdx];
                    UserLevelHistory::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'session_id' => (string) Str::uuid(),
                            'previous_level' => $previous,
                            'new_level' => $level,
                            'trigger_score' => random_int(6, 9),
                            'promotion_reason' => '4-Turn Rolling Window Threshold Reached',
                        ]
                    );
                }
            }
        }
    }

    private function seedAdminUser(): void
    {
        $admin = User::firstOrCreate(
            ['email' => config('admin.email', 'admin@topspeak.app')],
            [
                'name' => 'Admin TopSpeak',
                'password' => config('admin.password', 'Admin123!'),
                'device_uuid' => '00000000-0000-4000-8000-000000000000',
                'phone_number' => '+6281234567890',
                'current_cefr_level' => 'C2',
                'remaining_trial_sessions' => 0,
                'subscription_status' => SubscriptionStatus::PREMIUM_YEARLY,
                'is_admin' => true,
            ]
        );

        // Pastikan kredensial admin selalu tersedia (termasuk admin bawaan lama).
        $admin->forceFill([
            'password' => config('admin.password', 'Admin123!'),
            'is_admin' => true,
        ])->save();

        UserSubscription::updateOrCreate(
            ['user_id' => $admin->id, 'status' => SubscriptionStatus::PREMIUM_YEARLY->value],
            [
                'started_at' => now()->subMonths(2),
                'expires_at' => now()->addYear(),
                'payment_provider' => 'duitku',
                'payment_ref' => 'DUITKU-ADMIN-'.strtoupper(Str::random(6)),
                'is_active' => true,
            ]
        );
    }

    private function seedConversationSessions(): void
    {
        ConversationLog::query()->delete();

        $participants = User::where('email', 'like', '%@topspeak.test')->limit(8)->get();

        foreach ($participants as $user) {
            $level = $user->current_cefr_level->value;
            $turnTarget = random_int(5, 8);
            $sessionId = (string) Str::uuid();

            // 1 sesi "naik level" untuk user level >= B1: soal awal di level sebelumnya
            $activeLevel = $level;
            if ($level !== 'A1') {
                $activeLevel = $this->levels[array_search($level, $this->levels, true) - 1];
            }

            $asked = [];
            for ($turn = 1; $turn <= $turnTarget; $turn++) {
                if ($turn === 6) {
                    $activeLevel = $level; // pertanyaan turn 4-5+ dari level baru
                }

                $question = $this->pickQuestion($activeLevel, $asked);
                $asked[] = $question->id;

                // Pola skor realistis: sebagian kecil turn bermasalah
                $hasError = $turn === 3 && ($turnTarget >= 5);

                $response = $this->buildResponse($question, $hasError);
                $scores = $this->computeScores($response, $question, $hasError);

                ConversationLog::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'turn_number' => $turn,
                    'question_id' => $question->id,
                    'user_response_text' => $response,
                    'score_word_count' => $scores['word'],
                    'score_grammar' => $scores['grammar'],
                    'total_turn_score' => $scores['total'],
                    'has_error' => $hasError,
                    'user_said_text' => $hasError ? 'I go to the market yesterday' : null,
                    'correct_way_text' => $hasError ? 'I went to the market yesterday' : null,
                    'step_state' => $hasError ? 'WAITING_REPETITION' : 'NORMAL',
                    'expected_repetition_text' => $hasError ? 'I went to the market yesterday' : null,
                    'repetition_attempts' => $hasError ? random_int(0, 1) : 0,
                    'repetition_success' => $hasError ? random_int(0, 1) === 1 : null,
                    'created_at' => now()->subMinutes(random_int(30, 3000)),
                ]);
            }
        }
    }

    private function pickQuestion(string $level, array $exclude): QuestionBank
    {
        $pool = QuestionBank::where('cefr_level', $level)
            ->whereNotIn('id', $exclude)
            ->get();

        if ($pool->isEmpty()) {
            $pool = QuestionBank::whereNotIn('id', $exclude)->get();
        }

        return $pool->isEmpty() ? QuestionBank::first() : $pool->random();
    }

    private function buildResponse(QuestionBank $question, bool $hasError): string
    {
        $vocab = $question->required_vocab_tags ?? [];
        $keywords = array_slice($vocab, 0, 2);

        if ($hasError) {
            return "I think I go to the market yesterday and I buy some fruit there. It was very nice.";
        }

        $topic = $keywords[0] ?? 'this topic';
        $partner = $keywords[1] ?? 'family';

        $base = [
            "That is an interesting question. Let me share my opinion about it. I really enjoy talking about {$topic} because it is part of my life.",
            "Well, in my experience, {$topic} has always been something I care about. I usually spend time with my {$partner} and I feel happy.",
            "To be honest, I have thought about this before. I believe understanding {$topic} helps people communicate better every day.",
            "Hello, I would love to answer that. For me, {$topic} is very important and I practice it regularly with my friends.",
        ];

        return $base[array_rand($base)];
    }

    private function computeScores(string $response, QuestionBank $question, bool $hasError): array
    {
        $wordCount = str_word_count($response);
        $wordScore = $wordCount >= 20 ? 1 : 0;

        $grammarScore = $hasError ? 0 : (random_int(0, 1) === 1 ? 1 : 0);

        return [
            'word' => $wordScore,
            'grammar' => $grammarScore,
            'total' => $wordScore + $grammarScore,
        ];
    }
}