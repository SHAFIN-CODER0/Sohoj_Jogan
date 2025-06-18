document.addEventListener("DOMContentLoaded", function () {
    const openModalBtn = document.getElementById("openModalBtn"); // Button to open modal
    const accountModal = document.getElementById("accountModal"); // Modal element
    const closeModal = document.getElementById("closeModal"); // Close button inside modal

    // Show modal when "সাইন আপ" button is clicked
    openModalBtn.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default behavior of the link
        accountModal.style.display = "block"; // Show the modal
    });

    // Close modal when close button (×) is clicked
    closeModal.addEventListener("click", function () {
        accountModal.style.display = "none"; // Hide the modal
    });

    // Close modal if user clicks outside of modal content
    window.addEventListener("click", function (event) {
        if (event.target === accountModal) {
            accountModal.style.display = "none"; // Close the modal if clicked outside
        }
    });
});
