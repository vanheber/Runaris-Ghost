@extends('layouts.app')

@section('title', 'Meus Projetos - Runaris Ghost')

@section('content')
<div class="container mt-3">
    <div class="row">
        <!-- Main Content: Projects Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h1 class="fw-bold mb-2">Suas Histórias</h1>
                    <p class="text-body-secondary mb-0">Continue sua jornada literária ou comece uma nova aventura.</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-10 px-3 py-2">
                        <i class="bi bi-collection me-1"></i> Total: {{ $projects->count() }}
                    </span>
                </div>
            </div>

    @if($projects->isEmpty())
    <div class="card bg-body-tertiary border-0 shadow-sm p-5 text-center my-5 animate__animated animate__fadeIn">
        <i class="bi bi-journal-plus fs-1 text-primary mb-3 d-block"></i>
        <h3 class="fw-bold">Nenhum projeto encontrado</h3>
        <p class="text-body-secondary mb-4">Você ainda não iniciou sua primeira obra. Que tal começarmos hoje?</p>
        <button class="btn btn-primary btn-lg rounded-pill" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="bi bi-pencil-square me-2"></i> Criar Meu Primeiro Projeto
        </button>
    </div>
    @else
    <style>
        .project-card-link { text-decoration: none; color: inherit; }
        .project-card { 
            position: relative; 
            border-radius: 12px; 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .project-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important;
        }
    </style>
    <div class="row row-cols-1 row-cols-lg-2 g-4">
        @foreach($projects as $project)
        <div class="col">
            <div class="card bg-body-tertiary border-0 shadow-sm h-100 project-card hover-lift overflow-hidden">
                <div class="row g-0 h-100">
                    <!-- Thumbnail Side -->
                    <div class="col-5 col-sm-4 position-relative bg-dark bg-opacity-5 d-flex align-items-center justify-content-center border-end border-secondary border-opacity-10 overflow-hidden">
                        @if($project->cover_image_uuid)
                            <img src="{{ url('/projects/'.$project->uuid.'/gallery/'.$project->cover_image_uuid.'/image/thumb') }}" class="w-100 h-100 object-fit-contain" alt="{{ $project->name }}">
                        @else
                            <div class="p-2 text-center z-1 w-100 h-100 d-flex flex-column align-items-center justify-content-center cover-placeholder w-100 ambient-bg">
                                <i class="bi bi-file-earmark-image fs-1 opacity-50 mb-2"></i>
                                <span class="small text-body-secondary fw-bold d-block mb-3 fs-9">1600x2560</span>
                                <div class="d-flex gap-2 z-2" onclick="event.preventDefault(); event.stopPropagation();">
                                    <button class="btn btn-ghost-card btn-icon-round" title="Selecionar da Galeria" onclick="openGalleryForCover('{{ $project->uuid }}')">
                                        <i class="bi bi-images"></i>
                                    </button>
                                    <button class="btn btn-ghost-card btn-icon-round" title="Upload Nova Capa" onclick="openCoverModal('{{ $project->uuid }}')">
                                        <i class="bi bi-upload fs-5"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Content Side -->
                    <div class="col-7 col-sm-8">
                        <a href="{{ url('/projects/'.$project->uuid) }}" class="project-card-link text-decoration-none d-block h-100">
                            <div class="p-4 d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold text-project-title mb-0" style="font-size: 1.15rem; line-height: 1.3;">{{ $project->name }}</h5>
                                    <button class="btn btn-link text-body-secondary opacity-50 hover-danger p-0" title="Excluir Projeto" onclick="event.preventDefault(); event.stopPropagation(); openDeleteProjectModal('{{ $project->uuid }}', '{{ addslashes($project->name) }}')">
                                        <i class="bi bi-trash fs-5"></i>
                                    </button>
                                </div>
                                <span class="badge bg-secondary bg-opacity-10 text-body-secondary border border-secondary border-opacity-25 align-self-start mb-3 fs-11">
                                    <i class="bi bi-clock me-1"></i> Acesso: {{ $project->last_opened_at ? $project->last_opened_at->diffForHumans(null, true) : 'Never' }}
                                </span>
                                
                                <p class="text-body-secondary small line-clamp-3 mb-4">
                                    {{ $project->description ?? 'Sem sinopse definida. Clique aqui para começar a mapear a sua obra literária e descrever o enredo.' }}
                                </p>
                                
                                <div class="mt-auto pt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary small fw-bold font-monospace">UUID: {{ substr($project->uuid, 0, 8) }}</span>
                                    <span class="text-primary fw-bold small">Abrir <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- News Sidebar -->
    <div class="col-lg-3 mt-4 mt-lg-0 news-sidebar">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0 text-uppercase ls-wide fs-7 text-primary">
                <i class="bi bi-lightning-charge-fill me-1"></i> O Pulso do Autor
            </h5>
        </div>
        
        <div id="bento-news-feed" class="bento-news-container">
            <!-- Skeleton Loading -->
            <div class="bento-item large bento-skeleton"></div>
            <div class="bento-item small bento-skeleton"></div>
            <div class="bento-item small bento-skeleton"></div>
        </div>
    </div>
