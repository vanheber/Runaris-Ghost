<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Configuração Inicial - Runaris Ghost</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --brand-primary: #6366f1;
            --brand-secondary: #a855f7;
            --bg-dark: #0f172a;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: url("{{ asset('bg-app.jpg') }}") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            color: #f8fafc;
            overflow: hidden;
        }
        .setup-container {
            max-width: 900px;
            margin: 0 auto;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            padding: 3rem;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        .glass-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .brand-logo {
            font-weight: 800;
            font-size: 2.5rem;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            display: block;
            text-align: center;
        }
        .btn-brand {
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            border: none;
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-brand:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.4);
            color: white;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 0.8rem 1rem;
        }
        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            color: white;
        }
        .floating-circles div {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
        }
        .circle-1 {
            width: 400px; height: 400px;
            background: rgba(99, 102, 241, 0.15);
            top: -100px; left: -100px;
        }
        .circle-2 {
            width: 300px; height: 300px;
            background: rgba(168, 85, 247, 0.15);
            bottom: -50px; right: -50px;
        }
        .password-warning {
            border: 1px solid rgba(234, 179, 8, 0.2);
            background: rgba(234, 179, 8, 0.1);
            color: #fde047;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <div class="floating-circles">
        <div class="circle-1"></div>
        <div class="circle-2"></div>
    </div>

    <div class="setup-container">
        <div class="glass-card">
            
            <!-- Header com Logo -->
            <div class="text-center mb-5">
                <img src="{{ asset('ghost-icon-transparent.png') }}" alt="Runaris Ghost" class="mb-3" style="width: 80px; filter: drop-shadow(0 0 20px rgba(111, 66, 193, 0.4)); animation: float 6s ease-in-out infinite;">
                <span class="brand-logo mb-0">Runaris <span class="opacity-50">Ghost</span></span>
                <p class="text-secondary small ls-wide text-uppercase" style="letter-spacing: 2px;">{{ __('The Digital Sanctuary for Authors') }}</p>
            </div>

            <!-- Step 0: Language Selection -->
            <div id="step-0" class="step active text-center">
                <i class="bi bi-translate display-4 text-primary mb-4 d-block"></i>
                <h2 class="fw-bold mb-3">{{ __('Choose your Language') }}</h2>
                <p class="text-secondary mb-5">{{ __('Select your preferred language for the interface.') }}</p>
                
                <div class="row g-3 justify-content-center mb-5">
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('pt_BR')">
                            <span class="fs-2 d-block mb-2">🇧🇷</span>
                            <span class="small fw-bold text-nowrap">Português (BR)</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('pt_PT')">
                            <span class="fs-2 d-block mb-2">🇵🇹</span>
                            <span class="small fw-bold text-nowrap">Português (PT)</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('en')">
                            <span class="fs-2 d-block mb-2">🇺🇸</span>
                            <span class="small fw-bold">English</span>
                        </button>
                    </div>
                    <div class="col-6 col-md-3">
                        <button class="btn btn-outline-secondary w-100 p-3 rounded-4 border-opacity-10" onclick="setLanguage('es')">
                            <span class="fs-2 d-block mb-2">🇪🇸</span>
                            <span class="small fw-bold">Español</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 1: Welcome -->
            <div id="step-1" class="step text-center">
                <i class="bi bi-rocket-takeoff display-4 text-primary mb-4 d-block"></i>
                <h2 class="fw-bold mb-3">{{ __('Welcome, Writer.') }}</h2>
                <p class="text-secondary mb-5">{{ __('We are about to set up your creative sanctuary. Before we begin, we need to validate your license.') }}</p>
                <button type="button" class="btn btn-brand btn-lg" onclick="nextStep(2)">{{ __('Start Now') }}</button>
            </div>

            <!-- Step 2: License Key -->
            <div id="step-2" class="step">
                <h3 class="fw-bold mb-2">{{ __('License Key') }}</h3>
                <p class="text-secondary mb-4">{{ __('Enter the key received from Gumroad to permanently unlock Runaris Ghost.') }}</p>
                
                <div class="mb-4">
                    <label class="form-label text-uppercase small fw-bold opacity-50">{{ __('License Key') }}</label>
                    <input type="text" id="license_key" class="form-control" placeholder="GUM-XXXX-XXXX-XXXX">
                    <div id="license-error" class="text-danger small mt-2 d-none"></div>
                </div>

                <div class="d-flex justify-content-between">
                    <button class="btn btn-link text-secondary text-decoration-none" onclick="nextStep(0)">{{ __('Back') }}</button>
                    <button class="btn btn-brand" id="btn-validate-license" onclick="validateLicense()">{{ __('Validate License') }}</button>
                </div>
            </div>

            <!-- Step 3: User Details & Security -->
            <div id="step-3" class="step">
                <h3 class="fw-bold mb-2">{{ __('Profile and Security') }}</h3>
                <p class="text-secondary mb-4">{{ __('How should we call you? And how do you want to protect your manuscripts on this machine?') }}</p>
                
                <form id="user-form">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-uppercase small fw-bold opacity-50">{{ __('Author Name') }}</label>
                            <input type="text" name="name" class="form-control" placeholder="Ex: Machado de Assis" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-uppercase small fw-bold opacity-50">{{ __('Email') }}</label>
                            <input type="email" name="email" class="form-control" placeholder="seu@email.com" required>
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

                        <div id="password-warning" class="password-warning d-none mt-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>{{ __('Attention:') }}</strong> {{ __('Attention: Without a password, anyone with access to this computer can read and edit your manuscripts.') }}
                        </div>
                    </div>

                    <div id="user-error" class="text-danger small mb-3 d-none"></div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-brand" id="btn-final-setup">{{ __('Next') }}</button>
                    </div>
                </form>
            </div>

            <!-- Step 4: AI Setup -->
            <div id="step-4" class="step">
                <h3 class="fw-bold mb-2">{{ __('Inteligência Artificial (Opcional)') }}</h3>
                <p class="text-secondary mb-4">O Runaris Ghost utiliza o **Google Gemini** para oferecer sugestões de escrita, brainstorm de personagens e planejamento de cenas.</p>
                
                <div class="mb-4 p-4 rounded-4 bg-info bg-opacity-10 border border-info border-opacity-10">
                    <label class="form-label text-uppercase small fw-bold opacity-50">{{ __('Google Gemini API Key') }}</label>
                    <input type="password" id="ai_key" class="form-control mb-3" placeholder="AIzaSy...">
                    
                    <p class="small text-body-secondary mb-0">
                        <i class="bi bi-info-circle"></i> Você pode obter sua chave gratuita no 
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-bold text-info">Google AI Studio</a>.
                    </p>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-brand" onclick="saveAiKey()">{{ __('Ativar Inteligência Artificial') }}</button>
                    <button class="btn btn-link text-secondary text-decoration-none" onclick="nextStep(5)">{{ __('Não quero usar IA agora') }}</button>
                </div>
                
                <p class="text-center small text-secondary mt-3 opacity-50">
                    Você poderá configurar ou alterar sua chave a qualquer momento nas configurações do sistema.
                </p>
            </div>

            <!-- Step 5: Final Succes -->
            <div id="step-5" class="step text-center">
                <i class="bi bi-check-circle-fill display-1 text-success mb-4 d-block"></i>
                <h2 class="fw-bold mb-3">{{ __('All Ready!') }}</h2>
                <p class="text-secondary mb-5">{{ __('Your creative sanctuary has been successfully configured. Get ready to bring your stories to life.') }}</p>
                <button type="button" class="btn btn-brand btn-lg px-5" onclick="window.location.href='/'">{{ __('Enter the Ghost') }}</button>
            </div>

        </div>
    </div>

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
            document.querySelectorAll('.step').forEach(el => el.classList.remove('active'));
            document.getElementById(`step-${step}`).classList.add('active');
        }

        async function validateLicense() {
            const key = document.getElementById('license_key').value;
            const btn = document.getElementById('btn-validate-license');
            const errorEl = document.getElementById('license-error');

            if (!key) return;

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Validando...';
            errorEl.classList.add('d-none');

            try {
                const response = await fetch('/setup/license', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                    },
                    body: JSON.stringify({ license_key: key })
                });

                const data = await response.json();

                if (data.status) {
                    nextStep(3);
                } else {
                    errorEl.innerText = data.message;
                    errorEl.classList.remove('d-none');
                }
            } catch (error) {
                errorEl.innerText = 'Erro ao conectar servidor. Verifique sua internet.';
                errorEl.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Validar Licença';
            }
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
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Finalizando...';
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
                    let errorMsg = data.message || 'Erro ao criar usuário.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join('<br>');
                    }
                    errorEl.innerHTML = errorMsg;
                    errorEl.classList.remove('d-none');
                }
            } catch (error) {
                console.error(error);
                errorEl.innerText = 'Erro na conexão com o servidor.';
                errorEl.classList.remove('d-none');
            } finally {
                btn.disabled = false;
                btn.innerText = 'Finalizar Configuração';
            }
        };
    </script>
</body>
</html>
