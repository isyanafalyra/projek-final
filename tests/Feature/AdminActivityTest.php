<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Port;
use App\Models\Country;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminActivityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that an admin CRUD action is automatically logged.
     */
    public function test_admin_crud_action_is_logged()
    {
        // 1. Setup Admin User & Country
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        $country = Country::create([
            'name' => 'Indonesia',
            'iso_code' => 'ID',
            'region' => 'East Asia & Pacific',
            'currency_code' => 'IDR',
            'income_level' => 'Upper middle income'
        ]);

        // 2. Perform Port Store Request
        $response = $this->actingAs($admin)
            ->post(route('admin.ports.store'), [
                'name' => 'Tanjung Priok Test',
                'country_id' => $country->id,
                'latitude' => -6.100000,
                'longitude' => 106.880000,
                'code' => 'IDTPP'
            ]);

        // 3. Assert Port exists
        $response->assertStatus(302);
        $this->assertDatabaseHas('ports', [
            'name' => 'Tanjung Priok Test',
            'code' => 'IDTPP'
        ]);

        // 4. Assert ActivityLog exists
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'CREATE_PORT',
            'model_type' => 'Port',
            'details' => 'Menambahkan pelabuhan baru: Tanjung Priok Test (IDTPP)'
        ]);
    }
}
