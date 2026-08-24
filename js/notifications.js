// ===========================================
// Smart Rental Manager
// Notifications JavaScript
// ===========================================



const clearBtn = document.getElementById("clearNotifications");

const notificationCards = document.querySelectorAll(".notification-card");

const emptyBox = document.getElementById("emptyNotification");






// ===============================
// Clear Notifications
// ===============================


clearBtn.addEventListener("click",()=>{


    let confirmClear = confirm(
        "Clear all notifications?"
    );



    if(confirmClear){



        notificationCards.forEach(card=>{


            card.style.display="none";


        });





        emptyBox.style.display="block";



        alert(
            "All notifications cleared."
        );



    }


});







// ===============================
// Notification Click
// ===============================


notificationCards.forEach(card=>{


    card.addEventListener("click",()=>{


        card.style.opacity="0.7";



    });



});