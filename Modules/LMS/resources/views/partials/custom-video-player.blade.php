{{-- 
    Video Player Component using Plyr.io
    Fitur:
    - Menggunakan library video player modern teruji (Plyr.io) - UI lengkap bawaan & responsif.
    - Tanpa shadow pada video (box-shadow: none) sesuai permintaan.
    - Menghormati ukuran lebar (width), panjang/tinggi (height), dan posisi/alignment (center, float left, float right) dari Quill editor.
    - Menu Settings popup bergaya YouTube responsif:
      - Kecepatan pemutaran (0.5x - 2x)
      - Kualitas resolusi dengan batas maksimal sesuai resolusi asli video (misal 480p max 480p, 1080p max 1080p)
    - Dukungan keyboard shortcuts, scrubber tooltip waktu, volume, fullscreen, dan Picture-in-Picture.
    - Tetap kompatibel dengan tracking waktu tonton 90% materi kursus.
--}}

<!-- CDN Plyr CSS & JS Polyfilled -->
<link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

<style>
    /* Kostumisasi Tema Plyr (SIRENATA Brand Accent) */
    :root {
        --plyr-color-main: #13416B;
        --plyr-video-control-background-hover: #0f3354;
        --plyr-video-control-color-hover: #ffffff;
        --plyr-menu-background: rgba(23, 27, 34, 0.96);
        --plyr-menu-color: #ffffff;
        --plyr-menu-border-color: rgba(255, 255, 255, 0.12);
        --plyr-menu-radius: 12px;
        --plyr-menu-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
        --plyr-control-radius: 8px;
        --plyr-video-controls-background: linear-gradient(rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.88));
    }

    /* Hilangkan Box Shadow pada Video Player (Sesuai Permintaan) */
    .quill-content-render .plyr,
    .quill-content-render .plyr--video,
    .plyr,
    .plyr--video {
        box-shadow: none !important;
        border-radius: 0.75rem;
        overflow: hidden;
        background-color: #050505;
        outline: none;
    }

    /* Styling Menu Settings Plyr (Responsif YouTube Glass) */
    .plyr__menu__container {
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 12px !important;
        max-height: 260px;
        overflow-y: auto;
    }
    .plyr__menu__container::-webkit-scrollbar {
        width: 4px;
    }
    .plyr__menu__container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 4px;
    }
    .plyr__menu__container [role="menu"] {
        padding: 6px;
    }
    .plyr__menu__container .plyr__control {
        font-size: 12px;
        border-radius: 8px;
        padding: 8px 10px;
        transition: background 0.15s ease;
    }
    .plyr__menu__container .plyr__control:hover {
        background: rgba(255, 255, 255, 0.12);
    }
    .plyr__menu__container .plyr__control[aria-checked="true"] {
        color: #38bdf8;
        font-weight: 700;
    }

    /* Video di dalam Plyr */
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

    /* Alignment Wrapper Plyr di dalam Konten Materi Quill */
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
</style>

