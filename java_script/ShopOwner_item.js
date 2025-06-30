document.addEventListener("DOMContentLoaded", function () {
    // Sidebar logic
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

    function toggleSidebar(sidebar) {
        const isOpen = sidebar.classList.contains("show");
        closeAllSidebars();
        if (!isOpen) {
            sidebar.classList.add("show");
            overlay.classList.add("show");
        }
    }
    function closeAllSidebars() {
        document.querySelectorAll(".sidebar").forEach(sidebar => {
            sidebar.classList.remove("show");
        });
        overlay.classList.remove("show");
    }
    userIcon && userIcon.addEventListener("click", function () { toggleSidebar(userSidebar); });
    notificationIcon && notificationIcon.addEventListener("click", function () { toggleSidebar(notificationSidebar); });
    messengerIcon && messengerIcon.addEventListener("click", function () { toggleSidebar(messengerSidebar); });
    closeUserSidebar && closeUserSidebar.addEventListener("click", closeAllSidebars);
    closeNotification && closeNotification.addEventListener("click", closeAllSidebars);
    closeMessenger && closeMessenger.addEventListener("click", closeAllSidebars);
    overlay && overlay.addEventListener("click", closeAllSidebars);

    // Logout
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

    // --------- Product Form Popup Logic ---------
    console.log("ShopOwner_item.js loaded!");

    var openFormBtn = document.getElementById("openFormBtn");
    var plusBtn = document.getElementById("plusBtn");
    var productForm = document.getElementById("productForm");

    console.log("openFormBtn:", openFormBtn);
    console.log("plusBtn:", plusBtn);
    console.log("productForm:", productForm);

    if (openFormBtn) {
        openFormBtn.addEventListener("click", function() {
            console.log("openFormBtn clicked");
            if (productForm) productForm.style.display = "block";
        });
    }
    if (plusBtn) {
        plusBtn.addEventListener("click", function() {
            console.log("plusBtn clicked");
            if (productForm) productForm.style.display = "block";
        });
    }
    // Close form with all × buttons
    var closeBtns = document.getElementsByClassName("close-form");
    Array.from(closeBtns).forEach(function(btn){
        btn.onclick = function() {
            if (productForm) productForm.style.display = "none";
        }
    });
});

function toggleAdText(show) {
    var adTextInput = document.getElementById("adTextInput");
    if (adTextInput) {
        adTextInput.style.display = show ? "block" : "none";
    }
}
function addProduct() {
    var productList = document.getElementById("productList");
    if (!productList) {
        console.error("productList not found");
        return;
    }
    var newProduct = document.createElement("div");
    newProduct.className = "product-entry";
    newProduct.innerHTML = `
        <label>পণ্যের ছবি:</label>
        <input type="file" accept="image/*" name="product_image[]" required>
        <label>পণ্যের নাম:</label>
        <input type="text" name="product_name[]" required>
        <label>মজুদ:</label>
        <input type="number" name="stock[]" min="0" required>
        <label>দাম:</label>
        <input type="text" name="price[]" required>
    `;
    productList.appendChild(newProduct);
}