document.addEventListener("DOMContentLoaded", function () {
    console.log("JavaScript Loaded!"); // Debugging

    // Sidebar elements
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
    userIcon?.addEventListener("click", function () {
        toggleSidebar(userSidebar);
    });

    notificationIcon?.addEventListener("click", function () {
        toggleSidebar(notificationSidebar);
    });

    messengerIcon?.addEventListener("click", function () {
        toggleSidebar(messengerSidebar);
    });

    // Event Listeners for Closing Sidebars
    closeUserSidebar?.addEventListener("click", function () {
        closeAllSidebars();
    });

    closeNotification?.addEventListener("click", function () {
        closeAllSidebars();
    });

    closeMessenger?.addEventListener("click", function () {
        closeAllSidebars();
    });

    // Close sidebar when clicking outside (overlay)
    overlay?.addEventListener("click", function () {
        closeAllSidebars();
    });

    // Logout functionality
    const logoutLink = document.getElementById("logoutLink");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (event) {
            event.preventDefault();

            const confirmLogout = confirm("আপনি কি নিশ্চিত যে আপনি লগ আউট করতে চান?");

            if (confirmLogout) {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = '../Html/index.php'; // Redirect to login page
            }
        });
    }

    // Handle profile picture change
    document.getElementById('changePic').addEventListener('click', function () {
        document.getElementById('uploadImage').click(); // Trigger file input when camera icon is clicked
    });

    // Handle profile picture upload (display the selected image)
    document.getElementById('uploadImage').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('profileImage').src = e.target.result; // Update the displayed image
                document.getElementById('uploadHint').style.display = 'none'; // Hide the upload hint text

            };
            reader.readAsDataURL(file); // Read the image as a data URL
        }
    });

    

 

});
