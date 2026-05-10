<div class="card bg-body-tertiary border-0 shadow-sm mt-4 p-4 text-center d-flex flex-column min-h-200">
    <h6 class="text-body-secondary small text-uppercase fw-bold mb-4 ls-2px"><i class="bi bi-broadcast me-2 text-primary"></i> Runaris Hub</h6>
    
    <div id="qsite-news-feed" class="flex-grow-1 d-flex align-items-center justify-content-center">
        <!-- Feed Skeleton -->
        <div class="spinner-border text-primary opacity-50" role="status">
            <span class="visually-hidden">Carregando novidades...</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Placeholder: Aqui você irá inserir a URL JSON do seu Qsite futuramente
            const QsiteFeedUrl = null; 
            
            const feedContainer = document.getElementById('qsite-news-feed');
            
            if (QsiteFeedUrl) {
                fetch(QsiteFeedUrl)
                    .then(res => res.json())
                    .then(data => {
                        // Renderizar o card do blog
                        if(data && data.articles && data.articles.length > 0) {
                            const latest = data.articles[0];
                            feedContainer.innerHTML = `
                                <div class="card border border-primary border-opacity-10 bg-body shadow-sm w-100 p-3 text-start hover-lift transition-all">
                                    <div class="badge bg-primary bg-opacity-10 text-primary mb-2 align-self-start fs-9">NOVIDADE</div>
                                    <h6 class="fw-bold mb-1 fs-7">${latest.title}</h6>
                                    <p class="small text-body-secondary mb-2 fs-10">${latest.excerpt}</p>
                                    <a href="${latest.url}" target="_blank" class="btn btn-sm btn-link text-decoration-none p-0">Ver mais <i class="bi bi-arrow-right"></i></a>
                                </div>
                            `;
                        } else {
                            feedContainer.innerHTML = `<span class="small text-body-secondary opacity-50">Sem novidades por agora.</span>`;
                        }
                    })
                    .catch(err => {
                        feedContainer.innerHTML = `<span class="small text-danger opacity-50">Falha ao conectar com o Hub.</span>`;
                    });
            } else {
                // Mock state until the Qsite URL is provided
                feedContainer.innerHTML = `
                    <div class="card border border-secondary border-opacity-25 bg-body shadow-sm w-100 p-3 text-start hover-lift transition-all">
                        <div class="badge bg-info bg-opacity-10 text-info mb-2 align-self-start fs-9">ATUALIZAÇÃO</div>
                        <h6 class="fw-bold mb-1 fs-7">Apoie o Criador!</h6>
                        <p class="small text-body-secondary mb-2 fs-10">Confira os novos serviços de consultoria para Writers em Qsite.</p>
                        <a href="#" class="btn btn-sm btn-link text-decoration-none p-0">Saiba mais <i class="bi bi-arrow-right"></i></a>
                    </div>
                `;
            }
        });
    </script>
</div>
