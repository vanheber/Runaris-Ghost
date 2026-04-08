@extends('layouts.app')

@section('title', 'Configurações - ' . $project->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ url('/projects/'.$project->uuid) }}" class="btn btn-link text-body-secondary p-0 me-3" title="Voltar ao Workspace">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <h2 class="fw-bold mb-0">Configurações do Projeto</h2>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4 animate-fade-in">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="card bg-body-tertiary border-0 shadow-sm p-4 pt-5 mb-4 position-relative">
                <div class="position-absolute top-0 start-0 w-100 h-2 bg-primary opacity-50 rounded-top" style="height: 4px;"></div>
                
                <form action="{{ url('/projects/'.$project->uuid) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="form-label text-body-secondary small text-uppercase fw-bold ls-wide">Título da Obra</label>
                        <input type="text" class="form-control bg-body border-secondary border-opacity-25 py-2" id="name" name="name" value="{{ $project->name }}" required>
                        <div class="form-text opacity-50 small">O nome principal que aparecerá nas exportações e no dashboard.</div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label text-body-secondary small text-uppercase fw-bold ls-wide">Sinopse Curta / Descrição</label>
                        <textarea class="form-control bg-body border-secondary border-opacity-25" id="description" name="description" rows="5">{{ $project->description }}</textarea>
                        <div class="form-text opacity-50 small">Uma breve descrição para ajudar você a manter o foco no tema central da obra.</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top border-secondary border-opacity-10">
                        <span class="text-body-secondary small italic font-monospace">UUID: {{ $project->uuid }}</span>
                        <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger border-opacity-25 bg-danger bg-opacity-5 p-4 rounded-4 shadow-sm mt-5">
                <div class="d-flex align-items-center mb-3 text-danger">
                    <i class="bi bi-exclamation-octagon fs-4 me-2"></i>
                    <h5 class="fw-bold mb-0">Zona Crítica</h5>
                </div>
                <p class="small text-body-secondary mb-3">
                    A exclusão definitiva do projeto remove permanentemente todos os manuscritos, imagens e configurações. Não é possível reverter esta ação.
                </p>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="small text-body-secondary">Para excluir, vá até a <a href="{{ url('/projects') }}" class="text-danger fw-bold">página principal de projetos</a>.</span>
                    <i class="bi bi-trash opacity-25 fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-wide { letter-spacing: 0.05em; }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .form-control:focus {
        border-color: rgba(var(--bs-primary-rgb), 0.5);
        box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.1);
    }
</style>
@endsection
