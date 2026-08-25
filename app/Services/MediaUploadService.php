<?php

namespace App\Services;

use App\Contracts\MediaStorage;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class MediaUploadService
{
    public function __construct(private readonly MediaStorage $storage) {}

    public function upload(
        UploadedFile $file,
        User $actor,
        ?string $altText = null,
        ?string $copyright = null,
    ): MediaAsset {
        $contentHash = hash_file('sha256', $file->getRealPath());
        if ($contentHash === false) {
            throw new RuntimeException('无法计算图片内容哈希。');
        }

        $existing = MediaAsset::query()
            ->where('disk', $this->storage->disk())
            ->where('content_hash', $contentHash)
            ->first();

        if ($existing) {
            $existing->fill([
                'alt_text' => $existing->alt_text ?: $altText,
                'copyright' => $existing->copyright ?: $copyright,
            ])->save();

            return $existing;
        }

        $dimensions = @getimagesize($file->getRealPath()) ?: [null, null];
        $mimeType = (string) $file->getMimeType();
        $path = $this->pathFor($contentHash, $mimeType);
        $url = $this->storage->put($file, $path, $contentHash);

        return MediaAsset::create([
            'disk' => $this->storage->disk(),
            'path' => $path,
            'original_name' => basename($file->getClientOriginalName()),
            'mime_type' => $mimeType,
            'size' => $file->getSize(),
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'content_hash' => $contentHash,
            'alt_text' => $altText,
            'copyright' => $copyright,
            'source_url' => $url,
            'uploaded_by' => $actor->id,
        ]);
    }

    private function pathFor(string $contentHash, string $mimeType): string
    {
        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => throw new RuntimeException('不支持的图片格式。'),
        };
        $prefix = trim((string) config('media.prefix'), '/');

        return "{$prefix}/".substr($contentHash, 0, 2)."/{$contentHash}.{$extension}";
    }
}
