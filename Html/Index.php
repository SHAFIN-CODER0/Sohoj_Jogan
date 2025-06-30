<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Index.css?v=1"> <!-- Correct CSS path -->
</head>
<body>
    <header>
              <div class="logo" id="logoClickable" style="cursor:pointer;">
    <img src="../Images/Logo.png" alt="Liberty Logo">
    <h2>সহজ যোগান</h2>
</div>
<script>
    document.getElementById('logoClickable').addEventListener('click', function() {
        window.location.href = '../Html/Index.php';
    });
</script>
        <nav>
            <ul>
                <li><a href="../Html/Women.php">নারী</a></li>
                <li><a href="../Html/Man.php">পুরুষ</a></li>
                <li><a href="../Html/Gift.php">উপহার</a></li>
                <li><a href="">লাইব্রেরি</a></li>
            </ul>
        </nav>
        <div class="icons">
           <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
            <button><img src="../Images/heart.png" alt="Wishlist"></button>
        </div>
    </header>
 
<script>
// PHP দিয়ে আগে window.isLoggedIn সেট করেন

</script>
<!-- Login Sidebar -->
<div id="loginSidebar" class="sidebar">
    <div class="sidebar-content">
        <span id="closeSidebar" class="close-btn">&times;</span>
        <h2>লগইন করুন</h2>
        <form action="../PHP/login.php" method="POST">
            <label for="userType">আপনার পরিচয়:</label>
            <select name="userType" id="userType" required>
                <option value="customer" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='customer') echo 'selected'; ?>>গ্রাহক</option>
                <option value="shop_owner" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='shop_owner') echo 'selected'; ?>>দোকান মালিক</option>
                <option value="delivery_man" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='delivery_man') echo 'selected'; ?>>ডেলিভারি ম্যান</option>
                <option value="admin" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='admin') echo 'selected'; ?>>অ্যাডমিন</option>
            </select>

            <label for="emailOrPhone">ইমেইল বা ফোন নম্বর লিখুন:</label>
            <input type="text" id="emailOrPhone" name="emailOrPhone"
                   value="<?php echo isset($_COOKIE['remember_emailOrPhone']) ? htmlspecialchars($_COOKIE['remember_emailOrPhone']) : ''; ?>" required>

            <label for="password">পাসওয়ার্ড:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">লগইন করুন</button>

            <div class="options">
                <label>
                  <input type="checkbox" name="remember" <?php if(isset($_COOKIE['remember_emailOrPhone'])) echo 'checked'; ?>>
                  মনে রাখুন
                </label>
                <a href="#">পাসওয়ার্ড ভুলে গেছেন?</a>
            </div>
            <button type="button" class="signup-btn" id="newAccountBtn">নতুন অ্যাকাউন্ট তৈরি করুন</button>
        </form>
    </div>
</div>

    <!-- Forgot Password Modal -->
<div id="forgotPasswordModal" class="modal">
    <div class="modal-content">
<span class="forgot-close-btn" id="closeForgotModal">&times;</span><h2>পাসওয়ার্ড রিসেট করুন</h2>
        <form action="../PHP/forgot_password.php" method="POST">
            <label for="resetEmailOrPhone">ইমেইল :</label>
            <input type="text" id="resetEmailOrPhone" name="resetEmailOrPhone" required>
<button type="submit">রিসেট OTP পাঠান</button>        </form>
    </div>
</div>
<script>document.querySelector('.options a').onclick = function(e) {
    e.preventDefault();
    document.getElementById('forgotPasswordModal').style.display = 'block';
};
document.getElementById('closeForgotModal').onclick = function() {
    document.getElementById('forgotPasswordModal').style.display = 'none';
};</script>
    
<style>.forgot-close-btn {
  position: absolute;
  top: 14px;
  right: 20px;
  font-size: 28px;
  color: #888;
  cursor: pointer;
  font-weight: bold;
  transition: color 0.22s, transform 0.22s;
  z-index: 10;
  user-select: none;
  line-height: 1;
}
.forgot-close-btn:hover {
  color: #e74c3c;
  transform: scale(1.18) rotate(10deg);
}</style>


    <script src="../java_script/log_in_script.js"></script>


    <div id="infoSidebar" class="sidebar">
        <div class="sidebar-content">
            <span id="closeInfoSidebar" class="close-btn">&times;</span>
            <!-- Form will be loaded dynamically here -->
        </div>
    </div>
   


  <!-- Modal Pop-up -->
  <div id="accountModal" class="signup-modal">
    <div class="signup-modal-content">
        <span class="close-modal-btn" id="closeModal">&times;</span>
        <h2>আপনি কী হিসেবে নিবন্ধন করতে চান?</h2>
        <button id="signupCustomer">গ্রাহক হিসেবে নিবন্ধন করুন</button>
        <button id="signupShopOwner">দোকান মালিক হিসেবে নিবন্ধন করুন</button>
        <button id="signupDeliveryMan">ডেলিভারি ম্যান হিসেবে নিবন্ধন করুন</button>
    </div>
</div>

<script src="../java_script/sign_up.js"></script>

<div id="customerRegistrationSidebar" class="customer-sidebar">
    <!-- Close Button -->
    <span id="closeCustomerSidebar" class="closeCustomer-btn">&times;</span>

    <h2>গ্রাহক হিসেবে নিবন্ধন করুন</h2>

    <form action="../PHP/register_customer.php" method="POST" enctype="multipart/form-data">
        <!-- Only File Upload Input -->
        <label for="customerPic">ছবি আপলোড করুন:</label>
        <input type="file" id="customerPic" name="customerPic" accept="image/*" required>

        <label for="customerName">নাম:</label>
        <input type="text" id="customerName" name="customerName" required>

        <label for="customerPhone">ফোন নম্বর:</label>
        <input type="text" id="customerPhone" name="customerPhone" required>

        <label for="customerGender">লিঙ্গ:</label>
        <select id="customerGender" name="customerGender" required>
            <option value="male">পুরুষ</option>
            <option value="female">মহিলা</option>
            <option value="other">অন্যান্য</option>
        </select>

        <label for="customerAddress">ঠিকানা:</label>
        <input type="text" id="customerAddress" name="customerAddress" required>

        <label for="customerEmail">ইমেইল:</label>
        <input type="email" id="customerEmail" name="customerEmail" required>

        <label for="customerPassword">পাসওয়ার্ড:</label>
        <input type="password" id="customerPassword" name="customerPassword" required>

        <button type="submit">নিবন্ধন করুন</button>
    </form>  
</div>

<script src="../java_script/customer.js"></script>



<!-- Shop Owner Modal -->
<div id="shopOwnerModal" class="shop-owner-modal">
    <div class="modal-inner">
        <span id="closeShopOwnerModal" class="close-btn">&times;</span>
        <h2>দোকান খোলার শর্তাবলী</h2>
        <h6>আমাদের সাইটে দোকান খোলার শর্তাবলী:</h6>
        
        <h3>দোকান খোলার জন্য আবশ্যক ডকুমেন্টস ও শর্তাবলী:</h3>
        <ul>
            <li>🆔 <strong>জাতীয় পরিচয়পত্র (NID/জন্ম সনদ):</strong> দোকান মালিকের বৈধ জাতীয় পরিচয়পত্রের একটি কপি আপলোড করতে হবে।</li>
            <li>📸 <strong>ছবি:</strong> মালিকের পাসপোর্ট সাইজের ছবি এবং দোকানের সামনের ছবি দিতে হবে।</li>
            <li>📱 <strong>ফোন নম্বর:</strong> একটি কার্যকর মোবাইল নম্বর প্রদান করতে হবে, যা যাচাইকৃত এবং হালনাগাদ।</li>
            <li>📍 <strong>দোকানের সঠিক অবস্থান:</strong> দোকানের সঠিক অবস্থান বা ঠিকানা দিতে হবে (Google Maps বা ম্যানুয়াল ঠিকানা)।</li>
            <li>✅ <strong>নিয়ম ও শর্তাবলী গ্রহণ:</strong> আমাদের নিয়ম ও শর্তাবলী (Terms and Conditions) মেনে নিতে হবে।</li>
            <li>💸 <strong>ডেলিভারি চার্জ বণ্টন:</strong> ডেলিভারি চার্জের ২০% দোকান মালিক পাবেন এবং বাকি ৬০% ডেলিভারি ম্যানের কাছে যাবে।</li>
            <li>📂 <strong>অনুমোদন প্রক্রিয়া:</strong> সমস্ত ডকুমেন্ট যাচাইয়ের পরে দোকান খোলার অনুমোদন দেওয়া হবে।</li>
        </ul>

        <h3>গুরুত্বপূর্ণ নির্দেশনা:</h3>
        <ul>
            <li>যেকোনো জাল ডকুমেন্ট বা ভুল তথ্য প্রদান করলে দোকান নিবন্ধন বাতিল করা হবে।</li>
            <li>তথ্যের সঠিকতা নিশ্চিত করতে যাচাইকরণ প্রক্রিয়ায় কিছু সময় লাগতে পারে।</li>
            <li>ডেলিভারি চার্জ বণ্টন স্বয়ংক্রিয়ভাবে হিসাব করা হবে এবং সরাসরি সংশ্লিষ্টদের কাছে পৌঁছে যাবে।</li>
        </ul>

        <h4>আমাদের শর্তাবলী মেনে চলুন এবং নিরাপদ ব্যবসায়িক পরিবেশ গড়ে তুলুন।</h4>
        <button id="agreeToTermsBtn">শর্তাবলী গ্রহণ করুন</button>
    </div>
