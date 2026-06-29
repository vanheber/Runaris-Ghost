import os
import re

base_dir = '/Volumes/Arquivos/projetos-git/Runaris-Ghost'
design_dir = os.path.join(base_dir, 'design')
views_dir = os.path.join(base_dir, 'resources', 'views')

# Ensure design directory exists
os.makedirs(design_dir, exist_ok=True)

with open(os.path.join(views_dir, 'layouts', 'app.blade.php'), 'r', encoding='utf-8') as f:
    app_layout = f.read()

# Replace vite directive
app_layout = app_layout.replace("@vite(['resources/css/app.css', 'resources/js/app.js'])", 
    '<link rel="stylesheet" href="../public/build/assets/app.css">\n    <script src="../public/build/assets/app.js"></script>')

app_layout = app_layout.replace("{{ asset('assets/images/icon.png') }}", "../public/assets/images/icon.png")
app_layout = app_layout.replace("{{ asset('assets/images/lg-ghost-hz-color.svg') }}", "../public/assets/images/lg-ghost-hz-color.svg")
app_layout = app_layout.replace("{{ asset('assets/images/lg-ghost-hz-color-inverted.svg') }}", "../public/assets/images/lg-ghost-hz-color-inverted.svg")
app_layout = app_layout.replace("{{ url('/projects') }}", "projects.html")
app_layout = app_layout.replace("{{ route('logout') }}", "#")

layout_parts = app_layout.split("@yield('content')")

def process_blade(content):
    content = re.sub(r"@extends\('layouts\.app'\)", "", content)
    content = re.sub(r"@section\('title', .*?\)", "", content)
    match = re.search(r"@section\('content'\)(.*?)@endsection", content, re.DOTALL)
    if match:
        content = match.group(1)
        
    content = re.sub(r"{{ url\('.*?'\) }}", "#", content)
    content = re.sub(r"{{ asset\('.*?'\) }}", "#", content)
    content = re.sub(r"@if\(.*?\)", "", content)
    content = re.sub(r"@else", "", content)
    content = re.sub(r"@endif", "", content)
    content = re.sub(r"@foreach\(.*?\)", "", content)
    content = re.sub(r"@endforeach", "", content)
    content = re.sub(r"{{ .*? }}", "Mock Data", content)
    content = re.sub(r"{!! .*? !!}", "Mock Data", content)
    content = re.sub(r"@csrf", "", content)
    return content

# PROJECTS
with open(os.path.join(views_dir, 'projects', 'index.blade.php'), 'r', encoding='utf-8') as f:
    projects_content = f.read()

projects_html = process_blade(projects_content)
projects_html = projects_html.replace('Mock Data', 'Viagem Estelar', 1)
projects_html = projects_html.replace('Mock Data', 'No ano de 2245, a humanidade explora o espaço...', 1)

full_projects = layout_parts[0] + projects_html + layout_parts[1]
with open(os.path.join(design_dir, 'projects.html'), 'w', encoding='utf-8') as f:
    f.write(full_projects)


# DASHBOARD
with open(os.path.join(views_dir, 'projects', 'dashboard.blade.php'), 'r', encoding='utf-8') as f:
    dashboard_content = f.read()

dashboard_html = process_blade(dashboard_content)

match_header = re.search(r"@section\('project-header-title'\)(.*?)@endsection", dashboard_content, re.DOTALL)
header_title = match_header.group(1) if match_header else ""
header_title = header_title.replace("{{ strtoupper($project->name) }}", "VIAGEM ESTELAR")
full_dashboard_start = layout_parts[0].replace("@yield('project-header-title')", header_title)

# Function to isolate a tool in dashboard
def isolate_dashboard_tool(html_content, active_id):
    # Hide the main editor if it's not manuscript
    if active_id != 'center-editor':
        html_content = html_content.replace('id="center-editor"', 'id="center-editor" class="d-none"')
        html_content = html_content.replace('id="manuscript-tree-container"', 'id="manuscript-tree-container" class="d-none"')
        html_content = html_content.replace('id="right-panel-metadata"', 'id="right-panel-metadata" class="d-none"')
    
    # Hide all tool containers first, then show the active one
    containers = [
        'cards-grid-container',
        'gallery-container',
        'graph-container',
        'bible-container',
        'export-container',
        'backup-container'
    ]
    for c in containers:
        if c == active_id:
            # remove d-none if present
            html_content = html_content.replace(f'id="{c}" class="', f'id="{c}" class="active-preview ')
            html_content = re.sub(rf'id="{c}"([^>]*?)d-none', rf'id="{c}"\1', html_content)
        else:
            # ensure d-none is present (they usually have it by default, but just in case)
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
    'backup': 'backup-container'
}

for tool_name, container_id in tools.items():
    isolated_html = isolate_dashboard_tool(dashboard_html, container_id)
    full_tool_html = full_dashboard_start + isolated_html + layout_parts[1]
    with open(os.path.join(design_dir, f'dashboard_{tool_name}.html'), 'w', encoding='utf-8') as f:
        f.write(full_tool_html)


# INSTALL
with open(os.path.join(views_dir, 'install', 'index.blade.php'), 'r', encoding='utf-8') as f:
    install_content = f.read()

install_content = re.sub(r"{{ asset\('(.*?)'\) }}", r"../public/\1", install_content)
install_content = re.sub(r"{{ url\('.*?'\) }}", "#", install_content)
install_content = re.sub(r"@.*?($|\n)", "", install_content)

# The installer steps use `step` and `active` classes
# `id="step-0" class="step active"`
for i in range(7):
    # For each step, replace 'class="step active"' with 'class="step"' everywhere
    step_html = install_content.replace('class="step active"', 'class="step"')
    step_html = step_html.replace('class="step text-center active"', 'class="step text-center"')
    
    # Then make the target step active
    step_html = re.sub(rf'id="step-{i}" class="step"', rf'id="step-{i}" class="step active"', step_html)
    step_html = re.sub(rf'id="step-{i}" class="step text-center"', rf'id="step-{i}" class="step text-center active"', step_html)
    
    # Only save if step exists
    if f'id="step-{i}"' in install_content:
        with open(os.path.join(design_dir, f'install_step_{i}.html'), 'w', encoding='utf-8') as f:
            f.write(step_html)

print("Separate tool/step generation complete.")
