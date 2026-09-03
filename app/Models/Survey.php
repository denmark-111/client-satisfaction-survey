<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'agreed_to_participate' => 'boolean',
        'date_service_availed' => 'date',
        'age' => 'integer',
        'overall_satisfaction' => 'integer',
        'cc1_awareness' => 'integer',
        'cc2_visibility' => 'integer',
        'cc3_helpfulness' => 'integer',
    ];

    public function center(): BelongsTo
    {
        return $this->belongsTo(FormOption::class, 'center_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(FormOption::class, 'region_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(FormOption::class, 'service_id');
    }
}