</div>

<script src="../java_script/ShopOwner.js"></script>

<!-- Shop Owner Registration Sidebar -->
<div id="registrationSidebar" class="registration-sidebar">
    <div class="registration-sidebar-content">
        <span class="close-btn" id="closeRegistrationSidebar">&times;</span>
        <h2>দোকান মালিক হিসেবে নিবন্ধন করুন</h2>
        <form action="../PHP/register_shop_owner.php" method="POST" enctype="multipart/form-data">

            <!-- National ID or Birth Certificate Upload -->
            <label for="nid">জাতীয় পরিচয়পত্র (NID)/ জন্ম সনদ:</label>
            <input type="file" id="nid" name="nid" accept="image/*" required>

            <!-- Shop Owner's Name (Linked to NID/Birth Certificate) -->
            <label for="shopOwnerName">দোকান মালিকের নাম (NID/ জন্ম সনদ অনুসারে):</label>
            <input type="text" id="shopOwnerName" name="shopOwnerName" placeholder="NID এর নাম অনুসারে" required>

            <!-- Phone Number -->
            <label for="shopPhone">দোকানের ফোন নম্বর:</label>
            <input type="text" id="shopPhone" name="shopPhone" required>

            <!-- Optional Email -->
            <label for="shopEmail">দোকানের ইমেইল (অপশনাল):</label>
            <input type="email" id="shopEmail" name="shopEmail">

            <!-- Shop Owner Photo -->
            <label for="shopOwnerPic">দোকান মালিকের ছবি:</label>
            <input type="file" id="shopOwnerPic" name="shopOwnerPic" accept="image/*" required>

            <!-- Shop Name -->
            <label for="shopName">দোকানের নাম:</label>
            <input type="text" id="shopName" name="shopName" required placeholder="আপনার দোকানের নাম লিখুন">

            <!-- Shop Photo -->
            <label for="shopPic">দোকানের ছবি:</label>
            <input type="file" id="shopPic" name="shopPic" accept="image/*" required>

            <!-- Shop Type -->
            <label for="shopType">দোকানের ধরন:</label>
            <select id="shopType" name="shopType" required>
                <option value="" disabled selected>দোকানের ধরন নির্বাচন করুন</option>
                <option value="gift">গিফট</option>
                <option value="men">পুরুষ</option>
                <option value="women">মহিলা</option>
                <option value="others">অন্যান্য</option>
            </select>

            <!-- Address Section -->
            <label for="shopAddress">দোকানের ঠিকানা:</label>
            <input type="text" id="shopAddress" name="shopAddress" placeholder="দোকানের ঠিকানা লিখুন" required>
            <button type="button" id="mapButton" class="map-button" onclick="openMap()">ঠিকানা নির্বাচন করুন</button>

            <!-- Composite Address Section -->
            <label for="addressStreet">রাস্তা/বিল্ডিং:</label>
            <input type="text" id="addressStreet" name="addressStreet" placeholder="রাস্তার নাম বা ভবনের বিবরণ" required>

            <label for="addressArea">এরিয়া / থানা:</label>
            <input type="text" id="addressArea" name="addressArea" placeholder="এরিয়া বা থানা লিখুন" required>

            <label for="addressCity">শহর / উপজেলা:</label>
            <input type="text" id="addressCity" name="addressCity" placeholder="শহর বা উপজেলা লিখুন" required>

            <label for="addressPostcode">পোস্টকোড:</label>
            <input type="text" id="addressPostcode" name="addressPostcode" placeholder="পোস্টকোড লিখুন" required>

            <label for="addressDivision">বিভাগ:</label>
            <input type="text" id="addressDivision" name="addressDivision" placeholder="বিভাগের নাম লিখুন" required>

            <input type="hidden" id="shopLog" name="shopLongitude" placeholder="Longitude" readonly required>
            <input type="hidden" id="shopLan" name="shopLatitude" placeholder="Latitude" readonly required>

            <!-- Gender -->
            <label for="shopOwnerGender">লিঙ্গ:</label>
            <select id="shopOwnerGender" name="shopOwnerGender" required>
                <option value="male">পুরুষ</option>
                <option value="female">মহিলা</option>
                <option value="other">অন্যান্য</option>
            </select>

            <!-- Shop Description -->
            <label for="shopDescription">দোকানের বর্ণনা দিন:</label>
            <textarea id="shopDescription" name="shopDescription" rows="4" cols="50" placeholder="আপনার দোকানের বিস্তারিত লিখুন..." required></textarea>

            <!-- Shop Password -->
            <label for="shopPassword">পাসওয়ার্ড:</label>
            <input type="password" id="shopPassword" name="shopPassword" required>

            <!-- Submit Button -->
            <button type="submit">নিবন্ধন করুন</button>
        </form>
    </div>
</div>

<script>// Sidebar Elements
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

            // Set values in input fields
            document.getElementById('shopLan').value = lat;
            document.getElementById('shopLog').value = lng;

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

                    document.getElementById('shopAddress').value = address || "";
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
</script>
<div id="mapModal" class="map-modal hidden">
    <h3>ঠিকানা নির্বাচন করুন</h3>

    <!-- Map container -->
    <div id="map" style="width: 100%; height: 400px;"></div>
    <span id="closeMap" class="close-icon" onclick="closeMap()">&times;</span> <!-- Close Button -->

    <!-- Action Buttons -->
    <button type="button" id="locateButton" onclick="locateCurrentPosition()">বর্তমান অবস্থান</button>
<button type="button" id="saveLocation" onclick="saveLocation()">ঠিকানা সংরক্ষণ করুন</button>

</div>



<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>



<!-- Modal for Delivery Man Rules -->
<div id="deliveryManRulesModal" class="delivery-man-modal">
    <div class="delivery-man-modal-content">
        <span class="close-modal" id="closeDeliveryRulesModal">&times;</span>

        <section class="delivery-man-guidelines">
            <h1>ডেলিভারি ম্যানের নিয়মাবলী 🚚</h1>
            <p>একজন ডেলিভারি ম্যান হিসেবে কাজ করতে চাইলে, আপনাকে নিচের নিয়মগুলো অবশ্যই মেনে চলতে হবে:</p>
            <ul>
                <li>সময়মতো এবং নির্ভুলভাবে পণ্য ডেলিভারি করতে হবে ⏰📦।</li>
                <li>গ্রাহকের সাথে সদাচরণ বজায় রাখতে হবে এবং অপ্রয়োজনীয় কথাবার্তা এড়িয়ে চলতে হবে 😊📞।</li>
                <li>প্রতিটি ডেলিভারির সময় লোকেশন এবং তথ্য যাচাই করে নিতে হবে 🗺️📍।</li>
                <li>ডেলিভারির সময় কোম্পানির নির্ধারিত ড্রেস ও আইডি কার্ড ব্যবহার করতে হবে 👔🆔।</li>
                <li>যেকোনো সমস্যায় অ্যাডমিনকে দ্রুত অবহিত করতে হবে ⚠️👨‍💻।</li>
                <li><strong>জাতীয় পরিচয়পত্র (NID/জন্ম সনদ) ও ছবি প্রদান বাধ্যতামূলক</strong> — এটি যাচাইয়ের জন্য প্রয়োজন 📑🆔。</li>
                <li>মিথ্যা তথ্য দিলে রেজিস্ট্রেশন বাতিল হবে এবং আইনগত ব্যবস্থা নেওয়া হতে পারে ⚖️❌。</li>
            </ul>
            <button id="acceptTermsBtn">শর্তাবলী গ্রহণ করুন ✅</button>
        </section>
    </div>
</div>

<!-- Delivery Man Registration Form Sidebar -->
<div id="deliveryManRegisterSidebar" class="deliveryManSidebar">
    <div class="sidebar-content">
        <span class="close" id="closeDeliveryManRegisterSidebar">&times;</span>
        <h2>ডেলিভারি ম্যান নিবন্ধন ফর্ম</h2>

<form action="../PHP/register_delivery_man.php" method="POST" enctype="multipart/form-data">            <!-- National ID (NID) / Birth Certificate Upload -->
            <label for="nid">জাতীয় পরিচয়পত্র (NID)/ জন্ম সনদ:</label>
            <input type="file" id="nidImage" name="nidImage" accept="image/*" required>

            <!-- Profile Picture Upload -->
            <label for="profilePic">প্রোফাইল ছবি আপলোড করুন:</label>
            <input type="file" id="profilePic" name="profilePic" accept="image/*" required>

            <!-- Full Name -->
            <label for="fullName">পুরো নাম (NID/ জন্ম সনদ অনুসারে):</label>
            <input type="text" id="fullName" name="fullName" required placeholder="আপনার নাম লিখুন">

            <!-- Phone Number -->
            <label for="phone">ফোন নাম্বার:</label>
            <input type="tel" id="phone" name="phone" required placeholder="আপনার ফোন নাম্বার লিখুন">
              <!-- Email for Delivery Man -->
             <label for="deliveryEmail">ইমেইল(অপশনাল):</label>
            <input type="email" id="deliveryEmail" name="deliveryEmail" placeholder="আপনার ইমেইল লিখুন" required>

             <!-- Gender for Delivery Man -->
             <label for="deliveryGender">লিঙ্গ:</label>
             <select id="deliveryGender" name="deliveryGender" required>
             <option value="" disabled selected>আপনার লিঙ্গ নির্বাচন করুন</option>
             <option value="male">পুরুষ</option>
             <option value="female">মহিলা</option>
             <option value="other">অন্যান্য</option>
            </select>


            <!-- Address -->
            <label for="address">ঠিকানা:</label>
            <textarea id="address" name="address" required placeholder="আপনার ঠিকানা লিখুন"></textarea>

            <!-- Password -->
            <label for="password">পাসওয়ার্ড:</label>
            <input type="password" id="password" name="password" required placeholder="আপনার পাসওয়ার্ড লিখুন">

            <!-- Submit Button -->
            <button type="submit" id="submitDeliveryManForm">রেজিস্টার করুন</button>
        </form>
    </div>
