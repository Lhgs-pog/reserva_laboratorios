/**
 * Injeta token CSRF em formulários POST e envia header em fetch POST.
 */
(function () {
    'use strict';

    function token() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') || '' : '';
    }

    function injectForms() {
        var t = token();
        if (!t) return;
        document.querySelectorAll('form').forEach(function (form) {
            var method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method !== 'POST') return;
            if (form.querySelector('input[name="_csrf"]')) return;
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = '_csrf';
            input.value = t;
            form.appendChild(input);
        });
    }

    if (typeof window.fetch === 'function') {
        var origFetch = window.fetch;
        window.fetch = function (input, init) {
            init = init || {};
            var method = (init.method || 'GET').toUpperCase();
            if (method === 'POST') {
                var t = token();
                if (t) {
                    var headers = new Headers(init.headers || {});
                    if (!headers.has('X-CSRF-Token')) {
                        headers.set('X-CSRF-Token', t);
                    }
                    init.headers = headers;
                }
            }
            return origFetch.call(this, input, init);
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectForms);
    } else {
        injectForms();
    }

    // Formulários adicionados depois (modais, etc.)
    document.addEventListener('DOMContentLoaded', function () {
        injectForms();
    });
})();
