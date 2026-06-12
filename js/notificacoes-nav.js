(function () {
    'use strict';

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    window.destacarLinhaNotificacao = function (row) {
        if (!row) return;
        row.classList.add('table-warning');
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(function () {
            row.classList.remove('table-warning');
        }, 3500);
    };

    window.initNotificacoesNav = function (options) {
        options = options || {};
        const bell = document.getElementById('navBell');
        const popup = document.getElementById('notificacoes-popup');
        const lista = document.getElementById('notificacoes-lista');
        const wrap = document.getElementById('notifNavWrap');
        const btnFechar = document.getElementById('notifFechar');
        const btnVerTodas = document.getElementById('notifVerTodas');

        if (!bell || !popup || !lista || !wrap) {
            return;
        }

        const badgeIds = ['badge-nav-bell'].concat(options.badgeIds || []);
        let aberto = false;
        let qtdAnterior = 0;
        let ultimosItems = [];
        const somUrl = options.somUrl || 'https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3';
        const som = options.playSound ? new Audio(somUrl) : null;

        function atualizarBadges(qtd) {
            badgeIds.forEach(function (id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = qtd > 0 ? qtd : '';
                el.classList.toggle('d-none', qtd <= 0);
            });
            bell.classList.toggle('notif-active', qtd > 0);
            bell.classList.toggle('sos-active', qtd > 0 && options.sosStyle);
        }

        function renderLista(items) {
            ultimosItems = items || [];
            if (!items || items.length === 0) {
                lista.innerHTML = '<div class="notif-empty"><i class="bi bi-check-circle"></i>Nenhuma notificação no momento</div>';
                return;
            }

            lista.innerHTML = items.map(function (item) {
                const color = item.color || 'warning';
                const icon = item.icon || 'bi-bell';
                const tipo = item.tipo || 'geral';
                return (
                    '<button type="button" class="notif-item" data-notif-id="' + item.id + '" data-notif-tipo="' + escapeHtml(tipo) + '">' +
                    '<span class="notif-item-icon bg-' + color + '"><i class="bi ' + icon + '"></i></span>' +
                    '<span class="notif-item-body">' +
                    '<strong>' + escapeHtml(item.titulo) + '</strong>' +
                    '<small>' + escapeHtml(item.subtitulo) + '</small>' +
                    '</span>' +
                    '<span class="notif-item-time">' + escapeHtml(item.data) + '</span>' +
                    '</button>'
                );
            }).join('');

            lista.querySelectorAll('.notif-item').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = parseInt(btn.getAttribute('data-notif-id'), 10);
                    const tipo = btn.getAttribute('data-notif-tipo') || 'geral';
                    const item = ultimosItems.find(function (i) { return i.id === id; }) || { id: id, tipo: tipo };
                    fecharPopup();
                    executarAcao(item);
                });
            });
        }

        function executarAcao(item) {
            if (options.onItemClick && typeof options.onItemClick === 'function') {
                options.onItemClick(item);
                return;
            }
            const fn = options.verTodasFn;
            if (fn && typeof window[fn] === 'function') {
                window[fn](item);
            }
        }

        function carregarNotificacoes(playSound) {
            return fetch('check_notificacoes.php', { cache: 'no-store' })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data) return;
                    const qtd = parseInt(data.qtd, 10) || 0;
                    if (playSound && som && qtd > qtdAnterior) {
                        som.play().catch(function () {});
                    }
                    qtdAnterior = qtd;
                    atualizarBadges(qtd);
                    renderLista(data.items || []);
                    if (typeof options.onUpdate === 'function') {
                        options.onUpdate(data);
                    }
                })
                .catch(function () {});
        }

        function abrirPopup() {
            popup.classList.remove('d-none');
            bell.setAttribute('aria-expanded', 'true');
            aberto = true;
            carregarNotificacoes(false);
        }

        function fecharPopup() {
            popup.classList.add('d-none');
            bell.setAttribute('aria-expanded', 'false');
            aberto = false;
        }

        function togglePopup(e) {
            e.preventDefault();
            e.stopPropagation();
            if (aberto) {
                fecharPopup();
            } else {
                abrirPopup();
            }
        }

        bell.addEventListener('click', togglePopup);
        if (btnFechar) btnFechar.addEventListener('click', function (e) {
            e.stopPropagation();
            fecharPopup();
        });
        if (btnVerTodas) btnVerTodas.addEventListener('click', function (e) {
            e.stopPropagation();
            fecharPopup();
            executarAcao(null);
        });

        document.addEventListener('click', function (e) {
            if (aberto && !wrap.contains(e.target)) {
                fecharPopup();
            }
        });

        carregarNotificacoes(false);
        setInterval(function () {
            carregarNotificacoes(true);
        }, options.pollInterval || 120000);

        window.fecharNotificacoesPopup = fecharPopup;
        window.recarregarNotificacoes = function () {
            return carregarNotificacoes(false);
        };
    };
})();
