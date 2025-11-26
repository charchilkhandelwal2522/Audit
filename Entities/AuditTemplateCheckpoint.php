<?php

namespace Modules\Audit\Entities;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditTemplateCheckpoint extends BaseModel
{
    use HasFactory;

    protected $table = 'audit_template_checkpoints';

    protected $fillable = [
        'audit_template_id',
        'title',
        'description',
        'order',
        'requires_file_upload',
        'requires_photo',
        'requires_notes',
        'is_mandatory',
    ];

    protected $casts = [
        'requires_file_upload' => 'boolean',
        'requires_photo' => 'boolean',
        'requires_notes' => 'boolean',
        'is_mandatory' => 'boolean',
        'order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * Get the template this checkpoint belongs to.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'audit_template_id');
    }

    /**
     * Get the responses for this checkpoint.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(AuditCheckpointResponse::class, 'checkpoint_id');
    }

    /**
     * Get the file requirement label.
     */
    public function getRequirementsLabelAttribute(): string
    {
        $requirements = [];

        if ($this->requires_file_upload) {
            $requirements[] = 'File';
        }

        if ($this->requires_photo) {
            $requirements[] = 'Photo';
        }

        if ($this->requires_notes) {
            $requirements[] = 'Notes';
        }

        return implode(', ', $requirements) ?: 'None';
    }
}

