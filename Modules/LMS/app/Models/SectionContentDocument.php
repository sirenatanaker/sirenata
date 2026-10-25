<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SectionContentDocument extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'section_content_documents';

    protected $fillable = [
        'section_content_id',
        'file_path',
        'file_name',
        'file_size',
    ];

    public function sectionContent(): BelongsTo
    {
        return $this->belongsTo(SectionContent::class, 'section_content_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_name ?? $this->file_path, PATHINFO_EXTENSION));
    }

    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1) . ' ' . $units[$i];
    }
}
