// ===========================================
// Smart Rental Manager
// Receipt JavaScript
// ===========================================



const paymentDate = document.getElementById("paymentDate");

const downloadReceipt = document.getElementById("downloadReceipt");





// ===============================
// Set Current Date
// ===============================


const today = new Date();



const date = today.getDate();

const month = today.getMonth()+1;

const year = today.getFullYear();



paymentDate.innerHTML =
`${date}/${month}/${year}`;







// ===============================
// Download Receipt
// ===============================


downloadReceipt.addEventListener("click",()=>{


    window.print();


});