<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;

class CreateApiClientCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:create 
                            {name : The human-readable name of the client service}
                            {--slug= : Optional custom slug identifier}
                            {--key= : Optional specific plain API key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a permitted client service and generate an API key';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = (string) $this->argument('name');
        $slug = $this->option('slug') ? (string) $this->option('slug') : null;
        $customKey = $this->option('key') ? (string) $this->option('key') : null;

        $result = ApiClient::createWithKey($name, $slug, $customKey);
        $client = $result['client'];
        $plainKey = $result['plain_key'];

        $this->info("Client service [{$client->name}] registered successfully!");
        $this->newLine();
        $this->table(
            ['ID', 'Name', 'Slug', 'Key Prefix', 'Status'],
            [
                [$client->id, $client->name, $client->slug, $client->key_prefix, $client->is_active ? 'Active' : 'Inactive'],
            ]
        );
        $this->newLine();
        $this->warn('SAVE THIS API KEY NOW. It will not be shown again in plain text:');
        $this->line("<fg=green;options=bold>{$plainKey}</>");
        $this->newLine();

        return self::SUCCESS;
    }
}
