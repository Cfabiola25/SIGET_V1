/**
 * SIGET Embeddable Widget SDK v1.0
 * Permite a los clubes afiliados incrustar tablas de posiciones y fixtures
 * en sus páginas oficiales sin abandonar su marca y con auto-ajuste de altura.
 */
(function() {
    'use strict';

    function initWidgets() {
        const containers = document.querySelectorAll('[data-siget-tournament]');

        containers.forEach(function(el) {
            if (el.getAttribute('data-siget-loaded')) return;
            el.setAttribute('data-siget-loaded', 'true');

            const tournamentId = el.getAttribute('data-siget-tournament');
            const view = el.getAttribute('data-view') || 'standings';
            const theme = el.getAttribute('data-theme') || 'light';
            const color = encodeURIComponent(el.getAttribute('data-color') || '#057a55');
            const baseUrl = el.getAttribute('data-host') || window.location.origin;

            const iframe = document.createElement('iframe');
            iframe.src = `${baseUrl}/embed/tournaments/${tournamentId}/${view}?theme=${theme}&color=${color}`;
            iframe.style.width = '100%';
            iframe.style.border = 'none';
            iframe.style.overflow = 'hidden';
            iframe.style.transition = 'height 0.2s ease';
            iframe.style.minHeight = '320px';
            iframe.setAttribute('scrolling', 'no');
            iframe.setAttribute('title', `SIGET Widget - ${view}`);

            el.innerHTML = '';
            el.appendChild(iframe);

            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'siget-widget-resize') {
                    if (event.source === iframe.contentWindow) {
                        iframe.style.height = (event.data.height + 10) + 'px';
                    }
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidgets);
    } else {
        initWidgets();
    }
})();
