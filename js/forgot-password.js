// ===========================================
// Smart Rental Manager
// Forgot Password JavaScript
// ===========================================

const forgotForm = document.getElementById("forgotForm");

forgotForm.addEventListener("submit", function (e) {

    e.preventDefault();

    const email = document.getElementById("email").value.trim();

    // Check if email is entered
    if (email === "") {
        alert("Please enter your email address.");
        return;
    }

    // Basic email validation
    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return;
    }

    // Temporary frontend success
    alert("OTP has been sent to your email.");

    // Redirect to OTP page
    setTimeout(() => {
        window.location.href = "otp.html";
    }, 1000);

});