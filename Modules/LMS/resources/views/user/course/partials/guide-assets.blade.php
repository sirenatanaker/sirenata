{{-- Aset driver.js untuk panduan interaktif (CDN, versi dikunci pada major v1) --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css" />
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
        start(page, steps) {
            if (!window.driver || !window.driver.js) return;
            const self = this;
            const valid = steps.filter((s) => !s.element || self.isVisible(document.querySelector(s.element)));
            if (!valid.length) return;
            const tour = window.driver.js.driver({
                showProgress: true,
                allowClose: true,
                nextBtnText: 'Lanjut',
                prevBtnText: 'Kembali',
                doneBtnText: 'Selesai',
                onDestroyed: () => self.markSeen(page),
                steps: valid,
            });
            tour.drive();
        },
        autoStart(page, steps) {
            if (!this.seen(page)) this.start(page, steps);
        },
    };
</script>
