import os
import re
import json

base_dir = '/Volumes/Arquivos/projetos-git/Runaris-Ghost'
design_dir = os.path.join(base_dir, 'design')
views_dir = os.path.join(base_dir, 'resources', 'views')
public_dir = os.path.join(base_dir, 'public')

os.makedirs(design_dir, exist_ok=True)

# Load Vite manifest
manifest_path = os.path.join(public_dir, 'build', 'manifest.json')
manifest = {}
if os.path.exists(manifest_path):
    with open(manifest_path, 'r') as f:
        raw = json.load(f)
    for key, val in raw.items():
        manifest[key] = '../public/build/' + val['file']

def resolve_vite_asset(match):
    inner = match.group(1)
    if inner in manifest:
        return manifest[inner]
    return match.group(0)

def strip_blade_directives(content):
    content = re.sub(r'@php\s*.*?@endphp\s*', '', content, flags=re.DOTALL)
    content = re.sub(r'@json\(.*?\)', '', content)
    content = re.sub(r'@csrf', '', content)
    content = re.sub(r'@if\(.*?\)', '', content)
    content = re.sub(r'@else', '', content)
    content = re.sub(r'@endif', '', content)
    content = re.sub(r'@foreach\(.*?\)', '', content)
    content = re.sub(r'@endforeach', '', content)
    content = re.sub(r'@extends\(.*?\)', '', content)
    content = re.sub(r'@section\(.*?\)', '', content)
    content = re.sub(r'@endsection', '', content)
    content = re.sub(r'@yield\(.*?\)', '', content)
    content = re.sub(r'@lang\(.*?\)', 'Mock Data', content)
    content = re.sub(r"\{\{ asset\('(.*?)'\) \}\}", r'../public/\1', content)
    content = re.sub(r"\{\{ Vite::asset\('(.*?)'\) \}\}", resolve_vite_asset, content)
    content = re.sub(r'\{\{ url\(.*?\) \}\}', '#', content)
    content = re.sub(r'\{\{ route\(.*?\) \}\}', '#', content)
    content = re.sub(r'\{\{ .*? \}\}', 'Mock Data', content)
    content = re.sub(r'\{!! .*? !!\}', 'Mock Data', content)
    return content


# ============================================================
# LAYOUT (for views that extend app.blade.php)
# ============================================================
with open(os.path.join(views_dir, 'layouts', 'app.blade.php'), 'r', encoding='utf-8') as f:
    app_layout = f.read()

app_layout = app_layout.replace(
    "@vite(['resources/css/app.css', 'resources/js/app.js'])",
    '<link rel="stylesheet" href="../public/build/assets/app-CVjFDuoy.css">\n    <script src="../public/build/assets/app-BvuF79Bt.js"></script>'
)
app_layout = app_layout.replace("{{ asset('assets/images/icon.png') }}", "../public/assets/images/icon.png")
app_layout = app_layout.replace("{{ asset('assets/images/lg-ghost-hz-color.svg') }}", "../public/assets/images/lg-ghost-hz-color.svg")
app_layout = app_layout.replace("{{ asset('assets/images/lg-ghost-hz-color-inverted.svg') }}", "../public/assets/images/lg-ghost-hz-color-inverted.svg")
app_layout = app_layout.replace("{{ url('/projects') }}", "projects.html")
app_layout = app_layout.replace("{{ route('logout') }}", "#")

layout_parts = app_layout.split("@yield('content')")


def process_blade(content):
    content = re.sub(r"@extends\('layouts\.app'\)", '', content)
    content = re.sub(r"@section\('title', .*?\)", '', content)
    match = re.search(r"@section\('content'\)(.*?)@endsection", content, re.DOTALL)
    if match:
        content = match.group(1)
    content = strip_blade_directives(content)
    return content


# ============================================================
# PROJECTS (extends layout)
# ============================================================
with open(os.path.join(views_dir, 'projects', 'index.blade.php'), 'r', encoding='utf-8') as f:
    projects_content = f.read()

projects_html = process_blade(projects_content)
projects_html = projects_html.replace('Mock Data', 'Viagem Estelar', 1)
projects_html = projects_html.replace('Mock Data', 'No ano de 2245, a humanidade explora o espaço...', 1)

full_projects = layout_parts[0] + projects_html + layout_parts[1]
with open(os.path.join(design_dir, 'projects.html'), 'w', encoding='utf-8') as f:
    f.write(full_projects)
print('  [OK] projects.html')


