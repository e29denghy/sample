<?php

namespace App\Contracts;

use Illuminate\Http\UploadedFile;

interface MediaStorage
{
    public function disk(): string;

    public function put(UploadedFile $file, string $path, string $contentHash): string;
}
