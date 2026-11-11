{{-- Aset driver.js untuk panduan interaktif (CDN, versi dikunci pada major v1) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css" />
<style>
    .driver-popover {
        border-radius: 6px !important;
        padding: 14px 16px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
        width: calc(100vw - 32px) !important;
        max-width: 295px !important;
        border: 1px solid rgba(226, 232, 240, 0.9) !important;
        font-family: inherit !important;
        overflow: visible !important;
    }

    /* Maskot Chibi Kemnaker Berdiri di Samping Luar Card Guide (Besar & Jelas di Desktop) */
    .driver-popover-mascot-chibi {
        position: absolute !important;
        pointer-events: none !important;
        z-index: 1000000002 !important;
        filter: drop-shadow(0 10px 14px rgba(0, 0, 0, 0.22)) !important;
        transition: right 0.2s ease-out, left 0.2s ease-out, bottom 0.2s ease-out, opacity 0.2s ease-out !important;
    }

    /* Di Mobile: Tidak Tampilkan Maskot */
    @media (max-width: 639px) {
        .driver-popover-mascot-chibi {
            display: none !important;
        }
    }

    .driver-popover-mascot-chibi img {
        display: block !important;
        width: 100% !important;
        height: auto !important;
        user-select: none !important;
        pointer-events: none !important;
    }

    /* 1. Samping Kanan Luar Card (Besar 135px, menghadap ke card) */
    .driver-popover-mascot-chibi.mascot-pos-right {
        right: -125px !important;
        bottom: 0px !important;
        top: auto !important;
        left: auto !important;
        width: 135px !important;
        transform: none !important;
    }

    /* 2. Samping Kiri Luar Card (Dibalik horizontal agar menghadap ke card) */
    .driver-popover-mascot-chibi.mascot-pos-left {
        left: -125px !important;
        bottom: 0px !important;
        top: auto !important;
        right: auto !important;
        width: 135px !important;
        transform: scaleX(-1) !important;
    }
    .driver-popover-title {
        font-size: 15px !important;
        font-weight: 800 !important;
        color: #0f172a !important;
        margin-bottom: 6px !important;
        line-height: 1.4 !important;
        letter-spacing: -0.01em !important;
    }
    .driver-popover-description {
        font-size: 13px !important;
        line-height: 1.65 !important;
        color: #334155 !important;
    }
    .driver-popover-description strong {
        color: #0B5CFF !important;
        font-weight: 700 !important;
    }
    .driver-popover-footer {
        margin-top: 14px !important;
        padding-top: 10px !important;
        border-top: 1px solid #f1f5f9 !important;
    }
    .driver-popover-next-btn {
        background-color: #13416B !important;
        color: #ffffff !important;
        border-radius: 6px !important;
        font-weight: 700 !important;
        font-size: 12px !important;
        padding: 6px 16px !important;
        border: none !important;
        text-shadow: none !important;
        transition: background-color 0.2s ease !important;
    }
    .driver-popover-next-btn:hover {
        background-color: #0f3354 !important;
    }
    .driver-popover-prev-btn {
        border-radius: 6px !important;
        font-size: 12px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        background: #f1f5f9 !important;
        border: 1px solid #e2e8f0 !important;
        padding: 6px 14px !important;
    }
    .driver-popover-prev-btn:hover {
        background: #e2e8f0 !important;
        color: #334155 !important;
    }
    .driver-popover-close-btn {
        color: #94a3b8 !important;
        font-size: 18px !important;
    }
    .driver-popover-close-btn:hover {
        color: #334155 !important;
    }
    .driver-popover-progress-text {
        font-size: 11px !important;
        font-weight: 600 !important;
        color: #94a3b8 !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
    window.SirenataTour = window.SirenataTour || {
        storageKey(page) {
            return 'sirenata_tour_' + page + '_v1';
        },
        seen(page) {
            try {
                return !!localStorage.getItem(this.storageKey(page));
            } catch (e) {
                return true;
            }
        },
        markSeen(page) {
            try {
                localStorage.setItem(this.storageKey(page), '1');
            } catch (e) {}
        },
        isVisible(el) {
            return !!el && el.offsetParent !== null;
        },
        injectMascot(isEvaluasi = false) {
            if (window.innerWidth < 640) return;
            const popover = document.querySelector('.driver-popover');
            if (!popover) return;

            let mascot = popover.querySelector('.driver-popover-mascot-chibi');
            if (!mascot) {
                mascot = document.createElement('div');
                mascot.className = 'driver-popover-mascot-chibi';
                mascot.innerHTML = '<img src="{{ asset('images/maskot_kemnaker.webp') }}" onerror="this.onerror=null; this.src=\'{{ asset('images/maskot_kemnaker.png') }}\';" alt="Maskot Kemnaker" />';
                popover.appendChild(mascot);
            }

            this.positionMascot(mascot, popover, isEvaluasi);
        },
        positionMascot(mascot, popover, isEvaluasi = false) {
            if (!mascot || !popover) return;
            const popoverRect = popover.getBoundingClientRect();
            const winWidth = window.innerWidth;

            mascot.classList.remove('mascot-pos-right', 'mascot-pos-left');

            if (winWidth < 640) {
                mascot.style.display = 'none';
                return;
            }

            mascot.style.display = 'block';

            if (isEvaluasi) {
                // Khusus evaluasi akhir: maskot selalu di sebelah kanan luar card
                mascot.classList.add('mascot-pos-right');
                return;
            }

            // Untuk step lainnya: posisikan di kanan, jika tepi kanan sempit pindah ke kiri
            const spaceOnRight = winWidth - popoverRect.right;
            const spaceOnLeft = popoverRect.left;

            if (spaceOnRight >= 135) {
                mascot.classList.add('mascot-pos-right');
            } else if (spaceOnLeft >= 135) {
                mascot.classList.add('mascot-pos-left');
            } else {
                mascot.classList.add('mascot-pos-right');
            }
        },
        hideMascot() {
            const mascot = document.querySelector('.driver-popover-mascot-chibi');
            if (mascot) {
                mascot.style.display = 'none';
            }
        },
        start(page, steps, options = {}) {
            if (!window.driver || !window.driver.js) return;
            const self = this;
            const valid = steps.filter((s) => !s.element || self.isVisible(document.querySelector(s.element)));
            if (!valid.length) return;

            // Beri sinyal awal bahwa mode panduan/tour dimulai (misal untuk menutup accordion topik terlebih dahulu)
            window.dispatchEvent(new CustomEvent('guide:start', { detail: { page } }));
            window.dispatchEvent(new CustomEvent('guide-start', { detail: { page } }));
            if (typeof options.onStart === 'function') options.onStart();

            self.currentStepIsEvaluasi = false;

            const checkAndApplyMascot = (element, step) => {
                const popoverTitle = document.querySelector('.driver-popover-title')?.textContent || '';
                const isEvaluasi = (step?.element === '[data-tour="evaluasi"]') ||
                                   (step?.element === '[data-tour="evaluasi-narasumber"]') ||
                                   (element?.getAttribute?.('data-tour') === 'evaluasi') ||
                                   (element?.getAttribute?.('data-tour') === 'evaluasi-narasumber') ||
                                   (element?.matches && element.matches('[data-tour="evaluasi"], [data-tour="evaluasi-narasumber"]')) ||
                                   popoverTitle.toLowerCase().includes('evaluasi');
                self.currentStepIsEvaluasi = !!isEvaluasi;
                if (window.innerWidth >= 640) {
                    self.injectMascot(isEvaluasi);
                } else {
                    self.hideMascot();
                }
            };

            // Interval mikro saat panduan aktif untuk menyinkronkan tampilan maskot chibi di luar card
            let syncInterval = setInterval(() => {
                const popover = document.querySelector('.driver-popover');
                if (popover) {
                    if (window.innerWidth >= 640) {
                        self.injectMascot(self.currentStepIsEvaluasi);
                    } else {
                        self.hideMascot();
                    }
                } else {
                    clearInterval(syncInterval);
                }
            }, 100);

            const tour = window.driver.js.driver({
                showProgress: true,
                allowClose: true,
                nextBtnText: 'Lanjut',
                prevBtnText: 'Kembali',
                doneBtnText: 'Selesai',
                onHighlightStarted: (element, step) => {
                    setTimeout(() => checkAndApplyMascot(element, step), 30);
                },
                onHighlighted: (element, step) => {
                    setTimeout(() => checkAndApplyMascot(element, step), 30);
                },
                onDestroyed: () => {
                    clearInterval(syncInterval);
                    self.markSeen(page);
                    // Hapus maskot jika ada sisa di DOM
                    const existingMascot = document.querySelector('.driver-popover-mascot-chibi');
                    if (existingMascot) existingMascot.remove();

                    // Beri sinyal bahwa mode panduan/tour telah ditutup/selesai
                    window.dispatchEvent(new CustomEvent('guide:end', { detail: { page } }));
                    window.dispatchEvent(new CustomEvent('guide-end', { detail: { page } }));
                    if (typeof options.onDestroyed === 'function') options.onDestroyed();
                },
                steps: valid,
            });

            // Beri jeda singkat agar transisi accordion/animasi UI selesai sebelum driver.js menghitung posisi elemen
            setTimeout(() => {
                tour.drive();
            }, options.delay ?? 150);
        },
        autoStart(page, steps, options = {}) {
            if (!this.seen(page)) this.start(page, steps, options);
        },
    };
</script>
