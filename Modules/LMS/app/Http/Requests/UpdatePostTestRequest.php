<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\LMS\Models\Course;

class UpdatePostTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->title)) {
            $course = Course::where('slug', $this->course_slug)->first();
            $courseName = $course ? ($course->course_name ?? $course->name) : 'Pelatihan';
            $this->merge([
                'title' => 'Evaluasi Akhir : ' . $courseName,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'course_slug'                => ['required', 'string'],
            'title'                      => ['nullable', 'string', 'max:255'],
            'passing_score'              => ['required', 'integer', 'min:0', 'max:100'],
            'duration'                   => ['required', 'integer', 'min:1'],
            'description'                => ['nullable', 'string'],
            'questions'                  => ['required', 'array', 'min:1'],
            'questions.*.question'       => ['required', 'string'],
            'questions.*.choices'        => ['required', 'array', 'min:2'],
            'questions.*.choices.*'      => ['required', 'string'],
            'questions.*.correct_choice' => ['required', 'integer'],
        ];
    }
}