</div>


<script src="../java_script/delivaryman.js"></script>

 
        <section class="design-masters">
            <div class="design-masters-content">
                <h1 class="title">স্বাগতম! সবকিছু এখন এক ক্লিকে</h1>

<p class="description">
    আপনার পছন্দের পণ্য কোথায় আছে, জানতে সরাসরি ম্যাপে যান—সেখানেই আপনার প্রয়োজনীয় জিনিস মিলবে।
</p>

            </div>
            <div class="button-wrapper">
                <a href="../Html/map.html" class="shop-now-btn">
                    অবস্থান
                    <img src="../Images/location.png" alt="Location Icon" style="width: 50px; height: 50px; vertical-align: middle; margin-left: 10px;">
                </a>
            </div>
        </section>
        
        <style>

.design-masters {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Space between text and button */
    height: 120vh; /* Adjust height to fit screen */
    background-image: url('../Images/front_page.png'); /* Use the correct path */
    background-size: cover;
    background-position: center;
    color: white;
    text-align: left;
    padding: 50px; /* Add padding to space elements */
    margin-top: 50px; /* Add margin to top */
}

.design-masters-content {
    max-width: 50%;
     /* Limit width of text */
}

.title {
    margin-top:240px;
    font-size: 2em;
    font-weight: bold;
    font-family: 'Garamond', 'Times New Roman', serif;
font-weight: bold;

}

.description {
    font-size: 1.2em;
    margin-top: 10px;
    
}

