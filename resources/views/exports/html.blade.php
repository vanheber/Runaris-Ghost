<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->name }} - Reader</title>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bg-color: #ffffff;
            --text-color: #1a1a1a;
            --sidebar-bg: #f8f9fa;
            --border-color: #eeeeee;
        }

        [data-theme="dark"] {
            --bg-color: #121212;
            --text-color: #e0e0e0;
            --sidebar-bg: #1e1e1e;
            --border-color: #333333;
        }

        [data-theme="sepia"] {
            --bg-color: #f4ecd8;
            --text-color: #5b4636;
            --sidebar-bg: #eadeca;
            --border-color: #d3c5af;
        }

        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
            overflow-x: hidden;
        }

        /* Reader Content */
        .reader-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1.5rem 8rem;
        }

        .manuscript-content {
            {!! $css !!}
            padding: 0; /* Override the 5% from $css for this web view */
            color: var(--text-color);
        }
        
        /* Force color override for text elements injected by Markdown/CSS */
        .manuscript-content h1,
        .manuscript-content h2,
        .manuscript-content h3,
        .manuscript-content p,
        .manuscript-content blockquote,
        .manuscript-content .divider {
            color: var(--text-color) !important;
        }

        .section-view {
            display: none;
            animation: fadeIn 0.4s ease;
        }
        .section-view.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Kindle-like Navbar (Floating) */
        .reader-nav {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--sidebar-bg);
            border: 1px solid var(--border-color);
            border-radius: 50px;
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            z-index: 1000;
            gap: 1rem;
        }

        .nav-btn {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .nav-btn:hover { background: rgba(0,0,0,0.05); }
        [data-theme="dark"] .nav-btn:hover { background: rgba(255,255,255,0.05); }

        /* Sidebar TOC */
        .toc-sidebar {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100%;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            z-index: 2000;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 2rem 1rem;
            overflow-y: auto;
        }
        .toc-sidebar.open { left: 0; }

        .toc-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            margin-bottom: 2rem;
            padding-left: 0.5rem;
            opacity: 0.6;
        }

        .toc-list { list-style: none; padding: 0; margin: 0; }
        .toc-item {
            padding: 0.8rem 1rem;
            cursor: pointer;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: background 0.2s;
            margin-bottom: 2px;
        }
        .toc-item:hover { background: rgba(0,0,0,0.05); }
        .toc-item.active { 
            background: rgba(0,0,0,0.1); 
            font-weight: 600;
        }
        [data-theme="dark"] .toc-item:hover { background: rgba(255,255,255,0.05); }
        [data-theme="dark"] .toc-item.active { background: rgba(255,255,255,0.1); }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 1500;
            display: none;
        }
        .overlay.open { display: block; }

        /* Theme Switcher in Nav */
        .theme-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .theme-dot.active { border-color: var(--text-color); }
        .dot-light { background: #ffffff; border: 1px solid #ddd; }
        .dot-sepia { background: #f4ecd8; }
        .dot-dark { background: #121212; }
        
        .theme-switcher {
            display: flex;
            gap: 0.5rem;
            margin: 0 0.8rem;
        }

        .progress-bar {
            height: 4px;
            background: var(--border-color);
            position: fixed;
            top: 0;
            left: 0;
            width: 0%;
            transition: width 0.2s;
            z-index: 3000;
        }

        @media (max-width: 600px) {
            .reader-nav { width: 90%; gap: 0.5rem; }
            .toc-sidebar { width: 80%; }
        }
    </style>
</head>
<body data-theme="light">
    <div class="progress-bar" id="progress"></div>
    
    <div class="toc-sidebar" id="sidebar">
        <div class="toc-title">Conteúdo</div>
        <ul class="toc-list">
            <li class="toc-item" onclick="goToSection(0)" data-index="0">Capa</li>
            <li class="toc-item" onclick="goToSection(1)" data-index="1">Folha de Rosto</li>
            @foreach($manuscript as $index => $item)
                <li class="toc-item" onclick="goToSection({{ $index + 2 }})" data-index="{{ $index + 2 }}">
                    {{ $item['title'] }}
                </li>
            @endforeach
            @if($appendix->count() > 0)
                <!-- The appendix will be the last element (totalSections - 1) -->
                <li class="toc-item" onclick="goToSection('appendix')" data-index="appendix">
                    Apêndice
                </li>
            @endif
        </ul>
    </div>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <div class="reader-container">
        <div class="manuscript-content">
            <!-- Cover View -->
            <div class="section-view text-center" id="section-cover" style="padding-top: 5vh; min-height: 80vh; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                @if($project->cover_image_uuid)
                    @php
                        $coverItem = \App\Models\GalleryItem::where('uuid', $project->cover_image_uuid)->first();
                        $base64 = null;
                        if ($coverItem) {
                            $filePath = ltrim($coverItem->file_path, '/');
                            $coverPath = storage_path("app/{$filePath}");
                            if (file_exists($coverPath)) {
                                $data = file_get_contents($coverPath);
                                $base64 = 'data:image/' . pathinfo($coverPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($data);
                            }
                        }
                    @endphp
                    @if($base64)
                        <img src="{{ $base64 }}" style="max-width: 100%; max-height: 70vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.1);" alt="Capa de {{ $project->name }}">
                    @endif
                @endif
            </div>

            <!-- Folha de Rosto -->
            <div class="section-view text-center" id="section-title" style="padding-top: 15vh; min-height: 80vh;">
                <h1 style="font-size: 3rem; margin-bottom: 0.5rem; font-weight: 800;">{{ $project->name }}</h1>
                <h3 style="font-size: 1.5rem; color: var(--text-color); opacity: 0.8; margin-bottom: 4rem;">{{ $project->author ?: 'Autor Desconhecido' }}</h3>
                
                <div style="margin-top: 6rem; opacity: 0.8; line-height: 1.6;">
                    @if($project->publisher)<p style="margin-bottom: 0;"><strong>{{ $project->publisher }}</strong></p>@endif
                    @if($project->publication_date)<p>{{ $project->publication_date }}</p>@endif
                </div>
                
                <div style="margin-top: 3rem; font-size: 0.85rem; border-top: 1px solid rgba(128,128,128,0.2); padding-top: 1.5rem; max-width: 60%; margin-left: auto; margin-right: auto; opacity: 0.6;">
                    @if($project->isbn)<p style="margin-bottom: 0;"><strong>ISBN:</strong> {{ $project->isbn }}</p>@endif
                    @if($project->copyright_info)<p>{!! nl2br(e($project->copyright_info)) !!}</p>@endif
                </div>
            </div>
            @foreach($manuscript as $index => $item)
                <div class="section-view" id="section-{{ $index }}">
                    <h1>{{ $item['title'] }}</h1>
                    {!! $item['content'] !!}
                </div>
            @endforeach

            @if($appendix->count() > 0)
                <div class="section-view" id="section-appendix">
                    <h1>Apêndice: Worldbuilding</h1>
                    @foreach($appendix as $card)
                        <div class="appendix-item">
                            <h2>{{ $card['title'] }}</h2>
                            <p><strong>Categoria:</strong> {{ $card['type'] }}</p>
                            {!! $card['content'] !!}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="reader-nav">
        <button class="nav-btn" onclick="toggleSidebar()" title="Sumário">
            <i class="bi bi-list"></i>
        </button>
        <button class="nav-btn" onclick="prevSection()" title="Anterior">
            <i class="bi bi-chevron-left"></i>
        </button>
        
        <div class="theme-switcher">
            <div class="theme-dot dot-light active" onclick="setTheme('light')" title="Claro"></div>
            <div class="theme-dot dot-sepia" onclick="setTheme('sepia')" title="Sépia"></div>
            <div class="theme-dot dot-dark" onclick="setTheme('dark')" title="Escuro"></div>
        </div>

        <button class="nav-btn" onclick="nextSection()" title="Próximo">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>

    <script>
        let currentSection = 0;
        const sections = document.querySelectorAll('.section-view');
        const tocItems = document.querySelectorAll('.toc-item');
        const totalSections = sections.length;

        function goToSection(index) {
            if (index === 'appendix') {
                currentSection = totalSections - 1;
            } else {
                currentSection = parseInt(index);
            }
            updateView();
            toggleSidebar(false);
            window.scrollTo(0, 0);
        }

        function nextSection() {
            if (currentSection < totalSections - 1) {
                currentSection++;
                updateView();
                window.scrollTo(0, 0);
            }
        }

        function prevSection() {
            if (currentSection > 0) {
                currentSection--;
                updateView();
                window.scrollTo(0, 0);
            }
        }

        function updateView() {
            sections.forEach((s, idx) => {
                s.classList.toggle('active', idx === currentSection);
            });
            tocItems.forEach((item, idx) => {
                const itemIdx = item.getAttribute('data-index');
                const isActive = (itemIdx === 'appendix' && currentSection === totalSections - 1) || (parseInt(itemIdx) === currentSection);
                item.classList.toggle('active', isActive);
            });
            
            const progress = ((currentSection + 1) / totalSections) * 100;
            document.getElementById('progress').style.width = progress + '%';
        }

        function toggleSidebar(force) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const nextState = force !== undefined ? force : !sidebar.classList.contains('open');
            
            sidebar.classList.toggle('open', nextState);
            overlay.classList.toggle('open', nextState);
        }

        function setTheme(theme) {
            document.body.setAttribute('data-theme', theme);
            document.querySelectorAll('.theme-dot').forEach(dot => {
                dot.classList.toggle('active', dot.classList.contains('dot-' + theme));
            });
            localStorage.setItem('reader-theme', theme);
        }

        // Init
        const savedTheme = localStorage.getItem('reader-theme') || 'light';
        setTheme(savedTheme);
        updateView();

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight') nextSection();
            if (e.key === 'ArrowLeft') prevSection();
            if (e.key === 'm' || e.key === 'M') toggleSidebar();
        });
    </script>
</body>
</html>
