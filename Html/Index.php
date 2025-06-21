<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Index.css"> <!-- Correct CSS path -->
</head>
<body>
    <header>
              <div class="logo" id="logoClickable" style="cursor:pointer;">
    <img src="../Images/Logo.png" alt="Liberty Logo">
    <h2>সহজ যোগান</h2>
</div>
<script>
    document.getElementById('logoClickable').addEventListener('click', function() {
        window.location.href = '../Html/Index.html';
    });
</script>
        <nav>
            <ul>
                <li><a href="../Html/New_Collection.html">নতুন এসেছে</a></li>
                <li><a href="../Html/Women.html">নারী</a></li>
                <li><a href="../Html/Man.html">পুরুষ</a></li>
                <li><a href="../Html/Gift.html">উপহার</a></li>
                <li><a href="../Html/Admin.php">লাইব্রেরি</a></li>
            </ul>
        </nav>
        <div class="icons">
<div class="search-bar">
        <form method="get" action="" class="search-bar-form">
            <input type="text" name="search" placeholder="পণ্যের নাম লিখুন..." value="" required>
            <button id="submit"><img src="../Images/search.png" alt="Search"></button>
        </form>
    </div>            <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
            <button><img src="../Images/heart.png" alt="Wishlist"></button>
        </div>
    </header>
 <!-- Login Sidebar -->
<div id="loginSidebar" class="sidebar">
    <div class="sidebar-content">
        <span id="closeSidebar" class="close-btn">&times;</span>
        <h2>লগইন করুন</h2>
        <form action="../PHP/login.php" method="POST">
            <label for="userType">আপনার পরিচয়:</label>
            <select name="userType" required>
                <option value="customer" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='customer') echo 'selected'; ?>>গ্রাহক</option>
                <option value="shop_owner" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='shop_owner') echo 'selected'; ?>>দোকান মালিক</option>
                <option value="delivery_man" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='delivery_man') echo 'selected'; ?>>ডেলিভারি ম্যান</option>
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
<span class="forgot-close-btn" id="closeForgotModal">&times;</span>        <h2>পাসওয়ার্ড রিসেট করুন</h2>
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

            <!-- Shop Photos -->
            <label for="shopOwnerPic">দোকান মালিকের ছবি:</label>
            <input type="file" id="shopOwnerPic" name="shopOwnerPic" accept="image/*" required>
            <!-- Shop Name -->
            <label for="shopName">দোকানের নাম:</label>
            <input type="text" id="shopName" name="shopName" required placeholder="আপনার দোকানের নাম লিখুন">
 
            <label for="shopPic">দোকানের ছবি:</label>
            <input type="file" id="shopPic" name="shopPic" accept="image/*" required>

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

<script src="../java_script/Owner_signUp.js"></script>


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
                <h1 class="title">স্বাগতম! আপনার প্রয়োজনীয় সবকিছু এক জায়গায়</h1>
             
                <p class="description">আমরা আপনাকে সর্বোচ্চ মানসম্পন্ন সেবা প্রদান করতে প্রতিশ্রুতিবদ্ধ</p>
            </div>
            <div class="button-wrapper">
                <a href="../Html/map.html" class="shop-now-btn">
                    অবস্থান
                    <img src="../Images/location.png" alt="Location Icon" style="width: 50px; height: 50px; vertical-align: middle; margin-left: 10px;">
                </a>
            </div>
        </section>
        
        <section class="fashion-showcase">
            <div class="gallery-container">
                <div class="gallery-item">
                    <img src="../Images/delevery .jpg" alt="Fabric Design">
                    <p class="item-label">LIBERTY FABRICS SS25: RETOLD</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/Up.jpg" alt="Fragrance">
                    <p class="item-label">LIBERTY LBTY. FRAGRANCE</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/Courier.png" alt="Luxury Dress">
                    <p class="item-label">DRESSES</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/comment.jpg" alt="Luxury Bags">
                    <p class="item-label">BAGS</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/home_delivery.jpg" alt="Jewellery">
                    <p class="item-label">NEW IN: JEWELLERY</p>
                </div>
            </div>
        </section>
    
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
        <a href="report.html">
            <button class="cta-btn report-btn">রিপোর্ট করুন</button>
        </a>
        
    </div>
    <div class="report-image">
        <img src="../Images/complain.jpg" alt="Report Incident Illustration">
    </div>
</section>
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
                <h2>নতুন গ্রাহকদের জন্য ১৫% ছাড়!
                </h2>
                <p>এই অফারের সুবিধা নিতে সাইন আপ করুন
                </p>
                <a href="#" class="promo-btn" title="একটি অ্যাকাউন্ট তৈরি করতে সাইন আপ করুন">সাইন আপ</a>

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
                <li><a href="#">অর্ডার হিস্টোরি</a></li>
                <li><a href="#">পেমেন্ট </a></li>
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
</html>  <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan)</title>
    <link rel="stylesheet" href="../CSS/Index.css"> <!-- Correct CSS path -->
