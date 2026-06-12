<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Painel da Coordenação - UNICEPLAC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales-all.global.min.js"></script>

    <script>const savedTheme = localStorage.getItem('tema-uniceplac') || 'light'; document.documentElement.setAttribute('data-bs-theme', savedTheme);</script>

    <style>
        :root {
            --verde-uniceplac: #00734F;
            --roxo-uniceplac: #421B71;
            --laranja-uniceplac: #F0733C;
            --azul-google: #4285F4;
            --manha-cor: var(--verde-uniceplac);
            --tarde-cor: var(--laranja-uniceplac);
            --noite-cor: var(--roxo-uniceplac);
        }

        body {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .card,
        .card-header,
        .form-control,
        .form-select,
        .btn,
        .badge,
        .alert,
        .offcanvas,
        .modal-content {
            border-radius: 0 !important;
        }

        .bg-uniceplac {
            background-color: var(--verde-uniceplac) !important;
        }

        .content-section {
            display: none;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .offcanvas-menu-link {
            padding: 12px 20px;
            color: #495057;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #f1f1f1;
            font-weight: 500;
            transition: 0.2s;
            cursor: pointer;
        }

        .offcanvas-menu-link:hover,
        .offcanvas-menu-link.active-link {
            background-color: rgba(0, 115, 79, 0.05);
            color: var(--verde-uniceplac);
            border-right: 4px solid var(--verde-uniceplac);
        }

        .avatar-img-small {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50% !important;
            border: 2px solid #dee2e6;
            cursor: pointer;
        }

        .top-icon-btn {
            color: #495057;
            font-size: 1.3rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            transition: 0.2s;
        }

        .top-icon-btn:hover {
            color: var(--verde-uniceplac);
        }

        #nova_foto_input {
            display: none;
        }

        .transition-transform {
            transition: transform 0.3s ease;
        }

        .apple-search-box {
            background: #f5f5f7;
            border-radius: 20px;
            padding: 6px 16px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .apple-search-box:focus-within {
            background: #fff;
            border-color: rgba(0, 122, 255, 0.5);
            box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
        }

        .apple-search-input {
            border: none;
            background: transparent;
            width: 100%;
            padding: 8px;
            outline: none;
            color: #1d1d1f;
        }

        .grade-wrapper {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            max-height: 700px;
            overflow-y: auto;
        }

        .grade-container {
            display: flex;
            width: 100%;
            min-width: 1200px;
        }

        .grade-coluna {
            flex: 1;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }

        .grade-coluna:last-child {
            border-right: none;
        }

        .grade-cabecalho {
            background: #f8f9fa;
            text-align: center;
            padding: 12px 10px;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #495057;
            border-bottom: 3px solid var(--azul-google);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .grade-corpo {
            padding: 8px;
            flex-grow: 1;
            background-color: #fafbfe;
        }

        .aula-card-google {
            background: rgba(0, 115, 79, 0.05);
            border-left: 4px solid var(--verde-uniceplac);
            border-radius: 6px;
            padding: 10px;
            margin: 8px;
            font-size: 0.85rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .aula-card-google.matutino {
            background: rgba(0, 115, 79, 0.12);
            border-left-color: var(--manha-cor);
        }

        .aula-card-google.vespertino {
            background: rgba(240, 115, 60, 0.12);
            border-left-color: var(--tarde-cor);
        }

        .aula-card-google.noturno {
            background: rgba(66, 27, 113, 0.12);
            border-left-color: var(--noite-cor);
        }

        /* =======================================================
           EFEITO VIDRO E SELOS (EAD NA GRADE)
           ======================================================= */
        .aula-card-google.aula-ead-glass {
            background: rgba(255, 193, 7, 0.15) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(255, 193, 7, 0.4) !important;
            border-left: 4px solid #ffc107 !important;
            box-shadow: 0 4px 10px rgba(255, 193, 7, 0.1) !important;
        }

        [data-bs-theme="dark"] .aula-card-google.aula-ead-glass {
            background: rgba(255, 193, 7, 0.08) !important;
            border: 1px solid rgba(255, 193, 7, 0.2) !important;
            border-left: 4px solid #ffca2c !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3) !important;
            color: #fff !important;
        }

        .selo-matutino {
            background-color: var(--manha-cor) !important;
            color: #fff !important;
        }

        .selo-vespertino {
            background-color: var(--tarde-cor) !important;
            color: #fff !important;
        }

        .selo-noturno {
            background-color: var(--noite-cor) !important;
            color: #fff !important;
        }

        [data-bs-theme="dark"] .text-dark.prof-nome {
            color: #f8f9fa !important;
        }

        #calendarioGeral {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        /* Estilos Padrões do Calendário Claro */
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 12px;
            overflow: hidden;
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: rgba(0, 0, 0, 0.05);
        }

        .fc-col-header-cell {
            background-color: #fbfbfd;
            padding: 8px 0;
            font-weight: 600;
            color: #86868b;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .fc .fc-button-group>.fc-button {
            background: #f5f5f7 !important;
            color: #007aff !important;
            border-color: #d2d2d7 !important;
            text-transform: capitalize;
            box-shadow: none !important;
            font-weight: 500;
            transition: 0.2s;
        }

        .fc .fc-button-group>.fc-button:hover {
            background: #e8e8ed !important;
        }

        .fc .fc-button-group>.fc-button.fc-button-active {
            background: #007aff !important;
            color: #fff !important;
            border-color: #007aff !important;
        }

        .fc .fc-today-button {
            background: #fff !important;
            color: #007aff !important;
            border-color: #d2d2d7 !important;
            font-weight: 600;
            text-transform: capitalize;
        }

        .fc-toolbar-title {
            font-weight: 700 !important;
            color: #1d1d1f;
            text-transform: capitalize;
        }

        .apple-event-fixa {
            --fc-event-bg-color: rgba(66, 27, 113, 0.12);
            --fc-event-border-color: var(--roxo-uniceplac);
            --fc-event-text-color: var(--roxo-uniceplac);
        }

        .apple-event-avulsa {
            --fc-event-bg-color: rgba(240, 115, 60, 0.12);
            --fc-event-border-color: var(--laranja-uniceplac);
            --fc-event-text-color: #c95b28;
        }

        .apple-event-feriado {
            --fc-event-bg-color: rgba(220, 53, 69, 0.12);
            --fc-event-border-color: #dc3545;
            --fc-event-text-color: #a71d2a;
        }

        .fc-event {
            border-left-width: 4px !important;
            border-radius: 6px !important;
            border-top: none !important;
            border-right: none !important;
            border-bottom: none !important;
            padding: 2px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            margin-bottom: 2px;
            cursor: pointer;
        }

        .fc-event-main {
            width: 100%;
        }

        .text-truncate-multi {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .text-truncate-single {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        /* ==========================================================
           TEMA ESCURO (DARK MODE) - GERAL
           ========================================================== */
        [data-bs-theme="dark"] body {
            background-color: #121212;
            color: #e0e0e0;
        }

        [data-bs-theme="dark"] .bg-white,
        [data-bs-theme="dark"] .bg-light {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
        }

        [data-bs-theme="dark"] .card {
            background-color: #1e1e1e;
            border-color: #333 !important;
        }

        [data-bs-theme="dark"] .text-dark {
            color: #f8f9fa !important;
        }

        [data-bs-theme="dark"] .text-secondary,
        [data-bs-theme="dark"] .text-muted {
            color: #adb5bd !important;
        }

        [data-bs-theme="dark"] .border,
        [data-bs-theme="dark"] .border-bottom {
            border-color: #333 !important;
        }

        [data-bs-theme="dark"] .table {
            color: #e0e0e0;
            border-color: #444;
        }

        [data-bs-theme="dark"] .table-light th {
            background-color: #2a2a2a !important;
            color: #e0e0e0;
            border-color: #444;
        }

        [data-bs-theme="dark"] .offcanvas {
            background-color: #1e1e1e !important;
        }

        [data-bs-theme="dark"] .offcanvas-menu-link {
            color: #e0e0e0;
            border-bottom-color: #333;
        }

        [data-bs-theme="dark"] .offcanvas-menu-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #2a2a2a;
            color: #fff;
            border-color: #444;
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #333;
            color: #fff;
            border-color: var(--verde-uniceplac);
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: #1e1e1e;
            border-color: #444;
            border-radius: 20px !important;
        }

        [data-bs-theme="dark"] .top-icon-btn {
            color: #e0e0e0;
        }

        [data-bs-theme="dark"] .top-icon-btn:hover {
            color: var(--laranja-uniceplac);
        }

        [data-bs-theme="dark"] .apple-search-box {
            background: #2c2c2e;
        }

        [data-bs-theme="dark"] .apple-search-box:focus-within {
            background: #1c1c1e;
        }

        [data-bs-theme="dark"] .apple-search-input {
            color: #f8f9fa;
        }

        [data-bs-theme="dark"] .aula-card-google {
            background: rgba(0, 115, 79, 0.1);
            color: #e0e0e0;
        }

        [data-bs-theme="dark"] .aula-card-google.matutino {
            background: rgba(0, 115, 79, 0.15);
        }

        [data-bs-theme="dark"] .aula-card-google.vespertino {
            background: rgba(240, 115, 60, 0.15);
        }

        [data-bs-theme="dark"] .aula-card-google.noturno {
            background: rgba(66, 27, 113, 0.25);
        }

        /* ==========================================================
           DARK MODE - GRADE DE HORÁRIOS (FORÇA MÁXIMA)
           ========================================================== */
        [data-bs-theme="dark"] .grade-wrapper {
            background-color: #1e1e1e !important;
            border-color: #333 !important;
        }

        [data-bs-theme="dark"] .grade-coluna {
            border-right-color: #333 !important;
        }

        [data-bs-theme="dark"] .grade-cabecalho {
            background-color: #2a2a2a !important;
            color: #e0e0e0 !important;
            border-bottom: 3px solid #0d6efd !important;
        }

        [data-bs-theme="dark"] .grade-corpo {
            background-color: #121212 !important;
        }

        /* ==========================================================
           GARANTIA: ESCONDE CABEÇALHOS DE IMPRESSÃO NA TELA
           ========================================================== */
        @media screen {

            .print-only-header,
            .d-print-block {
                display: none !important;
            }
        }

        /* ==========================================================
           MODO DE IMPRESSÃO (PDF) - EXTERMINADOR DE PÁGINAS EM BRANCO
           ========================================================== */
        @media print {
            @page {
                size: landscape;
                margin: 5mm;
            }

            /* 1. A BOMBA ATÔMICA: Destrava qualquer altura ou rolagem oculta em TODOS os elementos */
            *,
            *::before,
            *::after {
                overflow: visible !important;
                height: auto !important;
                min-height: 0 !important;
                max-height: none !important;
            }

            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* 2. ESCONDE O LIXO (Menus, modais, painéis) */
            nav,
            .offcanvas,
            form,
            .btn,
            .top-icon-btn,
            #container-mensagens,
            .card-header,
            .apple-search-box,
            .d-print-none,
            #navBell,
            .modal,
            .modal-backdrop {
                display: none !important;
            }

            .content-section {
                display: none !important;
            }

            #sessao-quadro-horario {
                display: block !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            /* 3. O CABEÇALHO DE IMPRESSÃO */
            .print-only-header {
                display: flex !important;
                margin-bottom: 10px !important;
                page-break-after: avoid !important;
            }

            /* O SEGREDO PARA A LOGO FICAR PERFEITA NO PAPEL */
            .print-only-header img {
                height: 15mm !important;
                /* Altura exata de 1,5 centímetros no papel */
                max-height: 15mm !important;
                width: auto !important;
                /* Mantém a proporção sem amassar a imagem */
                margin-right: 15px !important;
            }

            /* 4. A GRADE (Forçada a caber na largura da página) */
            .container-fluid {
                padding: 0 !important;
                margin: 0 !important;
            }

            .bg-white.border.shadow-sm.p-3.overflow-auto {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            .grade-wrapper {
                border: none !important;
                display: block !important;
            }

            .grade-container {
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                width: 100% !important;
                page-break-inside: auto !important;
            }

            .grade-coluna {
                flex: 1 1 0 !important;
                width: 16.66% !important;
                min-width: 0 !important;
                /* Ajuda a não estourar a largura do A4 */
                border-right: 1px solid #ccc !important;
                display: block !important;
                /* Libera os cards para caírem naturalmente */
            }

            .grade-coluna:last-child {
                border-right: none !important;
            }

            .grade-cabecalho {
                font-size: 10px !important;
                padding: 4px !important;
                border-bottom: 2px solid #000 !important;
                text-align: center !important;
                page-break-after: avoid !important;
            }

            .grade-corpo {
                display: block !important;
                padding: 2px !important;
            }

            /* 5. OS CARDS DAS AULAS (Compactos e inquebráveis) */
            .aula-card-google {
                page-break-inside: avoid !important;
                /* Impede que corte a aula no meio */
                break-inside: avoid !important;
                font-size: 8px !important;
                padding: 4px !important;
                margin: 2px !important;
                border: 1px solid #e0e0e0 !important;
                border-left-width: 4px !important;
            }

            .aula-card-google * {
                font-size: 8px !important;
                line-height: 1.1 !important;
                margin-bottom: 1px !important;
            }
        }
    </style>
</head>

<body>

    <div class="d-none d-print-block w-100 mb-4 print-only-header">
        <div class="d-flex align-items-center border-bottom pb-3">
            <img src="uniceplac2.png" alt="Logo" style="height: 60px; margin-right: 20px;">
            <div>
                <h4 class="mb-0 fw-bold" style="color: #00734F;">CENTRAL DE RESERVAS ACADÊMICAS</h4>
                <p class="mb-0 text-muted small">Relatório Gerencial de Ocupação de Laboratórios</p>
            </div>
        </div>
    </div>

    <form id="formFotoPerfil" action="painel_coordenador.php" method="POST" enctype="multipart/form-data"
        class="d-none">
        <input type="file" name="nova_foto" id="nova_foto_input" accept="image/png, image/jpeg, image/webp">
    </form>

    <nav class="navbar navbar-light bg-white mb-4 border-bottom shadow-sm sticky-top">
        <div class="container-fluid px-3 px-md-4">
            <span class="navbar-brand d-flex align-items-center"><img src="uniceplac2.png" id="navbarLogo" alt="Logo"
                    style="height: 70px; margin-right: 12px; transition: 0.3s;"></span>
            <div class="ms-auto d-flex align-items-center">
                <div class="me-4 top-icon-btn" id="themeToggleBtn" title="Alternar Tema"><i class="bi bi-moon-stars"
                        id="themeIcon"></i></div>
                <div class="position-relative me-4 top-icon-btn" id="navBell" title="Ver Solicitações Pendentes"
                    onclick="showSection('sessao-historico-geral')">
                    <i class="bi bi-bell"></i>
                    <span id="badge-nav-bell"
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light text-white <?= $qtd_pendentes > 0 ? '' : 'd-none' ?>"
                        style="font-size: 0.65rem; padding: 0.25em 0.4em;"><?= $qtd_pendentes ?></span>
                </div>
                <div class="me-3 top-icon-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"><i
                        class="bi bi-grid-3x3-gap fs-5"></i></div>
                <img src="<?= htmlspecialchars($foto_atual) ?>" alt="Foto" class="avatar-img-small ms-1"
                    id="btnAlterarFotoNav">
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header bg-uniceplac text-white py-3 border-0">
            <h6 class="offcanvas-title fw-bold">Coordenação</h6><button type="button" class="btn-close btn-close-white"
                data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column bg-white">
            <div class="p-4 text-center border-bottom bg-light">
                <img src="<?= htmlspecialchars($foto_atual) ?>" alt="Foto"
                    style="width: 80px; height: 80px; object-fit: cover; border-radius: 50% !important; border: 3px solid var(--roxo-uniceplac);"
                    class="shadow-sm mb-2">
                <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($_SESSION['nome']) ?></h5>
                <span class="badge bg-uniceplac text-uppercase mt-2 px-3 py-1">Coordenador</span>
            </div>
            <div class="flex-grow-1 overflow-auto">
                <div class="p-3 text-muted small fw-bold text-uppercase opacity-50">Macro-Gestão</div>
                <a href="javascript:void(0);" onclick="showSection('sessao-calendario-geral')"
                    data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link text-primary fw-bold bg-light border-start border-4 border-primary"><i
                        class="bi bi-calendar3 me-2"></i> Calendário Geral</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-quadro-horario')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-table me-2"></i> Quadro de Horários (Editor)</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-relatorios')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-graph-up-arrow text-success me-2"></i> Relatórios e
                    Métricas</a>

                <div class="p-3 text-muted small fw-bold text-uppercase opacity-50">Gestão Dinâmica</div>
                <a href="javascript:void(0);" onclick="showSection('sessao-historico-geral')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link fw-bold" style="color: #dc3545;">
                    <i class="bi bi-bell-fill text-danger me-2"></i> Solicitações Pendentes
                    <span id="badge-pendentes-menu" class="badge bg-danger ms-2 <?= $qtd_pendentes > 0 ? '' : 'd-none' ?>"><?= $qtd_pendentes ?></span>
                </a>
                <a href="javascript:void(0);" onclick="showSection('sessao-agendar-lab')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-calendar-plus text-primary me-2"></i> Agendar
                    Laboratório</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-historico-geral')"
                    data-bs-dismiss="offcanvas" class="offcanvas-menu-link"><i
                        class="bi bi-clock-history text-info me-2"></i> Histórico de Solicitações</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-ensalamento')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-building text-primary me-2"></i> Grade de
                    Ensalamento</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-usuarios')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-people-fill text-secondary me-2"></i> Usuários do Sistema</a>

                <div class="p-3 text-muted small fw-bold text-uppercase opacity-50 border-top mt-2">Cadastros Base</div>
                <a href="javascript:void(0);" onclick="showSection('sessao-cursos')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-mortarboard text-primary me-2"></i> Cursos</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-semestres')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-calendar-range text-dark me-2"></i> Semestres</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-disciplinas')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-book-half text-secondary me-2"></i> Disciplinas</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-labs')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-pc-display text-info me-2"></i> Laboratórios</a>
                <a href="javascript:void(0);" onclick="showSection('sessao-locais')" data-bs-dismiss="offcanvas"
                    class="offcanvas-menu-link"><i class="bi bi-geo-alt-fill text-danger me-2"></i> Locais
                    (Blocos/Salas)</a>

                <div class="p-3 text-muted small fw-bold text-uppercase opacity-50 border-top mt-2">Visão Operacional
                </div>
                <div class="fw-bold text-dark d-flex justify-content-between align-items-center p-3"
                    onclick="abrirSanfona('dropMenuTI', 'setaDropTI')"
                    style="background-color: #fff3cd; border-left: 4px solid #ffc107; cursor: pointer; transition: 0.3s;">
                    <span><i class="bi bi-headset text-warning me-2 fs-5"></i> Menu de TI</span>
                    <i class="bi bi-chevron-down transition-transform text-muted" id="setaDropTI"
                        style="transition: transform 0.3s ease;"></i>
                </div>
                <div id="dropMenuTI" style="display: none;">
                    <div class="bg-light border-start border-4 border-warning ms-3 mb-2">
                        <a href="painel_suporte.php" class="p-3 text-muted d-block text-decoration-none fw-bold"
                            onmouseover="this.style.backgroundColor='rgba(0,0,0,0.05)'"
                            onmouseout="this.style.backgroundColor='transparent'"><i
                                class="bi bi-ticket-detailed me-2"></i> Acessar Painel TI</a>
                    </div>
                </div>
            </div>
            <div class="p-3 border-top mt-auto"><a href="logout.php"
                    class="btn btn-outline-danger w-100 fw-bold">Sair</a></div>
        </div>
    </div>

    <div class="container-fluid px-4 pb-5">
        <div id="container-mensagens"><?= $mensagem ?></div>

        <?php if ($erro_banco_relatorio): ?>
            <div class="alert alert-warning text-center shadow-sm"><i
                    class="bi bi-tools fs-3 d-block mb-2"></i><strong>Quase lá!</strong> Rode o UPDATE SQL no banco de dados
                para criar as colunas de Carga Horária e habilitar os Relatórios.</div>
        <?php endif; ?>

        <?php require __DIR__ . '/abas/sessao-calendario-geral.php'; ?>

        <?php require __DIR__ . '/abas/sessao-relatorios.php'; ?>

        <?php require __DIR__ . '/abas/sessao-historico-geral.php'; ?>

        <?php require __DIR__ . '/abas/sessao-quadro-horario.php'; ?>

    </div>
    <?php require __DIR__ . '/abas/sessao-agendar-lab.php'; ?>

    <?php require __DIR__ . '/abas/sessao-cursos.php'; ?>

    <?php require __DIR__ . '/abas/sessao-semestres.php'; ?>

    <?php require __DIR__ . '/abas/sessao-disciplinas.php'; ?>

    <?php require __DIR__ . '/abas/sessao-labs.php'; ?>

    <?php require __DIR__ . '/abas/sessao-locais.php'; ?>

    <!-- Seção: Gestão de Usuários -->
    <div id="sessao-usuarios" class="content-section container-fluid px-4 pb-5">
        <div class="card shadow-sm border-0 mb-4" style="border-top: 4px solid var(--roxo-uniceplac);">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold" style="color:var(--roxo-uniceplac);"><i class="bi bi-people-fill me-3 fs-4"></i>Usuários do Sistema</h5>
                <span class="badge" style="background:var(--roxo-uniceplac);"><?= count($lista_usuarios) ?> cadastrados</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:600px;overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-4 py-3">Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th class="pe-4">E-mail Verificado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lista_usuarios as $u): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($u['nome']) ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['perfil'] === 'coordenador'): ?>
                                        <span class="badge" style="background:var(--verde-uniceplac);">Coordenador</span>
                                    <?php elseif ($u['perfil'] === 'professor'): ?>
                                        <span class="badge" style="background:var(--roxo-uniceplac);">Professor</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Suporte TI</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4">
                                    <?= $u['email_verificado'] ? '<span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Verificado</span>' : '<span class="badge bg-warning text-dark rounded-pill"><i class="bi bi-hourglass-split me-1"></i>Pendente</span>' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalDetalheEvento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 16px;">
                <div class="modal-header bg-light border-0" style="border-radius: 16px 16px 0 0;">
                    <h5 class="modal-title fw-bold text-primary" id="modalDetalheTitulo">Detalhes da Aula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body fs-6 text-dark" id="modalDetalheCorpo"></div>
                <div class="modal-footer border-0"><button type="button"
                        class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
        let calendarioCoordenadorGlobal;

        window.abrirSanfona = function (caixaId, setaId) {
            let caixa = document.getElementById(caixaId);
            let seta = document.getElementById(setaId);

            if (caixa.style.display === 'none' || caixa.style.display === '') {
                caixa.style.display = 'block';
                if (seta) seta.style.transform = 'rotate(180deg)';
            } else {
                caixa.style.display = 'none';
                if (seta) seta.style.transform = 'rotate(0deg)';
            }
        };

        document.addEventListener('DOMContentLoaded', function () {

            // TRAVA DO EAD NO JAVASCRIPT (Técnica Blindada de Formulário)
            window.travarProfEAD = function (elementoModalidade) {
                let form = elementoModalidade.closest('form');
                if (!form) return;

                let selectProfessor = form.querySelector('[name="id_professor_aula"]');

                if (selectProfessor) {
                    if (elementoModalidade.value === 'EAD') {
                        selectProfessor.value = "";
                        selectProfessor.disabled = true;
                        selectProfessor.required = false;
                        selectProfessor.removeAttribute('required');
                        selectProfessor.parentElement.style.display = 'none';
                    } else {
                        selectProfessor.parentElement.style.display = 'block';
                        selectProfessor.disabled = false;
                        selectProfessor.required = true;
                        selectProfessor.setAttribute('required', 'required');
                    }
                }
            };

            // Roda a verificação assim que a tela abre (Para os modais de edição)
            document.querySelectorAll('select[name="modalidade"]').forEach(function (caixaModalidade) {
                travarProfEAD(caixaModalidade);
            });

            // MOTOR DO DRAG AND DROP (KANBAN)
            const colunasGrade = document.querySelectorAll('.coluna-sortable');

            colunasGrade.forEach(coluna => {
                new Sortable(coluna, {
                    group: 'gradeUniceplac', // Permite arrastar entre colunas diferentes
                    animation: 150, // Suavidade da animação (ms)
                    ghostClass: 'opacity-50', // Efeito visual no card original enquanto arrasta

                    onEnd: function (evt) {
                        const cardArrastado = evt.item;
                        const colunaDestino = evt.to;
                        const colunaOrigem = evt.from;

                        // Se soltou no mesmo lugar, não faz nada
                        if (colunaOrigem === colunaDestino) return;

                        // Pega os dados invisíveis que colocamos no HTML
                        const idAula = cardArrastado.getAttribute('data-id-aula');
                        const novoDia = colunaDestino.getAttribute('data-dia');

                        // Envia o comando silencioso pro PHP (Ajax)
                        let formData = new FormData();
                        formData.append('action', 'mover_aula');
                        formData.append('id_aula', idAula);
                        formData.append('novo_dia', novoDia);

                        fetch('painel_coordenador.php', {
                            method: 'POST',
                            body: formData
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    alert("Opa! Houve um erro no banco de dados ao salvar a posição.");
                                    // Se der erro, joga o card de volta pra onde estava
                                    evt.from.appendChild(evt.item);
                                }
                            })
                            .catch(error => {
                                console.error('Erro de conexão:', error);
                                alert("Falha de conexão. A aula voltou para a posição original.");
                                evt.from.appendChild(evt.item);
                            });
                    }
                });
            });

            const filtroInput = document.getElementById('filtroDashInput');
            const limparBtn = document.getElementById('btnLimparFiltroDash');

            function aplicarFiltroDashboard(termo) {
                if (!filtroInput) return;
                filtroInput.value = termo;
                limparBtn.style.display = termo ? 'block' : 'none';
                let textoBusca = termo.toLowerCase();

                document.querySelectorAll('.linha-filtro, .linha-bi').forEach(linha => {
                    let conteudo = linha.innerText.toLowerCase();
                    linha.style.display = conteudo.includes(textoBusca) ? '' : 'none';
                });

                document.querySelectorAll('.card-ociosidade').forEach(card => {
                    let nomeLab = card.getAttribute('data-search');
                    card.style.display = nomeLab.includes(textoBusca) ? '' : 'none';
                });

                ['collapseTabelaBI', 'collapseProfTabela', 'collapseLabTabela'].forEach(id => {
                    let tb = document.getElementById(id);
                    let seta = document.getElementById(id.replace('collapse', 'seta'));
                    if (tb && tb.style.display === 'none') {
                        tb.style.display = 'block';
                        if (seta) seta.style.transform = 'rotate(180deg)';
                    }
                });

                filtroInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            if (filtroInput) filtroInput.addEventListener('keyup', function () { aplicarFiltroDashboard(this.value); });
            if (limparBtn) limparBtn.addEventListener('click', function () { aplicarFiltroDashboard(''); filtroInput.focus(); });

            <?php if ($quadro_selecionado): ?>
                const profNomes = <?= json_encode($grafico_prof_nomes ?? []) ?>;
                const profLab = <?= json_encode($grafico_prof_lab ?? []) ?>;
                const profSala = <?= json_encode($grafico_prof_sala ?? []) ?>;

                if (document.getElementById('chartPerfilAulas') && profNomes.length > 0) {
                    new Chart(document.getElementById('chartPerfilAulas'), {
                        type: 'bar',
                        data: {
                            labels: profNomes,
                            datasets: [
                                { label: 'Prática (Lab)', data: profLab, backgroundColor: 'rgba(220, 53, 69, 0.8)', borderRadius: 4 },
                                { label: 'Teórica (Sala)', data: profSala, backgroundColor: 'rgba(25, 135, 84, 0.8)', borderRadius: 4 }
                            ]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            scales: { x: { stacked: true }, y: { stacked: true } },
                            plugins: { legend: { display: true } },
                            onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                            onClick: (e, el) => { if (el.length > 0) aplicarFiltroDashboard(profNomes[el[0].index]); }
                        }
                    });
                }

                const cursoNomes = <?= json_encode($grafico_curso_nomes ?? []) ?>;
                const cursoHoras = <?= json_encode($grafico_curso_horas ?? []) ?>;

                if (document.getElementById('chartCursos') && cursoNomes.length > 0) {
                    new Chart(document.getElementById('chartCursos'), {
                        type: 'doughnut',
                        data: {
                            labels: cursoNomes,
                            datasets: [{
                                data: cursoHoras,
                                backgroundColor: ['rgba(66, 27, 113, 0.8)', 'rgba(0, 115, 79, 0.8)', 'rgba(240, 115, 60, 0.8)', 'rgba(13, 202, 240, 0.8)', 'rgba(255, 193, 7, 0.8)'],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false, cutout: '60%',
                            plugins: { legend: { position: 'bottom' } },
                            onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                            onClick: (e, el) => { if (el.length > 0) aplicarFiltroDashboard(cursoNomes[el[0].index]); }
                        }
                    });
                }
            <?php endif; ?>

            var calendarEl = document.getElementById('calendarioGeral');
            if (calendarEl) {
                // Inteligência Mobile
                let isMobile = window.innerWidth < 768;
                let visaoInicial = isMobile ? 'listWeek' : 'dayGridMonth';

                calendarioCoordenadorGlobal = new FullCalendar.Calendar(calendarEl, {
                    locale: 'pt-br',
                    initialView: visaoInicial,

                    navLinks: true,
                    nowIndicator: true,
                    dayMaxEvents: 3,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia', list: 'Lista' },
                    events: <?= $eventos_json ?>,
                    slotMinTime: '08:00:00',
                    slotMaxTime: '23:30:00',
                    allDaySlot: false,
                    expandRows: true,

                    windowResize: function (arg) {
                        if (window.innerWidth < 768) {
                            calendarioCoordenadorGlobal.changeView('listWeek');
                        } else {
                            calendarioCoordenadorGlobal.changeView('dayGridMonth');
                        }
                    },

                    eventContent: function (arg) {
                        let isGrid = arg.view.type === 'dayGridMonth';
                        let truncateClass = isGrid ? 'text-truncate-multi' : 'text-truncate-single';

                        let timeHtml = arg.timeText
                            ? `<div style="font-size: 0.65rem; font-weight: 800; opacity: 0.8; margin-bottom: 2px;">${arg.timeText}</div>`
                            : '';

                        let titleHtml = `<div class="${truncateClass}" style="font-size: 0.75rem; font-weight: 700; line-height: 1.2;">${arg.event.title}</div>`;

                        let localHtml = '';
                        if (!isGrid && arg.event.extendedProps.local) {
                            localHtml = `<div class="${truncateClass}" style="font-size: 0.7rem; margin-top: 2px; opacity: 0.9;">${arg.event.extendedProps.local}</div>`;
                        }

                        let content = document.createElement('div');
                        content.style.width = '100%';
                        content.style.overflow = 'hidden';
                        content.innerHTML = timeHtml + titleHtml + localHtml;

                        let tooltipText = arg.event.title;
                        if (arg.event.extendedProps.local) {
                            tooltipText += " - " + arg.event.extendedProps.local.replace(/<[^>]*>?/gm, '');
                        }
                        content.title = tooltipText;

                        return { domNodes: [content] };
                    },

                    eventClick: function (arg) {
                        arg.jsEvent.preventDefault();
                        document.getElementById('modalDetalheTitulo').innerHTML = '<i class="bi bi-calendar2-event me-2"></i>' + arg.event.title;
                        let dataStr = arg.event.start.toLocaleDateString('pt-BR');
                        let horaInicio = arg.event.start.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                        let horaFim = arg.event.end ? arg.event.end.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }) : '';
                        let horarioStr = arg.event.allDay ? 'Dia Inteiro' : `${horaInicio} às ${horaFim}`;
                        let tipo = '<span class="badge bg-secondary">Reserva Avulsa</span>';
                        if (arg.event.classNames.includes('apple-event-fixa')) tipo = '<span class="badge" style="background-color: var(--roxo-uniceplac);">Aula Fixa da Grade</span>';
                        if (arg.event.classNames.includes('apple-event-feriado')) tipo = '<span class="badge bg-danger">Feriado</span>';
                        let localStr = arg.event.extendedProps.local ? arg.event.extendedProps.local : '<i class="bi bi-geo-alt me-1"></i> Local não definido';
                        let corpoHtml = `
                            <div class="mb-3"><strong class="text-secondary"><i class="bi bi-clock me-1"></i> Data e Horário:</strong><br> <span class="fs-6 fw-bold">${dataStr}</span> &nbsp;|&nbsp; <span class="fs-6">${horarioStr}</span></div>
                            <div class="mb-3"><strong class="text-secondary"><i class="bi bi-geo-alt me-1"></i> Localização:</strong><br> <span class="fs-6">${localStr}</span></div>
                            <div class="mb-2"><strong class="text-secondary"><i class="bi bi-tag me-1"></i> Categoria:</strong><br> ${tipo}</div>
                        `;
                        document.getElementById('modalDetalheCorpo').innerHTML = corpoHtml;
                        let modal = new bootstrap.Modal(document.getElementById('modalDetalheEvento'));
                        modal.show();
                    }
                });
                calendarioCoordenadorGlobal.render();
            }

            function updateThemeElements(theme) {
                const themeIcon = document.getElementById('themeIcon'); const navbarLogo = document.getElementById('navbarLogo');
                if (theme === 'dark') { if (themeIcon) { themeIcon.className = 'bi bi-sun text-warning'; } if (navbarLogo) navbarLogo.src = 'uniceplac.png'; }
                else { if (themeIcon) { themeIcon.className = 'bi bi-moon-stars'; } if (navbarLogo) navbarLogo.src = 'uniceplac2.png'; }
            }

            updateThemeElements(document.documentElement.getAttribute('data-bs-theme'));

            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', function () {
                    let newTheme = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-bs-theme', newTheme);
                    localStorage.setItem('tema-uniceplac', newTheme);
                    updateThemeElements(newTheme);
                });
            }

            document.querySelectorAll('.alert-autohide').forEach(alerta => {
                setTimeout(() => { alerta.style.transition = "opacity 0.6s ease"; alerta.style.opacity = "0"; setTimeout(() => alerta.remove(), 600); }, 4000);
            });

            const btnFoto = document.getElementById('btnAlterarFotoNav');
            const inputFoto = document.getElementById('nova_foto_input');
            if (btnFoto && inputFoto) {
                btnFoto.addEventListener('click', () => inputFoto.click());
                inputFoto.addEventListener('change', function () { if (this.value) document.getElementById('formFotoPerfil').submit(); });
            }

            window.showSection = function (sectionId) {
                document.querySelectorAll('.content-section').forEach(sec => sec.style.display = 'none');
                document.querySelectorAll('.offcanvas-menu-link').forEach(link => link.classList.remove('active-link'));
                let targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.style.display = 'block';
                    let activeLink = document.querySelector(`.offcanvas-menu-link[onclick*="${sectionId}"]`);
                    if (activeLink) activeLink.classList.add('active-link');
                    window.history.replaceState(null, null, '#' + sectionId);
                    if (sectionId === 'sessao-calendario-geral' && typeof calendarioCoordenadorGlobal !== 'undefined') {
                        setTimeout(() => { calendarioCoordenadorGlobal.updateSize(); }, 150);
                    }
                }
            }

            let hashURL = window.location.hash.replace('#', '');
            let abaPadrao = <?= count($lista_quadros) > 0 ? "'sessao-quadro-horario'" : "'sessao-calendario-geral'" ?>;
            let abaInicial = hashURL ? hashURL : abaPadrao;
            showSection(abaInicial);

            let qtdPendentesAnterior = <?= $qtd_pendentes ?>;
            const somNotificacao = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            // Polling do painel inteiro desativado — travava em túnel/Supabase (página ~200KB cada 5s).

            window.filtrarGrade = function () {
                const turnoSelecionado = document.getElementById('filtroTurnoGrade').value;
                const cursoSelecionado = document.getElementById('filtroCursoGrade').value;
                const cardsAula = document.querySelectorAll('.card-grade-aula');

                const modalidadeSelecionada = document.getElementById('filtroModalidadeGrade').value; // Captura o novo filtro

                cardsAula.forEach(card => {
                    const cardTurno = card.getAttribute('data-turno');
                    const cardCurso = card.getAttribute('data-curso');
                    const cardModalidade = card.getAttribute('data-modalidade');


                    let matchTurno = (turnoSelecionado === 'todos' || cardTurno === turnoSelecionado);
                    let matchCurso = (cursoSelecionado === 'todos' || cardCurso === cursoSelecionado);
                    let matchModalidade = (modalidadeSelecionada === 'todos' || cardModalidade === modalidadeSelecionada);


                    if (matchTurno && matchCurso && (modalidadeSelecionada === 'todos' || cardModalidade === modalidadeSelecionada)) {
                        card.style.display = 'block';

                    } else { card.style.display = 'none'; }
                });
            };
        });

        function exportarDashboardCSV() {
            let csvContent = "\uFEFF";
            csvContent += "RELATÓRIO DE PERFORMANCE - UNICEPLAC\n";
            csvContent += "Coordenador: " + "<?= htmlspecialchars($_SESSION['nome']) ?>" + "\n";
            csvContent += "Data de Extração: " + new Date().toLocaleDateString() + "\n\n";

            function extrairDadosTabela(idTabela, titulo) {
                const tabela = document.getElementById(idTabela);
                if (!tabela) return "";
                let conteudo = "--- " + titulo + " ---\n";
                const linhas = tabela.querySelectorAll("tr");
                linhas.forEach(linha => {
                    const colunas = linha.querySelectorAll("th, td");
                    const dadosLinha = Array.from(colunas).map(col => {
                        let texto = col.innerText.replace(/\n/g, " ").replace(/;/g, ",").trim();
                        return '"' + texto + '"';
                    });
                    conteudo += dadosLinha.join(";") + "\n";
                });
                return conteudo + "\n";
            }

            csvContent += extrairDadosTabela("tabelaProfessores", "RELATÓRIO DE PROFESSORES (CH)");
            csvContent += extrairDadosTabela("tabelaLabs", "OCUPAÇÃO DE LABORATÓRIOS");

            const tabelaBI = document.getElementById("corpoTabelaBI") ? document.getElementById("corpoTabelaBI").closest('table') : null;
            if (tabelaBI) {
                csvContent += "--- ANÁLISE DETALHADA DE ALOCAÇÃO ---\n";
                const linhasBI = tabelaBI.querySelectorAll("tr");
                linhasBI.forEach(linha => {
                    const colunas = linha.querySelectorAll("th, td");
                    csvContent += Array.from(colunas).map(col => '"' + col.innerText.trim() + '"').join(";") + "\n";
                });
            }

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "Relatorio_Dashboard_Uniceplac.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>

</html>