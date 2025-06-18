document.addEventListener("DOMContentLoaded", function () {
    const signupShopOwner = document.getElementById("signupShopOwner");
    const shopOwnerModal = document.getElementById("shopOwnerModal");
    const agreeToTermsBtn = document.getElementById("agreeToTermsBtn");
    const closeShopOwnerModal = document.getElementById("closeShopOwnerModal");
    const registrationSidebar = document.getElementById("registrationSidebar");
    const closeRegistrationSidebar = document.getElementById("closeRegistrationSidebar");
    const loginSidebar = document.getElementById("loginSidebar"); // Add this line

    // When "Shop Owner" signup button is clicked, show the shop owner terms modal
    signupShopOwner.addEventListener("click", function () {  
        shopOwnerModal.style.display = "block"; // Show the shop owner terms modal
    });

    // When user agrees to the terms and conditions
    agreeToTermsBtn.addEventListener("click", function () {
        // Close the terms modal
        shopOwnerModal.style.display = "none";

        // Close the login sidebar if it's open
        if (loginSidebar.classList.contains("show")) {
            loginSidebar.classList.remove("show");
        }

        // Show the registration sidebar
        registrationSidebar.classList.add("show");

        // Optional: Show confirmation
        alert("আপনার শর্তাবলী গ্রহণ সম্পন্ন হয়েছে। এখন আপনি দোকান খোলার জন্য ফর্ম পূরণ করতে পারবেন।");
    });

    // Close the modal when close button (X) is clicked
    closeShopOwnerModal.addEventListener("click", function () {
        shopOwnerModal.style.display = "none";
    });

    // Close modal if the user clicks outside the modal
    window.addEventListener("click", function (event) {
        if (event.target === shopOwnerModal) {
            shopOwnerModal.style.display = "none";
        }
    });

    // Close the registration sidebar when the close button (X) is clicked
    closeRegistrationSidebar.addEventListener("click", function () {
        registrationSidebar.classList.remove("show");
    });

    // Close the registration sidebar if the user clicks outside of it
    window.addEventListener("click", function (event) {
        if (!registrationSidebar.contains(event.target) && event.target !== signupShopOwner) {
            registrationSidebar.classList.remove("show");
        }
    });
});
