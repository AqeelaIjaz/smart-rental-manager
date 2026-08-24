// ===========================================
// Smart Rental Manager
// Reset Password JavaScript
// ===========================================


// Password fields

const newPassword = document.getElementById("newPassword");
const confirmNewPassword = document.getElementById("confirmNewPassword");

const toggleNewPassword = document.getElementById("toggleNewPassword");
const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");

const passwordStrength = document.getElementById("passwordStrength");


// ===============================
// Show / Hide New Password
// ===============================

toggleNewPassword.addEventListener("click", () => {

    if(newPassword.type === "password"){

        newPassword.type = "text";

        toggleNewPassword.classList.remove("fa-eye");
        toggleNewPassword.classList.add("fa-eye-slash");

    }
    else{

        newPassword.type = "password";

        toggleNewPassword.classList.remove("fa-eye-slash");
        toggleNewPassword.classList.add("fa-eye");

    }

});


// ===============================
// Show / Hide Confirm Password
// ===============================

toggleConfirmPassword.addEventListener("click", () => {

    if(confirmNewPassword.type === "password"){

        confirmNewPassword.type = "text";

        toggleConfirmPassword.classList.remove("fa-eye");
        toggleConfirmPassword.classList.add("fa-eye-slash");

    }
    else{

        confirmNewPassword.type = "password";

        toggleConfirmPassword.classList.remove("fa-eye-slash");
        toggleConfirmPassword.classList.add("fa-eye");

    }

});



// ===============================
// Password Strength Checker
// ===============================

newPassword.addEventListener("keyup",()=>{


    let password = newPassword.value;


    if(password.length < 6){

        passwordStrength.innerHTML = "Weak Password";
        passwordStrength.style.color = "red";

    }

    else if(password.length < 10){

        passwordStrength.innerHTML = "Medium Password";
        passwordStrength.style.color = "orange";

    }

    else{

        passwordStrength.innerHTML = "Strong Password";
        passwordStrength.style.color = "green";

    }


});




// ===============================
// Reset Password Form
// ===============================

const resetForm = document.getElementById("resetForm");


resetForm.addEventListener("submit",(e)=>{


    e.preventDefault();


    if(newPassword.value === "" || confirmNewPassword.value === ""){

        alert("Please fill all fields.");

        return;

    }



    if(newPassword.value !== confirmNewPassword.value){

        alert("Passwords do not match.");

        return;

    }



    alert("Password Reset Successfully!");



    setTimeout(()=>{

        window.location.href="login.html";

    },1000);



});