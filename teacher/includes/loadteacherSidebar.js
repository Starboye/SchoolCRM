async function loadSidebar() {
  const container = document.getElementById("sidebar-container");
  if (!container) return;

  try {
    const res = await fetch("includes/teacher_sidebar.php");
    if (!res.ok) throw new Error();

    const html = await res.text();
    container.innerHTML = html;

  } catch(e) {
    console.error("Sidebar failed to load.", e);
  }
}

document.addEventListener("DOMContentLoaded", loadSidebar);
