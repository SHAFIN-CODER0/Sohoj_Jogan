document.addEventListener("DOMContentLoaded", function () {
    const openModalBtn = document.getElementById("openModalBtn"); // New button to open the modal
    const newAccountBtn = document.getElementById("newAccountBtn");
    const joySectionBtn = document.querySelector(".joy-section .cta-btn "); // Select the button inside joy-section
    const accountModal = document.getElementById("accountModal");
    const closeModal = document.getElementById("closeModal");
    const signupCustomer = document.getElementById("signupCustomer");
    const signupShopOwner = document.getElementById("signupShopOwner");
    const customerSidebar = document.getElementById("customerRegistrationSidebar");
    const closeCustomerSidebar = document.getElementById("closeCustomerSidebar");
    const signupDeliveryMan = document.getElementById("signupDeliveryMan");
    const deliveryManRulesModal = document.getElementById("deliveryManRulesModal");
    const closeDeliveryRulesModal = document.getElementById("closeDeliveryRulesModal");
    // Show modal when "Create New Account" button is clicked
    newAccountBtn.addEventListener("click", function () {
        accountModal.style.display = "block"; // Show the modal
    });

    // Show modal when "openModalBtn" is clicked
    openModalBtn.addEventListener("click", function () {
        accountModal.style.display = "block"; // Show the modal
    });
    joySectionBtn.addEventListener("click", function () {
        accountModal.style.display = "block"; // Show the modal
    });

    // Close modal when close button is clicked
    closeModal.addEventListener("click", function () {
        accountModal.style.display = "none"; // Hide the modal
    });

    // Close modal if the user clicks outside the modal
    window.addEventListener("click", function (event) {
        if (event.target === accountModal) {
            accountModal.style.display = "none"; // Hide the modal
        }
    });

    // When "Customer" signup button is clicked, show the sidebar for customer registration
    signupCustomer.addEventListener("click", function () {
        accountModal.style.display = "none"; // Hide the modal
        customerSidebar.classList.add("show"); // Show customer sidebar
        
        // Close the login sidebar if it's open (optional)
        document.getElementById("loginSidebar").classList.remove("show");
    });

    // When "Shop Owner" signup button is clicked, close the modal
    signupShopOwner.addEventListener("click", function () {
        accountModal.style.display = "none"; // Hide the modal
    });

    // Close the customer sidebar when the close button is clicked
    closeCustomerSidebar.addEventListener("click", function () {
        customerSidebar.classList.remove("show"); // Remove the 'show' class to close the sidebar
    });
});
 // When the delivery man button is clicked
 signupDeliveryMan.addEventListener("click", function () {
    accountModal.style.display = "none"; // Close the account modal
    deliveryManRulesModal.style.display = "block"; // Show the delivery man rules modal
});

// Close the delivery modal when close (X) button is clicked
closeDeliveryRulesModal.addEventListener("click", function () {
    deliveryManRulesModal.style.display = "none";
});

// Close delivery modal when clicking outside of modal content
window.addEventListener("click", function (event) {
    if (event.target === deliveryManRulesModal) {
        deliveryManRulesModal.style.display = "none";
    }
});