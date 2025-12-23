<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default;
            
            return match($setting->type) {
                'int' => (int) $setting->value,
                'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                'float' => (float) $setting->value,
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        if ($type === 'json') $value = json_encode($value);
        if ($type === 'bool') $value = $value ? '1' : '0';
        
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type, 'group' => $group]
        );
        
        Cache::forget("setting.{$key}");
    }

    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }
}
