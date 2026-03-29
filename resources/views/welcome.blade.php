<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Runaris Ghost - Entre em seu Verso</title>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @vite(['resources/js/app.js'])
</head>
<body class="bg-dark text-light d-flex align-items-center justify-content-center vh-100 overflow-hidden" data-bs-theme="dark">
    <div class="ambient-bg animate-ambient"></div>
    <div class="animate-float" style="position: absolute; font-size: 12rem; opacity: 0.03; z-index: -1; right: 15%; top: 10%;">
        <i class="bi bi-ghost"></i>
    </div>

    <div class="container" style="max-width: 420px; z-index: 10;">
        <div class="text-center mb-5">
            <h1 class="display-1 fw-bold mb-0" style="background: linear-gradient(135deg, var(--bs-primary), #d946ef); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                <i class="bi bi-ghost text-primary-emphasis"></i> Runaris
            </h1>
            <p class="text-secondary small fw-bold tracking-widest mt-2">PLATAFORMA DE ESCRITA DE FICÇÃO</p>
        </div>

        <div class="card bg-body-tertiary border-0 shadow-lg p-4" style="border-radius: 20px; backdrop-filter: blur(20px); background: rgba(var(--bs-tertiary-bg-rgb), 0.7) !important;">
            <div class="mb-4">
                <label class="form-label text-body-secondary small fw-bold text-uppercase mb-2">E-mail de Autor</label>
                <input type="text" class="form-control" value="admin@runaris.com" readonly>
            </div>
            <div class="mb-4">
                <label class="form-label text-body-secondary small fw-bold text-uppercase mb-2">Chave Secreta</label>
                <input type="password" class="form-control" value="password" readonly>
            </div>
            
            <a href="/projects" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow-lg">
                <span>Entrar no Ghost</span>
                <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>

        <div class="text-center mt-4">
            <p class="text-body-secondary small">
                Deseja criar um novo verso? <a href="#" class="text-decoration-none text-primary">Solicite Acesso</a>
            </p>
        </div>
    </div>

    <script>
        // Subtle Mouse Movement for Background
        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            document.querySelector('.ambient-bg').style.transform = `translate(${x * 10}px, ${y * 10}px) scale(1.05)`;
        });
    </script>
</body>
</html>
