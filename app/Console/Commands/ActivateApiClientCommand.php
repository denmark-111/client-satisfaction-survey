<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;

class ActivateApiClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:activate 
                            {identifier : The ID or slug of the API client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate (re-enable) a previously revoked or inactive API client';

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

        if ($client->is_active) {
            $this->info("API client [{$client->name}] ({$client->slug}) is already active.");
            return self::SUCCESS;
        }

        $client->activate();

        $this->info("API client [{$client->name}] ({$client->slug}) has been activated successfully.");

        return self::SUCCESS;
    }
}