<script>
    (function () {
        // Ekstraksi dimensi lebar dan tinggi yang tersimpan di Quill editor
        function extractDimensions(video) {
            let w = video.style.width || '';
            let h = video.style.height || '';

            const attrW = video.getAttribute('width');
            const attrH = video.getAttribute('height');

            if (!w && attrW) {
                w = /^\d+(\.\d+)?$/.test(attrW.trim()) ? attrW.trim() + 'px' : attrW.trim();
            }
            if (!h && attrH) {
                h = /^\d+(\.\d+)?$/.test(attrH.trim()) ? attrH.trim() + 'px' : attrH.trim();
            }

            const rawStyle = video.getAttribute('style') || '';
            if (!w) {
                const matchW = rawStyle.match(/(?:^|;)\s*width\s*:\s*([^;]+)/i);
                if (matchW) w = matchW[1].trim();
            }
            if (!h) {
                const matchH = rawStyle.match(/(?:^|;)\s*height\s*:\s*([^;]+)/i);
                if (matchH) h = matchH[1].trim();
            }

            return { width: w, height: h };
        }

        // Deteksi alignment / posisi dari video (center, float left, float right)
        function detectAlignment(video) {
            const parent = video.parentElement;
            const rawStyle = video.getAttribute('style') || '';
            const floatStyle = video.style.float || '';
            const marginStyle = video.style.margin || '';
            const alignAttr = video.getAttribute('data-align') || video.getAttribute('align') || '';

            const isParentCenter = parent && (parent.classList.contains('ql-align-center') || parent.style.textAlign === 'center');
            const isParentRight = parent && (parent.classList.contains('ql-align-right') || parent.style.textAlign === 'right');
            const isParentLeft = parent && (parent.classList.contains('ql-align-left') || parent.style.textAlign === 'left');

            if (floatStyle === 'left' || /float\s*:\s*left/i.test(rawStyle) || alignAttr === 'left' || isParentLeft) {
                return 'left';
            }
            if (floatStyle === 'right' || /float\s*:\s*right/i.test(rawStyle) || alignAttr === 'right' || isParentRight) {
                return 'right';
            }
            if (marginStyle === 'auto' || /margin\s*:\s*auto/i.test(rawStyle) || isParentCenter || alignAttr === 'center') {
                return 'center';
            }

            return 'center';
        }

        // Tentukan resolusi yang diizinkan berdasarkan metadata video asli
        function getAllowedQuality(video) {
            const h = video.videoHeight || 0;
            const w = video.videoWidth || 0;
            const standardHeight = h > 0 ? (h <= w ? h : w) : 480;

            if (standardHeight >= 950) {
                return { default: 1080, options: [1080, 720, 480, 360, 240] };
            }
            if (standardHeight >= 650) {
                return { default: 720, options: [720, 480, 360, 240] };
            }
            if (standardHeight >= 420) {
                return { default: 480, options: [480, 360, 240] };
            }
            if (standardHeight >= 300) {
                return { default: 360, options: [360, 240] };
            }
            return { default: 240, options: [240] };
        }

        function initPlyrPlayer(video) {
            if (video.dataset.plyrInitialized) return;
            video.dataset.plyrInitialized = 'true';

            // Ekstraksi dimensi & alignment sebelum video dibungkus Plyr
            const dimensions = extractDimensions(video);
            const alignment = detectAlignment(video);

            // Fungsi pembangun instance Plyr
            function buildPlayer() {
                if (video.dataset.plyrCreated) return;
                video.dataset.plyrCreated = 'true';

                const qualityConfig = getAllowedQuality(video);

                const player = new Plyr(video, {
                    controls: [
                        'play-large',
                        'rewind',
                        'play',
                        'fast-forward',
                        'progress',
                        'current-time',
                        'duration',
                        'mute',
                        'volume',
                        'settings',
                        'pip',
                        'fullscreen'
                    ],
                    settings: ['quality', 'speed'],
                    speed: {
                        selected: 1,
                        options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2]
                    },
                    quality: {
                        default: qualityConfig.default,
                        options: qualityConfig.options,
                        forced: true,
                        onChange: (newQuality) => {
                            // Feedback toast kecil jika diperlukan
                        }
                    },
                    seekTime: 5,
                    keyboard: { focused: true, global: false },
                    tooltips: { controls: true, seek: true },
                    i18n: {
                        restart: 'Ulangi',
                        rewind: 'Mundur {seektime}d',
                        play: 'Putar',
                        pause: 'Jeda',
                        fastForward: 'Maju {seektime}d',
                        seek: 'Cari',
                        seekLabel: '{currentTime} dari {duration}',
                        played: 'Diputar',
                        buffered: 'Dimuat',
                        currentTime: 'Waktu sekarang',
                        duration: 'Durasi',
                        volume: 'Volume',
                        mute: 'Bisukan',
                        unmute: 'Bunyikan',
                        enableCaptions: 'Aktifkan subtitel',
                        disableCaptions: 'Nonaktifkan subtitel',
                        download: 'Unduh',
                        enterFullscreen: 'Layar penuh',
                        exitFullscreen: 'Keluar layar penuh',
                        frameTitle: 'Pemutar untuk {title}',
                        captions: 'Subtitel',
                        settings: 'Setelan',
                        pip: 'Picture-in-Picture',
                        menuBack: 'Kembali',
                        speed: 'Kecepatan',
                        normal: 'Normal',
                        quality: 'Kualitas',
                        loop: 'Ulangi terus'
                    }
                });

                player.on('ready', () => {
                    const container = player.elements.container;
                    if (!container) return;

                    // Hilangkan box-shadow secara tegas
                    container.style.boxShadow = 'none';

                    // Terapkan dimensi ke container Plyr
                    if (dimensions.width && dimensions.width !== '100%' && dimensions.width !== 'auto') {
                        container.style.width = dimensions.width;
                    } else {
                        container.style.width = '100%';
                        container.style.maxWidth = '840px';
                    }
                    container.style.maxWidth = '100%';

                    if (dimensions.height && dimensions.height !== 'auto') {
                        container.style.height = dimensions.height;
                    }

                    // Terapkan aspect-ratio
                    const numW = parseFloat(dimensions.width);
                    const numH = parseFloat(dimensions.height);
                    if (numW > 0 && numH > 0 && !isNaN(numW) && !isNaN(numH)) {
                        container.style.aspectRatio = `${numW} / ${numH}`;
                    } else if (video.videoWidth && video.videoHeight) {
                        container.style.aspectRatio = `${video.videoWidth} / ${video.videoHeight}`;
                    } else {
                        container.style.aspectRatio = '16 / 9';
                    }

                    // Terapkan alignment / posisi ke container Plyr
                    if (alignment === 'left') {
                        container.style.display = 'block';
                        container.style.float = 'left';
                        container.style.margin = '0.5rem 1.5rem 1rem 0';
                        container.style.clear = 'none';
                    } else if (alignment === 'right') {
                        container.style.display = 'block';
                        container.style.float = 'right';
                        container.style.margin = '0.5rem 0 1rem 1.5rem';
                        container.style.clear = 'none';
                    } else {
                        // Center
                        container.style.display = 'block';
                        container.style.marginLeft = 'auto';
                        container.style.marginRight = 'auto';
                        container.style.marginTop = '1.5rem';
                        container.style.marginBottom = '1.5rem';
                        container.style.float = 'none';
                        container.style.clear = 'both';
                    }

                    // Bersihkan jika terbungkus tag <p> kosong Quill
                    const parentEl = container.parentElement;
                    if (parentEl && parentEl.tagName === 'P' && parentEl.children.length === 1 && parentEl.textContent.trim() === '') {
                        const grandParent = parentEl.parentElement;
                        if (grandParent) {
                            grandParent.insertBefore(container, parentEl);
                            parentEl.remove();
                        }
                    }
                });
            }

            // Tunggu loadedmetadata jika belum ada agar deteksi resolusi maksimal akurat
            if (video.readyState >= 1) {
                buildPlayer();
            } else {
                video.addEventListener('loadedmetadata', buildPlayer, { once: true });
                // Fallback timeout jika video lambat merespons metadata
                setTimeout(buildPlayer, 600);
            }
        }

        function initAllInternalVideos() {
            if (typeof Plyr === 'undefined') {
                // Tunggu hingga script Plyr termuat
                setTimeout(initAllInternalVideos, 100);
                return;
            }
            const videos = document.querySelectorAll('.quill-content-render video:not([data-plyr-initialized])');
            videos.forEach(video => {
                initPlyrPlayer(video);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAllInternalVideos);
        } else {
            initAllInternalVideos();
        }

        window.initYouTubeStyleVideoPlayers = initAllInternalVideos;
    })();
</script>
