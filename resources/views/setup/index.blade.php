<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Configuração Inicial - Runaris Ghost') }}</title>
    @php $langJson = json_decode(file_get_contents(base_path('lang/'.app()->getLocale().'.json')), true) ?? []; @endphp
    <script id="lang-json" type="application/json">@json($langJson)</script>
    <script>window.__=function(k){const t=JSON.parse(document.getElementById('lang-json').textContent);return t[k]||k;};</script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body.setup-page {
            background: url("{{ Vite::asset('resources/assets/images/bg-app.jpg') }}") no-repeat center center fixed;
            background-size: cover;
        }
    </style>
</head>
<body class="setup-page">

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="installer-wrap">
        <main class="glass-card" role="main">
            
            <header class="text-center mb-5">
                <img src="{{ Vite::asset('resources/assets/images/lg-ghost-hz-color-inverted.svg') }}" alt="Runaris Ghost" class="setup-logo mb-3">
            </header>

            <!-- Step 0: Language Selection -->
            <div id="step-0" class="step active text-center" aria-current="step">
                <i class="bi bi-translate display-4 text-primary mb-4 d-block" aria-hidden="true"></i>
                <h2 class="fw-bold mb-3">{{ __('Choose your Language') }}</h2>
                <p class="text-secondary mb-5">{{ __('Select your preferred language for the interface.') }}</p>
                
                <div class="row g-3 justify-content-center mb-5">
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('pt_BR')" aria-label="Português do Brasil">
                            <span class="fs-2 d-block mb-2" aria-hidden="true">🇧🇷</span>
                            <span class="small fw-bold text-nowrap">Português (BR)</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('pt_PT')" aria-label="Português de Portugal">
                            <span class="fs-2 d-block mb-2" aria-hidden="true">🇵🇹</span>
                            <span class="small fw-bold text-nowrap">Português (PT)</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('en')" aria-label="English">
                            <span class="fs-2 d-block mb-2" aria-hidden="true">🇺🇸</span>
                            <span class="small fw-bold">English</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('es')" aria-label="Español">
                            <span class="fs-2 d-block mb-2" aria-hidden="true">🇪🇸</span>
                            <span class="small fw-bold">Español</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 1: Welcome -->
            <div id="step-1" class="step text-center">
                <i class="bi bi-rocket-takeoff display-4 text-primary mb-4 d-block" aria-hidden="true"></i>
                <h2 class="fw-bold mb-3">{{ __('Welcome, Writer.') }}</h2>
                <p class="text-secondary mb-5">{{ __('We are about to set up your creative sanctuary. Let\'s prepare your workspace!') }}</p>
                <button type="button" class="btn btn-brand btn-lg" onclick="nextStep(3)">{{ __('Start Now') }}</button>
            </div>

            <!-- Step 3: User Details & Security -->
            <div id="step-3" class="step">
                <h3 class="fw-bold mb-2">{{ __('Profile and Security') }}</h3>
                <p class="text-secondary mb-4">{{ __('How should we call you? And how do you want to protect your manuscripts on this machine?') }}</p>
                
                <form id="user-form">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="setup-name" class="form-label text-uppercase small fw-bold opacity-50">{{ __('Author Name') }}</label>
                            <input id="setup-name" type="text" name="name" class="form-control" placeholder="{{ __('Ex: Machado de Assis') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="setup-email" class="form-label text-uppercase small fw-bold opacity-50">{{ __('Email') }}</label>
                            <input id="setup-email" type="email" name="email" class="form-control" placeholder="{{ __('seu@email.com') }}" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch fs-5 mb-3">
                            <input class="form-check-input" type="checkbox" id="use_password" name="use_password" value="1" checked onchange="togglePasswordField(this.checked)">
                            <label class="form-check-label fs-6 fw-bold">{{ __('Enable password protection') }}</label>
                        </div>

                        <div id="password-fields">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="password" name="password" id="pass_field" class="form-control" placeholder="{{ __('App Password') }}">
                                </div>
                                <div class="col-md-6">
                                    <input type="password" name="password_confirmation" id="pass_confirm_field" class="form-control" placeholder="{{ __('Confirm password') }}">
                                </div>
                            </div>
                        </div>

                        <div id="password-warning" class="password-warning d-none mt-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                            <strong>{{ __('Attention:') }}</strong> {{ __('Attention: Without a password, anyone with access to this computer can read and edit your manuscripts.') }}
                        </div>
                    </div>

                    <div id="user-error" class="text-danger small mb-3 d-none" role="alert"></div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-brand" id="btn-final-setup">{{ __('Next') }}</button>
                    </div>
                </form>
            </div>

            <!-- Step 4: AI Setup -->
            <div id="step-4" class="step">
                <h3 class="fw-bold mb-2">{{ __('Inteligência Artificial (Opcional)') }}</h3>
                <p class="text-secondary mb-4">{{ __('O Runaris Ghost utiliza o Google Gemini para oferecer sugestões de escrita, brainstorm de personagens e planejamento de cenas.') }}</p>
                
                <div class="mb-4 p-4 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-10">
                    <label class="form-label text-uppercase small fw-bold opacity-50">{{ __('Google Gemini API Key') }}</label>
                    <input type="password" id="ai_key" class="form-control mb-3" placeholder="AIzaSy...">
                    
                    <p class="small text-body-secondary mb-0">
                        <i class="bi bi-info-circle" aria-hidden="true"></i> {{ __('Você pode obter sua chave gratuita no') }} 
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-bold text-info">Google AI Studio</a>.
                    </p>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-brand" onclick="saveAiKey()">{{ __('Ativar Inteligência Artificial') }}</button>
                    <button class="btn btn-link text-secondary text-decoration-none" onclick="nextStep(5)">{{ __('Não quero usar IA agora') }}</button>
                </div>
                
                <p class="text-center small text-secondary mt-3 opacity-50">
                    {{ __('Você poderá configurar ou alterar sua chave a qualquer momento nas configurações do sistema.') }}
                </p>
            </div>

            <!-- Step 5: Final Succes -->
            <div id="step-5" class="step text-center">
                <i class="bi bi-check-circle-fill display-1 text-success mb-4 d-block" aria-hidden="true"></i>
                <h2 class="fw-bold mb-3">{{ __('All Ready!') }}</h2>
                <p class="text-secondary mb-5">{{ __('Your creative sanctuary has been successfully configured. Get ready to bring your stories to life.') }}</p>
                <button type="button" class="btn btn-brand btn-lg px-5" onclick="window.location.href='/'">{{ __('Enter the Ghost') }}</button>
            </div>

        </div>
    </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedLocale = 'pt_BR';

        async function setLanguage(locale) {
            selectedLocale = locale;
            
            try {
                await fetch('/setup/locale', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({ locale: locale })
                });
                
                // Force reload to step 1 with the NEW language
                window.location.href = '/setup?step=1';
            } catch (e) {
                console.error('Failed to set locale', e);
                nextStep(1);
            }
        }

        window.onload = () => {
            const urlParams = new URLSearchParams(window.location.search);
            const step = urlParams.get('step');
            if (step) {
                nextStep(step);
            }
        };

        function nextStep(step) {
            document.querySelectorAll('.step').forEach(el => {el.classList.remove('active');el.removeAttribute('aria-current');});
            const next = document.getElementById(`step-${step}`);
            next.classList.add('active');
            next.setAttribute('aria-current', 'step');
        }


        async function saveAiKey() {
            const key = document.getElementById('ai_key').value;
            if (!key) {
                nextStep(5);
                return;
            }

            try {
                await fetch('/setup/ai', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({ gemini_api_key: key })
                });
                nextStep(5);
            } catch (e) {
                console.error(e);
                nextStep(5);
            }
        }

        function togglePasswordField(checked) {
            document.getElementById('password-fields').classList.toggle('d-none', !checked);
            document.getElementById('password-warning').classList.toggle('d-none', checked);
            
            const passFields = ['pass_field', 'pass_confirm_field'];
            passFields.forEach(id => {
                document.getElementById(id).required = checked;
            });
        }

        document.getElementById('user-form').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-final-setup');
            const errorEl = document.getElementById('user-error');
            const formData = new FormData(e.target);
            
            // ConveteFormData para JSON
            const payload = {
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password'),
                password_confirmation: formData.get('password_confirmation'),
                use_password: formData.get('use_password') === '1' ? 1 : 0
            };

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> '+window.__('Finalizando...');
            errorEl.classList.add('d-none');

            try {
                const response = await fetch('/setup/user', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.status) {
                    nextStep(4);
                } else {
                    // Handle validation errors or custom messages
                    let errorMsg = data.message || window.__('Erro ao criar usuário.');
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    errorEl.innerHTML = errorMsg;
                    errorEl.classList.remove('d-none');
                }
            } catch (error) {
                console.error(error);
                errorEl.innerText = window.__('Erro na conexão com o servidor.');
                errorEl.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerText = window.__('Finalizar Configuração');
            }
        };
    </script>
</body>
</html>
