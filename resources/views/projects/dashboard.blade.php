@extends('layouts.app')

@section('title', $project->name . ' - Workspace')

@section('content')


<div class="container-fluid px-4 mt-2">
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
                            <h3 id="current-item-title" class="fw-bold mb-0 cp-title" onclick="enableTitleEdit()">Título</h3>
                            <input type="text" id="title-edit-input" class="form-control form-control-lg bg-transparent border-0 text-body fw-bold d-none p-0 ms-2" style="font-size: 1.75rem;" onblur="saveTitleEdit()" onkeyup="if(event.key==='Enter') saveTitleEdit()">
                        </div>
                        <div id="save-status" class="text-body-secondary small">
                            <i class="bi bi-check2-all me-1"></i> Salvo
                        </div>
                    </div>
                    <div class="flex-grow-1 overflow-auto custom-editor-area">
                        <textarea id="markdown-editor"></textarea>
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
                            <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-25" style="width: 200px;">
                                <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search opacity-50"></i></span>
                                <input type="text" id="cards-search" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrar..." oninput="filterCards(this.value)">
                            </div>
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
                <hr class="border-secondary opacity-25 my-4">
                <button id="delete-item-btn" type="button" class="btn btn-sm btn-outline-danger w-100 mt-2 d-none" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="itemToDeleteUuid = activeItemUuid">
                    <i class="bi bi-trash me-1"></i> Excluir Item
                </button>
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

<!-- EasyMDE & SortableJS & Split.js -->
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://unpkg.com/split.js/dist/split.min.js"></script>

<script>
let easyMDE;
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

const projectUuid = "{{ $project->uuid }}";

document.addEventListener('DOMContentLoaded', function() {
    easyMDE = new EasyMDE({
        element: document.getElementById('markdown-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        placeholder: "Use sua criatividade...",
        toolbar: ["bold", "italic", "heading", "|", "quote", "unordered-list", "ordered-list", "|", "preview", "side-by-side", "fullscreen", "|", "guide"]
    });

    easyMDE.codemirror.on("change", () => {
        if (!activeItemUuid) return;
        showSaveStatus('Salvando...', 'bi-arrow-repeat spin');
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveActiveItem, 1500);
    });

    loadManuscript();
});

// --- UI Utility Logic ---

function showEmptyState(category) {
    activeItemUuid = null;
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('empty-state').classList.remove('d-none');
    
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

        const icon = node.type === 'section' ? 'bi-folder2-open' : (node.type === 'chapter' ? 'bi-journal-bookmark' : 'bi-text-paragraph');
        const colorClass = node.type === 'section' ? 'text-primary' : (node.type === 'chapter' ? 'text-info' : 'text-ghost-muted');

        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-center py-1 px-2 rounded node-row ${activeItemUuid === node.uuid ? 'bg-primary bg-opacity-10 shadow-sm' : ''}" onclick="openManuscriptItem('${node.uuid}')">
                <div class="d-flex align-items-center overflow-hidden">
                    <i class="bi ${icon} ${colorClass} me-2 flex-shrink-0"></i>
                    <span class="node-title text-truncate ${activeItemUuid === node.uuid ? 'text-primary fw-bold' : 'text-ghost-muted'}">${node.title}</span>
                </div>
                <div class="node-actions d-none gap-2">
                    ${node.type !== 'scene' ? `
                        <button class="btn btn-link btn-sm p-0 text-primary" type="button" title="Adicionar Filho" onclick="event.preventDefault(); event.stopPropagation(); createNewItem('${node.type === 'section' ? 'chapter' : 'scene'}', '${node.uuid}')">
                            <i class="bi bi-plus"></i>
                        </button>
                    ` : ''}
                    <button class="btn btn-link btn-sm p-0 text-danger" type="button" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="event.preventDefault(); event.stopPropagation(); itemToDeleteUuid = '${node.uuid}'">
                        <i class="bi bi-trash"></i>
                    </button>
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
    activeItemType = 'manuscript';
    
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('editor-container').classList.remove('d-none');
    document.getElementById('delete-item-btn').classList.remove('d-none');
    
    const response = await fetch(`/projects/${projectUuid}/manuscript/${uuid}`);
    const data = await response.json();
    const item = data.item;

    document.getElementById('current-item-title').innerText = item.title;
    document.getElementById('stat-type').innerText = item.type.charAt(0).toUpperCase() + item.type.slice(1);
    document.getElementById('stat-word-count').innerText = item.word_count || 0;
    
    const icons = { 'section': 'bi-folder2-open', 'chapter': 'bi-journal-bookmark', 'scene': 'bi-text-paragraph' };
    document.getElementById('editor-type-icon').innerHTML = `<i class="bi ${icons[item.type]}"></i>`;
    
    easyMDE.value(data.content);
    loadManuscript();
    showSaveStatus('Salvo', 'bi-check2-all');
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
    await fetch(`/projects/${projectUuid}/manuscript/${activeItemUuid}/title`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ title: newTitle })
    });
    loadManuscript();
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
    cardBeingEditedImageUuid = cardUuid;
    document.getElementById('gallery-picker-search').value = '';
    
    // Refresh current gallery items
    const response = await fetch(`/projects/${projectUuid}/gallery`);
    currentGalleryItems = await response.json();
    
    renderGalleryPicker(currentGalleryItems);
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
        col.innerHTML = `
            <div class="card h-100 border-0 bg-secondary bg-opacity-10 shadow-sm hover-lift cp overflow-hidden" onclick="selectImageForCard('${item.uuid}')">
                <div class="ratio ratio-1x1">
                    <img src="/projects/${projectUuid}/gallery/${item.uuid}/image/thumb" class="card-img-top object-fit-cover" alt="${item.name}">
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

async function selectImageForCard(imageUuid) {
    if (!cardBeingEditedImageUuid) return;
    
    await fetch(`/projects/${projectUuid}/cards/${cardBeingEditedImageUuid}/image`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ image_uuid: imageUuid })
    });
    
    // Fechar o modal via DOM (evita erro bootstrap is not defined)
    const modalElement = document.getElementById('galleryPickerModal');
    const closeBtn = modalElement.querySelector('.btn-close');
    if (closeBtn) closeBtn.click();
    
    loadCards(activeCardCategory);
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
    document.getElementById('delete-item-btn').classList.add('d-none');
    
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
    document.getElementById('delete-item-btn').classList.add('d-none');

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
</script>


@endsection
