<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;

class RotateApiClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:rotate 
                            {identifier : The ID or slug of the API client}
                            {--key= : Optional specific plain replacement API key}
                            {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate credentials for an API client, issuing a new API key and invalidating the previous one';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = (string) $this->argument('identifier');
        $client = ApiClient::findByIdentifier($identifier);

        if (! $client) {
            $this->error("API client [{$identifier}] not found.");
            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to rotate the API key for [{$client->name}] ({$client->slug})? The current key will be invalidated immediately.")) {
            $this->info('Key rotation cancelled.');
            return self::SUCCESS;
        }

        $customKey = $this->option('key') ? (string) $this->option('key') : null;
        $newPlainKey = $client->rotateKey($customKey);

        // Refresh model state
        $client->refresh();

        $this->info("API key for client [{$client->name}] rotated successfully!");
        $this->newLine();
        $this->table(
            ['ID', 'Name', 'Slug', 'New Key Prefix', 'Status'],
            [
                [$client->id, $client->name, $client->slug, $client->key_prefix, $client->is_active ? 'Active' : 'Inactive'],
            ]
        );
        $this->newLine();
        $this->warn('SAVE THIS NEW API KEY NOW. It will not be shown again in plain text:');
        $this->line("<fg=green;options=bold>{$newPlainKey}</>");
        $this->newLine();
        $this->line('<fg=yellow>Notice:</> The previous API key for this client has been permanently invalidated.');
        $this->newLine();

        return self::SUCCESS;
    }
}