.shop-now-btn {
    display: inline-block;
    padding: 10px 100px;
    font-size: 1.8em;
    font-weight: bold;
    color: white;
    background: #4B014B;
    border-radius: 60px;
    text-decoration: none;
    transition: 0.3s;
    position: absolute;
    right: 10px;
    width: 380px;
   margin-top: 50px; /* Adjust margin to fit design */
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.shop-now-btn:hover {
    background: #6b146b;
}

        </style>
        
    
        <section class="main-hero">  <!-- Changed section class -->
            <div class="hero-content">  <!-- Changed text container -->
                <h1>আমাদের ওয়েবসাইট ব্যবহারকারী-বান্ধব</h1>
                <p>আমাদের ওয়েবসাইটটি তৈরি করা হয়েছে ব্যবহারকারীদের কথা মাথায় রেখে। সহজ নেভিগেশন,</p>
                <p>পরিষ্কার ডিজাইন এবং দ্রুত লোডিং সময় নিশ্চিত করে যে আপনি নির্বিঘ্নে সেবা উপভোগ করতে পারবেন।</p>
                <button class="cta-btn main-cta" id="openModalBtn">আপনার অ্যাকাউন্ট তৈরি করুন</button>            </div>
            <div class="hero-visual">  <!-- Changed image container -->
                <img src="../Images/UserFriendly.jpg" alt="User Friendly Interface Illustration">
            </div>
        </section>
<section class="joy-section">
    <div class="joy-image">  <!-- Changed from hero-image -->
      <img src="../Images/Up.jpg" alt="Sales Growth Illustration">
    </div>
    <div class="joy-text">  <!-- Changed from hero-text -->
        <h1>বিক্রয় বৃদ্ধি করুন এবং অতিরিক্ত লাভ করুন!</h1>
        <p>আপনার দোকানের বিক্রয় বাড়াতে চান? তাহলে 
          আমাদের সাথে থাকুন! আমাদের প্ল্যাটফর্মে আপনার 
          দোকান তালিকাভুক্ত করে আরও বেশি ক্রেতার কাছে 
          পৌঁছান এবং ব্যবসার প্রসার ঘটনা
        </p>
          
        <h1>দোকান মালিকদের জন্য বিশেষ সুবিধা:</h1>
        <p>যদি ক্রেতা আমাদের ডেলিভারি সিস্টেম নির্বাচন করেন,
           তাহলে দোকান মালিক পাবেন ২০% অতিরিক্ত লাভ!  
        </p>
        
        <h1>কেন আমাদের ডেলিভারি সিস্টেম ব্যবহার করবেন?</h1>  
        <p>- বিক্রয় বৃদ্ধির সুযোগ<br>  
           - নিরাপদ এবং দ্রুত ডেলিভারি ব্যবস্থা<br>  
           - অতিরিক্ত আয়ের সুযোগ</p>
           
        <h2>আজই আপনার দোকান নিবন্ধন করুন এবং বিক্রয় বাড়ানোর সুযোগ নিন! 💪🚀</h2>
        <button class="cta-btn">আপনার অ্যাকাউন্ট তৈরি করুন</button>
    </div>
  </section>

  <section class="delivery-man-section">
    <div class="delivery-man-image">  <!-- Changed from hero-image -->
        <img src="../Images/deliveryman.jpeg" alt="Delivery Man Illustration">
    </div>
    <div class="delivery-man-text">  <!-- Changed from hero-text -->
        <h1>আপনার আয় বাড়ান, ডেলিভারি ম্যান হিসেবে কাজ শুরু করুন!</h1>
        <p>আপনি যদি ফ্রি ও ফ্লেক্সিবল সময়ে কাজ করতে চান এবং একাধিক দোকান থেকে পণ্য সরবরাহ করতে চান, তাহলে আমাদের প্ল্যাটফর্মে যোগ দিন। 
           আমাদের মাধ্যমে আপনি স্থানীয় ব্যবসার সাথে যুক্ত হয়ে আপনার আয় বৃদ্ধি করতে পারবেন এবং সঠিক সময়ে নিরাপদ ডেলিভারি সম্পন্ন করতে পারবেন।
        </p>
          
        <h1>ডেলিভারি ম্যানদের জন্য বিশেষ সুবিধা:</h1>
        <p>যদি আপনি আমাদের ডেলিভারি সিস্টেমে যোগ দেন, তাহলে পাবেন:</p>
        <ul>
           <li>দ্রুত পেমেন্ট</li>
           <li>উপার্জনের সীমাহীন সুযোগ</li>
        </ul>
        
        <h1>কেন আমাদের ডেলিভারি সিস্টেমে যোগ দেবেন?</h1>  
        <ul>
           <li>নিরাপদ, দ্রুত এবং সঠিক ডেলিভারি ব্যবস্থা</li>  
           <li>অতিরিক্ত আয়ের সুযোগ</li>  
           <li>আপনার সময় অনুযায়ী কাজের সুযোগ</li>
        </ul>
           
        <h2>আজই আমাদের প্ল্যাটফর্মে যোগ দিন এবং আপনার আয় বাড়ান! 🚚💼</h2>
        <button class="cta-btn">অ্যাকাউন্ট তৈরি করুন</button>
    </div>
</section>




  <section class="ratings-section">  <!-- Changed section class -->
    <div class="ratings-text">  <!-- Changed from hero-text -->
        <h1>রেটিং, রিভিউ ও পছন্দের দোকান সংরক্ষণ করুন!</h1>
        <p>আমাদের প্ল্যাটফর্মে ক্রেতারা সহজেই দোকান মালিকের সাথে যোগাযোগ করতে পারেন, 
           পণ্যের প্রাপ্যতা সম্পর্কে জানতে পারেন এবং রেটিং ও রিভিউ দিয়ে তাদের অভিজ্ঞতা শেয়ার করতে পারেন। 
           তাছাড়া, আপনার পছন্দের দোকানগুলো সংরক্ষণ করে ভবিষ্যতে সহজেই খুঁজে পাওয়ার সুবিধাও রয়েছে।</p>
        
        <h2>বিশ্বাসযোগ্য, সহজ ও স্মার্ট কেনাকাটার অভিজ্ঞতা - সবকিছু এক জায়গায়!<br>
            আজই আপনার প্রিয় দোকানগুলো সংরক্ষণ করুন, রিভিউ দিন এবং কেনাকাটা উপভোগ করুন! 💬🌟</h2>
    </div>
    <div class="ratings-image">  <!-- Changed from hero-image -->
        <img src="../Images/Communication.jpg" alt="Customer Reviews Illustration">
    </div>
</section>
<section class="help-section">  <!-- Changed section class -->
    <div class="help-image">  <!-- Changed from hero-image -->
      <img src="../Images/Helpline.jpg" alt="Customer Support Illustration">
    </div>
    <div class="help-text">  <!-- Changed from hero-text -->
      <h1>সহায়তা ও হেল্পলাইন</h1>
      <p>আমাদের প্ল্যাটফর্মে আপনার যেকোনো সমস্যা বা প্রশ্নের দ্রুত সমাধানের জন্য রয়েছে বিশেষ হেল্পলাইন সেবা। আমরা প্রতিনিয়ত চেষ্টা করি আপনাকে সর্বোত্তম সহায়তা প্রদান করতে।</p>
      
      <h2>যোগাযোগের মাধ্যম:</h2>
      
      <div class="contact-buttons">

        <button class="cta-btn help-btn" onclick="openHelplineModal()">📞 হেল্পলাইন নম্বর</button>
    
        <div id="helplineModal" class="helpline-modal" style="display: none;">
            <div class="helpline-modal-content">
              <span class="helpline-close" onclick="closeHelplineModal()">&times;</span>
              <h3>📞 আমাদের হেল্পলাইন</h3>
              <p>যেকোনো প্রশ্ন বা সহায়তার জন্য, আমাদের হেল্পলাইন নাম্বারে যোগাযোগ করতে পারেন:</p>
              <p><strong>হেল্পলাইন নাম্বার: 01743094595</strong></p>
              <p>আপনার যদি আরও কোনো প্রশ্ন থাকে, তবে আমাদের সাপোর্ট টিম আপনাকে সহায়তা করবে।</p>
              <a href="tel:+8801743094595">
                <button class="call-btn">📞 কল করুন</button>
              </a>
            </div>
        </div>
            <script src="../java_script/Helpline.js"></script>

</div>
        <button class="cta-btn help-btn" onclick="openEmailModal()">✉ ইমেইল</button>
        <a href="../Html/liveChat.html">
            <button class="cta-btn help-btn">💬 লাইভ চ্যাট</button>
          </a>
      </div>
    </div>
<div id="emailModal" class="custom-email-modal" style="display: none;">
    <div class="custom-modal-content">
      <span class="close" onclick="closeEmailModal()">&times;</span>
      <h3>📩 আমাদের সাথে যোগাযোগ করুন</h3>
      <p>আপনার প্রশ্ন, মতামত বা কোন সহায়তা প্রয়োজন হলে, আমাদের সাথে যোগাযোগ করতে পারেন।</p>
      <p>আমাদের ইমেইল ঠিকানা: <strong>sohajjogan@gmail.com</strong></p>
      <p>ইমেইল পাঠানোর জন্য, আপনি নীচের বাটনে ক্লিক করে সরাসরি আমাদের Gmail পেজে পৌঁছাতে পারবেন। সেখানে আপনি আপনার ইমেইল লিখে পাঠাতে পারবেন।</p>
      <p>ইমেইল পাঠানোর জন্য, নিচের বাটনে ক্লিক করুন:</p>
        <button onclick="openMail()">✉️ এখনই ইমেইল করুন</button>  
    </div>
  </div>
  <!-- 🔧 JavaScript -->
  <script src="../java_script/Email.js"></script>
  </section>
  <section class="report-section">
    <div class="report-text">
        <h1>দোকান রিপোর্ট করুন</h1>
        <p>আমাদের প্ল্যাটফর্মে আপনার নিরাপত্তা ও সন্তুষ্টি সর্বোচ্চ অগ্রাধিকার পায়। যদি কোনো দোকান বা বিক্রেতার আচরণ সন্দেহজনক মনে হয়, প্রতারণার শিকার হন বা অনাকাঙ্ক্ষিত অভিজ্ঞতা হয়ে থাকে, তাহলে দয়া করে আমাদেরকে জানান।</p>
        
        <h2>কিভাবে রিপোর্ট করবেন:</h2>
        <p class="steps">
            ১. দোকানের নাম ও ঠিকানা প্রদান করুন।<br>
            ২. সমস্যার সংক্ষিপ্ত বিবরণ দিন।<br>
            ৩. প্রয়োজনে প্রমাণ বা ছবি সংযুক্ত করুন।
        </p>
        
        <p>আমাদের টিম দ্রুততার সঙ্গে আপনার রিপোর্ট পর্যালোচনা করে প্রয়োজনীয় ব্যবস্থা গ্রহণ করবে। আপনার সহযোগিতার জন্য ধন্যবাদ।</p>
        
        <h2>বিশ্বাসযোগ্যতা বজায় রাখতে আপনার সহায়তা গুরুত্বপূর্ণ!</h2>
     <button class="cta-btn report-btn" id="reportBtn">রিপোর্ট করুন</button>
        
    </div>
   
    <div class="report-image">
        <img src="../Images/complain.jpg" alt="Report Incident Illustration">
    </div>
</section>
<div id="joinPopup" style="display:none; position:fixed; top:40%; left:50%; transform:translate(-50%,-50%); background:#fff; border-radius:14px; box-shadow:0 6px 20px rgba(0,0,0,0.18); padding:32px 40px; z-index:9999; text-align:center;">
    <div style="font-size:20px; color:#222;">দয়া করে আগে <b>লগ ইন করুন</b>!</div>
    <button onclick="document.getElementById('joinPopup').style.display='none'" style="margin-top:17px; padding:7px 20px; border:none; border-radius:6px; background:#1976d2; color:#fff; font-size:15px; cursor:pointer;">নিশ্চিত!</button>
</div>

<script>
window.isLoggedIn = <?php echo isset($_SESSION['customer_email']) ? 'true' : 'false'; ?>;
document.getElementById('reportBtn').addEventListener('click', function(e) {
    if (!window.isLoggedIn) {
        e.preventDefault();
        document.getElementById('joinPopup').style.display = 'block';
    } else {
        window.location.href = 'report.php';
    }
});
</script>
  <section class="promo-section">
    <div class="promo-container">

        <div class="promo-item">
            <img src="../Images/loacl_shop.jpg" alt="Liberty Store">
            <div class="promo-text">
                <h2>ফ্রি ডেলিভারি সহ অনলাইনে দোকান করুন
                </h2>
                <p>নির্বাচিত অঞ্চলে ফ্রি ডেলিভারি
                </p>
                <a href="../Html/Coin.html" class="promo-btn">আরো জানুন
                </a>
            </div>
        </div>

        <div class="promo-item">
            <img src="../Images/mosla.jpg" alt="Luxury Bag">
           <div class="promo-text">
    <h2>নতুনদের জন্য বিশেষ অভিজ্ঞতা!</h2>
    <p>একটি অ্যাকাউন্ট তৈরি করুন, আপনার আশেপাশের দোকানগুলো খুঁজুন আর ঘরে বসেই প্রয়োজনীয় জিনিস পেয়ে যান।</p>
</div>

        </div>

        <div class="promo-item">
            <img src="../Images/mudidokan.jpg" alt="Sewing Inspiration">
            <div class="promo-text">
                <h2>প্রেরণা
                </h2>
                <a href="../Html/Inspiration.html" class="promo-btn">আরো জানুন</a>
            </div>
        </div>

    </div>
</section>
<footer class="footer">
    <div class="footer-links">
        <div class="footer-column">
            <h4>শপিং অনলাই </h4>
            <ul>
                <li><a href="#">ডেলিভারি</a></li>
            </ul>
        </div>

        <div class="footer-column">
            <h4>আমাদের সম্পর্কে</h4>
            <ul>
                <li>
                    <a href="../Html/About_us.html">
                        <img src="../Images/light-bulb.png" alt="info icon" class="link-icon">
                        আমাদের সম্পর্কে বিস্তারিত জানুন
                    </a>
                </li>
            </ul>
        </div>
        
        

        <div class="footer-column">
            <h4>যোগাযোগের তথ্য</h4>
            <ul>
<li><a href="#" onclick="openHelplineModal(); return false;">📞 ফোন</a></li>
<li><a href="#" onclick="openEmailModal(); return false;">✉ ইমেইল</a></li>            </ul>
        </div> 
    </div>

    <div class="footer-bottom">
       
</footer>

</body>
<style>/* General Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    background-color: #fff;
    font-size: 16px; /* Root font size */
}

/* Header Styles */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 70px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    position: fixed; 
    top: 0; 
    width: 100%; 
    z-index: 1000; 
}


header .logo {
    display: flex;
    align-items: center;
}

header .logo img {
    width: 100px;
    height: 100px;
    margin-right: 10px;
}

header h2 {
    font-size: 1.5em; /* 32px */
    font-weight: bold;
    color: #333;
}

header nav ul {
    display: flex;
    list-style-type: none;
}

header nav ul li {
    margin-left: 25px;
}

header nav ul li a {
    text-decoration: none;
    font-family: Arial, sans-serif;
    text-transform: uppercase;
    font-size:1.2em; /* 16px */
    color: #333;
    transition: color 0.3s ease;
}

header nav ul li a:hover {

    border-bottom: 2px solid black;
}

header .icons {
    display: flex;
    align-items: center;
    margin: 25px;
}

header .icons button {
    background-color: transparent;
    border: none;
    margin-left: 15px;
    cursor: pointer;
}

header .icons button img {
    width: 30px;
    height: 30px;
}

/* Hover effects for icons */
header .icons button:hover img {
    filter: brightness(1.2); /* Slightly brighten on hover */
   
}

/* Responsive Design */
@media (max-width: 600px) {
    header {
        flex-direction: column;
        align-items: flex-start;
    }

    header .logo {
        margin-bottom: 20px;
    }

    header nav ul {
        flex-direction: column;
        margin-bottom: 20px;
    }

    header nav ul li {
        margin-left: 0;
        margin-bottom: 10px;
    }

    header .icons {
        margin-top: 10px;
    }
}

