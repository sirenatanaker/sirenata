<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// use Modules\LMS\Database\Factories\CourseSectionFactory;

class CourseSection extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'name',
        'description',
        'position',
    ];


    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(SectionContent::class)->orderBy('position');
    }

    public function postTest(): HasOne
    {
        return $this->hasOne(PostTest::class, 'course_section_id');
    }

    // protected static function newFactory(): CourseSectionFactory
    // {
    //     // return CourseSectionFactory::new();
    // }
}
