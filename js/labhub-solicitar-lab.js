(function () {
    'use strict';

    function slotKey(data, turno, periodo) {
        return [data, turno, periodo].join('|');
    }

    function initSolicitarLabsDisponiveis() {
        const dataEl = document.getElementById('solicitar-data');
        const turnoEl = document.getElementById('solicitar-turno');
        const periodoEl = document.getElementById('solicitar-periodo');
        const labEl = document.getElementById('solicitar-lab');
        const statusEl = document.getElementById('solicitar-lab-status');
        const btnReserva = document.getElementById('btn-solicitar-reserva');
        const btnEspera = document.getElementById('btn-lista-espera');
        const formEl = document.getElementById('form-solicitar-lab');
        if (!dataEl || !turnoEl || !periodoEl || !labEl || !statusEl) return;

        if (dataEl.dataset.lhLabsInit === '1') return;
        dataEl.dataset.lhLabsInit = '1';

        let debounceTimer = null;
        let abortCtrl = null;
        let reqSeq = 0;
        let lastLoadedKey = '';
        let loading = false;

        function setStatus(html, className) {
            statusEl.innerHTML = html;
            statusEl.className = 'form-text mt-2 ' + (className || 'text-muted');
        }

        function setModoReserva(ativo) {
            if (btnReserva) {
                btnReserva.classList.toggle('d-none', !ativo);
                btnReserva.disabled = !ativo;
            }
            if (btnEspera) {
                btnEspera.classList.toggle('d-none', ativo);
            }
            labEl.required = !!ativo;
        }

        function setModoEspera(ativo) {
            if (btnEspera) {
                btnEspera.classList.toggle('d-none', !ativo);
                btnEspera.disabled = !ativo;
            }
            if (btnReserva) {
                btnReserva.classList.toggle('d-none', ativo);
                btnReserva.disabled = true;
            }
            labEl.required = false;
        }

        function setModoIndisponivel() {
            if (btnReserva) {
                btnReserva.classList.add('d-none');
                btnReserva.disabled = true;
            }
            if (btnEspera) {
                btnEspera.classList.add('d-none');
                btnEspera.disabled = true;
            }
            labEl.required = false;
        }

        function applyLabOptions(items, cfg) {
            if (typeof window.setLabhubComboboxOptions === 'function') {
                window.setLabhubComboboxOptions(labEl, items, cfg);
            }
        }

        function resetLabs(msg) {
            lastLoadedKey = '';
            setModoIndisponivel();
            applyLabOptions([], {
                placeholder: msg || 'Preencha data, turno e horário acima',
                disabled: true,
            });
        }

        function loadLabs(force) {
            const data = dataEl.value;
            const turno = turnoEl.value;
            const periodo = periodoEl.value;
            const key = slotKey(data, turno, periodo);

            if (!data || !turno || !periodo) {
                if (abortCtrl) abortCtrl.abort();
                loading = false;
                resetLabs('Preencha data, turno e horário acima');
                setStatus('Selecione data, turno e horário para ver os laboratórios livres.');
                return;
            }

            if (!force && key === lastLoadedKey && !loading) {
                return;
            }

            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();
            const mySeq = ++reqSeq;
            loading = true;

            setModoIndisponivel();
            setStatus('<span class="spinner-border spinner-border-sm me-1"></span> Verificando laboratórios livres...', 'text-secondary');

            const url = 'painel_professor.php?ajax=labs_disponiveis'
                + '&data_reserva=' + encodeURIComponent(data)
                + '&turno=' + encodeURIComponent(turno)
                + '&periodo=' + encodeURIComponent(periodo);

            const timeoutId = setTimeout(function () {
                if (mySeq === reqSeq && abortCtrl) abortCtrl.abort();
            }, 15000);

            fetch(url, { cache: 'no-store', credentials: 'same-origin', signal: abortCtrl.signal })
                .then(function (res) {
                    return res.text().then(function (text) {
                        let payload;
                        try {
                            payload = JSON.parse(text);
                        } catch (e) {
                            throw new Error('Resposta inválida do servidor.');
                        }
                        if (!res.ok && !payload.error) {
                            throw new Error('Erro ' + res.status + ' ao consultar disponibilidade.');
                        }
                        return payload;
                    });
                })
                .then(function (payload) {
                    if (mySeq !== reqSeq) return;

                    loading = false;
                    lastLoadedKey = key;

                    if (!payload.ok) {
                        resetLabs('Indisponível');
                        setStatus('<i class="bi bi-exclamation-triangle me-1"></i>' + (payload.error || 'Erro ao verificar disponibilidade.'), 'text-warning');
                        return;
                    }

                    if (payload.ja_na_fila) {
                        applyLabOptions([], {
                            placeholder: 'Você já está na lista de espera',
                            disabled: true,
                        });
                        setModoIndisponivel();
                        setStatus(
                            '<i class="bi bi-hourglass-bottom me-1"></i> Você já está na lista de espera deste horário'
                            + (payload.posicao_fila ? ' (<strong>' + payload.posicao_fila + 'º</strong> na fila).' : '.'),
                            'text-info'
                        );
                        return;
                    }

                    const labs = payload.labs || [];
                    if (labs.length === 0) {
                        applyLabOptions([], {
                            placeholder: 'Todos os laboratórios ocupados',
                            disabled: true,
                        });
                        setModoEspera(true);
                        setStatus(
                            '<i class="bi bi-exclamation-circle me-1"></i> Todos os laboratórios estão ocupados neste horário. '
                            + 'Use <strong>Entrar na lista de espera</strong> — avisaremos na plataforma e por e-mail.',
                            'text-warning'
                        );
                        return;
                    }

                    applyLabOptions(labs, {
                        placeholder: 'Busque o laboratório...',
                        disabled: false,
                        required: true,
                    });
                    setModoReserva(true);
                    setStatus(
                        '<i class="bi bi-check-circle me-1"></i> '
                        + labs.length + ' laboratório(s) livre(s) de ' + (payload.total_labs || labs.length) + '.',
                        'lh-text-sala'
                    );
                })
                .catch(function (err) {
                    if (mySeq !== reqSeq || err.name === 'AbortError') return;
                    loading = false;
                    lastLoadedKey = '';
                    resetLabs('Erro ao carregar');
                    setStatus('<i class="bi bi-wifi-off me-1"></i> ' + (err.message || 'Erro de conexão.'), 'text-danger');
                })
                .finally(function () {
                    clearTimeout(timeoutId);
                    if (mySeq === reqSeq) loading = false;
                });
        }

        function scheduleLoad(force) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                loadLabs(!!force);
            }, 350);
        }

        dataEl.addEventListener('change', function () { scheduleLoad(true); });
        turnoEl.addEventListener('change', function () { scheduleLoad(true); });
        periodoEl.addEventListener('change', function () { scheduleLoad(true); });

        if (formEl && btnEspera) {
            formEl.addEventListener('submit', function (e) {
                if (e.submitter === btnEspera) {
                    labEl.removeAttribute('required');
                    labEl.disabled = false;
                    return;
                }
                if (!labEl.value) {
                    e.preventDefault();
                    setStatus('<i class="bi bi-exclamation-triangle me-1"></i> Selecione um laboratório livre.', 'text-warning');
                }
            });
        }

        resetLabs();
    }

    document.addEventListener('labhub-combobox-ready', initSolicitarLabsDisponiveis);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(initSolicitarLabsDisponiveis, 0);
        });
    } else {
        setTimeout(initSolicitarLabsDisponiveis, 0);
    }
})();
