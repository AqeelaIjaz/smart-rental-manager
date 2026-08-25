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

            togglePassword.classList.replace(
                "fa-eye",
                "fa-eye-slash"
            );

        }

        else{

            password.type="password";

            togglePassword.classList.replace(
                "fa-eye-slash",
                "fa-eye"
            );

        }


    });

}






// ----------------------------
// Form Validation
// ----------------------------


document.getElementById("signupForm")
.addEventListener("submit",(e)=>{


    e.preventDefault();



    const name =
    document.getElementById("fullName").value.trim();


    const email =
    document.getElementById("email").value.trim();


    const phone =
    document.getElementById("phone").value.trim();





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





    alert("Account Created Successfully!");



});