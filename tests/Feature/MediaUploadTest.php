<?php

namespace Tests\Feature;

use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'https://denghy.cn',
            'media.disk' => 'public',
            'media.prefix' => 'test/media',
            'media.max_kilobytes' => 8192,
        ]);
        Storage::fake('public');
    }

    public function test_admin_can_upload_an_image_and_receive_markdown(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'activated' => true]);
        $image = UploadedFile::fake()->image('release-proof.png', 1200, 800)->size(240);

        $response = $this->actingAs($admin)->postJson(route('admin.media.store'), [
            'image' => $image,
            'alt_text' => '发布链路验证图',
            'copyright' => '原创',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('asset.alt_text', '发布链路验证图')
            ->assertJsonPath('asset.disk', 'public');

        $asset = MediaAsset::firstOrFail();
        Storage::disk('public')->assertExists($asset->path);
        $this->assertSame(hash_file('sha256', $image->getRealPath()), $asset->content_hash);
        $this->assertSame(1200, $asset->width);
        $this->assertSame(800, $asset->height);
        $this->assertStringContainsString('![发布链路验证图](https://denghy.cn/storage/', $response->json('asset.markdown'));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'media.uploaded',
            'auditable_id' => $asset->id,
            'user_id' => $admin->id,
        ]);
    }

    public function test_same_image_is_reused_by_content_hash(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'activated' => true]);
        $first = UploadedFile::fake()->image('first.png', 600, 400);
        $bytes = file_get_contents($first->getRealPath());

        $this->actingAs($admin)->postJson(route('admin.media.store'), [
            'image' => $first,
            'alt_text' => '第一次上传',
        ])->assertCreated();

        $this->actingAs($admin)->postJson(route('admin.media.store'), [
            'image' => UploadedFile::fake()->createWithContent('duplicate.png', $bytes),
            'alt_text' => '重复上传',
        ])->assertCreated();

        $this->assertDatabaseCount('media_assets', 1);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_svg_upload_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'activated' => true]);
        $svg = UploadedFile::fake()->createWithContent('unsafe.svg', '<svg onload="alert(1)"></svg>');

        $this->actingAs($admin)->postJson(route('admin.media.store'), ['image' => $svg])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors' => ['image']]);
    }

    public function test_non_admin_upload_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('admin.media.store'), ['image' => UploadedFile::fake()->image('photo.jpg')])
            ->assertForbidden();
    }

    public function test_media_library_is_an_admin_only_inertia_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'activated' => true]);

        $this->get(route('admin.media.index'))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('admin.media.index'))
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Media/Index')
                ->where('storageDisk', 'public')
                ->has('assets.data', 0)
            );
    }
}
