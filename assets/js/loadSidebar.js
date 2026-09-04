function appBasePath() {
  const meta = document.querySelector('meta[name="app-base-path"]');
  if (meta && meta.content) {
    return meta.content.replace(/\/$/, '');
  }
  if (typeof window.APP_BASE_PATH !== 'undefined') {
    return String(window.APP_BASE_PATH).replace(/\/$/, '');
  }
  const dataEl = document.getElementById('sidebar-container');
  if (dataEl && dataEl.dataset.basePath) {
    return dataEl.dataset.basePath.replace(/\/$/, '');
  }
  return '';
}

async function loadSidebar() {
  const container = document.getElementById('sidebar-container');
  if (!container) return;

  const base = appBasePath();
  const paths = [
    base ? `${base}/includes/sidebar.html` : '',
    'includes/sidebar.html',
    '../includes/sidebar.html',
    '../../includes/sidebar.html',
  ].filter(Boolean);

  for (const path of paths) {
    try {
      const res = await fetch(path);
      if (res.ok) {
        const html = await res.text();
        if (html.trim().length > 0) {
          container.innerHTML = html;
          return;
        }
      }
    } catch (e) {
      /* try next path */
    }
  }

  console.error('Sidebar failed to load.');
}

document.addEventListener('DOMContentLoaded', loadSidebar);
