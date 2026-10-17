<x-landingpage::layouts.master title="SIRENATA - Sistem Informasi Perencanaan Ketenagakerjaan">

    {{-- CSS Kustom untuk Animasi, Custom Scrollbar & Font Kalam + Oswald --}}
    @push('styles')
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&family=Oswald:wght@400;600;700&display=swap');

            .font-kalam {
                font-family: 'Kalam', cursive;
            }

            .section-title {
                font-family: 'Oswald', sans-serif;
                font-size: 1.5rem;
                font-weight: 800;
                line-height: 1.2;
            }

            @media (min-width: 640px) {
                .section-title {
                    font-size: 1.875rem;
                }
            }

            @media (min-width: 1024px) {
                .section-title {
                    font-size: 3rem;
                }

                .section-title-stat {
                    font-size: 1.875rem;
                }

                .section-title-cta {
                    font-size: 3.75rem;
                }
            }

            html {
                scroll-behavior: smooth;
            }

            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }

            @keyframes float {
                0%, 100% {
                    transform: translateY(0) scale(1);
                }
                50% {
                    transform: translateY(-15px) scale(1.01);
                }
            }

            @keyframes cardFloat {
                0%, 100% {
                    transform: translateY(0) rotate(0deg);
                }
                33% {
                    transform: translateY(-8px) rotate(1deg);
                }
                66% {
                    transform: translateY(-4px) rotate(-1deg);
                }
            }

            @keyframes morphBlob {
                0%, 100% {
                    border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
                }
                50% {
                    border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
                }
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }

            .animate-card-float-1 {
                animation: cardFloat 5s ease-in-out infinite;
            }

            .animate-card-float-2 {
                animation: cardFloat 7s ease-in-out infinite 0.5s;
            }

            .animate-card-float-3 {
                animation: cardFloat 6s ease-in-out infinite 1.5s;
            }

            .course-scroll-container {
                cursor: grab;
                overscroll-behavior: contain;
                scrollbar-width: thin;
                scrollbar-color: #cbd5e1 transparent;
            }

            .course-scroll-container.is-dragging {
                cursor: grabbing;
                user-select: none;
            }

            .animate-blob {
                animation: morphBlob 8s ease-in-out infinite;
            }

            .reveal-left {
                opacity: 0;
                transform: translateX(-50px);
                transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            .reveal-right {
                opacity: 0;
                transform: translateX(50px);
                transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            .reveal-up {
                opacity: 0;
                transform: translateY(40px);
                transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            }

            .reveal-left.active,
            .reveal-right.active,
            .reveal-up.active {
                opacity: 1;
                transform: translate(0, 0);
            }
        </style>
    @endpush

    <!-- SECTIONS KONTEN -->
    <x-landingpage::hero />
    <x-landingpage::stats :stats="$stats" />
    <x-landingpage::features />
    <x-landingpage::courses :courses="$courses" />
    <x-landingpage::lms-stats :lms-stats="$lmsStats" :stats="$stats" :courses="$courses" />
    <x-landingpage::faq />
    <x-landingpage::cta :stats="$stats" />

    <!-- SCRIPT OBSERVER UNTUK ANIMASI MUNCUL (REVEAL) & COUNTER -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const revealOptions = {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.15
                };

                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('active');
                        }
                    });
                }, revealOptions);

                const revealElements = document.querySelectorAll('.reveal-left, .reveal-right, .reveal-up');
                revealElements.forEach(el => revealObserver.observe(el));

                document.querySelectorAll('.course-scroll-container').forEach(container => {
                    let animationFrame;
                    let isInteracting = false;
                    let lastPointerY = 0;
                    let hasDragged = false;
                    const autoScrollSpeed = 7 / 60;

                    const autoScroll = () => {
                        if (!isInteracting) {
                            container.scrollTop += autoScrollSpeed;

                            if (container.scrollTop >= container.scrollHeight / 3) {
                                container.scrollTop -= container.scrollHeight / 3;
                            }
                        }

                        animationFrame = requestAnimationFrame(autoScroll);
                    };

                    container.addEventListener('pointerdown', event => {
                        isInteracting = true;
                        hasDragged = false;
                        lastPointerY = event.clientY;
                        container.classList.add('is-dragging');
                        container.setPointerCapture(event.pointerId);
                    });

                    container.addEventListener('pointermove', event => {
                        if (!isInteracting) return;

                        const distance = event.clientY - lastPointerY;
                        if (Math.abs(distance) > 1) hasDragged = true;
                        container.scrollTop -= distance;
                        lastPointerY = event.clientY;
                    });

                    const stopDragging = event => {
                        if (!isInteracting) return;

                        isInteracting = false;
                        container.classList.remove('is-dragging');
                        if (event.pointerId !== undefined && container.hasPointerCapture(event.pointerId)) {
                            container.releasePointerCapture(event.pointerId);
                        }
                    };

                    container.addEventListener('pointerup', stopDragging);
                    container.addEventListener('pointercancel', stopDragging);
                    container.addEventListener('click', event => {
                        if (hasDragged) {
                            event.preventDefault();
                            event.stopPropagation();
                            hasDragged = false;
                        }
                    }, true);

                    animationFrame = requestAnimationFrame(autoScroll);
                    container.addEventListener('DOMNodeRemoved', () => cancelAnimationFrame(animationFrame));
                });

                const statOptions = {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0.5
                };

                const statObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const targetEl = entry.target;
                            const targetNum = parseFloat(targetEl.getAttribute('data-target'));
                            const isFloat = targetNum % 1 !== 0;
                            const suffix = targetEl.getAttribute('data-suffix') || '';
                            const duration = 2000;
                            let startTime = null;

                            const animateCount = (timestamp) => {
                                if (!startTime) startTime = timestamp;
                                const progress = Math.min((timestamp - startTime) / duration, 1);

                                const easeProgress = 1 - Math.pow(1 - progress, 3);
                                let currentNum = easeProgress * targetNum;

                                if (isFloat) {
                                    targetEl.innerText = currentNum.toFixed(1) + suffix;
                                } else {
                                    targetEl.innerText = Math.floor(currentNum) + suffix;
                                }

                                if (progress < 1) {
                                    requestAnimationFrame(animateCount);
                                } else {
                                    targetEl.innerText = targetNum + suffix;
                                }
                            };
                            requestAnimationFrame(animateCount);
                            observer.unobserve(targetEl);
                        }
                    });
                }, statOptions);

                const statCounters = document.querySelectorAll('.stat-counter');
                statCounters.forEach(counter => statObserver.observe(counter));
            });
        </script>
    @endpush

</x-landingpage::layouts.master>
