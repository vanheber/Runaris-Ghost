@extends('layouts.app')

@section('title', $project->name . ' - Workspace')
@section('project-header-title')
    <span class="ms-2 ps-2 border-start border-secondary border-opacity-25 small text-body-secondary fw-medium d-none d-md-inline text-author-meta">
        {{ strtoupper($project->name) }}
    </span>
@endsection

@section('content')


<div class="container-fluid px-4">
    <!-- Secondary Navbar for Worldbuilding -->
    <div class="d-flex align-items-center gap-2 border-bottom border-secondary border-opacity-10 world-navbar overflow-x-auto text-nowrap scroll-custom world-navbar-sticky">
        <a id="nav-scenario" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="loadCards('scenario')" title="Geografia"><i class="bi bi-geo-alt me-1 text-primary"></i> Geografia</a>
        <a id="nav-character" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="loadCards('character')" title="Personagens"><i class="bi bi-people me-1 text-primary"></i> Personagens</a>
        <a id="nav-lore" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="loadCards('lore')" title="Lore"><i class="bi bi-mortarboard me-1 text-primary"></i> Lore</a>
        <a id="nav-object" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="loadCards('object')" title="Objetos"><i class="bi bi-gem me-1 text-primary"></i> Objetos</a>
        <a id="nav-gallery" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="openGallery()" title="Galeria"><i class="bi bi-images me-1 text-primary"></i> Galeria</a>
        <a id="nav-connections" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="openGraph()" title="Conexões"><i class="bi bi-diagram-3 me-1 text-primary"></i> Conexões</a>
        <a id="nav-bible" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="openBible()" title="Bíblia"><i class="bi bi-book me-1 text-primary"></i> Bíblia</a>
        <a id="nav-export" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="openExport()" title="Exportar"><i class="bi bi-cloud-download me-1 text-primary"></i> Exportar</a>
        <a id="nav-backup" class="btn btn-sm border-0 px-3 py-2 bg-body-tertiary shadow-sm text-secondary nav-world hover-lift" href="#" onclick="openBackup()" title="Backup"><i class="bi bi-shield-check me-1 text-primary"></i> Backup</a>
    </div>

    <style>
        @keyframes pulse-backup {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
            50% { transform: scale(1.03); box-shadow: 0 0 0 8px rgba(255, 193, 7, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
        }
        .pulse-warning {
            animation: pulse-backup 2s infinite ease-in-out;
            border: 1px solid rgba(255, 193, 7, 0.6) !important;
            background-color: rgba(255, 193, 7, 0.15) !important;
            color: #ffc107 !important;
            z-index: 10;
        }
        .nav-world.active {
            background-color: var(--bs-primary) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25) !important;
        }
        .nav-world.active i {
            color: white !important;
        }
        /* Bible Styles */
        .custom-accordion .accordion-item {
            background: transparent;
            border: 1px solid rgba(13, 110, 253, 0.1);
            margin-bottom: 0.5rem;
            border-radius: 0.75rem !important;
            overflow: hidden;
        }
        .custom-accordion .accordion-button {
            background: rgba(13, 110, 253, 0.02);
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
        }
        .custom-accordion .accordion-button:not(.collapsed) {
            background: rgba(13, 110, 253, 0.05);
            color: var(--bs-primary);
            box-shadow: none;
        }
        .custom-accordion .accordion-body {
            background: var(--bs-body-bg);
            font-size: 0.9rem;
            line-height: 1.6;
            color: var(--bs-body-color);
            padding: 1.25rem;
        }
        .summary-empty {
            font-style: italic;
            color: var(--bs-secondary);
            opacity: 0.6;
        }

        /* Manuscript Tree Actions */
        .node-row {
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .node-row:hover {
            background: rgba(13, 110, 253, 0.05);
        }
        .node-row.active {
            background: var(--bs-primary) !important;
            color: white !important;
        }
        .node-row.active .node-title, .node-row.active i {
            color: white !important;
        }
        .node-actions {
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .node-row:hover .node-actions {
            opacity: 1;
        }
        .btn-node-action {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .btn-node-action:hover {
            background: rgba(13, 110, 253, 0.15);
            transform: scale(1.1);
        }
        .node-row.active .btn-node-action:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .node-row.active .btn-node-action {
            color: white !important;
        }
    </style>

    <!-- Graph Visualization Lib -->
    <script src="//unpkg.com/force-graph"></script>
    <!-- Layout Principal -->
    <div id="main-layout" class="d-flex align-items-start main-layout-spacing">
        
        <!-- Sidebar Esquerda -->
        <div id="left-sidebar" class="split-pane card bg-body-tertiary border-0 shadow-sm p-4 sidebar-sticky">
            <button id="left-sidebar-toggle" class="btn-ghost-card position-absolute toggle-btn-left" onclick="toggleSidebar('left')" title="Navegação">
                <i class="bi bi-layout-sidebar-inset"></i>
            </button>

            <div class="sidebar-content mt-4 overflow-y-auto scroll-custom">

                <!-- Sessão: Escrita -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="text-body-secondary small text-uppercase fw-bold mb-0 ls-wide cp" onclick="showEmptyState('manuscript')">Manuscrito</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-link text-primary p-0" onclick="createNewItem('scene')" title="Nova Cena (Raiz)">
                            <i class="bi bi-file-earmark-plus"></i>
                        </button>
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
        <div id="center-editor" class="split-pane p-0 center-editor-fluid">
            <div id="editor-wrapper" class="p-0 mx-1">
                <div id="editor-container" class="card bg-body-tertiary border-0 shadow-sm p-4 d-none flex-column mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <span id="editor-type-icon" class="me-2 text-primary"></span>
                            <div class="d-flex flex-column">
                                <h3 id="current-item-title" class="fw-bold mb-0 cp-title" onclick="enableTitleEdit()">Título</h3>
                                <div id="manuscript-mode-toggle" class="d-flex gap-2 mt-1 d-none">
                                    <button id="btn-mode-writing" class="btn btn-xs btn-outline-primary active py-0 px-2 small fs-xs" onclick="setManuscriptMode('writing')">ESCRITA</button>
                                    <button id="btn-mode-planning" class="btn btn-xs btn-outline-secondary btn-planning py-0 px-2 small fs-xs" onclick="setManuscriptMode('planning')">PLANEJAMENTO</button>
                                </div>
                            </div>
                            <input type="text" id="title-edit-input" class="form-control form-control-lg bg-transparent border-0 text-body fw-bold d-none p-0 ms-2 fs-author-title" onblur="saveTitleEdit()" onkeyup="if(event.key==='Enter') saveTitleEdit()">
                        </div>
                        <div id="save-status" class="text-body-secondary small d-flex align-items-center gap-3">
                            <button id="btn-magic-planning" class="btn btn-outline-info btn-icon-round d-none me-1" onclick="generateAiPlanning()" title="Sugerir ideias">
                                <i class="bi bi-stars"></i>
                            </button>
                            <button id="btn-magic-writing" class="btn btn-outline-info btn-icon-round d-none me-1" onclick="writeAiScene()" title="Escritor Fantasma">
                                <i class="bi bi-magic"></i>
                            </button>
                            <button id="btn-sync-to-bible" class="btn btn-outline-info btn-icon-round d-none me-1" onclick="syncSectionToBible()" title="Sincronizar com a Bíblia: Gera um resumo da seção atual para o cânone do projeto.">
                                <i class="bi bi-journal-arrow-up"></i>
                            </button>
                            <div id="save-status-container" class="d-flex align-items-center opacity-75">
                                <i id="save-status-icon" class="bi bi-check2-all me-1"></i> 
                                <span id="save-status-text">Salvo</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1 custom-editor-area">
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
                                <button class="btn btn-outline-primary border-secondary border-opacity-25" type="button" onclick="suggestAIConnections()" title="Sugerir conexões via IA">
                                    <i class="bi bi-magic"></i>
                                </button>
                            </div>
                            <div id="ai-suggestion-loading" class="mt-2 d-none">
                                <div class="d-flex align-items-center gap-2 text-primary x-small italic animate-pulse">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    O Fantasma está analisando conexões...
                                </div>
                            </div>
                            <div id="connection-results" class="position-absolute w-100 bg-body-tertiary border border-primary border-opacity-50 rounded mt-1 shadow-lg d-none connection-results-dropdown">
                                <!-- Resultados da busca -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bible View -->
                <div id="bible-container" class="card bg-body-tertiary border-0 shadow-sm p-4 d-none flex-column mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-book me-2 text-primary fs-4"></i>
                            <h3 class="fw-bold mb-0">Bíblia do Projeto</h3>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div id="bible-save-status" class="text-body-secondary small">
                                <i class="bi bi-check2-all me-1"></i> Salvo
                            </div>
                        </div>
                    </div>

                    <!-- Bible Mode Tabs (Style like Editor) -->
                    <div class="d-flex gap-2 mb-4" id="bible-tabs">
                        <button id="btn-bible-structure" class="btn btn-xs btn-primary active py-0 px-2 small fs-xs text-uppercase" onclick="setBibleMode('structure')">ESTRUTURA</button>
                        <button id="btn-bible-cerebellum" class="btn btn-xs btn-outline-secondary py-0 px-2 small fs-xs text-uppercase" onclick="setBibleMode('cerebellum')">CEREBELO</button>
                    </div>

                    <div class="tab-content flex-grow-1 overflow-y-auto scroll-custom px-1" id="bible-tabs-content" style="max-height: 70vh;">
                        <!-- Tab 1: Structure (Accordion) -->
                        <div class="tab-pane fade show active" id="bible-structure" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="text-body-secondary small mb-0">Resumos detalhados de cada capítulo e cena do manuscrito.</p>
                                <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small">
                                    <i class="bi bi-info-circle me-1"></i> Sincronize itens no editor
                                </div>
                            </div>
                            <div class="accordion accordion-flush custom-accordion" id="bible-manuscript-accordion">
                                <!-- Carregado via JS -->
                                <div class="text-center py-5 text-ghost-muted">
                                    <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                                    <p class="small">Construindo estrutura...</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Cerebellum (Master Resumo) -->
                        <div class="tab-pane fade" id="bible-cerebellum" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1">Cerebelo do Projeto</h6>
                                    <p class="text-body-secondary small mb-0">Macro-narrativa consolidada para contexto global da IA.</p>
                                </div>
                                <button id="btn-sync-cerebellum" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" onclick="syncCerebellum()">
                                    <i class="bi bi-stars me-1 text-warning"></i> Sincronizar Cerebelo
                                </button>
                            </div>
                            
                            <div class="border border-secondary border-opacity-10 rounded shadow-sm bg-body custom-editor-area">
                                <textarea id="bible-summary-editor"></textarea>
                            </div>
                            
                            <!-- Editor de Lore Oculto para compatibilidade de dados -->
                            <div class="d-none">
                                <textarea id="bible-editor"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Panel -->
                <div id="export-container" class="card bg-body-tertiary border-0 shadow-sm p-5 d-none flex-column animate-fade-in mb-4">
                    <!-- Project Cover Management -->
                    <div class="card border-0 shadow-sm p-4 bg-body mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-image me-2"></i> Capa do Projeto</h5>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#galleryPickerModal" onclick="openGalleryPickerForProjectCover()">
                                <i class="bi bi-collection me-1"></i> Escolher da Galeria
                            </button>
                        </div>
                        
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div id="project-cover-preview" class="ratio ratio-3x4 bg-secondary bg-opacity-10 rounded border border-primary border-opacity-10 overflow-hidden shadow-sm project-cover-preview-container">
                                    @if($project->cover_image_uuid)
                                        <img src="{{ url('/projects/'.$project->uuid.'/gallery/'.$project->cover_image_uuid.'/image/thumb') }}" class="object-fit-cover w-100 h-100" id="project-cover-img">
                                    @else
                                        <div class="d-flex flex-column align-items-center justify-content-center text-body-secondary opacity-50" id="project-cover-placeholder">
                                            <i class="bi bi-image fs-1 mb-2"></i>
                                            <span class="x-small">Sem Capa</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-9">
                                <p class="text-body-secondary small mb-3">
                                    A capa é essencial para a geração de ePubs e PDFs profissionais. 
                                    Recomendamos o formato <strong>1600x2560px</strong> para melhor compatibilidade com o Kindle.
                                </p>
                                <div class="alert alert-primary bg-primary bg-opacity-10 border-0 d-flex align-items-center py-2 px-3 small">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <span>Você pode enviar novas imagens na aba <strong>Galeria</strong> e depois selecioná-las aqui.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Metadata Form (Folha de Rosto e Metadados) -->
                    <div class="card border-0 shadow-sm p-4 bg-body mb-5">
                        <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-file-person me-2"></i> Folha de Rosto e Metadados <span class="badge bg-primary bg-opacity-10 text-primary fw-normal small ms-2">Obrigatório para Lojas</span></h5>
                        <form id="export-metadata-form" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-body-secondary small text-uppercase fw-bold">Título da Obra</label>
                                <input type="text" class="form-control" id="meta-title" value="{{ $project->name }}" onblur="saveExportMetadata()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-body-secondary small text-uppercase fw-bold">Autor / Pseudônimo</label>
                                <input type="text" class="form-control" id="meta-author" value="{{ $project->author }}" placeholder="Ex: J.R.R. Tolkien">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-body-secondary small text-uppercase fw-bold">ISBN</label>
                                <input type="text" class="form-control" id="meta-isbn" value="{{ $project->isbn }}" placeholder="Opcional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-body-secondary small text-uppercase fw-bold">Editora / Selo</label>
                                <input type="text" class="form-control" id="meta-publisher" value="{{ $project->publisher }}" placeholder="Sua editora independente">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-body-secondary small text-uppercase fw-bold">Data de Publicação</label>
                                <input type="text" class="form-control" id="meta-pubdate" value="{{ $project->publication_date }}" placeholder="Ex: 2026">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-body-secondary small text-uppercase fw-bold">Informações de Direitos Autorais</label>
                                <textarea class="form-control" id="meta-copyright" rows="2" placeholder="Todos os direitos reservados...">{{ $project->copyright_info }}</textarea>
                            </div>
                        </form>
                    </div>

                    <div class="row justify-content-center g-4 mb-5">
                        <div class="col-md-3">
                            <div class="card h-100 border-0 shadow-sm p-4 bg-body d-flex flex-column align-items-center text-center">
                                <i class="bi bi-book fs-1 text-primary mb-3"></i>
                                <h5 class="fw-bold">Kindle (ePub)</h5>
                                <p class="small text-body-secondary mb-4">Dispositivos e-reader.</p>
                                <div class="form-check form-switch fs-4">
                                    <input class="form-check-input" type="checkbox" id="export-epub" checked>
                                </div>
                                <div id="status-epub" class="mt-3 w-100"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card h-100 border-0 shadow-sm p-4 bg-body d-flex flex-column align-items-center text-center">
                                <i class="bi bi-file-pdf fs-1 text-danger mb-3"></i>
                                <h5 class="fw-bold">Impressão (PDF)</h5>
                                <p class="small text-body-secondary mb-4">Leitura e impressão.</p>
                                <div class="form-check form-switch fs-4">
                                    <input class="form-check-input" type="checkbox" id="export-pdf" checked>
                                </div>
                                <div id="status-pdf" class="mt-3 w-100"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card h-100 border-0 shadow-sm p-4 bg-body d-flex flex-column align-items-center text-center">
                                <i class="bi bi-browser-chrome fs-1 text-success mb-3"></i>
                                <h5 class="fw-bold">Leitor Web (HTML)</h5>
                                <p class="small text-body-secondary mb-4">Leitor interativo.</p>
                                <div class="form-check form-switch fs-4">
                                    <input class="form-check-input" type="checkbox" id="export-html" checked>
                                </div>
                                <div id="status-html" class="mt-3 w-100"></div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card h-100 border-0 shadow-sm p-4 bg-body d-flex flex-column align-items-center text-center">
                                <i class="bi bi-markdown fs-1 text-info mb-3"></i>
                                <h5 class="fw-bold">Manuscrito (.md)</h5>
                                <p class="small text-body-secondary mb-4">Fontes organizadas.</p>
                                <div class="form-check form-switch fs-4">
                                    <input class="form-check-input" type="checkbox" id="export-markdown" checked>
                                </div>
                                <div id="status-markdown" class="mt-3 w-100"></div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-auto py-4 border-top">
                        <button id="btn-run-export" class="btn btn-primary btn-lg rounded-pill px-5 shadow" onclick="runExportBatch()">
                            <i class="bi bi-gear-wide-connected me-2"></i> Gerar Arquivos Selecionados
                        </button>
                    </div>
                </div>
                
                <!-- Backup Panel -->
                <div id="backup-container" class="card bg-body-tertiary border-0 shadow-sm p-5 d-none flex-column animate-fade-in mb-4">
                    <!-- Backup Suggestion Alert -->
                    <div id="backup-alert" class="alert alert-warning border-0 shadow-sm d-none align-items-center animate-fade-in mb-4 py-3">
                        <i class="bi bi-shield-exclamation me-3 fs-4"></i>
                        <div class="flex-grow-1 text-dark">
                            <strong>Que tal fazer um backup agora?</strong> É importante manter seu trabalho salvo externamente com frequência.
                        </div>
                        <button type="button" class="btn-close ms-3" onclick="dismissBackupAlert()"></button>
                    </div>

                    <div class="card border-0 shadow-sm p-4 bg-body mb-4">
                        <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-shield-check me-2"></i> Backup do Projeto</h5>
                        <p class="text-body-secondary">
                            Este recurso gera um arquivo ZIP contendo todo o seu trabalho organizado de forma legível. 
                            Ao contrário do banco de dados, aqui os arquivos usam os <strong>títulos reais</strong> das cenas e lore.
                        </p>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 border border-secondary border-opacity-10 rounded bg-body-tertiary">
                                    <h6 class="fw-bold mb-2 small text-uppercase opacity-75">O que está incluído:</h6>
                                    <ul class="list-unstyled mb-0 small">
                                        <li class="mb-1"><i class="bi bi-check2 text-success me-2"></i> Manuscrito (Markdown estruturado)</li>
                                        <li class="mb-1"><i class="bi bi-check2 text-success me-2"></i> Worldbuilding (Personagens, Locais, Lore)</li>
                                        <li class="mb-1"><i class="bi bi-check2 text-success me-2"></i> Bíblia do Projeto & Resumos</li>
                                        <li class="mb-1"><i class="bi bi-check2 text-success me-2"></i> Galeria de Imagens</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center justify-content-center">
                                <div class="text-center">
                                    <a href="{{ url('/projects/'.$project->uuid.'/export/backup') }}" class="btn btn-primary btn-lg rounded-pill px-5 shadow hover-lift">
                                        <i class="bi bi-download me-2"></i> Baixar Backup Completo
                                    </a>
                                    <p class="x-small text-body-secondary mt-3 italic">Formato: ZIP (Markdown + Assets)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Snapshot & Rollback System -->
                    <div class="card border-0 shadow-sm p-4 bg-body mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-clock-history me-2"></i> Snapshots & Rollback</h5>
                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="createProjectSnapshot()">
                                <i class="bi bi-camera me-1"></i> Criar Novo Snapshot
                            </button>
                        </div>
                        
                        <p class="text-body-secondary small mb-4">
                            Snapshots são cópias de segurança locais do estado exato do seu projeto (incluindo o banco de dados SQLite). 
                            Use-os para criar pontos de restauração antes de grandes mudanças.
                        </p>

                        <div id="snapshots-list-container" class="border rounded bg-body-tertiary overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 small">
                                    <thead class="bg-dark bg-opacity-10">
                                        <tr>
                                            <th class="ps-3 py-2">Data</th>
                                            <th class="py-2">Tamanho</th>
                                            <th class="py-2 text-end pe-3">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody id="snapshots-table-body">
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-ghost-muted italic">Carregando snapshots...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary border-opacity-10">
                            <h6 class="fw-bold mb-3 small text-uppercase opacity-75">Importar Projeto / Restaurar Backup</h6>
                            <form action="{{ url('/projects/'.$project->uuid.'/restore') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-3 align-items-end">
                                @csrf
                                <div class="flex-grow-1">
                                    <label class="form-label x-small text-body-secondary fw-bold text-uppercase">Selecionar Arquivo ZIP</label>
                                    <input type="file" name="backup_file" class="form-control form-control-sm bg-body-tertiary border-secondary border-opacity-25" accept=".zip">
                                </div>
                                <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-4 shadow-sm" onclick="return confirm('ATENÇÃO: Isso substituirá TODO o conteúdo atual deste projeto pelo conteúdo do backup. Deseja continuar?')">
                                    <i class="bi bi-upload me-1"></i> Restaurar do Arquivo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div id="graph-container" class="card bg-body-tertiary border-0 shadow-sm d-none overflow-hidden position-relative mb-4 min-h-700">
                    <div id="graph-view" class="w-100 h-100"></div>
                    <div class="position-absolute top-0 end-0 p-3 z-2">
                        <button class="btn btn-sm btn-ghost-card backdrop-blur" onclick="loadGraphData(true)" title="Atualizar Grafo">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <!-- Navigation Help Overlay -->
                    <div class="position-absolute bottom-0 start-0 p-3 z-2">
                        <div class="badge bg-dark bg-opacity-50 backdrop-blur p-2 small border border-secondary border-opacity-25 pe-none">
                            <i class="bi bi-mouse me-2"></i> Scroll: Zoom | <i class="bi bi-arrows-move mx-2"></i> Arraste: Mover | <i class="bi bi-hand-index mx-2"></i> Clique: Abrir
                        </div>
                    </div>
                </div>

                <div id="empty-state" class="card bg-body-tertiary border-0 shadow-sm p-5 d-flex flex-column align-items-center justify-content-center text-center mb-4 min-h-50vh">
                    <div class="opacity-10 mb-4">
                        <i id="empty-icon" class="bi bi-feather display-1"></i>
                    </div>
                    <h2 id="empty-title" class="fw-bold mb-3">Onde a história começa?</h2>
                    <p id="empty-desc" class="text-body-secondary mb-4 max-w-md mx-auto">
                        Cada pixel foi pensado para o seu foco. Arraste as bordas ou colapse as barras laterais no topo para imersão total.
                    </p>
                    <div id="empty-actions" class="d-flex gap-3">
                        <button class="btn btn-primary" onclick="loadCards('scenario')">Geografia</button>
                        <button class="btn btn-primary" onclick="loadCards('lore')">Lore</button>
                        <button class="btn btn-outline-secondary border-secondary border-opacity-50" onclick="showEmptyState('manuscript')">Manuscrito</button>
                    </div>
                </div>

                <!-- Grid de Fichas (Worldbuilding) -->
                <div id="cards-grid-container" class="card bg-body-tertiary border-0 shadow-sm p-4 d-none flex-column mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <i id="cards-grid-icon" class="bi bi-people me-2 text-primary fs-4"></i>
                            <h3 id="cards-grid-title" class="fw-bold mb-0">Personagens</h3>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-25 search-input-w-sm">
                                <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search opacity-50"></i></span>
                                <input type="text" id="cards-search" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrar..." oninput="filterCards(this.value)">
                            </div>

                             <button class="btn btn-outline-info btn-icon-round me-1" onclick="suggestAiCard()" title="Sugerir Ficha">
                                <i class="bi bi-stars"></i>
                            </button>

                            <button class="btn btn-primary btn-icon-round" onclick="createCard()" title="Nova Ficha">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex-grow-1 overflow-y-auto overflow-x-hidden scroll-custom">
                        <div id="cards-grid" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                            <!-- Fichas serão carregadas aqui -->
                        </div>
                    </div>
                </div>

                <!-- Galeria de Imagens -->
                <div id="gallery-container" class="card bg-body-tertiary border-0 shadow-sm p-4 d-none flex-column mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-shrink-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-images me-2 text-primary fs-4"></i>
                            <h3 class="fw-bold mb-0">Galeria do Projeto</h3>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-25 search-input-w-md">
                                <span class="input-group-text bg-transparent border-0 px-2"><i class="bi bi-search opacity-50"></i></span>
                                <input type="text" id="gallery-search" class="form-control border-0 bg-transparent ps-0" placeholder="Buscar arte..." oninput="filterGallery(this.value)">
                            </div>
                            <input type="file" id="gallery-upload-input" class="d-none" accept="image/*" onchange="uploadImage(this)">
                            <button class="btn btn-primary btn-icon-round" title="Upload" onclick="document.getElementById('gallery-upload-input').click()">
                                <i class="bi bi-upload"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="flex-grow-1 overflow-y-auto overflow-x-hidden scroll-custom">
                        <div id="gallery-grid" class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                            <!-- Imagens serão carregadas aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel Direita (Estatísticas e IA) -->
        <div id="right-panel" class="split-pane card bg-body-tertiary border-0 shadow-sm p-4 sidebar-sticky">
            <button id="right-sidebar-toggle" class="btn-ghost-card position-absolute toggle-btn-right" onclick="toggleSidebar('right')" title="Estatísticas">
                <i class="bi bi-layout-sidebar-inset-reverse"></i>
            </button>

            <div class="sidebar-content mt-4 overflow-y-auto scroll-custom">
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
                    <div class="input-group input-group-sm rounded-pill overflow-hidden border border-secondary border-opacity-50 search-input-w-lg">
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

<!-- Modal de Sugestões de IA -->
<div class="modal fade" id="aiConnectionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body-tertiary border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fs-6 text-uppercase fw-bold text-primary ls-wide">Sugestões do Fantasma</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="ai-suggestions-list" class="modal-body py-4">
                <!-- Sugestões aqui -->
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
let bibleEditor;
let bibleSummaryEditor;
let activeItemUuid = null;
let activeCardCategory = null; // 'scenario', 'character', 'lore', 'object'
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
let imageMarkers = [];
let replacementRange = null; // Para troca de imagens
let imageRefreshTimeout;

const projectUuid = "{{ $project->uuid }}";
let activeProjectCoverUuid = "{{ $project->cover_image_uuid }}";

document.addEventListener('DOMContentLoaded', function() {
    const bootstrapToolbar = [
        { name: "undo", action: EasyMDE.undo, className: "bi bi-arrow-counterclockwise", title: "Desfazer (Ctrl+Z)" },
        { name: "redo", action: EasyMDE.redo, className: "bi bi-arrow-clockwise", title: "Refazer (Ctrl+Y)" },
        "|",
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
        {
            name: "pure-md",
            action: function(editor) {
                const cm = editor.codemirror;
                const wrapper = cm.getWrapperElement();
                const container = wrapper.closest('.EasyMDEContainer');
                const isActive = container.classList.toggle('pure-md-mode');
                
                // Seleciona o ícone e o botão pai (que está na toolbar)
                // Usamos o container para garantir que pegamos o botão certo
                const btnIcon = container.querySelector('.bi-markdown, .bi-pencil-square');
                const btn = btnIcon.parentElement;
                
                if (isActive) {
                    // MODO RAIO-X: Texto Puro (Markdown nativo sem overlays)
                    cm.setOption("mode", "markdown");
                    cm.removeOverlay(literaryOverlay);
                    
                    // Troca Visual do Botão
                    btnIcon.className = "bi bi-pencil-square";
                    btn.title = "Voltar ao Editor Literário";
                    btn.classList.add('active');
                    
                    // Limpa imagens fantasma no MD Puro
                    renderGhostImages();
                } else {
                    // MODO LITERÁRIO: Markdown + Overlays Customizados
                    cm.setOption("mode", "gfm");
                    cm.addOverlay(literaryOverlay);
                    
                    // Troca Visual do Botão
                    btnIcon.className = "bi bi-markdown";
                    btn.title = "Ver MD Puro (Raio-X)";
                    btn.classList.remove('active');
                    
                    // Renderiza imagens fantasma
                    setTimeout(renderGhostImages, 100);
                }
            },
            className: "bi bi-markdown",
            title: "Ver MD Puro (Raio-X)"
        },
        { name: "guide", action: "https://www.markdownguide.org/basic-syntax/", className: "bi bi-question-circle", title: "Guia Markdown" }
    ];

    easyMDE = new EasyMDE({
        element: document.getElementById('markdown-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        autoDownloadFontAwesome: false,
        placeholder: "Use sua criatividade...",
        toolbar: bootstrapToolbar,
        codeMirrorOptions: {
            viewportMargin: Infinity
        }
    });

    // CodeMirror Overlays for Literary Formatting
    const literaryOverlay = {
        token: function(stream) {
            // 1. Scene Divider: Atomic match - ONLY the first char gets the class
            if (stream.sol() && stream.match("-*-", false)) {
                stream.next(); // Consume only the first '-'
                return "divisao-cena";
            }
            
            // 2. Em Dash: Match "--" OR the real character "—"
            if (stream.match("--") || stream.match("—")) {
                return "travessao";
            }
            
            // 4. Image: ![alt](url)
            if (stream.match(/!\[.*\]\(.*\)/)) {
                return "ghost-image";
            }
            
            // 5. Skip ahead
            stream.next();
            return null;
        }
    };

    easyMDE.codemirror.addOverlay(literaryOverlay);

    bibleEditor = new EasyMDE({
        element: document.getElementById('bible-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        autoDownloadFontAwesome: false,
        placeholder: "Pense na bíblia como o DNA do seu projeto...",
        toolbar: bootstrapToolbar,
        codeMirrorOptions: {
            viewportMargin: Infinity
        }
    });

    bibleEditor.codemirror.addOverlay(literaryOverlay);

    bibleSummaryEditor = new EasyMDE({
        element: document.getElementById('bible-summary-editor'),
        spellChecker: false,
        autosave: { enabled: false },
        status: false,
        autoDownloadFontAwesome: false,
        placeholder: "O resumo narrativo será gerado aqui...",
        codeMirrorOptions: {
            viewportMargin: Infinity
        },
        toolbar: [
            { name: "bold", action: EasyMDE.toggleBold, className: "bi bi-type-bold", title: "Negrito" },
            { name: "italic", action: EasyMDE.toggleItalic, className: "bi bi-type-italic", title: "Itálico" },
            "|",
            "|",
            { 
                name: "sync", 
                action: syncBibleWithAI, 
                className: "bi bi-arrow-repeat", 
                title: "Sincronizar com Manuscrito" 
            }
        ]
    });

    easyMDE.codemirror.on("change", () => {
        if (!activeItemUuid) return;
        showSaveStatus('Salvando...', 'bi-arrow-repeat spin');
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveActiveItem, 1500);

        // Refresh ghost images com debounce curto
        clearTimeout(imageRefreshTimeout);
        imageRefreshTimeout = setTimeout(renderGhostImages, 300);
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

    loadManuscript();
    loadGallery();
});

function renderGhostImages() {
    if (!easyMDE) return;
    const cm = easyMDE.codemirror;
    const wrapper = cm.getWrapperElement();
    const container = wrapper.closest('.EasyMDEContainer');
    if (!container || container.classList.contains('pure-md-mode')) {
        imageMarkers.forEach(m => m.clear());
        imageMarkers = [];
        return;
    }
    imageMarkers.forEach(m => m.clear());
    imageMarkers = [];
    const lines = cm.getValue().split('\n');
    lines.forEach((line, idx) => {
        const regex = /!\[(.*?)\]\((.*?)\)/g;
        let match;
        while ((match = regex.exec(line)) !== null) {
            const alt = match[1];
            const urlOrName = match[2];
            
            // Tenta achar o item na galeria (por nome, UUID ou se a URL contém o UUID)
            const item = currentGalleryItems.find(i => {
                if (i.name === urlOrName || i.uuid === urlOrName) return true;
                if (urlOrName.includes(i.uuid)) return true;
                return false;
            });

            if (item) {
                const widget = document.createElement('div');
                widget.className = 'ghost-image-widget animate-fade-in';
                widget.title = "Clique para trocar esta imagem";
                widget.innerHTML = `<img src="/projects/${projectUuid}/gallery/${item.uuid}/image/thumb" alt="${alt}"><div class="ghost-image-caption">${alt || item.name}</div>`;
                
                // Lógica de Troca ao Clicar
                widget.onclick = (e) => {
                    e.stopPropagation();
                    const currentPos = marker.find();
                    if (currentPos) {
                        replacementRange = { from: currentPos.from, to: currentPos.to };
                        document.querySelectorAll('.ghost-image-widget').forEach(w => w.classList.remove('swapping'));
                        widget.classList.add('swapping');
                        openGalleryPickerForEditor(); // Abre o modal unificado
                    }
                };

                const marker = cm.markText(
                    {line: idx, ch: match.index}, 
                    {line: idx, ch: match.index + match[0].length}, 
                    {replacedWith: widget, handleMouseEvents: true, atomic: true}
                );
                imageMarkers.push(marker);
            }
        }
    });
}

// --- UI Utility Logic ---

function showEmptyState(category) {
    activeItemUuid = null;
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('empty-state').classList.remove('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    
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
        'lore': {
            icon: 'bi-mortarboard',
            title: 'Lore e Cultura',
            desc: 'Mitos, religiões, eventos históricos ou sistemas de magia. Documente o conhecimento que molda o mundo.',
            btn: `<button class="btn btn-ghost-primary" onclick="createCard('lore')">Nova Lore</button>`
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
            <div class="d-flex justify-content-between align-items-center py-1 px-2 rounded node-row ${activeItemUuid === node.uuid ? 'active' : ''}" onclick="openManuscriptItem('${node.uuid}')">
                <div class="d-flex align-items-center overflow-hidden">
                    <i class="bi ${icon} ${colorClass} me-2 flex-shrink-0"></i>
                    <span class="node-title text-truncate ${activeItemUuid === node.uuid ? '' : 'text-ghost-muted'}">${node.title}</span>
                </div>
                <div class="node-actions d-flex gap-1">
                    ${!node.is_system && node.type !== 'scene' ? `
                        <button class="btn btn-link btn-sm p-0 text-primary btn-node-action" type="button" title="Adicionar ${node.type === 'section' ? 'Capítulo' : 'Cena'}" onclick="event.preventDefault(); event.stopPropagation(); createNewItem('${node.type === 'section' ? 'chapter' : 'scene'}', '${node.uuid}')">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    ` : ''}
                    ${!node.is_system ? `
                    <button class="btn btn-link btn-sm p-0 text-danger btn-node-action" type="button" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" onclick="event.preventDefault(); event.stopPropagation(); itemToDeleteUuid = '${node.uuid}'">
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
    
    // Clear worldbuilding nav active state
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));

    // UI Update: Toggle Active Class in Sidebar
    document.querySelectorAll('.node-row').forEach(row => {
        row.classList.remove('active');
        row.querySelector('.node-title').classList.add('text-ghost-muted');
    });
    
    const activeRow = document.querySelector(`.tree-item[data-uuid="${uuid}"] .node-row`);
    if (activeRow) {
        activeRow.classList.add('active');
        activeRow.querySelector('.node-title').classList.remove('text-ghost-muted');
    }
    
    // Reset Editor Styles
    const editorContainer = document.getElementById('editor-container');
    if (editorContainer) editorContainer.classList.remove('editor-planning-mode');
    
    // Update UI Toggles
    const btnWriting = document.getElementById('btn-mode-writing');
    const btnPlanning = document.getElementById('btn-mode-planning');
    if (btnWriting) {
        btnWriting.classList.add('active');
        btnWriting.classList.replace('btn-outline-primary', 'btn-primary');
    }
    if (btnPlanning) {
        btnPlanning.classList.remove('active');
        btnPlanning.classList.replace('btn-primary', 'btn-outline-secondary');
    }
    
    const modeToggle = document.getElementById('manuscript-mode-toggle');
    if (modeToggle) modeToggle.classList.remove('d-none');
    
    const emptyState = document.getElementById('empty-state');
    if (emptyState) emptyState.classList.add('d-none');
    
    const bibleCon = document.getElementById('bible-container');
    if (bibleCon) bibleCon.classList.add('d-none');
    
    const graphCon = document.getElementById('graph-container');
    if (graphCon) graphCon.classList.add('d-none');
    
    const cardGridCon = document.getElementById('cards-grid-container');
    if (cardGridCon) cardGridCon.classList.add('d-none');
    
    const galleryCon = document.getElementById('gallery-container');
    if (galleryCon) galleryCon.classList.add('d-none');
    
    const backupCon = document.getElementById('backup-container');
    if (backupCon) backupCon.classList.add('d-none');
    
    const exportCon = document.getElementById('export-container');
    if (exportCon) exportCon.classList.add('d-none');
    
    if (editorContainer) editorContainer.classList.remove('d-none');
    
    const connectionsEditor = document.getElementById('card-connections-editor');
    if (connectionsEditor) connectionsEditor.classList.add('d-none');

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
            const modeToggle = document.getElementById('manuscript-mode-toggle');
            if (modeToggle) modeToggle.classList.add('d-none');
            
            const btnMagicWriting = document.getElementById('btn-magic-writing');
            if (btnMagicWriting) btnMagicWriting.classList.add('d-none');
            
            const btnMagicPlanning = document.getElementById('btn-magic-planning');
            if (btnMagicPlanning) btnMagicPlanning.classList.add('d-none');

            easyMDE.codemirror.setOption("readOnly", true);
            if (!easyMDE.isPreviewActive()) easyMDE.togglePreview();
        } else {
            const modeToggle = document.getElementById('manuscript-mode-toggle');
            if (modeToggle) modeToggle.classList.remove('d-none');

            const btnMagicWriting = document.getElementById('btn-magic-writing');
            const btnMagicPlanning = document.getElementById('btn-magic-planning');
            const btnSyncBible = document.getElementById('btn-sync-to-bible');

            // AI Buttons
            if (manuscriptMode === 'writing') {
                if (btnMagicWriting) btnMagicWriting.classList.remove('d-none');
                if (btnMagicPlanning) btnMagicPlanning.classList.add('d-none');
            } else {
                if (btnMagicPlanning) btnMagicPlanning.classList.remove('d-none');
                if (btnMagicWriting) btnMagicWriting.classList.add('d-none');
            }
            
            // Sync to Bible button only for Chapters and Scenes
            if (btnSyncBible) {
                if (item.type === 'chapter' || item.type === 'scene') btnSyncBible.classList.remove('d-none');
                else btnSyncBible.classList.add('d-none');
            }

            easyMDE.codemirror.setOption("readOnly", false);
            if (easyMDE.isPreviewActive()) easyMDE.togglePreview();
        }

        // Markdown Tips (Only for Chapters and Scenes)
        const tips = document.getElementById('markdown-tips');
        if (tips) {
            if (item.type === 'chapter' || item.type === 'scene') tips.classList.remove('d-none');
            else tips.classList.add('d-none');
        }

        // Mark active in tree
        document.querySelectorAll('.list-group-item').forEach(el => el.classList.remove('active'));
        const activeEl = document.querySelector(`[onclick="openManuscriptItem('${uuid}')"]`);
        if (activeEl) {
            const parent = activeEl.closest('.list-group-item');
            if (parent) parent.classList.add('active');
        }

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
        if (btnWriting) {
            btnWriting.classList.add('active');
            btnWriting.classList.replace('btn-outline-primary', 'btn-primary');
        }
        if (btnPlanning) {
            btnPlanning.classList.remove('active');
            btnPlanning.classList.replace('btn-primary', 'btn-outline-secondary');
        }
        if (editorContainer) editorContainer.classList.remove('editor-planning-mode');
        
        // AI Buttons
        const magicWriting = document.getElementById('btn-magic-writing');
        const magicPlanning = document.getElementById('btn-magic-planning');
        if (magicWriting) magicWriting.classList.remove('d-none');
        if (magicPlanning) magicPlanning.classList.add('d-none');
        
        loadManuscriptContent();
    } else {
        if (btnPlanning) {
            btnPlanning.classList.add('active');
            btnPlanning.classList.replace('btn-outline-secondary', 'btn-primary');
        }
        if (btnWriting) {
            btnWriting.classList.remove('active');
            btnWriting.classList.replace('btn-primary', 'btn-outline-primary');
        }
        if (editorContainer) editorContainer.classList.add('editor-planning-mode');
        
        // AI Buttons
        const magicWriting = document.getElementById('btn-magic-writing');
        const magicPlanning = document.getElementById('btn-magic-planning');
        if (magicPlanning) magicPlanning.classList.remove('d-none');
        if (magicWriting) magicWriting.classList.add('d-none');
        
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
        setTimeout(renderGhostImages, 200);
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
        setTimeout(renderGhostImages, 200);
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
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.remove('d-none');
    
    // Update headers
    const titles = { 'scenario': 'Geografia / Cenários', 'character': 'Personagens / Elenco', 'lore': 'Lore / Conhecimento', 'object': 'Itens / Objetos' };
    const icons = { 'scenario': 'bi-geo-alt', 'character': 'bi-people', 'lore': 'bi-mortarboard', 'object': 'bi-gem' };
    document.getElementById('cards-grid-title').innerText = titles[type] || 'Fichas';
    document.getElementById('cards-grid-icon').className = `bi ${icons[type]} me-2 text-primary fs-4`;

    const response = await fetch(`/projects/${projectUuid}/cards?type=${type}`);
    currentCards = await response.json();
    renderCards(currentCards);
    updateRightPanelForCards(type, currentCards);
}

function updateRightPanelForCards(type, cards) {
    const titles = { 'scenario': 'Cenários', 'character': 'Personagens', 'lore': 'Lore', 'object': 'Itens' };
    
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
        const emptyIcons = { 'scenario': 'bi-map', 'character': 'bi-person-plus', 'lore': 'bi-mortarboard', 'object': 'bi-box-seam' };
        grid.innerHTML = `<div class="col-12 text-center py-5 text-body-secondary animate-fade-in"><i class="bi ${emptyIcons[type] || 'bi-plus-circle'} display-1 opacity-10 d-block mb-3"></i> Nenhuma ficha encontrada.</div>`;
        return;
    }

    cards.forEach((card, index) => {
        // Center Grid
        const col = document.createElement('div');
        col.id = `grid-card-${card.uuid}`;
        col.className = 'col animate-fade-in';
        col.style.animationDelay = `${index * 0.05}s`;
        
        const placeholders = { 'scenario': 'Mapa', 'character': 'Personagem', 'lore': 'Lore', 'object': 'Item' };
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
                    <span class="small text-body-secondary fs-10">${card.type.charAt(0).toUpperCase() + card.type.slice(1)}</span>
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

                        <!-- Mover Categoria -->
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-ghost-card btn-sm p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Mover para...">
                                <i class="bi bi-arrow-left-right"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark shadow border-0 backdrop-blur fs-8">
                                <li><h6 class="dropdown-header text-uppercase opacity-50 fs-9">Mover para:</h6></li>
                                ${card.type !== 'scenario' ? `<li><a class="dropdown-item py-1" href="#" onclick="moveCard('${card.uuid}', 'scenario')"><i class="bi bi-geo-alt me-2 text-primary"></i> Geografia</a></li>` : ''}
                                ${card.type !== 'character' ? `<li><a class="dropdown-item py-1" href="#" onclick="moveCard('${card.uuid}', 'character')"><i class="bi bi-people me-2 text-primary"></i> Personagens</a></li>` : ''}
                                ${card.type !== 'lore' ? `<li><a class="dropdown-item py-1" href="#" onclick="moveCard('${card.uuid}', 'lore')"><i class="bi bi-mortarboard me-2 text-primary"></i> Lore</a></li>` : ''}
                                ${card.type !== 'object' ? `<li><a class="dropdown-item py-1" href="#" onclick="moveCard('${card.uuid}', 'object')"><i class="bi bi-gem me-2 text-primary"></i> Objetos</a></li>` : ''}
                            </ul>
                        </div>
                        
                        <button class="btn btn-ghost-card btn-sm text-danger p-1" title="Excluir Ficha" data-bs-toggle="modal" data-bs-target="#cardDeleteModal" onclick="cardToDeleteUuid = '${card.uuid}'">
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

async function moveCard(cardUuid, newType) {
    if (!confirm(`Deseja mover esta ficha para a categoria ${newType}?`)) return;
    
    try {
        const response = await fetch(`/projects/${projectUuid}/cards/${cardUuid}/type`, {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            },
            body: JSON.stringify({ type: newType })
        });
        
        if (response.ok) {
            toast('Ficha movida com sucesso!', 'success');
            
            // Immediate UI removal for smoothness
            const cardEl = document.getElementById(`grid-card-${cardUuid}`);
            if (cardEl) cardEl.remove();
            
            const sidebarEl = document.getElementById(`sidebar-card-${cardUuid}`);
            if (sidebarEl) sidebarEl.remove();
            
            await loadCards(activeCardCategory); // Full refresh
        }
    } catch (error) {
        console.error('Erro ao mover ficha:', error);
        toast('Erro ao mover ficha.', 'error');
    }
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
    // Se não veio de um clique de substituição, limpa o range
    const isSwapping = document.querySelector('.ghost-image-widget.swapping');
    if (!isSwapping) {
        replacementRange = null;
    }
    
    currentGalleryMode = 'editor';
    document.getElementById('gallery-picker-search').value = '';
    
    // Refresh current gallery items
    const response = await fetch(`/projects/${projectUuid}/gallery`);
    currentGalleryItems = await response.json();
    
    renderGalleryPicker(currentGalleryItems);
    
    // Mostra o modal via gatilho HTML (mais seguro contra erros de escopo JS)
    document.getElementById('gallery-picker-trigger').click();
}

async function openGalleryPickerForProjectCover() {
    currentGalleryMode = 'project_cover';
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
        const displayName = filename.replace(/\.[^/.]+$/, "");
        insertImageInEditor(imageUuid, displayName);
        
        // Fecha o modal
        const modalEl = document.getElementById('galleryPickerModal');
        const closeBtn = modalEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.click();
        return;
    }

    if (currentGalleryMode === 'project_cover') {
        setProjectCover(imageUuid);
        // Fecha o modal
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
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    
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
    const textEl = document.getElementById('save-status-text');
    const iconEl = document.getElementById('save-status-icon');
    if (textEl) textEl.innerText = text;
    if (iconEl) {
        iconEl.className = `bi ${iconClass} me-1`;
    }
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
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    document.getElementById('dynamic-context-panel').classList.add('d-none');
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
    try {
        const response = await fetch(`/projects/${projectUuid}/gallery`);
        if (!response.ok) throw new Error('Falha ao carregar galeria');
        currentGalleryItems = await response.json();
        renderGallery(currentGalleryItems);
        updateRightPanelForGallery();
        renderGhostImages();
    } catch (error) {
        console.error('Erro na galeria:', error);
        const galleryItemsEl = document.getElementById('gallery-items');
        if (galleryItemsEl) {
            galleryItemsEl.innerHTML = '<div class="col-12 text-center text-muted p-5">Erro ao carregar galeria. Tente novamente.</div>';
        }
    }
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
                <span class="badge bg-primary gallery-badge">${extension}</span>
                <div class="ratio ratio-1x1 position-relative cursor-pointer" onclick="insertImageInEditor('${item.uuid}', '${displayName.replace(/'/g, "\\'")}')">
                    <img src="/projects/${projectUuid}/gallery/${item.uuid}/image/thumb" class="card-img-top object-fit-cover" alt="${displayName}">
                    <!-- Overlay de zoom/view -->
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50 opacity-0 transition-opacity text-white gallery-overlay">
                        <a href="/projects/${projectUuid}/gallery/${item.uuid}/image" target="_blank" class="text-white p-2" onclick="event.stopPropagation()">
                            <i class="bi bi-search fs-3"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-2 d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <span class="small text-truncate text-body-secondary fw-bold flex-grow-1 mw-100px" title="${item.name}">${displayName}</span>
                    <div class="d-flex gap-1">
                        <button class="btn btn-ghost-card btn-sm text-primary p-1" title="Renomear" data-bs-toggle="modal" data-bs-target="#galleryRenameModal" onclick="prepareRenameImage('${item.uuid}', '${displayName.replace(/'/g, "\\'")}', '${extension}')">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-ghost-card btn-sm text-danger p-1" title="Excluir" data-bs-toggle="modal" data-bs-target="#galleryDeleteModal" onclick="galleryItemToDeleteUuid = '${item.uuid}'">
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

function insertImageInEditor(uuid, displayName) {
    if (!easyMDE) return;
    const cm = easyMDE.codemirror;
    const doc = cm.getDoc();
    const cursor = doc.getCursor();
    
    // URL canônica da imagem no sistema
    const imageUrl = `/projects/${projectUuid}/gallery/${uuid}/image`;
    const tag = `![${displayName}](${imageUrl})`;
    
    if (replacementRange) {
        doc.replaceRange(tag, replacementRange.from, replacementRange.to);
        replacementRange = null;
        document.querySelectorAll('.ghost-image-widget').forEach(w => w.classList.remove('swapping'));
    } else {
        doc.replaceRange(tag, cursor);
    }
    
    cm.focus();
    
    // Forçar renderização do Ghost
    setTimeout(renderGhostImages, 100);
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
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    document.getElementById('dynamic-context-panel').classList.add('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');
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

async function addConnection(uuid, title, type, metadata = null) {
    if (!currentConnections.some(c => c.uuid === uuid)) {
        currentConnections.push({ uuid, title, type, metadata });
        renderConnections();
        saveConnections();
    }
    document.getElementById('connection-results').classList.add('d-none');
    document.getElementById('connection-search').value = '';
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
    
    container.innerHTML = currentConnections.map(c => {
        const reason = (c.metadata && c.metadata.reason) ? c.metadata.reason : null;
        return `
            <div class="d-flex align-items-start justify-content-between p-2 bg-secondary bg-opacity-10 rounded mb-2 border border-secondary border-opacity-10 hover-lift">
                <div class="d-flex flex-column gap-1 overflow-hidden">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge ${colors[c.type] || 'bg-secondary'} x-small fw-normal">${c.type}</span>
                        <span class="fw-bold small text-truncate" title="${c.title}">${c.title}</span>
                    </div>
                    ${reason ? `<div class="x-small text-body-secondary italic opacity-75 mt-1 lh-1-4">${reason}</div>` : ''}
                </div>
                <button class="btn btn-link btn-sm text-body-secondary p-0 ms-2 hover-danger" onclick="removeConnection('${c.uuid}')">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        `;
    }).join('');
}

async function saveConnections() {
    if (!activeItemUuid) return;
    
    await fetch(`/projects/${projectUuid}/cards/${activeItemUuid}/connections`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ 
            connections: currentConnections.map(c => ({ 
                uuid: c.uuid, 
                metadata: c.metadata 
            })) 
        })
    });
}

// --- Project Bible Logic ---

async function openBible() {
    activeItemUuid = null;
    activeCardCategory = null;
    
    showEmptyState(null); // Clear other views
    
    // Update Navigation UI
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    document.getElementById('nav-bible').classList.add('active');
    
    document.querySelectorAll('.sidebar-nav .nav-link').forEach(el => el.classList.remove('active'));
    document.getElementById('bible-container').classList.remove('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    document.getElementById('empty-state').classList.add('d-none');
    
    // Reset to Structure Mode
    setBibleMode('structure');

    loadBibleData();
}

function setBibleMode(mode) {
    const btnStructure = document.getElementById('btn-bible-structure');
    const btnCerebellum = document.getElementById('btn-bible-cerebellum');
    const structurePane = document.getElementById('bible-structure');
    const cerebellumPane = document.getElementById('bible-cerebellum');

    if (!btnStructure || !btnCerebellum || !structurePane || !cerebellumPane) return;

    if (mode === 'structure') {
        btnStructure.classList.add('active', 'btn-primary');
        btnStructure.classList.remove('btn-outline-secondary');
        btnCerebellum.classList.remove('active', 'btn-primary');
        btnCerebellum.classList.add('btn-outline-secondary');
        
        structurePane.classList.add('show', 'active');
        cerebellumPane.classList.remove('show', 'active');
    } else {
        btnCerebellum.classList.add('active', 'btn-primary');
        btnCerebellum.classList.remove('btn-outline-secondary');
        btnStructure.classList.remove('active', 'btn-primary');
        btnStructure.classList.add('btn-outline-secondary');
        
        cerebellumPane.classList.add('show', 'active');
        structurePane.classList.remove('show', 'active');
    }
}

async function loadBibleData() {
    try {
        const response = await fetch(`/projects/${projectUuid}/bible`);
        const data = await response.json();
        
        // Update Cerebellum (Legacy "summary" in bible)
        if (bibleEditor) bibleEditor.value(data.content || '');
        if (bibleSummaryEditor) bibleSummaryEditor.value(data.summary || '');
        
        // Render Manuscript Structure with summaries
        renderBibleAccordion(data.manuscript_tree);
        
    } catch (error) {
        console.error('Erro ao carregar bíblia:', error);
    }
}

function renderBibleAccordion(tree) {
    const container = document.getElementById('bible-manuscript-accordion');
    if (!container) return;
    
    if (!tree || tree.length === 0) {
        container.innerHTML = '<div class="text-center py-5 text-ghost-muted italic small">Manuscrito vazio. Escreva algo primeiro.</div>';
        return;
    }

    // Limpa e renderiza recursivamente, informando que estamos no nível raiz (parentType null)
    container.innerHTML = tree.map(node => renderBibleItem(node, null)).join('');
}

function renderBibleItem(item, parentType = null) {
    if (item.type === 'toc') return '';

    // Regra: Itens fora de uma seção não têm resumo, exceto as próprias seções
    const showSync = parentType === 'section' || item.type === 'section';

    if (item.type === 'section') {
        const childrenHtml = (item.children || []).map(child => renderBibleItem(child, 'section')).join('');
        const hasSummary = item.summary && item.summary.trim() !== '';
        
        return `
            <div class="bible-section-group mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-primary border-opacity-20 pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-folder2-open text-primary fs-5"></i>
                        <h5 class="fw-bold mb-0 text-primary small text-uppercase ls-wide">${item.title}</h5>
                        ${showSync ? `
                        <span role="button" class="btn btn-xs btn-outline-primary border-0 btn-sync-bible-item p-0 opacity-75 hover-opacity-100 ms-1" data-uuid="${item.uuid}" onclick="event.stopPropagation(); syncSectionToBible('${item.uuid}')" title="Sincronizar esta seção">
                            <i class="bi bi-journal-arrow-up"></i>
                        </span>` : ''}
                    </div>
                </div>
                ${showSync && hasSummary ? `<div class="mb-3 p-2 bg-primary bg-opacity-5 rounded border-start border-primary border-3 small italic text-body-secondary mx-2">${item.summary}</div>` : ''}
                <div class="ps-2">
                    ${childrenHtml || '<div class="text-ghost-muted x-small italic ps-4 pb-3">Seção vazia.</div>'}
                </div>
            </div>
        `;
    }

    if (item.type === 'chapter') {
        const scenesHtml = (item.children || []).map(scene => renderSceneHtml(scene, 'chapter')).join('');
        return renderChapterAccordionHtml(item, scenesHtml, showSync);
    }

    if (item.type === 'scene') {
        return renderSceneHtml(item, parentType, showSync);
    }

    return '';
}

function renderChapterAccordionHtml(chapter, childrenHtml, showSync = true) {
    const hasChapterSummary = chapter.summary && chapter.summary.trim() !== '';
    
    return `
        <div class="accordion-item shadow-sm mb-3 border rounded overflow-hidden">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-bible-${chapter.uuid}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-journal-bookmark text-info"></i>
                        <span class="fw-bold text-truncate" style="max-width: 250px;">${chapter.title}</span>
                        ${showSync ? `
                        <span role="button" class="btn btn-xs btn-outline-primary border-0 btn-sync-bible-item p-0 opacity-75 hover-opacity-100 ms-1" data-uuid="${chapter.uuid}" onclick="event.stopPropagation(); syncSectionToBible('${chapter.uuid}')" title="Sincronizar este capítulo">
                            <i class="bi bi-journal-arrow-up"></i>
                        </span>` : ''}
                    </div>
                    ${chapter.children && chapter.children.length > 0 ? `<span class="badge bg-secondary bg-opacity-10 text-body-secondary ms-auto me-3 fw-normal small">${chapter.children.length} cenas</span>` : ''}
                </button>
            </h2>
            <div id="collapse-bible-${chapter.uuid}" class="accordion-collapse collapse" data-bs-parent="#bible-manuscript-accordion">
                <div class="accordion-body bg-body">
                    ${showSync && hasChapterSummary ? `<div class="mb-4 p-3 bg-info bg-opacity-5 rounded border-start border-info border-4 small">${chapter.summary}</div>` : ''}
                    <div class="scenes-list">
                        ${childrenHtml || '<div class="text-ghost-muted x-small italic ps-4">Sem cenas neste capítulo.</div>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderSceneHtml(scene, parentType, showSync = true) {
    const hasSummary = scene.summary && scene.summary.trim() !== '';
    return `
        <div class="mb-3 border-bottom border-secondary border-opacity-10 pb-3 last-child-border-0">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-text-paragraph text-ghost-muted small"></i>
                <span class="fw-bold small">${scene.title}</span>
                ${showSync ? `
                <span role="button" class="btn btn-xs btn-outline-primary border-0 btn-sync-bible-item p-0 opacity-75 hover-opacity-100" data-uuid="${scene.uuid}" onclick="event.stopPropagation(); syncSectionToBible('${scene.uuid}')" title="Sincronizar esta cena">
                    <i class="bi bi-journal-arrow-up"></i>
                </span>` : ''}
            </div>
            ${showSync ? `
            <div class="summary-content ps-4 ${hasSummary ? '' : 'summary-empty small'}">
                ${hasSummary ? scene.summary : 'Sem resumo sincronizado.'}
            </div>` : '<div class="ps-4 text-ghost-muted small italic">Item administrativo (sem resumo).</div>'}
        </div>
    `;
}

async function syncSectionToBible(uuid = null) {
    const targetUuid = uuid || activeItemUuid;
    if (!targetUuid) return;
    
    // Identificar qual botão foi clicado para feedback visual
    let btn, icon;
    if (uuid) {
        btn = document.querySelector(`.btn-sync-bible-item[data-uuid="${uuid}"]`);
    } else {
        btn = document.getElementById('btn-sync-to-bible');
    }
    
    if (btn) {
        icon = btn.querySelector('i');
        icon.className = 'spinner-border spinner-border-sm';
        btn.classList.add('disabled');
    }
    
    try {
        const response = await fetch(`/projects/${projectUuid}/manuscript/${targetUuid}/summary`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            showToast('Sucesso', 'Resumo gerado e sincronizado!', 'success');
            // Se estivermos na tela da bíblia, recarregar apenas os dados para atualizar o accordion (sem resetar modo)
            if (!document.getElementById('bible-container').classList.contains('d-none')) {
                loadBibleData();
            }
        } else {
            showToast('Aviso', data.error || 'Não foi possível gerar o resumo.', 'warning');
        }
    } catch (error) {
        console.error('Erro ao sincronizar:', error);
        showToast('Erro', 'Falha na comunicação com o servidor.', 'danger');
    } finally {
        if (btn && icon) {
            icon.className = 'bi bi-journal-arrow-up';
            btn.classList.remove('disabled');
        }
    }
}

async function syncCerebellum() {
    const btn = document.getElementById('btn-sync-cerebellum');
    const icon = btn.querySelector('i');
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sincronizando...';
    btn.classList.add('disabled');
    
    try {
        const response = await fetch(`/projects/${projectUuid}/bible/sync-cerebellum`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            bibleSummaryEditor.value(data.summary);
            showToast('Sucesso', 'Cerebelo atualizado com base nos resumos individuais!', 'success');
        } else {
            showToast('Erro', data.error || 'Erro ao sincronizar cerebelo.', 'danger');
        }
    } catch (error) {
        console.error('Erro ao sincronizar cerebelo:', error);
        showToast('Erro', 'Falha na conexão.', 'danger');
    } finally {
        btn.innerHTML = originalText;
        btn.classList.remove('disabled');
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

async function syncBibleWithAI() {
    const btn = document.querySelector('[onclick="syncBibleWithAI()"]');
    const originalHtml = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sincronizando...';
        
        const response = await fetch(`/projects/${projectUuid}/bible/sync`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const data = await response.json();
        
        if (data.error) {
            alert(data.error);
        } else {
            bibleSummaryEditor.value(data.summary);
            showBibleSaveStatus('Sincronizado', 'bi-stars text-info');
        }
    } catch (error) {
        console.error('Erro na sincronização:', error);
        alert('Erro ao conectar com a IA.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

function showBibleSaveStatus(text, iconClass) {
    const status = document.getElementById('bible-save-status');
    if (status) {
        status.innerHTML = `<i class="bi ${iconClass} me-1"></i> ${text}`;
    }
}

// --- Advanced Export Logic ---

async function openExport() {
    activeItemUuid = null;
    activeCardCategory = null;
    activeItemType = 'export';

    // UI state
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.remove('d-none');
    document.getElementById('backup-container').classList.add('d-none');
    document.getElementById('dynamic-context-panel').classList.add('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');

    // Highlight sidebar
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    if (document.getElementById('nav-export')) {
        document.getElementById('nav-export').classList.add('active');
    }

    // Load current state
    try {
        const response = await fetch(`/projects/${projectUuid}/export-state`);
        const state = await response.json();
        renderExportResults(state);
    } catch (error) {
        console.error('Erro ao carregar estado de exportação:', error);
    }
}

function openBackup() {
    activeItemUuid = null;
    activeCardCategory = null;
    activeItemType = 'backup';

    // UI state
    document.getElementById('empty-state').classList.add('d-none');
    document.getElementById('editor-container').classList.add('d-none');
    document.getElementById('cards-grid-container').classList.add('d-none');
    document.getElementById('gallery-container').classList.add('d-none');
    document.getElementById('graph-container').classList.add('d-none');
    document.getElementById('bible-container').classList.add('d-none');
    document.getElementById('export-container').classList.add('d-none');
    document.getElementById('backup-container').classList.remove('d-none');
    document.getElementById('dynamic-context-panel').classList.add('d-none');
    document.getElementById('markdown-tips').classList.add('d-none');

    // Highlight sidebar
    document.querySelectorAll('.nav-world').forEach(el => el.classList.remove('active'));
    if (document.getElementById('nav-backup')) {
        document.getElementById('nav-backup').classList.add('active');
    }

    loadProjectSnapshots();
}

function loadProjectSnapshots() {
    const tableBody = document.getElementById('snapshots-table-body');
    if (!tableBody) return;
    
    tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-ghost-muted italic">Carregando snapshots...</td></tr>';

    fetch(`/projects/${projectUuid}/snapshots`)
        .then(response => response.json())
        .then(snapshots => {
            if (!snapshots || snapshots.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-ghost-muted">Nenhum snapshot encontrado.</td></tr>';
                return;
            }
            
            tableBody.innerHTML = snapshots.map(s => `
                <tr>
                    <td class="ps-3 py-2 align-middle">
                        <div class="fw-bold">${s.date}</div>
                        <div class="x-small text-body-secondary">${s.name}</div>
                    </td>
                    <td class="py-2 align-middle text-body-secondary">${s.size}</td>
                    <td class="py-2 align-middle text-end pe-3">
                        <button class="btn btn-outline-danger btn-xs rounded-pill px-3" onclick="rollbackProject('${s.name}')">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Rollback
                        </button>
                    </td>
                </tr>
            `).join('');
        })
        .catch(err => {
            console.error('Error loading snapshots:', err);
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-danger">Erro ao carregar snapshots.</td></tr>';
        });
}

function createProjectSnapshot() {
    showToast('Iniciando snapshot do projeto...', 'info');
    
    fetch(`/projects/${projectUuid}/snapshots`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Snapshot criado com sucesso!', 'success');
            loadProjectSnapshots();
        } else {
            showToast('Falha ao criar snapshot: ' + (data.message || 'Erro desconhecido'), 'danger');
        }
    })
    .catch(err => {
        showToast('Erro de conexão ao criar snapshot.', 'danger');
    });
}

function rollbackProject(snapshotName) {
    if (!confirm(`Tem certeza que deseja restaurar o snapshot "${snapshotName}"? TODO o progresso atual desde este snapshot será perdido. O banco de dados e arquivos serão substituídos.`)) {
        return;
    }
    
    showToast('Restaurando snapshot... O sistema irá recarregar.', 'warning');
    
    fetch(`/projects/${projectUuid}/rollback`, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
        },
        body: JSON.stringify({ snapshot: snapshotName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            showToast(data.message || 'Falha no rollback.', 'danger');
        }
    })
    .catch(err => {
        showToast('Erro de conexão ao restaurar snapshot.', 'danger');
    });
}

async function setProjectCover(imageUuid) {
    try {
        const response = await fetch(`/projects/${projectUuid}/cover`, {
            method: 'PATCH',
            headers: { 
                'Content-Type': 'application/json', 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
            },
            body: JSON.stringify({ image_uuid: imageUuid })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update UI Cover
            activeProjectCoverUuid = imageUuid;
            
            const previewContainer = document.getElementById('project-cover-preview');
            if (previewContainer) {
                previewContainer.innerHTML = `<img src="${data.cover_url}" class="object-fit-cover w-100 h-100" id="project-cover-img">`;
            }
            
            console.log('Capa do projeto atualizada com sucesso.');
        } else {
            alert('Falha ao atualizar capa: ' + (data.message || 'Erro desconhecido'));
        }
    } catch (error) {
        console.error('Erro ao definir capa:', error);
        alert('Erro ao definir capa do projeto.');
    }
}

async function saveExportMetadata() {
    const payload = {
        name: document.getElementById('meta-title').value,
        author: document.getElementById('meta-author').value,
        isbn: document.getElementById('meta-isbn').value,
        publisher: document.getElementById('meta-publisher').value,
        publication_date: document.getElementById('meta-pubdate').value,
        copyright_info: document.getElementById('meta-copyright').value,
    };

    try {
        await fetch(`/projects/${projectUuid}/metadata`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify(payload)
        });
        
        // Update Project Title in all UI locations
        const newTitle = payload.name;
        
        // 1. Update the Main Header Title (if in Dashboard/Project Root)
        const headerTitle = document.getElementById('current-item-title');
        if (headerTitle && !activeItemUuid) {
            headerTitle.innerText = newTitle;
        }

        // 2. Update Browser Tab Title
        document.title = `${newTitle} - Workspace`;

        // 3. Update the Project Card Title if we are on the project list but that's a different page.
        // For the current page, we update any title-related elements.

    } catch(e) {
        console.error('Falha ao salvar metadados', e);
    }
}

async function runExportBatch() {
    await saveExportMetadata();

    const formats = [];
    if (document.getElementById('export-epub').checked) formats.push('epub');
    if (document.getElementById('export-pdf').checked) formats.push('pdf');
    if (document.getElementById('export-html').checked) formats.push('html');
    if (document.getElementById('export-markdown').checked) formats.push('markdown');

    if (formats.length === 0) {
        alert('Selecione ao menos um formato para exportar.');
        return;
    }

    const btn = document.getElementById('btn-run-export');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processando Exportação...';

    // Clear previous status with a spinner
    formats.forEach(f => {
        document.getElementById(`status-${f}`).innerHTML = '<div class="p-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>';
    });

    try {
        const response = await fetch(`/projects/${projectUuid}/export-process`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ formats })
        });
        
        if (!response.ok) throw new Error('Export failed');
        
        // Refresh state
        const stateResponse = await fetch(`/projects/${projectUuid}/export-state`);
        const state = await stateResponse.json();
        renderExportResults(state);

    } catch (error) {
        console.error('Erro no processamento:', error);
        alert('Ocorreu um erro ao gerar os arquivos.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    }
}

function renderExportResults(state) {
    const configs = {
        epub: { label: 'Download ePub', icon: 'bi-download', class: 'btn-outline-primary', target: '' },
        pdf: { label: 'Download PDF', icon: 'bi-file-earmark-pdf', class: 'btn-outline-danger', target: '' },
        html: { label: 'Navegar Reader', icon: 'bi-eye', class: 'btn-outline-success', target: '_blank' },
        markdown: { label: 'Baixar Fontes (.md)', icon: 'bi-file-zip', class: 'btn-outline-info', target: '' }
    };

    ['epub', 'pdf', 'html', 'markdown'].forEach(f => {
        const container = document.getElementById(`status-${f}`);
        if (state[f]) {
            const cfg = configs[f];
            const ts = new Date(state[f].timestamp * 1000).toLocaleString();
            
            if (f === 'html') {
                container.innerHTML = `
                    <div class="d-flex flex-column gap-1">
                        <a href="/projects/${projectUuid}/export/preview-html" target="_blank" class="btn btn-outline-success btn-sm w-100 rounded-pill">
                            <i class="bi bi-eye me-1"></i> Navegar Reader
                        </a>
                        <a href="/projects/${projectUuid}/export/zip" download class="btn btn-link btn-sm text-decoration-none x-small p-0 opacity-75">
                            <i class="bi bi-file-zip me-1"></i> Baixar Pacote Web (ZIP)
                        </a>
                    </div>
                    <div class="x-small text-body-secondary opacity-50 mt-1 fs-9">Gerado em: ${ts}</div>
                `;
            } else {
                container.innerHTML = `
                    <a href="${state[f].url}" class="btn ${cfg.class} btn-sm w-100 rounded-pill mb-1" ${cfg.target ? 'target="'+cfg.target+'"' : 'download'}>
                        <i class="bi ${cfg.icon} me-1"></i> ${cfg.label}
                    </a>
                    <div class="x-small text-body-secondary opacity-50 fs-9">Gerado em: ${ts}</div>
                `;
            }
        } else {
            container.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 w-100 py-2">Não gerado</span>';
        }
    });
}

// --- AI Magic Logic ---

async function suggestAiCard() {
    try {
        const btn = document.querySelector('[onclick="suggestAiCard()"]');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        const response = await fetch(`/projects/${projectUuid}/ai/card`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await response.json();
        
        if (data.success) {
            // Create it directly
            const title = data.suggestion.title;
            const content = data.suggestion.content;
            const type = data.suggestion.type;
            
            const createRes = await fetch(`/projects/${projectUuid}/cards`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ title, content, type })
            });
            await createRes.json();
            loadCards(activeCardCategory || type); // refresh grid
        } else {
            alert(data.error || 'Erro ao sugerir ficha');
        }
    } catch (e) {
        console.error(e);
        alert('Erro na conexão com IA');
    } finally {
        const btn = document.querySelector('[onclick="suggestAiCard()"]');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-stars"></i>';
        }
    }
}

async function generateAiPlanning() {
    if (!activeItemUuid) return;
    const btn = document.getElementById('btn-magic-planning');
    const originalHtml = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        const title = document.getElementById('current-item-title').innerText;
        const response = await fetch(`/projects/${projectUuid}/ai/planning`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ title })
        });
        const data = await response.json();
        
        if (data.success) {
            easyMDE.value(data.planning);
            saveManuscriptContent(); 
        } else {
            alert(data.error);
        }
    } catch (e) {
        alert('Erro ao gerar planejamento');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-stars"></i>';
    }
}

async function writeAiScene() {
    if (!activeItemUuid) return;
    const btn = document.getElementById('btn-magic-writing');
    const originalHtml = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        const title = document.getElementById('current-item-title').innerText;
        
        const planRes = await fetch(`/projects/${projectUuid}/manuscript/${activeItemUuid}/planning`);
        const planData = await planRes.json();
        
        const response = await fetch(`/projects/${projectUuid}/ai/scene`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ title, planning: planData.content || '' })
        });
        const data = await response.json();
        
        if (data.success) {
            if (!easyMDE.value().trim() || easyMDE.value().startsWith('# Planejamento')) {
                easyMDE.value(data.content);
            } else {
                easyMDE.value(easyMDE.value() + "\n\n" + data.content);
            }
            saveManuscriptContent();
        } else {
            alert(data.error);
        }
    } catch (e) {
        alert('Erro ao escrever cena');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-magic"></i>';
    }
}

async function suggestAIConnections() {
    if (!activeItemUuid) return;
    const loading = document.getElementById('ai-suggestion-loading');
    loading.classList.remove('d-none');
    
    try {
        const response = await fetch(`/projects/${projectUuid}/cards/${activeItemUuid}/suggest`);
        const suggestions = await response.json();
        
        if (suggestions.error) {
            alert(suggestions.error);
            return;
        }
        
        renderAISuggestions(suggestions);
    } catch (error) {
        console.error('Erro ao buscar sugestões:', error);
        alert('Falha ao obter sugestões da IA.');
    } finally {
        loading.classList.add('d-none');
    }
}

function renderAISuggestions(suggestions) {
    const list = document.getElementById('ai-suggestions-list');
    const modalEl = document.getElementById('aiConnectionsModal');
    const modal = new bootstrap.Modal(modalEl);
    
    if (!suggestions || suggestions.length === 0) {
        list.innerHTML = '<div class="text-center py-3 text-body-secondary italic small">O Fantasma não encontrou novas conexões baseadas nas descrições atuais.</div>';
    } else {
        list.innerHTML = `
            <div id="ai-suggestions-container">
                ${suggestions.map((s, idx) => `
                    <div class="p-3 bg-secondary bg-opacity-10 rounded mb-3 border border-secondary border-opacity-10 hover-lift d-flex gap-3">
                        <div class="pt-1">
                            <input type="checkbox" class="form-check-input ai-suggestion-check" 
                                   data-uuid="${s.uuid}" 
                                   data-title="${s.title.replace(/'/g, "\\'")}" 
                                   data-type="${s.type}" 
                                   data-reason="${s.reason.replace(/'/g, "\\'")}"
                                   id="sug-${idx}">
                        </div>
                        <label class="flex-grow-1 cp" for="sug-${idx}">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="d-flex align-items-center">
                                     <i class="bi bi-link-45deg me-2 text-primary"></i>
                                     <h6 class="mb-0 fw-bold small text-uppercase ls-wide">${s.title}</h6>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary x-small">${s.type}</span>
                            </div>
                            <p class="x-small text-body-secondary mb-0 italic lh-1-4">"${s.reason}"</p>
                        </label>
                    </div>
                `).join('')}
                <div class="mt-4 pt-3 border-top border-secondary border-opacity-10 d-flex justify-content-end">
                    <button class="btn btn-primary rounded-pill px-4 fw-bold" onclick="applySelectedAISuggestions()">Vincular Selecionados</button>
                </div>
            </div>
        `;
    }
    modal.show();
}

async function applySelectedAISuggestions() {
    const checks = document.querySelectorAll('.ai-suggestion-check:checked');
    if (checks.length === 0) return;
    
    checks.forEach(check => {
        const uuid = check.dataset.uuid;
        const title = check.dataset.title;
        const type = check.dataset.type;
        const reason = check.dataset.reason;
        
        // Evita duplicados
        if (!currentConnections.some(c => c.uuid === uuid)) {
            currentConnections.push({ uuid, title, type, metadata: { reason } });
        }
    });
    
    renderConnections();
    await saveConnections();
    
    // Fecha o modal
    const modalEl = document.getElementById('aiConnectionsModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function applyAISuggestion(uuid, title, type) {
    // Mantido por compatibilidade se necessário, mas o novo fluxo usa checkboxes
    if (!currentConnections.some(c => c.uuid === uuid)) {
        currentConnections.push({ uuid, title, type });
        renderConnections();
        saveConnections();
    }
    
    const modalEl = document.getElementById('aiConnectionsModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

// --- Notification Toast ---
function showToast(titleOrMessage, messageOrType = 'primary', type = 'primary') {
    if (arguments.length === 3) {
        // Formato: title, message, type
        toast(`<strong>${titleOrMessage}</strong><br>${messageOrType}`, type);
    } else {
        // Formato: message, type
        toast(titleOrMessage, messageOrType);
    }
}

function toast(message, type = 'primary') {
    // Map 'error' to 'danger' for Bootstrap compatibility
    if (type === 'error') type = 'danger';
    
    const container = document.getElementById('toast-container') || createToastContainer();
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0 show mb-2 animate-fade-in`;
    toastEl.role = 'alert';
    toastEl.style.minWidth = '250px';
    toastEl.innerHTML = `
        <div class="d-flex p-3 backdrop-blur bg-dark bg-opacity-25 rounded shadow-lg border border-white border-opacity-10">
            <div class="toast-body fw-bold d-flex align-items-center w-100">
                <i class="bi ${type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-info-circle-fill text-info'} me-3 fs-5"></i>
                <div class="flex-grow-1">${message}</div>
            </div>
        </div>
    `;
    container.appendChild(toastEl);
    setTimeout(() => {
        toastEl.classList.remove('show');
        setTimeout(() => toastEl.remove(), 500);
    }, 4000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-4';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// --- Backup Reminder Logic ---
function checkBackupReminder() {
    const lastBackup = localStorage.getItem('last_backup_at');
    const now = new Date().getTime();
    const sevenDays = 7 * 24 * 60 * 60 * 1000;

    if (!lastBackup || (now - parseInt(lastBackup)) > sevenDays) {
        const navBackup = document.getElementById('nav-backup');
        if (navBackup) navBackup.classList.add('pulse-warning');
        
        // Se a aba de backup estiver aberta, mostra o alerta interno também
        const backupContainer = document.getElementById('backup-container');
        if (backupContainer && !backupContainer.classList.contains('d-none')) {
            const backupAlert = document.getElementById('backup-alert');
            if (backupAlert) {
                backupAlert.classList.remove('d-none');
                backupAlert.classList.add('d-flex');
            }
        }
    }
}

function dismissBackupAlert() {
    const alert = document.getElementById('backup-alert');
    if (alert) {
        alert.classList.add('d-none');
        alert.classList.remove('d-flex');
    }
    // Parar o pulsar da navbar ao fechar o alerta
    const navBackup = document.getElementById('nav-backup');
    if (navBackup) navBackup.classList.remove('pulse-warning');
}

function recordBackup() {
    localStorage.setItem('last_backup_at', new Date().getTime().toString());
    const navBackup = document.getElementById('nav-backup');
    if (navBackup) navBackup.classList.remove('pulse-warning');
    dismissBackupAlert();
}

// Chamar verificação ao carregar
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(checkBackupReminder, 2000); // Delay suave
    
    // Vincular clique de download ao recordBackup
    // Usamos delegação ou procuramos após carregar
    document.body.addEventListener('click', (e) => {
        if (e.target.closest('a[href*="/export/backup"]')) {
            recordBackup();
        }
    });
});

// Extende a função openBackup existente para mostrar o alerta se necessário
const originalOpenBackup = window.openBackup;
window.openBackup = function() {
    if (typeof originalOpenBackup === 'function') originalOpenBackup();
    
    const lastBackup = localStorage.getItem('last_backup_at');
    const now = new Date().getTime();
    const sevenDays = 7 * 24 * 60 * 60 * 1000;

    if (!lastBackup || (now - parseInt(lastBackup)) > sevenDays) {
        const alert = document.getElementById('backup-alert');
        if (alert) {
            alert.classList.remove('d-none');
            alert.classList.add('d-flex');
        }
    }
};
</script>


@endsection
