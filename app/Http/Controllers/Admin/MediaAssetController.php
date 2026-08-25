<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MediaAsset;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaAssetController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Media/Index', [
            'assets' => MediaAsset::query()
                ->latest()
                ->paginate(24)
                ->through(fn (MediaAsset $asset): array => $this->payload($asset)),
            'uploadAction' => route('admin.media.store'),
            'maxMegabytes' => round((int) config('media.max_kilobytes') / 1024, 1),
            'storageDisk' => (string) config('media.disk'),
        ]);
    }

    public function store(Request $request, MediaUploadService $uploader): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp,gif',
                'max:'.(int) config('media.max_kilobytes'),
                'dimensions:max_width='.(int) config('media.max_width').',max_height='.(int) config('media.max_height'),
            ],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'copyright' => ['nullable', 'string', 'max:255'],
        ]);

        $asset = $uploader->upload(
            $validated['image'],
            $request->user(),
            $validated['alt_text'] ?? null,
            $validated['copyright'] ?? null,
        );

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'media.uploaded',
            'auditable_type' => MediaAsset::class,
            'auditable_id' => $asset->id,
            'metadata' => [
                'public_id' => $asset->public_id,
                'disk' => $asset->disk,
                'path' => $asset->path,
                'content_hash' => $asset->content_hash,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['asset' => $this->payload($asset)], 201);
        }

        return redirect()->route('admin.media.index')->with('success', '图片已上传，可以复制 Markdown 地址。');
    }

    private function payload(MediaAsset $asset): array
    {
        $url = $asset->url();
        $alt = str_replace([']', "\r", "\n"], ['', ' ', ' '], $asset->alt_text ?: $asset->original_name ?: '文章图片');

        return [
            'id' => $asset->public_id,
            'url' => $url,
            'markdown' => "![{$alt}]({$url})",
            'original_name' => $asset->original_name,
            'alt_text' => $asset->alt_text,
            'copyright' => $asset->copyright,
            'mime_type' => $asset->mime_type,
            'size' => $asset->size,
            'width' => $asset->width,
            'height' => $asset->height,
            'disk' => $asset->disk,
            'created_at' => $asset->created_at?->toIso8601String(),
        ];
    }
}
