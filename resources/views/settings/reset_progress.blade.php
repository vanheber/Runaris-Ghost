<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Limpando Santuário - Runaris Ghost</title>
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
            background: #000;
            min-height: 100vh;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0;
        }
        .main-container {
            text-align: center;
            z-index: 10;
        }
        .spinner-container {
            margin-bottom: 2rem;
            position: relative;
            display: inline-block;
        }
        .outer-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(99, 102, 241, 0.1);
            border-top: 4px solid var(--brand-primary);
            animation: spin 2s linear infinite;
        }
        .inner-ring {
            position: absolute;
            top: 20px; left: 20px;
            width: 80px; height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(168, 85, 247, 0.1);
            border-bottom: 4px solid var(--brand-secondary);
            animation: spin-reverse 1.5s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes spin-reverse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }
        .status-text {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .detail-text {
            color: rgba(255, 255, 255, 0.5);
            font-weight: 300;
            letter-spacing: 0.05em;
        }
        .background-glow {
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(0,0,0,0) 70%);
            z-index: 1;
        }
    </style>
</head>
<body>

    <div class="background-glow"></div>

    <div class="main-container">
        <div class="spinner-container">
            <div class="outer-ring"></div>
            <div class="inner-ring"></div>
        </div>
        
        <div id="status-container">
            <h2 class="status-text" id="status-title">Purificando Santuário</h2>
            <p class="detail-text" id="status-detail">Apagando manuscritos e registros locais...</p>
        </div>

        <div id="error-container" class="d-none">
            <i class="bi bi-exclamation-triangle display-1 text-danger mb-4"></i>
            <h3 class="text-danger fw-bold">Falha no Processo</h3>
            <p id="error-message" class="text-secondary mb-4"></p>
            <button class="btn btn-outline-light rounded-pill px-4" onclick="window.location.href='/settings'">Voltar e tentar novamente</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const statusDetail = document.getElementById('status-detail');
            const statusTitle = document.getElementById('status-title');
            const token = document.querySelector('meta[name="csrf-token"]').content;
            
            await new Promise(r => setTimeout(r, 2000));

            try {
                // ETAPA 1: ARQUIVOS
                statusDetail.innerText = "Etapa 1/3: Removendo manuscritos e backups...";
                await runStep('/settings/factory-reset/step-files', token);

                // ETAPA 2: BANCO (PESADA)
                statusDetail.innerText = "Etapa 2/3: Reconstruindo a estrutura do banco...";
                await runStep('/settings/factory-reset/step-database', token);

                // ETAPA 3: FINALIZAÇÃO
                statusDetail.innerText = "Etapa 3/3: Limpando caches e finalizando...";
                await runStep('/settings/factory-reset/step-finalize', token);

                // SUCESSO
                statusTitle.innerText = "Santuário Pronto";
                statusDetail.innerText = "Ambiente limpo com sucesso. Redirecionando...";
                
                setTimeout(() => {
                    window.location.href = '/setup';
                }, 2000);

            } catch (error) {
                console.error("Reset Error:", error);
                showError(error.message);
            }
        });

        async function runStep(url, token) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.message || `Erro na etapa (${response.status})`);
            }

            const data = await response.json();
            if (!data.status) {
                throw new Error(data.message || "Falha ao processar etapa.");
            }
            return data;
        }

        function showError(msg) {
            document.getElementById('status-container').classList.add('d-none');
            document.getElementById('error-container').classList.remove('d-none');
            document.getElementById('error-message').innerText = msg;
        }
    </script>
</body>
</html>
