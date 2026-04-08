<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $project->name }}</title>
    <style>
        {!! $css !!}
        
        @page {
            margin: 2cm;
        }

        .cover-page {
            text-align: center;
            page-break-after: always;
            padding-top: 5cm;
        }

        .cover-image {
            max-width: 100%;
            max-height: 15cm;
            margin-bottom: 2cm;
        }

        .manuscript-item {
            page-break-after: always;
        }

        .manuscript-item:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body>
    <!-- Cover -->
    <div class="cover-page">
        @if($project->cover_image_uuid)
            @php
                $coverItem = \App\Models\GalleryItem::where('uuid', $project->cover_image_uuid)->first();
                if ($coverItem) {
                    $filePath = ltrim($coverItem->file_path, '/');
                    $coverPath = storage_path("app/{$filePath}");
                    if (file_exists($coverPath)) {
                        $data = file_get_contents($coverPath);
                        $base64 = 'data:image/' . pathinfo($coverPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($data);
                    }
                }
            @endphp
            @if(isset($base64))
                <img src="{{ $base64 }}" class="cover-image" alt="Cover">
            @endif
        @endif
        <h1>{{ $project->name }}</h1>
        <p>Por Runaris Ghost</p>
    </div>

    <!-- Table of Contents -->
    <div class="toc-page" style="page-break-after: always;">
        <h1 style="text-align: center; margin-bottom: 1.5cm;">Sumário</h1>
        <div style="font-size: 1.2em; line-height: 1.8; margin: 0 10%;">
            @foreach($manuscript as $index => $item)
                <div><a href="#section-{{ $index }}" style="color: black; text-decoration: none; border-bottom: 1px dotted #ccc;">{{ $item['title'] }}</a></div>
            @endforeach
            @if($appendix->count() > 0)
                <div style="margin-top: 1em;"><a href="#section-appendix" style="color: black; text-decoration: none; font-weight: bold; border-bottom: 1px dotted #ccc;">Apêndice: Worldbuilding</a></div>
            @endif
        </div>
    </div>

    <!-- Manuscript -->
    @foreach($manuscript as $index => $item)
        <div class="manuscript-item" id="section-{{ $index }}">
            <h1>{{ $item['title'] }}</h1>
            {!! $item['content'] !!}
        </div>
    @endforeach

    <!-- Appendix -->
    @if($appendix->count() > 0)
        <div class="manuscript-item" id="section-appendix">
            <h1 style="page-break-before: always;">Apêndice: Worldbuilding</h1>
            @foreach($appendix as $card)
                <div class="appendix-item">
                    <h2>{{ $card['title'] }}</h2>
                    <p><strong>Categoria:</strong> {{ $card['type'] }}</p>
                    {!! $card['content'] !!}
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
