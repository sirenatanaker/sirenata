<?php

return [
    'gemini' => [
        'api_key'         => env('GEMINI_API_KEY', ''),
        'base_url'        => rtrim((string) env('GEMINI_API_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'), '/'),
        'model_essay'     => env('GEMINI_MODEL_ESSAY', 'gemini-3.1-flash-lite'),
        'model_report'    => env('GEMINI_MODEL_REPORT', 'gemini-3.5-flash-lite'),
        'timeout_seconds' => (int) env('GEMINI_TIMEOUT_SECONDS', 50),
    ],

    'queue' => [
        'connection'                      => env('GRADING_QUEUE_CONNECTION') ?: env('QUEUE_CONNECTION', 'database'),
        'name'                            => env('GRADING_QUEUE_NAME', 'ai-grading'),
        'rate_limit_delay_sec'            => (int) env('GRADING_RATE_LIMIT_DELAY_SEC', 7),
        'max_retries'                     => (int) env('GRADING_MAX_RETRIES', 3),
        'backoff_seconds'                 => [60, 180, 300],
        'consecutive_429_pause_threshold' => (int) env('GRADING_CONSECUTIVE_429_PAUSE_THRESHOLD', 3),
    ],

    'preprocessing' => [
        'pdf_min_chars'          => (int) env('GRADING_PDF_MIN_CHARS', 100),
        'pdf_min_chars_per_page' => (int) env('GRADING_PDF_MIN_CHARS_PER_PAGE', 50),
        'text_max_chars'         => (int) env('GRADING_TEXT_MAX_CHARS', 25000),
        'essay_min_chars'        => (int) env('GRADING_ESSAY_MIN_CHARS', 15),
    ],

    'essay' => [
        'max_questions_per_req' => (int) env('GRADING_ESSAY_MAX_QUESTIONS_PER_REQ', 6),
    ],

    'triage' => [
        'borderline_lower_offset' => (int) env('GRADING_BORDERLINE_LOWER_OFFSET', 10),
        'borderline_upper_offset' => (int) env('GRADING_BORDERLINE_UPPER_OFFSET', 4),
    ],
];
