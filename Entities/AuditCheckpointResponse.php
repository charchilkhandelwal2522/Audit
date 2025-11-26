<?php

namespace Modules\Audit\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditCheckpointResponse extends BaseModel
{
    use HasFactory;

    protected $table = 'audit_checkpoint_responses';

    const STATUS_NOT_COMPLETED = 'not_completed';
    const STATUS_PARTIALLY_COMPLETED = 'partially_completed';
    const STATUS_COMPLETED = 'completed';

    const STATUSES = [
        self::STATUS_NOT_COMPLETED => [
            'label' => 'Not Completed',
            'color' => 'danger',
            'icon' => 'times-circle',
        ],
        self::STATUS_PARTIALLY_COMPLETED => [
            'label' => 'Partially Completed',
            'color' => 'warning',
            'icon' => 'exclamation-circle',
        ],
        self::STATUS_COMPLETED => [
            'label' => 'Completed',
            'color' => 'success',
            'icon' => 'check-circle',
        ],
    ];

    protected $fillable = [
        'audit_id',
        'checkpoint_id',
        'status',
        'notes',
        'order',
        'responded_at',
    ];

    protected $casts = [
        'order' => 'integer',
        'responded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the audit this response belongs to.
     */
    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    /**
     * Get the checkpoint this response is for.
     */
    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(AuditTemplateCheckpoint::class, 'checkpoint_id');
    }

    /**
     * Get the files for this response.
     */
    public function files(): HasMany
    {
        return $this->hasMany(AuditFile::class, 'checkpoint_response_id');
    }

    /**
     * Check if this response is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this response is partially completed.
     */
    public function isPartiallyCompleted(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_COMPLETED;
    }

    /**
     * Check if this response has been answered.
     */
    public function isResponded(): bool
    {
        return $this->responded_at !== null;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? 'Unknown';
    }

    /**
     * Get the status color.
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    /**
     * Get the status icon.
     */
    public function getStatusIconAttribute(): string
    {
        return self::STATUSES[$this->status]['icon'] ?? 'question-circle';
    }

    /**
     * Check if the response meets all requirements.
     */
    public function meetsRequirements(): bool
    {
        $checkpoint = $this->checkpoint;

        if ($checkpoint->requires_file_upload || $checkpoint->requires_photo) {
            if ($this->files()->count() === 0) {
                return false;
            }
        }

        if ($checkpoint->requires_notes && empty($this->notes)) {
            return false;
        }

        return true;
    }
}

