@extends('layouts.app')

@section('title', $project->name . ' - Workspace')

@section('content')


<div class="container-fluid px-4 mt-2">
    <!-- Graph Visualization Lib -->
    <script src="//unpkg.com/force-graph"></script>
    <style>
        .card-ficha {
            cursor: pointer;
            border-radius: 12px;
            border: 1px solid rgba(var(--bs-primary-rgb), 0.1) !important;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
        }
        .backdrop-blur {
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .btn-ghost-card {
            color: var(--bs-body-color);
            opacity: 0.6;
            transition: all 0.2s;
        }
        .btn-ghost-card:hover {
            opacity: 1;
            background: rgba(var(--bs-primary-rgb), 0.1);
        }
        .card-ficha .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        /* Grid de Worldbuilding na Sidebar */
        .world-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        .world-grid-btn.full-width {
            grid-column: span 2;
        }
        .world-grid-btn {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
            border-radius: 10px;
            background: rgba(var(--bs-primary-rgb), 0.02);
            border: 1px solid rgba(var(--bs-primary-rgb), 0.05);
            color: var(--bs-body-color);
            text-decoration: none;
            transition: all 0.2s linear;
            font-size: 0.62rem;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
            opacity: 0.8;
            white-space: nowrap;
        }
        .world-grid-btn:hover {
            background: rgba(var(--bs-primary-rgb), 0.08);
            border-color: rgba(var(--bs-primary-rgb), 0.2);
            color: var(--bs-primary);
            opacity: 1;
            transform: translateY(-1px);
        }
        .world-grid-btn.active {
            background: var(--bs-primary);
            color: white !important;
            border-color: var(--bs-primary);
            opacity: 1;
            box-shadow: 0 4px 10px rgba(var(--bs-primary-rgb), 0.3);
        }
        .world-grid-btn i {
            display: inline-block;
            font-size: 1.1rem;
            margin-right: 6px;
        }
        .ls-wide { letter-spacing: 0.05em; }
        /* Badge de Extensão na Galeria */
        .gallery-badge {
            position: absolute;
            top: 8px !important;
            right: 8px !important;
            left: auto !important;
            bottom: auto !important;
            width: auto !important;
            height: auto !important;
            font-size: 0.55rem;
            padding: 3px 6px !important;
            border-radius: 4px;
            background: rgba(var(--bs-primary-rgb), 0.9);
            color: white;
            font-weight: 800;
            backdrop-filter: blur(4px);
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            pointer-events: none;
            line-height: 1;
        }
        /* Animações de Grade */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        .cp-sidebar-item {
            cursor: pointer;
            transition: all 0.2sease;
            border-radius: 4px;
            padding: 2px 5px;
        }
        .cp-sidebar-item:hover {
            background: rgba(var(--bs-primary-rgb), 0.1);
            color: var(--bs-primary);
        }
        /* Planejamento Mode Highlights */
        .btn-planning.active {
            background-color: #fd7e14 !important;
            border-color: #fd7e14 !important;
            color: white !important;
            box-shadow: 0 0 10px rgba(253, 126, 20, 0.4);
        }
        .editor-planning-mode {
            border: 2px solid rgba(253, 126, 20, 0.5) !important;
            transition: border 0.3s ease;
        }
        /* Unificar Toolbar com Bootstrap Icons */
        .editor-toolbar button.bi {
            font-family: "bootstrap-icons" !important;
            font-style: normal;
        }
        .editor-toolbar button.bi::before {
            vertical-align: middle;
        }
    </style>
    <!-- Layout Principal -->
    <div id="main-layout" class="d-row d-flex vh-workspace mt-3">
        
        <!-- Sidebar Esquerda -->
        <div id="left-sidebar" class="split-pane card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
            <button id="left-sidebar-toggle" class="btn-ghost-card position-absolute toggle-btn-left" onclick="toggleSidebar('left')" title="Navegação">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>

            <div class="sidebar-content mt-4 overflow-auto scroll-custom h-100">
                <h6 class="text-body-secondary small text-uppercase fw-bold mb-4 ls-wide">Worldbuilding</h6>
                
                <div class="world-grid mb-4">
                    <a id="nav-scenario" class="world-grid-btn nav-world" href="#" onclick="loadCards('scenario')" title="Geografia">
                        <i class="bi bi-geo-alt"></i> Geografia
                    </a>
                    <a id="nav-character" class="world-grid-btn nav-world" href="#" onclick="loadCards('character')" title="Personagens">
                        <i class="bi bi-people"></i> Personagens
                    </a>
                    <a id="nav-object" class="world-grid-btn nav-world" href="#" onclick="loadCards('object')" title="Objetos">
                        <i class="bi bi-gem"></i> Objetos
                    </a>
                    <a id="nav-gallery" class="world-grid-btn nav-world" href="#" onclick="openGallery()" title="Galeria">
                        <i class="bi bi-images"></i> Galeria
                    </a>
                    <a id="nav-connections" class="world-grid-btn nav-world" href="#" onclick="openGraph()" title="Conexões">
                        <i class="bi bi-diagram-3"></i> Conexões
                    </a>
                    <a id="nav-bible" class="world-grid-btn nav-world" href="#" onclick="openBible()" title="Bíblia">
                        <i class="bi bi-book"></i> Bíblia
                    </a>
                </div>

                <hr class="border-secondary opacity-25 my-4">

                <!-- Sessão: Escrita -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-body-secondary small text-uppercase fw-bold mb-0 ls-wide cp" onclick="showEmptyState('manuscript')">Manuscrito</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-link text-primary p-0" onclick="createNewItem('section')" title="Nova Seção">
                            <i class="bi bi-folder-plus"></i>
                        </button>
                    </div>
                </div>

                <div id="manuscript-tree-container" class="small manuscript-tree">
                    <div id="manuscript-tree-root" class="list-group list-group-flush">
                        <div class="text-ghost-muted italic small py-2">Carregando manuscrito...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Coluna Central (Editor) -->
        <div id="center-editor" class="split-pane p-0">
            <div id="editor-wrapper" class="h-100 p-0 mx-1">
                <div id="editor-container" class="card bg-body-tertiary border-0 shadow-sm p-4 h-100 d-none overflow-hidden d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <span id="editor-type-icon" class="me-2 text-primary"></span>
                            <div class="d-flex flex-column">
                                <h3 id="current-item-title" class="fw-bold mb-0 cp-title" onclick="enableTitleEdit()">Título</h3>
                                <div id="manuscript-mode-toggle" class="d-flex gap-2 mt-1 d-none">
                                    <button id="btn-mode-writing" class="btn btn-xs btn-outline-primary active py-0 px-2 small" onclick="setManuscriptMode('writing')" style="font-size: 0.65rem;">ESCRITA</button>
                                    <button id="btn-mode-planning" class="btn btn-xs btn-outline-secondary btn-planning py-0 px-2 small" onclick="setManuscriptMode('planning')" style="font-size: 0.65rem;">PLANEJAMENTO</button>
                                </div>
                            </div>
                            <input type="text" id="title-edit-input" class="form-control form-control-lg bg-transparent border-0 text-body fw-bold d-none p-0 ms-2" style="font-size: 1.75rem;" onblur="saveTitleEdit()" onkeyup="if(event.key==='Enter') saveTitleEdit()">
                        </div>
                        <div id="save-status" class="text-body-secondary small">
                            <i class="bi bi-check2-all me-1"></i> Salvo
                        </div>
                    </div>
                    <div class="flex-grow-1 overflow-auto custom-editor-area">
                        <textarea id="markdown-editor"></textarea>
                    </div>

                    <!-- Connection Management Area -->
                    <div id="card-connections-editor" class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-none">
                        <h6 class="text-primary small text-uppercase fw-bold mb-3 ls-wide">Relacionamentos</h6>
                        <div id="active-connections" class="d-flex flex-wrap gap-2 mb-3">
                            <!-- Conexões atuais -->
                        </div>
                        <div class="position-relative pt-2 pb-5"> 
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-transparent border-secondary border-opacity-25 text-body-secondary">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="connection-search" class="form-control bg-transparent border-secondary border-opacity-25" placeholder="Adicionar relação (digite o nome...)" onkeyup="searchConnections(this.value)">
                            </div>
                            <div id="connection-results" class="position-absolute w-100 bg-body-tertiary border border-primary border-opacity-50 rounded mt-1 shadow-lg d-none" style="z-index: 10000; max-height: 200px; overflow-y: auto;">
                                <!-- Resultados da busca -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bible View -->
                <div id="bible-container" class="card bg-body-tertiary border-0 shadow-sm p-4 h-100 d-none overflow-hidden d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-book me-2 text-primary fs-4"></i>
                            <h3 class="fw-bold mb-0">Bíblia do Projeto</h3>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <button id="btn-sync-bible" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="syncBibleWithAI()" title="A IA lerá seu manuscrito e atualizará o resumo.">
                                <i class="bi bi-stars me-1 text-warning"></i> Sincronizar Bíblia
                            </button>
                            <div id="bible-save-status" class="text-body-secondary small">
                                <i class="bi bi-check2-all me-1"></i> Salvo
                            </div>
                        </div>
                    </div>

                    <div class="flex-grow-1 overflow-hidden d-flex flex-column gap-4">
                        <!-- Duas seções: Lore (Manual) e Resumo (IA) -->
                        <div class="row h-100 g-4">
                            <div class="col-md-7 d-flex flex-column">
                                <h6 class="text-primary small text-uppercase fw-bold mb-2 ls-wide">DNA & Lore do Mundo</h6>
                                <div class="flex-grow-1 overflow-auto custom-editor-area">
                                    <textarea id="bible-editor"></textarea>
                                </div>
                            </div>
                            <div class="col-md-5 d-flex flex-column border-start border-secondary border-opacity-10 ps-4">
                                <h6 class="text-accent small text-uppercase fw-bold mb-2 ls-wide">
                                    Resumo Narrativo 
                                    <span class="badge bg-secondary bg-opacity-10 text-body-secondary fw-normal ms-1" style="font-size: 0.6rem;">IA RECAP</span>
                                </h6>
                                <div class="flex-grow-1 overflow-auto custom-editor-area">
                                    <textarea id="bible-summary-editor"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="graph-container" class="card bg-body-tertiary border-0 shadow-sm h-100 d-none overflow-hidden position-relative">
                    <div id="graph-view" style="width: 100%; height: 100%;"></div>
                    <div class="position-absolute top-0 end-0 p-3 z-2">
                        <button class="btn btn-sm btn-ghost-card backdrop-blur" onclick="loadGraphData(true)" title="Atualizar Grafo">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <!-- Navigation Help Overlay -->
                    <div class="position-absolute bottom-0 start-0 p-3 z-2">
                        <div class="badge bg-dark bg-opacity-50 backdrop-blur p-2 small border border-secondary border-opacity-25" style="pointer-events: none;">
                            <i class="bi bi-mouse me-2"></i> Scroll: Zoom | <i class="bi bi-arrows-move mx-2"></i> Arraste: Mover | <i class="bi bi-hand-index mx-2"></i> Clique: Abrir
                        </div>
                    </div>
                </div>

                <div id="empty-state" class="card bg-body-tertiary border-0 shadow-sm p-5 h-100 d-flex flex-column align-items-center justify-content-center text-center">
                    <div class="opacity-10 mb-4">
                        <i id="empty-icon" class="bi bi-feather display-1"></i>
                    </div>
                    <h2 id="empty-title" class="fw-bold mb-3">Onde a história começa?</h2>
                    <p id="empty-desc" class="text-body-secondary mb-4 max-w-md mx-auto">
                        Cada pixel foi pensado para o seu foco. Arraste as bordas ou colapse as barras laterais no topo para imersão total.
                    </p>
                    <div id="empty-actions" class="d-flex gap-3">
                        <button class="btn btn-primary" onclick="loadCards('scenario')">Geografia</button>
                        <button class="btn btn-outline-secondary border-secondary border-opacity-50" onclick="showEmptyState('manuscript')">Manuscrito</button>
                    </div>
                </div>

                <!-- Grid de Fichas (Worldbuilding) -->
                <div id="cards-grid-container" class="card bg-body-tertiary border-0 shadow-sm p-4 h-100 d-none overflow-hidden d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <i id="cards-grid-icon" class="bi bi-people me-2 text-primary fs-4"></i>
                            <h3 id="cards-grid-title" class="fw-bold mb-0">Personagens</h3>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-25" style="width: 150px;">
                                <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search opacity-50"></i></span>
                                <input type="text" id="cards-search" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrar..." oninput="filterCards(this.value)">
                            </div>
                            <button class="btn btn-outline-primary rounded-pill btn-sm px-3" onclick="exportProject()" title="Gerar ePub para Kindle">
                                <i class="bi bi-cloud-download me-1"></i> Exportar
                            </button>
                            <button class="btn btn-primary rounded-pill btn-sm px-3" onclick="createCard()">
                                <i class="bi bi-plus-lg me-1"></i> Nova Ficha
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex-grow-1 overflow-auto scroll-custom">
                        <div id="cards-grid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                            <!-- Fichas serão carregadas aqui -->
                        </div>
                    </div>
                </div>

                <!-- Galeria de Imagens -->
                <div id="gallery-container" class="card bg-body-tertiary border-0 shadow-sm p-4 h-100 d-none overflow-hidden d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-images me-2 text-primary fs-4"></i>
                            <h3 class="fw-bold mb-0">Galeria do Projeto</h3>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-25" style="width: 200px;">
                                <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search opacity-50"></i></span>
                                <input type="text" id="gallery-search" class="form-control border-0 bg-transparent ps-0" placeholder="Buscar arte..." oninput="filterGallery(this.value)">
                            </div>
                            <input type="file" id="gallery-upload-input" class="d-none" accept="image/*" onchange="uploadImage(this)">
                            <button class="btn btn-primary rounded-pill btn-sm px-3" onclick="document.getElementById('gallery-upload-input').click()">
                                <i class="bi bi-upload me-1"></i> Upload
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex-grow-1 overflow-auto scroll-custom">
                        <div id="gallery-grid" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                            <!-- Imagens serão carregadas aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel Direita (Estatísticas e IA) -->
        <div id="right-panel" class="split-pane card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
            <button id="right-sidebar-toggle" class="btn-ghost-card position-absolute toggle-btn-right" onclick="toggleSidebar('right')" title="Estatísticas">
                <i class="bi bi-layout-sidebar-inset-reverse"></i>
            </button>

            <div class="sidebar-content mt-4 overflow-auto scroll-custom h-100">
                <div class="p-2 mb-4 border-primary border-opacity-25 rounded" style="background: rgba(var(--bs-primary-rgb), 0.03);">
                    <h6 class="text-primary small text-uppercase fw-bold mb-2">IA Support</h6>
                    <p class="x-small text-body-secondary mb-3 lh-sm">Worldbuilding context is injected automatically.</p>
                    <button class="btn btn-sm btn-outline-primary w-100 disabled">
                        <i class="bi bi-magic me-1"></i> Locked
                    </button>
                </div>

                <h6 class="text-body-secondary small text-uppercase fw-bold mb-3">Item Details</h6>
                <div id="item-stats" class="small text-body-secondary">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tipo:</span>
                        <span id="stat-type" class="text-accent fw-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Palavras:</span>
                        <span id="stat-word-count" class="text-accent fw-bold">0</span>
                    </div>
                </div>

                <!-- Painel Conteúdo Dinâmico (Listas/Estatísticas) -->
                <div id="dynamic-context-panel" class="mt-4 pt-4 border-top border-secondary border-opacity-10 d-none">
                    <h6 id="dynamic-title" class="text-body-secondary small text-uppercase fw-bold mb-3">CONTEÚDO</h6>
                    <div id="dynamic-content" class="small text-body-secondary">
                        <!-- Conteúdo dinâmico -->
                    </div>
                </div>
                <!-- Markdown Help (Hidden by default) -->
                <div id="markdown-tips" class="mt-4 pt-4 border-top border-secondary border-opacity-10 d-none">
                    <h6 class="text-primary small text-uppercase fw-bold mb-3 ls-wide">Guia de Formatação</h6>
                    <div class="small text-body-secondary">
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code># Título</code>
                            <span class="x-small opacity-75">H1 (Principal)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code>## Subtítulo</code>
                            <span class="x-small opacity-75">H2 (Seção)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code>**Negrito**</code>
                            <span class="x-small opacity-75">Destaque</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code>*Itálico*</code>
                            <span class="x-small opacity-75">Ênfase</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code>> Citação</code>
                            <span class="x-small opacity-75">Pensamento</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code>-- Texto</code>
                            <span class="x-small opacity-75">Travessão (Auto)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 pb-1 border-bottom border-secondary border-opacity-10">
                            <code>---</code>
                            <span class="x-small opacity-75">Divisão</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Expand Buttons (only visible when sidebars are collapsed) -->
<button id="expand-left-btn" type="button" class="btn btn-primary position-fixed bottom-0 start-0 m-3 d-none shadow-lg z-3" onclick="toggleSidebar('left')">
    <i class="bi bi-layout-sidebar-inset"></i>
</button>
<button id="expand-right-btn" type="button" class="btn btn-primary position-fixed bottom-0 end-0 m-3 d-none shadow-lg z-3" onclick="toggleSidebar('right')">
    <i class="bi bi-layout-sidebar-inset-reverse"></i>
</button>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-tertiary border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 text-uppercase fw-bold text-danger ls-wide" id="deleteConfirmModalLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-body">Tem certeza que deseja excluir este item e todo o conteúdo dentro dele? Esta ação <strong>não pode ser desfeita</strong>.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 rounded-pill" onclick="confirmDeleteManuscriptItem()">Excluir Permanentemente</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Imagem -->
<div class="modal fade" id="galleryDeleteModal" tabindex="-1" aria-labelledby="galleryDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-tertiary border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 text-uppercase fw-bold text-danger ls-wide" id="galleryDeleteModalLabel">Excluir Imagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-body">Deseja realmente excluir esta imagem? Esta ação removerá o arquivo permanentemente do projeto.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmGalleryDeleteBtn" class="btn btn-danger px-4 rounded-pill" onclick="confirmDeleteGalleryItem()">Excluir Imagem</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Renomear Imagem -->
<div class="modal fade" id="galleryRenameModal" tabindex="-1" aria-labelledby="galleryRenameModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-tertiary border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 text-uppercase fw-bold text-primary ls-wide" id="galleryRenameModalLabel">Renomear Imagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-3 text-start">
                    <label for="new-image-name" class="form-label small text-body-secondary fw-bold text-uppercase">Novo Nome</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-body border-0 shadow-sm" id="new-image-name">
                        <span class="input-group-text bg-secondary bg-opacity-10 border-0 fw-bold opacity-75" id="image-ext-preview">.PNG</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmGalleryRenameBtn" class="btn btn-primary px-4 rounded-pill" onclick="confirmRenameGalleryItem()">Salvar Nome</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Ficha -->
<div class="modal fade" id="cardDeleteModal" tabindex="-1" aria-labelledby="cardDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-tertiary border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 text-uppercase fw-bold text-danger ls-wide" id="cardDeleteModalLabel">Excluir Ficha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-body">Deseja realmente excluir esta ficha? Todos os dados vinculados a ela e seu arquivo serão perdidos permanentemente.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-link link-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmCardDeleteBtn" class="btn btn-danger px-4 rounded-pill" onclick="confirmDeleteCard()">Excluir Ficha</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Seletor de Galeria (Picker) -->
<div class="modal fade" id="galleryPickerModal" tabindex="-1" aria-labelledby="galleryPickerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-body-tertiary border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 text-uppercase fw-bold text-primary ls-wide" id="galleryPickerModalLabel">Selecionar da Galeria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-4">
                    <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-50" style="max-width: 300px; margin: 0 auto;">
                        <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search opacity-50"></i></span>
                        <input type="text" id="gallery-picker-search" class="form-control border-0 bg-transparent ps-0" placeholder="Procurar na galeria..." oninput="filterGalleryPicker(this.value)">
                    </div>
                </div>
                <div id="gallery-picker-grid" class="row row-cols-3 row-cols-md-4 g-3">
                    <!-- Imagens carregadas dinamicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gatilho invisível para o Modal da Galeria (evita erros de biblioteca undefined) -->
<button id="gallery-picker-trigger" class="d-none" data-bs-toggle="modal" data-bs-target="#galleryPickerModal"></button>

<!-- EasyMDE & SortableJS & Split.js -->
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://unpkg.com/split.js/dist/split.min.js"></script>

<script>
let easyMDE;
let bibleEditor;
let bibleSummaryEditor;
let activeItemUuid = null;
let activeCardCategory = null; // 'scenario', 'character', 'object'
let itemToDeleteUuid = null;
let galleryItemToDeleteUuid = null;
let galleryItemToRenameUuid = null;
let cardToDeleteUuid = null;
let cardBeingEditedImageUuid = null;
let currentImageExt = '';
let currentCards = [];
let currentGalleryItems = [];
let saveTimeout;
let bibleSaveTimeout;
let manuscriptMode = 'writing'; // 'writing' or 'planning'
let currentGalleryMode = 'card'; // 'card' or 'editor'

const projectUuid = "{{ $project->uuid }}";
let activeProjectCoverUuid = "{{ $project->cover_image_uuid }}";

document.addEventListener('DOMContentLoaded', function() {
    const bootstrapToolbar = [
        { name: "bold", action: EasyMDE.toggleBold, className: "bi bi-type-bold", title: "Negrito" },
        { name: "italic", action: EasyMDE.toggleItalic, className: "bi bi-type-italic", title: "Itálico" },
        { name: "heading", action: EasyMDE.toggleHeadingSmaller, className: "bi bi-type-h1", title: "Título" },
        "|",
        { name: "quote", action: EasyMDE.toggleBlockquote, className: "bi bi-quote", title: "Citação" },
        { name: "unordered-list", action: EasyMDE.toggleUnorderedList, className: "bi bi-list-ul", title: "Lista Genérica" },
        { name: "ordered-list", action: EasyMDE.toggleOrderedList, className: "bi bi-list-ol", title: "Lista Numerada" },
        "|",
        {
            name: "gallery-image",
            action: (editor) => openGalleryPickerForEditor(),
            className: "bi bi-image",
            title: "Inserir imagem da galeria",
        },
        "|",
        { name: "preview", action: EasyMDE.togglePreview, className: "bi bi-eye", title: "Visualizar" },
        "|",
        { name: "guide", action: "https://www.markdownguide.org/basic-syntax/", className: "bi bi-question-circle", title: "Guia Markdown" }
    ];

    easyMDE = new EasyMDE({
        element: document.getElementById('markdown-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        autoDownloadFontAwesome: false,
        placeholder: "Use sua criatividade...",
        toolbar: bootstrapToolbar
    });

    bibleEditor = new EasyMDE({
        element: document.getElementById('bible-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        autoDownloadFontAwesome: false,
        placeholder: "Pense na bíblia como o DNA do seu projeto...",
        toolbar: bootstrapToolbar
    });

    bibleSummaryEditor = new EasyMDE({
        element: document.getElementById('bible-summary-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        autoDownloadFontAwesome: false,
        placeholder: "O resumo narrativo será gerado aqui...",
        toolbar: [
            { name: "bold", action: EasyMDE.toggleBold, className: "bi bi-type-bold", title: "Negrito" },
            { name: "italic", action: EasyMDE.toggleItalic, className: "bi bi-type-italic", title: "Itálico" },
            "|",
            { name: "preview", action: EasyMDE.togglePreview, className: "bi bi-eye", title: "Visualizar" }
        ]
    });

    easyMDE.codemirror.on("change", () => {
        if (!activeItemUuid) return;
        showSaveStatus('Salvando...', 'bi-arrow-repeat spin');
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveActiveItem, 1500);
    });

    bibleEditor.codemirror.on("change", () => {
        showBibleSaveStatus('Salvando...', 'bi-arrow-repeat spin');
        clearTimeout(bibleSaveTimeout);
        bibleSaveTimeout = setTimeout(saveBibleContent, 1500);
    });

    bibleSummaryEditor.codemirror.on("change", () => {
        showBibleSaveStatus('Salvando...', 'bi-arrow-repeat spin');
        clearTimeout(bibleSaveTimeout);
        bibleSaveTimeout = setTimeout(saveBibleContent, 1500);
    });

    // Auto-convert -- to — (travessão)
    easyMDE.codemirror.on("beforeChange", (cm, change) => {
        if (change.origin === "+input" && change.text[0] === "-") {
            const cursor = cm.getCursor();
            const range = { line: cursor.line, ch: cursor.ch - 1 };
            const prevChar = cm.getRange(range, cursor);
            if (prevChar === "-") {
                change.update(range, cursor, ["—"]);
            }
        }
    });

    loadManuscript();
});

// --- UI Utility Logic ---

function showEmptyState(category) {
    activeItemUuid = null;
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('empty-state').classList.remove('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    
    // Deactivate nav buttons
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    
    const icon = document.getElementById('empty-icon');
    const title = document.getElementById('empty-title');
    const desc = document.getElementById('empty-desc');
    const actions = document.getElementById('empty-actions');

    const contents = {
        'scenario': {
            icon: 'bi-map',
            title: 'Mapeie seu Mundo',
            desc: 'A geografia define os limites do possível. Crie desertos, cidades ou reinos inteiros antes de começar a jornada.',
            btn: `<button class="btn btn-ghost-primary" onclick="createCard('scenario')">Novo Cenário</button>`
        },
        'character': {
            icon: 'bi-people',
            title: 'Dê Alma à História',
            desc: 'Quem são os seus protagonistas? Quais seus desejos e segredos? Comece criando as fichas de personagens.',
            btn: `<button class="btn btn-ghost-primary" onclick="createCard('character')">Novo Personagem</button>`
        },
        'object': {
            icon: 'bi-gem',
            title: 'Artefatos e Itens',
            desc: 'Espadas lendárias, cartas perdidas ou relíquias antigas. Registre os objetos que movem a trama.',
            btn: `<button class="btn btn-ghost-primary" onclick="createCard('object')">Novo Objeto</button>`
        },
        'manuscript': {
            icon: 'bi-diagram-3',
            title: 'Estruture sua Trama',
            desc: 'Defina a sequência de capítulos e cenas para construir o esqueleto da sua narrativa antes de escrever.',
            btn: '<button class="btn btn-primary" onclick="createChapter()">Adicionar Capítulo</button>'
        }
    };

    const content = contents[category] || contents['manuscript'];
    icon.className = `bi ${content.icon} display-1`;
    title.innerText = content.title;
    desc.innerHTML = content.desc;
    actions.innerHTML = content.btn;
}

// --- Manuscript Tree Logic ---

let splitInstance;

// Initialize Split.js
function initSplit() {
    splitInstance = Split(['#left-sidebar', '#center-editor', '#right-panel'], {
        sizes: [20, 60, 20],
        minSize: [0, 400, 0],
        gutterSize: 8,
        onDrag: function() {
            // Adjust editor size if needed
        }
    });
}

// Sidebars Toggle Logic
function toggleSidebar(side) {
    const paneId = side === 'left' ? 'left-sidebar' : 'right-panel';
    const toggleBtnId = side === 'left' ? 'left-sidebar-toggle' : 'right-sidebar-toggle';
    
    const pane = document.getElementById(paneId);
    const btn = document.getElementById(toggleBtnId);
    const icon = btn.querySelector('i');
    
    const isCollapsed = pane.classList.toggle('collapsed');
    
    // Get current sizes from Split.js
    let currentSizes = splitInstance.getSizes();
    
    if (isCollapsed) {
        icon.className = side === 'left' ? 'bi bi-layout-sidebar' : 'bi bi-layout-sidebar-reverse';
        
        // Pin button to edge
        if (side === 'left') {
            btn.style.right = 'auto';
            btn.style.left = '10px';
            currentSizes[1] += currentSizes[0]; // Give space to center
            currentSizes[0] = 0;
        } else {
            btn.style.left = 'auto';
            btn.style.right = '10px';
            currentSizes[1] += currentSizes[2]; // Give space to center
            currentSizes[2] = 0;
        }

        // Hide gutter
        const gutters = document.querySelectorAll('.gutter');
        if (side === 'left') gutters[0].style.display = 'none';
        else gutters[1].style.display = 'none';
        
    } else {
        icon.className = side === 'left' ? 'bi bi-layout-sidebar-inset' : 'bi bi-layout-sidebar-inset-reverse';
        
        // Restore internal position
        if (side === 'left') {
            btn.style.left = '';
            btn.style.right = '12px';
            currentSizes[0] = 20;
            currentSizes[1] -= 20;
        } else {
            btn.style.right = '';
            btn.style.left = '12px';
            currentSizes[2] = 20;
            currentSizes[1] -= 20;
        }

        const gutters = document.querySelectorAll('.gutter');
        if (side === 'left') gutters[0].style.display = 'block';
        else gutters[1].style.display = 'block';
    }
    
    // Update Split.js
    splitInstance.setSizes(currentSizes);
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

window.addEventListener('DOMContentLoaded', () => {
    initSplit();
    loadManuscript();
});

async function loadManuscript() {
    const response = await fetch(`/projects/${projectUuid}/manuscript`);
    const tree = await response.json();
    const root = document.getElementById('manuscript-tree-root');
    root.innerHTML = '';
    
    if (tree.length === 0) {
        root.innerHTML = '<p class="text-ghost-muted italic py-2 px-1">Nenhum item ainda.</p>';
        showEmptyState('manuscript');
        return;
    }

    renderTreeNodes(tree, root);
    initDraggable();
}

function renderTreeNodes(nodes, container) {
    nodes.forEach(node => {
        const item = document.createElement('div');
        item.className = 'tree-item mb-1';
        item.dataset.uuid = node.uuid;
        item.dataset.type = node.type;
        item.dataset.isSystem = node.is_system;

        const icon = node.type === 'toc' ? 'bi-list-ul' : (node.type === 'section' ? 'bi-folder2-open' : (node.type === 'chapter' ? 'bi-journal-bookmark' : 'bi-text-paragraph'));
        const colorClass = node.type === 'toc' ? 'text-accent' : (node.type === 'section' ? 'text-primary' : (node.type === 'chapter' ? 'text-info' : 'text-ghost-muted'));

        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center py-1 px-2 rounded node-row ${activeItemUuid === node.uuid ? 'bg-primary bg-opacity-10 shadow-sm' : ''}" onclick="openManuscriptItem('${node.uuid}')">
                <div class="d-flex align-items-center overflow-hidden">
                    <i class="bi ${icon} ${colorClass} me-2 flex-shrink-0"></i>
                    <span class="node-title text-truncate ${activeItemUuid === node.uuid ? 'text-primary fw-bold' : 'text-ghost-muted'}">${node.title}</span>
                </div>
                <div class="node-actions d-none gap-2">
                    ${!node.is_system && node.type !== 'scene' ? `
                        <button class="btn btn-link btn-sm p-0 text-primary" type="button" title="Adicionar Filho" onclick="event.preventDefault(); event.stopPropagation(); createNewItem('${node.type === 'section' ? 'chapter' : 'scene'}', '${node.uuid}')">
                            <i class="bi bi-plus"></i>
                        </button>
                    ` : ''}
                    ${!node.is_system ? `
                    <button class="btn btn-link btn-sm p-0 text-danger" type="button" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="event.preventDefault(); event.stopPropagation(); itemToDeleteUuid = '${node.uuid}'">
                        <i class="bi bi-trash"></i>
                    </button>
                    ` : ''}
                </div>
            </div>
            <div class="tree-children ms-3 mt-1" id="children-of-${node.uuid}"></div>
        `;

        container.appendChild(item);

        if (node.children && node.children.length > 0) {
            renderTreeNodes(node.children, item.querySelector('.tree-children'));
        }
    });
}

function initDraggable() {
    const containers = document.querySelectorAll('.list-group, .tree-children');
    containers.forEach(el => {
        new Sortable(el, {
            group: 'nested',
            animation: 150,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            filter: '.is-system', // Prevent dragging system items
            onMove: function (evt) {
                // Prevent moving anything above a system item if it's the TOC
                if (evt.related.dataset.type === 'toc') return false;
            },
            onEnd: async function() {
                const sortingData = [];
                document.querySelectorAll('.tree-item').forEach((item, index) => {
                    const parent = item.parentElement.closest('.tree-item');
                    sortingData.push({
                        uuid: item.dataset.uuid,
                        parent_uuid: parent ? parent.dataset.uuid : null,
                        order: index
                    });
                });
                
                await fetch(`/projects/${projectUuid}/manuscript/sort`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ sorting: sortingData })
                });
            }
        });
    });
}

async function createNewItem(type, parentUuid = null) {
    const titles = { 'section': 'Nova Seção', 'chapter': 'Novo Capítulo', 'scene': 'Nova Cena' };
    const response = await fetch(`/projects/${projectUuid}/manuscript`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ title: titles[type], type: type, parent_uuid: parentUuid })
    });
    const item = await response.json();
    await loadManuscript();
    openManuscriptItem(item.uuid);
}

async function openManuscriptItem(uuid) {
    activeItemUuid = uuid;
    activeCardCategory = null;
    activeItemType = 'manuscript';
    manuscriptMode = 'writing'; // Reset to writing mode by default
    
    // Reset Editor Styles
    document.getElementById('editor-container').classList.remove('editor-planning-mode');
    
    // Update UI Toggles
    const btnWriting = document.getElementById('btn-mode-writing');
    const btnPlanning = document.getElementById('btn-mode-planning');
    btnWriting.classList.add('active');
    btnWriting.classList.replace('btn-outline-primary', 'btn-primary');
    btnPlanning.classList.remove('active');
    btnPlanning.classList.replace('btn-primary', 'btn-outline-secondary');
    
    document.getElementById('manuscript-mode-toggle').classList.remove('d-none');
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('editor-container').classList.remove('d-none');
    document.getElementById('card-connections-editor').classList.add('d-none');

    try {
        const response = await fetch(`/projects/${projectUuid}/manuscript/${uuid}`);
        const data = await response.json();
        const item = data.item;
        
        document.getElementById('current-item-title').innerText = item.title;
        document.getElementById('stat-type').innerText = item.type.charAt(0).toUpperCase() + item.type.slice(1);
        document.getElementById('stat-word-count').innerText = item.word_count || 0;
        
        const icons = { 'toc': 'bi-list-ul', 'section': 'bi-folder2-open', 'chapter': 'bi-journal-bookmark', 'scene': 'bi-text-paragraph' };
        document.getElementById('editor-type-icon').innerHTML = `<i class="bi ${icons[item.type]}"></i>`;
        
        easyMDE.value(data.content);
        
        // TOC specific logic
        if (item.type === 'toc') {
            document.getElementById('manuscript-mode-toggle').classList.add('d-none');
            easyMDE.codemirror.setOption("readOnly", true);
            if (!easyMDE.isPreviewActive()) easyMDE.togglePreview();
        } else {
            easyMDE.codemirror.setOption("readOnly", false);
            if (easyMDE.isPreviewActive()) easyMDE.togglePreview();
        }

        // Markdown Tips (Only for Chapters and Scenes)
        const tips = document.getElementById('markdown-tips');
        if (item.type === 'chapter' || item.type === 'scene') tips.classList.remove('d-none');
        else tips.classList.add('d-none');

        // Mark active in tree
        document.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
        const activeEl = document.querySelector(`[onclick="openManuscriptItem('${uuid}')"]`);
        if (activeEl) activeEl.closest('.list-group-item').classList.add('active');

    } catch (error) {
        console.error('Erro ao carregar item:', error);
    }
}

function setManuscriptMode(mode) {
    if (mode === manuscriptMode) return;
    manuscriptMode = mode;
    
    const btnWriting = document.getElementById('btn-mode-writing');
    const btnPlanning = document.getElementById('btn-mode-planning');
    const editorContainer = document.getElementById('editor-container');
    
    if (mode === 'writing') {
        btnWriting.classList.add('active');
        btnWriting.classList.replace('btn-outline-primary', 'btn-primary');
        btnPlanning.classList.remove('active');
        btnPlanning.classList.replace('btn-primary', 'btn-outline-secondary');
        editorContainer.classList.remove('editor-planning-mode');
        loadManuscriptContent();
    } else {
        btnPlanning.classList.add('active');
        btnPlanning.classList.replace('btn-outline-secondary', 'btn-primary');
        btnWriting.classList.remove('active');
        btnWriting.classList.replace('btn-primary', 'btn-outline-primary');
        editorContainer.classList.add('editor-planning-mode');
        loadManuscriptPlanning();
    }
}

async function loadManuscriptContent() {
    showSaveStatus('Carregando...', 'bi-arrow-repeat spin');
    try {
        const response = await fetch(`/projects/${projectUuid}/manuscript/${activeItemUuid}`);
        const data = await response.json();
        easyMDE.value(data.content);
        showSaveStatus('Salvo', 'bi-check2-all');
    } catch (error) {
        showSaveStatus('Erro ao carregar', 'bi-exclamation-triangle text-danger');
    }
}

async function loadManuscriptPlanning() {
    showSaveStatus('Carregando...', 'bi-arrow-repeat spin');
    try {
        const response = await fetch(`/projects/${projectUuid}/manuscript/${activeItemUuid}/planning`);
        const data = await response.json();
        easyMDE.value(data.content || "# Planejamento da Cena\n\nDescreva aqui os pontos chaves, objetivos e conflitos desta seção.");
        showSaveStatus('Salvo', 'bi-check2-all');
    } catch (error) {
        showSaveStatus('Erro ao carregar', 'bi-exclamation-triangle text-danger');
    }
}

// --- Title Edit Logic ---

function enableTitleEdit() {
    const title = document.getElementById('current-item-title');
    const input = document.getElementById('title-edit-input');
    title.classList.add('d-none');
    input.classList.remove('d-none');
    input.value = title.innerText;
    input.focus();
}

async function saveTitleEdit() {
    if (!activeItemUuid) return;
    const input = document.getElementById('title-edit-input');
    const title = document.getElementById('current-item-title');
    const newTitle = input.value;
    
    input.classList.add('d-none');
    title.classList.remove('d-none');
    
    if (newTitle === title.innerText) return;
    
    title.innerText = newTitle;
    const endpoint = activeItemType === 'manuscript' ? `/projects/${projectUuid}/manuscript/${activeItemUuid}/title` : `/projects/${projectUuid}/cards/${activeItemUuid}/title`;
    
    await fetch(endpoint, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ title: newTitle })
    });

    if (activeItemType === 'manuscript') {
        loadManuscript();
    } else {
        loadCards(activeCardCategory);
    }
}

// O modal é aberto via data-bs-toggle nos botões da árvore e barra lateral
// Esta função é chamada apenas após a confirmação no Modal
async function confirmDeleteManuscriptItem() {
    if (!itemToDeleteUuid) return;
    
    // Mostra estado de carregamento no botão
    const btn = document.getElementById('confirmDeleteBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...';

    try {
        await fetch(`/projects/${projectUuid}/manuscript/${itemToDeleteUuid}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        });
        
        if (activeItemUuid === itemToDeleteUuid) {
            activeItemUuid = null;
            document.getElementById('editor-container').classList.add('d-none');
            document.getElementById('empty-state').classList.remove('d-none');
        }

        // Fecha o modal de forma segura (via clique no botão fechar ou API se disponível)
        const modalElement = document.getElementById('deleteConfirmModal');
        const closeBtn = modalElement.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        
        itemToDeleteUuid = null;
        loadManuscript();
    } catch (error) {
        console.error('Erro ao excluir:', error);
        alert('Erro ao excluir item.');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

// --- Card (Worldbuilding) Logic ---

async function loadCards(type) {
    activeCardCategory = type;
    activeItemUuid = null;
    activeItemType = 'card-list';

    // Highlight active worldbuilding button in the grid
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    document.getElementById(`nav-${type}`).classList.add('active');

    // UI state & clear search
    document.getElementById('cards-search').value = '';
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.remove('d-none');
    
    // Update headers
    const titles = { 'scenario': 'Geografia / Cenários', 'character': 'Personagens / Elenco', 'object': 'Itens / Objetos' };
    const icons = { 'scenario': 'bi-geo-alt', 'character': 'bi-people', 'object': 'bi-gem' };
    document.getElementById('cards-grid-title').innerText = titles[type] || 'Fichas';
    document.getElementById('cards-grid-icon').className = `bi ${icons[type]} me-2 text-primary fs-4`;

    const response = await fetch(`/projects/${projectUuid}/cards?type=${type}`);
    currentCards = await response.json();
    renderCards(currentCards);
    updateRightPanelForCards(type, currentCards);
}

function updateRightPanelForCards(type, cards) {
    const titles = { 'scenario': 'Cenários', 'character': 'Personagens', 'object': 'Itens' };
    
    // Only hide item-stats if nothing is being edited
    if (!activeItemUuid) {
        document.getElementById('item-stats').classList.add('d-none');
    }
    
    const panel = document.getElementById('dynamic-context-panel');
    panel.classList.remove('d-none');
    
    document.getElementById('dynamic-title').innerText = `LISTA DE ${titles[type].toUpperCase()}`;
    const dynamicContent = document.getElementById('dynamic-content');
    
    if (cards.length === 0) {
        dynamicContent.innerHTML = '<div class="opacity-50 italic">Nenhum item cadastrado.</div>';
        return;
    }

    dynamicContent.innerHTML = cards.map(c => `
        <div id="sidebar-card-${c.uuid}" class="mb-2 cp-sidebar-item d-flex align-items-center ${activeItemUuid === c.uuid ? 'bg-primary bg-opacity-10 text-primary' : ''}" onclick="openItem('${c.uuid}', 'card')">
            <i class="bi bi-dot me-1 ${activeItemUuid === c.uuid ? 'text-primary' : 'text-secondary'}"></i> 
            <span class="text-truncate">${c.title}</span>
        </div>
    `).join('');
}

function renderCards(cards) {
    const grid = document.getElementById('cards-grid');
    grid.innerHTML = '';

    if (cards.length === 0) {
        const type = activeCardCategory;
        const emptyIcons = { 'scenario': 'bi-map', 'character': 'bi-person-plus', 'object': 'bi-box-seam' };
        grid.innerHTML = `<div class="col-12 text-center py-5 text-body-secondary animate-fade-in"><i class="bi ${emptyIcons[type] || 'bi-plus-circle'} display-1 opacity-10 d-block mb-3"></i> Nenhuma ficha encontrada.</div>`;
        return;
    }

    cards.forEach((card, index) => {
        // Center Grid
        const col = document.createElement('div');
        col.className = 'col animate-fade-in';
        col.style.animationDelay = `${index * 0.05}s`;
        
        const placeholders = { 'scenario': 'Mapa', 'character': 'Personagem', 'object': 'Item' };
        const label = placeholders[card.type] || 'Ficha';
        const thumbUrl = card.image_uuid ? `/projects/${projectUuid}/gallery/${card.image_uuid}/image/thumb` : `https://placehold.co/400x400/1e1e2e/6272a4?text=${label}`;

        col.innerHTML = `
            <div class="card h-100 border-0 bg-secondary bg-opacity-10 shadow-sm card-ficha hover-lift transition-all overflow-hidden" onclick="openItem('${card.uuid}', 'card')">
                <div class="ratio ratio-1x1 position-relative">
                    <img src="${thumbUrl}" class="card-img-top object-fit-cover h-100" alt="${card.title}">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-0">
                        <div class="bg-dark bg-opacity-50 text-white p-2 backdrop-blur">
                            <h5 class="card-title h6 mb-0 text-truncate">${card.title}</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body p-2 d-flex justify-content-between align-items-center bg-body-tertiary">
                    <span class="small text-body-secondary" style="font-size: 0.75rem;">${card.type.charAt(0).toUpperCase() + card.type.slice(1)}</span>
                    <div class="d-flex gap-1" onclick="event.stopPropagation()">
                        <input type="file" id="card-upload-${card.uuid}" class="d-none" accept="image/*" onchange="uploadCardImage('${card.uuid}', this)">
                        
                        <!-- Selecionar da Galeria -->
                        <button class="btn btn-ghost-card btn-sm p-1" title="Selecionar da Galeria" data-bs-toggle="modal" data-bs-target="#galleryPickerModal" onclick="prepareGalleryPicker('${card.uuid}')">
                            <i class="bi bi-images"></i>
                        </button>
                        
                        <!-- Upload Novo -->
                        <button class="btn btn-ghost-card btn-sm p-1" title="Upload Nova Imagem" onclick="document.getElementById('card-upload-${card.uuid}').click()">
                            <i class="bi bi-upload"></i>
                        </button>
                        
                        <button class="btn btn-link btn-sm text-danger p-1" title="Excluir Ficha" data-bs-toggle="modal" data-bs-target="#cardDeleteModal" onclick="cardToDeleteUuid = '${card.uuid}'">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        grid.appendChild(col);
    });
}

async function createCard() {
    if (!activeCardCategory) return;
    const response = await fetch(`/projects/${projectUuid}/cards`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ title: `Novo(a) ${activeCardCategory}`, type: activeCardCategory })
    });
    const card = await response.json();
    await loadCards(activeCardCategory);
}

async function uploadCardImage(cardUuid, input) {
    if (!input.files || !input.files[0]) return;
    
    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    const cardElement = input.closest('.card-ficha');
    const originalContent = cardElement.innerHTML;
    cardElement.style.opacity = "0.5";
    cardElement.style.pointerEvents = "none";

    try {
        const response = await fetch(`/projects/${projectUuid}/cards/${cardUuid}/image`, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            loadCards(activeCardCategory);
        } else {
            alert('Erro ao enviar imagem.');
            cardElement.style.opacity = "1";
            cardElement.style.pointerEvents = "auto";
        }
    } catch (e) {
        console.error(e);
        alert('Erro na conexão.');
        cardElement.style.opacity = "1";
        cardElement.style.pointerEvents = "auto";
    }
}

function filterCards(query) {
    const q = query.toLowerCase();
    const filtered = currentCards.filter(c => c.title.toLowerCase().includes(q));
    renderCards(filtered);
}

async function confirmDeleteCard() {
    if (!cardToDeleteUuid) return;
    
    const btn = document.getElementById('confirmCardDeleteBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...';

    try {
        await fetch(`/projects/${projectUuid}/cards/${cardToDeleteUuid}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        
        const modalElement = document.getElementById('cardDeleteModal');
        const closeBtn = modalElement.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        
        cardToDeleteUuid = null;
        loadCards(activeCardCategory);
    } catch (error) {
        console.error('Erro ao excluir:', error);
        alert('Erro ao excluir ficha.');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

async function prepareGalleryPicker(cardUuid) {
    currentGalleryMode = 'card';
    cardBeingEditedImageUuid = cardUuid;
    document.getElementById('gallery-picker-search').value = '';
    
    // Refresh current gallery items
    const response = await fetch(`/projects/${projectUuid}/gallery`);
    currentGalleryItems = await response.json();
    
    renderGalleryPicker(currentGalleryItems);
    
    // Mostra o modal via gatilho HTML (mais seguro contra erros de escopo JS)
    document.getElementById('gallery-picker-trigger').click();
}

async function openGalleryPickerForEditor() {
    currentGalleryMode = 'editor';
    document.getElementById('gallery-picker-search').value = '';
    
    // Refresh current gallery items
    const response = await fetch(`/projects/${projectUuid}/gallery`);
    currentGalleryItems = await response.json();
    
    renderGalleryPicker(currentGalleryItems);
    
    // Mostra o modal via gatilho HTML (mais seguro contra erros de escopo JS)
    document.getElementById('gallery-picker-trigger').click();
}

function renderGalleryPicker(items) {
    const grid = document.getElementById('gallery-picker-grid');
    grid.innerHTML = '';
    
    if (items.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-4 text-body-secondary animate-fade-in">Nenhuma imagem encontrada.</div>';
        return;
    }

    items.forEach((item, index) => {
        const col = document.createElement('div');
        col.className = 'col animate-fade-in';
        col.style.animationDelay = `${index * 0.02}s`;
        
        // Determina se deve usar a miniatura ou imagem completa
        const thumbUrl = `/projects/${projectUuid}/gallery/${item.uuid}/image/thumb`;
        
        col.innerHTML = `
            <div class="card h-100 border-0 bg-secondary bg-opacity-10 shadow-sm hover-lift cp overflow-hidden" onclick="selectImageFromPicker('${item.uuid}', '${item.name}')">
                <div class="ratio ratio-1x1">
                    <img src="${thumbUrl}" class="card-img-top object-fit-cover" alt="${item.name}">
                </div>
                <div class="card-body p-2 text-center">
                    <p class="x-small text-truncate mb-0 fw-bold opacity-75">${item.name}</p>
                </div>
            </div>
        `;
        grid.appendChild(col);
    });
}

function filterGalleryPicker(query) {
    const q = query.toLowerCase();
    const filtered = currentGalleryItems.filter(item => item.name.toLowerCase().includes(q));
    renderGalleryPicker(filtered);
}

async function selectImageFromPicker(imageUuid, filename) {
    if (currentGalleryMode === 'editor') {
        const imageMarkdown = `\n\n![Ilustração](/projects/${projectUuid}/gallery/${imageUuid}/image)\n\n`;
        const cm = easyMDE.codemirror;
        const cursor = cm.getCursor();
        cm.replaceRange(imageMarkdown, cursor);
        
        // Fecha o modal via botão de fechar nativo (seguro contra erros de biblioteca)
        const modalEl = document.getElementById('galleryPickerModal');
        const closeBtn = modalEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        return;
    }

    if (!cardBeingEditedImageUuid) return;
    
    try {
        await fetch(`/projects/${projectUuid}/cards/${cardBeingEditedImageUuid}/image`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ image_uuid: imageUuid })
        });
        
        // Fecha o modal de forma segura
        const modalEl = document.getElementById('galleryPickerModal');
        const closeBtn = modalEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        
        loadCards(activeCardCategory);
    } catch (error) {
        console.error('Erro ao vincular imagem:', error);
    }
}

// --- General Editor Logic ---

async function openItem(uuid, type) {
    if (type === 'manuscript') return openManuscriptItem(uuid);
    
    activeItemUuid = uuid;
    activeItemType = type;
    
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('editor-container').classList.remove('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    
    // Hide markdown help by default when opening a card
    document.getElementById('markdown-tips').classList.add('d-none');
    
    const editorCon = document.getElementById('card-connections-editor');
    if (type === 'card') {
        editorCon.classList.remove('d-none');
        // Fetch connections
        const response = await fetch(`/projects/${projectUuid}/cards/${uuid}/connections`);
        currentConnections = await response.json();
        renderConnections();
    } else {
        editorCon.classList.add('d-none');
    }
    
    // Highlight active button in world grid
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    
    const endpoint = `/projects/${projectUuid}/cards/${uuid}`;
    const response = await fetch(endpoint);
    const data = await response.json();

    const item = data.card;
    document.getElementById('current-item-title').innerText = item.title;
    document.getElementById('stat-type').innerText = item.type.charAt(0).toUpperCase() + item.type.slice(1);
    document.getElementById('stat-word-count').innerText = item.word_count || 0;
    
    document.getElementById('item-stats').classList.remove('d-none');
    
    // Only hide dynamic panel if we are in manuscript mode (since cards/gallery use it for current context)
    if (activeCardCategory === null && activeItemType === 'manuscript') {
        document.getElementById('dynamic-context-panel').classList.add('d-none');
    } else if (type === 'card') {
        // If it's a card, refresh the list to highlight active item
        updateRightPanelForCards(activeCardCategory, currentCards);
    }

    easyMDE.value(data.content);
    
    showSaveStatus('Salvo', 'bi-check2-all');
}

async function saveActiveItem() {
    if (!activeItemUuid) return;
    const content = easyMDE.value();
    const endpoint = activeItemType === 'manuscript' ? `/projects/${projectUuid}/manuscript/${activeItemUuid}` : `/projects/${projectUuid}/cards/${activeItemUuid}`;
    
    const response = await fetch(endpoint, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ content: content })
    });

    const item = await response.json();
    document.getElementById('stat-word-count').innerText = item.word_count || 0;
    showSaveStatus('Salvo', 'bi-check2-all');
}

function showSaveStatus(text, iconClass) {
    const status = document.getElementById('save-status');
    status.innerHTML = `<i class="bi ${iconClass} me-1"></i> ${text}`;
}

// --- Gallery Logic ---

function openGallery() {
    activeItemUuid = null;
    activeItemType = 'gallery';
    
    // UI state & clear search
    document.getElementById('gallery-search').value = '';
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('gallery-container').classList.remove('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');

    // Highlight sidebar
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    document.getElementById('nav-gallery').classList.add('active');

    loadGallery();
    updateRightPanelForGallery();
}

function updateRightPanelForGallery() {
    document.getElementById('item-stats').classList.add('d-none');
    const panel = document.getElementById('dynamic-context-panel');
    panel.classList.remove('d-none');
    
    document.getElementById('dynamic-title').innerText = 'ESTATÍSTICAS DA GALERIA';
    const dynamicContent = document.getElementById('dynamic-content');
    
    const totalItems = currentGalleryItems.length;
    const totalBytes = currentGalleryItems.reduce((acc, item) => acc + (item.filesize || 0), 0);
    
    dynamicContent.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <span>Imagens:</span>
            <span class="fw-bold text-primary">${totalItems}</span>
        </div>
        <div class="d-flex justify-content-between">
            <span>Espaço em disco:</span>
            <span class="fw-bold text-accent">${formatBytes(totalBytes)}</span>
        </div>
    `;
}

async function loadGallery() {
    const response = await fetch(`/projects/${projectUuid}/gallery`);
    currentGalleryItems = await response.json();
    renderGallery(currentGalleryItems);
    updateRightPanelForGallery(); // Refreshes stats after load
}

function renderGallery(items) {
    const grid = document.getElementById('gallery-grid');
    grid.innerHTML = '';

    if (items.length === 0) {
        grid.innerHTML = '<div class="col-12 text-center py-5 text-body-secondary animate-fade-in"><i class="bi bi-camera-reels display-1 opacity-10 d-block mb-3"></i> Nenhuma imagem encontrada.</div>';
        return;
    }

    items.forEach((item, index) => {
        const extension = item.file_path ? item.file_path.split('.').pop().toUpperCase() : 'IMG';
        const displayName = item.name.replace(/\.[^/.]+$/, "");

        const col = document.createElement('div');
        col.className = 'col animate-fade-in';
        col.style.animationDelay = `${index * 0.03}s`;
        col.innerHTML = `
            <div class="card h-100 border-0 bg-secondary bg-opacity-10 shadow-sm position-relative group overflow-hidden gallery-card text-start">
                <div class="ratio ratio-1x1 position-relative">
                    <span class="gallery-badge">${extension}</span>
                    <img src="/projects/${projectUuid}/gallery/${item.uuid}/image/thumb" class="card-img-top object-fit-cover" alt="${displayName}">
                    <!-- Overlay de zoom/view -->
                    <a href="/projects/${projectUuid}/gallery/${item.uuid}/image" target="_blank" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 transition-opacity text-white text-decoration-none gallery-overlay" style="pointer-events: none;">
                        <i class="bi bi-search fs-3"></i>
                    </a>
                </div>
                <div class="card-body p-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <span class="small text-truncate text-body-secondary fw-bold flex-grow-1" style="max-width: 100px;" title="${item.name}">${displayName}</span>
                    <div class="d-flex gap-1">
                        <button class="btn btn-link btn-sm text-primary p-0" title="Renomear" data-bs-toggle="modal" data-bs-target="#galleryRenameModal" onclick="prepareRenameImage('${item.uuid}', '${displayName.replace(/'/g, "\\'")}', '${extension}')">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-link btn-sm text-danger p-0" title="Excluir" data-bs-toggle="modal" data-bs-target="#galleryDeleteModal" onclick="galleryItemToDeleteUuid = '${item.uuid}'">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        const card = col.querySelector('.gallery-card');
        const overlay = col.querySelector('.gallery-overlay');
        
        card.onmouseenter = () => {
            overlay.style.opacity = "1";
            overlay.style.pointerEvents = "auto";
        };
        card.onmouseleave = () => {
            overlay.style.opacity = "0";
            overlay.style.pointerEvents = "none";
        };

        grid.appendChild(col);
    });
}

async function uploadImage(input) {
    if (!input.files || !input.files[0]) return;
    
    const formData = new FormData();
    formData.append('image', input.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    const btn = document.querySelector('[onclick*="gallery-upload-input"]');
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

    try {
        const response = await fetch(`/projects/${projectUuid}/gallery`, {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            loadGallery();
        } else {
            alert('Erro ao enviar imagem.');
        }
    } catch (e) {
        console.error(e);
        alert('Erro na conexão.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
        input.value = '';
    }
}

// deleteGalleryItem removido - usando data-bs-toggle nativo no botão da lixeira

async function confirmDeleteGalleryItem() {
    if (!galleryItemToDeleteUuid) return;
    
    const btn = document.getElementById('confirmGalleryDeleteBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Excluindo...';

    try {
        await fetch(`/projects/${projectUuid}/gallery/${galleryItemToDeleteUuid}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        
        // Esconde o modal de forma segura (via clique no botão fechar)
        const modalElement = document.getElementById('galleryDeleteModal');
        const closeBtn = modalElement.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        
        galleryItemToDeleteUuid = null;
        loadGallery();
    } catch (error) {
        console.error('Erro ao excluir:', error);
        alert('Erro ao excluir imagem.');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

function prepareRenameImage(uuid, name, ext) {
    galleryItemToRenameUuid = uuid;
    currentImageExt = ext;
    document.getElementById('new-image-name').value = name;
    document.getElementById('image-ext-preview').innerText = `.${ext}`;
}

async function confirmRenameGalleryItem() {
    if (!galleryItemToRenameUuid) return;
    
    const newName = document.getElementById('new-image-name').value.trim();
    if (!newName) return;

    const btn = document.getElementById('confirmGalleryRenameBtn');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Salvando...';

    const fullFileName = `${newName}.${currentImageExt.toLowerCase()}`;

    try {
        await fetch(`/projects/${projectUuid}/gallery/${galleryItemToRenameUuid}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ name: fullFileName })
        });
        
        const modalElement = document.getElementById('galleryRenameModal');
        const closeBtn = modalElement.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        
        galleryItemToRenameUuid = null;
        loadGallery();
    } catch (error) {
        console.error('Erro ao renomear:', error);
        alert('Erro ao renomear imagem.');
    } finally {
        btn.disabled = false;
        btn.innerText = originalText;
    }
}

// --- Worldbuilding Graph & Connections ---

let worldGraph = null;
let currentConnections = [];

function openGraph() {
    activeItemUuid = null;
    activeItemType = 'graph';
    
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('graph-container').classList.remove('d-none');
    
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    document.getElementById('nav-connections').classList.add('active');
    
    loadGraphData();
}

async function loadGraphData(force = false) {
    const container = document.getElementById('graph-view');
    const response = await fetch(`/projects/${projectUuid}/graph`);
    const data = await response.json();
    
    // Adjust colors to be more vibrant
    const colors = { 'character': '#22c55e', 'scenario': '#3b82f6', 'object': '#eab308' };
    
    const gData = {
        nodes: data.nodes.map(n => ({ id: n.uuid, name: n.title, type: n.type, color: colors[n.type] || '#94a3b8' })),
        links: data.links.map(l => ({ source: l.source, target: l.target }))
    };
    
    if (!worldGraph) {
        // Use 2D ForceGraph for Obsidian style and better performance/labels
        worldGraph = ForceGraph()(container)
            .graphData(gData)
            .nodeLabel('name')
            .nodeColor('color')
            .width(container.clientWidth)
            .height(container.clientHeight)
            .backgroundColor('rgba(0,0,0,0)') 
            .onNodeClick(node => {
                openItem(node.id, 'card');
            })
            .nodeCanvasObject((node, ctx, globalScale) => {
                const label = node.name;
                const fontSize = 12 / globalScale;
                ctx.font = `${fontSize}px Sans-Serif`;
                
                // Draw circle (node)
                const size = 5;
                ctx.beginPath();
                ctx.arc(node.x, node.y, size, 0, 2 * Math.PI, false);
                ctx.fillStyle = node.color;
                ctx.fill();

                // Draw label background
                const textWidth = ctx.measureText(label).width;
                const bckgDimensions = [textWidth, fontSize].map(n => n + fontSize * 0.2); // some padding

                ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                ctx.fillRect(node.x - bckgDimensions[0] / 2, node.y - bckgDimensions[1] / 2 + 12, ...bckgDimensions);

                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillStyle = node.color;
                ctx.fillText(label, node.x, node.y + 12);

                node.__bckgDimensions = bckgDimensions; // to perform selection
            })
            .nodePointerAreaPaint((node, color, ctx) => {
                ctx.fillStyle = color;
                const bckgDimensions = node.__bckgDimensions;
                bckgDimensions && ctx.fillRect(node.x - bckgDimensions[0] / 2, node.y - bckgDimensions[1] / 2 + 10, ...bckgDimensions);
            });
            
        // Initial zoom
        worldGraph.zoom(3);
        
        // Handle window resize
        window.addEventListener('resize', () => {
             if (worldGraph) {
                 worldGraph.width(container.clientWidth);
                 worldGraph.height(container.clientHeight);
             }
        });
    } else {
        worldGraph.graphData(gData);
    }
}

async function searchConnections(query) {
    const resultsPanel = document.getElementById('connection-results');
    if (!query || query.trim().length < 2) {
        resultsPanel.classList.add('d-none');
        return;
    }
    
    try {
        const params = new URLSearchParams();
        params.append('q', query);
        
        // Anti-exclusão: não sugerir a própria ficha nem as já conectadas
        const excludeUuids = [activeItemUuid, ...(currentConnections || []).map(c => c.uuid)];
        excludeUuids.forEach(uuid => {
            if (uuid) params.append('exclude[]', uuid);
        });

        const response = await fetch(`/projects/${projectUuid}/cards/search?${params.toString()}`);
        if (!response.ok) throw new Error('Search failed');
        
        const cards = await response.json();
        
        if (cards.length === 0) {
            resultsPanel.innerHTML = '<div class="p-2 text-body-secondary x-small italic text-center">Nenhum resultado</div>';
        } else {
            resultsPanel.innerHTML = cards.map(c => {
                const escapedTitle = c.title.replace(/'/g, "\\'");
                return `
                    <div class="p-2 cp-sidebar-item small d-flex align-items-center border-bottom border-secondary border-opacity-10" onclick="addConnection('${c.uuid}', '${escapedTitle}', '${c.type}')">
                        <i class="bi ${getIconForType(c.type)} me-2 text-primary opacity-75"></i> 
                        <span class="text-truncate">${c.title}</span>
                    </div>
                `;
            }).join('');
        }
        resultsPanel.classList.remove('d-none');
    } catch (error) {
        console.error('Erro na busca:', error);
        resultsPanel.innerHTML = '<div class="p-2 text-danger x-small italic text-center">Erro na busca</div>';
        resultsPanel.classList.remove('d-none');
    }
}

function getIconForType(type) {
    const icons = { 'character': 'bi-people', 'scenario': 'bi-geo-alt', 'object': 'bi-gem' };
    return icons[type] || 'bi-file-text';
}

async function addConnection(uuid, title, type) {
    currentConnections.push({ uuid, title, type });
    document.getElementById('connection-results').classList.add('d-none');
    document.getElementById('connection-search').value = '';
    renderConnections();
    saveConnections();
}

function removeConnection(uuid) {
    currentConnections = currentConnections.filter(c => c.uuid !== uuid);
    renderConnections();
    saveConnections();
}

function renderConnections() {
    const container = document.getElementById('active-connections');
    if (currentConnections.length === 0) {
        container.innerHTML = '<span class="text-body-secondary x-small italic">Sem conexões ainda.</span>';
        return;
    }
    
    const colors = { 'character': 'bg-success', 'scenario': 'bg-primary', 'object': 'bg-warning text-dark' };
    
    container.innerHTML = currentConnections.map(c => `
        <span class="badge ${colors[c.type] || 'bg-secondary'} d-flex align-items-center gap-1">
            ${c.title}
            <i class="bi bi-x cp" onclick="removeConnection('${c.uuid}')"></i>
        </span>
    `).join('');
}

async function saveConnections() {
    if (!activeItemUuid) return;
    
    await fetch(`/projects/${projectUuid}/cards/${activeItemUuid}/connections`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ related_uuids: currentConnections.map(c => c.uuid) })
    });
}

