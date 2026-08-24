// ===========================================
// Smart Rental Manager
// Payment JavaScript
// ===========================================



const payBtn = document.getElementById("payBtn");

const paymentAmount = document.getElementById("paymentAmount");

const paymentStatus = document.getElementById("paymentStatus");






// ===============================
// Confirm Payment
// ===============================


payBtn.addEventListener("click",()=>{



    const amount = paymentAmount.value;



    if(amount === ""){


        alert(
            "Please enter payment amount."
        );


        return;


    }





    // Demo payment processing


    paymentStatus.innerHTML =
    "Processing payment...";




    setTimeout(()=>{



        paymentStatus.innerHTML =
        "Payment completed successfully!";



        alert(
            "Payment successful. Receipt generated."
        );




        setTimeout(()=>{



            window.location.href="receipt.html";



        },1000);




    },1500);




});