<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group_name',
        'description',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Scope a query to only include public settings.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to filter by group.
     */
    public function scopeGroup($query, $group)
    {
        return $query->where('group_name', $group);
    }

    /**
     * Get setting value with proper casting.
     */
    public function getValueAttribute($value)
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $value;
            case 'number':
                return is_numeric($value) ? (float) $value : $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * Set setting value with proper formatting.
     */
    public function setValueAttribute($value)
    {
        switch ($this->type) {
            case 'json':
                $this->attributes['value'] = json_encode($value);
                break;
            case 'boolean':
                $this->attributes['value'] = $value ? '1' : '0';
                break;
            default:
                $this->attributes['value'] = (string) $value;
        }
    }

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value, string $type = 'string'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
            ]
        );
    }
}