<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DiscogsService
{
    private string $baseUrl = 'https://api.discogs.com';

    public function search(string $query, ?string $type = null): array
    {
        $cacheKey = 'discogs_search_' . md5($query . $type);

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($query, $type) {
            $params = [
                'q' => $query,
                'token' => config('services.discogs.token'),
                'per_page' => 10,
            ];

            if ($type) {
                $params['type'] = $type;
            }

            $response = Http::withHeaders([
                'User-Agent' => 'MediaVault/1.0',
            ])->get("{$this->baseUrl}/database/search", $params);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('results', []))
                ->map(fn (array $result) => $this->normalizeSearchResult($result))
                ->toArray();
        });
    }

    public function searchByBarcode(string $barcode): array
    {
        $cacheKey = 'discogs_barcode_' . $barcode;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($barcode) {
            $response = Http::withHeaders([
                'User-Agent' => 'MediaVault/1.0',
            ])->get("{$this->baseUrl}/database/search", [
                'barcode' => $barcode,
                'token' => config('services.discogs.token'),
                'per_page' => 5,
            ]);

            if ($response->failed()) {
                return [];
            }

            return collect($response->json('results', []))
                ->map(fn (array $result) => $this->normalizeSearchResult($result))
                ->toArray();
        });
    }

    public function getRelease(int $releaseId): ?array
    {
        $cacheKey = 'discogs_release_' . $releaseId;

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($releaseId) {
            $response = Http::withHeaders([
                'User-Agent' => 'MediaVault/1.0',
            ])->get("{$this->baseUrl}/releases/{$releaseId}", [
                'token' => config('services.discogs.token'),
            ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();

            return [
                'title' => $data['title'] ?? '',
                'year' => $data['year'] ?? null,
                'cover_image' => $data['images'][0]['uri'] ?? null,
                'metadata' => [
                    'artist' => collect($data['artists'] ?? [])->pluck('name')->implode(', '),
                    'genre' => collect($data['genres'] ?? [])->implode(', '),
                    'label' => collect($data['labels'] ?? [])->pluck('name')->first(),
                    'catalog_number' => collect($data['labels'] ?? [])->pluck('catno')->first(),
                    'track_listing' => collect($data['tracklist'] ?? [])
                        ->map(fn ($t) => ($t['position'] ?? '') . '. ' . ($t['title'] ?? ''))
                        ->implode("\n"),
                ],
                'barcode' => collect($data['identifiers'] ?? [])
                    ->firstWhere('type', 'Barcode')['value'] ?? null,
            ];
        });
    }

    private function normalizeSearchResult(array $result): array
    {
        return [
            'source' => 'discogs',
            'source_id' => $result['id'] ?? null,
            'title' => $result['title'] ?? '',
            'year' => $result['year'] ?? null,
            'cover_image' => $result['cover_image'] ?? $result['thumb'] ?? null,
            'type' => $result['type'] ?? 'release',
            'format' => collect($result['format'] ?? [])->implode(', '),
        ];
    }
}
