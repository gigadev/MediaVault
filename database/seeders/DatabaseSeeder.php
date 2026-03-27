<?php

namespace Database\Seeders;

use App\Enums\FamilyMemberRole;
use App\Enums\MediaCondition;
use App\Models\Checkout;
use App\Models\Collection;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\MediaItem;
use App\Models\MediaType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed plans and media types first
        $this->call([
            PlanSeeder::class,
            MediaTypeSeeder::class,
        ]);

        // Create demo user and family
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@mediavault.test',
            'password' => bcrypt('password'),
        ]);

        $family = Family::create([
            'name' => 'The Demo Family',
            'slug' => 'demo-family',
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role' => FamilyMemberRole::Owner,
            'joined_at' => now(),
        ]);

        // Add a second family member
        $member = User::factory()->create([
            'name' => 'Family Member',
            'email' => 'member@mediavault.test',
            'password' => bcrypt('password'),
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $member->id,
            'role' => FamilyMemberRole::Member,
            'joined_at' => now(),
        ]);

        // Create some demo media items
        $mediaTypes = MediaType::whereNull('family_id')->get()->keyBy('slug');
        $conditions = MediaCondition::cases();

        $dvds = [
            ['title' => 'The Shawshank Redemption', 'year' => 1994],
            ['title' => 'The Dark Knight', 'year' => 2008],
            ['title' => 'Inception', 'year' => 2010],
            ['title' => 'Pulp Fiction', 'year' => 1994],
            ['title' => 'The Matrix', 'year' => 1999],
        ];

        foreach ($dvds as $dvd) {
            MediaItem::create([
                'family_id' => $family->id,
                'media_type_id' => $mediaTypes['dvd']->id,
                'added_by_user_id' => $user->id,
                'title' => $dvd['title'],
                'year' => $dvd['year'],
                'condition' => $conditions[array_rand($conditions)],
                'metadata' => ['genre' => 'Drama', 'director' => 'Various'],
                'is_available' => true,
                'is_shareable' => true,
            ]);
        }

        $vinyls = [
            ['title' => 'Abbey Road', 'year' => 1969, 'artist' => 'The Beatles'],
            ['title' => 'Dark Side of the Moon', 'year' => 1973, 'artist' => 'Pink Floyd'],
            ['title' => 'Rumours', 'year' => 1977, 'artist' => 'Fleetwood Mac'],
            ['title' => 'Kind of Blue', 'year' => 1959, 'artist' => 'Miles Davis'],
        ];

        foreach ($vinyls as $vinyl) {
            MediaItem::create([
                'family_id' => $family->id,
                'media_type_id' => $mediaTypes['vinyl']->id,
                'added_by_user_id' => $user->id,
                'title' => $vinyl['title'],
                'year' => $vinyl['year'],
                'condition' => $conditions[array_rand($conditions)],
                'metadata' => ['artist' => $vinyl['artist'], 'genre' => 'Rock', 'rpm' => '33'],
                'is_available' => true,
                'is_shareable' => true,
            ]);
        }

        // Create a collection
        $collection = Collection::create([
            'family_id' => $family->id,
            'created_by_user_id' => $user->id,
            'name' => 'Favorites',
            'description' => 'Our family favorites',
            'is_public' => false,
        ]);

        $items = MediaItem::where('family_id', $family->id)->take(5)->get();
        $collection->mediaItems()->attach($items->pluck('id'));

        // Create a checkout
        $itemToCheckout = MediaItem::where('family_id', $family->id)->first();
        if ($itemToCheckout) {
            Checkout::create([
                'family_id' => $family->id,
                'media_item_id' => $itemToCheckout->id,
                'checked_out_to_user_id' => $member->id,
                'checked_out_by_user_id' => $user->id,
                'checked_out_at' => now()->subDays(3),
                'due_at' => now()->addDays(4),
            ]);
            $itemToCheckout->update(['is_available' => false]);
        }

        // Create a second family for cross-family features
        $user2 = User::factory()->create([
            'name' => 'Neighbor User',
            'email' => 'neighbor@mediavault.test',
            'password' => bcrypt('password'),
        ]);

        $family2 = Family::create([
            'name' => 'The Neighbors',
            'slug' => 'the-neighbors',
            'visibility' => \App\Enums\FamilyVisibility::PublicBrowsable,
        ]);

        FamilyMember::create([
            'family_id' => $family2->id,
            'user_id' => $user2->id,
            'role' => FamilyMemberRole::Owner,
            'joined_at' => now(),
        ]);

        // Add some items to neighbor family
        $cds = [
            ['title' => 'Thriller', 'year' => 1982, 'artist' => 'Michael Jackson'],
            ['title' => 'Back in Black', 'year' => 1980, 'artist' => 'AC/DC'],
            ['title' => 'Nevermind', 'year' => 1991, 'artist' => 'Nirvana'],
        ];

        foreach ($cds as $cd) {
            MediaItem::create([
                'family_id' => $family2->id,
                'media_type_id' => $mediaTypes['cd']->id,
                'added_by_user_id' => $user2->id,
                'title' => $cd['title'],
                'year' => $cd['year'],
                'condition' => $conditions[array_rand($conditions)],
                'metadata' => ['artist' => $cd['artist'], 'genre' => 'Rock'],
                'is_available' => true,
                'is_shareable' => true,
            ]);
        }
    }
}
