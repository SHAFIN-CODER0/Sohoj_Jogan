let quantity = 3; // Initial quantity
const unitPrice = 240; // Unit price per item
let deliveryCharge = 20; // Default delivery charge

// Get DOM elements
const qtyDisplay = document.getElementById("qty-display");
const unitPriceEl = document.getElementById("unit-price");
const deliveryChargeEl = document.getElementById("delivery-charge");
const totalPriceEl = document.getElementById("total-price");

// Function to convert numbers to Bengali digits
function toBengali(num) {
    return num.toString().replace(/\d/g, d => "০১২৩৪৫৬৭৮৯"[d]);
}

// Function to confirm order submission
function confirmOrder() {
    // Collecting the input values
    const customerName = document.getElementById('customer-name').value.trim();
    const customerAddress = document.getElementById('customer-address').value.trim();
    const customerPhone = document.getElementById('customer-phone').value.trim();
    const customerComment = document.getElementById('customer-comment').value.trim();

    // Validate that all fields are filled out
    if (customerName === '' || customerAddress === '' || customerPhone === '') {
        alert('দয়া করে সব ক্ষেত্র পূর্ণ করুন!');
        return false; // Prevent form submission
    }

    // Validate phone number (basic validation for format)
    const phoneRegex = /^[0-9]{11}$/; // Example: 11 digits, adjust as per your needs
    if (!phoneRegex.test(customerPhone)) {
        alert('ভুল ফোন নাম্বার। ১১ ডিজিটের ফোন নাম্বার দিন!');
        return false; // Prevent form submission
    }

    // Confirmation dialog
    const confirmation = confirm('আপনি কি নিশ্চিত যে আপনার অর্ডার দিতে চান?');
    if (confirmation) {
        // Proceed with form submission (you can modify this for AJAX requests if needed)
        alert('অর্ডার সফলভাবে নিশ্চিত করা হয়েছে!');
        return true; // Allow form submission
    } else {
        alert('অর্ডার বাতিল করা হয়েছে।');
        return false; // Prevent form submission
    }
}

