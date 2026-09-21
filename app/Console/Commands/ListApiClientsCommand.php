<?php

namespace App\Console\Commands;

use App\Models\ApiClient;
use Illuminate\Console\Command;

class ListApiClientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'client:list 
                            {--active : List only active clients}
                            {--revoked : List only inactive/revoked clients}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered API client services and their credentials status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = ApiClient::query()->orderBy('id');

        if ($this->option('active')) {
            $query->active();
        } elseif ($this->option('revoked')) {
            $query->revoked();
        }

        $clients = $query->get();

        if ($clients->isEmpty()) {
            $this->info('No API clients found matching the specified criteria.');
            return self::SUCCESS;
        }

        $rows = $clients->map(function (ApiClient $client) {
            return [
                $client->id,
                $client->name,
                $client->slug,
                $client->key_prefix ?: 'N/A',
                $client->is_active ? '<fg=green>Active</>' : '<fg=red>Inactive</>',
                $client->last_used_at ? $client->last_used_at->toDateTimeString() : 'Never',
                $client->created_at ? $client->created_at->toDateTimeString() : 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Slug', 'Key Prefix', 'Status', 'Last Used At', 'Created At'],
            $rows
        );

        return self::SUCCESS;
    }
}