</div> <!-- row -->

    <!-- Cover Image Modal -->
    <div class="modal fade" id="coverModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-body-tertiary border-0 shadow-lg">
                <div class="modal-header border-secondary border-opacity-10">
                    <h5 class="modal-title fw-bold"><i class="bi bi-image"></i> Configurar Capa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <input type="file" id="coverUploadInput" class="d-none" accept="image/*" onchange="handleCoverUpload(this)">
                    <div id="coverLoading" class="d-none my-4"><div class="spinner-border text-primary"></div><p class="mt-3 text-body-secondary small fw-bold">Processando imagem em alta qualidade (1600x2560)...</p></div>
                    <div id="coverOptions">
                        <p class="text-body-secondary mb-4">O formato nativo da Amazon KDP possui largura de 1600px e altura de 2560px. Qualquer imagem enviada aqui será automaticamente redimensionada a esses limites e salva em formato JPG para otimizar os bytes da sua exportação EPUB e PDF.</p>
                        <button class="btn btn-primary rounded-pill w-100 mb-2 py-2" onclick="document.getElementById('coverUploadInput').click()">
                            <i class="bi bi-cloud-arrow-up me-2"></i> Fazer Upload de Imagem
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover Gallery Picker Modal -->
    <div class="modal fade" id="coverGalleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content bg-body-tertiary border-0 shadow-lg">
                <div class="modal-header border-secondary border-opacity-10">
                    <h5 class="modal-title fw-bold"><i class="bi bi-images"></i> Escolher da Galeria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row" id="coverGalleryGrid">
                        <!-- Carregado dinamicamente -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Project Modal -->
    <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-body-tertiary border-0 shadow-lg">
                <div class="modal-header border-secondary border-opacity-10">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-triangle"></i> Destruir Projeto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-body-secondary mb-4">
                        Esta ação é <strong>permanente</strong>. Todos os manuscritos, fichas, imagens e bíblia do mundo serão deletados para sempre.
                    </p>
                    
                    <div class="mb-3">
                        <label class="form-label small text-uppercase fw-bold opacity-75">Confirme digitando o título:</label>
                        <p class="mb-2 fw-bold text-primary" id="deleteProjectNameDisplay"></p>
                        <input type="text" class="form-control" id="deleteProjectInput" placeholder="Digite o nome aqui..." oninput="validateProjectDeleteName()">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-link text-body-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger px-4 disabled" onclick="confirmDestroyProject()">Destruir Permanentemente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Triggers for Modals (to avoid JS library initialization race conditions) -->
    <button id="coverModalTrigger" class="d-none" data-bs-toggle="modal" data-bs-target="#coverModal"></button>
    <button id="coverGalleryTrigger" class="d-none" data-bs-toggle="modal" data-bs-target="#coverGalleryModal"></button>
    <button id="deleteProjectTrigger" class="d-none" data-bs-toggle="modal" data-bs-target="#deleteProjectModal"></button>

    <script>
    let currentProjectUuid = null;

    function openCoverModal(uuid) {
        currentProjectUuid = uuid;
        document.getElementById('coverModalTrigger').click();
    }

    async function openGalleryForCover(uuid) {
        currentProjectUuid = uuid;
        const grid = document.getElementById('coverGalleryGrid');
        grid.innerHTML = '<div class="text-center py-4 w-100"><div class="spinner-border text-primary"></div></div>';
        
        document.getElementById('coverGalleryTrigger').click();
        
        try {
            const response = await fetch(`/projects/${uuid}/gallery`);
            const items = await response.json();
            
            if (items.length === 0) {
                grid.innerHTML = '<div class="text-center text-body-secondary py-4 w-100">Galeria vazia. Faça o upload pelo outro botão ou dentro do projeto.</div>';
                return;
            }
            
            grid.innerHTML = items.map(item => `
                <div class="col-4 mb-3">
                    <div class="ratio ratio-1x1 cp overflow-hidden rounded border border-primary border-opacity-25 shadow-sm" onclick="selectCoverFromGallery('${item.uuid}')">
                        <img src="/projects/${uuid}/gallery/${item.uuid}/image/thumb" class="object-fit-cover w-100 h-100 d-block transition-all cp" onmouseover="this.classList.add('scale-110')" onmouseout="this.classList.remove('scale-110')">
                    </div>
                </div>
            `).join('');
        } catch(e) {
            grid.innerHTML = '<div class="text-danger text-center py-4 w-100">Erro ao carregar a galeria de imagens.</div>';
        }
    }

    async function selectCoverFromGallery(imageUuid) {
        try {
            await fetch(`/projects/${currentProjectUuid}/cover`, {
                method: 'PATCH',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ image_uuid: imageUuid })
            });
            window.location.reload();
        } catch(e) {
            alert('Falha ao definir capa.');
        }
    }

    async function handleCoverUpload(input) {
        if (!input.files || !input.files[0]) return;
        
        document.getElementById('coverOptions').classList.add('d-none');
        document.getElementById('coverLoading').classList.remove('d-none');
        
        const formData = new FormData();
        formData.append('image', input.files[0]);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        try {
            // Upload to project gallery automatically
            const galResponse = await fetch(`/projects/${currentProjectUuid}/gallery`, {
                method: 'POST',
                body: formData
            });
            const galleryItem = await galResponse.json();
            
            if (galleryItem && galleryItem.uuid) {
                // Link gallery UUID to project Cover UUID
                await fetch(`/projects/${currentProjectUuid}/cover`, {
                    method: 'PATCH',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ image_uuid: galleryItem.uuid })
                });
                window.location.reload();
            }
        } catch (e) {
            alert('Falha interna ao processar ou vincular a capa do projeto.');
            document.getElementById('coverOptions').classList.remove('d-none');
            document.getElementById('coverLoading').classList.add('d-none');
        }
    }

    // --- Destructive Deletion Logic ---
    let projectToDeleteName = '';

    function openDeleteProjectModal(uuid, name) {
        currentProjectUuid = uuid;
        projectToDeleteName = name;
        document.getElementById('deleteProjectNameDisplay').innerText = name;
        document.getElementById('deleteProjectInput').value = '';
        document.getElementById('confirmDeleteBtn').classList.add('disabled');
        document.getElementById('deleteProjectTrigger').click();
    }

    function validateProjectDeleteName() {
        const input = document.getElementById('deleteProjectInput').value;
        const btn = document.getElementById('confirmDeleteBtn');
        if (input === projectToDeleteName) {
            btn.classList.remove('disabled');
        } else {
            btn.classList.add('disabled');
        }
    }

    async function confirmDestroyProject() {
        if (document.getElementById('confirmDeleteBtn').classList.contains('disabled')) return;

        const btn = document.getElementById('confirmDeleteBtn');
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Destruindo...';
        btn.classList.add('disabled');

        try {
            const response = await fetch(`/projects/${currentProjectUuid}`, {
                method: 'DELETE',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            }
        } catch(e) {
            alert('Falha ao excluir o projeto.');
            btn.innerHTML = 'Destruir Permanentemente';
            btn.classList.remove('disabled');
        }
    }
    // --- News Feed Logic ---
    async function loadNewsFeed() {
        const feedContainer = document.getElementById('bento-news-feed');
        const NEWS_URL = 'https://runaris.com.br/ghost/news/news.json';
        
        try {
            const response = await fetch(NEWS_URL);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            if (data.news && data.news.length > 0) {
                renderNews(data.news);
            } else {
                showOfflineFeed();
            }
        } catch (error) {
            console.error('Ghost News Feed offline or unreachable.');
            showOfflineFeed();
        }
    }

    function renderNews(news) {
        const feedContainer = document.getElementById('bento-news-feed');
        feedContainer.innerHTML = news.map(item => {
            const style = item.image ? `style="background-image: url('${item.image}')"` : '';
            const overlay = item.image ? '<div class="bento-overlay"></div>' : '';
            const footerIcon = item.type === 'video' ? 'bi-play-circle-fill' : 'bi-arrow-right-short';
            
            return `
                <a href="${item.url}" target="_blank" class="bento-item ${item.size || 'small'}" ${style}>
                    <span class="badge-type">${item.type}</span>
                    ${overlay}
                    <div class="z-1">
                        <div class="bento-title">${item.title}</div>
                        ${item.content ? `<div class="bento-content line-clamp-2">${item.content}</div>` : ''}
                    </div>
                    <div class="bento-footer z-1">
                        <i class="bi ${footerIcon}"></i> ${item.type === 'video' ? 'Assistir' : 'Ler mais'}
                    </div>
                </a>
            `;
        }).join('');
    }

    function showOfflineFeed() {
        const feedContainer = document.getElementById('bento-news-feed');
        feedContainer.innerHTML = `
            <div class="bento-item large">
                <div class="bento-title">Santuário Offline</div>
                <div class="bento-content small">O feed de notícias não pôde ser carregado. Continue escrevendo sua história com foco total.</div>
                <div class="bento-footer mt-3"><i class="bi bi-shield-check"></i> Modo Imersivo Ativo</div>
            </div>
        `;
    }

    // Initialize News
    document.addEventListener('DOMContentLoaded', loadNewsFeed);
    </script>
    @endif
    </div> <!-- container -->
</div> <!-- bg-body -->
@endsection
