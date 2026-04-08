<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ManuscriptItem;
use App\Models\Card;
use App\Models\GalleryItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;
use Parsedown;
use Barryvdh\DomPDF\Facade\Pdf;

class ExporterService
{
    protected $project;
    protected $parsedown;
    protected $baseExportPath;

    public function __construct(Project $project)
    {
        $this->project        = $project;
        $this->parsedown      = new Parsedown();
        $this->baseExportPath = "projects/{$this->project->uuid}/exports";
        // Note: ProjectContextMiddleware already calls switchToProject() before the
        // controller, so ManuscriptItem / Card use the correct sqlite_project connection.
    }

    /**
     * Read a manuscript .md file content by its item UUID.
     */
    protected function getManuscriptContent(string $uuid): string
    {
        $path = "projects/{$this->project->uuid}/manuscript/{$uuid}.md";
        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->get($path);
        }
        return '';
    }

    /**
     * Generate the ePub file.
     */
    public function generateEpub(): string
    {
        $safeName = Str::slug($this->project->name);
        $fileName = "{$safeName}_Kindle.epub";
        $epubDir  = Storage::disk('public')->path($this->baseExportPath);
        $epubFile = "{$epubDir}/{$fileName}";

        if (!is_dir($epubDir)) {
            mkdir($epubDir, 0755, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($epubFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Não foi possível criar o arquivo ePub.");
        }

        // 1. Mimetype — must be first and uncompressed (EPUB spec)
        $zip->addFromString('mimetype', 'application/epub+zip');
        $zip->setCompressionName('mimetype', ZipArchive::CM_STORE);

        // 2. META-INF/container.xml
        $container = '<?xml version="1.0" encoding="UTF-8"?>
<container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
  <rootfiles>
    <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
  </rootfiles>
</container>';
        $zip->addFromString('META-INF/container.xml', $container);

        // 3. Build OEBPS structure
        $this->buildOebps($zip);

        $zip->close();
        return $this->getPublicUrl($fileName);
    }

    /**
     * Generate PDF file.
     */
    public function generatePdf(): string
    {
        $data = $this->prepareFullManuscriptData();
        $pdf  = Pdf::loadView('exports.pdf', $data);

        $safeName = Str::slug($this->project->name);
        $fileName = "{$safeName}.pdf";
        $dir      = Storage::disk('public')->path($this->baseExportPath);
        $filePath = "{$dir}/{$fileName}";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf->save($filePath);
        return $this->getPublicUrl($fileName);
    }

    /**
     * Generate HTML file (Standalone Reader).
     */
    public function generateHtml(): string
    {
        $data     = $this->prepareFullManuscriptData();
        $html     = view('exports.html', $data)->render();

        $safeName = Str::slug($this->project->name);
        $fileName = "{$safeName}_Reader.html";
        $dir      = Storage::disk('public')->path($this->baseExportPath);
        $filePath = "{$dir}/{$fileName}";

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($filePath, $html);
        return $this->getPublicUrl($fileName);
    }

    /**
     * Return the public URL for a given export file.
     */
    public function getPublicUrl(string $fileName): string
    {
        return Storage::disk('public')->url("{$this->baseExportPath}/{$fileName}");
    }

    /**
     * List existing exports with their public URLs and timestamps.
     */
    public function getExistingExports(): array
    {
        $safeName = Str::slug($this->project->name);
        $files = [
            'epub' => "{$safeName}_Kindle.epub",
            'pdf'  => "{$safeName}.pdf",
            'html' => "{$safeName}_Reader.html",
        ];

        $results = [];
        foreach ($files as $type => $fileName) {
            $path = "{$this->baseExportPath}/{$fileName}";
            if (Storage::disk('public')->exists($path)) {
                $results[$type] = [
                    'url'       => $this->getPublicUrl($fileName),
                    'timestamp' => Storage::disk('public')->lastModified($path),
                ];
            }
        }
        return $results;
    }

    /**
     * Prepare the manuscript data for PDF/HTML templates.
     */
    protected function prepareFullManuscriptData(): array
    {
        $sections = ManuscriptItem::whereIn('type', ['section', 'chapter', 'scene'])
            ->orderBy('order')
            ->get();

        $manuscript = $sections->map(function ($section) {
            $raw = $this->getManuscriptContent($section->uuid);
            return [
                'title'   => $section->title,
                'content' => $this->parseMarkdownToHtml($raw),
                'type'    => $section->type,
                'uuid'    => $section->uuid,
            ];
        });

        $cards    = Card::orderBy('title')->get();
        $appendix = $cards->map(function ($card) {
            return [
                'title'   => $card->title,
                'type'    => $card->type,
                'content' => $this->parseMarkdownToHtml($card->content ?? ''),
                'uuid'    => $card->uuid,
            ];
        });

        return [
            'project'    => $this->project,
            'manuscript' => $manuscript,
            'appendix'   => $appendix,
            'css'        => $this->getGlobalStyles(),
        ];
    }

    /**
     * Build OEBPS structure inside the ZIP.
     */
    protected function buildOebps(ZipArchive $zip): void
    {
        $cf       = 'OEBPS'; // content folder alias
        $manifest = [];
        $spine    = [];

        // CSS
        $zip->addFromString("{$cf}/style.css", $this->getGlobalStyles());
        $manifest[] = '<item id="style" href="style.css" media-type="text/css"/>';

        // Cover image
        if ($this->project->cover_image_uuid) {
            $coverItem = GalleryItem::where('uuid', $this->project->cover_image_uuid)->first();
            if ($coverItem) {
                // Remove leading / if exists
                $filePath = ltrim($coverItem->file_path, '/');
                $coverPath = storage_path("app/{$filePath}");
                if (file_exists($coverPath)) {
                    $zip->addFile($coverPath, "{$cf}/cover.jpg");
                    $manifest[] = '<item id="cover-image" href="cover.jpg" media-type="image/jpeg" properties="cover-image"/>';

                    $coverHtml = '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Capa</title><style>body{margin:0;padding:0;text-align:center}img{width:100%;height:100%;object-fit:contain}</style></head>
<body><img src="cover.jpg" alt="Cover"/></body>
</html>';
                    $zip->addFromString("{$cf}/cover.xhtml", $coverHtml);
                    $manifest[] = '<item id="cover" href="cover.xhtml" media-type="application/xhtml+xml"/>';
                    array_unshift($spine, 'cover');
                }
            }
        }

        // Folha de Rosto (Title page)
        $titleHtml = '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Folha de Rosto</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
<body class="page-break">
<div class="title-page" style="text-align:center;margin-top:5em">
    <h1 style="font-size:2.5em;margin-bottom:0.2em;">' . htmlspecialchars($this->project->name) . '</h1>
    <h3 style="font-size:1.2em;color:#555;margin-bottom:3em;">' . ($this->project->author ? htmlspecialchars($this->project->author) : 'Autor Desconhecido') . '</h3>
    <div style="margin-top:4em;font-size:0.9em;color:#666;">
        ' . ($this->project->publisher ? '<p><strong>' . htmlspecialchars($this->project->publisher) . '</strong></p>' : '') . '
        ' . ($this->project->publication_date ? '<p>' . htmlspecialchars($this->project->publication_date) . '</p>' : '') . '
    </div>
    <div style="margin-top:2em;font-size:0.8em;text-align:center;border-top:1px solid #ccc;padding-top:1em;width:60%;margin-left:auto;margin-right:auto;">
        ' . ($this->project->isbn ? '<p>ISBN: ' . htmlspecialchars($this->project->isbn) . '</p>' : '') . '
        ' . ($this->project->copyright_info ? '<p>' . nl2br(htmlspecialchars($this->project->copyright_info)) . '</p>' : '') . '
    </div>
</div>
</body>
</html>';
        $zip->addFromString("{$cf}/title.xhtml", $titleHtml);
        $manifest[] = '<item id="title" href="title.xhtml" media-type="application/xhtml+xml"/>';
        $spine[]    = 'title';

        // Manuscript sections
        $sections = ManuscriptItem::whereIn('type', ['section', 'chapter', 'scene'])
            ->orderBy('order')
            ->get();

        foreach ($sections as $index => $section) {
            $id      = "section_{$index}";
            $raw     = $this->getManuscriptContent($section->uuid);
            $content = $this->parseMarkdownForEpub($raw, $zip, $manifest);

            $sectionHtml = '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>' . htmlspecialchars($section->title) . '</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
<body class="page-break"><h1>' . htmlspecialchars($section->title) . '</h1>' . $content . '</body>
</html>';
            $zip->addFromString("{$cf}/{$id}.xhtml", $sectionHtml);
            $manifest[] = '<item id="' . $id . '" href="' . $id . '.xhtml" media-type="application/xhtml+xml"/>';
            $spine[]    = $id;
        }

        // Appendix (Worldbuilding cards)
        $cards = Card::orderBy('title')->get();
        if ($cards->count() > 0) {
            $appendixContent = '<h1>Apêndice: Worldbuilding</h1>';
            foreach ($cards as $card) {
                $appendixContent .= '<div class="appendix-item">';
                $appendixContent .= '<h2>' . htmlspecialchars($card->title) . '</h2>';
                $appendixContent .= '<p><strong>Categoria:</strong> ' . htmlspecialchars($card->type) . '</p>';
                $appendixContent .= $this->parseMarkdownForEpub($card->content ?? '', $zip, $manifest);
                $appendixContent .= '</div>';
            }
            $appendixHtml = '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>Apêndice</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
<body class="page-break">' . $appendixContent . '</body>
</html>';
            $zip->addFromString("{$cf}/appendix.xhtml", $appendixHtml);
            $manifest[] = '<item id="appendix" href="appendix.xhtml" media-type="application/xhtml+xml"/>';
            $spine[]    = 'appendix';
        }

        // EPUB3 Navigation (nav.xhtml)
        $navItems = '';
        foreach ($sections as $index => $section) {
            $navItems .= '<li><a href="section_' . $index . '.xhtml">' . htmlspecialchars($section->title) . '</a></li>';
        }
        $navHtml = '<?xml version="1.0" encoding="UTF-8"?>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head><title>Sumário</title></head>
<body>
  <nav epub:type="toc" id="toc">
    <h1>Sumário</h1>
    <ol>' . $navItems . '</ol>
  </nav>
</body>
</html>';
        $zip->addFromString("{$cf}/nav.xhtml", $navHtml);
        $manifest[] = '<item id="nav" href="nav.xhtml" media-type="application/xhtml+xml" properties="nav"/>';

        // EPUB2 NCX (backward compatibility — e.g., older Kindles)
        $ncxPoints = '';
        foreach ($sections as $index => $section) {
            $order = $index + 1;
            $ncxPoints .= '<navPoint id="nav-' . $order . '" playOrder="' . $order . '">'
                . '<navLabel><text>' . htmlspecialchars($section->title) . '</text></navLabel>'
                . '<content src="section_' . $index . '.xhtml"/>'
                . '</navPoint>';
        }
        $ncxUid = (string) Str::uuid();
        $ncx = '<?xml version="1.0" encoding="UTF-8"?>
<ncx xmlns="http://www.daisy.org/z3986/2005/ncx/" version="2005-1">
  <head>
    <meta name="dtb:uid" content="urn:uuid:' . $ncxUid . '"/>
    <meta name="dtb:depth" content="1"/>
    <meta name="dtb:totalPageCount" content="0"/>
    <meta name="dtb:maxPageNumber" content="0"/>
  </head>
  <docTitle><text>' . htmlspecialchars($this->project->name) . '</text></docTitle>
  <navMap>' . $ncxPoints . '</navMap>
</ncx>';
        $zip->addFromString("{$cf}/toc.ncx", $ncx);
        $manifest[] = '<item id="ncx" href="toc.ncx" media-type="application/x-dtbncx+xml"/>';

        // OPF package document
        $bookUid = (string) Str::uuid();
        $manifestXml = implode("\n    ", array_unique($manifest));
        $spineXml    = implode("\n    ", array_map(fn($id) => '<itemref idref="' . $id . '"/>', $spine));

        $opf = '<?xml version="1.0" encoding="UTF-8"?>
<package xmlns="http://www.idpf.org/2007/opf" unique-identifier="book-id" version="3.0">
  <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
    <dc:identifier id="book-id">urn:uuid:' . $bookUid . '</dc:identifier>
    <dc:title>' . htmlspecialchars($this->project->name) . '</dc:title>
    <dc:language>' . htmlspecialchars($this->project->language ?? 'pt-BR') . '</dc:language>
    ' . ($this->project->author ? '<dc:creator>' . htmlspecialchars($this->project->author) . '</dc:creator>' : '') . '
    ' . ($this->project->publisher ? '<dc:publisher>' . htmlspecialchars($this->project->publisher) . '</dc:publisher>' : '') . '
    ' . ($this->project->publication_date ? '<dc:date>' . htmlspecialchars($this->project->publication_date) . '</dc:date>' : '') . '
    ' . ($this->project->isbn ? '<dc:identifier id="isbn">urn:isbn:' . htmlspecialchars($this->project->isbn) . '</dc:identifier>' : '') . '
    <meta property="dcterms:modified">' . gmdate('Y-m-d\TH:i:s\Z') . '</meta>
  </metadata>
  <manifest>
    ' . $manifestXml . '
  </manifest>
  <spine toc="ncx">
    ' . $spineXml . '
  </spine>
</package>';
        $zip->addFromString("{$cf}/content.opf", $opf);
    }

    /**
     * Kindle-style CSS.
     */
    protected function getGlobalStyles(): string
    {
        return "body { font-family: 'Georgia', 'Palatino', serif; line-height: 1.7; text-align: justify; padding: 5%; color: #1a1a1a; }
h1, h2, h3 { text-align: center; margin-top: 1.5em; margin-bottom: 1em; color: #000; font-weight: bold; }
h1 { font-size: 2em; }
h2 { font-size: 1.5em; }
p { margin-bottom: 0.8em; text-indent: 1.5em; }
p:first-of-type, h1 + p, h2 + p, h3 + p { text-indent: 0; }
img { max-width: 100%; height: auto; display: block; margin: 2em auto; }
blockquote { font-style: italic; text-align: center; margin: 2em 10%; padding: 0; border: none; }
hr { border: 0; border-top: 1px solid #ddd; margin: 2.5em auto; width: 30%; }
.divider { text-align: center; margin: 2em 0; font-size: 1.2em; letter-spacing: 0.5em; color: #888; }
.page-break { page-break-before: always; }
.appendix-item { margin-bottom: 4em; border-top: 1px solid #f0f0f0; padding-top: 2em; font-size: 0.8em; text-align: left; }
.appendix-item h1, .appendix-item h2, .appendix-item h3 { text-align: left; }
.appendix-item p { text-indent: 0; }";
    }

    /**
     * Parse Markdown to HTML for PDF/HTML exports. Images become base64.
     */
    protected function parseMarkdownToHtml(string $text): string
    {
        $text = str_replace('---', '<div class="divider">***</div>', $text);

        // Inline images as base64
        $text = preg_replace_callback(
            '/!\[(.*?)\]\(\/projects\/[^\/]+\/gallery\/([^\/]+)\/image[^)]*\)/',
            function ($matches) {
                [$all, $alt, $imageUuid] = $matches;
                $item = GalleryItem::where('uuid', $imageUuid)->first();
                if ($item) {
                    $filePath = ltrim($item->file_path, '/');
                    $path = storage_path("app/{$filePath}");
                    if (file_exists($path)) {
                        $ext    = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                        $mime   = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : "image/{$ext}";
                        $b64    = base64_encode(file_get_contents($path));
                        return '<img src="data:' . $mime . ';base64,' . $b64 . '" alt="' . htmlspecialchars($alt) . '" />';
                    }
                }
                return '';
            },
            $text
        );

        return $this->parsedown->text($text);
    }

    /**
     * Parse Markdown to XHTML for EPUB. Images are embedded into the zip.
     */
    protected function parseMarkdownForEpub(string $text, ZipArchive $zip, array &$manifest): string
    {
        $text = str_replace('---', '<div class="divider">***</div>', $text);

        $text = preg_replace_callback(
            '/!\[(.*?)\]\(\/projects\/[^\/]+\/gallery\/([^\/]+)\/image[^)]*\)/',
            function ($matches) use ($zip, &$manifest) {
                [$all, $alt, $imageUuid] = $matches;
                $item = GalleryItem::where('uuid', $imageUuid)->first();
                if ($item) {
                    $filePath = ltrim($item->file_path, '/');
                    $path = storage_path("app/{$filePath}");
                    if (file_exists($path)) {
                        $ext      = strtolower(pathinfo($item->file_path, PATHINFO_EXTENSION));
                        $zipName  = "img_{$imageUuid}.{$ext}";
                        $mimeType = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : "image/{$ext}";
                        $zip->addFile($path, "OEBPS/{$zipName}");
                        $manifest[] = '<item id="img_' . $imageUuid . '" href="' . $zipName . '" media-type="' . $mimeType . '"/>';
                        return '<img src="' . $zipName . '" alt="' . htmlspecialchars($alt) . '" />';
                    }
                }
                return '';
            },
            $text
        );

        return $this->parsedown->text($text);
    }
}
