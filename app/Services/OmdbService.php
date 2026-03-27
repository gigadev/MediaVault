<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OmdbService
{
    private string $baseUrl = 'https://www.omdbapi.com';

    public function search(string $title, ?int $year = null): array
    {
        $cacheKey = 'omdb_search_' . md5($title . $year);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($title, $year) {
            $params = [
                'apikey' => config('services.omdb.api_key'),
                's' => $title,
            ];

            if ($year) {
                $params['y'] = $year;
            }

            $response = Http::get($this->baseUrl, $params);

            if ($response->failed() || $response->json('Response') === 'False') {
                return [];
            }

            return collect($response->json('Search', []))
                ->map(fn (array $result) => $this->normalizeSearchResult($result))
                ->toArray();
        });
    }

    public function getById(string $imdbId): ?array
    {
        $cacheKey = 'omdb_detail_' . $imdbId;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($imdbId) {
            $response = Http::get($this->baseUrl, [
                'apikey' => config('services.omdb.api_key'),
                'i' => $imdbId,
                'plot' => 'short',
            ]);

            if ($response->failed() || $response->json('Response') === 'False') {
                return null;
            }

            $data = $response->json();

            return [
                'title' => $data['Title'] ?? '',
                'year' => isset($data['Year']) ? (int) $data['Year'] : null,
                'description' => $data['Plot'] ?? null,
                'cover_image' => ($data['Poster'] ?? 'N/A') !== 'N/A' ? $data['Poster'] : null,
                'metadata' => [
                    'director' => $data['Director'] ?? null,
                    'runtime' => $data['Runtime'] ?? null,
                    'genre' => $data['Genre'] ?? null,
                    'rating' => $data['Rated'] ?? null,
                    'languages' => $data['Language'] ?? null,
                ],
                'barcode' => null,
            ];
        });
    }

    private function normalizeSearchResult(array $result): array
    {
        return [
            'source' => 'omdb',
            'source_id' => $result['imdbID'] ?? null,
            'title' => $result['Title'] ?? '',
            'year' => isset($result['Year']) ? (int) $result['Year'] : null,
            'cover_image' => ($result['Poster'] ?? 'N/A') !== 'N/A' ? $result['Poster'] : null,
            'type' => $result['Type'] ?? 'movie',
        ];
    }
}
