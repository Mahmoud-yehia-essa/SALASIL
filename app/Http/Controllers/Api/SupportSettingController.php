<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class SupportSettingController extends Controller
{
    /**
     * Get active support contact channels and settings for mobile app.
     */
    public function getSettings()
    {
        $settings = [
            'call' => [
                'enabled' => (bool) AppSetting::get('support_call_enabled', true),
                'phone' => (string) AppSetting::get('support_call_phone', '+965 1800 0000'),
                'badge' => (string) AppSetting::get('support_call_badge', 'Toll Free'),
            ],
            'email' => [
                'enabled' => (bool) AppSetting::get('support_email_enabled', true),
                'email' => (string) AppSetting::get('support_email_address', 'support@salasel.com'),
            ],
            'whatsapp' => [
                'enabled' => (bool) AppSetting::get('support_whatsapp_enabled', true),
                'number' => (string) AppSetting::get('support_whatsapp_number', '+965 9876 5432'),
            ],
            'live_chat' => [
                'enabled' => (bool) AppSetting::get('support_livechat_enabled', false),
                'badge' => (string) AppSetting::get('support_livechat_badge', 'Instant'),
            ],
        ];

        return response()->json([
            'status' => true,
            'message' => 'Support settings retrieved successfully',
            'data' => $settings,
        ], 200);
    }
}
