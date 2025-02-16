<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $keyType = 'integer';
    public $incrementing = false;
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ]; // try to remove Dayjs later

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot('participated_at')
            ->withTimestamps();
    }


    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }


    // ask adam if you can code by your style using Validator class later
    public function rules($action = 'create'): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_time' => ['nullable', 'date'],
            'end_time' => ['nullable', 'date', 'after:start_time'],
            'location' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:0'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],

        ];

        if ($action === 'create') {
            $rules['creator_id'] = ['required', 'integer', 'exists:users,id'];
        }

        if ($action === 'update') {
            $rules['title'] = 'sometimes|string|max:255';
            $rules['start_time'] = 'nullable|date';
            $rules['end_time'] = 'nullable|date|after:start_time';
            $rules['capacity'] = 'nullable|integer|min:0';
        }

        return $rules;
    }
}
