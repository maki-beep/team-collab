document.addEventListener("DOMContentLoaded", () => {
  const landingScreen = document.getElementById("screen-admin-landing");
  const dashboardScreen = document.getElementById("screen-admin-dashboard");

  const launchBtn = document.getElementById("btn-launch-analytics");
  const logoutBtn = document.getElementById("btn-dashboard-logout");

  // Custom Modal Nodes
  const logoutModal = document.getElementById("logout-modal-overlay");
  const cancelModalBtn = document.getElementById("btn-modal-cancel");
  const confirmModalBtn = document.getElementById("btn-modal-confirm");

  // Action: Open Dashboard Panel
  if (launchBtn) {
    launchBtn.addEventListener("click", () => {
      landingScreen.classList.remove("active");
      dashboardScreen.classList.add("active");
    });
  }

  // Trigger: Open Custom Modal Popup Box View
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      logoutModal.classList.add("show-modal");
    });
  }

  // Action: Cancel button inside modal dismissed popup view
  if (cancelModalBtn) {
    cancelModalBtn.addEventListener("click", () => {
      logoutModal.classList.remove("show-modal");
    });
  }

  // Action: Confirm button inside modal handles true screen state reset
  if (confirmModalBtn) {
    confirmModalBtn.addEventListener("click", () => {
      logoutModal.classList.remove("show-modal");
      dashboardScreen.classList.remove("active");
      landingScreen.classList.add("active");
    });
  }
});
