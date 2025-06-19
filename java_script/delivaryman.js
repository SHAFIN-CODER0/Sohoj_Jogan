document.addEventListener("DOMContentLoaded", function () {
    const acceptTermsBtn = document.getElementById("acceptTermsBtn");
    const deliveryManRulesModal = document.getElementById("deliveryManRulesModal");
    const deliveryManRegisterSidebar = document.getElementById("deliveryManRegisterSidebar");
    const closeDeliveryRulesModal = document.getElementById("closeDeliveryRulesModal");
    const closeDeliveryManRegisterSidebar = document.getElementById("closeDeliveryManRegisterSidebar");
    const loginSidebar = document.getElementById("loginSidebar"); // <-- Reference to login sidebar

    // When the "Agree" button is clicked
    acceptTermsBtn.addEventListener("click", function () {
        // Close the terms modal
        deliveryManRulesModal.style.display = "none";

        // Close the login sidebar if it's open
        if (loginSidebar.classList.contains("show")) {
            loginSidebar.classList.remove("show");
        }

        // Show the delivery man registration sidebar
        deliveryManRegisterSidebar.classList.add("open");

        // Optional alert
        alert("আপনার শর্তাবলী গ্রহণ সম্পন্ন হয়েছে। এখন আপনি ডেলিভারি ম্যান নিবন্ধন ফর্ম পূরণ করতে পারবেন।");
    });

    // Close the Delivery Man Rules Modal when the close button (X) is clicked
    closeDeliveryRulesModal.addEventListener("click", function () {
        deliveryManRulesModal.style.display = "none";
    });

    // Close the Delivery Man Registration Sidebar when the close button (X) is clicked
    closeDeliveryManRegisterSidebar.addEventListener("click", function () {
        deliveryManRegisterSidebar.classList.remove("open");
    });

    // Close modals/sidebars if user clicks outside
    window.addEventListener("click", function (event) {
        if (event.target === deliveryManRulesModal) {
            deliveryManRulesModal.style.display = "none";
        }
        if (event.target === deliveryManRegisterSidebar) {
            deliveryManRegisterSidebar.classList.remove("open");
        }
    });
});
window.addEventListener("click", function (event) {
    // Close modal if clicked outside
    if (event.target === deliveryManRulesModal) {
        deliveryManRulesModal.style.display = "none";
    }

    // Close sidebar if clicked outside
    if (!deliveryManRegisterSidebar.contains(event.target) && !acceptTermsBtn.contains(event.target)) {
        deliveryManRegisterSidebar.classList.remove("open");
    }
});