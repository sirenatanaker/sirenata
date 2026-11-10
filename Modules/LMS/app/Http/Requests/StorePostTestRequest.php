<?php

namespace Modules\LMS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseSection;

class StorePostTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('type') === 'file_upload') {
            $this->merge([
                'passing_score' => $this->input('passing_score') ?: 0,
                'duration'      => $this->input('duration') ?: 0,
            ]);
        }

        if (empty($this->title)) {
            $course = Course::where('slug', $this->course_slug)->first();
            $courseName = $course ? ($course->course_name ?? $course->name) : 'Pelatihan';
            $section = !empty($this->course_section_id) ? CourseSection::find($this->course_section_id) : null;

            if ($section) {
                $type = $this->input('type', 'multiple_choice');
                $title = match ($type) {
                    'essay'       => 'Evaluasi Uraian: ' . $section->name,
                    'file_upload' => 'Penugasan Berkas: ' . $section->name,
                    default       => 'Evaluasi: ' . $section->name,
                };
            } else {
                $title = 'Evaluasi Akhir : ' . $courseName;
            }

            $this->merge([
                'title' => $title,
            ]);
        }
    }

    public function rules(): array
    {
        $type = $this->input('type', 'multiple_choice');

        $rules = [
            'type'              => ['required', 'string', 'in:multiple_choice,essay,file_upload'],
            'course_slug'       => ['required', 'string'],
            'course_section_id' => ['nullable', 'uuid'],
            'course_id'         => ['nullable', 'uuid'],
            'title'             => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
        ];

        if ($type === 'file_upload') {
            $rules['passing_score'] = ['nullable', 'integer', 'min:0', 'max:100'];
            $rules['duration']      = ['nullable', 'integer', 'min:0'];
            $rules['admin_file']    = ['required', 'file', 'mimes:pdf,docx,doc,zip,rar', 'max:10240'];
            $rules['instructions']  = ['nullable', 'string', 'max:2000'];
        } elseif ($type === 'essay') {
            $rules['passing_score']              = ['required', 'integer', 'min:0', 'max:100'];
            $rules['duration']                   = ['required', 'integer', 'min:1'];
            $rules['essay_questions']            = ['required', 'array', 'min:1'];
            $rules['essay_questions.*.question'] = ['required', 'string'];
        } else {
            $rules['passing_score']              = ['required', 'integer', 'min:0', 'max:100'];
            $rules['duration']                   = ['required', 'integer', 'min:1'];
            $rules['questions']                  = ['required', 'array', 'min:1'];
            $rules['questions.*.question']       = ['required', 'string'];
            $rules['questions.*.choices']        = ['required', 'array', 'min:2'];
            $rules['questions.*.choices.*']      = ['required', 'string'];
            $rules['questions.*.correct_choice'] = ['required', 'integer'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'admin_file.required'                 => 'Harap unggah berkas panduan/soal penugasan (PDF/DOCX/ZIP).',
            'admin_file.mimes'                    => 'Format berkas soal harus berupa PDF, DOCX, DOC, atau ZIP.',
            'admin_file.max'                      => 'Ukuran berkas soal maksimal 10 MB.',
            'essay_questions.required'            => 'Harap tambahkan minimal 1 butir pertanyaan uraian.',
            'essay_questions.min'                 => 'Harap tambahkan minimal 1 butir pertanyaan uraian.',
            'essay_questions.*.question.required' => 'Setiap butir pertanyaan uraian wajib diisi.',
            'questions.required'                  => 'Harap tambahkan minimal 1 butir pertanyaan pilihan ganda.',
            'questions.*.choices.min'             => 'Setiap pertanyaan pilihan ganda harus memiliki minimal 2 pilihan jawaban.',
        ];
    }
}
