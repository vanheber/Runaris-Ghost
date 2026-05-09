<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Runaris Ghost')</title>

    <!-- Theme Initialization Script (Avoids Flash) -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Scripts -->
    @vite(['resources/js/app.js'])
</head>
<body class="antialiased d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg border-bottom sticky-top py-2 bg-body-tertiary">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/projects') }}">
                <img src="{{ Vite::asset('resources/assets/images/logo-ghost-color.png') }}" alt="Runaris Ghost" class="logo-light" style="width: 160px;">
                <img src="{{ Vite::asset('resources/assets/images/logo-ghost-inverted.png') }}" alt="Runaris Ghost" class="logo-dark" style="width: 160px;">
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="bi bi-list text-primary fs-2"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-2 {{ Request::is('projects') ? 'active' : '' }}" href="{{ url('/projects') }}">Projetos</a>
                    </li>
                    
                    <!-- Theme Switcher -->
                    <li class="nav-item dropdown ms-lg-3">
                        <button class="btn btn-link nav-link dropdown-toggle d-flex align-items-center px-2" id="bd-theme" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
                            <i class="bi bi-palette2 me-2"></i>
                            <span class="d-lg-none">Tema</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 bg-body-tertiary p-2" aria-labelledby="bd-theme">
                            <li><button type="button" class="dropdown-item d-flex align-items-center rounded mb-1" data-bs-theme-value="dark"><i class="bi bi-moon-stars-fill me-2 opacity-50"></i> Escuro</button></li>
                            <li><button type="button" class="dropdown-item d-flex align-items-center rounded mb-1" data-bs-theme-value="light"><i class="bi bi-sun-fill me-2 opacity-50"></i> Claro</button></li>
                            <li><button type="button" class="dropdown-item d-flex align-items-center rounded" data-bs-theme-value="solar-light"><i class="bi bi-brightness-high-fill me-2 opacity-50"></i> Solarizado Claro</button></li>
                        </ul>
                    </li>

                    <li class="nav-item ms-lg-3">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createProjectModal">
                            <i class="bi bi-plus-lg me-1"></i> Novo Projeto
                        </button>
                    </li>
                    @if(\App\Models\SystemSetting::getSetting('use_local_password', 'true') === 'true')
                        <li class="nav-item ms-lg-3">
                            <form action="{{ route('logout') }}" method="POST" id="logout-form" class="d-none">@csrf</form>
                            <a href="#" class="nav-link px-0 text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="bi bi-box-arrow-right me-1"></i> Sair
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-shrink-0">
        @yield('content')
    </main>

    <footer class="py-2 bg-body-tertiary border-top mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="col-md-4 d-flex align-items-center">
                    <span class="text-body-secondary small me-3">
                        &copy; {{ date('Y') }} Runaris Ghost. | <a href="{{ url('/eula') }}" class="text-body-secondary text-decoration-none small opacity-75 hover-opacity-100">Termos e Licença</a>
                    </span>
                    <a href="https://runaris.com.br" target="_blank" class="opacity-50 hover-opacity-100">
                        <img src="{{ Vite::asset('resources/assets/images/lg-runaris-hz.svg') }}" alt="Desenvolvido por Runaris" class="logo-light" style="height: 15px;">
                        <img src="{{ Vite::asset('resources/assets/images/lg-runaris-hz-inverted.svg') }}" alt="Desenvolvido por Runaris" class="logo-dark" style="height: 15px;">
                    </a>
                </div>
                
                <div class="col-md-4 d-flex justify-content-center">
                    <span class="text-body-secondary small opacity-50">Sua jornada, suas regras.</span>
                </div>

                <ul class="nav col-md-4 justify-content-end list-unstyled d-flex mb-0">
                    <li class="ms-3"><a class="text-body-secondary lh-1" href="#"><i class="bi bi-github"></i></a></li>
                    <li class="ms-3"><a class="text-body-secondary lh-1" href="#"><i class="bi bi-discord"></i></a></li>
                    <li class="ms-3">
                        <a class="text-body-secondary lh-1" href="{{ url('/settings') }}" title="Configurações do Sistema">
                            <i class="bi bi-gear-fill"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Global Theme Toggle Script -->
    <script>
        (() => {
            'use strict'
            const getStoredTheme = () => localStorage.getItem('theme')
            const setStoredTheme = theme => localStorage.setItem('theme', theme)

            const setTheme = theme => {
                document.documentElement.setAttribute('data-bs-theme', theme)
            }

            window.addEventListener('DOMContentLoaded', () => {
                const showActiveTheme = (theme) => {
                    document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
                        element.classList.remove('active')
                    })
                    const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
                    if (btnToActive) btnToActive.classList.add('active')
                }

                document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
                    toggle.addEventListener('click', () => {
                        const theme = toggle.getAttribute('data-bs-theme-value')
                        setStoredTheme(theme)
                        setTheme(theme)
                        showActiveTheme(theme)
                    })
                })
                showActiveTheme(localStorage.getItem('theme') || 'dark')
            })
        })()
    </script>

    <!-- Create Project Modal -->
    <div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-body-tertiary border-0 shadow-lg p-4">
                <form action="{{ url('/projects') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 p-0 mb-4">
                        <h5 class="modal-title fw-bold">Criar Nova História</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="mb-3">
                            <label for="name" class="form-label text-body-secondary small text-uppercase fw-bold">Título da Obra</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Ex: As Crônicas de Runaris" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label text-body-secondary small text-uppercase fw-bold">Sinopse Curta</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Uma breve descrição da sua jornada..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-0 mt-4">
                        <button type="button" class="btn btn-link text-body-secondary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4">Começar Jornada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        [data-bs-theme="dark"] .logo-light { display: none; }
        [data-bs-theme="light"] .logo-dark { display: none; }
        [data-bs-theme="solar-light"] .logo-dark { display: none; }
        
        .logo-light, .logo-dark {
            object-fit: contain;
            transition: opacity 0.3s ease;
        }
    </style>
</body>
</html>
