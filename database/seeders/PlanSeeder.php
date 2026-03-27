<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'id' => Str::ulid(),
                'name' => 'Free',
                'slug' => 'free',
                'stripe_price_id' => null,
                'max_media_items' => 50,
                'max_family_members' => 2,
                'can_cross_family_share' => false,
                'can_use_api_lookup' => false,
                'can_create_custom_media_types' => false,
                'price_monthly' => 0,
                'price_yearly' => null,
                'features' => json_encode([
                    'Up to 50 media items',
                    '2 family members',
                    '3 collections',
                    'Family checkout tracking',
                ]),
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::ulid(),
                'name' => 'Basic',
                'slug' => 'basic',
                'stripe_price_id' => null, // Set after Stripe product creation
                'max_media_items' => 500,
                'max_family_members' => 5,
                'can_cross_family_share' => true,
                'can_use_api_lookup' => true,
                'can_create_custom_media_types' => false,
                'price_monthly' => 500,
                'price_yearly' => 5000,
                'features' => json_encode([
                    'Up to 500 media items',
                    '5 family members',
                    '20 collections',
                    'Cross-family sharing',
                    '10 API lookups/day',
                    'Export to spreadsheet',
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::ulid(),
                'name' => 'Premium',
                'slug' => 'premium',
                'stripe_price_id' => null, // Set after Stripe product creation
                'max_media_items' => null,
                'max_family_members' => 15,
                'can_cross_family_share' => true,
                'can_use_api_lookup' => true,
                'can_create_custom_media_types' => true,
                'price_monthly' => 1200,
                'price_yearly' => 12000,
                'features' => json_encode([
                    'Unlimited media items',
                    '15 family members',
                    'Unlimited collections',
                    'Cross-family sharing',
                    'Unlimited API lookups',
                    'Custom media types',
                    'Export to spreadsheet',
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('plans')->insert($plans);
    }
}
