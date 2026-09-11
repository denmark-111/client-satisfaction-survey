<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Seeder;

class ApiClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'Dairy Loan System',
                'slug' => 'dairy-loan-system',
                'key' => 'css_test_dairy_loan_key_1234567890abcdef',
            ],
            [
                'name' => 'Herd Management Service',
                'slug' => 'herd-management-service',
                'key' => 'css_test_herd_management_key_1234567890abcdef',
            ],
            [
                'name' => 'Test Client Service',
                'slug' => 'test-client',
                'key' => 'css_test_client_key_1234567890abcdef',
            ],
        ];

        foreach ($clients as $client) {
            $payload = ApiClient::generateKeyPayload($client['key']);
            ApiClient::updateOrCreate(
                ['slug' => $client['slug']],
                [
                    'name' => $client['name'],
                    'api_key_hash' => $payload['hash'],
                    'key_prefix' => $payload['prefix'],
                    'is_active' => true,
                ]
            );
        }
    }
}
