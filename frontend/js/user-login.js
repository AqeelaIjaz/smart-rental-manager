// ===========================================
// Smart Rental Manager
// User Login JavaScript
// ===========================================

// Password Show / Hide

const password = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");

togglePassword.addEventListener("click", () => {

    if (password.type === "password") {
        password.type = "text";
        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");
    } else {
        password.type = "password";
        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");
    }

});


// User Login Form

const loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", async (e) => {

    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const pass = password.value.trim();

    if (email === "" || pass === "") {
        alert("Please fill all fields.");
        return;
    }

    try {
        const response = await fetch("../../backend/auth/login.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: email, password: pass })
        });

        const result = await response.json();

        if (result.success) {

            // login.php now returns { success, message, data: { id, name, email, phone, role, language } }
            // Save it directly - no need to fill in email ourselves anymore.
            localStorage.setItem("user", JSON.stringify(result.data));

            alert("Login Successful!");
            setTimeout(() => {
                window.location.href = "dashboard.html";
            }, 1000);
        } else {
            alert("Login failed: " + result.message);
        }

    } catch (err) {
        alert("Error connecting to server.");
        console.error(err);
    }

});