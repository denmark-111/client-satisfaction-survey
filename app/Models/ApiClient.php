<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiClient extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Generate a new API key and its hash details.
     *
     * @return array{plain_key: string, hash: string, prefix: string}
     */
    public static function generateKeyPayload(?string $customKey = null): array
    {
        $plainKey = $customKey ?: 'css_live_' . Str::random(48);
        $hash = hash('sha256', $plainKey);
        $prefix = substr($plainKey, 0, 12) . '...';

        return [
            'plain_key' => $plainKey,
            'hash' => $hash,
            'prefix' => $prefix,
        ];
    }

    /**
     * Create a new client record with a generated or custom plain API key.
     *
     * @return array{client: self, plain_key: string}
     */
    public static function createWithKey(string $name, ?string $slug = null, ?string $customKey = null): array
    {
        $payload = self::generateKeyPayload($customKey);
        $slug = $slug ?: Str::slug($name);

        $client = self::create([
            'name' => $name,
            'slug' => $slug,
            'api_key_hash' => $payload['hash'],
            'key_prefix' => $payload['prefix'],
            'is_active' => true,
        ]);

        return [
            'client' => $client,
            'plain_key' => $payload['plain_key'],
        ];
    }

    /**
     * Find an active API client by its plain key.
     */
    public static function findByPlainKey(string $plainKey): ?self
    {
        $hash = hash('sha256', trim($plainKey));

        return self::where('api_key_hash', $hash)->first();
    }

    /**
     * Find an API client by its ID or slug identifier.
     */
    public static function findByIdentifier(string|int $identifier): ?self
    {
        if (is_numeric($identifier)) {
            $client = self::find($identifier);
            if ($client) {
                return $client;
            }
        }

        return self::where('slug', (string) $identifier)->first();
    }

    /**
     * Rotate the API key for this client and return the new plain key.
     */
    public function rotateKey(?string $customKey = null): string
    {
        $payload = self::generateKeyPayload($customKey);

        $this->update([
            'api_key_hash' => $payload['hash'],
            'key_prefix' => $payload['prefix'],
        ]);

        return $payload['plain_key'];
    }

    /**
     * Revoke / deactivate this client.
     */
    public function revoke(): bool
    {
        return (bool) $this->update(['is_active' => false]);
    }

    /**
     * Activate / reactivate this client.
     */
    public function activate(): bool
    {
        return (bool) $this->update(['is_active' => true]);
    }

    /**
     * Scope a query to only include active clients.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include revoked / inactive clients.
     */
    public function scopeRevoked($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Record last usage timestamp.
     */
    public function recordUsage(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