</head>
<body>
    <header>
              <div class="logo" id="logoClickable" style="cursor:pointer;">
    <img src="../Images/Logo.png" alt="Liberty Logo">
    <h2>সহজ যোগান</h2>
</div>
<script>
    document.getElementById('logoClickable').addEventListener('click', function() {
        window.location.href = '../Html/Index.html';
    });
</script>
        <nav>
            <ul>
                <li><a href="../Html/New_Collection.html">নতুন এসেছে</a></li>
                <li><a href="../Html/Women.html">নারী</a></li>
                <li><a href="../Html/Man.html">পুরুষ</a></li>
                <li><a href="../Html/Gift.html">উপহার</a></li>
                <li><a href="../Html/Admin.php">লাইব্রেরি</a></li>
            </ul>
        </nav>
        <div class="icons">
<div class="search-bar">
        <form method="get" action="" class="search-bar-form">
            <input type="text" name="search" placeholder="পণ্যের নাম লিখুন..." value="" required>
            <button id="submit"><img src="../Images/search.png" alt="Search"></button>
        </form>
    </div>            <button id="userIcon"><img src="../Images/Sample_User_Icon.png" alt="User"></button>
            <button><img src="../Images/heart.png" alt="Wishlist"></button>
        </div>
    </header>
 <!-- Login Sidebar -->
<div id="loginSidebar" class="sidebar">
    <div class="sidebar-content">
        <span id="closeSidebar" class="close-btn">&times;</span>
        <h2>লগইন করুন</h2>
        <form action="../PHP/login.php" method="POST">
            <label for="userType">আপনার পরিচয়:</label>
            <select name="userType" required>
                <option value="customer" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='customer') echo 'selected'; ?>>গ্রাহক</option>
                <option value="shop_owner" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='shop_owner') echo 'selected'; ?>>দোকান মালিক</option>
                <option value="delivery_man" <?php if(isset($_COOKIE['remember_userType']) && $_COOKIE['remember_userType']=='delivery_man') echo 'selected'; ?>>ডেলিভারি ম্যান</option>
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
<span class="forgot-close-btn" id="closeForgotModal">&times;</span>        <h2>পাসওয়ার্ড রিসেট করুন</h2>
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

            <!-- Shop Photos -->
            <label for="shopOwnerPic">দোকান মালিকের ছবি:</label>
            <input type="file" id="shopOwnerPic" name="shopOwnerPic" accept="image/*" required>
            <!-- Shop Name -->
            <label for="shopName">দোকানের নাম:</label>
            <input type="text" id="shopName" name="shopName" required placeholder="আপনার দোকানের নাম লিখুন">
 
            <label for="shopPic">দোকানের ছবি:</label>
            <input type="file" id="shopPic" name="shopPic" accept="image/*" required>

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

<script src="../java_script/Owner_signUp.js"></script>


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
                <h1 class="title">স্বাগতম! আপনার প্রয়োজনীয় সবকিছু এক জায়গায়</h1>
             
                <p class="description">আমরা আপনাকে সর্বোচ্চ মানসম্পন্ন সেবা প্রদান করতে প্রতিশ্রুতিবদ্ধ</p>
            </div>
            <div class="button-wrapper">
                <a href="../Html/map.html" class="shop-now-btn">
                    অবস্থান
                    <img src="../Images/location.png" alt="Location Icon" style="width: 50px; height: 50px; vertical-align: middle; margin-left: 10px;">
                </a>
            </div>
        </section>
        
        <section class="fashion-showcase">
            <div class="gallery-container">
                <div class="gallery-item">
                    <img src="../Images/delevery .jpg" alt="Fabric Design">
                    <p class="item-label">LIBERTY FABRICS SS25: RETOLD</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/Up.jpg" alt="Fragrance">
                    <p class="item-label">LIBERTY LBTY. FRAGRANCE</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/Courier.png" alt="Luxury Dress">
                    <p class="item-label">DRESSES</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/comment.jpg" alt="Luxury Bags">
                    <p class="item-label">BAGS</p>
                </div>
                <div class="gallery-item">
                    <img src="../Images/home_delivery.jpg" alt="Jewellery">
                    <p class="item-label">NEW IN: JEWELLERY</p>
                </div>
            </div>
        </section>
    
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
        <a href="report.html">
            <button class="cta-btn report-btn">রিপোর্ট করুন</button>
        </a>
        
    </div>
    <div class="report-image">
        <img src="../Images/complain.jpg" alt="Report Incident Illustration">
    </div>
</section>
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
                <h2>নতুন গ্রাহকদের জন্য ১৫% ছাড়!
                </h2>
                <p>এই অফারের সুবিধা নিতে সাইন আপ করুন
                </p>
                <a href="#" class="promo-btn" title="একটি অ্যাকাউন্ট তৈরি করতে সাইন আপ করুন">সাইন আপ</a>

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
                <li><a href="#">অর্ডার হিস্টোরি</a></li>
                <li><a href="#">পেমেন্ট </a></li>
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
</html>  