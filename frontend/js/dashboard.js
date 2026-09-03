// ===========================================
// Smart Rental Manager
// Dashboard JavaScript
// ===========================================



// ===============================
// User Data (Temporary)
// Backend will replace this later
// ===============================


let user = {

    name:"User",

    role:"Tenant"

};




// Display User Information


const userName = document.querySelector(".user-info h3");

const userRole = document.querySelector(".user-info p");



if(userName){

    userName.innerHTML = "Welcome, " + user.name;

}



if(userRole){

    userRole.innerHTML = user.role + " Account";

}




// ===============================
// Notification Button
// ===============================


const notificationBtn = document.querySelector(".notification-btn");



if(notificationBtn){


    notificationBtn.addEventListener("click",()=>{


        alert("You have new notifications.");


    });


}




// ===============================
// Logout Function
// ===============================


// This will connect with backend later


function logout(){


    let confirmLogout = confirm(
        "Are you sure you want to logout?"
    );


    if(confirmLogout){


        window.location.href="login.html";


    }


}




// ===============================
// Quick Action Click Animation
// ===============================


const cards = document.querySelectorAll(".action-card");



cards.forEach(card=>{


    card.addEventListener("click",()=>{


        card.style.transform="scale(.95)";


        setTimeout(()=>{


            card.style.transform="";


        },150);


    });


});