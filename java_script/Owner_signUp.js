// Sidebar Elements
const agreeToTermsBtn = document.getElementById("agreeToTermsBtn");
const registrationSidebar = document.getElementById("registrationSidebar");
const closeRegistrationSidebar = document.getElementById("closeRegistrationSidebar");

// Open sidebar
agreeToTermsBtn.addEventListener("click", () => {
    registrationSidebar.classList.add("open");
});

// Close sidebar
closeRegistrationSidebar.addEventListener("click", () => {
    registrationSidebar.classList.remove("open");
});

// Prevent click inside sidebar from closing
registrationSidebar.addEventListener("click", (event) => {
    event.stopPropagation();
});

window.addEventListener("click", (event) => {
    const ignoreClickTargets = [
        agreeToTermsBtn,
        document.getElementById("locateButton"),
        document.getElementById("saveLocation")
    ];

    const clickedInsideIgnored = ignoreClickTargets.some(el => el && (el === event.target || el.contains(event.target)));

    if (!registrationSidebar.contains(event.target) && !clickedInsideIgnored) {
        registrationSidebar.classList.remove("open");
    }
});



// Map Elements and Logic
let map;
let currentMarker;

function openMap() {
    console.log("Opening map...");
    document.getElementById("mapModal").classList.remove("hidden");

    if (!map) {
        console.log("Initializing map...");
        map = L.map('map').setView([23.8103, 90.4125], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        currentMarker = L.marker([23.8103, 90.4125]).addTo(map);
    }
}

function locateCurrentPosition() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            map.setView([lat, lng], 13);
            if (currentMarker) {
                currentMarker.remove();
            }
            currentMarker = L.marker([lat, lng]).addTo(map);

            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(response => response.json())
                .then(data => {
                    const address = data.display_name;
                    const addr = data.address;

                    document.getElementById('shopAddress').value = address;
                    document.getElementById('addressStreet').value = addr.road || addr.building || "";
                    document.getElementById('addressArea').value = addr.suburb || addr.neighbourhood || addr.village || "";
                    document.getElementById('addressCity').value = addr.city || addr.town || addr.municipality || addr.county || "";
                    document.getElementById('addressPostcode').value = addr.postcode || "";
                    document.getElementById('addressDivision').value = addr.state || addr.region || "";

                    console.log("Full address info:", addr);
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

function saveLocation() {
    const shopAddress = document.getElementById('shopAddress').value;
    console.log("Saving this address:", shopAddress);
    localStorage.setItem('shopAddress', shopAddress);
    closeMap();
}

function closeMap() {
    document.getElementById("mapModal").classList.add("hidden");
}
