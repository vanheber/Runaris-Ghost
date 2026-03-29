@extends('layouts.app')

@section('title', $project->name . ' - Workspace')

@section('content')


<div class="container-fluid px-4 mt-2">
    <!-- Layout Principal -->
    <div id="main-layout" class="d-row d-flex vh-workspace mt-3">
        
        <!-- Sidebar Esquerda -->
        <div id="left-sidebar" class="split-pane card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
            <button id="left-sidebar-toggle" class="btn-ghost-card position-absolute toggle-btn-left" onclick="toggleSidebar('left')" title="Navegação">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>

            <div class="sidebar-content mt-4 overflow-auto scroll-custom h-100">
                <h6 class="text-body-secondary small text-uppercase fw-bold mb-4 ls-wide">Worldbuilding</h6>
                
                <nav class="nav flex-column mb-4">
                    <a id="nav-scenario" class="nav-link nav-world mb-2 p-2 rounded d-flex align-items-center text-body-secondary" href="#" onclick="loadCards('scenario')">
                        <i class="bi bi-geo-alt me-2"></i> Geografia / Cenários
                    </a>
                    <a id="nav-character" class="nav-link nav-world mb-2 p-2 rounded d-flex align-items-center text-body-secondary" href="#" onclick="loadCards('character')">
                        <i class="bi bi-people me-2"></i> Personagens
                    </a>
                    <a id="nav-object" class="nav-link nav-world mb-2 p-2 rounded d-flex align-items-center text-body-secondary" href="#" onclick="loadCards('object')">
                        <i class="bi bi-gem me-2"></i> Objetos
                    </a>
                </nav>

                <div id="card-list-container" class="mb-4 d-none">
                    <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                        <span id="active-card-type-label" class="fw-bold text-uppercase small text-primary opacity-75">Fichas</span>
                        <button class="btn btn-link btn-sm p-0 text-primary" onclick="createCard()">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div id="card-list" class="list-group list-group-flush mb-3"></div>
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

<!-- EasyMDE & SortableJS & Split.js -->
<link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script src="https://unpkg.com/split.js/dist/split.min.js"></script>

<script>
let easyMDE;
let activeItemUuid = null;
let activeItemType = null; // 'scene' or 'card'
let activeCardCategory = null; // 'scenario', 'character', 'object'
let itemToDeleteUuid = null;
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
    
    // Highlight active worldbuilding tab
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('text-primary', 'fw-bold'));
    document.getElementById(`nav-${type}`).classList.add('text-primary', 'fw-bold');

    const response = await fetch(`/projects/${projectUuid}/cards?type=${type}`);
    const cards = await response.json();
    
    document.getElementById('card-list-container').classList.remove('d-none');
    document.getElementById('active-card-type-label').innerText = (type === 'scenario' ? 'Geografia' : (type === 'character' ? 'Personagens' : 'Objetos'));
    
    const list = document.getElementById('card-list');
    list.innerHTML = '';

    if (cards.length === 0) {
        list.innerHTML = '<p class="text-ghost-muted px-2 py-1 small italic">Vazio.</p>';
        showEmptyState(type);
        return;
    }

    cards.forEach(card => {
        const item = document.createElement('a');
        item.href = '#';
        item.className = `list-group-item list-group-item-action bg-transparent border-0 px-2 py-1 text-ghost-muted mb-1 rounded ${activeItemUuid === card.uuid ? 'active bg-primary bg-opacity-10 text-primary fw-bold' : ''}`;
        item.innerHTML = `<i class="bi bi-file-earmark-person me-2"></i> ${card.title}`;
        item.onclick = (e) => { e.preventDefault(); openItem(card.uuid, 'card'); };
        list.appendChild(item);
    });
}

async function createCard() {
    if (!activeCardCategory) return;
    const response = await fetch(`/projects/${projectUuid}/cards`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ title: `Novo ${activeCardCategory}`, type: activeCardCategory })
    });
    const card = await response.json();
    await loadCards(activeCardCategory);
    openItem(card.uuid, 'card');
}

// --- General Editor Logic ---

async function openItem(uuid, type) {
    if (type === 'manuscript') return openManuscriptItem(uuid);
    
    activeItemUuid = uuid;
    activeItemType = type;
    
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('editor-container').classList.remove('d-none');
    document.getElementById('delete-item-btn').classList.add('d-none');
    
    const endpoint = `/projects/${projectUuid}/cards/${uuid}`;
    const response = await fetch(endpoint);
    const data = await response.json();

    const item = data.card;
    document.getElementById('current-item-title').innerText = item.title;
    document.getElementById('stat-type').innerText = item.type;
    document.getElementById('stat-word-count').innerText = item.word_count || 0;
    
    document.getElementById('editor-type-icon').innerHTML = '<i class="bi bi-person-bounding-box"></i>';
    
    easyMDE.value(data.content);
    loadCards(activeCardCategory);
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
</script>


@endsection
