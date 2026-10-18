<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class AboutSalasilSettingController extends Controller
{
    /**
     * Get dynamic About Salasil settings and social channels for mobile app.
     */
    public function getSettings()
    {
        $website = (string) AppSetting::get('about_website', 'https://salasel.com');
        $description = (string) AppSetting::get('about_description', 'Salasel connects you with trusted carriers to ship anything, anywhere. Book trips, track shipments in real time, and manage everything from one app.');
        $terms = (string) AppSetting::get('about_terms_of_service', '');
        $privacy = (string) AppSetting::get('about_privacy_policy', '');

        $socialChannels = [
            [
                'platform' => 'linkedin',
                'title' => 'LinkedIn',
                'handle' => 'salasil-logistics',
                'url' => (string) AppSetting::get('social_linkedin_url', 'https://www.linkedin.com/company/salasil-logistics'),
            ],
            [
                'platform' => 'twitter',
                'title' => 'X (Twitter)',
                'handle' => '@SalasilGCC',
                'url' => (string) AppSetting::get('social_twitter_url', 'https://twitter.com/SalasilGCC'),
            ],
            [
                'platform' => 'instagram',
                'title' => 'Instagram',
                'handle' => '@salasil.kw',
                'url' => (string) AppSetting::get('social_instagram_url', 'https://instagram.com/salasil.kw'),
            ],
            [
                'platform' => 'facebook',
                'title' => 'Facebook',
                'handle' => 'SalasilLogistics',
                'url' => (string) AppSetting::get('social_facebook_url', 'https://facebook.com/SalasilLogistics'),
            ],
            [
                'platform' => 'website',
                'title' => 'Website',
                'handle' => 'salasel.com',
                'url' => (string) AppSetting::get('social_website_url', $website),
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'About Salasil settings retrieved successfully',
            'data' => [
                'website' => $website,
                'description' => $description,
                'terms_of_service' => $terms,
                'privacy_policy' => $privacy,
                'social_channels' => $socialChannels,
            ],
        ], 200);
    }
}
