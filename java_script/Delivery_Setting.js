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
                window.location.href = '../Html/Index.html';
            }
        });
    }

    // Password Change Form Editing
    const editButton = document.getElementById("editButton");
    const saveButton = document.getElementById("saveButton");
    const cancelButton = document.getElementById("cancelButton");
    const mapButton = document.getElementById("mapButton"); // mapButton added

    const newPasswordLabel = document.getElementById("newPasswordLabel");
    const newPasswordInput = document.getElementById("newShopPassword");

    const form = document.getElementById("shopForm");
    const formFields = form.querySelectorAll("input, select");

    // Store initial values (optional)
    const initialValues = {};
    formFields.forEach(field => {
        initialValues[field.id] = field.value;
    });

    // Initially hide the Save button
    saveButton.hidden = true;
    cancelButton.hidden = true;
    mapButton.hidden = true;

    // Enable fields on Edit
    editButton.addEventListener("click", function () {
        formFields.forEach(field => {
            field.disabled = false;
        });

        newPasswordLabel.style.display = "block";
        newPasswordInput.style.display = "block";

        saveButton.hidden = false;  // Show Save button
        cancelButton.hidden = false;  // Show Cancel button
        mapButton.hidden = false; // Show map button
        editButton.hidden = true;  // Hide Edit button
    });

    // Cancel editing and restore initial values
    cancelButton.addEventListener("click", function () {
        formFields.forEach(field => {
            field.disabled = true;
            if (initialValues[field.id] !== undefined) {
                field.value = initialValues[field.id];
            }
        });

        newPasswordLabel.style.display = "none";
        newPasswordInput.style.display = "none";

        saveButton.hidden = true;  // Hide Save button
        cancelButton.hidden = true;  // Hide Cancel button
        mapButton.hidden = true; // Hide map button
        editButton.hidden = false;  // Show Edit button again
    });
});

let map;
let currentMarker;  // Track the current marker on the map

// Function to open the map modal
function openMap() {
    console.log("Opening map...");

    // Show the map modal
    document.getElementById("mapModal").classList.remove("hidden");

    // Check if the map is already initialized
    if (!map) {
        console.log("Initializing map...");

        // Initialize the map with a default view (Dhaka coordinates as an example)
        map = L.map('map').setView([23.8103, 90.4125], 13); // Set initial coordinates (Dhaka)

        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add a default marker at the initial location
        currentMarker = L.marker([23.8103, 90.4125]).addTo(map);
    }
}

// Function to locate current position
function locateCurrentPosition() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            // Update map view and marker
            map.setView([lat, lng], 13);
            if (currentMarker) {
                currentMarker.remove();
            }
            currentMarker = L.marker([lat, lng]).addTo(map);

            // Reverse Geocoding to get address
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    const address = data.display_name;
                    document.getElementById('shopAddress').value = address; // Set full address

                    // Optionally log address for debugging
                    console.log("Address found:", address);
                })
                .catch(error => {
                    console.error('Error fetching address:', error);
                    alert("ঠিকানা আনতে সমস্যা হয়েছে। আবার চেষ্টা করুন।");
                });
        });
    } else {
        alert("Geolocation আপনার ব্রাউজারে সমর্থিত নয়।");
    }
}

// Function to save location when Save Location button is clicked
function saveLocation() {
    const shopAddress = document.getElementById('shopAddress').value;

    console.log("Saving this address:", shopAddress);

    // You can then save it to localStorage, send it to the server, etc.
    localStorage.setItem('shopAddress', shopAddress); // Example: save to localStorage

    // Close the map modal after saving
    closeMap();
}

// Function to close the map modal
function closeMap() {
    document.getElementById("mapModal").classList.add("hidden"); // Hide the modal
}

document.addEventListener("DOMContentLoaded", function () {
    const logoutLink = document.getElementById("logoutLink");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (event) {
            event.preventDefault();

            const confirmLogout = confirm("আপনি কি নিশ্চিত যে আপনি লগ আউট করতে চান?");

            if (confirmLogout) {
                localStorage.clear();
                sessionStorage.clear();
                window.location.href = '../Html/Index.html';
            }
        });
    }
});


