@extends('layouts.app')

@section('title', 'Licença de Uso - Runaris Ghost')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card bg-body-tertiary border-0 shadow-sm p-5 rounded-4 animate-fade-in">
                <div class="mb-4">
                    <a href="{{ url()->previous() }}" class="btn btn-link text-body-secondary small text-decoration-none p-0">
                        <i class="bi bi-arrow-left me-1"></i> Voltar
                    </a>
                </div>

                <div class="markdown-body text-body-secondary lh-lg">
                    {!! $content !!}
                </div>
            </div>

            <p class="text-center mt-5 small text-body-secondary opacity-50">
                Runaris Ghost &copy; 2026 A. W. VanHeber. Todos os direitos reservados.
            </p>
        </div>
    </div>
</div>

<style>
    .markdown-body h1, .markdown-body h2, .markdown-body h3 {
        color: var(--bs-emphasis-color);
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    .markdown-body h1 { border-bottom: 2px solid var(--bs-primary); padding-bottom: 0.5rem; }
    .markdown-body h2 { border-bottom: 1px solid rgba(var(--bs-primary-rgb), 0.1); padding-bottom: 0.5rem; }
    .markdown-body code { background: rgba(var(--bs-primary-rgb), 0.1); color: var(--bs-primary); padding: 2px 4px; border-radius: 4px; }
    .markdown-body li { margin-bottom: 0.5rem; }
</style>
@endsection
