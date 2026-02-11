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
use Modules\Audit\Entities\AuditSetting;

class Audit extends BaseModel
{
    use HasFactory, HasCompany;

    protected $table = 'audits';

    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_IN_PROGRESS => 'text-yellow',
        self::STATUS_COMPLETED => 'text-light-green',
        self::STATUS_CANCELLED => 'text-red',
    ];

    protected $fillable = [
        'company_id',
        'audit_template_id',
        'department_id',
        'auditor_id',
        'auditee_id',
        'location',
        'status',
        'started_at',
        'total_elapsed_seconds',
        'resumed_at',
        'completed_at',
        'duration_seconds',
        'score',
        'total_checkpoints',
        'completed_checkpoints',
        'partially_completed_checkpoints',
        'summary',
        'report_pdf',
        'photo',
        'added_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'resumed_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_elapsed_seconds' => 'integer',
        'score' => 'decimal:2',
        'total_checkpoints' => 'integer',
        'completed_checkpoints' => 'integer',
        'partially_completed_checkpoints' => 'integer',
        'duration_seconds' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'company_id',
    ];

    protected $appends = ['duration_formatted', 'report_pdf_url', 'photo_url'];

    /**
     * Get the audit template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(AuditTemplate::class, 'audit_template_id');
    }

    /**
     * Get the department being audited.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'department_id');
    }

    /**
     * Get the auditor (person conducting the audit).
     */
    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the auditee (person being audited).
     */
    public function auditee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditee_id')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the user who created this audit.
     */
    public function addedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class);
    }

    /**
     * Get the checkpoint responses for this audit.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(AuditCheckpointResponse::class, 'audit_id')->orderBy('order');
    }

    /**
     * Get the files for this audit.
     */
    public function files(): HasMany
    {
        return $this->hasMany(AuditFile::class, 'audit_id');
    }

    /**
     * Get the formatted duration.
     */
    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration_seconds) {
            return '--';
        }

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Get the PDF report URL.
     */
    public function getReportPdfUrlAttribute(): ?string
    {
        if (!$this->report_pdf) {
            return null;
        }

        return asset_url_local_s3('audit-reports/' . $this->report_pdf);
    }

    /**
     * Get the audit photo URL (stored via Files::uploadLocalOrS3 like other app uploads).
     * Backward compatible with old storage path (storage/app/public).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }

        if (str_contains($this->photo, '/')) {
            return asset('storage/' . $this->photo);
        }

        return asset_url_local_s3('audit-photos/' . $this->photo);
    }

    /**
     * Calculate the audit score.
     */
    public function calculateScore(): float
    {
        $setting = AuditSetting::where('company_id', $this->company_id)->first();
        $partialWeight = $setting?->partial_completion_weight ?? 0.5;

        $completedWeight = $this->completed_checkpoints;
        $partialWeightTotal = $this->partially_completed_checkpoints * $partialWeight;

        if ($this->total_checkpoints === 0) {
            return 0;
        }

        return round((($completedWeight + $partialWeightTotal) / $this->total_checkpoints) * 100, 2);
    }

    /**
     * Calculate and save the score.
     */
    public function updateScore(): void
    {
        $responses = $this->responses;

        $this->total_checkpoints = $responses->count();
        $this->completed_checkpoints = $responses->where('status', 'completed')->count();
        $this->partially_completed_checkpoints = $responses->where('status', 'partially_completed')->count();
        $this->score = $this->calculateScore();
        $this->save();
    }

    /**
     * Complete the audit.
     */
    public function complete(): void
    {
        $this->updateScore();
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();

        if ($this->resumed_at) {
            $this->duration_seconds = (int) $this->total_elapsed_seconds + $this->resumed_at->diffInSeconds(now());
        } elseif ($this->started_at) {
            $this->duration_seconds = $this->started_at->diffInSeconds(now());
        }

        $this->save();
    }

    /**
     * Check if audit is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if audit is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to get audits for an auditor.
     */
    public function scopeForAuditor($query, $userId)
    {
        return $query->where('auditor_id', $userId);
    }

    /**
     * Scope a query to get audits for an auditee.
     */
    public function scopeForAuditee($query, $userId)
    {
        return $query->where('auditee_id', $userId);
    }

    /**
     * Get the progress percentage.
     */
    public function getProgressPercentageAttribute(): int
    {
        $totalResponded = $this->responses()->whereNotNull('responded_at')->count();
        $total = $this->responses()->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round(($totalResponded / $total) * 100);
    }

    /**
     * Get the score color class.
     */
    public function getScoreColorAttribute(): string
    {
        if ($this->score >= 80) {
            return 'text-success';
        }

        if ($this->score >= 60) {
            return 'text-warning';
        }

        return 'text-danger';
    }

    /**
     * Check if score is below threshold alert.
     */
    public function isBelowThreshold(): bool
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        $setting = AuditSetting::where('company_id', $this->company_id)->first();
        $threshold = $setting?->score_threshold_alert ?? 70;

        return $this->score < $threshold;
    }

    /**
     * Get the threshold alert message.
     */
    public function getThresholdAlertMessage(): ?string
    {
        if (!$this->isBelowThreshold()) {
            return null;
        }

        $setting = AuditSetting::where('company_id', $this->company_id)->first();
        $threshold = $setting?->score_threshold_alert ?? 70;

        return __('audit::app.scoreBelowThreshold', [
            'score' => $this->score,
            'threshold' => $threshold
        ]);
    }
}

