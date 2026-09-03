// ===========================================
// Smart Rental Manager
// Admin Login JavaScript
// ===========================================


// Password Show / Hide

const adminPassword = document.getElementById("adminPassword");

const toggleAdminPassword = document.getElementById("toggleAdminPassword");


toggleAdminPassword.addEventListener("click",()=>{


    if(adminPassword.type === "password"){


        adminPassword.type = "text";


        toggleAdminPassword.classList.remove("fa-eye");

        toggleAdminPassword.classList.add("fa-eye-slash");


    }
    else{


        adminPassword.type = "password";


        toggleAdminPassword.classList.remove("fa-eye-slash");

        toggleAdminPassword.classList.add("fa-eye");


    }


});




// Admin Login Form

const adminLoginForm = document.getElementById("adminLoginForm");



adminLoginForm.addEventListener("submit",(e)=>{


    e.preventDefault();



    const email = document.getElementById("adminEmail").value.trim();

    const password = adminPassword.value.trim();



    if(email === "" || password === ""){


        alert("Please fill all fields.");

        return;


    }



    // Temporary frontend login
    // Backend team will connect database authentication later


    alert("Admin Login Successful!");



    setTimeout(()=>{


        window.location.href="dashboard.html";


    },1000);



});