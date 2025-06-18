document.addEventListener("DOMContentLoaded", function () {
    const userIcon = document.getElementById("userIcon");
    const closeBtn = document.getElementById("closeSidebar");
    const sidebar = document.getElementById("loginSidebar");
    const overlay = document.createElement("div");

    overlay.className = "overlay";
    document.body.appendChild(overlay);

    function toggleSidebar() {
        if (sidebar.classList.contains("show")) {
            sidebar.classList.remove("show");
            overlay.style.display = "none";
        } else {
            sidebar.classList.add("show");
            overlay.style.display = "block";
        }
    }

    function closeLoginSidebar() {
        sidebar.classList.remove("show");
        overlay.style.display = "none"; // Ensure overlay hides
    }

    userIcon.addEventListener("click", toggleSidebar);
    closeBtn.addEventListener("click", closeLoginSidebar);
    overlay.addEventListener("click", closeLoginSidebar); // Close when clicking outside
});
