<?php

namespace App\Services\Media;

use App\Contracts\MediaStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class LocalMediaStorage implements MediaStorage
{
    public function disk(): string
    {
        return 'public';
    }

    public function put(UploadedFile $file, string $path, string $contentHash): string
    {
        $stored = Storage::disk($this->disk())->putFileAs(
            dirname($path),
            $file,
            basename($path),
            'public',
        );

        if ($stored === false) {
            throw new RuntimeException('图片保存失败，请稍后重试。');
        }

        $url = Storage::disk($this->disk())->url($path);

        return str_starts_with($url, '/')
            ? rtrim((string) config('app.url'), '/').$url
            : $url;
    }
}
