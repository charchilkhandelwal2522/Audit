<?php

namespace Modules\Audit\Entities;

use App\Models\BaseModel;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditTemplate extends BaseModel
{
    use HasFactory, HasCompany;

    protected $table = 'audit_templates';

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'department_id',
        'status',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'company_id',
    ];

    /**
     * Get the department/team this template belongs to.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'department_id');
    }

    /**
     * Get the user who created this template.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the user who last updated this template.
     */
    public function lastUpdatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the checkpoints for this template.
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(AuditTemplateCheckpoint::class, 'audit_template_id')->orderBy('order');
    }

    /**
     * Get audits using this template.
     */
    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'audit_template_id');
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to filter by department.
     */
    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Get the total number of checkpoints.
     */
    public function getCheckpointCountAttribute(): int
    {
        return $this->checkpoints()->count();
    }
}

