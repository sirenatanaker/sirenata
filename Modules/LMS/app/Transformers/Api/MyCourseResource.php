<?php

namespace Modules\LMS\Transformers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyCourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'slug'          => $this->slug,
            'thumbnail_url' => $this->thumbnail_url,
            'status'        => $this->pivot->status,
            'progress'      => $this->pivot->progress,
            'completed_at'  => $this->pivot->completed_at,
            'certificate_code'=> $this->pivot->certificate_code,
            'certificate_file'=> $this->pivot->certificate_file ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->pivot->certificate_file) : null,
            'certificate_issued_at'=> $this->pivot->certificate_issued_at,
            'enrolled_at'   => $this->pivot->created_at,
            'benefits'      => CourseBenefitResource::collection($this->whenLoaded('benefits')),
            'testimonis'    => CourseTestimoniResource::collection($this->whenLoaded('testimonis')),
            'course_sections'  => CourseSectionResource::collection($this->whenLoaded('sections')),
        ];

    }
}
