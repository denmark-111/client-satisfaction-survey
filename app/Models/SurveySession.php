<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Relations\HasOne;

class SurveySession extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date_service_availed' => 'date',
        'age' => 'integer',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function response(): HasOne
    {
        return $this->hasOne(SurveyResponse::class, 'survey_session_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function center(): BelongsTo
    {
        return $this->belongsTo(FormOption::class, 'center_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(FormOption::class, 'region_id');
    }

    public function webhookDeliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'survey_session_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->completed_at !== null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->isCompleted() && !$this->isExpired();
    }

    public function isFieldLocked(string $field): bool
    {
        return $this->getAttribute($field) !== null;
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
