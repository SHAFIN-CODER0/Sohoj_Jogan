let quantity = 1;
const unitPrice =  $productPrice ;
const customerCoins =  $customer_coins;
const shopLat = $shop_lat ;
const shopLng =  $shop_lng 

let deliveryCharge = 0;
let currentDistance = 0;

const qtyDisplay = document.getElementById('qty-display');
const qtyPriceDisplay = document.getElementById('qty-display-price');
const unitPriceElem = document.getElementById('unit-price');
const deliveryChargeElem = document.getElementById('delivery-charge');
const totalPriceElem = document.getElementById('total-price');
const quantityInput = document.getElementById('quantity');
const distanceInput = document.getElementById('distance');
const deliveryChargeInput = document.getElementById('delivery_charge');

function toBengali(num) {
    return num.toString().replace(/\d/g, d => "০১২৩৪৫৬৭৮৯"[d]);
}

// Use Routing Machine for road distance
let map, routingControl;
function findDistance() {
    const address = document.getElementById('customer-address').value;
    if (address.length < 4) {
        alert("ঠিকানা লিখুন");
        return;
    }
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`)
    .then(res => res.json())
    .then(data => {
        if (data.length === 0) {
            document.getElementById('distance-info').textContent = "ঠিকানা পাওয়া যায়নি!";
            document.getElementById('delivery-charge-info').textContent = "";
            distanceInput.value = "";
            deliveryChargeInput.value = "";
            currentDistance = 0;
            deliveryCharge = 0;
            updateDisplay();
            return;
        }
        const userLat = parseFloat(data[0].lat);
        const userLng = parseFloat(data[0].lon);

        // Routing Machine ব্যবহার করুন
        if (!map) {
            map = L.map('address-map').setView([shopLat, shopLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
        }
        if (routingControl) map.removeControl(routingControl);

        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(shopLat, shopLng),
                L.latLng(userLat, userLng)
            ],
            routeWhileDragging: false,
            draggableWaypoints: false,
            addWaypoints: false,
            lineOptions: { styles: [{ color: 'blue', weight: 5 }] },
            show: false
        })
        .on('routesfound', function(e) {
            const routes = e.routes;
            if (routes.length > 0) {
                const best = routes[0];
                const distanceKm = best.summary.totalDistance / 1000;
                const coinNeeded = Math.ceil(distanceKm) * 20;

                document.getElementById('distance-info').textContent = `দূরত্ব: ${toBengali(distanceKm.toFixed(2))} কিমি`;
                document.getElementById('delivery-charge-info').textContent = ` | ডেলিভারি চার্জ: ${toBengali(coinNeeded)} টাকা/কয়েন`;

                distanceInput.value = distanceKm.toFixed(2);
                deliveryChargeInput.value = coinNeeded;
                currentDistance = distanceKm;
                deliveryCharge = coinNeeded;
                updateDisplay();
            }
        })
        .addTo(map);

        map.fitBounds([[shopLat, shopLng],[userLat, userLng]], {padding:[30,30]});
    });
}

function updateDisplay() {
    qtyDisplay.textContent = toBengali(quantity);
    qtyPriceDisplay.textContent = toBengali(quantity);
    unitPriceElem.textContent = `মূল্যঃ ${toBengali(unitPrice)} × ${toBengali(quantity)}`;

    const selected = document.querySelector('input[name="delivery"]:checked').value;

    if (!deliveryCharge || !currentDistance) {
        deliveryChargeElem.textContent = "দয়া করে আপনার ঠিকানা লিখে দূরত্ব ও চার্জ দেখুন";
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali((unitPrice * quantity).toFixed(2))} টাকা`;
        return;
    }

    if (selected === "coin") {
        if (customerCoins >= deliveryCharge) {
            deliveryChargeElem.textContent = `ফ্রি ডেলিভারি (${toBengali(deliveryCharge)} কয়েন কেটে যাবে)`;
            totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali((unitPrice * quantity).toFixed(2))} টাকা`;
        } else {
            deliveryChargeElem.textContent = `আপনার কাছে যথেষ্ট কয়েন নেই (${toBengali(deliveryCharge)} দরকার)`;
            totalPriceElem.textContent = "";
        }
    } else if (selected === "pickup") {
        deliveryChargeElem.textContent = "স্টোর পিকআপ: কোন ডেলিভারি চার্জ নেই";
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali((unitPrice * quantity).toFixed(2))} টাকা`;
    } else if (selected === "home") {
        deliveryChargeElem.textContent = `হোম ডেলিভারি: ${toBengali(deliveryCharge)} টাকা`;
        totalPriceElem.textContent = `মোট মূল্যঃ ${toBengali(((unitPrice * quantity) + deliveryCharge).toFixed(2))} টাকা`;
    }
    quantityInput.value = quantity;
}

function increaseQty() {
    quantity++;
    updateDisplay();
}

function decreaseQty() {
    if (quantity > 1) {
        quantity--;
        updateDisplay();
    }
}

function updateDelivery() {
    updateDisplay();
}

function confirmOrder() {
    const name = document.getElementById('customer-name').value.trim();
    const address = document.getElementById('customer-address').value.trim();
    const phone = document.getElementById('customer-phone').value.trim();

    if (!name || !address || !phone) {
        alert("দয়া করে সমস্ত আবশ্যক তথ্য পূরণ করুন।");
        return false;
    }

    const phonePattern = /^[0-9]{10,11}$/;
    if (!phonePattern.test(phone)) {
        alert("সঠিক মোবাইল নাম্বার দিন (১০-১১ সংখ্যার)।");
        return false;
    }

    if (!deliveryCharge || !currentDistance) {
        alert("ঠিকানা দিয়ে দূরত্ব ও চার্জ দেখুন, তারপর অর্ডার করুন!");
        return false;
    }

    const selected = document.querySelector('input[name="delivery"]:checked').value;
    if (selected === "coin") {
        if (customerCoins < deliveryCharge) {
            alert("আপনার কাছে যথেষ্ট কয়েন নেই!");
            return false;
        }
    }
    return confirm("আপনি কি অর্ডার নিশ্চিত করতে চান?");
}

window.onload = function() {
    updateDisplay();
};