# ============================================================
# DASHBOARD (extends layout, multi-tool isolation)
# ============================================================
with open(os.path.join(views_dir, 'projects', 'dashboard.blade.php'), 'r', encoding='utf-8') as f:
    dashboard_content = f.read()

dashboard_html = process_blade(dashboard_content)

match_header = re.search(r"@section\('project-header-title'\)(.*?)@endsection", dashboard_content, re.DOTALL)
header_title = match_header.group(1) if match_header else ''
header_title = header_title.replace("{{ strtoupper($project->name) }}", 'VIAGEM ESTELAR')
full_dashboard_start = layout_parts[0].replace("@yield('project-header-title')", header_title)


def isolate_dashboard_tool(html_content, active_id):
    if active_id != 'center-editor':
        html_content = html_content.replace('id="center-editor"', 'id="center-editor" class="d-none"')
        html_content = html_content.replace('id="manuscript-tree-container"', 'id="manuscript-tree-container" class="d-none"')
        html_content = html_content.replace('id="right-panel-metadata"', 'id="right-panel-metadata" class="d-none"')

    containers = [
        'cards-grid-container',
        'gallery-container',
        'graph-container',
        'bible-container',
        'export-container',
        'backup-container',
    ]
    for c in containers:
        if c == active_id:
            html_content = html_content.replace(f'id="{c}" class="', f'id="{c}" class="active-preview ')
            html_content = re.sub(rf'id="{c}"([^>]*?)d-none', rf'id="{c}"\1', html_content)
        else:
            if 'd-none' not in html_content.split(f'id="{c}"')[1].split('>')[0]:
                html_content = html_content.replace(f'id="{c}" class="', f'id="{c}" class="d-none ')
    return html_content


tools = {
    'manuscript': 'center-editor',
    'cards': 'cards-grid-container',
    'gallery': 'gallery-container',
    'connections': 'graph-container',
    'bible': 'bible-container',
    'export': 'export-container',
    'backup': 'backup-container',
}

for tool_name, container_id in tools.items():
    isolated_html = isolate_dashboard_tool(dashboard_html, container_id)
    full_tool_html = full_dashboard_start + isolated_html + layout_parts[1]
    with open(os.path.join(design_dir, f'dashboard_{tool_name}.html'), 'w', encoding='utf-8') as f:
        f.write(full_tool_html)
    print(f'  [OK] dashboard_{tool_name}.html')


# ============================================================
# INSTALL (standalone view — no layout)
# ============================================================
def resolve_vite_entry(match):
    """Replace @vite(['resources/css/app.css']) with static CSS link."""
    css_url = manifest.get('resources/css/app.css', '../public/build/assets/app.css')
    return f'<link rel="stylesheet" href="{css_url}">'

def generate_standalone_steps(view_name, steps_range):
    """Gera HTML estático para views standalone (install, setup).
    Cria um arquivo principal + um arquivo por step.
    """
    with open(os.path.join(views_dir, view_name, 'index.blade.php'), 'r', encoding='utf-8') as f:
        content = f.read()

    content = re.sub(r"@vite\(\[.*?\]\)", resolve_vite_entry, content)
    content = strip_blade_directives(content)

    # Fix ghost-icon path
    ghost_icon = '../public/build/assets/ghost-icon-transparent-BctpTeNf.png'
    content = re.sub(
        r'src="[^"]*ghost-icon-transparent[^"]*"',
        f'src="{ghost_icon}"',
        content
    )

    # Save full version (all steps, step-0 active)
    full_path = os.path.join(design_dir, f'{view_name}.html')
    with open(full_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f'  [OK] {view_name}.html')

    # Save one file per step
    for i in steps_range:
        step_html = content.replace('class="step active"', 'class="step"')
        step_html = step_html.replace('class="step text-center active"', 'class="step text-center"')

        step_html = re.sub(rf'id="step-{i}" class="step"', rf'id="step-{i}" class="step active"', step_html)
        step_html = re.sub(rf'id="step-{i}" class="step text-center"', rf'id="step-{i}" class="step text-center active"', step_html)

        if f'id="step-{i}"' in content:
            step_path = os.path.join(design_dir, f'{view_name}_step_{i}.html')
            with open(step_path, 'w', encoding='utf-8') as f:
                f.write(step_html)
            print(f'  [OK] {view_name}_step_{i}.html')


generate_standalone_steps('install', range(7))
generate_standalone_steps('setup', range(6))


print('\nDone — all mockups regenerated.')
