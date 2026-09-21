<?php

namespace Tests\Feature;

use App\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiClientManagementCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_client_list_command_displays_all_clients(): void
    {
        $this->artisan('client:list')
            ->expectsOutputToContain('dairy-loan-system')
            ->expectsOutputToContain('herd-management-service')
            ->expectsOutputToContain('Active')
            ->assertSuccessful();
    }

    public function test_client_list_command_filters_active_and_revoked(): void
    {
        // Revoke one client
        $client = ApiClient::where('slug', 'dairy-loan-system')->first();
        $client->revoke();

        // Active filter should only show active clients
        $this->artisan('client:list', ['--active' => true])
            ->expectsOutputToContain('herd-management-service')
            ->doesntExpectOutput('dairy-loan-system')
            ->assertSuccessful();

        // Revoked filter should only show revoked clients
        $this->artisan('client:list', ['--revoked' => true])
            ->expectsOutputToContain('dairy-loan-system')
            ->doesntExpectOutput('herd-management-service')
            ->assertSuccessful();
    }

    public function test_client_revoke_command_deactivates_client_by_slug_and_blocks_api_access(): void
    {
        $client = ApiClient::where('slug', 'dairy-loan-system')->first();
        $this->assertTrue($client->is_active);

        // API request succeeds before revocation
        $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'Before Revoke Test',
        ])->assertStatus(201);

        // Run revoke command with --force
        $this->artisan('client:revoke', [
            'identifier' => 'dairy-loan-system',
            '--force' => true,
        ])->expectsOutputToContain('has been revoked successfully')
          ->assertSuccessful();

        $client->refresh();
        $this->assertFalse($client->is_active);

        // API request must now be rejected with 403 Forbidden
        $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'After Revoke Test',
        ])->assertStatus(403)
          ->assertJson([
              'success' => false,
              'message' => 'Client service is inactive or unauthorized.',
          ]);
    }

    public function test_client_revoke_command_deactivates_client_by_id(): void
    {
        $client = ApiClient::where('slug', 'herd-management-service')->first();

        $this->artisan('client:revoke', [
            'identifier' => (string) $client->id,
            '--force' => true,
        ])->assertSuccessful();

        $client->refresh();
        $this->assertFalse($client->is_active);
    }

    public function test_client_revoke_command_handles_non_existent_client(): void
    {
        $this->artisan('client:revoke', [
            'identifier' => 'non-existent-client',
            '--force' => true,
        ])->expectsOutputToContain('API client [non-existent-client] not found.')
          ->assertFailed();
    }

    public function test_client_activate_command_reactivates_client_and_restores_api_access(): void
    {
        $client = ApiClient::where('slug', 'dairy-loan-system')->first();
        $client->revoke();
        $this->assertFalse($client->is_active);

        // API is blocked while inactive
        $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'Inactive Test',
        ])->assertStatus(403);

        // Run activate command
        $this->artisan('client:activate', [
            'identifier' => 'dairy-loan-system',
        ])->expectsOutputToContain('has been activated successfully')
          ->assertSuccessful();

        $client->refresh();
        $this->assertTrue($client->is_active);

        // API request should now succeed
        $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'Restored Test',
        ])->assertStatus(201);
    }

    public function test_client_activate_command_handles_non_existent_client(): void
    {
        $this->artisan('client:activate', [
            'identifier' => 'non-existent-client',
        ])->expectsOutputToContain('API client [non-existent-client] not found.')
          ->assertFailed();
    }

    public function test_client_rotate_command_invalidates_old_key_and_authorizes_new_key(): void
    {
        $client = ApiClient::where('slug', 'dairy-loan-system')->first();
        $oldHash = $client->api_key_hash;

        $newKey = 'css_rotated_custom_key_0987654321fedcba';

        $this->artisan('client:rotate', [
            'identifier' => 'dairy-loan-system',
            '--key' => $newKey,
            '--force' => true,
        ])->expectsOutputToContain('rotated successfully')
          ->expectsOutputToContain($newKey)
          ->assertSuccessful();

        $client->refresh();
        $this->assertNotEquals($oldHash, $client->api_key_hash);
        $this->assertEquals(hash('sha256', $newKey), $client->api_key_hash);

        // Old key should now be rejected with 401
        $this->withHeaders([
            'X-API-Key' => 'css_test_dairy_loan_key_1234567890abcdef',
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'Old Key Test',
        ])->assertStatus(401);

        // New key should succeed with 201
        $this->withHeaders([
            'X-API-Key' => $newKey,
        ])->postJson(route('api.survey-sessions.store'), [
            'respondent_name' => 'New Key Test',
        ])->assertStatus(201);
    }

    public function test_client_rotate_command_handles_non_existent_client(): void
    {
        $this->artisan('client:rotate', [
            'identifier' => 'non-existent-client',
            '--force' => true,
        ])->expectsOutputToContain('API client [non-existent-client] not found.')
          ->assertFailed();
    }
}
