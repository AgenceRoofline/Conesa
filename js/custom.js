document.addEventListener('DOMContentLoaded', function () {

    // ── Particuliers & Pros ────────────────────────────────────────────────
    (function () {
        var cards = Array.from(document.querySelectorAll('.pp-zoom'));
        if (!cards.length) return;

        var wrap = cards[0].parentElement;
        if (wrap) wrap.classList.add('pp-wrap');

        var BP_TABLET_MIN = 768;
        var BP_TABLET_MAX = 1024;

        function isTablet() {
            var w = window.innerWidth;
            return w >= BP_TABLET_MIN && w <= BP_TABLET_MAX;
        }

        function setActive(active) {
            cards.forEach(function (c) {
                c.classList.remove('pp-active', 'pp-inactive');
                c.classList.add(c === active ? 'pp-active' : 'pp-inactive');
            });
        }

        function init() { setActive(cards[0]); }
        init();

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                if (!card.classList.contains('pp-active')) {
                    setActive(card);
                } else {
                    var other = cards.find(function (c) { return c !== card; });
                    if (other) setActive(other);
                }
            });
        });

        window.addEventListener('resize', function () { init(); });
    }());

    // ── Stacking cards — Nos prestations ──────────────────────────────────
    (function () {
        var items = Array.from(document.querySelectorAll('.svc-stack .svc-item'));
        if (!items.length) return;

        // Injecte l'index CSS sur chaque card
        items.forEach(function (item, i) {
            item.style.setProperty('--svc-i', i);
        });

        var BASE = 90;  // top du premier card (px)
        var STEP = 16;  // décalage entre chaque card sticky (px)

        // Réduit légèrement les cards enterrées sous les suivantes
        function updateScale() {
            for (var i = 0; i < items.length; i++) {
                var top = items[i].getBoundingClientRect().top;
                var threshold = BASE + i * STEP;

                if (top <= threshold + 2) {
                    // Compte combien de cards suivantes sont déjà collées au-dessus
                    var above = 0;
                    for (var j = i + 1; j < items.length; j++) {
                        if (items[j].getBoundingClientRect().top <= BASE + j * STEP + 2) above++;
                    }
                    var scale = Math.max(0.88, 1 - above * 0.03);
                    items[i].style.transform = 'scale(' + scale + ')';
                } else {
                    items[i].style.transform = '';
                }
            }
        }

        window.addEventListener('scroll', updateScale, { passive: true });
        updateScale();
    }());

});
