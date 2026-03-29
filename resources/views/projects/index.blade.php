@extends('layouts.app')

@section('title', 'Meus Projetos - Runaris Ghost')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="fw-bold mb-2">Suas Histórias</h1>
            <p class="text-body-secondary mb-0">Continue sua jornada literária ou comece uma nova aventura.</p>
        </div>
        <div class="d-none d-md-block">
            <span class="badge bg-dark border border-secondary text-secondary p-2">Total: {{ $projects->count() }}</span>
        </div>
    </div>

    @if($projects->isEmpty())
    <div class="card bg-body-tertiary border-0 shadow-sm p-5 text-center my-5 animate__animated animate__fadeIn">
        <i class="bi bi-journal-plus fs-1 text-primary mb-3 d-block"></i>
        <h3 class="fw-bold">Nenhum projeto encontrado</h3>
        <p class="text-body-secondary mb-4">Você ainda não iniciou sua primeira obra. Que tal começarmos hoje?</p>
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="bi bi-pencil-square me-2"></i> Criar Meu Primeiro Projeto
        </button>
    </div>
    @else
    <div class="row g-4">
        @foreach($projects as $project)
        <div class="col-md-6 col-lg-4">
            <div class="card bg-body-tertiary border-0 shadow-sm p-4 h-100 d-flex flex-column" onclick="window.location='{{ url('/projects/'.$project->uuid) }}'" style="cursor: pointer;">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary small fw-bold text-uppercase px-2 py-1">Projeto Ativo</span>
                    <span class="text-body-secondary small"><i class="bi bi-clock me-1"></i> {{ $project->last_opened_at ? $project->last_opened_at->diffForHumans() : 'Nunca aberto' }}</span>
                </div>
                
                <h4 class="fw-bold mb-3 text-truncate">{{ $project->name }}</h4>
                <p class="text-body-secondary small mb-4 flex-grow-1">
                    {{ Str::limit($project->description ?? 'Sem sinopse definida.', 120) }}
                </p>
                
                <div class="border-top pt-3 mt-auto border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar-group d-flex me-3">
                            <i class="bi bi-file-earmark-text text-primary me-2"></i>
                            <span class="small fw-bold">{{ $project->uuid }}</span>
                        </div>
                    </div>
                    <a href="{{ url('/projects/'.$project->uuid) }}" class="btn btn-link text-primary p-0 text-decoration-none fw-bold small">
                        Abrir <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