// --- Project Bible Logic ---

async function openBible() {
    activeItemUuid = null;
    activeCardCategory = null;
    
    // Update Navigation UI
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    document.getElementById('nav-bible').classList.add('active');
    
    showEmptyState(null); // Clear other views
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(el => el.classList.remove('active'));
    document.querySelector('[onclick="openBible()"]').classList.add('active');
    
    document.getElementById('bible-container').classList.remove('d-none');
    document.getElementById('empty-state').classList.add('d-none');
    
    try {
        const response = await fetch(`/projects/${projectUuid}/bible`);
        const data = await response.json();
        bibleEditor.value(data.content);
        bibleSummaryEditor.value(data.summary || '');
    } catch (error) {
        console.error('Erro ao carregar bíblia:', error);
    }
}

async function saveBibleContent() {
    try {
        await fetch(`/projects/${projectUuid}/bible`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                content: bibleEditor.value(),
                summary: bibleSummaryEditor.value()
            })
        });
        showBibleSaveStatus('Salvo', 'bi-check2-all');
    } catch (error) {
        showBibleSaveStatus('Erro ao salvar', 'bi-exclamation-triangle text-danger');
    }
}

function syncBibleWithAI() {
    // Placeholder para futura integração com IA
    alert("Em breve: A IA processará todo o seu manuscrito e gerará um resumo narrativo atualizado automaticamente!");
}

function showBibleSaveStatus(text, iconClass) {
    const status = document.getElementById('bible-save-status');
    if (status) {
        status.innerHTML = `<i class="bi ${iconClass} me-1"></i> ${text}`;
    }
}
</script>


@endsection
