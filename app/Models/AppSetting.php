<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'type',
        'description',
    ];

    /**
     * Get a setting by key with optional fallback.
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('setting_key', $key)->first();
        if (!$setting) {
            return $default;
        }

        if ($setting->type === 'boolean') {
            return filter_var($setting->setting_value, FILTER_VALIDATE_BOOLEAN);
        }

        if ($setting->type === 'json') {
            return json_decode($setting->setting_value, true);
        }

        return $setting->setting_value;
    }

    /**
     * Set a setting key and value.
     */
    public static function set($key, $value, $group = 'general', $type = 'string', $description = null)
    {
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = 'boolean';
        } elseif (is_array($value)) {
            $value = json_encode($value);
            $type = 'json';
        }

        return static::updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => (string) $value,
                'setting_group' => $group,
                'type' => $type,
                'description' => $description,
            ]
        );
    }
}
