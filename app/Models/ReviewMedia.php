<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewMedia extends Model
{
    use HasFactory;

    protected $table = 'review_media';

    protected $fillable = [
        'type',
        'path',
        'mime_type',
        'original_name',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
    ];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.ltrim($this->path, '/'));
    }
}
