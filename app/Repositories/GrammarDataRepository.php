<?php

namespace App\Repositories;

/**
 * Repository baca/tulis data Word Transformation Engine.
 * Data disimpan sebagai file JSON di `resources/data/grammar/`:
 *  - irregular_verbs.json
 *  - irregular_nouns.json
 *  - irregular_adjectives.json
 *  - demonstratives_and_pronouns.json
 */
class GrammarDataRepository
{
    protected string $basePath;

    public function __construct(?string $basePath = null)
    {
        $this->basePath = $basePath ?? resource_path('data/grammar');
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function read(string $filename): array
    {
        $path = $this->path($filename);

        if (! file_exists($path)) {
            return [];
        }

        $data = json_decode(file_get_contents($path), true);

        return is_array($data) ? $data : [];
    }

    public function write(string $filename, array $data): void
    {
        if (! is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }

        file_put_contents(
            $this->path($filename),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n",
        );
    }

    private function path(string $filename): string
    {
        return rtrim($this->basePath, '/\\').DIRECTORY_SEPARATOR.$filename;
    }
}
