<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ReviewMediaRules
{
    public static function rules(): array
    {
        return [
            'media' => ['nullable', 'array', 'max:5'],
            'media.*' => [
                'bail',
                'file',
                'mimes:jpg,jpeg,png,webp,mp4,mov,webm',
                'max:51200',
                function (string $attribute, mixed $file, $fail): void {
                    if (
                        $file instanceof UploadedFile
                        && str_starts_with((string) $file->getMimeType(), 'image/')
                        && $file->getSize() > 5 * 1024 * 1024
                    ) {
                        $fail('Mỗi ảnh đánh giá không được vượt quá 5 MB.');
                    }
                },
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            'media.array' => 'Danh sách ảnh hoặc video không hợp lệ.',
            'media.max' => 'Mỗi đánh giá chỉ được tải lên tối đa 5 ảnh hoặc video.',
            'media.*.file' => 'Tệp đính kèm không hợp lệ.',
            'media.*.mimes' => 'Chỉ chấp nhận ảnh JPG, PNG, WebP hoặc video MP4, MOV, WebM.',
            'media.*.max' => 'Mỗi video đánh giá không được vượt quá 50 MB.',
        ];
    }
}
