<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReviewMediaUploader
{
    /**
     * @param  array<int, UploadedFile>  $files
     * @return array<int, string>
     */
    public function store(Review $review, array $files): array
    {
        $storedPaths = [];

        try {
            foreach ($files as $file) {
                $path = $file->store("reviews/{$review->id}", 'public');

                if (! $path) {
                    throw ValidationException::withMessages([
                        'media' => 'Không thể lưu ảnh hoặc video. Vui lòng thử lại.',
                    ]);
                }

                $storedPaths[] = $path;
                $mimeType = (string) $file->getMimeType();

                $review->media()->create([
                    'type' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
                    'path' => $path,
                    'mime_type' => $mimeType,
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ]);
            }
        } catch (Throwable $exception) {
            $this->delete($storedPaths);

            throw $exception;
        }

        return $storedPaths;
    }

    /**
     * @param  array<int, string>  $paths
     */
    public function delete(array $paths): void
    {
        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }
}
