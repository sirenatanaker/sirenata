{{-- SIRENATA Global Dark Mode Integration --}}
<script>
    (function () {
        const saved = localStorage.getItem('sirenata_dark_mode');
        if (saved === 'true') {
            document.documentElement.classList.add('dark');
        } else if (saved === 'false') {
            document.documentElement.classList.remove('dark');
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    })();

    window.isDarkMode = function () {
        return document.documentElement.classList.contains('dark');
    };

    window.setDarkMode = function (enabled) {
        if (enabled) {
            document.documentElement.classList.add('dark');
            localStorage.setItem('sirenata_dark_mode', 'true');
        } else {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('sirenata_dark_mode', 'false');
        }
        window.dispatchEvent(new CustomEvent('dark-mode-toggled', { detail: { isDark: enabled } }));
    };

    window.toggleDarkMode = function () {
        const next = !window.isDarkMode();
        window.setDarkMode(next);
        return next;
    };
</script>

<style>
    /* ==========================================================================
       Global Dark Mode Styles for SIRENATA
       ========================================================================== */

    html.dark,
    html.dark body {
        background-color: #0b1120 !important;
        color: #e2e8f0 !important;
        color-scheme: dark;
    }

    /* Surfaces & Containers */
    html.dark .bg-white {
        background-color: #151e32 !important;
        border-color: #243048 !important;
        color: #e2e8f0;
    }

    html.dark .bg-slate-50,
    html.dark .bg-gray-50,
    html.dark .bg-slate-50\/80 {
        background-color: #0f172a !important;
        border-color: #243048 !important;
    }

    html.dark .bg-slate-100,
    html.dark .bg-gray-100 {
        background-color: #1a243a !important;
        border-color: #243048 !important;
    }

    html.dark .bg-slate-200,
    html.dark .bg-gray-200 {
        background-color: #26334d !important;
    }

    /* Borders & Dividers */
    html.dark .border-slate-100,
    html.dark .border-slate-200,
    html.dark .border-slate-300,
    html.dark .border-gray-100,
    html.dark .border-gray-200,
    html.dark .border-gray-300 {
        border-color: #243048 !important;
    }

    html.dark hr {
        border-color: #243048 !important;
    }

    /* Typography in Dark Mode */
    html.dark .text-slate-900,
    html.dark .text-slate-800,
    html.dark .text-gray-900,
    html.dark .text-gray-800 {
        color: #f8fafc !important;
    }

    html.dark .text-slate-700,
    html.dark .text-slate-600,
    html.dark .text-gray-700,
    html.dark .text-gray-600 {
        color: #cbd5e1 !important;
    }

    html.dark .text-slate-500,
    html.dark .text-gray-500 {
        color: #94a3b8 !important;
    }

    html.dark .text-slate-400,
    html.dark .text-gray-400 {
        color: #64748b !important;
    }

    /* Navigation Bar & Sidebar */
    html.dark nav.fixed {
        background-color: #151e32 !important;
        border-color: #243048 !important;
    }

    html.dark aside {
        background-color: #151e32 !important;
        border-color: #243048 !important;
    }

    html.dark aside a.text-slate-600,
    html.dark aside button.text-slate-600 {
        color: #94a3b8 !important;
    }

    html.dark aside a.text-slate-600:hover,
    html.dark aside button.text-slate-600:hover {
        background-color: #1a243a !important;
        color: #f8fafc !important;
    }

    /* Form Controls & Inputs */
    html.dark input:not([type="submit"]):not([type="button"]):not([type="checkbox"]):not([type="radio"]),
    html.dark select,
    html.dark textarea {
        background-color: #0f172a !important;
        border-color: #243048 !important;
        color: #f8fafc !important;
    }

    html.dark input:not([type="submit"]):not([type="button"]):focus,
    html.dark select:focus,
    html.dark textarea:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2) !important;
    }

    html.dark input::placeholder,
    html.dark textarea::placeholder {
        color: #64748b !important;
    }

    html.dark input:disabled,
    html.dark input[readonly],
    html.dark select:disabled {
        background-color: #141c2e !important;
        border-color: #243048 !important;
        color: #64748b !important;
    }

    /* Dropdowns & Popovers */
    html.dark .dropdown-menu,
    html.dark [x-show="open"],
    html.dark [x-show="isOpen"],
    html.dark [x-show="showQuickSettings"] {
        background-color: #151e32 !important;
        border-color: #243048 !important;
        color: #e2e8f0 !important;
    }

    /* Dropdown items hover */
    html.dark ul.space-y-1 a:hover,
    html.dark ul.space-y-1 button:hover {
        background-color: #1a243a !important;
        color: #38bdf8 !important;
    }

    /* Tables */
    html.dark table thead th {
        background-color: #0f172a !important;
        color: #94a3b8 !important;
        border-color: #243048 !important;
    }

    html.dark table tbody td {
        border-color: #243048 !important;
        color: #cbd5e1 !important;
    }

    html.dark table tbody tr:hover {
        background-color: #1a253c !important;
    }

    /* Breadcrumbs */
    html.dark nav[aria-label="Breadcrumb"] {
        background: transparent !important;
        border-color: transparent !important;
    }
    html.dark nav[aria-label="Breadcrumb"] a,
    html.dark nav[aria-label="Breadcrumb"] span {
        color: #94a3b8 !important;
    }

    /* Accent & Brand Colors in Dark */
    html.dark a.text-\[\#13416B\],
    html.dark .text-\[\#13416B\] {
        color: #38bdf8 !important;
    }

    html.dark .bg-\[\#13416B\]\/10 {
        background-color: rgba(56, 189, 248, 0.15) !important;
    }

    html.dark .border-\[\#13416B\]\/20,
    html.dark .border-\[\#13416B\]\/30 {
        border-color: rgba(56, 189, 248, 0.3) !important;
    }

    /* Modals & Dialogs */
    html.dark .modal-content,
    html.dark [role="dialog"] > div {
        background-color: #151e32 !important;
        border-color: #243048 !important;
        color: #e2e8f0 !important;
    }

    /* Scrollbars */
    html.dark ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    html.dark ::-webkit-scrollbar-track {
        background: #0f172a;
    }
    html.dark ::-webkit-scrollbar-thumb {
        background: #243048;
        border-radius: 4px;
    }
    html.dark ::-webkit-scrollbar-thumb:hover {
        background: #334155;
    }
</style>
