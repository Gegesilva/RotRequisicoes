(function () {
    function obterOverlay() {
        var overlay = document.getElementById('carregamentoGlobal');
        if (overlay) return overlay;

        overlay = document.createElement('div');
        overlay.id = 'carregamentoGlobal';
        overlay.className = 'carregamento-global';
        overlay.setAttribute('aria-live', 'polite');
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML = '<div class="carregamento-conteudo"><span class="carregamento-spinner"></span><span>Aguarde...</span></div>';
        document.body.appendChild(overlay);
        return overlay;
    }

    window.mostrarCarregamento = function () {
        var overlay = obterOverlay();
        overlay.classList.add('ativo');
        overlay.setAttribute('aria-hidden', 'false');
    };

    window.ocultarCarregamento = function () {
        var overlay = document.getElementById('carregamentoGlobal');
        if (!overlay) return;
        overlay.classList.remove('ativo');
        overlay.setAttribute('aria-hidden', 'true');
    };

    document.addEventListener('DOMContentLoaded', function () {
        obterOverlay();

        document.addEventListener('submit', function (event) {
            if (!event.defaultPrevented) window.mostrarCarregamento();
        });

        document.addEventListener('click', function (event) {
            var link = event.target.closest ? event.target.closest('a[href]') : null;
            if (!link || link.target === '_blank' || link.hasAttribute('download')) return;
            if ((link.getAttribute('href') || '').charAt(0) === '#') return;
            window.mostrarCarregamento();
        });

        if (window.jQuery && window.jQuery(document).ajaxStart) {
            window.jQuery(document).ajaxStart(window.mostrarCarregamento);
            window.jQuery(document).ajaxStop(window.ocultarCarregamento);
        }
    });

    window.addEventListener('load', window.ocultarCarregamento);
    window.addEventListener('pageshow', window.ocultarCarregamento);
})();
