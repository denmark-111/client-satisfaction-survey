<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormOption extends Model
{
    protected $fillable = [
        'category',
        'label',
        'code',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory(\Illuminate\Database\Eloquent\Builder $query, string $category): \Illuminate\Database\Eloquent\Builder
    {
        return $query->active()->where('category', $category)->orderBy('sort_order')->orderBy('label');
    }
}
