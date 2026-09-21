<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;

class RevokeApiClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:revoke 
                            {identifier : The ID or slug of the API client}
                            {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke (deactivate) an API client service and block its API access';

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

        if (! $client->is_active) {
            $this->warn("API client [{$client->name}] ({$client->slug}) is already revoked/inactive.");
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Are you sure you want to revoke API access for [{$client->name}] ({$client->slug})?")) {
            $this->info('Revocation cancelled.');
            return self::SUCCESS;
        }

        $client->revoke();

        $this->warn("API client [{$client->name}] ({$client->slug}) has been revoked successfully.");
        $this->line('Any API requests using this client key will now be rejected with HTTP 403.');

        return self::SUCCESS;
    }
}
