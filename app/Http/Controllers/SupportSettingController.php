<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;

class SupportSettingController extends Controller
{
    /**
     * Show Support & Help Center Settings Page
     */
    public function EditSettings()
    {
        $settings = [
            'support_call_phone' => AppSetting::get('support_call_phone', '+965 1800 0000'),
            'support_call_badge' => AppSetting::get('support_call_badge', 'Toll Free'),
            'support_call_enabled' => AppSetting::get('support_call_enabled', true),

            'support_email_address' => AppSetting::get('support_email_address', 'support@salasel.com'),
            'support_email_enabled' => AppSetting::get('support_email_enabled', true),

            'support_whatsapp_number' => AppSetting::get('support_whatsapp_number', '+965 9876 5432'),
            'support_whatsapp_enabled' => AppSetting::get('support_whatsapp_enabled', true),

            'support_livechat_enabled' => AppSetting::get('support_livechat_enabled', false),
            'support_livechat_badge' => AppSetting::get('support_livechat_badge', 'Instant'),
        ];

        return view('admin.backend.support.settings', compact('settings'));
    }

    /**
     * Update Support Settings from Admin Panel
     */
    public function UpdateSettings(Request $request)
    {
        $request->validate([
            'support_call_phone' => 'nullable|string|max:50',
            'support_call_badge' => 'nullable|string|max:50',
            'support_email_address' => 'nullable|email|max:100',
            'support_whatsapp_number' => 'nullable|string|max:50',
            'support_livechat_badge' => 'nullable|string|max:50',
        ]);

        AppSetting::set('support_call_phone', $request->input('support_call_phone', ''), 'support', 'string', 'Customer Call Phone Number');
        AppSetting::set('support_call_badge', $request->input('support_call_badge', 'Toll Free'), 'support', 'string', 'Call Us Badge Text');
        AppSetting::set('support_call_enabled', $request->has('support_call_enabled') ? '1' : '0', 'support', 'boolean', 'Enable/Disable Call Us option');

        AppSetting::set('support_email_address', $request->input('support_email_address', ''), 'support', 'string', 'Customer Support Email');
        AppSetting::set('support_email_enabled', $request->has('support_email_enabled') ? '1' : '0', 'support', 'boolean', 'Enable/Disable Email option');

        AppSetting::set('support_whatsapp_number', $request->input('support_whatsapp_number', ''), 'support', 'string', 'WhatsApp Support Phone Number');
        AppSetting::set('support_whatsapp_enabled', $request->has('support_whatsapp_enabled') ? '1' : '0', 'support', 'boolean', 'Enable/Disable WhatsApp option');

        AppSetting::set('support_livechat_enabled', $request->has('support_livechat_enabled') ? '1' : '0', 'support', 'boolean', 'Enable/Disable Live Chat option');
        AppSetting::set('support_livechat_badge', $request->input('support_livechat_badge', 'Instant'), 'support', 'string', 'Live Chat Badge Text');

        $notification = [
            'message' => 'Support & Help Center settings updated successfully!',
            'alert-type' => 'success'
        ];

        return redirect()->back()->with($notification);
    }
}
