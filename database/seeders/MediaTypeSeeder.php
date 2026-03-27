<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MediaTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'DVD',
                'slug' => 'dvd',
                'icon' => 'heroicon-o-film',
                'api_source' => 'omdb',
                'metadata_schema' => json_encode([
                    ['name' => 'director', 'type' => 'text', 'label' => 'Director'],
                    ['name' => 'runtime', 'type' => 'text', 'label' => 'Runtime'],
                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre'],
                    ['name' => 'rating', 'type' => 'text', 'label' => 'Rating'],
                    ['name' => 'region_code', 'type' => 'select', 'label' => 'Region Code', 'options' => ['1', '2', '3', '4', '5', '6', 'All']],
                    ['name' => 'aspect_ratio', 'type' => 'text', 'label' => 'Aspect Ratio'],
                    ['name' => 'languages', 'type' => 'text', 'label' => 'Languages'],
                ]),
            ],
            [
                'name' => 'Blu-ray',
                'slug' => 'bluray',
                'icon' => 'heroicon-o-film',
                'api_source' => 'omdb',
                'metadata_schema' => json_encode([
                    ['name' => 'director', 'type' => 'text', 'label' => 'Director'],
                    ['name' => 'runtime', 'type' => 'text', 'label' => 'Runtime'],
                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre'],
                    ['name' => 'rating', 'type' => 'text', 'label' => 'Rating'],
                    ['name' => 'region_code', 'type' => 'select', 'label' => 'Region Code', 'options' => ['A', 'B', 'C', 'Free']],
                    ['name' => 'resolution', 'type' => 'select', 'label' => 'Resolution', 'options' => ['1080p', '4K UHD']],
                    ['name' => 'disc_count', 'type' => 'text', 'label' => 'Disc Count'],
                ]),
            ],
            [
                'name' => 'VHS',
                'slug' => 'vhs',
                'icon' => 'heroicon-o-video-camera',
                'api_source' => 'omdb',
                'metadata_schema' => json_encode([
                    ['name' => 'director', 'type' => 'text', 'label' => 'Director'],
                    ['name' => 'runtime', 'type' => 'text', 'label' => 'Runtime'],
                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre'],
                    ['name' => 'format', 'type' => 'select', 'label' => 'Format', 'options' => ['NTSC', 'PAL', 'SECAM']],
                ]),
            ],
            [
                'name' => 'Vinyl Record',
                'slug' => 'vinyl',
                'icon' => 'heroicon-o-musical-note',
                'api_source' => 'discogs',
                'metadata_schema' => json_encode([
                    ['name' => 'artist', 'type' => 'text', 'label' => 'Artist', 'required' => true],
                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre'],
                    ['name' => 'label', 'type' => 'text', 'label' => 'Record Label'],
                    ['name' => 'catalog_number', 'type' => 'text', 'label' => 'Catalog Number'],
                    ['name' => 'rpm', 'type' => 'select', 'label' => 'RPM', 'options' => ['33', '45', '78']],
                    ['name' => 'format', 'type' => 'select', 'label' => 'Format', 'options' => ['LP', 'EP', 'Single', 'Double LP', 'Box Set']],
                    ['name' => 'track_listing', 'type' => 'textarea', 'label' => 'Track Listing'],
                ]),
            ],
            [
                'name' => 'CD',
                'slug' => 'cd',
                'icon' => 'heroicon-o-musical-note',
                'api_source' => 'discogs',
                'metadata_schema' => json_encode([
                    ['name' => 'artist', 'type' => 'text', 'label' => 'Artist', 'required' => true],
                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre'],
                    ['name' => 'label', 'type' => 'text', 'label' => 'Record Label'],
                    ['name' => 'catalog_number', 'type' => 'text', 'label' => 'Catalog Number'],
                    ['name' => 'disc_count', 'type' => 'text', 'label' => 'Disc Count'],
                    ['name' => 'track_listing', 'type' => 'textarea', 'label' => 'Track Listing'],
                ]),
            ],
            [
                'name' => 'Cassette',
                'slug' => 'cassette',
                'icon' => 'heroicon-o-musical-note',
                'api_source' => 'discogs',
                'metadata_schema' => json_encode([
                    ['name' => 'artist', 'type' => 'text', 'label' => 'Artist', 'required' => true],
                    ['name' => 'genre', 'type' => 'text', 'label' => 'Genre'],
                    ['name' => 'label', 'type' => 'text', 'label' => 'Record Label'],
                    ['name' => 'tape_type', 'type' => 'select', 'label' => 'Tape Type', 'options' => ['Type I (Normal)', 'Type II (Chrome)', 'Type IV (Metal)']],
                    ['name' => 'track_listing', 'type' => 'textarea', 'label' => 'Track Listing'],
                ]),
            ],
        ];

        foreach ($types as $type) {
            DB::table('media_types')->insert(array_merge($type, [
                'id' => Str::ulid(),
                'family_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
