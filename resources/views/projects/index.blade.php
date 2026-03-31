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
        <button class="btn btn-primary btn-lg rounded-pill" data-bs-toggle="modal" data-bs-target="#createProjectModal">
            <i class="bi bi-pencil-square me-2"></i> Criar Meu Primeiro Projeto
        </button>
    </div>
    @else
    <style>
        .project-card-link { text-decoration: none; color: inherit; }
        .project-cover-container { 
            position: relative; 
            border-radius: 12px; 
            overflow: hidden; 
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .project-card:hover .project-cover-container { transform: translateY(-5px); }
        .cover-image { width: 100%; height: 100%; object-fit: cover; }
        .cover-placeholder { 
            background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.1), rgba(var(--bs-dark-rgb), 0.8));
            display: flex; align-items: center; justify-content: center;
        }
    </style>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        @foreach($projects as $project)
        <div class="col">
            <a href="{{ url('/projects/'.$project->uuid) }}" class="project-card-link">
                <div class="card bg-body-tertiary border-0 shadow-sm p-3 h-100 project-card hover-lift">
                    <div class="project-cover-container ratio ratio-2x3">
                        @if($project->cover_image_uuid)
                            <img src="{{ url('/projects/'.$project->uuid.'/gallery/'.$project->cover_image_uuid.'/image') }}" class="cover-image" alt="{{ $project->name }}">
                        @else
                            <div class="cover-placeholder cover-image">
                                <i class="bi bi-ghost fs-1 opacity-10"></i>
                            </div>
                        @endif
                        <div class="position-absolute top-0 end-0 p-2">
                            <span class="badge bg-dark bg-opacity-75 backdrop-blur text-secondary x-small p-2">
                                <i class="bi bi-clock me-1"></i> {{ $project->last_opened_at ? $project->last_opened_at->diffForHumans(null, true) : 'Never' }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="px-2 pb-2">
                        <h5 class="fw-bold mb-2 text-truncate text-white">{{ $project->name }}</h5>
                        <p class="text-body-secondary x-small mb-0 line-clamp-3">
                            {{ $project->description ?? 'Sem sinopse definida.' }}
                        </p>
                    </div>
                    
                    <div class="mt-auto px-2 pt-3 border-top border-secondary border-opacity-10 d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary small fw-bold">ID: {{ substr($project->uuid, 0, 8) }}...</span>
                        <span class="text-primary fw-bold small">Abrir <i class="bi bi-arrow-right ms-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
