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

    public function test_article_editor_exposes_the_media_upload_endpoint(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.articles.create'))
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Articles/Form')
                ->where('mediaUploadAction', route('admin.media.store'))
            );
    }
}
