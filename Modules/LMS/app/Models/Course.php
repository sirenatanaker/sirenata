<?php

namespace Modules\LMS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\LMS\Database\Factories\CourseFactory;

// use Modules\LMS\Database\Factories\CourseFactory;

class Course extends Model
{
    use HasFactory, HasUuids, Sluggable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'thumbnail',
        'description',
        'audience',
    ];

    protected static function newFactory(): CourseFactory
    {
        return CourseFactory::new();
    }

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($course) {
            $course->slug = SlugService::createSlug($course, 'slug', $course->name);
        });
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail) return null;
        return str_starts_with($this->thumbnail, 'https') ? $this->thumbnail : asset('storage/' . $this->thumbnail);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the Course
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function benefits(): HasMany
    {
        return $this->hasMany(CourseBenefits::class);
    }

    public function testimonis(): HasMany
    {
        return $this->hasMany(CourseTestimoni::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('position');
    }

    public function evaluasiAkhir(): HasOne
    {
        return $this->hasOne(PostTest::class, 'course_id')->whereNull('course_section_id');
    }

    public function postTests(): HasMany
    {
        return $this->hasMany(PostTest::class, 'course_id');
    }

    // public function students()
    // {
    //     return $this->belongsToMany(User::class, 'course_student')
    //         ->withPivot(['status', 'progress', 'completed_at', 'certificate_code', 'certificate_file', 'certificate_issued_at'])
    //         ->withTimestamps();
    // }

    /**
     * Pivot biasa — semua field di course_student diload lewat withPivot
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_student')
            ->withPivot([
                'status',
                'progress',
                'completed_at',
                'certificate_code',
                'certificate_file',
                'certificate_issued_at',
            ])
            ->withTimestamps();
    }

    /**
     * Custom pivot CourseMentor karena ada field is_active
     */
    public function mentors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_mentors')
            ->using(CourseMentor::class)
            ->withPivot('is_active')
            ->withTimestamps();
    }
}
