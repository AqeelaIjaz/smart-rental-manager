// ===========================================
// Smart Rental Manager
// Profile JavaScript
// ===========================================



const editProfile = document.getElementById("editProfile");

const logoutBtn = document.getElementById("logoutBtn");





// ===============================
// Edit Profile
// ===============================


editProfile.addEventListener("click",()=>{


    alert(
        "Edit profile feature will be connected with backend."
    );


});







// ===============================
// Logout
// ===============================


logoutBtn.addEventListener("click",()=>{


    let confirmLogout = confirm(
        "Are you sure you want to logout?"
    );



    if(confirmLogout){


        alert(
            "Logged out successfully."
        );


        window.location.href="login.html";


    }


});






// ===============================
// Load User Data (Demo)
// Backend will replace this
// ===============================



const user = {


    name:"Ali Ahmed",

    role:"Tenant"


};





const userName = document.getElementById("userName");

const userRole = document.getElementById("userRole");




if(userName){

    userName.innerHTML=user.name;

}



if(userRole){

    userRole.innerHTML=user.role;

}