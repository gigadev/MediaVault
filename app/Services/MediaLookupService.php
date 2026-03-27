<?php

namespace App\Services;

use App\Enums\MediaApiSource;
use App\Models\MediaType;

class MediaLookupService
{
    public function __construct(
        private DiscogsService $discogs,
        private OmdbService $omdb,
    ) {}

    public function search(string $query, MediaType $mediaType, ?int $year = null): array
    {
        return match ($mediaType->api_source) {
            MediaApiSource::Discogs => $this->discogs->search($query),
            MediaApiSource::Omdb => $this->omdb->search($query, $year),
            default => [],
        };
    }

    public function searchByBarcode(string $barcode): array
    {
        // Try Discogs first (covers music media)
        $results = $this->discogs->searchByBarcode($barcode);

        if (! empty($results)) {
            return $results;
        }

        // Could add UPC database lookup here as fallback
        return [];
    }

    public function getDetails(string $sourceId, MediaApiSource $source): ?array
    {
        return match ($source) {
            MediaApiSource::Discogs => $this->discogs->getRelease((int) $sourceId),
            MediaApiSource::Omdb => $this->omdb->getById($sourceId),
            default => null,
        };
    }
}
