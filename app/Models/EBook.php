<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EBook extends Model
{
    use HasFactory;

    protected $table = 'e_books';

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'author',
        'publisher',
        'publication_year',
        'language',
        'category_id',  // Changed from 'category' to 'category_id'
        'file_path',
        'cover_image',
        'file_format',
        'file_size',
        'download_count',
        'view_count',
        'is_featured',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Add this relationship
    public function category(): BelongsTo
    {
        return $this->belongsTo(EBookCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Add scope for category
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) return 'N/A';

        $bytes = $this->file_size * 1024;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
