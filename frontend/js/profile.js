// ===========================================
// Smart Rental Manager
// Profile JavaScript
// ===========================================



const editProfile = document.getElementById("editProfile");

const logoutBtn = document.getElementById("logoutBtn");

const viewInfoCard = document.getElementById("viewInfoCard");
const editInfoCard = document.getElementById("editInfoCard");
const editProfileForm = document.getElementById("editProfileForm");
const cancelEditBtn = document.getElementById("cancelEditBtn");

const editNameInput = document.getElementById("editName");
const editEmailInput = document.getElementById("editEmail");
const editPhoneInput = document.getElementById("editPhone");




// ===============================
// Load Logged-in User Data
// Saved to localStorage by user-login.js at login time
// ===============================


const userName = document.getElementById("userName");
const userRole = document.getElementById("userRole");
const fullNameEl = document.getElementById("fullName");
const emailEl = document.getElementById("email");
const phoneEl = document.getElementById("phone");

let currentUser = null;

const storedUser = localStorage.getItem("user");

if (storedUser) {

    currentUser = JSON.parse(storedUser);

    if (userName) userName.innerHTML = currentUser.name || "";
    if (userRole) userRole.innerHTML = currentUser.role || "";
    if (fullNameEl) fullNameEl.innerHTML = currentUser.name || "";
    if (emailEl) emailEl.innerHTML = currentUser.email || "";
    if (phoneEl) phoneEl.innerHTML = currentUser.phone || "Not available";

} else {

    // Nobody logged in - send back to login page
    window.location.href = "login.html";

}




// ===============================
// Edit Profile - show the edit form
// ===============================


editProfile.addEventListener("click", () => {

    if (!currentUser) return;

    editNameInput.value = currentUser.name || "";
    editEmailInput.value = currentUser.email || "";
    editPhoneInput.value = currentUser.phone || "";

    viewInfoCard.style.display = "none";
    editInfoCard.style.display = "block";

});


cancelEditBtn.addEventListener("click", () => {

    editInfoCard.style.display = "none";
    viewInfoCard.style.display = "block";

});




// ===============================
// Save Edited Profile
// ===============================


editProfileForm.addEventListener("submit", async (e) => {

    e.preventDefault();

    const name = editNameInput.value.trim();
    const email = editEmailInput.value.trim();
    const phone = editPhoneInput.value.trim();

    if (name === "" || email === "") {
        alert("Name and email are required.");
        return;
    }

    const saveBtn = document.getElementById("saveProfileBtn");
    saveBtn.disabled = true;
    saveBtn.innerText = "Saving...";

    try {

        // NOTE: confirm this path with Meeral - assumes the update.php
        // template was placed at backend/users/update.php
        const response = await fetch("../../backend/users/update.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include", // sends session cookie so requireLogin() passes
            body: JSON.stringify({ name, email, phone })
        });

        const result = await response.json();

        if (result.success) {

            currentUser = { ...currentUser, ...result.data };
            localStorage.setItem("user", JSON.stringify(currentUser));

            if (userName) userName.innerHTML = currentUser.name || "";
            if (fullNameEl) fullNameEl.innerHTML = currentUser.name || "";
            if (emailEl) emailEl.innerHTML = currentUser.email || "";
            if (phoneEl) phoneEl.innerHTML = currentUser.phone || "Not available";

            alert("Profile updated successfully!");

            editInfoCard.style.display = "none";
            viewInfoCard.style.display = "block";

        } else {
            alert("Update failed: " + (result.message || "Please try again."));
        }

    } catch (err) {
        alert("Error connecting to server.");
        console.error(err);
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerText = "Save";
    }

});







// ===============================
// Logout
// ===============================


logoutBtn.addEventListener("click",()=>{


    let confirmLogout = confirm(
        "Are you sure you want to logout?"
    );



    if(confirmLogout){


        // clear the saved session data on logout
        localStorage.removeItem("user");


        alert(
            "Logged out successfully."
        );


        window.location.href="login.html";


    }


});