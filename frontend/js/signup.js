// ===================================
// Smart Rental Manager
// Signup Page
// ===================================


// Password Fields

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

const togglePassword = document.getElementById("togglePassword");


// ----------------------------
// Show / Hide Password
// ----------------------------

if(togglePassword){

    togglePassword.addEventListener("click",()=>{

        if(password.type === "password"){
            password.type="text";
            togglePassword.classList.replace("fa-eye","fa-eye-slash");
        }
        else{
            password.type="password";
            togglePassword.classList.replace("fa-eye-slash","fa-eye");
        }

    });

}


// ----------------------------
// Form Validation + Submit
// ----------------------------

document.getElementById("signupForm")
.addEventListener("submit", async (e)=>{

    e.preventDefault();

    const name = document.getElementById("fullName").value.trim();
    const email = document.getElementById("email").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const role = document.getElementById("role").value; // "tenant" or "landlord"

    if(
        name === "" ||
        email === "" ||
        phone === "" ||
        password.value === "" ||
        confirmPassword.value === ""
    ){
        alert("Please fill all fields.");
        return;
    }

    if(password.value !== confirmPassword.value){
        alert("Passwords do not match.");
        return;
    }

    try {

        const response = await fetch("../../backend/auth/signup.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                name: name,
                phone: phone,
                email: email,
                password: password.value,
                role: role
            })
        });

        const result = await response.json();

        if(result.success){
            alert("Account Created Successfully!");
            setTimeout(()=>{
                window.location.href = "login.html";
            },1000);
        } else {
            alert("Signup failed: " + result.message);
        }

    } catch(err){
        alert("Error connecting to server.");
        console.error(err);
    }

});