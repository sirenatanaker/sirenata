@foreach ($courses as $course)
    <x-lms::user.course-card :course="$course" />
@endforeach
