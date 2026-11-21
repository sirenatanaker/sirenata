<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CertificateSubmission extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'certificate_submissions';

    protected $fillable = [
        'bimtek_event_id',
        'submission_code',
        'submission_letter_number',
        'ppsdm_decree_number',
        'total_graduates',
        'submitted_by',
        'submitted_at',
        'numbered_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'submitted_at'    => 'datetime',
        'numbered_at'     => 'datetime',
        'total_graduates' => 'integer',
    ];

    public function bimtekEvent(): BelongsTo
    {
        return $this->belongsTo(BimtekEvent::class, 'bimtek_event_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_student', 'cert_submission_id', 'user_id')
            ->withPivot([
                'course_id',
                'bimtek_event_id',
                'progress',
                'status',
                'completed_at',
                'certificate_code',
                'official_cert_number',
                'cert_submission_status',
                'zoom_attended',
                'certificate_file',
            ])
            ->withTimestamps();
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'draft'               => ['label' => 'Draft Usulan', 'class' => 'bg-slate-500 text-white'],
            'submitted_to_ppsdm'  => ['label' => 'Diajukan ke PPSDM', 'class' => 'bg-amber-500 text-white'],
            'numbered'            => ['label' => 'Nomor Telah Terbit', 'class' => 'bg-blue-600 text-white'],
            'completed'           => ['label' => 'Sertifikat Diterbitkan', 'class' => 'bg-emerald-600 text-white'],
            default               => ['label' => ucfirst($this->status), 'class' => 'bg-slate-500 text-white'],
        };
    }
}
