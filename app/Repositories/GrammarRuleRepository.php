<?php

namespace App\Repositories;

use App\Models\GrammarRule;
use App\Services\Grammar\GrammarRulePatternResolver;

class GrammarRuleRepository extends Repository
{
    protected function model(): string
    {
        return GrammarRule::class;
    }

    public function allActive(): \Illuminate\Support\Collection
    {
        return $this->query()->where('is_active', true)->get();
    }

    public function allActiveErrorRules(): \Illuminate\Support\Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->where('rule_type', 'error')
            ->get();
    }

    /** Deteksi pelanggaran grammar pada teks berdasarkan semua rule aktif (tipe error). */
    public function detectViolations(string $text): array
    {
        $violations = [];
        $resolver = $this->resolver();

        foreach ($this->allActiveErrorRules() as $rule) {
            $pattern = $resolver->resolve($rule->regex_pattern);

            if (@preg_match($pattern, $text) === 1) {
                $violations[] = [
                    'rule_code' => $rule->rule_code,
                    'category' => $rule->category,
                    'description' => $rule->description,
                ];
            }
        }

        return $violations;
    }

    /** Cek apakah teks match rule positif (pola benar) yang aktif. */
    public function matchesPositiveRule(string $text): bool
    {
        $resolver = $this->resolver();

        foreach ($this->query()->where('is_active', true)->where('rule_type', 'positive')->get() as $rule) {
            $pattern = $resolver->resolve($rule->regex_pattern);

            if (@preg_match($pattern, $text) === 1) {
                return true;
            }
        }

        return false;
    }

    private function resolver(): GrammarRulePatternResolver
    {
        return app(GrammarRulePatternResolver::class);
    }
}