.fashion-showcase {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.gallery-container {
    display: flex;
    gap: 10px;
    flex-wrap: nowrap;
    overflow-x: auto; /* Enable horizontal scrolling */
    max-width: 100%;
}

.gallery-item {
    position: relative;
    width: 20%; /* Adjust item width */
}

.gallery-item img {
    width: 100%;
    height: auto;
    display: block;
}

.item-label {
    position: absolute;
    bottom: 10px;
    left: 10px;
    color: white;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.6);
    font-size: 14px;
}


.main-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 40px;
    padding: 80px 5%;
    background: #ffffff;
    border-bottom: 2px solid #eee;
}

.hero-content {
    flex: 1;
    padding-right: 50px;
}

.hero-content h1 {
    font-size:1.4em;
    color: #000000;
    margin-bottom: 25px;
    line-height: 1.2;
}

.hero-content p {
    font-size: 1em;
    line-height: 1.4;
    color: #34495e;
    margin-bottom: 30px;
}

.hero-visual {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

.hero-visual img {
    max-width: 500px;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.cta-btn.main-cta {
    background-color:#4B014B;
    color: white;
    padding: 15px 45px;
    border: none;
    border-radius: 8px;
    font-size: 1.2em;
    cursor: pointer;
    margin-top: 30px;
    width: 400px;
}

.cta-btn.main-cta:hover {
    background-color: #6b146b;
}

/* Responsive Design */
@media (max-width: 600px) {
    .main-hero {
        flex-direction: column;
        padding: 50px 5%;
    }
    
    .hero-content {
        padding-right: 0;
        text-align: center;
    }
    
    .hero-visual {
        justify-content: center;
        margin-top: 30px;
    }
    
    .hero-visual img {
        max-width: 100%;
    }
    
    .hero-content h1 {
        font-size: 2.2rem;
    }
    
    .cta-btn.main-cta {
        font-size: 1.2rem;
        padding: 12px 30px;
    }
}
/* Joy Section Styles */
.joy-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 40px;
    padding: 80px 5%;
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
}

.joy-image {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 30px;
}

.joy-image img {
    max-width: 500px; /* Maximum size */
    width: auto;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 2px solid #fff;
    transition: transform 0.3s ease; /* Smooth scaling */
}

.joy-text {
    flex: 1;
    text-align: right center;
    padding-left: 40px;
}

.joy-text h1 {
    font-size: 1.4em;
    color: #2c3e50;
    margin-bottom: 25px;
    line-height: 1;
}

.joy-text h2 {
    font-size: 1.2em;
    color: #27ae60;
    margin: 35px 0 20px;
}

.joy-text p {
    font-size: 1em;
    line-height: 1.4;
    color: #495057;
    margin-bottom: 25px;
}

.joy-text ul {
    list-style-type: none;
    padding-left: 0;
    text-align: right;
}

.joy-text li {
    font-size: 1em;
    margin-bottom: 15px;
    padding: 8px 0;
}

.cta-btn {
    background-color: #4B014B;
    color: white;
    padding: 15px 45px;
    border: none;
    border-radius: 8px;
    font-size: 1.2em;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 30px;
    width: 350px;
}

.cta-btn:hover {
    background-color: #6b146b;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
}




@media (max-width: 600px) {
    .joy-text h1 {
        font-size: 1.4em;
    }
    
    .joy-text h2 {
        font-size: 1.2em;
    }
    
    .joy-text p {
        font-size: 1.em;
    }
    
    .cta-btn {
        font-size: 1.2em;
        padding: 12px 35px;
    }
}


.ratings-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 40px;
    padding: 80px 5%;
    background: #f9f9f9;
    border-bottom: 2px solid #eee;
}

.ratings-text {
    flex: 1;
    padding-right: 50px;
}

.ratings-text h1 {
    font-size: 1.4em;
    color: #2c3e50;
    margin-bottom: 25px;
    line-height: 1.3;
}

.ratings-text p {
    font-size: 1.2em;
    line-height: 1.8;
    align-items: left center;
    color: #34495e;
    margin-bottom: 30px;
}

.ratings-text h2 {
    font-size: 1.3em;
    color: #27ae60;
    line-height: 1.5;
    margin-top: 40px;
    text-align: left;
}

.ratings-image {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

.ratings-image img {
    max-width: 500px;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

/* Responsive Design */
@media (max-width: 600px) {
    .ratings-section {
        flex-direction: column;
        padding: 50px 5%;
    }
    
    .ratings-text {
        padding-right: 0;
        text-align: center;
    }
    
    .ratings-image {
        justify-content: right center;
        margin-top: 30px;
    }
    
    .ratings-image img {
        max-width: 0%;
    }
    
    .ratings-text h1 {
        font-size: 1.4em;
    }
    
    .ratings-text h2 {
        font-size: 1.3em;
    }
}
/* Help Section */
.help-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 50px 5%;
    background-color: #fff;
    border-top: 2px solid #f0f0f0;
}

.help-image {
    flex: 1;
    display: flex;
    justify-content: center;
    margin-left: 30px;
}

.help-image img {
    max-width: 500px;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.help-text {
    flex: 1;
    text-align: right center;
    padding-right: 40px;
}

.help-text h1 {
    font-size: 1.4em;
    color: #2c3e50;
    margin-bottom: 25px;
    text-align: center;
}
.help-text p{
    font-size: 1.2em;
    color: #2c3e50;
    margin-bottom: 25px;
    text-align: right center;

}
.help-text h2 {
    font-size: 1.3em;
    color: #27ae60;
    margin: 30px 0 20px;
    text-align: center; /* Centers h2 text */
}

.contact-buttons {
    display: flex;
    flex-direction: column;
    align-items: center; /* Centers buttons */
    gap: 5px;
    margin-top: 30px;
}

.cta-btn.help-btn {
    background-color: #6b146b;;
    padding: 15px 30px;
    border-radius: 8px;
    font-size: 1.2em;
    transition: transform 0.2s ease;
    align-self: center; /* Centers the button */
}

.cta-btn.help-btn:hover {
    background-color: #4B014B;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 600px) {
    .help-section {
        flex-direction: column;
        padding: 30px 5%;
    }
    
    .help-image {
        margin-left: 0;
        margin-bottom: 30px;
    }
    
    .help-image img {
        max-width: 100%;
    }
    
    .help-text {
        padding-right: 0;
        text-align: center;
    }
    
    .help-text h2 {
        text-align: center;
    }
    
    .contact-buttons {
        align-items: center;
    }
}
/* Delivery Man Section */
.delivery-man-section {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 40px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.delivery-man-image {
    flex: 1;
    max-width: 40%;
    margin-right: 20px;
}

.delivery-man-image img {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.1);
}

.delivery-man-text {
    flex: 1;
    max-width: 55%;
}

.delivery-man-text h1 {
    font-size: 1.3rem;
    font-weight: bold;
    color: #333;
    margin-bottom: 12px;
}

.delivery-man-text p {
    font-size: 0.95rem;
    color: #555;
    line-height: 1.5;
    margin-bottom: 12px;
}

.delivery-man-text ul {
    margin-left: 18px;
    font-size: 0.95rem;
    color: #555;
    list-style-type: disc;
}

.delivery-man-text h2 {
    font-size: 1.2rem;
    color: #22ae43;
    margin-top: 18px;
    font-weight: bold;
}

.delivery-man-text button.cta-btn {
    background-color: #6b146b;;
    color: white;
    padding: 10px 22px;
    font-size: 0.95rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    display: inline-block;
    margin-top: 16px;
    
}

.delivery-man-text button.cta-btn:hover {
    background-color: #4B014B;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .delivery-man-section {
        flex-direction: column;
        align-items: center;
        padding: 30px;
    }

    .delivery-man-image {
        max-width: 90%;
        margin-bottom: 20px;
    }

    .delivery-man-image img {
        max-height: 300px;
    }

    .delivery-man-text {
        max-width: 100%;
    }

    .delivery-man-text h1 {
        font-size: 1.1rem;
    }

    .delivery-man-text p,
    .delivery-man-text h2 {
        font-size: 0.9rem;
    }

    .delivery-man-text ul {
        margin-left: 15px;
        font-size: 0.9rem;
    }

    .delivery-man-text button.cta-btn {
        font-size: 0.9rem;
        padding: 10px 20px;
    }
}

.report-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 40px;
    padding: 60px 5%;
    background-color: #fff5f5;
    border-top: 2px solid #ffe6e6;
}

.report-text {
    flex: 1;
    padding-left: 30px;
}

.report-text h1 {
    font-size: 1.6em;
    color: #c0392b;
    margin-bottom: 25px;
}

.report-text h2 {
    font-size: 1.3em;
    color: #e74c3c;
    margin: 30px 0 15px;
}

.report-text p {
    font-size: 1.1em;
    line-height: 1.2;
    color: #4a4a4a;
}

.report-text .steps {
    background-color: #fff;
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin: 20px 0;
}

.report-image {
    flex: 1;
    display: flex;
    justify-content: flex-end;
}

.report-image img {
    max-width: 500px;
    height: auto;
    border-radius: 10px;
    border: 2px solid #ffe0e0;
}

