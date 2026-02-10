async function loadSidebar() {
  const container = document.getElementById("sidebar-container");
  if (!container) return;

  // Try absolute + relative paths
  const paths = [
    "/Asimos/includes/sidebar.html",
    "includes/sidebar.html",
    "../includes/sidebar.html",
    "../../includes/sidebar.html"
  ];

  for (const p of paths) {
    try {
      const res = await fetch(p);
      if (res.ok) {
        const html = await res.text();
        if (html.trim().length > 0) {
          container.innerHTML = html;
          return;
        }
      }
    } catch(e) { /* ignore & continue */ }
  }

  console.error("Sidebar failed to load.");
}

document.addEventListener("DOMContentLoaded", loadSidebar);
