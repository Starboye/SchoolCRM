async function loadSidebar() {
  const container = document.getElementById("sidebar-container");
  if (!container) return;

  const meta = document.querySelector('meta[name="app-base"]');
  const base = meta ? meta.getAttribute("content") || "" : "";
  const prefix = base.replace(/\/$/, "");
  const sidebarUrl = (prefix ? prefix : "") + "/teacher/includes/teacher_sidebar.php";

  try {
    const res = await fetch(sidebarUrl);
    if (!res.ok) throw new Error("Sidebar request failed");

    container.innerHTML = await res.text();
  } catch (e) {
    console.error("Sidebar failed to load.", e);
  }
}

document.addEventListener("DOMContentLoaded", loadSidebar);
