<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ManuscriptItem;
use App\Models\Card;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ExporterService
{
    protected $project;
    protected $tempDir;

    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->tempDir = storage_path("app/tmp/export_{$this->project->uuid}_" . time());
        
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    /**
     * Generate the ePub file.
     */
    public function generateEpub()
    {
        $epubFile = storage_path("app/tmp/{$this->project->name}_Kindle.epub");
        $zip = new ZipArchive();

        if ($zip->open($epubFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Não foi possível criar o arquivo ePub.");
        }

        // 1. Mimetype (must be first and uncompressed)
        $zip->addFromString('mimetype', 'application/epub+zip');

        // 2. META-INF/container.xml
        $container = '<?xml version="1.0" encoding="UTF-8"?>
        <container version="1.0" xmlns="urn:oasis:names:tc:opendocument:xmlns:container">
            <rootfiles>
                <rootfile full-path="OEBPS/content.opf" media-type="application/oebps-package+xml"/>
            </rootfiles>
        </container>';
        $zip->addFromString('META-INF/container.xml', $container);

        // 3. Content.opf & Resources
        $this->buildOebps($zip);

        $zip->close();
        return $epubFile;
    }

    protected function buildOebps($zip)
    {
        $contentFolder = 'OEBPS';
        $zip->addEmptyDir($contentFolder);
        
        $manifest = [];
        $spine = [];
        $items = [];

        // Add CSS
        $css = "body { font-family: serif; line-height: 1.6; text-align: justify; padding: 5%; }
                h1, h2, h3 { text-align: center; margin-top: 2em; color: #333; }
                p { margin-bottom: 1em; text-indent: 1.5em; }
                img { max-width: 100%; height: auto; display: block; margin: 2em auto; }
                blockquote { font-style: italic; border-left: 4px solid #ccc; padding-left: 1em; margin: 2em 0; }
                hr { border: 0; border-top: 1px solid #ccc; margin: 3em 0; }
                .page-break { page-break-before: always; }
                .appendix-item { margin-bottom: 4em; border-top: 1px solid #eee; padding-top: 2em; }";
        $zip->addFromString("$contentFolder/style.css", $css);
        $manifest[] = '<item id="style" href="style.css" media-type="text/css"/>';

        // Cover
        if ($this->project->cover_image_uuid) {
            $coverItem = \App\Models\GalleryItem::where('uuid', $this->project->cover_image_uuid)->first();
            if ($coverItem) {
                $coverPath = storage_path("app/projects/{$this->project->uuid}/gallery/{$coverItem->filename}");
                if (file_exists($coverPath)) {
                    $zip->addFile($coverPath, "$contentFolder/cover.jpg");
                    $manifest[] = '<item id="cover-image" href="cover.jpg" media-type="image/jpeg" properties="cover-image"/>';
                    
                    $coverHtml = '<?xml version="1.0" encoding="UTF-8"?>
                    <html xmlns="http://www.w3.org/1999/xhtml">
                    <head><title>Capa</title><style>body { margin: 0; padding: 0; text-align: center; } img { width: 100%; height: 100%; }</style></head>
                    <body><img src="cover.jpg" alt="Cover"/></body></html>';
                    $zip->addFromString("$contentFolder/cover.xhtml", $coverHtml);
                    $manifest[] = '<item id="cover" href="cover.xhtml" media-type="application/xhtml+xml"/>';
                    array_unshift($spine, 'cover');
                }
            }
        }

        // Title Page
        $titleHtml = '<?xml version="1.0" encoding="UTF-8"?>
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head><title>Título</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
        <body><div style="text-align: center; margin-top: 5em;"><h1>' . htmlspecialchars($this->project->name) . '</h1><p>Gerado por Runaris Ghost</p></div></body></html>';
        $zip->addFromString("$contentFolder/title.xhtml", $titleHtml);
        $manifest[] = '<item id="title" href="title.xhtml" media-type="application/xhtml+xml"/>';
        $spine[] = 'title';

        // Manuscript Content
        $sections = ManuscriptItem::where('project_uuid', $this->project->uuid)
            ->whereIn('type', ['chapter', 'scene'])
            ->orderBy('order')
            ->get();

        foreach ($sections as $index => $section) {
            $id = "section_$index";
            $content = $this->parseMarkdown($section->content ?? '');
            
            $sectionHtml = '<?xml version="1.0" encoding="UTF-8"?>
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head><title>' . htmlspecialchars($section->title) . '</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
            <body class="page-break"><h1>' . htmlspecialchars($section->title) . '</h1>' . $content . '</body></html>';
            
            $zip->addFromString("$contentFolder/$id.xhtml", $sectionHtml);
            $manifest[] = '<item id="' . $id . '" href="' . $id . '.xhtml" media-type="application/xhtml+xml"/>';
            $spine[] = $id;
        }

        // Appendix (Worldbuilding)
        $cards = Card::where('project_uuid', $this->project->uuid)->orderBy('title')->get();
        if ($cards->count() > 0) {
            $id = "appendix";
            $appendixContent = "<h1>Apêndice: Worldbuilding</h1>";
            
            foreach ($cards as $card) {
                $appendixContent .= '<div class="appendix-item">';
                $appendixContent .= '<h2>' . htmlspecialchars($card->title) . '</h2>';
                $appendixContent .= '<p><strong>Categoria:</strong> ' . htmlspecialchars($card->type) . '</p>';
                $appendixContent .= $this->parseMarkdown($card->content ?? '');
                $appendixContent .= '</div>';
            }

            $appendixHtml = '<?xml version="1.0" encoding="UTF-8"?>
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head><title>Apêndice</title><link rel="stylesheet" type="text/css" href="style.css"/></head>
            <body class="page-break">' . $appendixContent . '</body></html>';
            
            $zip->addFromString("$contentFolder/$id.xhtml", $appendixHtml);
            $manifest[] = '<item id="' . $id . '" href="' . $id . '.xhtml" media-type="application/xhtml+xml"/>';
            $spine[] = $id;
        }

        // Build OPF
        $opf = '<?xml version="1.0" encoding="UTF-8"?>
        <package xmlns="http://www.idpf.org/2007/opf" unique-identifier="pub-id" version="3.0">
            <metadata xmlns:dc="http://purl.org/dc/elements/1.1/">
                <dc:identifier id="pub-id">urn:uuid:' . Str::uuid() . '</dc:identifier>
                <dc:title>' . htmlspecialchars($this->project->name) . '</dc:title>
                <dc:language>pt-BR</dc:language>
                <meta property="dcterms:modified">' . gmdate('Y-m-d\TH:i:s\Z') . '</meta>
            </metadata>
            <manifest>' . implode("\n", $manifest) . '</manifest>
            <spine>' . implode("\n", array_map(fn($id) => '<itemref idref="' . $id . '"/>', $spine)) . '</spine>
        </package>';
        $zip->addFromString("$contentFolder/content.opf", $opf);
    }

    protected function parseMarkdown($text)
    {
        // Simple Markdown parser (for a more complete one, we could use Parsedown)
        // Convert headers
        $text = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $text);
        
        // Convert Bold/Italic
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);

        // Convert Images (Gallery Images)
        // Pattern: ![alt](/projects/uuid/gallery/image-uuid/image)
        $text = preg_replace('/\!\[(.*?)\]\(\/projects\/.*?\/gallery\/(.*?)\/image\)/', '<img src="gallery_$2.jpg" alt="$1" />', $text);

        // Handle Paragraphs
        $lines = explode("\n", $text);
        $result = "";
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if (strpos($line, '<h') === 0 || strpos($line, '<img') === 0) {
                $result .= $line . "\n";
            } else {
                $result .= "<p>$line</p>\n";
            }
        }
        
        return $result;
    }
}
