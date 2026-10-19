{{-- CSS Presets untuk Tampilan Baca Materi LMS & Perpustakaan --}}

/* --- 1. Font Size Presets --- */
.reader-font-sm,
.reader-font-sm p,
.reader-font-sm li,
.reader-font-sm span:not([class*="badge"]) {
    font-size: 0.875rem !important; /* 14px */
}
.reader-font-sm h1 { font-size: 1.5rem !important; }
.reader-font-sm h2 { font-size: 1.25rem !important; }
.reader-font-sm h3 { font-size: 1.125rem !important; }

.reader-font-normal,
.reader-font-normal p,
.reader-font-normal li,
.reader-font-normal span:not([class*="badge"]) {
    font-size: 1rem !important; /* 16px */
}
.reader-font-normal h1 { font-size: 1.875rem !important; }
.reader-font-normal h2 { font-size: 1.5rem !important; }
.reader-font-normal h3 { font-size: 1.25rem !important; }

.reader-font-lg,
.reader-font-lg p,
.reader-font-lg li,
.reader-font-lg span:not([class*="badge"]) {
    font-size: 1.125rem !important; /* 18px */
}
.reader-font-lg h1 { font-size: 2.125rem !important; }
.reader-font-lg h2 { font-size: 1.75rem !important; }
.reader-font-lg h3 { font-size: 1.375rem !important; }

.reader-font-xl,
.reader-font-xl p,
.reader-font-xl li,
.reader-font-xl span:not([class*="badge"]) {
    font-size: 1.25rem !important; /* 20px */
}
.reader-font-xl h1 { font-size: 2.375rem !important; }
.reader-font-xl h2 { font-size: 1.95rem !important; }
.reader-font-xl h3 { font-size: 1.5rem !important; }

/* --- 2. Line Height Presets --- */
.reader-leading-normal,
.reader-leading-normal p,
.reader-leading-normal li {
    line-height: 1.75 !important;
}

.reader-leading-loose,
.reader-leading-loose p,
.reader-leading-loose li {
    line-height: 2.2 !important;
}

/* --- 3. Font Family Presets --- */
.reader-font-sans,
.reader-font-sans p,
.reader-font-sans li,
.reader-font-sans h1,
.reader-font-sans h2,
.reader-font-sans h3,
.reader-font-sans blockquote {
    font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
}

.reader-font-serif,
.reader-font-serif p,
.reader-font-serif li,
.reader-font-serif h1,
.reader-font-serif h2,
.reader-font-serif h3,
.reader-font-serif blockquote {
    font-family: 'Georgia', 'Cambria', 'Times New Roman', Times, serif !important;
}

/* Pastikan pre & code tetap monospace */
.quill-content-render pre,
.quill-content-render code,
.quill-content-render pre.ql-syntax {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
}

/* --- 4. Reader Theme: Sepia --- */
.reader-theme-sepia {
    background-color: #F8F1E5 !important;
    border-color: #E6D9C5 !important;
    color: #4A3B2C !important;
}
.reader-theme-sepia .reader-card-header,
.reader-theme-sepia .reader-card-footer,
.reader-theme-sepia .reader-footer {
    background-color: #F3E8D7 !important;
    border-color: #E6D9C5 !important;
}
.reader-theme-sepia h1,
.reader-theme-sepia h2,
.reader-theme-sepia h3,
.reader-theme-sepia h4,
.reader-theme-sepia p,
.reader-theme-sepia li,
.reader-theme-sepia td,
.reader-theme-sepia th {
    color: #4A3B2C !important;
}
.reader-theme-sepia span:not([class*="badge"]):not([class*="bg-"]) {
    color: inherit;
}
.reader-theme-sepia a:not([class*="btn"]):not([class*="bg-"]):not([class*="text-white"]) {
    color: #8C531B !important;
}
.reader-theme-sepia .quill-content-render blockquote {
    background-color: #EFE3CE !important;
    border-left-color: #8C531B !important;
    color: #4A3B2C !important;
}
.reader-theme-sepia .border-slate-100,
.reader-theme-sepia .border-slate-200 {
    border-color: #E6D9C5 !important;
}
.reader-theme-sepia hr {
    border-color: #E6D9C5 !important;
}
.reader-theme-sepia .text-slate-400,
.reader-theme-sepia .text-slate-500 {
    color: #8C7B68 !important;
}

/* --- 5. Reader Theme: Dark --- */
.reader-theme-dark {
    background-color: #171E2B !important;
    border-color: #263346 !important;
    color: #E2E8F0 !important;
}
.reader-theme-dark .reader-card-header,
.reader-theme-dark .reader-card-footer,
.reader-theme-dark .reader-footer {
    background-color: #131A26 !important;
    border-color: #263346 !important;
}
.reader-theme-dark h1,
.reader-theme-dark h2,
.reader-theme-dark h3,
.reader-theme-dark h4,
.reader-theme-dark p,
.reader-theme-dark li,
.reader-theme-dark td,
.reader-theme-dark th {
    color: #E2E8F0 !important;
}
.reader-theme-dark span:not([class*="badge"]):not([class*="bg-"]) {
    color: inherit;
}
.reader-theme-dark a:not([class*="btn"]):not([class*="bg-"]):not([class*="text-white"]) {
    color: #FFFFFF !important;
    text-decoration: underline;
}
.reader-theme-dark .quill-content-render blockquote {
    background-color: #1E293B !important;
    border-left-color: #FFFFFF !important;
    color: #CBD5E1 !important;
}
.reader-theme-dark .border-slate-100,
.reader-theme-dark .border-slate-200 {
    border-color: #263346 !important;
}
.reader-theme-dark hr {
    border-color: #263346 !important;
}
.reader-theme-dark .bg-slate-50 {
    background-color: #1E2837 !important;
}
.reader-theme-dark .text-slate-400,
.reader-theme-dark .text-slate-500 {
    color: #94A3B8 !important;
}
