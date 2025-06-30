document.addEventListener("DOMContentLoaded", function () {
    console.log("JavaScript Loaded!"); // Debugging

    // Get elements for sidebar functionality
    const userIcon = document.getElementById("userIcon");
    const notificationIcon = document.getElementById("notificationIcon");

    const userSidebar = document.getElementById("userSidebar");
    const notificationSidebar = document.getElementById("notificationSidebar");

    const closeUserSidebar = document.getElementById("closeUserSidebar");
    const closeNotification = document.getElementById("closeNotification");

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
        document.querySelectorAll(".sidebar").forEach((sidebar) => {
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

    // Event Listeners for Closing Sidebars
    closeUserSidebar.addEventListener("click", function () {
        closeAllSidebars();
    });

    closeNotification.addEventListener("click", function () {
        closeAllSidebars();
    });

    // Close sidebar when clicking outside (overlay)
    overlay.addEventListener("click", function () {
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
                window.location.href = "../Html/Index.php";
            }
        });
    }

    // Map Elements and Logic
    let map;
    let currentMarker;

    function openMap() {
        console.log("Opening map...");
        document.getElementById("mapModal").classList.remove("hidden");

        if (!map) {
            console.log("Initializing map...");
            map = L.map("map").setView([23.8103, 90.4125], 13);
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);
            currentMarker = L.marker([23.8103, 90.4125]).addTo(map);
        }
    }

    function locateCurrentPosition() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    map.setView([lat, lng], 13);

                    if (currentMarker) {
                        currentMarker.remove();
                    }
                    currentMarker = L.marker([lat, lng]).addTo(map);

                    fetch(
                        `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`
                    )
                        .then((response) => response.json())
                        .then((data) => {
                            const address = data.display_name;

                            document.getElementById("shopAddress").value = address;

                            console.log("Full address info:", data.address);
                        })
                        .catch((error) => {
                            console.error("Error fetching address:", error);
                            alert("ঠিকানা আনতে সমস্যা হয়েছে। আবার চেষ্টা করুন।");
                        });
                },
                function (error) {
                    alert("জিপিএস ডিভাইস অ্যাক্সেস করতে সমস্যা হয়েছে।");
                    console.error("Geolocation error:", error);
                }
            );
        } else {
            alert("Geolocation আপনার ব্রাউজারে সমর্থিত নয়।");
        }
    }

    function saveLocation() {
        const shopAddress = document.getElementById("shopAddress").value;
        console.log("Saving this address:", shopAddress);
        localStorage.setItem("shopAddress", shopAddress);
        closeMap();
    }

    function closeMap() {
        document.getElementById("mapModal").classList.add("hidden");
    }

    // Add event listeners for map-related buttons
    const mapButton = document.getElementById("mapButton");
    const locateButton = document.getElementById("locateButton");
    const saveLocationButton = document.getElementById("saveLocation");
    const closeMapButton = document.getElementById("closeMap");

    if (mapButton) mapButton.addEventListener("click", openMap);
    if (locateButton) locateButton.addEventListener("click", locateCurrentPosition);
    if (saveLocationButton) saveLocationButton.addEventListener("click", saveLocation);
    if (closeMapButton) closeMapButton.addEventListener("click", closeMap);
});
