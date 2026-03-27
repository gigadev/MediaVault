<?php

namespace App\Services;

use App\Models\Family;
use App\Models\Plan;

class PlanLimitService
{
    public function getPlan(Family $family): Plan
    {
        // Check if family has an active subscription
        if ($family->subscribed('default')) {
            $stripePriceId = $family->subscription('default')->stripe_price;
            $plan = Plan::where('stripe_price_id', $stripePriceId)->first();

            if ($plan) {
                return $plan;
            }
        }

        // Default to free plan
        return Plan::where('slug', 'free')->firstOrFail();
    }

    public function canAddMediaItem(Family $family): bool
    {
        $plan = $this->getPlan($family);

        if ($plan->max_media_items === null) {
            return true;
        }

        return $family->mediaItems()->count() < $plan->max_media_items;
    }

    public function canAddFamilyMember(Family $family): bool
    {
        $plan = $this->getPlan($family);

        if ($plan->max_family_members === null) {
            return true;
        }

        return $family->members()->count() < $plan->max_family_members;
    }

    public function canShareCrossFamily(Family $family): bool
    {
        return $this->getPlan($family)->can_cross_family_share;
    }

    public function canUseApiLookup(Family $family): bool
    {
        return $this->getPlan($family)->can_use_api_lookup;
    }

    public function canCreateCustomMediaType(Family $family): bool
    {
        return $this->getPlan($family)->can_create_custom_media_types;
    }

    public function getUsageSummary(Family $family): array
    {
        $plan = $this->getPlan($family);

        return [
            'plan' => $plan,
            'media_items' => [
                'used' => $family->mediaItems()->count(),
                'limit' => $plan->max_media_items,
            ],
            'family_members' => [
                'used' => $family->members()->count(),
                'limit' => $plan->max_family_members,
            ],
        ];
    }
}
