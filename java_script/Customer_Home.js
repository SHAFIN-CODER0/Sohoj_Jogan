document.addEventListener("DOMContentLoaded", function () {
    console.log("JavaScript Loaded!"); // Debugging

    // Get elements
    const userIcon = document.getElementById("userIcon");
    const notificationIcon = document.getElementById("notificationIcon");
    const messengerIcon = document.getElementById("messengerBtn");

    const userSidebar = document.getElementById("userSidebar");
    const notificationSidebar = document.getElementById("notificationSidebar");
    const messengerSidebar = document.getElementById("messengerSidebar");

    const closeUserSidebar = document.getElementById("closeUserSidebar");
    const closeNotification = document.getElementById("closeNotification");
    const closeMessenger = document.getElementById("closeMessenger");

    const overlay = document.getElementById("overlay");

    // Toggle function (opens if closed, closes if open)
    function toggleSidebar(sidebar) {
        const isOpen = sidebar.classList.contains("show");
        closeAllSidebars(); // Close all sidebars first
        if (!isOpen) {
            sidebar.classList.add("show");
            overlay.classList.add("show");
        }
    }

    // Close all sidebars
    function closeAllSidebars() {
        document.querySelectorAll(".sidebar").forEach(sidebar => {
            sidebar.classList.remove("show");
        });
        overlay.classList.remove("show"); // Hide overlay
    }

    // Event Listeners for Toggling Sidebars
    userIcon.addEventListener("click", function () {
        toggleSidebar(userSidebar);
    });

    notificationIcon.addEventListener("click", function () {
        toggleSidebar(notificationSidebar);
    });

    messengerIcon.addEventListener("click", function () {
        toggleSidebar(messengerSidebar);
    });

    // Event Listeners for Closing Sidebars
    closeUserSidebar.addEventListener("click", function () {
        closeAllSidebars();
    });

    closeNotification.addEventListener("click", function () {
        closeAllSidebars();
    });

    closeMessenger.addEventListener("click", function () {
        closeAllSidebars();
    });

    // Close sidebar when clicking outside (overlay)
    overlay.addEventListener("click", function () {
        closeAllSidebars();
    });
});
document.addEventListener("DOMContentLoaded", function () {
    const logoutLink = document.getElementById("logoutLink");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (event) {
            event.preventDefault();

            const confirmLogout = confirm("আপনি কি নিশ্চিত যে আপনি লগ আউট করতে চান?");

            if (confirmLogout) {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = '../Html/Index.php';
            }
        });
    }
   const galleryContainer = document.querySelector('.gallery-container');

// ✅ Duplicate the items for seamless scrolling
galleryContainer.innerHTML += galleryContainer.innerHTML;

let position = 0;
let scrollSpeed = 1; // Start scrolling right
let isHoveringItem = false;
let hoveredItem = null;
const maxSpeed = 5; // Max scroll speed

// ✅ Mouse movement affects scroll speed after load
galleryContainer.addEventListener('mousemove', (e) => {
    const bounds = galleryContainer.getBoundingClientRect();
    const centerX = bounds.left + bounds.width / 2;
    const offset = e.clientX - centerX;

    // Adjusted factor to make the scroll speed more subtle
    scrollSpeed = Math.max(-maxSpeed, Math.min(maxSpeed, offset / 30)); // Changed divisor to 30 for less sensitivity
});

// ✅ Pause on hover
galleryContainer.addEventListener('mouseover', (e) => {
    const item = e.target.closest('.gallery-item');
    if (item) {
        isHoveringItem = true;
        hoveredItem = item;
    }
});

galleryContainer.addEventListener('mouseout', (e) => {
    const item = e.target.closest('.gallery-item');
    if (item && item === hoveredItem) {
        isHoveringItem = false;
        hoveredItem = null;
    }
});

// ✅ Animate scrolling
function animateGallery() {
    if (!isHoveringItem) {
        position -= scrollSpeed;

        const totalWidth = galleryContainer.scrollWidth / 2;

        if (position <= -totalWidth) {
            position = 0;
        } else if (position >= 0) {
            position = -totalWidth;
        }

        galleryContainer.style.transform = `translateX(${position}px)`;
    }

    requestAnimationFrame(animateGallery);
}

animateGallery();

});
