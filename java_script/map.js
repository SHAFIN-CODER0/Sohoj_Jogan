// Initialize the map with higher zoom settings
var map = L.map('mapid', {
    center: [23.8103, 90.4125], 
    zoom: 15,  
    maxZoom: 21, // Increased max zoom
    minZoom: 5  ,
    zoomControl: false
});

L.control.zoom({
    position: 'bottomright' 
}).addTo(map);
document.addEventListener("DOMContentLoaded", function () {
    let zoomButtons = document.querySelectorAll(".leaflet-control-zoom a");

    zoomButtons.forEach(button => {
        
        button.style.width = "30px";
        button.style.height = "30px";
      
        button.style.fontSize = "22px";
        button.style.lineHeight = "40px";
    });
});

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 22  
}).addTo(map);
let currentLocationMarker = null;
let currentLocationCircle = null;
// Function to clear previous markers & circles
function clearPreviousMarkers() {
    if (currentLocationMarker) {
        map.removeLayer(currentLocationMarker);
    }
    if (currentLocationCircle) {
        map.removeLayer(currentLocationCircle);
    }
}
// 📍 **Current Location Button**
document.getElementById("currentLocationBtn").addEventListener("click", () => {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(position => {
            const { latitude, longitude } = position.coords;

            // Clear previous markers
            clearPreviousMarkers();

            // Move the map to the new location
            map.setView([latitude, longitude], 20);

            // Add marker at the current location
            currentLocationMarker = L.marker([latitude, longitude]).addTo(map)
                .bindPopup("📍 <b>আপনার বর্তমান অবস্থান</b>")
                .openPopup();

            // Add a circle around the current location
            currentLocationCircle = L.circle([latitude, longitude], {
                color: "blue",
                fillColor: "#2196F3",
                fillOpacity: 0.3,
                radius: 50 // 50 meters
            }).addTo(map);

        }, () => {
            alert("আপনার অবস্থান নির্ধারণ করা যাচ্ছে না!");
        });
    } else {
        alert("আপনার ব্রাউজার জিওলোকেশন সমর্থন করে না!");
    }
});

// 🔍 **Search Location by Name**
document.getElementById("searchPlaceBtn").addEventListener("click", async () => {
    let placeName = document.getElementById("placeInput").value.trim();

    if (placeName === "") {
        alert("অনুগ্রহ করে একটি স্থান নাম লিখুন!");
        return;
    }
    let url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(placeName)}`;
    try {
        let response = await fetch(url);
        let data = await response.json();
        if (data.length > 0) {
            let lat = parseFloat(data[0].lat);
            let lon = parseFloat(data[0].lon);

            // Clear previous markers
            clearPreviousMarkers();

            // Move the map to the searched location
            map.setView([lat, lon], 18);

            // Add marker at the searched location
            currentLocationMarker = L.marker([lat, lon]).addTo(map)
                .bindPopup(`📍 <b>${placeName}</b>`)
                .openPopup();

            // Add a circle around the searched location
            currentLocationCircle = L.circle([lat, lon], {
                color: "blue",
                fillColor: "#2196F3",
                fillOpacity: 0.3,
                radius: 100 // 100 meters
            }).addTo(map);

        } else {
            alert("কোনো ফলাফল পাওয়া যায়নি!");
        }
    } catch (error) {
        console.error("Error fetching location:", error);
        alert("অনুসন্ধান করতে সমস্যা হচ্ছে!");
    }
});
