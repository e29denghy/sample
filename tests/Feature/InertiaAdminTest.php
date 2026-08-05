<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InertiaAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_an_inertia_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('projectCount', 0)
                ->where('pendingEvents', 0)
            );
    }
}
