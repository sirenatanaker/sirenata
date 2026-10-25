{{-- CSS render konten Quill bersama: dipakai halaman preview admin dan
    halaman baca user agar tampil IDENTIK dengan editor create/edit
    admin-pusat. Jangan duplikasi aturan ini di masing-masing view. --}}

/* --- Media: gambar, video, iframe (termasuk embed YouTube) --- */
.quill-content-render img,
.quill-content-render video:not(.plyr video):not(.yt-player-wrapper video),
.quill-content-render iframe.ql-video,
.quill-content-render iframe {
    display: inline-block !important;
    max-width: 100% !important;
    height: auto;
    margin-top: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.quill-content-render img[style*="margin: auto"],
.quill-content-render img[style*="display: block"],
.quill-content-render video:not(.plyr video):not(.yt-player-wrapper video)[style*="margin: auto"],
.quill-content-render video:not(.plyr video):not(.yt-player-wrapper video)[style*="display: block"],
.quill-content-render iframe[style*="margin: auto"],
.quill-content-render iframe[style*="display: block"] {
    display: block !important;
    margin-left: auto !important;
    margin-right: auto !important;
}

.quill-content-render img[style*="float: left"],
.quill-content-render video:not(.plyr video):not(.yt-player-wrapper video)[style*="float: left"],
.quill-content-render iframe[style*="float: left"] {
    float: left !important;
    margin-right: 1.5rem !important;
    margin-bottom: 1rem !important;
    margin-top: 0.5rem !important;
}

.quill-content-render img[style*="float: right"],
.quill-content-render video:not(.plyr video):not(.yt-player-wrapper video)[style*="float: right"],
.quill-content-render iframe[style*="float: right"] {
    float: right !important;
    margin-left: 1.5rem !important;
    margin-bottom: 1rem !important;
    margin-top: 0.5rem !important;
}

/* --- Penyesuaian Video Player Plyr (Tanpa Shadow, Responsif) --- */
.quill-content-render .plyr,
.quill-content-render .plyr--video {
    box-shadow: none !important;
    border-radius: 0.75rem !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}
.quill-content-render .plyr video {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border-radius: inherit !important;
    box-shadow: none !important;
    object-fit: contain !important;
    background-color: #000000 !important;
}
.quill-content-render .plyr[style*="float: left"] {
    float: left !important;
    margin-right: 1.5rem !important;
    margin-bottom: 1rem !important;
    margin-top: 0.5rem !important;
}
.quill-content-render .plyr[style*="float: right"] {
    float: right !important;
    margin-left: 1.5rem !important;
    margin-bottom: 1rem !important;
    margin-top: 0.5rem !important;
}
.quill-content-render .plyr[style*="margin-left: auto"],
.quill-content-render .plyr[style*="margin: auto"] {
    margin-left: auto !important;
    margin-right: auto !important;
}

/* --- Khusus Resolusi Mobile: Video & Media Dipaksa Full Width (w-full) --- */
@media (max-width: 639.98px) {
    .quill-content-render video,
    .quill-content-render iframe,
    .quill-content-render .plyr,
    .quill-content-render .plyr--video,
    .quill-content-render .yt-player-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        float: none !important;
        margin-left: auto !important;
        margin-right: auto !important;
        display: block !important;
        clear: both !important;
    }
}

/* --- Alignment & indent Quill --- */
.quill-content-render .ql-align-center { text-align: center !important; }
.quill-content-render .ql-align-right { text-align: right !important; }
.quill-content-render .ql-align-justify { text-align: justify !important; }

.quill-content-render .ql-indent-1 { padding-left: 3em !important; }
.quill-content-render .ql-indent-2 { padding-left: 6em !important; }
.quill-content-render .ql-indent-3 { padding-left: 9em !important; }
.quill-content-render .ql-indent-4 { padding-left: 12em !important; }

/* --- Heading: samakan skala editor Quill snow (h1 2em, h2 1.5em) --- */
.quill-content-render h1 { font-size: 2em !important; font-weight: 700 !important; line-height: 1.3 !important; margin: 0.75em 0 0.5em !important; }
.quill-content-render h2 { font-size: 1.5em !important; font-weight: 700 !important; line-height: 1.35 !important; margin: 0.75em 0 0.5em !important; }

/* --- Font family pilihan editor (whitelist toolbar) --- */
.quill-content-render .ql-font-arial { font-family: Arial, sans-serif !important; }
.quill-content-render .ql-font-times-new-roman { font-family: 'Times New Roman', serif !important; }
.quill-content-render .ql-font-calibri { font-family: Calibri, sans-serif !important; }
.quill-content-render .ql-font-verdana { font-family: Verdana, sans-serif !important; }
.quill-content-render .ql-font-courier { font-family: 'Courier New', monospace !important; }

/* --- Code block: samakan editor (highlight.js tema github-dark) --- */
.quill-content-render pre.ql-syntax {
    background-color: #0f172a !important;
    color: #f8fafc !important;
    padding: 1.25rem !important;
    border-radius: 0.5rem !important;
    overflow-x: auto !important;
    font-size: 0.875rem !important;
    line-height: 1.6 !important;
    border: 1px solid #1e293b;
    text-align: left !important;
}

/* --- Blockquote khas SIRENATA --- */
.quill-content-render blockquote {
    border-left: 4px solid #13416B !important;
    background-color: #f8fafc !important;
    padding: 16px 20px !important;
    margin: 12px 0 !important;
    font-style: italic !important;
    color: #334155 !important;
    border-radius: 0 10px 10px 0 !important;
    position: relative;
    overflow: hidden;
}
.quill-content-render blockquote::after {
    content: "\f10d";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    position: absolute;
    top: -4px;
    right: 12px;
    font-size: 40px;
    color: #e2e8f0;
    opacity: 0.5;
    z-index: 0;
}
.quill-content-render blockquote * {
    position: relative;
    z-index: 1;
}
