<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public function update(UploadedFile $file, string $directory, ?string $oldPath): string
    {
        $newPath = $this->store($file, $directory);
        $this->delete($oldPath);

        return $newPath;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
