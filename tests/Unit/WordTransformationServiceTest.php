<?php

namespace Tests\Unit;

use App\Services\Grammar\WordTransformationService;
use Tests\TestCase;

class WordTransformationServiceTest extends TestCase
{
    private WordTransformationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WordTransformationService::class);
    }

    public function test_past_simple_irregular_lookup(): void
    {
        $this->assertSame('went', $this->service->getPastSimple('go'));
        $this->assertSame('saw', $this->service->getPastSimple('see'));
        $this->assertSame('bought', $this->service->getPastSimple('buy'));
        $this->assertSame('ate', $this->service->getPastSimple('eat'));
        $this->assertSame('took', $this->service->getPastSimple('take'));
    }

    public function test_past_simple_case_insensitive(): void
    {
        $this->assertSame('went', $this->service->getPastSimple('Go'));
        $this->assertSame('saw', $this->service->getPastSimple('SEE'));
    }

    public function test_past_simple_regular_fallback(): void
    {
        $this->assertSame('walked', $this->service->getPastSimple('walk'));
        $this->assertSame('liked', $this->service->getPastSimple('like'));
        $this->assertSame('studied', $this->service->getPastSimple('study'));
        $this->assertSame('played', $this->service->getPastSimple('play'));
    }

    public function test_past_participle_lookup(): void
    {
        $this->assertSame('gone', $this->service->getPastParticiple('go'));
        $this->assertSame('seen', $this->service->getPastParticiple('see'));
        $this->assertSame('written', $this->service->getPastParticiple('write'));
    }

    public function test_known_past_simple_only_for_irregular_verbs(): void
    {
        $this->assertSame('went', $this->service->knownPastSimple('go'));
        $this->assertNull($this->service->knownPastSimple('market'));
        $this->assertNull($this->service->knownPastSimple('walk'));
    }

    public function test_plural_irregular_lookup(): void
    {
        $this->assertSame('children', $this->service->getPluralNoun('child'));
        $this->assertSame('people', $this->service->getPluralNoun('person'));
        $this->assertSame('men', $this->service->getPluralNoun('man'));
        $this->assertSame('women', $this->service->getPluralNoun('woman'));
        $this->assertSame('feet', $this->service->getPluralNoun('foot'));
    }

    public function test_plural_regular_fallback(): void
    {
        $this->assertSame('books', $this->service->getPluralNoun('book'));
        $this->assertSame('boxes', $this->service->getPluralNoun('box'));
        $this->assertSame('churches', $this->service->getPluralNoun('church'));
        $this->assertSame('cities', $this->service->getPluralNoun('city'));
        $this->assertSame('boys', $this->service->getPluralNoun('boy'));
        $this->assertSame('days', $this->service->getPluralNoun('day'));
    }

    public function test_adjective_comparative_superlative_adverb(): void
    {
        $this->assertSame('better', $this->service->getComparative('good'));
        $this->assertSame('best', $this->service->getSuperlative('good'));
        $this->assertSame('well', $this->service->getDerivedAdverb('good'));
        $this->assertSame('worse', $this->service->getComparative('bad'));
        $this->assertSame('worst', $this->service->getSuperlative('bad'));
        $this->assertSame('badly', $this->service->getDerivedAdverb('bad'));
        $this->assertNull($this->service->getComparative('beautiful'));
    }

    public function test_pronoun_forms(): void
    {
        $this->assertSame('me', $this->service->getObjectPronoun('i'));
        $this->assertSame('him', $this->service->getObjectPronoun('he'));
        $this->assertSame('her', $this->service->getObjectPronoun('she'));
        $this->assertSame('us', $this->service->getObjectPronoun('we'));
        $this->assertSame('them', $this->service->getObjectPronoun('they'));

        $this->assertSame('my', $this->service->getPossessiveAdjective('i'));
        $this->assertSame('mine', $this->service->getPossessivePronoun('i'));
        $this->assertSame('myself', $this->service->getReflexivePronoun('i'));
        $this->assertSame('themselves', $this->service->getReflexivePronoun('they'));

        $this->assertNull($this->service->getObjectPronoun('nobody'));
    }

    public function test_demonstrative_plural(): void
    {
        $this->assertSame('these', $this->service->getPluralDemonstrative('this'));
        $this->assertSame('those', $this->service->getPluralDemonstrative('that'));
        $this->assertNull($this->service->getPluralDemonstrative('whatever'));
    }
}