.cta-btn.report-btn {
    background-color: #e74c3c;
    padding: 15px 40px;
    font-size: 1.2em;
    margin-top: 25px;
    transition: background-color 0.3s ease;
}

.cta-btn.report-btn:hover {
    background-color: #c0392b;
}

/* Responsive Design */
@media (max-width: 600px) {
    .report-section {
        flex-direction: column;
        padding: 40px 5%;
    }
    
    .report-text {
        padding-left: 0;
        text-align: center;
    }
    
    .report-image {
        justify-content: center;
        margin-top: 30px;
    }
    
    .report-image img {
        max-width: 100%;
    }
    
    .report-text h1 {
        font-size: 1.4em;
    }
    
    .report-text h2 {
        font-size: 1.2em;
    }
}

.custom-email-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.custom-modal-content h3 {
 font-size: 24px;
    color: #333;
    margin-bottom: 10px;
}

.custom-modal-content p {
    font-size: 16px;
    color: #000000;
    line-height: 1.6;
    margin-bottom: 12px;
}

/* Strong Text */
.custom-modal-content strong {
    color: #121213;
    font-weight: 800;
}

/* Modal content */
.custom-modal-content {
    background-color: #fff;
    padding: 20px;
    border-radius: 12px;
    width: 600px;
    text-align: center;
    position: relative;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

/* Close button */
.custom-modal-content .close {
    position: absolute;
    top: -100px;
    right: 14px;
    font-size: 22px;
    cursor: pointer;
}

/* Button style */
.custom-modal-content button {
    background-color: rgb(65, 39, 88); /* Purple color */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.custom-modal-content button:hover {
    background-color: #612878; /* Darker purple when hovered */
}

/* Helpline Modal Container */
.helpline-modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    margin-top: 140px;
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 60px;
    animation: fadeIn 0.5s ease-in-out;
  }
  
  /* Helpline Modal Content */
  .helpline-modal-content {
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    transform: scale(0);
    animation: popUp 0.5s forwards;
  }
  
  /* Close button */
  .helpline-close {
    color: #aaa;
    float: right;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
  }
  
  .helpline-close:hover,
  .helpline-close:focus {
    color: black;
    text-decoration: none;
  }
  
  /* Header style */
  h3 {
    font-size: 24px;
    color: #333;
    margin-bottom: 10px;
  }
  
  /* Paragraph style */
  p {
    font-size: 16px;
    color: #fafafa;
    line-height: 1.6;
    margin-bottom: 12px;
  }
  
  /* Strong text style */
  strong {
    color: #2c3e50;
    font-weight: 600;
  }
  
  /* Pop-up animation */
  @keyframes popUp {
    0% {
      transform: scale(0);
    }
    100% {
      transform: scale(1);
    }
  }
  
  /* Fade-in background animation */
  @keyframes fadeIn {
    0% {
      opacity: 0;
    }
    100% {
      opacity: 1;
    }
  }

/* Call Button */
.call-btn {
    background-color: rgb(65, 39, 88); /* Purple color */
    color: white;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 9px;
    cursor: pointer;
    margin-top: 15px;
    width: 100%;
    transition: background-color 0.3s ease;
  }
  
  .call-btn:hover {
    background-color: #612878; /* Darker purple when hovered */
  }
  
  
.footer-column h4 {
    text-align: center;
    font-size: 1.3rem;
    color: #333;
    margin-bottom: 10px;
}

.footer-column ul li a {
    text-decoration: none;
    color: #ddd;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.footer-column ul li a:hover {
    color: #2ecc71;
    transform: translateX(5px);
}

.link-icon {
    width: 18px;
    height: 18px;
    filter: brightness(90%);
    transition: filter 0.3s ease;
}

.footer-column ul li a:hover .link-icon {
    filter: brightness(120%);
}


.footer {
    background-color: #f8f8f8;
    padding: 30px;
    font-family: Arial, sans-serif;
}

/* Removed .newsletter and .newsletter-signup styles */

.footer-links {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.footer-column h4 {
    font-size: 1.4em;
    font-weight: bold;
}

.footer-column ul {
    list-style: none;
    padding: 0;
}

.footer-column ul li a {
    text-decoration: none;
    color: black;
    font-size: 1.2em;
}

.footer-bottom {
    text-align: center;
    margin-top: 20px;
}




/* Sidebar Styling */
.sidebar {
    position: fixed;
    right: -50%; /* Initially hidden */
    width: 30%; /* Adjusted to take half the screen */
    height: 100%;
    background: white;
    box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
    transition: right 0.4s ease-in-out;
    z-index: 980;
    display: flex;
    flex-direction: column;
    justify-content: center;
    margin-top: 50px;

}

/* Sidebar shows when class is added */
.sidebar.show {
    right: 0;
}

/* Close button inside the sidebar */
.close-btn {
    position: absolute;
    margin-top: 50px; /**/
    right: 20px; /* Adjust position from right */
    color: rgb(0, 0, 0);
    background: none;
    border: none;
    cursor: pointer;
    z-index: 980; /* Ensure it stays on top */
    
}


/* Sidebar content styling */
.sidebar-content {
    width:80%;
    margin: auto;
}

/* Sidebar heading */
.sidebar h2 {
    font-size: 24px; /* Large font size */
    margin-bottom: 30px;
    text-align: center;
    color: #000000;
    font-weight: bold;
}

/* Label styling */
.sidebar label {
    font-size: 16px;
    font-weight: bold;
    display: block;
    margin: 10px 0 10px;
}

/* Input fields */
.sidebar input {
    width: 100%;
    font-size: 1em;
    border: none;
    border-bottom: 2px solid black;
    outline: none;
    background: transparent;
}

/* Submit button styling */
.sidebar button {
    font-size: 1em;
    width: 100%;
    background: #4B014B;
    color: white;
    border-radius: 25px;
    border: none;
    cursor: pointer;
    text-transform: uppercase;
    margin: 10px 0;
}

/* Sign-up button styling */
.sidebar .signup-btn {
    font-size: 1em;
    padding: 15px;
    width: 100%;
    border: 2px solid black;
    background: white;
    color: black;
    border-radius: 25px;
    cursor: pointer;
    margin-bottom: 20px;
}

/* Options section (checkbox and forgot password link) */
.sidebar .options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 1em;

}

/* Checkbox style */
.sidebar .options input {
    width: 15px;
    height: 15px;
    margin-right: 10px;
    font-size: 12px;
 
}

/* "Forgot Password" link style */
.sidebar .options a {
    text-decoration: none;
    color: #4B014B;
    font-weight: bold;
    font-size: 16px;
 
}

/* Overlay effect */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 950;
    display: none;
}

/* When sidebar is visible */
.sidebar.show {
    right: 0;
}

/* Modal Styling */
.signup-modal {
    display: none;
    position: fixed;
    top: 50%; /* Center vertically */
    left: 40%; /* Center horizontally */
    transform: translate(-50%, -50%); /* Adjust to truly center */
    width: 100%;
    max-width: 700px; /* Limit maximum width */
    background: white;
    padding: 30px;
    border-radius: 10px;
    z-index: 1000;
    text-align: center;
    box-shadow: 0 0 10px black;
}
/* Modal Content */
.signup-modal-content {
    width: 100%;
    padding: 20px;
}
/* Close button in modal */
.signup-modal .close-modal-btn {
    font-size: 40px;
    position: absolute;
    top: 10px;
    right: 20px;
    cursor: pointer;
}

/* Button styling for modal */
.signup-modal button {
    padding: 10px 20px;
    font-size: 24px;
    margin-top: 20px;
    cursor: pointer;
    background: #4B014B;
    color: white;
    border: none;
    border-radius: 25px;
    width: 100%;
}


/* Modal styling */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    width: 300px;
}

.close-btn {
    font-size: 30px;
    position: absolute;
    top: 10px;
    right: 30px;
    cursor: pointer;
}

/* Sidebar for Customer Registration */
.customer-sidebar {
    position: fixed;
    right: -50%; /* Initially hidden */
    width: 30%; /* Sidebar takes 40% of the screen width */
    height: 100%;
    background: white;
    box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
    transition: right 0.4s ease-in-out;
    z-index: 985;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding-top: 160px; /* Space from top */
    overflow: hidden; /* Hide overflow */
    padding: 30px;
    padding-top: 120PX;
    margin-top: 70px;
}

/* Show sidebar when class is added */
.customer-sidebar.show {
    right: 0;
}



/* Close Button */
.closeCustomer-btn {
    position: absolute;
    margin-top: 80px; /* Adjusted for top spacing */
    top: 80px;
    right: 20px;
    font-size: 30px;
    color: #5c5b5b;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 981; /* Ensure it stays on top */
}

.closeCustomer-btn:hover {
    color: rgb(0, 0, 0);
}

/* Sidebar Title */
.customer-sidebar h2 {
    font-size: 14px;
    margin-bottom: 8px;
    text-align: center;
    font-weight: bold;
    color: #000000;
}

/* Form elements */
.customer-sidebar label {
    font-size:14px;
    font-weight: bold;
    display: block;
    color: #333;
    margin-bottom: 2px; 
}

.customer-sidebar input,
.customer-sidebar select {
    width: 100%;
    font-size: 12px;
    border: 1px solid #000000;
    border-radius: 4px;
    outline: none;
    height: 32px; /* Explicitly set height */
    margin-bottom: 4px; /* Reduce gap between input/select fields */
    padding: 4px 6px; /* Adjust padding to reduce overall space */
}


