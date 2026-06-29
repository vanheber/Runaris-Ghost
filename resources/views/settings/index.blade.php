@extends('layouts.app')

@section('title', 'Configurações do Sistema')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar de Configurações -->
        <div class="col-md-3 mb-4">
            <h4 class="fw-bold mb-4 px-2">Configurações</h4>
            <div class="list-group list-group-flush shadow-sm rounded-4 overflow-hidden border-0 bg-body-tertiary">
                <a href="{{ url('/settings?tab=general') }}" class="list-group-item list-group-item-action py-3 px-4 {{ $tab === 'general' ? 'active' : '' }} border-0">
                    <i class="bi bi-sliders me-2"></i> Geral
                </a>
                <a href="{{ url('/settings?tab=ai') }}" class="list-group-item list-group-item-action py-3 px-4 {{ $tab === 'ai' ? 'active' : '' }} border-0">
                    <i class="bi bi-magic me-2"></i> Inteligência Artificial
                </a>
                <a href="{{ url('/settings?tab=docs') }}" class="list-group-item list-group-item-action py-3 px-4 {{ $tab === 'docs' ? 'active' : '' }} border-0">
                    <i class="bi bi-book me-2"></i> Documentação
                </a>
                <a href="{{ url('/settings?tab=system') }}" class="list-group-item list-group-item-action py-3 px-4 {{ $tab === 'system' ? 'active' : '' }} border-0">
                    <i class="bi bi-shield-lock me-2"></i> Sistema
                </a>
                <a href="{{ url('/eula') }}" class="list-group-item list-group-item-action py-3 px-4 border-0">
                    <i class="bi bi-file-earmark-text me-2"></i> Licença (EULA)
                </a>
            </div>
            
            <div class="mt-4 px-2">
                <a href="{{ url('/projects') }}" class="btn btn-link text-body-secondary small text-decoration-none p-0">
                    <i class="bi bi-arrow-left me-1"></i> Voltar aos Projetos
                </a>
            </div>
        </div>

        <!-- Conteúdo das Configurações -->
        <div class="col-md-9 animate-fade-in">
            @if($tab === 'general')
                <div id="general" class="card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
                    <div class="position-absolute top-0 start-0 w-100 h-2 bg-primary opacity-50 rounded-top" style="height: 4px;"></div>
                    <h5 class="fw-bold mb-4">Preferências Gerais</h5>
                    <div class="mb-4">
                        <label class="form-label text-body-secondary small text-uppercase fw-bold ls-wide">Nome da Instalação</label>
                        <input type="text" class="form-control bg-body border-secondary border-opacity-25" value="Runaris Ghost" readonly>
                    </div>
                </div>
            @endif

            @if($tab === 'ai')
                <div id="ai" class="card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
                    <div class="position-absolute top-0 start-0 w-100 h-2 bg-info opacity-50 rounded-top" style="height: 4px;"></div>
                    <h5 class="fw-bold mb-4 text-info"><i class="bi bi-magic"></i> Inteligência Artificial</h5>
                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ url('/settings/ai') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="gemini_api_key" class="form-label text-body-secondary small text-uppercase fw-bold ls-wide">Google Gemini API Key</label>
                            <input type="password" class="form-control bg-body border-secondary border-opacity-25 py-2" id="gemini_api_key" name="gemini_api_key" value="{{ $geminiApiKey }}" placeholder="AIzaSy...">
                            <div class="form-text opacity-75 small mt-2">
                                <i class="bi bi-info-circle"></i> Sua chave é armazenada com segurança no banco local (`system_settings`) e nunca será enviada para fora da sua máquina, exceto para os servidores do Google via API oficial.
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-info text-white px-4 rounded-pill shadow-sm">
                                <i class="bi bi-save me-2"></i> Salvar API Key
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($tab === 'system')
                <div id="system" class="card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
                    <div class="position-absolute top-0 start-0 w-100 h-2 bg-danger opacity-50 rounded-top" style="height: 4px;"></div>
                    <h5 class="fw-bold mb-4 text-danger"><i class="bi bi-shield-exclamation"></i> Administração do Santuário</h5>
                    
                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm mb-4">
                            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger border-0 shadow-sm mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                        </div>
                    @endif

                    <!-- Seção de Atualização -->
                    <div class="mb-5 p-4 rounded-4 border border-primary border-opacity-10 bg-primary bg-opacity-10">
                        <div class="d-flex align-items-center mb-3">
                            <div class="position-relative">
                                <i class="bi bi-shield-check fs-2 text-primary me-3"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary" style="font-size: 0.5rem;">
                                    GUARD
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">Guardian Update System</h6>
                                <p class="text-body-secondary small mb-0">Versão Atual: <span class="badge bg-body text-primary border border-primary border-opacity-25">{{ $currentVersion }}</span></p>
                            </div>
                            <div id="update-status" class="text-end d-none">
                                <span class="badge bg-success shadow-sm">Nova Versão Disponível!</span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <form action="{{ url('/settings/update') }}" method="POST" id="update-form">
                                @csrf
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btn-update">
                                    <i class="bi bi-cloud-arrow-down me-2"></i> Atualizar Sistema
                                </button>
                            </form>

                            @if($hasRollback)
                                <form action="{{ url('/settings/rollback') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary rounded-pill px-4" onclick="return confirm('Isso irá restaurar o banco de dados e arquivos para o estado anterior à última atualização. Continuar?')">
                                        <i class="bi bi-arrow-counterclockwise me-2"></i> Desfazer Atualização
                                    </button>
                                </form>
                            @endif
                        </div>
                        
                        <div class="mt-3">
                            <p class="text-body-secondary small mb-0">
                                <i class="bi bi-info-circle me-1"></i> 
                                Um backup de segurança é criado automaticamente antes de qualquer atualização.
                            </p>
                        </div>
                    </div>

                    <div class="mb-5 p-4 rounded-4 border border-info border-opacity-10 bg-info bg-opacity-10">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-cloud-download fs-2 text-info me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Backup Total do Sistema</h6>
                                <p class="text-body-secondary small mb-0">Baixe um arquivo ZIP contendo todos os seus projetos, manuscritos, imagens e o banco de dados.</p>
                            </div>
                        </div>
                        <a href="{{ url('/settings/backup') }}" class="btn btn-info text-white rounded-pill px-4">
                            <i class="bi bi-download me-2"></i> Baixar Tudo como ZIP
                        </a>
                    </div>

                    <hr class="my-5 opacity-10">

                    <div class="p-4 rounded-4 border border-danger border-opacity-10 bg-danger bg-opacity-10">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-fire fs-2 text-danger me-3"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Reset de Fábrica</h6>
                                <p class="text-body-secondary small mb-0">CUIDADO: Isso irá apagar PERMANENTEMENTE todos os seus projetos, usuários e configurações. Esta ação não pode ser desfeita.</p>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#resetModal">
                            <i class="bi bi-trash-fill me-2"></i> Redefinir Aplicativo
                        </button>
                    </div>
                </div>

                <!-- Modal de Confirmação de Reset -->
                <div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-danger">Você tem certeza?</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body py-4">
                                <p class="mb-4">Esta operação irá apagar tudo. <br>Recomendamos fortemente que você <strong>baixe o backup ZIP</strong> antes de continuar.</p>
                                
                                <form action="{{ url('/settings/factory-reset') }}" method="POST">
                                    @csrf
                                    @if(\App\Models\SystemSetting::getSetting('use_local_password', 'true') === 'true')
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-uppercase opacity-50">Confirme sua senha</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                    @endif
                                    
                                    <div class="d-grid gap-2">
                                        <a href="{{ url('/settings/backup') }}" class="btn btn-outline-info rounded-pill">
                                            <i class="bi bi-download me-2"></i> Baixar Backup antes de apagar
                                        </a>
                                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold">
                                            <i class="bi bi-exclamation-triangle-fill me-2"></i> APAGAR TUDO PERMANENTEMENTE
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($tab === 'docs')
                <div id="docs" class="card bg-body-tertiary border-0 shadow-sm p-4 pt-5 position-relative">
                    <div class="position-absolute top-0 start-0 w-100 h-2 bg-accent opacity-50 rounded-top" style="height: 4px;"></div>
                    <h5 class="fw-bold mb-4">Documentação do Sistema</h5>
                    
                    <div class="documentation-content small text-body-secondary lh-lg markdown-body p-2">
                        {!! $docContent !!}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .ls-wide { letter-spacing: 0.05em; }
    .bg-accent { background-color: #fd7e14; }
    .list-group-item.active {
        background-color: var(--bs-primary) !important;
        color: white !important;
    }
    .documentation-content h1, .documentation-content h2, .documentation-content h3 {
        color: var(--bs-emphasis-color);
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    .documentation-content h2 { border-bottom: 1px solid rgba(var(--bs-primary-rgb), 0.1); padding-bottom: 0.5rem; }
    .documentation-content code { background: rgba(var(--bs-primary-rgb), 0.1); color: var(--bs-primary); padding: 2px 4px; border-radius: 4px; }
    .documentation-content li { margin-bottom: 0.5rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if($tab === 'system')
            // Check for updates
            fetch('{{ url("/settings/update/check") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.has_update) {
                        document.getElementById('update-status').classList.remove('d-none');
                        const btn = document.getElementById('btn-update');
                        btn.innerHTML = `<i class="bi bi-stars me-2"></i> Instalar Versão ${data.latest_version}`;
                        btn.classList.replace('btn-primary', 'btn-success');
                    }
                })
                .catch(err => console.error('Update check failed:', err));

            // Prevent multiple clicks
            document.getElementById('update-form')?.addEventListener('submit', function() {
                const btn = document.getElementById('btn-update');
                btn.disabled = true;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Atualizando...`;
            });
        @endif
    });
</script>
@endsection
