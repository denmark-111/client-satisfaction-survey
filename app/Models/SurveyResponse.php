<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $table = 'survey_responses';

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

    public function session(): BelongsTo
    {
        return $this->belongsTo(SurveySession::class, 'survey_session_id');
    }

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
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function webhookDeliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'survey_response_id');
    }
}