/* Submit Button */
.customer-sidebar button {
    font-size: 16px;
    padding: 12px;
    width: 100%;
    background: #4B014B;
    color: white;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    margin-top:8px;
    text-transform: uppercase;
}

.customer-sidebar button:hover {
    background-color: #3d003d; /* Darker shade for hover effect */
}

/* Adjust for mobile */
@media (max-width: 600px) {
    .customer-sidebar {
        width: 100%; /* Sidebar takes full width on smaller screens */
    }
}

.shop-owner-modal {
    display: none;
    position: fixed;
    margin-top: 20px;
    top: 50%; /* Center vertically */
    left: 50%; /* Center horizontally */
    transform: translate(-50%, -50%); /* Adjust to truly center */
    width: 80%; /* Makes the modal 70% of the screen width */
    height: 92%; /* Makes the modal height 30% of the screen */
    max-width: 1800px; /* Sets a max-width to prevent it from getting too large */
    background: white;
    padding: 50px; /* Increased padding for more space inside */
    border-radius: 12px; /* Rounded corners */
    z-index: 1000;
    text-align: left; /* Align text to the left */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); /* Deep shadow for more depth */
    font-size: 1.2em; /* Set a base font size */
    box-shadow: 0 0 10px black;
}

.shop-owner-modal .modal-inner {
    padding: 30px;
}

.shop-owner-modal h1 {
    font-size: 24px; /* Increased font size for larger headings */
    margin-bottom: 20px;
}

.shop-owner-modal h2 {
    font-size: 18px; /* Larger subheading */
    margin-bottom: 20px;
}

.shop-owner-modal h3 {
    font-size: 18px; /* Larger font size for subheading */
    font-weight: bold;
    color: #FF0000; /* Red color for emphasis */
    margin-top: 30px;
    margin-bottom: 2px;
    text-align: left center;
}

.shop-owner-modal h4 {
    font-size: 18px; /* Larger font size for subheading */
    font-weight: bold;
    color: #FF0000; /* Red color for emphasis */
    margin-top: 30px;
    margin-bottom: 15px;
    text-align: center;
}
.shop-owner-modal h6 {
    font-size: 18px; /* Larger font size for subheading */
    font-weight: bold;
    color: #000000; /* Red color for emphasis */

    margin-bottom: 15px;
    text-align: left;
}
.shop-owner-modal p, 
.shop-owner-modal li {
    font-size: 16px; /* Slightly larger font size for readability */
    line-height: 1.5; /* More spacing for better text readability */
}

.shop-owner-modal button {
    background-color: #4B014B; /* Green background */
    color: white;
    padding: 18px 35px; /* Padding for top/bottom and left/right */
    font-size: 18px; /* Button font size */
    border: none;
    border-radius: 10px;
    width: auto; /* Automatically adjust width based on content */
    height: auto; /* Adjust height based on content */
    display: block; /* Makes the button block-level element */
    margin: 20px auto; /* Centers the button horizontally */
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.shop-owner-modal button:hover {
    background-color: #6b146b; /* Darker green on hover */
}

  
/* Close button styling */
.close-btn {
    color: #aaa;
    font-size: 30px; /* Bigger close button */
    font-weight: bold;
    position: absolute;
    top: 40px;
    right: 20px;
    cursor: pointer;
}

.close-btn:hover,
.close-btn:focus {
    color: black;
    text-decoration: none;
}

.shop-owner-modal .modal-content {
    margin-top: 10px;
}


@media (max-width: 600px) {
    .shop-owner-modal {
        width: 90%; /* Takes up 90% of the screen width */
        height: 92%; /* Adjust height for smaller screens */

    }

    .shop-owner-modal h1 {
        font-size: 16px; /* Smaller font size for heading */
    }

    .shop-owner-modal h2 {
        font-size: 14px; /* Smaller font size for subheading */
    }

    .shop-owner-modal h3 {
        font-size: 12px; /* Smaller font size for subheading */
    }

    .shop-owner-modal p, 
    .shop-owner-modal li {
        font-size: 10px; /* Smaller font size for text */
    }

    .shop-owner-modal button {
        font-size: 12px; /* Smaller font size for the button */
        padding: 14px 25px; /* Smaller button padding */
    }

    .close-btn {
        font-size: 20px; /* Smaller close button on mobile */
    }
}
/* Sidebar Styling */
.registration-sidebar {
    position: fixed;
    right: -50%; /* Initially hidden */
    width: 30%; /* Adjusted to take half the screen */
    height: 100%;
    background: white;
    box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
    transition: right 0.4s ease-in-out;
    padding-top: 100px; /* More padding for spacing */
    z-index: 981;
    display: flex;
    flex-direction: column;
    justify-content: center;
    top: 40px;
    
}


/* When the sidebar is open, slide it into view */
#registrationSidebar.open {
    right: 0; /* Move sidebar into view */
}

/* Close button inside the sidebar */
#closeRegistrationSidebar {
    position: absolute;
top: 70px;
    right: 20px;
    font-size: 30px;
    cursor: pointer;
}

/* Sidebar Content Styling */
.registration-sidebar-content {
    padding: 20px;
    font-family: Arial, sans-serif;
}

/* Heading */
h2 {
    font-size: 16px;
    text-align: center;
    margin-bottom: 20px;
}

/* Form Labels */
label {
    font-size: 12px;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}

/* Input Fields (Reduced Height) */
input {
    width: 100%;
    padding: 6px; /* Less padding */
    font-size: 11px;
    border: 1px solid #ccc;
    border-radius: 4px;
    height: 30px; /* Reduced height */
    margin-bottom: 2px;
}



/* Submit Button */
button {
    width: 100%;
    padding: 12px;
    background-color: #4B014B;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 10px;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #6b146b; /* Slightly lighter shade */
}


/* Styling the dropdown container (select element) */
#shopOwnerGender ,#shopType{
    width: 100%; /* Full width */
    padding: 10px; /* Padding inside the dropdown */
    font-size: 12px; /* Font size for the options */
    border: 1px solid #ccc; /* Border around the dropdown */
    border-radius: 4px; /* Rounded corners */
    background-color: #fff; /* Background color */
    outline: none; /* Removes the default focus outline */
    margin-bottom: 2px; /* Space below the dropdown */
}

/* Style for the options inside the dropdown */
#shopOwnerGender option ,
#shopType option {
    font-size: 10px; /* Font size for options */
    padding: 10px; /* Padding inside each option */
    background-color: #fff; /* Background color for options */
    color: #333; /* Text color for the options */
}

/* Change background color when hovering over options */
#shopOwnerGender option:hover,
#shopType option:hover {
    background-color: #f0f0f0; /* Light gray background on hover */
}

/* Optionally add a focus style when the user selects the dropdown */
#shopOwnerGender:focus ,
#shopType:focus {
    border-color: #4B014B; /* Border color when focused */
}

textarea {
    width: 100%; /* Full width */
    height: 90%;
    padding: 10px; /* Padding inside the textarea */
    font-size: 12px; /* Font size for the text */
    border: 1px solid #ccc; /* Border around the textarea */
    border-radius: 4px; /* Rounded corners */
    margin-bottom: 2px; /* Space below the textarea */
    resize: vertical; /* Allow resizing only vertically */
    transition: border-color 0.3s ease; /* Smooth transition for border color */
}

/* Textarea focus effect */
textarea:focus {
    border-color: #4B014B; /* Change border color on focus */
    outline: none; /* Remove the default outline */
}
/* Sidebar Content Scrolling */
.registration-sidebar-content {
    padding: 20px;
    font-family: Arial, sans-serif;
    overflow-y: auto; /* Enables vertical scrolling */
    max-height: 80vh; /* Limits height to allow scrolling */
}

/* Hide scrollbar for Chrome, Safari, and Edge */
.registration-sidebar-content::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

/* Hide scrollbar for Firefox */
.registration-sidebar-content {
    scrollbar-width: none;
}

/* Hide scrollbar for Internet Explorer and Edge */
.registration-sidebar-content {
    -ms-overflow-style: none;
}


.map-button {
    background-color: #4B014B; /* Green background */
    color: white; /* White text */
    padding: 4px 16px; /* Padding for better size */
    margin-top: 8px; /* Space above button */
    margin-left: 1px; /* Small gap from the input field */
    border: none; /* No default border */
    border-radius: 4px; /* Slightly rounded corners */
    cursor: pointer; /* Pointer cursor on hover */
    font-size: 14px; /* Text size */
    transition: background-color 0.3s ease;
    max-width: 140px;
    max-height: 80px;
    margin-bottom: 20px;
  }
  
  .map-button:hover {
    background-color: #6b146b; /* Slightly darker green on hover */
  }
  
/* Map Modal Container */
.map-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 600px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
  }
  
  /* Hidden by default */
  .hidden {
    display: none;
  }
  
  /* Modal Heading */
  .map-modal h3 {
    margin: 0;
    font-size: 20px;
    text-align: center;
    color: #333;
  }
  
  /* Close Button */
  .close-icon {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 24px;
    cursor: pointer;
    color: #555;
    transition: color 0.2s ease;
  }
  
  
  #saveLocation {
    background-color: #4B014B;
    color: white;
    border: none;
    padding: 10px 16px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s ease;
  }
  
  #locateButton:hover,
  #saveLocation:hover {
    background-color: #6b146b;
  }
  
  /* Button Layout */
  #locateButton {
    margin-top: 10px;
  }
  
  #saveLocation {
    align-self: flex-end;
  }
  

