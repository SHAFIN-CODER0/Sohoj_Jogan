document.addEventListener("DOMContentLoaded", function () {
    // Close sidebar functionality
    const closeBtn = document.getElementById("closeCustomerSidebar");
    const sidebar = document.getElementById("customerRegistrationSidebar");
    const overlay = document.querySelector(".overlay");

    if (closeBtn && sidebar) {
        closeBtn.addEventListener("click", function () {
            sidebar.classList.remove("show");
            if (overlay) {
                overlay.style.display = "none";
            }
        });
    }

    // Validate image file type
    const customerPicInput = document.getElementById("customerPic");

    customerPicInput.addEventListener("change", function (event) {
        const file = event.target.files[0];

        if (file) {
            const fileType = file.type;
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];

            if (!validTypes.includes(fileType)) {
                alert("Invalid image file type. Please upload a JPG, PNG, or GIF.");
                customerPicInput.value = ""; // Clear the input field
            }
        }
    });
});
// Function to show the OTP Method popup
function showOTPMethodPopup() {
    document.getElementById('otpMethodSection').style.display = 'flex';
}

// Function to close the OTP Method popup
function closeOTPMethodPopup() {
    document.getElementById('otpMethodSection').style.display = 'none';
}

// Event listener for the "নিবন্ধন করুন" button click
document.getElementById('registerBtn').addEventListener('click', function(event) {
    event.preventDefault();  // Prevent form submission

    // Show OTP Method popup
    showOTPMethodPopup();
});

// Event listener to close the popup
document.getElementById('closePopupBtn').addEventListener('click', closeOTPMethodPopup);
