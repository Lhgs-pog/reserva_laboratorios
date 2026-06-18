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

    function itemKey(item) {
        const tipo = item.tipo || 'geral';
        return String(tipo) + ':' + String(item.id);
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
        const contexto = options.contexto || '';
        let aberto = false;
        let ultimosItems = [];
        let idsConhecidos = new Set((options.initialIds || []).map(String));
        let baselinePronta = false;
        let audioPronto = false;
        let audioCtx = null;
        const playSound = !!options.playSound;
        const somVolume = typeof options.somVolume === 'number' ? options.somVolume : 0.05;

        function ensureAudioContext() {
            if (!playSound || audioPronto) {
                return Promise.resolve();
            }
            try {
                if (!audioCtx) {
                    const Ctx = window.AudioContext || window.webkitAudioContext;
                    if (!Ctx) return Promise.resolve();
                    audioCtx = new Ctx();
                }
                if (audioCtx.state === 'suspended') {
                    return audioCtx.resume().then(function () {
                        audioPronto = true;
                    }).catch(function () {});
                }
                audioPronto = true;
            } catch (e) {
                /* navegador sem Web Audio */
            }
            return Promise.resolve();
        }

        /** Ding curto e suave — só para notificação nova (não usa MP3 externo). */
        function tocarSom() {
            if (!playSound) return;
            ensureAudioContext().then(function () {
                if (!audioCtx || !audioPronto) return;
                try {
                    const t0 = audioCtx.currentTime;
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(740, t0);
                    osc.frequency.exponentialRampToValueAtTime(620, t0 + 0.1);
                    gain.gain.setValueAtTime(0.0001, t0);
                    gain.gain.linearRampToValueAtTime(somVolume * 0.85, t0 + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t0 + 0.22);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(t0);
                    osc.stop(t0 + 0.3);
                } catch (e) {
                    /* ignore */
                }
            });
        }

        let popupAnchor = null;
        let scrollLockPadding = 0;

        function bloquearScrollEncadeado(el) {
            if (!el || el.dataset.notifScrollBound === '1') return;
            el.dataset.notifScrollBound = '1';
            el.addEventListener('wheel', function (e) {
                const maxScroll = el.scrollHeight - el.clientHeight;
                if (maxScroll <= 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                const goingUp = e.deltaY < 0;
                const goingDown = e.deltaY > 0;
                const atTop = el.scrollTop <= 0;
                const atBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 1;
                if ((goingUp && atTop) || (goingDown && atBottom)) {
                    e.preventDefault();
                }
                e.stopPropagation();
            }, { passive: false });
            el.addEventListener('touchmove', function (e) {
                e.stopPropagation();
            }, { passive: true });
        }

        function travarScrollPagina() {
            scrollLockPadding = window.innerWidth - document.documentElement.clientWidth;
            document.body.classList.add('notif-scroll-lock');
            if (scrollLockPadding > 0) {
                document.body.style.paddingRight = scrollLockPadding + 'px';
            }
        }

        function destravarScrollPagina() {
            document.body.classList.remove('notif-scroll-lock');
            document.body.style.paddingRight = '';
        }

        function posicionarPopup() {
            if (popup.classList.contains('d-none')) {
                return;
            }

            const margin = 8;
            const gap = 10;
            const rect = bell.getBoundingClientRect();
            const popupWidth = Math.min(380, window.innerWidth - margin * 2);
            const maxHeight = Math.min(420, window.innerHeight - margin * 2);

            popup.classList.add('notif-popup-fixed');
            popup.style.width = popupWidth + 'px';
            popup.style.maxWidth = popupWidth + 'px';
            popup.style.maxHeight = maxHeight + 'px';
            popup.style.left = 'auto';

            const measuredHeight = popup.offsetHeight || 200;

            let top = rect.bottom + gap;
            if (top + measuredHeight > window.innerHeight - margin) {
                const topAbove = rect.top - gap - measuredHeight;
                top = topAbove >= margin ? topAbove : margin;
            }
            top = Math.max(margin, Math.min(top, window.innerHeight - margin - 80));
            popup.style.top = Math.round(top) + 'px';

            let right = window.innerWidth - rect.right;
            const maxRight = window.innerWidth - popupWidth - margin;
            right = Math.max(margin, Math.min(right, maxRight));
            popup.style.right = Math.round(right) + 'px';
        }

        function anexarPopupNoBody() {
            if (popup.parentNode !== document.body) {
                popupAnchor = wrap;
                document.body.appendChild(popup);
            }
        }

        function restaurarPopupNoNav() {
            if (popupAnchor && popup.parentNode === document.body) {
                popupAnchor.appendChild(popup);
            }
            popup.classList.remove('notif-popup-fixed');
            popup.style.top = '';
            popup.style.left = '';
            popup.style.right = '';
            popup.style.width = '';
            popup.style.maxWidth = '';
        }

        function urlNotificacoes() {
            return contexto
                ? 'check_notificacoes.php?contexto=' + encodeURIComponent(contexto)
                : 'check_notificacoes.php';
        }

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
                const icon = (item.icon || 'bi-bell').replace(/[^a-z0-9-]/gi, '');
                const tipo = item.tipo || 'geral';
                const id = parseInt(item.id, 10) || 0;
                const timeBlock = item.horario
                    ? '<span class="notif-item-date">' + escapeHtml(item.data) + '</span>' +
                      '<span class="notif-item-hora">' + escapeHtml(item.horario) + '</span>'
                    : '<span class="notif-item-date">' + escapeHtml(item.data) + '</span>';
                return (
                    '<button type="button" class="notif-item" data-notif-id="' + id + '" data-notif-tipo="' + escapeHtml(tipo) + '">' +
                    '<span class="notif-item-icon bg-' + escapeHtml(color) + '"><i class="bi ' + icon + '"></i></span>' +
                    '<span class="notif-item-body">' +
                    '<strong>' + escapeHtml(item.titulo) + '</strong>' +
                    '<small>' + escapeHtml(item.subtitulo) + '</small>' +
                    '</span>' +
                    '<span class="notif-item-time">' + timeBlock + '</span>' +
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

        function carregarNotificacoes(permitirSom) {
            return fetch(urlNotificacoes(), { cache: 'no-store', credentials: 'same-origin' })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data) return;
                    const items = data.items || [];
                    const qtd = parseInt(data.qtd, 10) || 0;
                    const chavesAtuais = items.map(itemKey);

                    if (baselinePronta && permitirSom && playSound) {
                        const temNovidade = chavesAtuais.some(function (chave) {
                            return !idsConhecidos.has(chave);
                        });
                        if (temNovidade) {
                            tocarSom();
                        }
                    }

                    idsConhecidos = new Set(chavesAtuais);
                    baselinePronta = true;
                    atualizarBadges(qtd);
                    renderLista(items);
                    if (typeof options.onUpdate === 'function') {
                        options.onUpdate(data);
                    }
                    if (aberto) {
                        posicionarPopup();
                    }
                })
                .catch(function () {});
        }

        function abrirPopup() {
            ensureAudioContext();
            anexarPopupNoBody();
            popup.classList.remove('d-none');
            bell.setAttribute('aria-expanded', 'true');
            aberto = true;
            travarScrollPagina();
            bloquearScrollEncadeado(lista);
            bloquearScrollEncadeado(popup);
            posicionarPopup();
            carregarNotificacoes(false);
        }

        function fecharPopup() {
            popup.classList.add('d-none');
            bell.setAttribute('aria-expanded', 'false');
            aberto = false;
            destravarScrollPagina();
            restaurarPopupNoNav();
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
            if (aberto && !wrap.contains(e.target) && !popup.contains(e.target)) {
                fecharPopup();
            }
        });

        window.addEventListener('resize', function () {
            if (aberto) posicionarPopup();
        });

        window.addEventListener('scroll', function () {
            if (aberto) posicionarPopup();
        }, { passive: true });

        bloquearScrollEncadeado(lista);

        carregarNotificacoes(false);
        setInterval(function () {
            carregarNotificacoes(true);
        }, options.pollInterval || 120000);

        window.fecharNotificacoesPopup = fecharPopup;
        window.recarregarNotificacoes = function (permitirSom) {
            return carregarNotificacoes(!!permitirSom);
        };
    };
})();
