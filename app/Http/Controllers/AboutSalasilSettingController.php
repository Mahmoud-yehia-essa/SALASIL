<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;

class AboutSalasilSettingController extends Controller
{
    /**
     * Show About Salasil Management Settings Page
     */
    public function EditSettings()
    {
        $settings = [
            'about_website' => AppSetting::get('about_website', 'https://salasel.com'),
            'about_description' => AppSetting::get('about_description', 'Salasel connects you with trusted carriers to ship anything, anywhere. Book trips, track shipments in real time, and manage everything from one app.'),
            'about_terms_of_service' => AppSetting::get('about_terms_of_service', ''),
            'about_privacy_policy' => AppSetting::get('about_privacy_policy', ''),

            // Social media links
            'social_linkedin_url' => AppSetting::get('social_linkedin_url', 'https://www.linkedin.com/company/salasil-logistics'),
            'social_twitter_url' => AppSetting::get('social_twitter_url', 'https://twitter.com/SalasilGCC'),
            'social_instagram_url' => AppSetting::get('social_instagram_url', 'https://instagram.com/salasil.kw'),
            'social_facebook_url' => AppSetting::get('social_facebook_url', 'https://facebook.com/SalasilLogistics'),
            'social_website_url' => AppSetting::get('social_website_url', 'https://salasel.com'),
        ];

        return view('admin.backend.about.settings', compact('settings'));
    }

    /**
     * Update About Salasil Settings from Admin Panel
     */
    public function UpdateSettings(Request $request)
    {
        $request->validate([
            'about_website' => 'required|string|max:255',
            'about_description' => 'required|string',
            'about_terms_of_service' => 'required|string',
            'about_privacy_policy' => 'required|string',

            'social_linkedin_url' => 'nullable|string|max:255',
            'social_twitter_url' => 'nullable|string|max:255',
            'social_instagram_url' => 'nullable|string|max:255',
            'social_facebook_url' => 'nullable|string|max:255',
            'social_website_url' => 'nullable|string|max:255',
        ]);

        AppSetting::set('about_website', $request->about_website, 'about', 'string', 'Official Website URL');
        AppSetting::set('about_description', $request->about_description, 'about', 'text', 'App Overview description');
        AppSetting::set('about_terms_of_service', $request->about_terms_of_service, 'about', 'text', 'Terms of Service text');
        AppSetting::set('about_privacy_policy', $request->about_privacy_policy, 'about', 'text', 'Privacy Policy text');

        // Social links
        AppSetting::set('social_linkedin_url', $request->input('social_linkedin_url', ''), 'social', 'string', 'LinkedIn URL');
        AppSetting::set('social_twitter_url', $request->input('social_twitter_url', ''), 'social', 'string', 'Twitter/X URL');
        AppSetting::set('social_instagram_url', $request->input('social_instagram_url', ''), 'social', 'string', 'Instagram URL');
        AppSetting::set('social_facebook_url', $request->input('social_facebook_url', ''), 'social', 'string', 'Facebook URL');
        AppSetting::set('social_website_url', $request->input('social_website_url', ''), 'social', 'string', 'Website URL');

        $notification = [
            'message' => 'About Salasil & Social Media settings updated successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
