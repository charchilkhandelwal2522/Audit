<?php

namespace Modules\Audit\Entities;

use App\Models\BaseModel;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditFile extends BaseModel
{
    use HasFactory;

    protected $table = 'audit_files';

    protected $fillable = [
        'audit_id',
        'checkpoint_response_id',
        'filename',
        'hashname',
        'file_type',
        'size',
        'description',
        'added_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $appends = ['file_url', 'icon'];

    /**
     * Get the audit this file belongs to.
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    /**
     * Get the checkpoint response this file belongs to.
     */
    public function checkpointResponse(): BelongsTo
    {
        return $this->belongsTo(AuditCheckpointResponse::class, 'checkpoint_response_id');
    }

    /**
     * Get the user who uploaded this file.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the file URL.
     */
    public function getFileUrlAttribute(): string
    {
        return asset_url_local_s3('audit-files/' . $this->hashname);
    }

    /**
     * Get the file icon based on type.
     */
    public function getIconAttribute(): string
    {
        $icons = [
            'image' => 'fa-file-image',
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint',
            'zip' => 'fa-file-archive',
            'video' => 'fa-file-video',
            'audio' => 'fa-file-audio',
        ];

        return $icons[$this->file_type] ?? 'fa-file';
    }

    /**
     * Check if this file is an image.
     */
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * Get the human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}