/* Modal Background */
.delivery-man-modal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5); /* Dark background */
}

/* Modal Content */
.delivery-man-modal-content {
    background-color: #fff;
    margin: 0;
    padding: 30px 25px;
    border-radius: 10px;
    width: 90%;
    max-width: 900px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%); /* Centering */
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

/* Close Button */
.close-modal {
    color: #aaa;
    float: right;
    font-size: 26px;
    font-weight: bold;
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
}

.close-modal:hover {
    color: #000;
}

/* Section Styling */
.delivery-man-guidelines h1 {
    font-size: 22px;
    margin-bottom: 15px;
    color: #333;
}

.delivery-man-guidelines p {
    margin-bottom: 15px;
    color: #555;
    line-height: 1.5;
}

.delivery-man-guidelines ul {
    list-style: disc;
    padding-left: 20px;
    color: #444;
    margin-bottom: 20px;
}

.delivery-man-guidelines ul li {
    margin-bottom: 10px;
}

/* Accept Button */
#acceptTermsBtn {
    padding: 10px 18px;
    background-color: #4B014B;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    cursor: pointer;
}

#acceptTermsBtn:hover {
    background-color: #6b146b;
}


/* The Modal (delivery man registration form) */
.deliveryManModal {
    display: none; /* Hidden by default */
    position: fixed;
    z-index: 1000; /* Ensure it's above other elements */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4); /* Black background with transparency */
    overflow: auto;
    padding-top: 60px;
}

/* Modal content */
.modal-content {
    background-color: #fff;
    margin: 5% auto; /* Center the modal */
    padding: 20px;
    width: 80%; /* Can be adjusted */
    max-width: 600px; /* Set a max width */
    border-radius: 8px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 120px; /* Space from top */
}

/* Close button (X) */
.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 15px;
    right: 20px;
    cursor: pointer;
    margin-top: 120px;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

/* Modal Title */
.modal-content h2 {
    text-align: center;
    font-size: 18px;
    margin-bottom: 20px;
}

/* Form Labels */
.modal-content label {
    display: block;
    font-size: 14px;
    margin-bottom: 5px;
    color: #333;
}

/* Form Inputs */
.modal-content input[type="text"],
.modal-content input[type="tel"],
.modal-content input[type="password"],
.modal-content input[type="file"],
.modal-content textarea {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

/* Button Styling */
#submitDeliveryForm {
    background-color: #4B014B;;
    color: white;
    padding: 15px 20px;
    border: none;
    border-radius: 5px;
    width: 100%;
    font-size: 14px;
    cursor: pointer;
}

#submitDeliveryForm:hover {
    background-color: #6b146b;
}

/* Textarea for address */
textarea {
    height: 100px; /* Allow for more text */
    resize: vertical;
}

/* Optional responsiveness for small devices */
@media screen and (max-width: 600px) {
    .modal-content {
        width: 95%;
        padding: 15px;
    }
    #submitDeliveryForm {
        padding: 12px;
    }
}

/* Sidebar Styling */
.deliveryManSidebar {
    position: fixed;
    right: -50%; /* Initially hidden */
    width: 30%; /* Adjusted width for the sidebar */
    height: 100%;
    background: white;
    box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
    transition: right 0.4s ease-in-out; /* Smooth transition for sliding effect */
    padding-top: 20px; /* Adjusted padding */
    z-index: 981; /* Ensure it stays on top */
    display: flex;
    flex-direction: column;
    justify-content: flex-start; /* Align content to the top */
    top: 0;
    overflow-y: auto; /* Allow vertical scrolling */
    margin-top: 70px; /* Space from top */
}

/* When the sidebar is open, slide it into view */
#deliveryManRegisterSidebar.open {
    right: 0; /* Move sidebar into view */
}

/* Close button inside the sidebar */
#closeDeliveryManRegisterSidebar {
    position: absolute;
    top: -10px; /* Adjusted to keep the close button at the top */
    right: 20px;
    font-size: 25px;
    cursor: pointer;
}

/* Sidebar Content Styling */
.deliveryManSidebar-content {
    padding: 20px;
    font-family: Arial, sans-serif;
}

/* Heading */
h2 {
    
    font-size: 16px;
    text-align: center;
    margin-bottom: 20px;
}

/* Form Labels */
label {
    font-size: 12px;
    font-weight: bold;
    display: block;
    margin-bottom: 10px;
}

/* Input Fields */
input, textarea {
    width: 100%;
    padding: 8px;
    font-size: 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 10px;
    height: 35px;
}

/* Submit Button */
button {
    width: 100%;
    padding: 12px;
    background-color: #4B014B;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 10px;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #6b146b; /* Slightly lighter shade */
}

/* Styling the textarea */
textarea {
    height: 60px; /* Adjusted height for the textarea */
    resize: vertical; /* Allow vertical resizing */
}

/* Focus Effect on Input Fields */
input:focus, textarea:focus {
    border-color: #4B014B; /* Highlight input borders on focus */
    outline: none; /* Remove default focus outline */
}

/* Sidebar Content Scrolling */
.deliveryManSidebar-content {
    overflow-y: auto; /* Enable vertical scrolling if content overflows */
    max-height: 80vh; /* Limit height to allow scrolling */
}

/* Hide scrollbar for Chrome, Safari, and Edge */
.deliveryManSidebar-content::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

/* Hide scrollbar for Firefox */
.deliveryManSidebar-content {
    scrollbar-width: none;
}

/* Hide scrollbar for Internet Explorer and Edge */
.deliveryManSidebar-content {
    -ms-overflow-style: none;
}
/* Gender select dropdown */
#deliveryGender {
    padding: 10px;
    font-size: 12px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fff;
    color: #333;
    cursor: pointer;
    box-sizing: border-box;
    margin-bottom: 15px;
}

/* Label for the Gender dropdown */
label[for="deliveryGender"] {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

/* Focus effect for the dropdown */
#deliveryGender:focus {
    border-color: #4B014B; /* Change to match sidebar button color */
    outline: none;
    box-shadow: 0 0 5px rgba(18, 18, 18, 0.5);
}

/* Style for the placeholder text (optional) */
#deliveryGender option:disabled {
    color: #999;
}

/* Hover effect for options */
#deliveryGender option:hover {
    background-color: #f0f0f0;
}

#imagePreview {
    max-width: 200px; /* Limit the size of the preview image */
    max-height: 200px;
    margin-top: px;
    border: 1px solid #ccc;
    padding: 5px;
    display: none; /* Initially hidden */
}

/* Popup base */
.popup {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
}

/* Popup content */
.popup-content {
    background-color: #fff;
    padding: 30px 25px;
    border-radius: 10px;
    width: 350px;
    text-align: center;
    position: relative;
    box-shadow: 0 5px 10px rgba(0,0,0,0.2);
}

/* Close button */
.closeCustomer-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    color: #555;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
}

.closeCustomer-btn:hover {
    color: red;
}

.popup-content label, .popup-content select, .popup-content input {
    display: block;
    width: 100%;
    margin: 15px 0 10px;
    font-size: 16px;
}

.popup-content input, .popup-content select {
    padding: 8px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

.popup-content button {
    padding: 10px 20px;
    font-size: 16px;
    margin-top: 15px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.popup-content button:hover {
    background-color: #0056b3;
}

/* Style for the label */
label[for="userType"] {
    font-weight: bold;
    font-size: 16px;
    display: block;
    margin-bottom: 8px;
    color: #333;
}

/* Style for the select dropdown */
select[name="userType"] {
    width: 100%;
    padding: 10px 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 6px;
    background-color: #fff;
    color: #333;
    outline: none;
    transition: border-color 0.3s ease;
    appearance: none;
}

/* Focus effect */
select[name="userType"]:focus {
    border-color: #4CAF50;
    box-shadow: 0 0 5px rgba(76, 175, 80, 0.4);
}

/* Optional: style the dropdown arrow (for Webkit-based browsers) */
select[name="userType"]::-webkit-scrollbar {
    width: 6px;
}
.search-bar-form {
    display: flex;
    align-items: center;
    gap: 7px;
    background: #f6faf7;
    border-radius: 8px;
    padding: 7px 12px;
    box-shadow: 0 1px 6px 0 rgba(28,124,84,0.04);
}

.search-bar-form input[type="text"] {
    padding: 9px 14px;
    border: 1px solid #c2eccb;
    border-radius: 7px;
    font-size: 1rem;
    width: 220px;
    outline: none;
    transition: border-color 0.2s;
    background: #fff;
}

.search-bar-form input[type="text"]:focus {
    border-color: #1c7c54;
}

.search-bar-form button {
    background: #1c7c54;
    border: none;
    border-radius: 7px;
    padding: 7px 13px;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.2s;
}

.search-bar-form button:hover {
    background: #0a4025;
}

.search-bar-form button img {
    width: 24px;
    height: 24px;
    display: block;
}
.forgot-close-btn {
    position: absolute;
    top: 12px;
    right: 18px;
    font-size: 26px;
    color: #c00;
    cursor: pointer;
    font-weight: bold;
    transition: color 0.2s;
}
.forgot-close-btn:hover {
    color: #a00;
}</style>
</html>  